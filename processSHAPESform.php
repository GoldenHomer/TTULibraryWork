<?php
  header('Content-Type: text/plain; charset=utf-8');
  
  $itemTitle = $_POST['itemTitle'];
  $collectionID = $_POST['collectionID'];
  $itemDescription = $_POST['itemDescription'];
  $itemRights = $_POST['itemRights'];
  $itemExtent = $_POST['itemExtent'];
  $dateCreated = $_POST['dateCreated'];
  $itemIsPartOf = $_POST['itemIsPartOf'];
  $totalParts = $_POST['totalParts'];
  $length = $_POST['length'];
  $itemWidth = $_POST['itemWidth'];
  $itemDepth = $_POST['itemDepth'];
  $itemCreator = $_POST['itemCreator'];
  $itemSubject = $_POST['itemSubject'];
  $itemSubject2 = $_POST['itemSubject2'];
  $itemSubject3 = $_POST['itemSubject3'];
  $previousPrints = $_POST['previousPrints'];
  $requestedFor = $_POST['requestedFor'];
  $oclc = strtolower($_POST['oclc']);
  $modMaterial = $_POST['modMaterial'];
  $supMaterial = $_POST['supMaterial'];
  $buildTime = $_POST['buildTime'];
  $cost = $_POST['cost'];

  //generate a hexadecimal value for objectID and folder name for images of object.
  $bytes = openssl_random_pseudo_bytes(10);
  $objectID = bin2hex($bytes);
  
  require_once("../includes/dbconn.php");
  
  // Below is for uploading object image(s) into a newly created folder. Each object will have its own folder.
  // Image file type/extension, size and dimensions are verified client-side, so no need to do it server-side unless necessary.
  // 4 is no file selected error code
  if($_FILES['imageToUpload']['error'][0] != 4){
    // Can't submit metadata about object without pics of object	  
    $sql = "INSERT INTO dbo.Objects 
    	   	(collectionID, itemTitle, itemDescription, itemRights, itemExtent, dateCreated, itemPartNumber, itemTotalParts, itemWidth, itemDepth, itemCreator,
		 itemSubject1, itemSubject2, itemSubject3, previousPrints, requestedFor, oclc, objectID, itemLength, modMaterial, supMaterial, buildTime, cost)
    	    VALUES 
	    	(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
		 ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
	    
    $params = array($collectionID,
      		    $itemTitle,
      		    $itemDescription,
      		    $itemRights,
       		    $itemExtent,
      		    $dateCreated,
      		    $itemIsPartOf,
      		    $totalParts,
      		    $itemWidth,
      		    $itemDepth,
      		    $itemCreator,
      		    $itemSubject,
      		    $itemSubject2,
      		    $itemSubject3,
     		    $previousPrints,
      		    $requestedFor,
      		    $oclc,
      		    $objectID,
      		    $length,
      		    $modMaterial,
      		    $supMaterial,
      		    $buildTime,
      		    $cost);
    
    $stmt = sqlsrv_query($conn, $sql, $params);
	  
    // E_USER_ERROR is an error type that halts PHP execution, which is needed here. Will also log error.
    if(!$stmt)
      trigger_error("Could not insert to database. Check table and credentials.", E_USER_ERROR);
	    
    $eventSQL = "INSERT INTO dbo.DBEvents (event) VALUES (?)";
    $query = sqlsrv_query( $conn, $eventSQL, array('Object was inserted.') );
    // If there's something wrong with the event query, write to error log but don't stop PHP execution since the event message isn't critical
    if(!$query)
      error_log("Database error: Object event could not be written to database.\r\n");
    
    sqlsrv_close($conn);
	  
    // Number of images uploaded
    $imageCount = count($_FILES["imageToUpload"]["name"]);
    
    // create directory for image(s)
    $newFolderPath = $_SERVER['DOCUMENT_ROOT'];
    $newFolderPath .= "\\images\\" . $objectID;
    mkdir($newFolderPath, 0777);

    for($i = 0; $i<$imageCount; $i++) {
      move_uploaded_file($images[$i], $newFolderPath."/".basename($_FILES["imageToUpload"]["name"][$i]));
    }
  }
  else
    error_log("Error: No image(s) of object was selected to upload. Object metadata not submitted.");
?>
