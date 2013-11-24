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
	
		// Filter the variables to avoid an SQL injection attack.
		
		/*
		$FNAME = filter_var($_POST['first_name'], FILTER_SANITIZE_STRING);
		$PHONE = filter_var($_POST['phone_number'], FILTER_SANITIZE_INT);
		$SIZE = filter_var($_POST['party_size'], FILTER_SANITIZE_INT); */
		
		$FNAME = $_POST['first_name'];
		$PHONE = $_POST['phone_number'];
		$SIZE = (integer) $_POST['party_size'];
		
		$DATE = $_POST['datepicker'];
		$HOUR =  $_POST['hour'];
		$MINUTE = $_POST['minute'];
		
	
		$ERROR = false;
		
		if($FNAME == false or $PHONE == false)
		{
		    echo "<h4 align = 'center'>ERROR: Data invalid.</h4><br>
			  The guest has not been added to the queue.
			  <br>A firstname or a phone number may not have been specified.";
			  
			$ERROR = True;
		}
		
		elseif($DATE == false or $HOUR == "hour" or $MINUTE == "minute")
		{
		    echo "<h4 align = 'center'>ERROR: Data invalid.</h4><br>
			  The guest has not been added to the queue.
			  <br>A reservation time or date was not specified.";
			  
			  $ERROR = True;
        }
        
        $HOUR = (int) $HOUR;
		$MINUTE = (int) $MINUTE;
        
        list($MONTH, $DAY, $YEAR) = explode('/', $DATE);
        
        date_default_timezone_set('America/New_York');
        $MONTH = (int) $MONTH;
        $DAY = (int) $DAY;
        $YEAR = (int) $YEAR;
        
        $CUR_TIME = time();
        $REQ_TIME = mktime((int)$HOUR,$MINUTE,0,$MONTH,$DAY,$YEAR);
        
        if($REQ_TIME < $CUR_TIME)
        {
             echo "<h4 align = 'center'>ERROR: Data invalid.</h4><br>
			  The guest has not been added to the queue.
			  <br> The time specified has already passed.";
            $ERROR = True;
        }
       
		
        
        if($ERROR == False)
        {
		    if($SIZE < 1)
		    {
		        $SIZE = 1;
		    }
		       
		    // Create a PHP Timestamp.
		    
		    $TIMESTAMP =  date('Y-m-d H:i:s', $REQ_TIME);
		    
		    
	        $PAGR_database->query("INSERT INTO patrons_t (name, phone_number, party_size, reservation_time) 
			               VALUES ('".$FNAME."','"
					        .$PHONE."','".$SIZE."','".$TIMESTAMP."');");
					        
		    echo "<h4 align = 'center'>The customer has been added!</h4><br>";
			 
       }
	}
	else
	{
	    echo "<h4 align = 'center'>ERROR: Data invalid.</h4><br>
			  The guest has not been added to the queue.
			  <br>An error occurred.";

	}
				       
?>


