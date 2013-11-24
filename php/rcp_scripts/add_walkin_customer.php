<html>
<body>

<?php
	// Get the database connection.
	$path = $_SERVER['DOCUMENT_ROOT'];
   	$path .= "/PAGR/php/pagr_db.php";
   	include_once($path);

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
	
		// Filter the variables to avoid an SQL injection attack.
		
		/*
		$FNAME = filter_var($_POST['first_name'], FILTER_SANITIZE_STRING);
		$PHONE = filter_var($_POST['phone_number'], FILTER_SANITIZE_INT);
		$SIZE = filter_var($_POST['party_size'], FILTER_SANITIZE_INT); */
		
		$FNAME = $_POST['first_name'];
		$PHONE = $_POST['phone_number'];
		$SIZE = (integer) $_POST['party_size'];
		
		
		if($FNAME == false or $PHONE == false or $SIZE == false)
		{
		    echo "<h4 align = 'center'>ERROR: Data invalid.</h4><br>
			  The guest has not been added to the queue.
			  <br>A firstname or a phone number may not have been specified.";
		}
		elseif($SIZE < 1)
		{
		    $SIZE = 1;
		    
		    $PAGR_database->query("INSERT INTO patrons_t (name, phone_number, party_size) 
				           VALUES ('".$FNAME."','"
						    .$PHONE."','".$SIZE."');");
						    
		    echo "<h4 align = 'center'>The customer has been added!</h4><br>";
		   
		}
		else
		{
		    $PAGR_database->query("INSERT INTO patrons_t (name, phone_number, party_size) 
				           VALUES ('".$FNAME."','"
						    .$PHONE."','".$SIZE."');");
						    
			echo "<h4 align = 'center'>The customer has been added!</h4><br>";   
        }
	}
	else
	{
	    echo "<h4 align = 'center'>ERROR: Data invalid.</h4><br>
			  The guest has not been added to the queue.
			  <br>A firstname or a phone number may not have been specified.";

	}
				       
?>


