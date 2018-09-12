<?php
	// start user session
	session_start();
	
	// load database login details
	require_once('config.php');
	header("Content-Type: application/json");
	$db_host = constant("DB_HOST");
	$db_name = constant("DB_DATABASE");
	$db_pass = constant("DB_PASSWORD");
	
	// connect to database
	$dbh = new PDO(
		'mysql:host='.$db_host.'; dbname='.$db_name.'',
		$db_name,
		$db_pass
	);
	
	// obtain POST data and process it
	$username = $_POST['username'];
	$password = $_POST['password'];
	$hashedpass = SHA1($password);
	
	if (($username == "") || ($password == ""))
	{
		$message = "fields must not be blank";
		$success = false;
		$result = json_encode(array('success' => $success, 'message' => $message));
		echo $result;
		exit;
	}
	
	// perform database query and return number of rows that contain the username and the hashed password.
	$query = $dbh->prepare("SELECT COUNT(*) FROM users WHERE username = '".$username."' AND password = '".$hashedpass."'");
	$query->execute();
	$count = $query->fetchColumn();
	
	// if the password is correct the count will be exactly 1. as a result, sets a user session ID
	if ($count == 1)
	{
		$_SESSION["user_login"] = $username;
		$message = "login successful";
		$success = true;
	}
	else
	{
		$message = "error, username or password not recognised.";
		$success = false;
	}
	
	// query database to determine if the user is an admin
	$query = $dbh->prepare("SELECT COUNT(*) FROM users WHERE username = '".$username."' AND type = 'admin'");
	$query->execute();
	$count = $query->fetchColumn();
	
	// declares whether account is an admin or not
	if ($count == 1)
	{
		$_SESSION["admin"] = true;
	}
	else
	{
		$_SESSION["admin"] = false;
	}
	
	// returns the result in json
	$result = json_encode(array('success' => $success, 'message' => $message));
	echo $result;
?>