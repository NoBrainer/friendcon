<?php
session_start();
$userSession = $_SESSION['userSession'];

include('../internal/constants.php');
include('../internal/functions.php');
include('../internal/initDB.php');

// Setup the content-type and response template
header(CONTENT['JSON']);
$response = [];

$name = trim($_POST['name']);
$phone = preg_replace('/\D+/', '', trim($_POST['phone']));
$favoriteBooze = trim($_POST['favoriteBooze']);
$favoriteNerdism = trim($_POST['favoriteNerdism']);
$favoriteAnimal = trim($_POST['favoriteAnimal']);
$email = trim($_POST['email']);
$password = md5(trim($_POST['password']));
$contactName = trim($_POST['emergencyCN']);
$contactPhone = preg_replace('/\D+/', '', trim($_POST['emergencyCNP']));

// Check for missing fields
$missingFields = [];
if (empty($name)) $missingFields[] = "name";
if (empty($phone)) $missingFields[] = "phone";
if (empty($email)) $missingFields[] = "email";
if (empty($password)) $missingFields[] = "password";
if (empty($favoriteBooze)) $missingFields[] = "favoriteBooze";
if (empty($favoriteNerdism)) $missingFields[] = "favoriteNerdism";
if (empty($favoriteAnimal)) $missingFields[] = "favoriteAnimal";
if (empty($contactName)) $missingFields[] = "emergencyCN";
if (empty($contactPhone)) $missingFields[] = "emergencyCNP";
if (count($missingFields) > 0) {
	$fieldStr = join(", ", $missingFields);
	$response['error'] = "Missing fields for user signup [$fieldStr]";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

// Make sure the email address isn't already registered
$emailQuery = "SELECT email FROM users WHERE email = ?";
$emailResult = executeSqlForResult($mysqli, $emailQuery, 's', $email);
if (hasRows($emailResult)) {
	$response['error'] = "This email is already registered.";
	http_response_code(HTTP['BAD_REQUEST']);
	echo json_encode($response);
	return;
}

// Try to register the user
$query = "INSERT INTO users(`name`, `email`, `phone`, `password`, `favoriteAnimal`, `favoriteBooze`," .
		" `favoriteNerdism`, `emergencyCN`, `emergencyCNP`, `agreeToTerms`)" .
		" VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
$affectedRows = executeSqlForAffectedRows($mysqli, $query, 'sssssssss', $name, $email, $phone,
		$password, $favoriteAnimal, $favoriteBooze, $favoriteNerdism, $contactName, $contactPhone);

$shouldSendEmailToAdmin = true;
if ($affectedRows === 1) {
	$shouldSendEmailToUser = true;
	http_response_code(HTTP['OK']);
} else {
	$shouldSendEmailToUser = false;
	$response['error'] = "Unexpected failure.";
	http_response_code(HTTP['INTERNAL_SERVER_ERROR']);
	echo json_encode($response);
}

// Email settings
$headers = "From: admin@friendcon.com";

if ($shouldSendEmailToAdmin) {
	// Send ourselves an email on new user registration (on success or failure)
	$to = "admin@friendcon.com";
	$subject = "New Friend Registered! Welcome, {$name}!";
	$body = "Name: {$name}\nEmail: {$email}";
	mail($to, $subject, $body, $headers);
}

if ($shouldSendEmailToUser) {
	// Send an email to the user saying it was successful
	$to = $email;
	$subject = "Your FriendCon Account Has Been Created!";
	$body = "Hey there, {$name}!\n\nWe're so happy you decided to create an account and hopefully join us at " .
			"the next FriendCon! We look forward to seeing you there!\n\nAll the best from your friends at " .
			"FriendCon!\n";
	mail($to, $subject, $body, $headers);
}
