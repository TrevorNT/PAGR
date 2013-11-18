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
?>
<?php
	/**
	 * Creates a new reservation given $_REQUEST['handset_id'].
	 * 
	 * @return Integer A positive integer representing the reservation ID if successful, ERROR if not.
	 */
	function create_reservation() {
		// PRECONDITION: handset_id, party_size must be specified
		if (!isset($_REQUEST['handset_id'])) die("ERROR: handset_id required");
		if (!isset($_REQUEST['party_size'])) die("ERROR: party_size required");
		if (!isset($_REQUEST['patron_name'])) die("ERROR: patron_name required");
		
		$PATRON_NAME = $_REQUEST['patron_name'];
		$PARTY_SIZE = $_REQUEST['party_size'];
		
		$RESERVATION_TIME = NULL;
		
		// OPTIONAL PRECONDITION: reservation_time specifies a reservation, not a walk-in.
		// NOTE: reservation_time MUST be in the following format: "YYYY-MM-DD HH:MM:SS"
		//	(checking this will be reserved to the database though, as it is more efficient)
		if (isset($_REQUEST['reservation_time'])) $RESERVATION_TIME = $_REQUEST['reservation_time'];
		
		// Connect to the database, set the local variables
		$DB = get_pagr_db_connection();
		$HANDSET_ID = $_REQUEST['handset_id'];
		
		// Simple injection attack prevention; by removing a semicolon, you can
		// prevent a SQL injection attack by creating a string that is not
		// SQL-compliant and will force a MySQL error.
		$HANDSET_ID = str_replace(";", "", $HANDSET_ID);
		if (!empty($RESERVATION_TIME)) $RESERVATION_TIME = str_replace(";", "", $RESERVATION_TIME);
		
		$RESULT = $DB->query("SELECT count(*) FROM patrons_t WHERE android_id = '$HANDSET_ID'");
		$EXISTS = $RESULT->fetch_row()[0];
		if ($EXISTS == 0 && !empty($RESERVATION_TIME)) {
			$RESULT = $DB->query("INSERT INTO patrons_t(name, party_size, reservation_time, android_id) VALUES ('$PATRON_NAME', $PARTY_SIZE, '$RESERVATION_TIME', '$HANDSET_ID';)");
			
			if ($RESULT === false) {
				die("ERROR: unspecified database error");
			}
			
			$RESULT = $DB->query("SELECT patron_id FROM patrons_t WHERE android_id = '$HANDSET_ID';");
			echo $RESULT->fetch_row()[0];
		}
		else if ($EXISTS == 0) {
			$RESULT = $DB->query("INSERT INTO patrons_t(name, party_size, android_id) VALUES ('$PATRON_NAME', $PARTY_SIZE, '$HANDSET_ID');");
			
			if ($RESULT === false) {
				die("ERROR: unspecified database error");
			}
			
			$RESULT = $DB->query("SELECT patron_id FROM patrons_t WHERE android_id = '$HANDSET_ID';");
			echo $RESULT->fetch_row()[0];
		}
		else die("ERROR: reservation exists");
	}
	
	/**
	 * Prints reservation details in a <property>:<value>; format.  Requires $_REQUEST['reservation_id'].
	 * 
	 * @return String A long string about reservation details on multiple lines, or -2 if the reservation doesn't exist.
	 */
    function get_reservation() {
        echo "get a reservation!";
		
		// This function gets reservation details for a given reservation_id.
		// if (exists(reservation_id))
		//		get reservation_details from DB;
		//		return reservation_details;
		// else
		//		return -2;
	}
	
	/**
	 * Make a change to the reservation using $_REQUEST['reservation_id'] and
	 * $_REQUEST['reservation_time'].
	 * 
	 * @return Boolean True if the reservation time change was successful, -3 if not.
	 */
	function modify_reservation() {
		echo "mod a reservation!";
		
		// This function allows you to modify a reservation time.
		// if (exists(reservation_id) and exists(reservation_new_time))
		//		set reservation_time to DB;
		//		return true;
		// else
		//		return -3;
	}
	
	/**
	 * 
	 */
	function check_wait_time() {
		
	}
	
	/**
	 * 
	 */
	function create_update_order() {
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
		$PATRON_ID = $_REQUEST['reservation_id'];
		
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
	 * Request variables to be set:
	 *		handset_id: the handset ID of the mobile device.
	 *		reservation_id: the ID of the patron's reservation.
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
		$PATRON_ID = $_REQUEST['reservation_id'];
		
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