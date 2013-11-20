<?php
	/**
	 * PAGR Database Access
	 *
	 * This file contains the get_pagr_db_connection function which allows a user to
	 * get an instance of a PAGR database connection.  It is a wrapper to mysqli
	 * that simplifies access to the PAGR DB and obfuscates the database location,
	 * username, and password from accessing applications.
	 *
	 * It reads from the pagr.ini file to determine connection settings for the
	 * database connection.
	 *
	 * To use, just call get_pagr_db_connection() and you will be returned a mysqli
	 * object to use.
	 *
	 * As per PAGR bug #7, please disable IPv6 on machines you call this database
	 * connector from.  Unless you like really long page load times, that is.
	 *
	 * @author Trevor Toryk
	 * @license Proprietary
	 * @package com.pagr.server
	 */
?>
<?php
	/**
	 * get_pagr_db_connection()
	 *
	 * Simply returns a mysqli object with the necessary parameters to access the
	 * PAGR database built in.  (No sorry, we don't want you to be able to see them.)
	 *
	 * @return mixed A new mysqli object pre-initialized with the database parameters.
	 */
	function get_pagr_db_connection() {
		$DB_UNAME;		// The username for the database.
		$DB_PASS;		// The user's password.
		$DB_LOCATION;	// The DB's location on the internet.
		$DB_PORT;		// The port the database is accessed on (usually 3306 but can be manually configured.)
		
		// Read pagr.ini and get the values for the variables above.
		$INI_FILE = fopen($_SERVER["DOCUMENT_ROOT"] . "/PAGR/php/pagr.ini", "r");
		if ($INI_FILE == false) die("ERROR: pagr.ini not found!");
		
		// NOTE: this line will make it work with Windows-based but NOT Unix-based files
		//		because Windows line endings are stupid.
		while ($INI_LINE = fscanf($INI_FILE, "%[a-zA-z0-9_.-]=%[a-zA-Z0-9_.-];\r\n")) {
			if ($INI_LINE[0] == "db_uname") $DB_UNAME = $INI_LINE[1];
			elseif ($INI_LINE[0] == "db_pass") $DB_PASS = $INI_LINE[1];
			elseif ($INI_LINE[0] == "db_location") $DB_LOCATION = $INI_LINE[1];
			elseif ($INI_LINE[0] == "db_port") $DB_PORT = $INI_LINE[1];
		}
		
		fclose($INI_FILE);
		
		if (strlen($DB_UNAME) == 0 || strlen($DB_PASS) == 0 || strlen($DB_LOCATION) == 0 || strlen($DB_PORT) == 0) die("ERROR: malformed pagr.ini file!");
		
		return new mysqli($DB_LOCATION, $DB_UNAME, $DB_PASS, "pagr_s", $DB_PORT);
	}
?>