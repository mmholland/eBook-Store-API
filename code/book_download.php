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
	
	// store supplied search terms via GET method 
	$book_id = $_GET['book_id'];
	$username = $_GET['user'];
	
	// checks if user is logged in
	if (isset($_SESSION["user_login"]))
	{
		// checks if user is logged in as the submitted user -- to ensure they've purchased it
		if ($username == $_SESSION["user_login"])
		{
			// checks if user has purchased this book
			$query = $dbh->prepare("SELECT COUNT(*) FROM Purchases WHERE username = '".$username."' AND book_id = '".$book_id."'");
			$query->execute();
			$count = $query->fetchColumn();
			if ($count == 1)
			{
				// checks number of downloads for this user is < 100
				$query = $dbh->prepare("SELECT downloads FROM Purchases WHERE username = '".$username."' AND book_id = '".$book_id."'");
				$query->execute();
				$downloadcount = $query->fetchColumn();
				if ($downloadcount < 100)
				{
					// obtains base64 data from database
					$query = $dbh->query("SELECT content FROM Books WHERE book_id = '".$book_id."'");
					$query->execute();
					$data = $query->fetchColumn();
					// obtains book title
					$query = $dbh->query("SELECT title FROM Books WHERE book_id ='".$book_id."'");
					$query->execute();
					$title = $query->fetchColumn();
					// decode epub contents
					$data = base64_decode($data);
					// create temp file and write epub contents to it
					$temp = tempnam("/tmp", "tmp");
					$handle = fopen($temp, "w");
					fwrite($handle, $data);
					// declare header and filename, and send .epub file to user
					header("Content-Disposition: attachment; filename='".$title.".epub'");
					header("Content-type: application/epub+zip");
					readfile($temp);				
					// increment download count by one
					$dbh->exec("UPDATE Purchases SET downloads = downloads + 1 WHERE username = '".$username."' AND book_id = '".$book_id."'");
				}
				// sends appropriate error messages
				else
				{
					// sets HTTP code 403 as the user has reached their individual download limit for this book
					$message = "error 403 (forbidden) -- sorry, download limit reached.";
					$success = false;
					http_response_code(403);
				}
			}
			else
			{
				$message = "you do not own this book.";
				$success = false;
			}
		}
		else
		{
			$message = "you are not logged in as this user, please purchase the book.";
			$success = false;
		}
	}
	else
	{
		$message = "you are not logged in.";
		$success = false;
	}
	
	header('Content-type: application/json');
	$result = json_encode(array('success' => $success, 'message' => $message));
	echo $result;
	
?>