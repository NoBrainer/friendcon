<?php
/**
 * Get the PayPal API Access Token.
 *
 * @param string $url - PayPal API OAuth URL
 * @param string $clientId - PayPal API Client ID
 * @param string $secret - PayPal API Secret
 * @return string - PayPal API Access Token
 */
function getAccessToken($url, $clientId, $secret) {
	if (!$url || !$clientId || !$secret) {
		return "Error: Must provide url, clientId, & secret";
	}

	// Build the headers, and post data
	$headers = ["Accept: application/json", "Accept-Language: en_US"];
	$postData = "grant_type=client_credentials"; //string or array

	// Setup curl handle
	$ch = curl_init($url);
	curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true, //return something
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => $postData,
			CURLOPT_HTTPHEADER     => $headers,
			CURLOPT_USERPWD        => "$clientId:$secret",
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSLVERSION     => 6,
			CURLOPT_CONNECTTIMEOUT => 15
	]);

	// Execute the curl command and handle errors
	$resp = curl_exec($ch);
	if (curl_errno($ch)) {
		return 'Error: ' . curl_error($ch);
	}
	curl_close($ch);

	// Return the Access Token string
	$json = json_decode($resp, true);
	return $json['access_token'];
}

/**
 * Complete the order via the PayPal API.
 *
 * @param string $urlRoot - PayPal API Order URL
 * @param string $orderId - PayPal API order ID
 * @param string $accessToken - PayPal API Access Token
 * @return boolean|string - true on success, false on failure, message on invalid input
 */
function isOrderComplete($urlRoot, $orderId, $accessToken) {
	if (!$urlRoot || !$orderId || !$accessToken) {
		return "Must provide urlRoot, orderId, & accessToken";
	}

	// Build the URL, headers, and post data
	$url = $urlRoot . $orderId;
	$headers = ["Accept: application/json", "Authorization: Bearer $accessToken"];

	// Setup curl handle
	$ch = curl_init($url);
	curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true, //return something
			CURLOPT_HTTPHEADER     => $headers,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSLVERSION     => 6,
			CURLOPT_CONNECTTIMEOUT => 15
	]);

	// Execute the curl command and handle errors
	$resp = curl_exec($ch);
	if (curl_errno($ch)) {
		return false;
	}
	curl_close($ch);

	$json = json_decode($resp, true);
	$intent = $json['intent'];
	$status = $json['status'];

	if ($intent === 'CAPTURE' && $status === 'COMPLETED') {
		return true;
	} else {
		return false;
	}
}

/**
 * Save the PayPal order to MySQL.
 *
 * @param mysqli $mysqli - MySQL connection
 * @param integer $uid - user id for the order
 * @param integer $conYear - convention year for the order
 * @param string $orderId - PayPal API Order ID (default: null)
 * @return string
 */
function saveOrder($mysqli, $uid, $conYear, $orderId = null) {
	include_once('sql.php');

	// Update registration
	$query = "UPDATE users u SET u.isRegistered = 1 WHERE u.uid = ?";
	$result = executeSqlForResult($mysqli, $query, 'i', $uid);
	if (!$result) {
		return "User registration change failed [DB-P1]";
	}

	// Check if the user is registered
	$query = "SELECT u.isRegistered, u.isPresent FROM users u WHERE u.uid = ?";
	$result = executeSqlForResult($mysqli, $query, 'i', $uid);
	$user = $result->fetch_array();
	$result->free_result();
	$isRegistered = $user['isRegistered'];
	$isPresent = $user['isPresent'];

	if (!$isRegistered) {
		return "User registration change failed [DB-P2]";
	}

	// Count the `registration_stats` rows for this user that are for this year
	$numRows = 0;
	$query = "SELECT * FROM registration_stats s WHERE s.conYear = ? AND s.uid = ?";
	$checkResult = executeSqlForResult($mysqli, $query, 'ii', $conYear, $uid);
	while ($result = $checkResult->fetch_array()) {
		$prevOrderId = $result['orderId'];
		$numRows++;
	}
	$checkResult->free_result();

	if ($isRegistered == 0) {
		if ($prevOrderId == null) {
			// Delete registration stats when users unregister (if there's no orderId)
			$deleteQuery = "DELETE FROM registration_stats WHERE uid = ? AND conYear = ?";
			$deleteResult = executeSqlForResult($mysqli, $deleteQuery, 'ii', $uid, $conYear);
		} else {
			// If there is an orderId, just update
			$updateQuery = "UPDATE registration_stats s" .
					" SET s.isRegistered = ?, s.isPresent = ?, s.modified = CURRENT_TIMESTAMP()" .
					" WHERE s.uid = ? AND s.conYear = ?";
			$updateResult = executeSqlForResult($mysqli, $updateQuery, 'iiii', $isRegistered, $isPresent,
					$uid, $conYear);
		}
	} else if (!$checkResult || $numRows == 0) {
		// Update the registration stats

		// Insert a new row for this year's registration stats for this user
		$insertQuery = "INSERT INTO `registration_stats`(`uid`, `conYear`, `isRegistered`, `isPresent`, `orderId`)" .
				" VALUES (?, ?, ?, ?, ?)";
		$insertResult = executeSqlForResult($mysqli, $insertQuery, 'iiiis', $uid, $conYear, $isRegistered,
				$isPresent, $orderId);
	} else {
		// Update this year's registration stats for this user
		$updateQuery = "UPDATE registration_stats s" .
				" SET s.isRegistered = ?, s.isPresent = ?, s.orderId = ?, s.modified = CURRENT_TIMESTAMP()" .
				" WHERE s.uid = ? AND s.conYear = ?";
		$updateResult = executeSqlForResult($mysqli, $updateQuery, 'iisii', $isRegistered, $isPresent,
				$orderId, $uid, $conYear);
	}
}
