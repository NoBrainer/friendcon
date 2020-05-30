# Custom Code For FriendCon

## Development Workflow
This repository is to version control all custom code on our server.
- Make changes here then copy changed files to the server to test/deploy. You can do this with security copy (SCP).
(On Windows, WinSCP is a decent option.)
- Use separate feature branches. Interactively rebase them onto themselves to clean-up commits before merging onto
`origin/master`.
- Merge to `origin/master` when things are good.

## File Organization
- The `root` directory represents the root directory on the server. PHP files directly under `root` are for short URLs.
For example `root/game.php` gives us the https://friendcon.com/game URL.
- The `fun/api` directory is for the REST API.
- The `fun/classes` directory is for the autoloaded classes.
- The `fun/js` directory is for custom JavaScript files.
- The `fun/static` directory is for assets and third-party libraries.
- The `fun/wp-content/mu-plugins` directory is for WordPress must use plugins.
- Every other directory in `fun` is part of the UI. Each of those should have its own CSS file for its specific styling
and its own JS file for its specific utility functions. They should also have a `head.php` to prevent repeating HTML 
headers and `nav.php` to prevent repeating navigation HTML.
