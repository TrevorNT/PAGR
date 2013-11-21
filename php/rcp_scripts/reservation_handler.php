<?php
	/**
	 * reservation_handler.php
	 * PAGR External Application Bridge
	 *
	 * This file is responsible for displaying the reservations for the
	 * restaurant. It has two functions.  The first function clears "yesterday's"
	 * reservations when called, and the second function populates the reservation
	 * queue.
	 * 
	 *
	 * @author Akshar Shastri
	 * @license Proprietary
	 * @package rcp.pagr.server
	 */
?>
<?php
    function clearReservations($PAGR_database)
    {
        // Clean up old reservations from yesterday.
        $PAGR_database->query("UPDATE patrons_t 
                               SET is_deleted = 1 
                               WHERE reservation_time < curdate()");
    }
    
    function populateReservations($PAGR_database)
    {
        // Display the reservations in the reservation queue.
        $RESERVATION_DATES = $PAGR_database->query("SELECT DISTINCT
                                                    
                                                    EXTRACT(YEAR from reservation_time) 
                                                    AS RES_YEAR,
                                                    
                                                    EXTRACT(MONTH from reservation_time) 
                                                    AS RES_MONTH,
                                                    
                                                    EXTRACT(DAY from reservation_time) 
                                                    AS RES_DAY
                                               
                                                   FROM patrons_t
                                                   
                                                   WHERE 
                                                   reservation_time IS NOT NULL
                                                   AND is_deleted = 0
                                                    
                                                   ORDER BY 
                                                   reservation_time");
                                                   
       
       while($DATE_ROW = mysqli_fetch_array($RESERVATION_DATES))
       {
            // Get the reservations for a particular day.
            
            $MONTH =  $DATE_ROW["RES_MONTH"];
            $DAY =  $DATE_ROW["RES_DAY"];
            $YEAR =  $DATE_ROW["RES_YEAR"];
            $RESERVATIONS = $PAGR_database->query("SELECT name, party_size, patron_id, is_deleted,
                                                         
                                                          
                                                          EXTRACT(HOUR FROM reservation_time) AS Hour,
                                                          EXTRACT(Minute FROM reservation_time) AS Minute
                                                          
                                                          
                                                          
                                                          FROM pagr_s.patrons_t
                                                          
                                                          WHERE
                                                          
                                                              EXTRACT(YEAR FROM reservation_time) = $YEAR AND
                                                              EXTRACT(MONTH FROM reservation_time) = $MONTH AND
                                                              EXTRACT(DAY FROM reservation_time) = $DAY
                                        
                                                                
                                                               
                                                          ORDER BY reservation_time");
                 
           // Display the date for this "set" of reservations.                                                             
           echo "<h5 align = 'center'><b>".$DATE_ROW["RES_MONTH"]."-"
                                       .$DATE_ROW["RES_DAY"]."-"
                                       .$DATE_ROW["RES_YEAR"]."</b></h5>";
                                                                                     
           echo "<ol>";
           while($ENTRY_ROW = mysqli_fetch_array($RESERVATIONS))
           {
                if($ENTRY_ROW['is_deleted'] == 0)
                {
                    $NAME = $ENTRY_ROW['name'];
			        $DISPLAY_NAME = "";
			        $ID = $ENTRY_ROW['patron_id'];
			        
			        // Make sure that the time is in an hh:mm format.
			        $HOUR = "";
			        $MINUTE = "";
			        if(strlen($ENTRY_ROW['Hour'] < 2))
			        {
			            $HOUR = "0".$ENTRY_ROW['Hour'];
			        }
			        else
			        {
			            $HOUR = $ENTRY_ROW['Hour'];
			        }
			        
			        if(strlen($ENTRY_ROW['Minute'] < 2))
			        {
			            $MINUTE = "0".$ENTRY_ROW['Minute'];
			        }
			        else
			        {
			            $MINUTE = $ENTRY_ROW['Minute'];
			        }
			            
			        $TIME = $HOUR.":".$MINUTE;
			        
			        /* Grab the first 7 characters of the name.
			        If the name is less than 7 characters, pad the
			        right side with spaces. */
			        for($i = 0; $i < 7; $i++)
			        {
			            if($i < strlen($NAME))
			            {
			                $DISPLAY_NAME = $DISPLAY_NAME.$NAME[$i];
			            }
			            
			            else
			            {
			                $DISPLAY_NAME = $DISPLAY_NAME."&nbsp";
			            }
			        }
			        
			        // Display the reservation information.
			        if($ID < 10)
			        {
				        echo "<li> <font face='courier new'><b>ID:</b>".$ID."&nbsp".$DISPLAY_NAME.
				         "&nbsp<b>Time:</b> ".$TIME."&nbsp<b>Party Size:</b> "
				         .$ENTRY_ROW['party_size']."</font></li>";
				    }
				
				    else
				    {
				        echo "<li> <font face='courier new'><b>ID:</b>&nbsp".$ID."&nbsp".$DISPLAY_NAME.
				         "&nbsp<b>Time:</b> ". $TIME."&nbsp<b> Size:</b> "
				         .$ENTRY_ROW['party_size']."</font></li>";
				    }
				}
				
		    }
		    
		    echo "</ol>";
                                                          
       }                                             
       
    }                                   
?>
