<?php 
	// INITIAL COMMENT BLOCK
	//
	/**
	 * application_bridge.php
	 * PAGR External Application Bridge
	 *
	 * This file defines the different functions that external applications
	 * can use to gather information from PAGR.  (The most obvious of which
	 * is the PAGR Android application, though in posting the right data in
	 * the right fields, anything is possible.)
	 *
	 * Fields:
	 *
	 * $_REQUEST['pagr_exec'] = the name of the action to perform (see list
	 * below)
	 *
	 * All remaining fields are dependent on the pagr_exec you're calling:
	 * 
	 *      $_REQUEST field     |      What it is
	 * -------------------------+-----------------------
	 *      handset_id          |  Android handset ID
	 * -------------------------+-----------------------
	 *      reservation_id      |  PAGR reservation ID
	 * 
	 * Returns: this page returns a limited number of possible results:
	 * 
	 * "OK" - server has acknowledged your request.
	 * "ERROR" - there was an error processing your request (there will be a
	 *			message after it).
	 * "0" - only used in get_page_status(), this means no, should not page.
	 * "1" - only used in get_page_status(), this means yes, should page.
	 * 
	 * @author Trevor Toryk
	 * @license Proprietary
	 * @package com.pagr.server
	 */
?>
<?php
	// NAMESPACE BLOCK
	// 
	// The namespace is used for security by encapsulation.
	// Basically, now the exec statement will only call functions
	// in this namespace (on this page).
	namespace pagr\app_bridge;
	//include $_SERVER['DOCUMENT_ROOT'] . '/PAGR/php/pagr_db.php';
	include $_SERVER['DOCUMENT_ROOT'] . '/PAGR/php/seating-algorithm/algorithmDB.php'
