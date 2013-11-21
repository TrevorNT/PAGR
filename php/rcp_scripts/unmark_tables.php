<?php

	include_once(dirname(__FILE__))."/../pagr_db.php";
	$PAGR_database = get_pagr_db_connection();
	include_once(dirname(__FILE__))."/../seating-algorithm/algorithm.php";

	if(isset($_POST['table']))
	{
		$TABLE_ID = $_POST['table'];
		$TABLE_ID = (int) $TABLE_ID;
		
		//Figure out who was sitting at this table.
		$PATRON_ID = $PAGR_database->query("SELECT patron_id
                                            FROM 
                                            pagr_s.tablegroup_table_mapping_t, 
                                            pagr_s.table_t,
                                            pagr_s.patron_tablegroup_mapping_t,
                                            pagr_s.tablegroups_t
                                            WHERE
                                            tablegroup_table_mapping_t.table_id = 
                                            table_t.table_id AND
                                            table_t.isOccupied = 1 AND
                                            table_t.table_id = $TABLE_ID AND
                                            tablegroups_t.is_occupied = 1 AND
                                            (tablegroup_table_mapping_t.tablegroup_id = 
                                            tablegroups_t.tablegroup_id) AND
                                            (patron_tablegroup_mapping_t.tablegroup_id = 
                                            tablegroups_t.tablegroup_id);");
						    

		$ROW = mysqli_fetch_array($PATRON_ID);
		$ID = $ROW['patron_id'];
		$RestaurantObject->unseatGroupDB($ID);
		
		echo "<h1 align = 'center'> Table $TABLE_ID is free. </h1>";
		echo '<div align = "center"><input type = "button" align = "top"
	                onClick="window.location.href = \'../index.php\'"  value = "Go Back" >
	                </input> </div>';
		
						
	}

?>

