<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="/static/main.css">
  <?php include $_SERVER['DOCUMENT_ROOT'] . "/static/scripts/connectdb.php";?>
  <title>Admin | New Horse Care Shift</title>
</head>

<body>

  <header>
    <h1>New Horse Care Shift</h1>
    <nav>
      <a href="index.php"><button>Create Another</button></a>
      <a href="../"><button id="back-button">Back</button></a>
      <a href="/"><button id="home-button">Home</button></a>
    </nav>
  </header>

  <?php
    //Process form input
    //get array of dates and times
    $date = $_POST['start-date'];
    $end_date = $_POST['end-date'];
    $dateTimeTriplets = array();

    $all_weekdays_times = "";
    $weekdaysAdded = array();
    while (strtotime($date) <= strtotime($end_date)) {
      $dayOfWeek = date('l', strtotime($date));
      if (in_array($dayOfWeek, $_POST)) {
        $startTime =  $_POST[strtolower($dayOfWeek).'-start-time'];
        $endTime = $_POST[strtolower($dayOfWeek).'-end-time'];
        $dateTimeTriplets[$date] = array($startTime, $endTime);
        if (!in_array($dayOfWeek, $weekdaysAdded)){
          $all_weekdays_times = $all_weekdays_times . $dayOfWeek . "," . $startTime . "," . $endTime . ";";
          $weekdaysAdded[] = $dayOfWeek;
        }
      }

      //looper
      $date = date ('Y-m-d', strtotime('+1 day', strtotime($date)));
    }
    //Convert other user selections to database ids

    function to_pg_array($set) {
      settype($set, 'array'); // can be called with a scalar or array
      $result = array();
      foreach ($set as $t) {
          if (is_array($t)) {
              $result[] = to_pg_array($t);
          } else {
              $t = str_replace('"', '\\"', $t); // escape double quote
              if (! is_numeric($t)) // quote only non-numeric values
                  $t = '"' . $t . '"';
              $result[] = $t;
          }
      }
      return '{' . implode(",", $result) . '}'; // format
    }

    $leaderID = pg_fetch_row(pg_query($db_connection, "SELECT id FROM workers WHERE name LIKE '{$_POST['leader']}'"))[0];
    if (!$leaderID) {
      $leaderID = 'null';
    }
    $volunteerIDList = array();
    foreach ($_POST['volunteers'] as $key => $value) {
      $id = pg_fetch_row(pg_query($db_connection, "SELECT id FROM workers WHERE name LIKE '{$value}'"))[0];
      $volunteerIDList[] = $id;
    }
    $volunteerIDList = to_pg_array($volunteerIDList);

    //Create SQL query
    $query = "INSERT INTO horse_care_shifts (care_type, date_of_shift, start_time, end_time, all_weekdays_times, leader, volunteers) VALUES";
    foreach ($dateTimeTriplets as $date => $timeArray) {
      $query = $query . "('{$_POST['shift-type']}', '{$date}', '{$timeArray[0]}', '{$timeArray[1]}', '$all_weekdays_times', {$leaderID}, '{$volunteerIDList}'),";
    }

    $query = chop($query, ",") . ";";



    //Modify database
    $result = pg_query($db_connection, $query);
    if ($result) {
      echo "<h3 class='main-content-header'>Success</h3";
    } else {
      echo "<h3 class='main-content-header>An error occured.</h3><p class='main-content-header'>Please try again, ensure that all data is correctly formatted.</p>";
    }
  ?>



</body>

</html>