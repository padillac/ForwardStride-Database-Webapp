<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="/static/main.css">
  <?php include $_SERVER['DOCUMENT_ROOT']."/static/scripts/connectdb.php"; ?>
  <title>Staff Timesheet</title>
</head>

<body>

  <header>
    <h1>Staff Timesheet</h1>
    <nav> <a href="../"><button id="back-button">Back</button></a>
      <a href="/"><button id="home-button">Home</button></a>
    </nav>
  </header>


      <form class="main-form" action="staff-record-hours.php" method="post">

        <p>Name:</p>
        <input type="text" name="staff" list="staff-list" required>
          <datalist id="staff-list">
            <?php
              $staffNames = pg_fetch_all_columns(pg_query($db_connection, "SELECT name FROM workers WHERE staff = TRUE AND (archived IS NULL OR archived = '');"));
              foreach ($staffNames as $name) {
                echo "<option value='{$name}'>";
              }
            ?>
          </datalist>

        <p>Type of Work:</p>
        <input type="text" name="work-type" list="work-type-list" required>
          <datalist id="work-type-list">
            <?php
              //POTENTIALLY CHANGE THIS SECTION TO DISPLAY A LIST OF STAFF WORK OPTIONS INSTEAD OF VOLUNTEER ONES
              //-------------------------------------------------------------------------
              $staffShiftTypes = pg_fetch_all_columns(pg_query($db_connection, "SELECT unnest(enum_range(NULL::STAFF_WORK_TYPE));"));
              foreach ($staffShiftTypes as $value) {
                echo "<option value='{$value}'>";
              }
              //-----------------------------------------------------------------
            ?>
          </datalist>

        <p>Date:</p>
        <input type="date" name="date-of-hours" value="<?php echo date('Y-m-d'); ?>" required>

        <p>Number of hours</p>
        <input type="number" name="hours" required>

        <br><br>
        <input type="submit" value="Submit">
      </form>



</body>

</html>
