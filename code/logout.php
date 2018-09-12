<?php
	// start user session
	session_start();
	
	// checks to see if user is logged in by checking if their user login session is set
	// if set, it destroys all session cookies
	if (isset($_SESSION["user_login"]))
	{
		session_destroy();
		$message = "logged out successfully.";
		$success = true;
	}
	// if not set, it will return a message accordingly
	else
	{
		$message = "you are not logged in.";
		$success = false;
	}
	
	$result = json_encode(array('success' => $success, 'message' => $message));
	echo $result;
?>