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
?>
<?php
	/**
	 * Creates a new reservation given $_REQUEST['handset_id'].
	 * 
	 * @return Integer A positive integer representing the reservation ID if successful, -1 if not.
	 */
	function create_reservation() {
		echo "create a reservation!";
		
		// This function gives a handset a reservation.
		// if (not exists(reservation_id for given handset_id))
		//		create new reservation;
		//		return new_reservation_id;
		// else
		//		return -1;
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
	function should_page() {
		// PRECONDITION: check passed variables
		if (!isset($_REQUEST['reservation_id'])) die("ERROR: reservation_id required");
		
		// This function tells the handset to page the user.
		// if (exists(reservation_id))
		//		get should_ping from DB;
		//		return should_ping;
	}
	
	/**
	 * 
	 */
	function ack_page() {
		// This function tells the handset to stop paging the user.  (It is
		// called from the handset to acknowledge that the user has picked up
		// the page.)
		// if (exists(reservation_id))
		//		set should_ping = 0 to DB;
		//		return true;
	}
?>
<?php
	// Simply calls the function given in $_REQUEST['pagr_exec'] provided
	// it is within this namespace.  (And provided it exists <_< )
	if (!isset($_REQUEST['pagr_exec'])) die('ERROR: pagr_exec must be defined!');
	call_user_func("pagr\app_bridge\\" . $_REQUEST['pagr_exec']);
?>