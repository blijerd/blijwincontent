# AGENTS.md - Blijwin Content CMS

## Purpose

This document is the central instruction file for developers and AI agents working on Blijwin Content CMS.

This repository is a standalone Laravel CMS. It is not Blijwin OS, but it follows the same architecture philosophy so both systems can cooperate later.

The goal is to build a commercial-grade, long-lived CMS that is:

- markdown-first
- multilingual-ready
- GRAV-import-ready
- SEO-first
- fast and cache-friendly
- relational, not JSON-pagebuilder based
- simple where possible and explicit where needed
- deployable to DirectAdmin hosting with `public_html/`

---

## Non-Negotiable Rules

1. Do not place business logic inside controllers, Blade views, Livewire components or Filament resources.
2. Put domain decisions in Actions.
3. Put rendering, SEO, media, markdown and integration-like behavior in Services.
4. Keep content structure relational. Do not introduce a generic JSON pagebuilder.
5. Use Markdown fields for rich editorial content.
6. Public URLs and public references must not expose incremental database IDs.
7. Use `public_id` UUIDs where content can be referenced outside the database.
8. Use typed enums for controlled states and types.
9. Use Policies for authorization.
10. Add activity logging for important content and administrative changes.
11. Use settings/config for functional defaults; keep `.env` limited to runtime secrets and infrastructure such as database credentials.
12. Keep the backend as the source of truth.
13. Update README.md for significant architecture, setup or behavior changes.
14. Update CHANGELOG.md for every user-facing, admin-facing, architecture, database, deployment or integration-relevant change.
15. Add tests for migrations, rendering, SEO behavior, content services and risky admin workflows.
16. Do not copy Blijwin OS modules one-to-one. Reuse its architecture philosophy, not its business domain implementation.

---

## Language Rules

### Development Language

All code, architecture, developer documentation, comments, database names, classes, methods, Actions and Services must be written in English.

### User-Facing Language

The CMS interface and public website content should be Dutch by default unless a feature is explicitly locale-specific.

Admin labels, validation messages, frontend text and email text should be clear Dutch.

---

## Tech Stack

- PHP 8.4+
- Laravel 12
- Filament 4
- Livewire
- Blade
- Tailwind CSS
- Alpine.js
- MySQL
- Vite

Do not introduce React, Vue, Next.js, microservices or distributed event infrastructure unless explicitly requested and architecturally justified.

---

## Hosting And Deployment

The application must run on DirectAdmin-style hosting with:

- `public_html/` as webroot
- DeployHQ deployments over SSH
- no dependency on manual SSH actions in production
- no dependency on permanent queue workers

Laravel must use `public_html` as public path. Vite must build assets into `public_html/build`.

The current DeployHQ project transfers repository files and does not run a Vite build hook. Keep `public_html/build` committed after frontend changes unless deployment automation is changed to build assets server-side.

Production deployment should be automated through DeployHQ. If migrations or build steps are needed, they belong in deployment hooks or documented release automation, not in undocumented manual steps.

---

## Project Structure

Target structure:

```txt
/
├── app/
│   ├── Actions/
│   ├── Enums/
│   ├── Events/
│   ├── Filament/
│   ├── Http/
│   ├── Listeners/
│   ├── Models/
│   ├── Policies/
│   ├── Services/
│   ├── Support/
│   └── ViewModels/
├── bootstrap/
├── config/
├── database/
├── public_html/
├── resources/
├── routes/
├── storage/
├── tests/
├── README.md
└── AGENTS.md
```

Keep the modular monolith shape. Add module-like folders only when they reduce real coupling or match an established local pattern.

---

## Content Architecture

The CMS is relational and markdown-first.

Core content models:

- `Site`
- `Page`
- `Section`
- `Block`
- `MediaAsset`
- `Redirect`
- `FaqCategory`
- `FaqItem`
- `DownloadCategory`
- `DownloadItem`
- `DownloadFormat`

Pages contain sections. Sections contain blocks or controlled relational content such as FAQ and download categories. Rich text belongs in Markdown fields.

Do not store page layouts, sections, blocks or translations as arbitrary JSON blobs. JSON casts are acceptable only for bounded settings where relational modeling adds no value.

---

## GRAV Compatibility

The CMS must remain ready for a later GRAV importer.

Design content so GRAV concepts can map cleanly to:

- page folders -> `pages`
- frontmatter -> template, status, SEO and metadata fields
- modular pages -> `sections`
- modular content -> `blocks`
- Markdown body -> Markdown fields
- page media -> `media_assets`
- redirects -> `redirects`
- translated pages -> shared `translation_group_id`
- GRAV FAQ data -> `faq_categories` and `faq_items`
- GRAV downloads catalog data -> `download_categories`, `download_items` and `download_formats`

Do not build importer behavior unless explicitly requested. Keep importer preparation in services and relational structures.

---

## Multilingual Rules

The CMS must be multilingual-ready.

Use:

- explicit `locale` columns on translatable content
- `translation_group_id` for translated pages
- locale-specific slugs
- locale-specific SEO metadata
- locale-specific redirects
- hreflang output
- canonical handling per language

Do not use JSON translation blobs as the primary translation model.

---

## Templates And Sections

Templates are typed and controlled through enums.

Current template types:

- `default`
- `landingpage`
- `product`
- `blog`
- `downloads`

Current section types:

- `hero`
- `two_columns`
- `triplets`
- `reviews`
- `spotlight_panel`
- `rich_text`
- `cta`
- `faq`
- `downloads`
- `video`

Each section type should have:

- a Blade partial
- controlled rendering behavior
- validation rules where applicable
- Markdown rendering where content requires rich text

Do not let editors create arbitrary layout schemas through JSON.

---

## Markdown Rules

