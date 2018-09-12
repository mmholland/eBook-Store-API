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
		
	if ($_SESSION["admin"] == true)
	{
		// obtain user input via POST
		$title = $_POST['title'];
		$authors = $_POST['authors'];
		$description = $_POST['description'];
		$price = $_POST['price'];
		
		// validation to check for empty fields and end script if so
		if (($title == "") || ($authors == "") || ($description == "") || ($price == "") || (empty($_FILES['image']['tmp_name'])) || (empty($_FILES['content']['tmp_name'])))
		{
			$message = "fields must not be blank";
			$success = false;
			$result = json_encode(array('success' => $success, 'message' => $message));
			echo $result;
			exit;
		}
		else
		{
			// obtain contents of the image and ebook files, and encode them to prevent data loss while storing
			$image = $_FILES['image']['tmp_name'];
			$image_data = file_get_contents($image);
			$image_data = base64_encode($image_data);
			$content = $_FILES['content']['tmp_name'];
			$content_data = file_get_contents($content);
			$content_data = base64_encode($content_data);
			// insert book into database
			$dbh->exec("INSERT INTO Books (title, authors, description, price, image, content) VALUES('".$title."', '".$authors."', '".$description."','".$price."','".$image_data."','".$content_data."')");
			$message = "book added to database";
			$success = true;
			
			// obtain book_id and declare it for return message
			$query = $dbh->prepare("SELECT book_id FROM Books WHERE content = '".$content_data."'");
			$query->execute();
			$book_id = $query->fetchColumn();
		}
	}
	else
	{
		$message = "you are not an admin.";
		$success = false;
	}
	
	// format of the return message depends on whether the script failed or not, so this checks the $success variable
	// to determine which format to use before returning the json
	if ($success == true)
	{
		$result = json_encode(array('success' => $success, 'message' => $message, 'book_id' => $book_id));
	}
	else
	{
		$result = json_encode(array('success' => $success, 'message' => $message));
	}
	echo $result;
?>