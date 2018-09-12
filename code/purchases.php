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
	$test = true;
		
	// checks if user is logged in
	if (isset($_SESSION["user_login"]))
	{
		// checks if $username is set
		if (isset($username))
		{
			// checks if supplied username is currently logged in
			if ($username == $_SESSION["user_login"])
			{
				// checks if supplied username exists
				$query = $dbh->prepare("SELECT COUNT(*) FROM users WHERE username = '".$username."'");
				$query->execute();
				$count = $query->fetchColumn();
				if ($count == 1)
				{
					if (empty($book_id))
					{
						// retrieves purchase information from database and returns it
						$data = array();
						$query = $dbh->prepare("SELECT * FROM Purchases WHERE username = '".$username."'");
						$query->execute();
						$result = $query->fetchAll(PDO::FETCH_ASSOC);
					
						foreach ($result as $row)
						{
							$data[] = '{"book_id":'.$row['book_id'].',"username":"'.$row['username'].'","purchasedate":"'.$row['purchasedate'].'"}';
						}
					}
					else
					{
						// retrieves purchase information from database and returns it
						$data = array();
						$query = $dbh->prepare("SELECT * FROM Purchases WHERE username = '".$username."' AND book_id = '".$book_id."'");
						$query->execute();
						$result = $query->fetchAll(PDO::FETCH_ASSOC);
					
						foreach ($result as $row)
						{
							$data[] = '{"book_id":'.$row['book_id'].',"username":"'.$row['username'].'","purchasedate":"'.$row['purchasedate'].'"}';
						}
					}
				}				
				else
				{
					$message = "no user with this name.";
					$success = false;
				}
			}
			// checks if user account is an admin
			else if ($_SESSION["admin"] == true)
			{
				if (empty($book_id) && isset($username))
				{
					// checks account exists in database
					$query = $dbh->prepare("SELECT COUNT(*) FROM users WHERE username = '".$username."'");
					$query->execute();
					$count = $query->fetchColumn();
					
					if ($count == 1)
					{
						// retrieves all purchases for this user and returns in json
						$data = array();
						$query = $dbh->prepare("SELECT * FROM Purchases WHERE username = '".$username."'");
						$query->execute();
						$result = $query->fetchAll(PDO::FETCH_ASSOC);
					
						foreach ($result as $row)
						{
							$data[] = '{"book_id":'.$row['book_id'].',"username":"'.$row['username'].'","purchasedate":"'.$row['purchasedate'].'"}';
						}
					}
					else
					{
						$message = "no results";
						$success = false;
					}
				}
				else if (empty($username) && isset($book_id))
				{
					// checks the specified book ID exists
					$query = $dbh->prepare("SELECT COUNT(*) FROM Books WHERE book_id = '".$book_id."'");
					$query->execute();
					$count = $query->fetchColumn();
					
					if ($count == 1)
					{					
						// retrieves all purchases for specified book and returns in json
						$data = array();
						$query = $dbh->prepare("SELECT * FROM Purchases WHERE book_id = '".$book_id."'");
						$query->execute();
						$result = $query->fetchAll(PDO::FETCH_ASSOC);
					
						foreach ($result as $row)
						{
							$data[] = '{"book_id":'.$row['book_id'].',"username":"'.$row['username'].'","purchasedate":"'.$row['purchasedate'].'"}';
						}
					}
					else
					{
						$message = "no results";
						$success = false;
					}
				}
				else if ((isset($username)) && (isset($book_id)))
				{
					$userstmt = $dbh->prepare("SELECT COUNT(*) FROM users WHERE username = '".$username."'");
					$userstmt->execute();
					$usercount = $userstmt->fetchColumn();
					
					if ($usercount == 1)
					{
						$bookstmt = $dbh->prepare("SELECT COUNT(*) FROM Books WHERE book_id = '".$book_id."'");
						$bookstmt->execute();
						$bookcount = $bookstmt->fetchColumn();
					
						if ($bookcount == 1)
						{
							$data = array();
							$query = $dbh->prepare("SELECT * FROM Purchases WHERE book_id = '".$book_id."' AND username = '".$username."'");
							$query->execute();
							$result = $query->fetchAll(PDO::FETCH_ASSOC);
					
							foreach ($result as $row)
							{
								$data[] = '{"book_id":'.$row['book_id'].',"username":"'.$row['username'].'","purchasedate":"'.$row['purchasedate'].'"}';
							}
						}
						else
						{
							$message = "no book exists with this id.";
							$success = false;
						}
					}
					else
					{
						$message = "given username does not exist.";
						$success = false;
					}
				}
				else
				{
					$message = "no such user exists.";
					$success = false;
				}
			}
			else
			{
				$message = "you are not logged in as this user";
				$success = false;
			}
		}
		else
		{
			$message = "username must not be empty";
			$success = false;
		}
	}
	else
	{
		$message = "you are not logged in.";
		$success = false;
	}
			
			
			
			
			
	
	header('Content-type: application/json');
	if (isset($message))
	{
		$result = json_encode(array('success' => $success, 'message' => $message));
		print_r($result);
	}
	else
	{
		print_r($data);
	}
	
?>