Markdown is the primary editorial content format.

Use Markdown for:

- page excerpts
- section intros
- block body content
- blog content
- product descriptions
- FAQ answers
- rich editorial text

Markdown rendering must go through `MarkdownRenderService`.

Markdown rendering must:

- sanitize unsafe HTML
- disallow unsafe links
- support heading structure
- support caching
- remain suitable for SEO
- be extendable for future internal links and media handling

Do not render Markdown directly in Blade views or controllers.

---

## SEO Rules

SEO is a first-class concern.

Support and preserve:

- meta title
- meta description
- canonical URLs
- Open Graph metadata
- XML sitemap output
- hreflang tags
- robots index/follow fields
- semantic HTML
- FAQPage JSON-LD where relevant

SEO decisions belong in SEO services or template configuration, not in views.

---

## Performance Rules

Performance must be designed into content rendering.

Use:

- eager loading for page rendering
- cached Markdown rendering
- page render caching where appropriate
- explicit cache invalidation on content changes
- lazy loading for images
- optimized public assets

Avoid repeated queries in Blade views. Views should render prepared view data.

---

## Filament Rules

Filament is an admin interface, not a business logic layer.

Filament resources may define:

- forms
- tables
- navigation
- relation managers
- simple UI actions that delegate to Actions

Filament resources must not contain:

- business rules
- status transition logic
- complex rendering logic
- import logic
- SEO generation logic

Markdown editor fields should use the project Markdown editor support component where possible.

---

## Controllers, Livewire And Blade

Controllers must stay thin:

- resolve request context
- call Actions or Services
- return responses

Livewire components must coordinate UI state only.

Blade views must not contain business decisions. They should render viewmodels or prepared arrays.

Public frontend rendering should be SSR-first through Blade and viewmodels/presenters.

---

## Actions And Services

Use Actions for domain operations:

- publishing content
- recalculating paths
- changing statuses
- importing prepared data
- mutating content structures

Use Services for technical/domain support:

- Markdown rendering
- page rendering
- SEO metadata
- sitemap generation
- media handling
- FAQ building
- tracking collection

Actions should be small, typed and testable.

---

## Database Rules

Use MySQL as the target database.

Migrations must be:

- reversible where reasonable
- MySQL-safe
- explicit about indexes
- explicit about foreign keys
- safe for production data

Use factories for new models.

Do not change application behavior solely to support SQLite tests. SQLite may be useful for fast local tests, but MySQL remains the production target.

---

## Settings Rules

Functional defaults should live in config/settings or dedicated config files.

Use `.env` only for:

- app key and runtime environment
- database connection
- secrets
- infrastructure endpoints
- credentials

Do not add ordinary product settings to `.env`.

---

## Testing Rules

Add tests for meaningful behavior.

Minimum expectations:

- Services have unit tests where logic is non-trivial.
- Rendering behavior has feature tests.
- Database-backed changes have migration/model/factory coverage.
- SEO output should be asserted when changed.
- Admin-only logic should be tested through Actions or Services.

Before finishing a code change, run the relevant tests. For broad changes, run:

```bash
/opt/homebrew/opt/php@8.5/bin/php artisan test
npm run build
```

The default system `php` may be older than the project requirement. Use PHP 8.4+.

---

## Documentation And Changelog Rules

README.md and CHANGELOG.md are required project surfaces, not optional notes.

Update README.md when a change affects:

- setup or installation
- deployment
- public webroot behavior
- architecture
- content model
- admin workflow
- public frontend behavior
- configuration or settings
- integrations or external data sources

Update CHANGELOG.md for every meaningful change that should be visible to a future developer, operator or product owner. This includes:

- new features
- changed behavior
- database migrations
- deployment changes
- admin changes
- public frontend changes
- bug fixes
- security or performance changes
- removed or deprecated behavior

Use the format:

```md
## Unreleased

### Added
- ...

### Changed
- ...

### Fixed
- ...
```

Keep entries short, factual and user-impact focused. Do not bury important release notes in commit messages only.

Before committing, agents must check whether README.md and CHANGELOG.md need updates. If they are not updated for a meaningful change, the work is not done.

---

## Frontend Rules

The public website should inherit the Blijwin visual direction without copying Blijwin OS one-to-one.

Frontend UI should be:

- SSR-first
- responsive
- accessible
- fast
- content-focused
- semantically structured

Use Blade, Tailwind CSS and Alpine.js. Avoid SPA architecture.

Do not put explanatory implementation text in the UI. Build the actual usable experience.

---

## Activity Logging

Important changes should be observable.

Use events/listeners and activity logs for:

- publishing changes
- content structure changes
- redirects
- media changes
- tracking-relevant consent changes
- significant admin operations

Do not silently mutate important production content.

---

## Security Rules

Security-sensitive rules:

- sanitize Markdown output
- validate uploaded media
- do not expose internal IDs in public URLs
- use Policies for admin models
- keep secrets out of Git
- do not add external API integrations without explicit request
- do not trust imported GRAV/frontmatter data without validation

---

## Forbidden Directions

Do not introduce:

- generic JSON pagebuilders
- untyped template strings where enums exist
- business logic in Filament resources
- direct Markdown rendering in views
- React/Vue/Next.js frontend architecture
- microservices
- permanent production queue worker dependencies
- manual production shell requirements
- external API integrations unless explicitly requested

---

## Definition Of Done

A change is done when:

- it follows this document
- the architecture remains relational and markdown-first
- business logic is in Actions/Services
- policies exist where required
- tests pass
- Vite build passes when frontend assets changed
- README.md is updated when setup, architecture, admin, frontend or deployment behavior changed
- CHANGELOG.md is updated for every meaningful change
- migrations are production-conscious
- deployment implications are clear
- the work is committed and pushed when requested
