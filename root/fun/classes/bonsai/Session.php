<?php

namespace fun\classes\bonsai;

/**
 * Session handling class
 *
 * Allows for starting a session with a given timeout, regenerating the session id (e.g. after authentication), and removing the session.
 * This attempts to preserve the session on an unstable network connection (which may occur on mobile phones) when starting the session and when regenerating the session id.
 *
 * @author Alan Davis <alan@bonsaicode.dev>
 * @copyright 2018 BonsaiCode, LLC
 *
 */
class Session {

	const TIMEOUT = 3600; # seconds, 1 hour = 60 seconds x 60 minutes
	#const TIMEOUT = 120; # 2 minutes for testing

	/**
	 * startSession - private method to be called by start() only.
	 *
	 * This always sets the session timeout before starting session.
	 *
	 * @param int $timeout the session timeout in seconds
	 *
	 * @return void
	 */
	private static function startSession(int $timeout): void {

		$currentTime = time();

		# Increase session timeout.  This must be set before starting the session.

		ini_set('session.gc_maxlifetime', $timeout); # server should keep session data for AT LEAST $timeout seconds

		# NOTE: Do not call session_set_cookie_params( $timeout ) because it does not extend the session each time it is called, it sets a hard limit.

		# Extend the cookie timeout for the user's browser.  See http://php.net/manual/en/function.session-set-cookie-params.php
		$options = [
				'expires'  => $currentTime + $timeout,
				'samesite' => 'Strict'
		];
		setcookie(session_name(), session_id(), $options);

		session_start(); # Start session and extend server timeout by amount set in session.gc_maxlifetime above.

		# If we have passed the session timeout, then remove the session.
		# This will force the session timeout at the timeout we specify, instead of waiting for the GC to timeout sometime after session.gc_maxlifetime.

		#echo "timeout_idle[".date( 'Y-m-d H:i:s', $_SESSION['timeout_idle'] )."] < time[".date( 'Y-m-d H:i:s', $currentTime )."] = [".( $_SESSION['timeout_idle'] < $currentTime )."] seconds remaining until timeout[".( $_SESSION['timeout_idle'] - $currentTime )."]\n";
		if (isset($_SESSION['timeout_idle']) && $_SESSION['timeout_idle'] < $currentTime) {  # 2:05pm < 2:01pm then not expired, 2:05pm < 2:10pm then expired

			self::remove();

		} else {

			# Extend the timeout that we will check later (above).
			$_SESSION['timeout_idle'] = $currentTime + $timeout; # 2pm + 5mins = 2:05pm
			#echo 'new seconds remaining until timeout['.( $_SESSION['timeout_idle'] - $currentTime )."]\n";
		}
	}

	/**
	 * start - start the session
	 *
	 * Start the session and account for unstable network connection when/if regenerating session id.  If you need to regenerate a session id, use the regenerate_id() method that follows.
	 *
	 * @param int $timeout the session timeout in seconds
	 *
	 * @return void
	 */
	public static function start(int $timeout = self::TIMEOUT): void {

		self::startSession($timeout);

		# This logic works with regenerate_id() below, to avoid lost sessions on an unstable (mobile) network.

		if (isset($_SESSION['_destroyed'])) {

			# If session was destroyed more than 5 minutes ago...
			if ($_SESSION['_destroyed'] < time() - 300) {
				# this should not usually happen.  This could be an attack or unstable network.
				# Completely remove the session.
				self::remove();

				# If we have a regenerated session...
			} else if (isset($_SESSION['_new_session_id'])) {

				# Keep current session for the next session
				$currentSession = $_SESSION;

				# Session has not expired yet.  The cookie could have been lost by an unstable network connection, e.g. mobile phone.
				# Try again to set proper session ID cookie.

				session_write_close();
				session_id($_SESSION['_new_session_id']);

				# New session ID should exist
				self::startSession($timeout);

				$_SESSION = $currentSession;

				unset($_SESSION['_destroyed']);
				unset($_SESSION['_new_session_id']);
			}
		}
	}

	/**
	 * regenerateId - prevent session fixation by regenerating session id.  Avoid lost session by unstable network which may happen with some mobile users.
	 *
	 * From http://php.net/manual/en/function.session-regenerate-id.php
	 * Note: DO NOT CALL session_regenerate_id() or this regenerateId() in a file that is referenced via JavaScript ajax immediately before a windows.location.href (e.g. login.js),
	 *       otherwise session is lost in IE browsers (8&9).  Instead, regenerate the session in the target file of the windows.location.href
	 *
	 * @param int $timeout the session timeout in seconds
	 *
	 * @return void
	 */
	static function regenerateId(int $timeout = self::TIMEOUT): void {

		# Keep current session for the next session.
		$currentSession = $_SESSION;

		# New session ID is required to set session ID when it gets unset due to unstable network.
		$new_session_id = session_create_id();
		$_SESSION['_new_session_id'] = $new_session_id;
		# Set _destroyed timestamp
		$_SESSION['_destroyed'] = time();

		# Write and close current session;
		session_write_close();

		# Start session with new session ID
		session_id($new_session_id);
		#ini_set( 'session.use_strict_mode', 0 );
		self::startSession($timeout);
		#ini_set( 'session.use_strict_mode', 1 );

		$_SESSION = $currentSession;

		# New session does not need these
		unset($_SESSION['_destroyed']);
		unset($_SESSION['_new_session_id']);
	}

	/**
	 *
	 * remove - completely remove the session
	 *
	 * @return void
	 */
	static function remove(): void {

		# If session has not already started then...
		if (session_status() == PHP_SESSION_NONE) {
			session_start(); # must get the session to destroy
		}

		# Unset all $_SESSION variables
		$_SESSION = [];

		# Delete the session cookie, because session_destroy() does not do this
		if (ini_get('session.use_cookies')) {
			$params = session_get_cookie_params();
			unset($params['lifetime']);
			$params['expires'] = time() - 42000;
			setcookie(session_name(), '', $params);
		}

		# Destroy session data in session storage, session_start() must be called prior to this
		session_destroy();

		# Close session in case of iframes
		session_write_close();
	}
}
