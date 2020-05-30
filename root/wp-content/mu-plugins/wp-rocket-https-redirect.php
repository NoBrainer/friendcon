<?php
defined('ABSPATH') or exit;

/**
 * Plugin Name: WP Rocket | Forces http to https redirect.
 * Description: Forces redirect from http to https. Based on https://docs.wp-rocket.me/article/965-combine-https-and-www-redirection-rules
 * Author:      Vincent Incarvite
 * License:     GNU General Public License v3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
add_filter('before_rocket_htaccess_rules', '__fix_wprocket_https_redirect');
function __fix_wprocket_https_redirect($marker) {
	$redirection = '# Redirect http to https' . PHP_EOL;
	$redirection .= 'RewriteCond %{HTTPS} !on' . PHP_EOL;
	$redirection .= 'RewriteCond %{SERVER_PORT} !^443$' . PHP_EOL;
	$redirection .= 'RewriteCond %{HTTP:X-Forwarded-Proto} !https' . PHP_EOL;
	$redirection .= 'RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]' . PHP_EOL . PHP_EOL;
	return $redirection . $marker;
}
