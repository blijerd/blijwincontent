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
- `FaqCategory` en `FaqItem`: centrale veelgestelde vragen, koppelbaar aan FAQ-secties.
- `DownloadCategory`, `DownloadItem` en `DownloadFormat`: centrale downloadcatalogus, koppelbaar aan downloads-secties.
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

## SEO

De XML-sitemap is beschikbaar op `/sitemap.xml`. `SitemapBuilderService` neemt alleen gepubliceerde, routeerbare en indexeerbare pagina's op en cachet de output per site. Contentwijzigingen legen de sitemap-cache via het bestaande content change event. Browsers krijgen via `/sitemap.xsl` een leesbare tabelweergave, terwijl de sitemap XML-compatible blijft voor zoekmachines.

## Admin

Filament resources zijn aanwezig voor sites, pages, sections, blocks, media assets, redirects en tracking visitors. De eerste versie bevat Markdown editor, locale/status/template filters, SEO velden en relationele selectors. Page tree en drag/drop sorting zijn voorbereid via parent/sort_order en kunnen als custom Filament page verder worden verfijnd.

## Tracking

De kern van de oude GRAV `tracking-writer` plugin is overgezet naar een relationele Laravel-implementatie, in dezelfde richting als Blijwin OS:

- consent endpoint: `/tracking-consent`
- collect endpoint: `/tracking-collect`
- visitor/session identifiers met cookie-opslag na analytics consent en server-session fallback
- relationele opslag voor visitors, sessions, page visits, contact attempts en consent decisions
- pageview, heartbeat, page_end, mail/tel-clicks en form_submit tracking
- verborgen formuliercontext via `data[blijwin_t_info]`
- Filament overzicht voor tracking visitors

Externe scripts, pixels en GRAV-import van historische trackingdata zijn bewust nog niet meegenomen in deze eerste CMS-versie.

## Veelgestelde Vragen

De kern van de oude GRAV `veelgestelde-vragen` plugin is relationeel overgezet:

- centrale FAQ categorieen en vragen in Filament
- FAQ-secties kunnen categorieen hergebruiken
- Markdown-antwoorden met veilige rendering en caching
- `{trefwoord}` en `{keyword}` tokenvervanging per sectie
- zoeken, categoriefilters, initial limit, CTA en accordion-gedrag
- FAQPage JSON-LD output voor SEO
- importservice voor latere extractie uit JSON-LD en Markdown headings

De volledige GRAV-import UI is bewust nog niet gebouwd; de services bereiden die stap wel voor.

## Downloads

De kern van de oude GRAV `downloads` plugin is relationeel overgezet:

- centrale downloadcategorieen, downloaditems en formats in Filament
- downloads-secties kunnen categorieen hergebruiken
- Markdown-intro's en previewteksten met veilige rendering en caching
- previewafbeeldingen via `MediaAsset`
- primaire downloadknop met alternatieve formats
- directe public-disk downloads via `/downloads/file/{category}/{item}/{format}`
- beveiligde downloads met voornaam/e-mail modal, honeypot, minimale invultijd, rate limiting, tijdelijke tokenlinks en mail logs

De bestaande GRAV YAML-import is bewust nog niet gebouwd. De relationele structuur sluit wel aan op GRAV downloads-categorieen, preview images, formats en secure delivery.

## Blijwin OS Koppeling

Het CMS heeft een dedicated `BlijwinosApiClient` voor betrouwbare uitwisseling met Blijwin OS:

- lezen van catalogusdata via `/api/blijwinboekingen/catalogus`
- lezen van prijslijstdata via `/api/blijwinboekingen/prijslijsten`
- schrijven van publieke aanvragen via `/api/blijwinboekingen/aanvragen`
- HMAC-signing voor write requests met `X-Blijwin-Timestamp`, `X-Blijwin-Request-Id` en `X-Blijwin-Signature`
- timeouts, retries, read-cache en relationele request logging in `blijwinos_api_logs`

Functionele defaults staan in `config/settings.php` onder `blijwinos`. De write secret moet per omgeving veilig worden ingevuld voordat schrijven naar Blijwin OS werkt.

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
php artisan migrate
npm install
npm run build
php artisan serve
```

Setup: `/setup` zolang er nog geen beheerder bestaat.

Admin: `/admin`

Webroot: `public_html/`, gelijk aan Blijwin OS en geschikt voor DirectAdmin hosting. Deploy de repository zelf naar de domein-root, bijvoorbeeld `/home/u26717p132995/domains/cms.vieranders.nl`, niet naar `/home/u26717p132995/domains/cms.vieranders.nl/public_html`. Alleen `public_html` is publiek; Laravel moet naast `public_html` blijven staan zodat `public_html/index.php` `../vendor/autoload.php` en `../bootstrap/app.php` kan laden.

De DeployHQ-configuratie kopieert repository-bestanden en draait nu geen Vite build hook. Daarom staat `public_html/build` in Git en moet `npm run build` worden gedraaid en mee gecommit bij frontendwijzigingen.

DeployHQ draait voor zero-downtime releases het SSH command `cd %path% && ./scripts/deploy/post_deploy.sh` voordat de release actief wordt. Dit script controleert PHP 8.4+, installeert ontbrekende productie-dependencies met `composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction` via dezelfde PHP-binary, voert database-migraties uit met `php artisan migrate --force`, maakt de publieke storage-link aan en bouwt Laravel caches opnieuw op. Zet "Stop the deployment if the command fails" aan, zodat een mislukte migratie de release niet actief maakt.

Selecteer op DirectAdmin PHP 8.4 of nieuwer voor `cms.vieranders.nl` en zorg dat dezelfde versie beschikbaar is voor het DeployHQ SSH command. Als de server meerdere PHP-binaries heeft, kan de hook met `PHP_BIN=/pad/naar/php84 ./scripts/deploy/post_deploy.sh` worden aangeroepen. Als Composer niet als `composer` beschikbaar is, zet dan `COMPOSER_BIN=/pad/naar/composer`. Een Apache/LSAPI log met `include_path='.:/opt/alt/php83/...` betekent dat de site nog op PHP 8.3 draait en niet aan de projectvereiste voldoet.

Demo seed login na `php artisan db:seed`:

- Email: `admin@example.com`
- Password: `password`

## Tests

```bash
composer test:dev
composer test:fast
composer test:full
```

De volledige test-set staat in `docs/test-set.md`. Gebruik gerichte profielen zoals `composer test:downloads`, `composer test:faq`, `composer test:tracking` en `composer test:content` tijdens featurewerk.
Voor de Blijwin OS-koppeling is er `composer test:blijwinos`.

## Changelog

Meaningful changes are tracked in `CHANGELOG.md`. Update it together with README.md when behavior, setup, architecture, database structure, admin workflows, frontend rendering or deployment changes.
