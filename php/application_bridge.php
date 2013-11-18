<?php 
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
	 * "0" - only used in should_page(), this means no, should not page.
	 * "1" - only used in should_page(), this means yes, should page.
	 * 
	 * @author Trevor Toryk
	 * @license Proprietary
	 * @package com.pagr.server
	 */
?>
<?php
	// The namespace is used for security by encapsulation.
	// Basically, now the exec statement will only call functions
	// in this namespace (on this page).
	namespace pagr\app_bridge;
	include 'pagr_db.php';
//	include 'seating-algorithm/algorithm.php'
?>
<?php
	/**
	 * Creates a new reservation given $_REQUEST['handset_id'].
	 * 
	 * @return Integer A positive integer representing the reservation_id if successful, ERROR if not.
	 */
	function create_reservation() {
		// PRECONDITION: handset_id, party_size, patron_name must be specified
		if (!isset($_REQUEST['handset_id'])) die("ERROR: handset_id required");
		if (!isset($_REQUEST['party_size'])) die("ERROR: party_size required");
		if (!isset($_REQUEST['patron_name'])) die("ERROR: patron_name required");
		
		// OPTIONAL PRECONDITION: reservation_time specifies a reservation, not a walk-in.
		// NOTE: reservation_time MUST be in the following format: "YYYY-MM-DD HH:MM:SS"
		//	(checking this will be reserved to the database though, as it is more efficient)
		$RESERVATION_TIME = NULL;
		if (isset($_REQUEST['reservation_time'])) $RESERVATION_TIME = $_REQUEST['reservation_time'];
		
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
		$RESULT = $DB->query("SELECT count(*) FROM patrons_t WHERE android_id = '$HANDSET_ID'");
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
			echo $RESULT->fetch_row()[0];
		}
		else die("ERROR: reservation exists");
	}
	
	/**
	 * Prints reservation details in a <property>:<value>; format.  Requires $_REQUEST['reservation_id'].
	 * 
	 * @return String A long string about reservation details, or ERROR if the reservation doesn't exist.
	 */
    function get_reservation() {
        // PRECONDITION: handset_id, reservation_id must be specified
		if (!isset($_REQUEST['handset_id'])) die("ERROR: handset_id required");
		if (!isset($_REQUEST['reservation_id'])) die("ERROR: reservation_id required");
		
		// Connect to the database, set the local variables
		$DB = get_pagr_db_connection();
		$HANDSET_ID = $_REQUEST['handset_id'];
		$PATRON_ID = (int)$_REQUEST['reservation_id'];
		
		// Simple injection attack prevention; by removing a semicolon, you can
		// prevent a SQL injection attack by creating a string that is not
		// SQL-compliant and will force a MySQL error.
		$HANDSET_ID = str_replace(";", "", $HANDSET_ID);
		
		// Run the query, return the result
		$RESULT = $DB->query("SELECT name, party_size, reservation_time FROM patrons_t WHERE android_id = '$HANDSET_ID' AND patron_id = $PATRON_ID LIMIT 1;");
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
	 * @return Boolean True if the reservation time change was successful, -3 if not.
	 */
	function modify_reservation() {
		// PRECONDITION: handset_id, reservation_id must be specified
		if (!isset($_REQUEST['handset_id'])) die("ERROR: handset_id required");
		if (!isset($_REQUEST['reservation_id'])) die("ERROR: reservation_id required");
		
		// OPTIONAL PRECONDIITON: party_size, reservation_time
		$PARTY_SIZE = NULL;
		$RESERVATION_TIME = NULL;
		if (isset($_REQUEST['party_size'])) $PARTY_SIZE = (int)$_REQUEST['party_size'];
		if (isset($_REQUEST['reservation_time'])) $RESERVATION_TIME = $_REQUEST['reservation_time'];
		
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
			$RESULT = $DB->query("UPDATE patrons_t SET party_size = $PARTY_SIZE AND reservation_time = $RESERVATION_TIME WHERE android_id = '$HANDSET_ID' AND patron_id = $PATRON_ID;");
		}
		// Only the party size changed
		elseif (!empty($PARTY_SIZE)) {
			$RESULT = $DB->query("UPDATE patrons_t SET party_size = $PARTY_SIZE WHERE android_id = '$HANDSET_ID' AND patron_id = $PATRON_ID;");
		}
		// Only the reservation time changed
		elseif (!empty($RESERVATION_TIME)) {
			$RESULT = $DB->query("UPDATE patrons_t SET reservation_time = $RESERVATION_TIME WHERE android_id = '$HANDSET_ID' AND patron_id = $PATRON_ID;");
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
	 * 
	 */
	function make_order() {
		echo "create an order!";
		
		// This function allows you to create a new order for a reservation (or
		// modify an existing one by setting a new order).
		// if (exists(order_id))
		//		set order_details to DB;
		//		return true;
		// else if (not exists(order_id) and exists(new_order_details))
		//		create new order_details;
		//		return new_order_id;
		// else
		//		return -4;
	}
	
	/**
	 * 
	 */
	function add_change_order_item() {
		
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
	 * 
	 */
	function get_item() {
		
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
		if (!isset($_REQUEST['handset_id'])) die("ERROR: handset_id required");
		if (!isset($_REQUEST['reservation_id'])) die("ERROR: reservation_id required");
		
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
		if (!isset($_REQUEST['handset_id'])) die("ERROR: handset_id required");
		if (!isset($_REQUEST['reservation_id'])) die("ERROR: reservation_id required");
		
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
	// Simply calls the function given in $_REQUEST['pagr_exec'] provided
	// it is within this namespace.  (And provided it exists.)
	if (!isset($_REQUEST['pagr_exec'])) die('ERROR: pagr_exec must be defined');
	call_user_func("pagr\app_bridge\\" . $_REQUEST['pagr_exec']);
?>