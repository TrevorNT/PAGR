<!DOCTYPE html>

<html lang="en"><head>

<meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="http://getbootstrap.com/assets/ico/favicon.png">
    
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
    <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <link rel="stylesheet" href="/resources/demos/style.css" />

    <title>Add Reservation Customer</title>

    <!-- Bootstrap core CSS -->
    <link href="../media/css/bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="../media/Starter%20Template%20for%20Bootstrap_files/starter-template.css" rel="stylesheet">

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
          <a class="navbar-brand" href="#">PAGR</a>
        </div>
      </div>
    </div>

    <div class="container" align = "left">

     <div class="starter-template" align = "left">
	<form method="Post" action= "add_reserv_customer.php">

        <!-- jQuery calendar -->
        <script>
        $(function() {
        $( "#datepicker" ).datepicker();
        });
        </script>
        
        <font face="courier new" align = "left">Name:&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp  
		<input type="text" name="first_name"> </font></br>
		
		<font face="courier new">Phone:&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp 
		<input type="text" name="phone_number"> </font></br>
		
		<font face="courier new">Party Size:&nbsp  <input type="text" name="party_size"></font></br>
		<br>
        
        <p><font face="courier new">Date:&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp 
        <input type="text" READONLY id=datepicker name="datepicker" /></font></p>
        <hr>
        <div align = "center">
        
        <font face="courier new" align = "left"> Time:&nbsp&nbsp </font>
        
        <!-- Dropdown menus for the time. -->
        <select name = "hour">
            <?php
                echo "<option value = hour> HOUR </option>";
                for($i = 8; $i < 24; $i++)
                {
                    echo "<option value = $i> $i </option>";
                }
                
            ?>
            
         </select>
         : 
         <select name = "minute">
            <?php
                echo "<option value = minute> MIN </option>";
                for($i = 0; $i < 59; $i = $i + 15)
                {
                    if($i < 10)
                    {
                        echo "<option value = $i> 0$i </option>";
                    }
                    
                    else
                    {
                        echo "<option value = $i> $i </option>";
                    }
                }
                
            ?>
            
         </select>
         
         </div>
        <br>
        <input type="submit" class="btn btn-lg btn-primary" value = "Submit" align = bottom>
        
     </form>
    </div>
    </div>


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="Starter%20Template%20for%20Bootstrap_files/jquery.js"></script>
    <script src="Starter%20Template%20for%20Bootstrap_files/bootstrap.js"></script>
  

</body></html>