?>
<?php
	// FUNCTION CREATION BLOCK
	//
	/**
	 * Creates a new reservation given $_REQUEST['handset_id'].
	 * 
	 * @return Integer A positive integer representing the reservation_id if successful, ERROR if not.
	 */
	function create_reservation() {
		// PRECONDITION: handset_id, party_size, patron_name must be specified
		if (empty($_REQUEST['handset_id'])) die("ERROR: handset_id required");
		if (empty($_REQUEST['party_size'])) die("ERROR: party_size required");
		if (empty($_REQUEST['patron_name'])) die("ERROR: patron_name required");
		
		// OPTIONAL PRECONDITION: reservation_time specifies a reservation, not a walk-in.
		// NOTE: reservation_time MUST be in the following format: "YYYY-MM-DD HH:MM:SS"
		//	(checking this will be reserved to the database though, as it is more efficient)
		$RESERVATION_TIME = NULL;
		if (!empty($_REQUEST['reservation_time'])) $RESERVATION_TIME = $_REQUEST['reservation_time'];
		
		// Connect to the database, set the local variables
		$DB = get_pagr_db_connection();
		$HANDSET_ID = (int)$_REQUEST['handset_id'];
		$PATRON_NAME = $_REQUEST['patron_name'];
		$PARTY_SIZE = (int)$_REQUEST['party_size'];
		
		// Simple injection attack prevention; by removing a semicolon, you can
		// prevent a SQL injection attack by creating a string that is not
		// SQL-compliant and will force a MySQL error.
		$HANDSET_ID = str_replace(";", "", $HANDSET_ID);
		if (!empty($RESERVATION_TIME)) $RESERVATION_TIME = str_replace(";", "", $RESERVATION_TIME);
		
		// Run the query and fetch the results
		$RESULT = $DB->query("SELECT count(*) FROM patrons_t WHERE android_id = '$HANDSET_ID' AND is_deleted = 0;");
		$EXISTS = $RESULT->fetch_row()[0];
		
		// If the one result (which is a count(*)) is 0 and a $RESERVATION_TIME has been specified, insert a new reservation.
		if ($EXISTS == 0 && !empty($RESERVATION_TIME)) {
			$RESULT = $DB->query("INSERT INTO patrons_t(name, party_size, reservation_time, android_id) VALUES ('$PATRON_NAME', $PARTY_SIZE, '$RESERVATION_TIME', '$HANDSET_ID';)");
			
			if ($RESULT === false) {
				$ERROR = $DB->error;
				echo "ERROR: $ERROR";
			}
			
			// ...and then return the result.
			$RESULT = $DB->query("SELECT patron_id FROM patrons_t WHERE android_id = '$HANDSET_ID';");
			echo $RESULT->fetch_row()[0];
		}
		// If the one result is 0 and no $RESERVATION_TIME, then it's just a walk-in customer.
		else if ($EXISTS == 0) {
			$RESULT = $DB->query("INSERT INTO patrons_t(name, party_size, android_id) VALUES ('$PATRON_NAME', $PARTY_SIZE, '$HANDSET_ID');");
			
			if ($RESULT === false) {
				$ERROR = $DB->error;
				echo "ERROR: $ERROR";
			}
			
			// Return the walk-in's ID number.
			$RESULT = $DB->query("SELECT patron_id FROM patrons_t WHERE android_id = '$HANDSET_ID';");
			$PATRON_ID = $RESULT->fetch_row()[0];
			echo $PATRON_ID;
			
			$TABLEGROUP_ID = null;
			// Now, from algorithmDB's Restaurant object, determine the wait time for the walk-in.
			$TIME_IN_SECONDS = $RestaurantObject->findBestTableGroupForSeatingDB($PATRON_ID, $TABLEGROUP_ID);
			
			if (empty($TIME_IN_SECONDS)) die("ERROR: blame Jake, as something in RestaurantObject is returning null");
			else {
				$TIME_IN_MINUTES = (int)($TIME_IN_SECONDS / 60);
				echo ",$TIME_IN_MINUTES";
			}
		}
		else die("ERROR: reservation exists");
	}
	
	/**
	 * Prints reservation details in a <property>=<value>; format.  Requires $_REQUEST['reservation_id'].
	 * 
	 * @return String A long string about reservation details, or ERROR if the reservation doesn't exist.
	 */
    function get_reservation() {
        // PRECONDITION: handset_id, reservation_id must be specified
		if (empty($_REQUEST['handset_id'])) die("ERROR: handset_id required");
		if (empty($_REQUEST['reservation_id'])) die("ERROR: reservation_id required");
		
		// Connect to the database, set the local variables
		$DB = get_pagr_db_connection();
		$HANDSET_ID = $_REQUEST['handset_id'];
		$PATRON_ID = (int)$_REQUEST['reservation_id'];
		
		// Simple injection attack prevention; by removing a semicolon, you can
		// prevent a SQL injection attack by creating a string that is not
		// SQL-compliant and will force a MySQL error.
		$HANDSET_ID = str_replace(";", "", $HANDSET_ID);
		
		// Run the query, return the result
		$RESULT = $DB->query("SELECT name, party_size, reservation_time FROM patrons_t WHERE android_id = '$HANDSET_ID' AND patron_id = $PATRON_ID AND is_deleted = 0 LIMIT 1;");
		if ($RESULT === false) {
			$ERROR = $DB->error;
			echo "ERROR: $ERROR";
		}
		else {
			$ROW = $RESULT->fetch_row();
			if (empty($ROW)) die("ERROR: no reservation found");
			$NAME = $ROW[0];
			$PARTY = $ROW[1];
			$RESERVATION = $ROW[2];
			if (!empty($RESERVATION)) echo "name=$NAME;party_size=$PARTY;reservation_time=$RESERVATION";
			else echo "name=$NAME;party_size=$PARTY;";
		}
		
		// Close the connection.
		$DB->close();
	}
	
	/**
	 * Make a change to the reservation using $_REQUEST['reservation_id'] and
	 * $_REQUEST['reservation_time'].
	 * 
	 * @return String OK if the reservation time change was successful, ERROR if not.
	 */
	function modify_reservation() {
		// PRECONDITION: handset_id, reservation_id must be specified
		if (empty($_REQUEST['handset_id'])) die("ERROR: handset_id required");
		if (empty($_REQUEST['reservation_id'])) die("ERROR: reservation_id required");
		
		// OPTIONAL PRECONDIITON: party_size, reservation_time
		$PARTY_SIZE = NULL;
		$RESERVATION_TIME = NULL;
		if (!empty($_REQUEST['party_size'])) $PARTY_SIZE = (int)$_REQUEST['party_size'];
		if (!empty($_REQUEST['reservation_time'])) $RESERVATION_TIME = $_REQUEST['reservation_time'];
		
		// What if neither of them are specified?
		if (empty($PARTY_SIZE) && empty($RESERVATION_TIME)) die("ERROR: nothing to change");
		
		// Connect to the database, set the local variables
		$DB = get_pagr_db_connection();
		$HANDSET_ID = $_REQUEST['handset_id'];
		$PATRON_ID = (int)$_REQUEST['reservation_id'];
		$RESULT = NULL;
		
		// Simple injection attack prevention; by removing a semicolon, you can
		// prevent a SQL injection attack by creating a string that is not
		// SQL-compliant and will force a MySQL error.
		$HANDSET_ID = str_replace(";", "", $HANDSET_ID);
		
		// Both changed
		if (!empty($PARTY_SIZE) && !empty($RESERVATION_TIME)) {
			$RESULT = $DB->query("UPDATE patrons_t SET party_size = $PARTY_SIZE AND reservation_time = $RESERVATION_TIME WHERE android_id = '$HANDSET_ID' AND patron_id = $PATRON_ID AND is_deleted = 0;");
		}
		// Only the party size changed
		elseif (!empty($PARTY_SIZE)) {
			$RESULT = $DB->query("UPDATE patrons_t SET party_size = $PARTY_SIZE WHERE android_id = '$HANDSET_ID' AND patron_id = $PATRON_ID AND is_deleted = 0;");
		}
		// Only the reservation time changed
		elseif (!empty($RESERVATION_TIME)) {
			$RESULT = $DB->query("UPDATE patrons_t SET reservation_time = $RESERVATION_TIME WHERE android_id = '$HANDSET_ID' AND patron_id = $PATRON_ID AND is_deleted = 0;");
		}
		
		// Run the query, return the result
		if ($RESULT === false) {
			$ERROR = $DB->error;
			echo "ERROR: $ERROR";
		}
		else {
			echo "OK";
		}
		
		// Close the connection.
		$DB->close();
	}
	
	/**
	 * This function has no arguments.  Instead, it merely returns the current wait time for a table.
	 */
	function check_wait_time() {
		// This is just a call to a function in seating-algorithm/algorithm.php
		
	}
	
	/**
	 * This function creates a new order associated with a reservation ID.  For security,
	 * you must also specify handset ID.  (Wouldn't want to have other people ordering for
	 * you, now, would you?)
	 * 
	 * @return Mixed Order ID as integer if successful, ERROR if not.
	 */
	function create_order() {
		// PRECONDITION: handset_id, reservation_id must be set
		if (empty($_REQUEST['handset_id'])) die("ERROR: handset_id required");
		if (empty($_REQUEST['reservation_id'])) die("ERROR: reservation_id required");
		
		// Set reservation_id, get DB connection
		$RESERVATION_ID = (int)$_REQUEST['reservation_id'];
		$HANDSET_ID = $_REQUEST['handset_id'];
		$DB = get_pagr_db_connection();
		
		// Simple injection attack prevention; by removing a semicolon, you can
		// prevent a SQL injection attack by creating a string that is not
		// SQL-compliant and will force a MySQL error.
		$HANDSET_ID = str_replace(";", "", $HANDSET_ID);
		
		// Check to make sure that the given reservation ID exists (with the given handset id)
		$RESULT = $DB->query("SELECT count(*) FROM patrons_t WHERE patron_id = $RESERVATION_ID AND android_id = '$HANDSET_ID' AND is_deleted = 0;");
		if ($RESULT->fetch_row()[0] == 0) die("ERROR: the given handset ID and reservation ID pair does not exist");
		
		// Check to make sure that an order ID doesn't exist for the given reservation ID
		$RESULT = $DB->query("SELECT count(*) FROM order_mapping_t WHERE patron_id = $RESERVATION_ID;");
		if ($RESULT->fetch_row()[0] == 0) {
			// And finally, create the order and return the new order ID
			$DB->query("INSERT INTO order_mapping_t (patron_id) VALUES ($RESERVATION_ID);");
			if ($RESULT === false) {
				$ERROR = $DB->error;
				die("ERROR: $ERROR");
			}
			$RESULT = $DB->query("SELECT order_id FROM order_mapping_t WHERE patron_id = $RESERVATION_ID LIMIT 1;");
			echo $RESULT->fetch_row()[0];
		}
	}
	
	/**
	 * Given reservation ID and order ID, as well as item # and quantity, creates a new item
	 * order for the order in question.
	 * 
	 * Two quick notes on this function:
	 *		- You can delete items using this function!  Just pass the item ID and quantity 0.
	 *		- If you send an item ID that already exists for the given order, it is *overwritten* with the new quantity.
	 * 
	 * @return String OK if acknowledged, ERROR if there is a problem.
	 */
	function add_order_item() {
		// PRECONDITION: reservation_id, order_id, item_id, and quantity must be set:
		if (empty($_REQUEST['reservation_id'])) die("ERROR: reservation_id required");
		if (empty($_REQUEST['order_id'])) die("ERROR: reservation_id required");
		if (empty($_REQUEST['item_id'])) die("ERROR: reservation_id required");
		if (empty($_REQUEST['quantity'])) die("ERROR: reservation_id required");
		
		// Set all the many variables, incl. the database
		$RESERVATION_ID = (int)$_REQUEST['reservation_id'];
		$ORDER_ID = (int)$_REQUEST['order_id'];
		$ITEM_ID = (int)$_REQUEST['item_id'];
		$QUANTITY = (int)$_REQUEST['quantity'];
		$DB = get_pagr_db_connection();
		
		// Check to make sure that the RESERVATION_ID, ORDER_ID pair is valid
		$RESULT = $DB->query("SELECT count(*) FROM order_mapping_t WHERE reservation_id = $RESERVATION_ID AND order_id = $ORDER_ID;");
		if ($RESULT === false) {
			$ERROR = $DB->error;
			die("ERROR: $ERROR");
		}
		if ($RESULT->fetch_row()[0] == 0) die("ERROR: the given reservation_id and order_id pair does not exist");
		
		// We have, naturally, 3 cases here, each corresponding to a different SQL operation:
		//		1. Quantity == 0.  In this case, that particular item is being deleted entirely.  (SQL: DELETE FROM)
		//		2. On first query, item_id already exists for that order id.  Update quantity.  (SQL: UPDATE)
		//		3. On first query, item_id does not exist for that order id.  Insert new order item.  (SQL: INSERT INTO)
		if ($QUANTITY == 0) {
			$RESULT = $DB->query("DELETE FROM order_t WHERE order_id = $ORDER_ID AND item_id = $ITEM_ID;");
			if ($RESULT === false) {
				$ERROR = $DB->error;
				die("ERROR: $ERROR");
			}
		}
		else {
			$RESULT = $DB->query("SELECT count(*) FROM order_t WHERE order_id = $ORDER_ID AND item_id = $ITEM_ID;");
			if ($RESULT === false) {
				$ERROR = $DB->error;
				die("ERROR: $ERROR");
			}
			
			$EXISTS = (int)$RESULT->fetch_row()[0];
			
			if ($EXISTS == 0) {
				$RESULT = $DB->query("INSERT INTO order_t (order_id, item_id, quantity) VALUES ($ORDER_ID, $ITEM_ID, $QUANTITY);");
				if ($RESULT === false) {
					$ERROR = $DB->error;
					die("ERROR: $ERROR");
				}
			}
			else {
				$RESULT = $DB->query("UPDATE order_t SET quantity = $QUANTITY WHERE order_id = $ORDER_ID AND item_id = $ITEM_ID;");
				if ($RESULT === false) {
					$ERROR = $DB->error;
					die("ERROR: $ERROR");
				}
			}
		}
		
		// Thanks to all the error checking along the way, all that remains is to return an acknowledgement.
		echo "OK";
	}
	
	/**
	 * 
	 */
	function get_order() {
		echo "get an order!";
		
		// This function allows you to get the current order for the user.
		// if (exists(order_id))
		//		get order_details from DB;
		//		return order_details;
		// else
		//		return "";
		//
		// In this case, an empty string simply means "no data".  It could be
		// that the handset doesn't have an order out, or that the handset also
		// has no reservation.
	}
	
	/**
	 * Returns item details for a given appetizer order item.
	 * 
	 * @return String A <property>=<value>; string with all the properties about the given item, or ERROR if not found.
	 */
	function get_item() {
		// PRECONDITION: item_id must be set.
		if (empty($_REQUEST['item_id'])) die("ERROR: item_id must be set");
		
		// Connect to the database, set the local variables
		$DB = get_pagr_db_connection();
		$ITEM_ID = (int)$_REQUEST['item_id'];
		
		// Run the query, return the result
		$RESULT = $DB->query("SELECT item_name, item_desc, item_price, is_drink, picture FROM items_t WHERE item_id = $ITEM_ID LIMIT 1;");
		if ($RESULT === false) {
			$ERROR = $DB->error;
			echo "ERROR: $ERROR";
		}
		else {
			$ROW = $RESULT->fetch_row();
			if (empty($ROW)) die("ERROR: no item found");
			$ITEM_NAME = $ROW[0];
			$ITEM_DESC = $ROW[1];
			$ITEM_PRICE = $ROW[2];
			$IS_DRINK = $ROW[3];
			$PIC = $ROW[4];
			echo "name=$ITEM_NAME;desc=$ITEM_DESC;price=$ITEM_PRICE;drink=$IS_DRINK;picture=$PIC;";
		}
	}
	
	/**
	 * This function tells you whether or not you should page.
	 * 
	 * Request variables to be set:
	 *		handset_id: the handset ID of the mobile device.
	 *		reservation_id: the ID of the patron's reservation.
	 * 
	 * Both must be set to check a page.
	 * 
	 * @return string "0" if don't page, "1" if page, "ERROR" on error.
	 */
	function get_page_status() {
		// PRECONDITION: handset_id, reservation_id must be specified
		if (empty($_REQUEST['handset_id'])) die("ERROR: handset_id required");
		if (empty($_REQUEST['reservation_id'])) die("ERROR: reservation_id required");
		
		// Connect to the database, set the local variables
		$DB = get_pagr_db_connection();
		$HANDSET_ID = $_REQUEST['handset_id'];
		$PATRON_ID = (int)$_REQUEST['reservation_id'];
		
		// Simple injection attack prevention; by removing a semicolon, you can
		// prevent a SQL injection attack by creating a string that is not
		// SQL-compliant and will force a MySQL error.
		$HANDSET_ID = str_replace(";", "", $HANDSET_ID);
		
		// Run the query, return the result
		$RESULT = $DB->query("SELECT page FROM patrons_t WHERE android_id = '$HANDSET_ID' AND patron_id = $PATRON_ID LIMIT 1;");
		if ($RESULT === false) {
			$ERROR = $DB->error;
			echo "ERROR: $ERROR";
		}
		else {
			echo $RESULT->fetch_row()[0];
		}
		
		// Close the connection.
		$DB->close();
	}
	
	/**
	 * This function acknowledges a page request.
	 * 
	 * Both must be set to acknowledge! (that way it's much harder to falsify an
	 * acknowledgement).
	 * 
	 * @return string "OK" if the acknowledgement went through, "ERROR" if not.
	 */
	function ack_page() {
		// PRECONDITION: handset_id, reservation_id must be specified
		if (empty($_REQUEST['handset_id'])) die("ERROR: handset_id required");
		if (empty($_REQUEST['reservation_id'])) die("ERROR: reservation_id required");
		
		// Connect to the database, set the local variables
		$DB = get_pagr_db_connection();
		$HANDSET_ID = $_REQUEST['handset_id'];
		$PATRON_ID = (int)$_REQUEST['reservation_id'];
		
		// Simple injection attack prevention; by removing a semicolon, you can
		// prevent a SQL injection attack by creating a string that is not
		// SQL-compliant and will force a MySQL error.
		$HANDSET_ID = str_replace(";", "", $HANDSET_ID);
		
		// Run the query.
		$RESULT = $DB->query("UPDATE patrons_t SET page = 0 WHERE android_id = '$HANDSET_ID' AND patron_id = $PATRON_ID;");
		if ($RESULT === true) {
			echo "OK";
		}
		else {
			$ERROR = $DB->error;
			echo "ERROR: $ERROR";
		}
		
		// Close the connection.
		$DB->close();
	}
?>
<?php
	// PROCEDURAL BLOCK
	// 
	// Simply calls the function given in $_REQUEST['pagr_exec'] provided
	// it is within this namespace.  (And provided it exists.)
	if (empty($_REQUEST['pagr_exec'])) die('ERROR: pagr_exec must be defined');
	call_user_func("pagr\app_bridge\\" . $_REQUEST['pagr_exec']);
	
	$RR = new \Restaurant();
?>