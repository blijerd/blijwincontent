<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Boeking aanvragen | {{ $site?->name ?? 'Blijwin' }}</title>
    <meta name="robots" content="index,follow">
    <meta name="description" content="Vraag vrijblijvend een boeking bij Blijwin aan.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <div class="bw-site-shell">
        <header class="bw-site-header">
            <a href="{{ url('/') }}" class="bw-brand-lockup" aria-label="{{ $site?->name ?? 'Blijwin' }}">
                <span class="bw-brand-mark">{{ $site?->name ?? 'Blijwin' }}</span>
                <span class="bw-brand-copy">
                    <strong>{{ $site?->name ?? 'Blijwin' }}</strong>
                    <span>boekingsaanvraag</span>
                </span>
            </a>
        </header>

        <main class="bw-page-stack">
            <section class="bw-booking-shell" data-booking-request-form>
                <div class="bw-booking-intro">
                    <span class="bw-pill bw-pill--hero">Vrijblijvende aanvraag</span>
                    <h1>Boeking aanvragen</h1>
                    <p>Kies je datum, vul je gegevens in en ontvang daarna de vervolgstap per e-mail.</p>
                </div>

                <form class="bw-booking-form" method="post" action="{{ route('booking-requests.store') }}">
                    @csrf
                    <input type="text" name="{{ config('settings.booking_requests.honeypot_field', 'website_url') }}" tabindex="-1" autocomplete="off" class="bw-booking-honeypot" aria-hidden="true">

                    <div class="bw-booking-grid">
                        <label>
                            <span>Soort feest</span>
                            <select name="event_type" required>
                                @foreach($catalogOptions as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                                <option value="anders">Iets anders</option>
                            </select>
                        </label>

                        <label>
                            <span>Pakket of voorkeur</span>
                            <input name="package_slug" type="text" autocomplete="off" placeholder="Bijvoorbeeld basis, premium of weet ik nog niet">
                        </label>

                        <label>
                            <span>Datum</span>
                            <input name="requested_date" type="date" required min="{{ now()->toDateString() }}">
                        </label>

                        <label>
                            <span>Starttijd</span>
                            <input name="requested_start_time" type="time">
                        </label>

                        <label>
                            <span>Alternatieve datum</span>
                            <input name="alternative_date" type="date" min="{{ now()->toDateString() }}">
                        </label>

                        <label>
                            <span>Alternatieve starttijd</span>
                            <input name="alternative_start_time" type="time">
                        </label>

                        <label>
                            <span>Duur in minuten</span>
                            <input name="duration_minutes" type="number" min="30" max="720" step="15" placeholder="120">
                        </label>

                        <label>
                            <span>Aantal gasten</span>
                            <input name="guest_count" type="number" min="1" max="5000" placeholder="40">
                        </label>
                    </div>

                    <div class="bw-booking-grid">
                        <label>
                            <span>Locatie</span>
                            <input name="location_name" type="text" autocomplete="organization" placeholder="Naam van school, zaal of thuislocatie">
                        </label>

                        <label>
                            <span>Plaats</span>
                            <input name="city" type="text" autocomplete="address-level2" required>
                        </label>

                        <label>
                            <span>Adres</span>
                            <input name="address" type="text" autocomplete="street-address">
                        </label>

                        <label>
                            <span>Postcode</span>
                            <input name="postal_code" type="text" autocomplete="postal-code">
                        </label>
                    </div>

                    <div class="bw-booking-grid">
                        <label>
                            <span>Voornaam</span>
                            <input name="contact_first_name" type="text" autocomplete="given-name" required>
                        </label>

                        <label>
                            <span>Achternaam</span>
                            <input name="contact_last_name" type="text" autocomplete="family-name">
                        </label>

                        <label>
                            <span>Organisatie</span>
                            <input name="organization" type="text" autocomplete="organization">
                        </label>

                        <label>
                            <span>E-mailadres</span>
                            <input name="email" type="email" autocomplete="email" required>
                        </label>

                        <label>
                            <span>Telefoonnummer</span>
                            <input name="phone" type="tel" autocomplete="tel">
                        </label>
                    </div>

                    <label>
                        <span>Opmerkingen</span>
                        <textarea name="notes_markdown" rows="5" placeholder="Vertel kort wat je wilt boeken en wat handig is om te weten."></textarea>
                    </label>

                    <label class="bw-booking-checkbox">
                        <input name="privacy_accepted" type="checkbox" value="1" required>
                        <span>Ik ga akkoord met verwerking van mijn gegevens voor deze boekingsaanvraag.</span>
                    </label>

                    <div class="bw-booking-actions">
                        <button class="bw-button-primary" type="submit">Aanvraag verzenden</button>
                        <p data-booking-request-error role="alert"></p>
                    </div>
                </form>

                <div class="bw-booking-status" data-booking-request-status hidden>
                    <div class="bw-booking-status__step" data-status-screen="submitting">
                        <strong>Wordt verzonden</strong>
                        <span>We controleren je aanvraag en sturen hem door naar Blijwin OS.</span>
                    </div>
                    <div class="bw-booking-status__step" data-status-screen="available">
                        <strong>Is beschikbaar</strong>
                        <span>Deze aanvraag lijkt beschikbaar. Bevestig je e-mailadres om verder te gaan.</span>
                    </div>
                    <div class="bw-booking-status__step" data-status-screen="confirm_email">
                        <strong>Bevestig je e-mailadres</strong>
                        <span>We hebben je aanvraag ontvangen. In je mailbox staat de vervolgstap.</span>
                    </div>
                    <div class="bw-booking-status__step" data-status-screen="propose_alternative">
                        <strong>Voorstel andere tijd</strong>
                        <span>De gekozen tijd lijkt niet direct passend. Je ontvangt een voorstel of gebruikt je alternatieve datum.</span>
                    </div>
                    <div class="bw-booking-status__step" data-status-screen="queued">
                        <strong>Lokaal opgeslagen</strong>
                        <span>De koppeling is tijdelijk niet bereikbaar. De aanvraag staat klaar om automatisch opnieuw te verzenden.</span>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
