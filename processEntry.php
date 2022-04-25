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
  $countQuery = "SELECT ".$columnName." FROM dbo.typeCounts";
  $countResource = sqlsrv_query($conn, $countQuery);

  // $currentCount is an integer
  $currentCount = sqlsrv_fetch_array($countResource)[$columnName];
 
  // $stringToInt and endLoopInt should be integers
  $endLoopInt = $currentCount + (int)$quantity;
  
  // How the AV# is generated
  for($currentCount; $currentCount < $endLoopInt; $currentCount++){
    if($currentCount < 10)
      $zeros = "0000";
    elseif(10 <= $currentCount && $currentCount < 100)
      $zeros = "000";
    elseif(100 <= $currentCount && $currentCount < 1000)
      $zeros = "00";
    elseif(1000 <= $currentCount && $currentCount < 10000)
      $zeros = "0";
    elseif(10000 <= $currentCount)
      $zeros = "";

    $AVnum = $type.$zeros.(string)$currentCount;

    $insertSQL = "INSERT INTO dbo.entries (name, type, AV#, price)
                  VALUES ('$name', '$type', '$AVnum', '$price')";

    $entryQuery = sqlsrv_query($conn, $insertSQL);

    // Need to get equipment's unique ID to insert into equipment history table
    // using the AV#, assuming the AV is unique.
    $getSQL = "SELECT id FROM dbo.entries WHERE AV#='$AVnum'";

    $getQuery = sqlsrv_query($conn, $getSQL);
    if($getQuery === false) die( print_r( sqlsrv_errors(), true));

    // Make the first (and in this case, only) record of the query return result available for reading.
    if(sqlsrv_fetch( $getQuery ) === false)
      die( print_r( sqlsrv_errors(), true));

    $id = sqlsrv_get_field($getQuery, 0);



    // Keep track of equipment history event
    $now = date('m/j/y h:i:s A');
    $historySQL = "INSERT INTO dbo.equipHistory (username, event, timestamp, AV#, eqID)
             	   VALUES ('$eRaiderusername', 'Equipment record was created', '$now', '$AVnum', '$id')";

    $historyQuery = sqlsrv_query($conn, $historySQL);
  }
  
  // this SQL query doesn't need the WHERE clause as there should only be one record in dbo.typeCounts
  $updateCount = "UPDATE dbo.typeCounts SET ".$columnName."=".$currentCount;
  $updateQuery = sqlsrv_query($conn, $updateCount);
  
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
