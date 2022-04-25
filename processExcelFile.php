<?php
  // PHP file below is for parsing Excel file once imported button is clicked
  require_once('include/SimpleXLSX.php');
  require_once('include/dbconn.php');
  
  // if file was uploaded
  if (isset($_FILES['file'])) {
    // if file uploaded is Excel file and can be parsed
    if ( $xlsx = SimpleXLSX::parse($_FILES['file']['tmp_name']) ) {
		
      $dim = $xlsx->dimension();
      $cols = $dim[0];

      // $key => $row is a key-value pair that's easier to read
      foreach ( $xlsx->rows() as $key => $row ) {
        if ($key == 0) continue; // skip first row which is the column headers

        // For the acquired date column, Excel includes hidden time (00:00:00) which isn't needed. So trim the time out.
        $sql = "INSERT INTO dbo.inventory (tag, description, serial, model, manufacturer, building, room, note, acquiredDate, amount)
              VALUES ('".$row[0]."', '".$row[1]."', '".$row[2]."', '".$row[3]."', '".$row[4]."', '".$row[5]."', '".$row[6]."', '".$row[7]."', '".substr($row[8], 0, 10)."', '".$row[9]."')";

          $execute = sqlsrv_query($conn, $sql);
      }
    } 
    else
      error_log(SimpleXLSX::parseError());
  }
  
  sqlsrv_close($conn);
  
  exit( header("Location: [GO BACK TO INVENTORY PAGE]") );
?>
