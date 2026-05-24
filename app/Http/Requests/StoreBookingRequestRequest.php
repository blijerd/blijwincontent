<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'event_type' => ['required', 'string', 'max:120'],
            'package_slug' => ['nullable', 'string', 'max:120'],
            'requested_date' => ['required', 'date', 'after_or_equal:today'],
            'requested_start_time' => ['nullable', 'date_format:H:i'],
            'alternative_date' => ['nullable', 'date', 'after_or_equal:today'],
            'alternative_start_time' => ['nullable', 'date_format:H:i'],
            'duration_minutes' => ['nullable', 'integer', 'min:30', 'max:720'],
            'guest_count' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'location_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:32'],
            'city' => ['required', 'string', 'max:255'],
            'contact_first_name' => ['required', 'string', 'max:255'],
            'contact_last_name' => ['nullable', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:64'],
            'notes_markdown' => ['nullable', 'string', 'max:5000'],
            'privacy_accepted' => ['accepted'],
            (string) config('settings.booking_requests.honeypot_field', 'website_url') => ['nullable', 'prohibited'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'event_type' => 'soort feest',
            'requested_date' => 'datum',
            'requested_start_time' => 'starttijd',
            'alternative_date' => 'alternatieve datum',
            'alternative_start_time' => 'alternatieve starttijd',
            'duration_minutes' => 'duur',
            'guest_count' => 'aantal gasten',
            'city' => 'plaats',
            'contact_first_name' => 'voornaam',
            'contact_last_name' => 'achternaam',
            'organization' => 'organisatie',
            'email' => 'e-mailadres',
            'phone' => 'telefoonnummer',
            'notes_markdown' => 'opmerkingen',
            'privacy_accepted' => 'privacyverklaring',
        ];
    }
}
