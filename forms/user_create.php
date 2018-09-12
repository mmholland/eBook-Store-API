<?php
	session_start();
	require_once('config.php');
	$db_host = constant("DB_HOST");
	$db_name = constant("DB_DATABASE");
	$db_pass = constant("DB_PASSWORD");
	
	$dbh = new PDO(
		'mysql:host='.$db_host.'; dbname='.$db_name.'',
		$db_name,
		$db_pass
	);
	
	$username = $_POST['username'];
	$password = $_POST['password'];
	$hashedpass = SHA1($password);
	$email = $_POST['email'];
	
	// query the database to check if chosen username is available
	$query = $dbh->prepare("SELECT COUNT(*) FROM users WHERE username = '".$username."'");
	$query->execute();
	$count = $query->fetchColumn();
	
	if ($count == 0)
	{
		if ($username == "")
		{
			$success = false;
			$message = "error, username is mandatory.";
		}
		else if ($password == "")
		{
			$success = false;
			$message = "error, password is mandatory.";
		}
		else if ($email == "")
		{
			$success = false;
			$message = "error, email is mandatory.";
		}
		else
		{
			$dbh->exec("INSERT INTO users VALUES('".$username."', '".$hashedpass."', '".$email."','user')");
			$message = "success, account created";
			$success = true;
		}
	}
	else 
	{
		$message = "sorry, username is unavailable";
		$success = false;
	}
	
	$result = json_encode(array('success' => $success, 'message' => $message));
	header("Content-Type: application/json");
	echo $result;
?>