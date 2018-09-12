<?php
	session_start();
	require_once('config.php');
	header("Content-Type: application/json");
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
	$date = date('d/m/y');
	
	// checks that the user has entered all information required
	if ((!empty($username)) || (!empty($book_id)))
	{
		//checks if the user is logged in
		if (isset($_SESSION["user_login"]))
		{
			// queries database to check if the book id exists (checks for 1 as book id and username are both unique)
			$query = $dbh->prepare("SELECT COUNT(*) FROM Books WHERE book_id = '".$book_id."'");
			$query->execute();
			$idcount = $query->fetchColumn();
			if ($idcount == 1)
			{
				// queries database to check that the user exists
				$query = $dbh->prepare("SELECT COUNT(*) FROM users WHERE username = '".$username."'");
				$query->execute();
				$usercount = $query->fetchColumn();
				if ($usercount == 1)
				{
					// queries database to check if user has already purchased the book
					$query = $dbh->prepare("SELECT COUNT(*) FROM Purchases WHERE username = '".$username."' AND book_id = '".$book_id."'");
					$query->execute();
					$purchasecount = $query->fetchColumn();
					if ($purchasecount == 0)
					{
						// checks to see if logged in user is the username in the field -- to ensure the user is buying a book for themselves
						// also checks whether the account is an admin to allow the admin accounts the ability to purchase books for anyone
						if (($_SESSION["user_login"] == $username) || ($_SESSION["admin"] == true))
						{
							// inserts book purchase into the Purchases table
							$dbh->exec("INSERT INTO Purchases (book_id, username, purchasedate, downloads) VALUES('".$book_id."', '".$username."', '".$date."', 0)");
							$message = "book successfully purchased.";
							$success = true;
						}
						else
						{
							$message = "account you are trying to purchase for isn't your own!";
							$success = false;
						}
					}
					// return appropriate error messages
					else
					{
						$message = "user has already purchased this book!";
						$success = false;
					}
				}
				else
				{
					$message = "sorry, invalid username.";
					$success = false;
				}
			}
			else
			{
				$message = "sorry, no book with that id.";
				$success = false;
			}
		}
		else
		{
			$message = "please login first!";
			$success = false;
		}
	}
	else
	{
		$message = "please enter both book ID and username";
		$success = false;
	}
	// return response in json format
	$result = json_encode(array('success' => $success, 'message' => $message));
	echo $result;
?>