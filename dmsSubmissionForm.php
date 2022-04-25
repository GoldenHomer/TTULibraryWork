<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>DMS Inventory</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/normalize.css">
  </head>

  <body>
    <!--[if IE]>
      <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
    <![endif]-->

    <!-- CONTENT START -->
    <?php include('include/header.php');

      // check who comes to this index file, allow only those in $submissing array in usergroups.php
      if(!isset($_SESSION['eRaiderUsername']) || empty($_SESSION['eRaiderUsername']))
        exit(header("Location: https://www.depts.ttu.edu/library/"));
      else {
        if(!in_array($_SESSION['eRaiderUsername'], $submitting))
        exit(header("Location: update.php"));
      }
    ?>

    <div class="container">
      <div class="page-header">
        <h2>Equipment Entry</h2>
      </div>

      <form class="form-horizontal" action="php/processEntry.php" method="post" role="form" onsubmit="return checkQuantity(this)">
        <div class="form-group">
          <label for="eqName" class="col-sm-2 control-label">Equipment Name</label>
          <div class="col-sm-10">
            <select class="form-control" name="eqName">
              <?php
                require('include/dbconn.php');

                //Start display equipment names from dbo.equipmentNames
                $query = "SELECT name FROM dbo.equipmentNames";
                $result = sqlsrv_query($conn, $query);
                if(!$result) error_log("Cannot get equipment names. Check dbconn.php or dbo.equipmentNames");

                if(sqlsrv_has_rows($result)){
                    while($row = sqlsrv_fetch_array($result)){
                      echo "<option value='".$row['name']."'>".$row['name']."</option>";
                    }
                }
                sqlsrv_close($conn);
              ?>
            </select>
          </div>
        </div>

      <div class="form-group">
        <label for="eqType" class="col-sm-2 control-label">Equipment Type</label>
        <div class="col-sm-10">
          <select class="form-control" name="eqType">			
            <?php
              require('include/dbconn.php');

              //Start display equipment names from dbo.equipmentNames
              $query = "SELECT * FROM dbo.equipmentTypes";
              $result = sqlsrv_query($conn, $query);
              if(!$result) error_log("Cannot get equipment types. Check dbconn.php or dbo.equipmentTypes");

              if(sqlsrv_has_rows($result)){
                while($row = sqlsrv_fetch_array($result)){
                  echo "<option value='".$row['acronym']."'>".$row['acronym']." - ".$row['type']."</option>";
                }
              }
              sqlsrv_close($conn);
            ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label for="quantity" class="col-sm-2 control-label">Quantity</label>
        <div class="col-sm-10">
          <input type="number" class="form-control" id="quantity" name="quantity" min="1" max="99" required>
        </div>
      </div>

      <div class="form-group">
        <label for="price" class="col-sm-2 control-label">Price</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="price" name="price">
        </div>
      </div>

      <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
          <button type="submit" class="btn btn-primary">Submit</button>
        </div>
      </div>
      <!-- hidden eRaider username input for equipment history-->
      <input type="hidden" name="username" value="<?php echo $_SESSION["[fakeEradUsrName]"];?>">
    </form>

    </div>

    <script src="js/jquery-1.11.3.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/modernizr-3.8.0.min.js"></script>
    <script>
      function checkQuantity(form) {
        var quantity = $('#quantity').val(),
            price = $('#price').val();

        // if more than 10 items in Quantity field, confirm with user
        if(quantity > 10){
          // do not submit if cancel is clicked
          if(!confirm("You want to submit more than 10 items. Are you sure?")) {
            event.preventDefault;
            return false;
          }
        }

        // If no price is entered
        if(price == ""){
          if(!confirm("You left the price field empty. Still want to submit?")) {
            event.preventDefault;
            return false;
          }
        }
      }
    </script>
  </body>
</html>
