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
	$title = $_GET['title'];
	$authors = $_GET['authors'];

	// validation to check if a search term has been entered
	if ((empty($title)) && (empty($authors)))
	{
		$message = "must specify search term for at least one field";
		$success = false;
		$result = json_encode(array('success' => $success, 'message' => $message));
		echo $result;
		exit;
	}
	
	// checks if $title is not null, if so returns all books that contain the search term in the title field
	if (!empty($title))
	{
		$data = array();
		$query = $dbh->query("SELECT * FROM Books WHERE title LIKE '%".$title."%'");
		$result = $query->fetchAll(PDO::FETCH_ASSOC);
		
		foreach ($result as $row)
		{
			$data[] = '{"book_id":'.$row['book_id'].',"title":"'.$row["title"].'","authors":"'.$row['authors'].'","description":"'.$row['description'].'","price":'.$row['price'].',"image":"'.$row['image'].'"}';
		}
	}
	
	// checks if $author variable is not null, if so returns all books that contain the search term in the author field
	else if (!empty($authors))
	{
		$data = array();
		$query = $dbh->query("SELECT * FROM Books WHERE authors LIKE '%".$authors."%'");
		$result = $query->fetchAll(PDO::FETCH_ASSOC);
		
		foreach ($result as $row)
		{
			$data[] = '{"book_id":'.$row['book_id'].',"title":"'.$row["title"].'","authors":"'.$row['authors'].'","description":"'.$row['description'].'","price":'.$row['price'].',"image":"'.$row['image'].'"}';
		}
	}
	print_r($data);
	
	//foreach ($dbh->query("SELECT * FROM Users") as $row)
	//{
	//	print $row['username'];
	//	print $row['email'];
	//}
	
?>