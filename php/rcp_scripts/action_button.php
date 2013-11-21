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

	// Include the seating algorithm file.
	include(dirname(__FILE__))."/../seating-algorithm/algorithm.php";
	
	
	if(isset($_POST['get_order']))
	{
	   // Figure out what customer we need the order for.
	   $person = $PAGR_database->query("SELECT name FROM patrons_t 
	                                    WHERE ".$_POST['customer']."= patron_id;");
        
       // Get the name from the person.
       $person = mysqli_fetch_array($person);
        
       // Create the heading.
	   echo "<h1 align = center> Order for ".$person['name'].
	         "  "."(ID: ".$_POST['customer'].")</h1>";
	   echo '<div align = "center"><input type = "button" align = "top"
	                onClick="window.location.href = \'../index.php\'"  value = "Go Back" >
	                </input> </div>';
	   
	   echo '<p></p>';
	   
	   $ITEMS = $PAGR_database->query("SELECT 
	                                       pagr_s.items_t.item_name,
                                           pagr_s.order_t.quantity,
                                           pagr_s.items_t.is_drink
                                       
                                       FROM 
                                            pagr_s.order_mapping_t,
                                            pagr_s.items_t,
                                            pagr_s.order_t
                                       
                                       WHERE
                                            pagr_s.order_mapping_t.patron_id =
                                            ".$_POST['customer']."
                                            AND order_mapping_t.order_id = order_t.order_id
                                            AND order_t.item_id = items_t.item_id;");
                                            
       // No orders available.                       
       if($ITEMS->num_rows == 0)
       {
            echo "<h3 align = center> No orders for this customer.</h3>";
       }
       
       else
       {
	         
	   
	       // Create the table.
	       echo "<table border= 1 align = center>";
	       
	       echo '<table style="width: 95%; height: 75%; text-align: left; 
	             margin-left: auto; margin-right: auto;" border="1" 
	             cellpadding="2" cellspacing="2">';
	             
	       echo "<col width='50%'>";
           echo "<col width='50%'>";
	       
	       // Table column headings.
	       echo "<tr>";
	       echo "<td align = 'center' style = 'height: 35px'><b> Item </b></td>";
	       echo "<td align = 'center'><b> Quantity </b></td>";
	       echo "</tr>";
	       
	       echo "<tr>";
	       echo "<td colspan='2' align='center' style = 'height: 35px'>
	             <b> Appetizers </b></td>";
	       echo "</tr>";
	
	       
	       // This query finds all the items the customer ordered. 
	       $ITEMS = $PAGR_database->query("SELECT 
	                                           pagr_s.items_t.item_name,
                                               pagr_s.order_t.quantity,
                                               pagr_s.items_t.is_drink
                                           
                                           FROM 
                                                pagr_s.order_mapping_t,
                                                pagr_s.items_t,
                                                pagr_s.order_t
                                           
                                           WHERE
                                                pagr_s.order_mapping_t.patron_id =
                                                ".$_POST['customer']."
                                                
                                                AND order_mapping_t.order_id = order_t.order_id
                                                AND order_t.item_id = items_t.item_id;");
                                                
           // Populate the appetizers area.                   
	       while($A_ROW = mysqli_fetch_array($ITEMS))
	       {
	            if($A_ROW['is_drink'] == 0)
	            {
                    echo "<tr>";
                    echo "<td align='center'>".$A_ROW['item_name']."</td>";
                    echo "<td align='center'>".$A_ROW['quantity']."</td>";
                    echo "</tr>";
                }
	       }
	       
	       $ITEMS = $PAGR_database->query("SELECT 
	                                           pagr_s.items_t.item_name,
                                               pagr_s.order_t.quantity,
                                               pagr_s.items_t.is_drink
                                           
                                           FROM 
                                                pagr_s.order_mapping_t,
                                                pagr_s.items_t,
                                                pagr_s.order_t
                                           
                                           WHERE
                                                pagr_s.order_mapping_t.patron_id =
                                                ".$_POST['customer']."
                                                
                                                AND order_mapping_t.order_id = order_t.order_id
                                                AND order_t.item_id = items_t.item_id;");
	       echo "<tr>";
	       echo "<td colspan='2' align='center' style = 'height: 35px'>
	       <b> Beverages </b></td>";
	       echo "</tr>";
	      
	       // Populate the beverage area.               
	       while($A_ROW = mysqli_fetch_array($ITEMS))
	       {
	            if($A_ROW['is_drink'] == 1)
	            {
                    echo "<tr>";
                    echo "<td align='center'>".$A_ROW['item_name']."</td>";
                    echo "<td align='center'>".$A_ROW['quantity']."</td>";
                    echo "</tr>";
                }
                
	       }
	       
	   }
	}

    elseif(isset($_POST['page']))
    {
        $GET_CUST = $PAGR_database->query("SELECT patron_id, phone_number FROM patrons_t
                                           WHERE
                                                patron_id = ".$_POST["customer"]."
                                                AND android_id IS NOT NULL;");
             
        // The guest has an Android uuid.                                   
        if($GET_CUST->num_rows > 0)
        {
            // Set the page attribute for the guest to 1.
            $PAGR_database->query("UPDATE patrons_t SET page = 1 WHERE patron_id = "
                                  . $_POST["customer"]);
            
            echo "<h1 align = 'center'> Customer with ID ".$_POST['customer']." has been".
                  " paged. </h1>";
                  
        }
        else
        {
            // The guest will have to be called manually.
            $GET_CUST = $PAGR_database->query("SELECT patron_id, phone_number FROM patrons_t
                                                
                                                WHERE
                                                patron_id = ".$_POST["customer"]."
                                                AND android_id IS NULL;");
                                                
            while($ROW = mysqli_fetch_array($GET_CUST))
            {
                echo "<h1 align = 'center'> Customer with ID ".$_POST['customer']." cannot be".
                  " paged. </h1>";
                  
                echo "<h3 align = 'center'> Please call them at ".$ROW['phone_number']."</h3>";
            }
            
        }
                
        echo '<div align = "center"><input type = "button" align = "top"
	                onClick="window.location.href = \'../index.php\'"  value = "Go Back" >
	                </input> </div>';
    }
    elseif(isset($_POST['delete']))
    {
        // Set the is_deleted attribute for the guest to 1.
        $PAGR_database->query("UPDATE patrons_t SET is_deleted = 1 WHERE patron_id = "
                              . $_POST["customer"]);
        echo "<h1 align = 'center'> Customer with ID ".$_POST['customer']." deleted. </h1>";
        echo '<div align = "center"><input type = "button" align = "top"
	                onClick="window.location.href = \'../index.php\'"  value = "Go Back" >
	                </input> </div>';
    }
	elseif(isset($_POST['seat_customer']))
	{
		$RETURNEDTABLEGROUP = 0;
		$SEATING_RET = $RestaurantObject->findBestTableGroupForSeatingDB($_POST["customer"],
																		 $RETURNEDTABLEGROUP);
	
	   /*
	    echo (0 == Null);
	    if ($SEATING_RET == Null)
	    {
	        echo "<h1 align = 'center'> Customer with ID ".$_POST['customer']." cannot be".
                  " seated. </h1>";
                  
            echo "<h3 align = 'center'> The party may be too large to be seated automatically.</h3>";
            
        } */
        
        if($SEATING_RET > 0)
        {
            echo "<h1 align = 'center'> Customer with ID ".$_POST['customer']." will have to".
                  " wait to be seated. </h1>";
                  
            echo "<h3 align = 'center'> The wait time is: $SEATING_RET</h3>";
            
        }
        else
        {
		    $RestaurantObject->seatGroupDB($_POST["customer"], $RETURNEDTABLEGROUP);
		    
		    $TABLES = $PAGR_database->query("SELECT table_id FROM tablegroup_table_mapping_t
		                                     WHERE
		                                        tablegroup_table_mapping_t.tablegroup_id = 
		                                        $RETURNEDTABLEGROUP;");
		    
		    echo "<h1 align = 'center'> Customer with ID ".$_POST['customer']." has been".
                  " seated. </h1>";
                  
            $counter = 0;
            
            $MAX_ROWS = $TABLES->num_rows;
            $TABLE_STRING = "";
            while($ROW = mysqli_fetch_array($TABLES))
            {
                // More than one table left.
                if($counter < $MAX_ROWS - 1)
                {
                    // Append a comma to the end of the table name.
                    $TABLE_STRING = $TABLE_STRING.$ROW['table_id'].", ";
                }
                
                else
                {
                      $TABLE_STRING = $TABLE_STRING.$ROW['table_id'].".";
                      
                }
                
                $counter++;
            }  
            
            echo "<h3 align = 'center'> The customer has been seated at table(s): ".
                  $TABLE_STRING."</h3>";
		                                        
		}
		
		echo '<div align = "center"><input type = "button" align = "top"
	                onClick="window.location.href = \'../index.php\'"  value = "Go Back" >
	                </input> </div>';
	}
    
    
?>
