<html>
<body>

<?php
	$guest_con = mysqli_connect("127.0.0.1", "root","","db_guest");
	if(mysqli_connect_errno($guest_con))
	{
		$good =  "Guest Connection Failed";
	}
	else
		$good = "good";
?>

<?php echo $good; ?>
<?php mysqli_query($guest_con, "UPDATE Guests SET Page = 1 WHERE id = ". $_POST["customer"]) ?>
Request for cust <?php echo $_POST["customer"]; ?> sent <br>
