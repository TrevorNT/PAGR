<html>
<body>

<?php
	include "pagr_db.php";
	$PAGR_database = get_pagr_db_connection();
	if(mysqli_connect_errno($PAGR_database))
	{
		$good =  "Guest Connection Failed";
	}
	else
		$good = "good";
?>

<?php echo $good; ?>
<?php $PAGR_database->query("UPDATE patrons_t SET page = 1 WHERE patron_id = ". $_POST["customer"]) ?>
Request for cust <?php echo $_POST["customer"]; ?> sent <br>

</body>
</html>
