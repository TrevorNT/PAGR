<html>

<script type="text/javascript">
function createTableJS(x, y, width, height, table_id, isOccupied, init) 
{ 

    // Create the canvas on the first call to the function.
    if (init == 1)
    {
       createTableJS.paper = new Raphael(document.getElementById("table_container"), 400, 250);
    }
    
    paper = createTableJS.paper;

    // Create the table.
    var rectangle = paper.rect(x, y, width, height);
    var txt_1 = paper.text(x + (.5)*width, y + (.5)*height, table_id);
    txt_1.attr({ "font-size": 32});
    
    // Fill the rectangle with the proper color.
    if (isOccupied ==  1) 
    {
        // Fill red.
        rectangle.attr({"fill": "#FF6666"});
    }
    
    else
    {
        // Fill green.
        rectangle.attr({"fill": "#66FF99"});
    }               
}
  
</script>
<div>
    <?php
    function createTables($PAGR_database)
    {
        
        $TABLE_COUNT = $PAGR_database->query("SELECT * FROM table_t");
        $TABLE_COUNT =  $TABLE_COUNT->num_rows;
        $TABLES = array();
        
        // Get the table coordinates from the database.
        $DATABASE_TABLE_INFO = $PAGR_database->query("SELECT * FROM table_t");
        
        while($A_ROW = $DATABASE_TABLE_INFO->fetch_array(MYSQLI_ASSOC))
        {
            $TABLES_ROW = array();
            $TABLE_INFO = array();
            
            array_push($TABLES_ROW, $A_ROW['table_id']);
            
            array_push($TABLE_INFO, $A_ROW['capacity'], $A_ROW['x_pos'], $A_ROW['y_pos'],
                       $A_ROW['width'], $A_ROW['height'], $A_ROW['isOccupied']);
                       
            // The list [table_id, [table_info]]
            array_push($TABLES_ROW, $TABLE_INFO);
            
            // Add the table to the overall tables list.
            array_push($TABLES, $TABLES_ROW);
            
        }
        
        
        // Draw each table using the createTableJS javascript function.
        for($i = 0; $i < $TABLE_COUNT; $i++)
        {
            $A_TABLE = $TABLES[$i];
       
           
            // Gather all the attributes
            $TABLE_ID = $A_TABLE[0];
            
            $TABLE_X_POS = $A_TABLE[1][1];
            $TABLE_Y_POS = $A_TABLE[1][2];

            $TABLE_WIDTH = $A_TABLE[1][3];
            $TABLE_HEIGHT = $A_TABLE[1][4];
            
            $TABLE_OCCUPIED = $A_TABLE[1][5];
            
            if($i == 0)
            {
                $INIT = 1;
            }
            else
            {
                $INIT = 0;
            }
             
            echo
             '<script type= "text/javascript"> 
                createTableJS('.$TABLE_X_POS.', '.$TABLE_Y_POS.', 
                '.$TABLE_WIDTH.', '.$TABLE_HEIGHT.', '.$TABLE_ID.', 
                '.$TABLE_OCCUPIED.', '.$INIT.');
             </script>'; 
             
        }    
    }
?>
</div>
</html>
