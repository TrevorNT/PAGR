<html>
<body>

<?php
	// Get the database connection.
	include(dirname(__FILE__))."/../pagr_db.php";
	$PAGR_database = get_pagr_db_connection();
	if(mysqli_connect_errno($PAGR_database))
	{
		$good =  "Guest Connection Failed";
	}
	else
	{
		$good = "good";
	}

	//Add the customer to the database.
	if(isset($_POST['first_name']) and isset($_POST['phone_number']))
	{
		echo $_POST['first_name'];
		echo $_POST['phone_number'];
		$PAGR_database->query("INSERT INTO patrons_t (name, phone_number) 
				       VALUES ('".$_POST['first_name']."','"
						.$_POST['phone_number']."');");
	}
	else
	{

	}
				       
?>


