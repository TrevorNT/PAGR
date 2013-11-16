<!DOCTYPE html>
 
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
<html lang="en"><head>
<script type="text/javascript">
	function addWalkInCust()
	{
		WINDOW = window.open("/add_walkin.php","addCust","height = 400, width = 300, menubar = 0, scrollbars = 0");
		X = (screen.width-300)/2
		Y = (screen.height-400)/2
		WINDOW.moveTo(X,Y)
		WINDOW.focus()
	}

	function addReservationCust()
	{
		window.open("/add_reservation.php","addCust","height = 800, width = 500, menubar = 0, scrollbars = 0");
	}

</script>
<style type="text/css">
	#table_container
	{ 
		width: 400px
		border: 1px solid #aaa
	}
</style>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta http-equiv="refresh" content="25" > 
    <link rel="shortcut icon" href="http://getbootstrap.com/assets/ico/favicon.png">

    <title>Restaurant Control Panel</title>

    <!-- Bootstrap core CSS -->
    <link href="media/css/bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="media/Starter%20Template%20for%20Bootstrap_files/starter-template.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="../../assets/js/html5shiv.js"></script>
      <script src="../../assets/js/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">PAGR</a>
        </div>
        <div class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li class="active"><a href="/">Home</a></li>
            <li><a href="#about">About</a></li>
            <li><a> <font color= "FFFF66">Current Wait Time: <?php echo "" ?> </font></a></li>  
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>

    <div class="container">
      <div class="starter-template">
        <h1>PAGR Restaurant Control Panel</h1>
      </div>

 <div class="row">
        <div class="col-md-3">
         <div class="panel panel-default">
                <div class="panel-heading">
                  <h3 class="panel-title" align = "center">Walk-In</h3>
                </div>
                <div class="panel-body">
                  <ol>
		  			<?php
						$table = $PAGR_database->query("SELECT patron_id, name FROM patrons_t WHERE reservation_time IS NULL ORDER BY patron_id");
						while ($row = mysqli_fetch_array($table))
						{
							echo "<li>   ".$row['patron_id']."  ". $row['name'] . "</li>";
						}
					?>
                  </ol>
                </div>
              </div>

          <p align = "center">
            <button type="button" onclick="addWalkInCust()" class="btn btn-lg btn-default">ADD+</button>
          </p>

    </div><!-- /.container -->

    <div class="col-md-6">
            <h4 align = "center"> <?php echo "hello world" ?> </h4>
            <h3 align = "center">Select Customer</h3>
			<div align = "center" >
				<form method="Post" action= "action_button.php">
					<select name = "customer">
						<?php 
							$table = $PAGR_database->query("SELECT patron_id, name FROM patrons_t");
							while ($row = mysqli_fetch_array($table))
							{
								echo "<option value= ".$row['patron_id'].">".$row['patron_id']."  ". $row['name'] . "</option>";
							}
						?>
					</select>
					<br>
					<br>
					<input type="submit" class="btn btn-lg btn-primary" value = "Page" align = bottom>
					<input type="button" class="btn btn-lg btn-success" value = "Get Order">
					<input type="button" class="btn btn-lg btn-default" value = "Seat Customer"> 
					<br>
					<br>
					<input type="button" class="btn btn-lg btn-danger" value = "DELETE"> 
				</form>

				<h3 align = "center"> Table Setup</h3>
				<h4 align = "center"> <font color = "FF6666"><b> RED </b></font> 
				    tables are <font color = "FF6666"><b>OCCUPIED </b></font> </h4>
				    
				<div align = "center" class= "container" id= "table_container">
					<script type="text/javascript" src="/raphael.js"></script>
					<!-- Call the php driver to create the tables. -->
					<?php include "tables.php"; createTables($PAGR_database); ?>
				</div>
				<form method="Post" action= "unmark_tables.php">
				    
				    <h4> Unmark Table:
				    <select name = "table">
				    <?php
				        $OCC_TABLES = $PAGR_database->query("SELECT table_id 
				                                             FROM table_t
				                                             WHERE isOccupied = 1");
				                                             
				        while ($ROW = mysqli_fetch_array($OCC_TABLES))
				        {
				        
				            
				            echo '<option value= '.$ROW['table_id'].'> 
				            Table '.$ROW['table_id']."&nbsp&nbsp&nbsp".'</option>';
				            
				        }
				        
				    ?>
				    </select>
				    <input type="submit" value = "Submit"> 
				</form>
		    </div>
    </div>
    <div class="row">
        <div class="col-md-3">
         <div class="panel panel-primary">
                <div class="panel-heading">
                  <h3 class="panel-title" align = "center">Reservations</h3>
                </div>
                <div class="panel-body">
                  <ol>
		  			<?php
						$table = $PAGR_database->query("SELECT patron_id, name, 
						                                reservation_time FROM patrons_t WHERE 
						                                reservation_time IS NOT NULL ORDER BY 
						                                reservation_time");
						while ($row = mysqli_fetch_array($table))
						{
							echo "<li>   ".$row['patron_id']."  ". $row['name'] ."  ".$row['reservation_time']."</li>";
						}
					?>
                  </ol>
                </div>
         </div>
         <p align = "center">
            <button type="button" class="btn btn-lg btn-primary">ADD+</button>
         </p>
       </div>
    </div>


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="Starter%20Template%20for%20Bootstrap_files/jquery.js"></script>
    <script src="Starter%20Template%20for%20Bootstrap_files/bootstrap.js"></script>
  

</body></html>
