<!DOCTYPE html>

<?php
	$guest_con = mysqli_connect("127.0.0.1", "root","","db_guest");
	if(mysqli_connect_errno($guest_con))
	{
		$good =  "Guest Connection Failed";
	}
	else
		$good = "good";
?>
<html lang="en"><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
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
            <li class="active"><a href="#">Home</a></li>
            <li><a href="#about">About</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>

    <div class="container">

      <div class="starter-template">
	<h2> <?php if($good == 'good')
		    {echo "Connected to database!";}?> <h2>
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
                    <li> Customer1 </li>
                    <li> Customer2 </li>
                    <li> Customer3 </li>
                    <li> Customer4 </li> 
                    <li> Customer5 </li>
                    <li> Customer6 </li>
                    <li> Customer7 </li>
                    <li> Customer8 </li>
                    <li> Customer9 </li>
                    <li> Customer10 </li>
                  </ol>
                </div>
              </div>

          <p align = "center">
            <button type="button" class="btn btn-lg btn-default">ADD+</button>
          </p>

    </div><!-- /.container -->

    <div class="col-md-6">
            <h1 align = "center">ACTIONS</h1>
			<div align = "center" >
				<form method="Post" action= "query.php">
					<select name = "customer">
						<?php 
							$sql = mysqli_query($guest_con, "SELECT ID, Guest FROM Guests");
							while ($row = mysqli_fetch_array($sql))
							{
								echo "<option value= ".$row['ID'].">" . $row['Guest'] . "</option>";
							}
						?>
					</select>
					<br>
					<br>
					<input type="submit" class="btn btn-lg btn-primary" value = "Page" align = bottom>
					<input type="button" class="btn btn-lg btn-success" value = "Get Order">
					<input type="button" class="btn btn-lg btn-danger" value = "DELETE">
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
                    <li> Customer1 </li>
                    <li> Customer2 </li>
                    <li> Customer3 </li>
                    <li> Customer4 </li>
                    <li> Customer5 </li>
                    <li> Customer6 </li>
                    <li> Customer7 </li>
                    <li> Customer8 </li>
                    <li> Customer9 </li>
                    <li> Customer10 </li>
	            <li> <?php print "Hello World" ?> </li>
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
