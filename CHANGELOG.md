# CHANGELOG

This changelog references the relevant changes (bug and security fixes) done

## [Unreleased]
- Added: `getPagination` method to `Source` abstract class
- Added: `--parallel` option to `app:crawl` command to crawl multiple pages in parallel
- Added: `app:update` command to update the database with the latest articles
- Added: `$sep` parameter to `DateRange::from` method
- Added: support for mysql database
- Added: export to csv feature
- Removed: `--filename` option from `app:crawl` command


### 1.2.1
- Added: '--page' option to 'app:crawl' command
- Added: '--date' option to 'app:crawl' command
- Added: notification by email when the crawl is finished

### 1.0.0
- Initial release