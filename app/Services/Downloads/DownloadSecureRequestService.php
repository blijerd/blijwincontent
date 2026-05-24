<?php

namespace App\Services\Downloads;

use App\Models\DownloadFormat;
use App\Models\DownloadMailLog;
use App\Models\DownloadSecureToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DownloadSecureRequestService
{
    /** @return array<string, string> */
    public function request(Request $request): array
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'category_id' => ['required', 'uuid'],
            'item_id' => ['required', 'uuid'],
            'format_id' => ['required', 'uuid'],
            'form_token' => ['required', 'string', 'max:80'],
            config('settings.downloads.secure.honeypot_field', 'website_url') => ['nullable', 'string', 'max:255'],
        ]);

        $this->guardHoneypot($data);
        $this->guardSubmitTime((string) $data['form_token']);
        $this->guardRateLimit($request);

        $format = DownloadFormat::query()
            ->with('item.category')
            ->where('public_id', $data['format_id'])
            ->whereHas('item', fn ($query) => $query
                ->where('public_id', $data['item_id'])
                ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('public_id', $data['category_id'])))
            ->firstOrFail();

        abort_unless($format->is_secure, 404);

        $token = DownloadSecureToken::query()->create([
            'download_format_id' => $format->id,
            'token' => Str::random(64),
            'first_name' => $data['first_name'],
            'email' => $data['email'],
            'expires_at' => now()->addHours((int) config('settings.downloads.secure.token_ttl_hours', 48)),
            'ip_address' => $request->ip(),
        ]);

        $deliveryUrl = route('downloads.secure', ['token' => $token->token]);

        try {
            Mail::raw($this->mailBody((string) $data['first_name'], $format->item->title, $deliveryUrl), function ($message) use ($data): void {
                $message
                    ->to($data['email'])
                    ->subject(config('settings.downloads.secure.mail_subject', 'Je download staat klaar'));
            });

            DownloadMailLog::query()->create([
                'download_format_id' => $format->id,
                'download_secure_token_id' => $token->id,
                'first_name' => $data['first_name'],
                'email' => $data['email'],
                'status' => 'sent',
                'ip_address' => $request->ip(),
            ]);
        } catch (\Throwable $exception) {
            DownloadMailLog::query()->create([
                'download_format_id' => $format->id,
                'download_secure_token_id' => $token->id,
                'first_name' => $data['first_name'],
                'email' => $data['email'],
                'status' => 'failed',
                'message' => $exception->getMessage(),
                'ip_address' => $request->ip(),
            ]);

            throw $exception;
        }

        return [
            'message' => 'We hebben de downloadlink naar je e-mailadres gestuurd.',
        ];
    }

    /** @param array<string, mixed> $data */
    private function guardHoneypot(array $data): void
    {
        $field = config('settings.downloads.secure.honeypot_field', 'website_url');

        if (! empty($data[$field])) {
            throw ValidationException::withMessages(['email' => 'De aanvraag kon niet worden verwerkt.']);
        }
    }

    private function guardSubmitTime(string $formToken): void
    {
        $sessionKey = config('settings.downloads.secure.form_token_session_key', 'download_secure_form_tokens').'.'.$formToken;
        $createdAt = session($sessionKey);
        $minimumSeconds = (int) config('settings.downloads.secure.min_submit_seconds', 4);

        if (! is_int($createdAt) || now()->timestamp - $createdAt < $minimumSeconds) {
            throw ValidationException::withMessages(['email' => 'Wacht kort voordat je de download aanvraagt.']);
        }

        session()->forget($sessionKey);
    }

    private function guardRateLimit(Request $request): void
    {
        $minuteKey = 'downloads:minute:'.$request->ip();
        $hourKey = 'downloads:hour:'.$request->ip();

        if (
            RateLimiter::tooManyAttempts($minuteKey, (int) config('settings.downloads.secure.rate_limits.per_minute', 2))
            || RateLimiter::tooManyAttempts($hourKey, (int) config('settings.downloads.secure.rate_limits.per_hour', 6))
        ) {
            throw ValidationException::withMessages(['email' => 'Er zijn te veel aanvragen gedaan. Probeer het later opnieuw.']);
        }

        RateLimiter::hit($minuteKey, 60);
        RateLimiter::hit($hourKey, 3600);
    }

    private function mailBody(string $firstName, string $title, string $deliveryUrl): string
    {
        return trim("Hallo {$firstName},\n\nJe download '{$title}' staat klaar:\n{$deliveryUrl}\n\nDeze link blijft tijdelijk geldig.\n\nBlijwin");
    }
}
