<?php
  // input values from the form in the index file
  $name = $_POST['eqName'];
  $type = substr($_POST['eqType'], 0, 3);
  $quantity = $_POST['quantity'];
  $eRaiderusername = $_POST['username'];
  $price = $_POST['price'];
  
  
  // Already DB error handling in dbconn.php
  require('../include/dbconn.php');
  
  // $columnName used to get appropriate column from DB.
  $columnName = strtolower($type)."Count";
  
  // $itemType used to get the current count based on type submitted
  $countQuery = "SELECT $columnName FROM dbo.typeCounts";
  $countResource = sqlsrv_query($conn, $countQuery);

  // $currentCount is an integer
  $currentCount = sqlsrv_fetch_array($countResource)[$columnName];
 
  // $stringToInt and endLoopInt should be integers
  $endLoopInt = $currentCount + (int)$quantity;
  
  // Unique AV# is generated, no longer necessary as AV# can be edited client side
  for($currentCount; $currentCount < $endLoopInt; $currentCount++){
    // pad left side of current count string with zeros as necessary to generate an ID such as CAM00007 or CAM12345
    $zeros = str_pad((string)$currentCount, 5, "0", STR_PAD_LEFT);

    $AVnum = $type . $zeros;
    
    $query = "INSERT INTO dbo.entries (name, type, AV#, price)
                  VALUES (?, ?, ?, ?)";
	  
    $params = array(&$name, &$type, &$AVnum, &$price);
    $stmt = sqlsrv_prepare($conn, $query, $params);
    sqlsrv_execute($stmt);


    // Need to get equipment's unique ID to insert into equipment history table
    $selectQuery = "SELECT id FROM dbo.entries WHERE AV# = ?";
    $sql = sqlsrv_prepare( $conn, $selectQuery, array(&$AVnum) );
    $result = sqlsrv_execute($sql);  

    // Make the first (and only) row of the query result available for reading.
    if( sqlsrv_has_rows($result) ) {
	while( $row = sqlsrv_fetch_array($result) ) {
	    $id = $row['id'];
	}
    }

    // Keep track of equipment history event
    $now = date('m/j/y h:i:s A');
    
    $historyQuery = "INSERT INTO dbo.equipHistory (username, event, timestamp, AV#, eqID)
             	     VALUES (?, ?, ?, ?, ?)";
	  
    $params2 = array(&$eRaiderusername, 'Equipment record was created', &$now, &$AVnum, &$id);
    $sql2 = sqlsrv_prepare( $conn, $historyQuery, $params2 );
    sqlsrv_execute($stmt);
  }
  
  // this SQL query doesn't need the WHERE clause as there should only be one record in dbo.typeCounts
  $updateSQL = "UPDATE dbo.typeCounts SET $columnName = ?";
  $updateQuery = sqlsrv_query( $conn, $updateSQL, array(&$currentCount) );
  
  sqlsrv_close($conn);
  
  // If there's something wrong with updateQuery
  if(!$updateQuery){
    // Display to user
    echo("Your inventory could not be submitted as there is an issue with the database. Notify project developer");
    // For our PHP error log
    error_log("Something went wrong with database while being called by processEntry.php.");
    exit();
  }
  else {
    include('../include/header.php');
    header('refresh:5; url=../index.php');
	
    echo("<html>
            <head>
              <title>Entry submission successful</title>
              <link rel='stylesheet' href='../css/bootstrap.min.css'>
            </head>
            <body>
              <div class='jumbotron container'>
                <h1>Success</h1>
              <p>Entries were successfully submitted. You'll be redirected back in 5 seconds.</p>
              </div>
            </body>
          </html>
    ");
	
  }
?>
