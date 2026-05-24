# Blijwin Content CMS

Zelfstandig Laravel 12 + Filament 4 CMS voor markdown-first contentbeheer. Dit project staat los van Blijwin OS, maar volgt dezelfde modulaire monolith-filosofie: backend als source of truth, domeinlogica in actions/services en relationele contentmodellen.

## Stack

- PHP 8.4+
- Laravel 12
- Filament 4
- Livewire, Blade, Tailwind CSS en Alpine.js
- MySQL in productie, SQLite bruikbaar voor lokale tests

## Contentmodel

- `Site`: domein, default locale en beschikbare locales.
- `Page`: locale-specifieke pagina met slug, full path, template, status, SEO metadata en `translation_group_id`.
- `Section`: relationele pagina-secties met gecontroleerde section types.
- `Block`: relationele contentblokken met Markdown body, CTA en optionele media.
- `MediaAsset`: media naast content, passend bij latere GRAV page-folder mapping.
- `Redirect`: locale-specifieke redirects.
- `ActivityLog`: basis voor audit trail.

Er is bewust geen generieke JSON pagebuilder. Markdown content staat in expliciete velden en structuur staat relationeel in MySQL.

## GRAV Voorbereiding

De structuur kan later gemapt worden vanuit GRAV:

- page folders naar `pages`
- frontmatter naar template/status/SEO/metadata
- modular pages naar `sections`
- modular content naar `blocks`
- markdown body naar Markdown velden
- media naast pagina's naar `media_assets`
- redirects naar `redirects`
- vertaalde pagina's naar gedeelde `translation_group_id`

De importer zelf is nog niet gebouwd.

## Rendering

Publieke pagina's renderen SSR via Blade. Controllers blijven dun; `PageRenderService`, `MarkdownRenderService` en `SeoMetadataService` bouwen de viewdata. Templates zijn typed via `TemplateType` en section partials via `SectionType`.

Markdown wordt gecachet, unsafe HTML wordt gestript en links worden via CommonMark veilig verwerkt. Interne link/media resolvers zijn voorbereid als uitbreidingspunt.

## Admin

Filament resources zijn aanwezig voor sites, pages, sections, blocks, media assets en redirects. De eerste versie bevat Markdown editor, locale/status/template filters, SEO velden en relationele selectors. Page tree en drag/drop sorting zijn voorbereid via parent/sort_order en kunnen als custom Filament page verder worden verfijnd.

## Settings

Functionele applicatie-instellingen staan zoveel mogelijk in `config/settings.php`, niet in `.env`. Denk aan appnaam, publieke URL, locales, cache, queue, session, logging, mail defaults en filesystem defaults.

`.env` blijft bedoeld voor runtime en infrastructuur:

- `APP_ENV`, `APP_DEBUG`, `APP_KEY` en eventuele previous keys
- databaseverbinding
- optionele Redis-infrastructuur

Databasegegevens blijven bewust environment-specifiek en staan dus in `.env`.

## Installatie

```bash
composer install
cp .env.example .env
php artisan key:generate
# Zet je databasegegevens in .env en overige defaults in config/settings.php.
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Admin: `/admin`

Seed login:

- Email: `admin@example.com`
- Password: `password`

## Tests

```bash
php artisan test
```
