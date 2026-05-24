# Changelog

All notable changes to Blijwin Content CMS are documented in this file.

This project uses an `Unreleased` section so upcoming work is visible before a tagged release exists.

## Unreleased

### Added

- Added a DeployHQ post-deploy hook script that runs forced Laravel migrations, refreshes storage links and rebuilds framework caches before a zero-downtime release becomes active.
- Added relational downloads functionality with categories, items, formats, secure e-mail token delivery, frontend rendering and Filament resources.
- Added tracked Vite build output under `public_html/build` so DeployHQ deployments include current frontend assets.
- Added Blijwin OS-style Composer test profiles and documented the project test set.
- Added a tested Blijwin OS API client for cached reads, signed writes and request logging.
- Added project documentation rules requiring README and changelog updates for meaningful changes.
