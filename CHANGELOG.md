# Changelog

All notable changes to Blijwin Content CMS are documented in this file.

This project uses an `Unreleased` section so upcoming work is visible before a tagged release exists.

## Unreleased

### Added

- Added a cached `/sitemap.xml` endpoint that lists published, routable and indexable pages per site.
- Added browser-friendly XSL styling for `/sitemap.xml`.
- Added a DeployHQ post-deploy hook script that runs forced Laravel migrations, refreshes storage links and rebuilds framework caches before a zero-downtime release becomes active.
- Added relational downloads functionality with categories, items, formats, secure e-mail token delivery, frontend rendering and Filament resources.
- Added tracked Vite build output under `public_html/build` so DeployHQ deployments include current frontend assets.
- Added Blijwin OS-style Composer test profiles and documented the project test set.
- Added a tested Blijwin OS API client for cached reads, signed writes and request logging.
- Added project documentation rules requiring README and changelog updates for meaningful changes.

### Changed

- Changed the DeployHQ post-deploy hook to verify PHP 8.4+ and install missing production Composer dependencies before running Laravel deployment commands.

### Fixed

- Fixed public page, sitemap and hreflang rendering so inactive sites, non-routable pages and future-scheduled translations are not exposed.
