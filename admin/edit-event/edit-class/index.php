<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="/static/main.css">
  <?php include $_SERVER['DOCUMENT_ROOT'] . "/static/scripts/connectdb.php";?>
  <title>Admin | Edit Class</title>
</head>

<body>

  <header>
    <h1>Edit Class</h1>
    <nav> <a href="../"><button id="back-button">Back</button></a>
      <a href="/"><button id="home-button">Home</button></a>
    </nav>
  </header>



  <?php
    if ($_POST['selected-class']) {
      echo "<h3 class='main-content-header'>{$_POST['selected-class']}</h3>";

      $selectedClassType = explode('; ', $_POST['selected-class'])[0];
      $selectedClientNames = explode(', ', explode('; ', $_POST['selected-class'])[1]);

      $clientIDList = array();
      foreach ($selectedClientNames as $name) {
        if ($name == "") {continue;}
        $IDQuery = "SELECT id FROM clients WHERE name LIKE '{$name}' AND (archived IS NULL OR archived = '');";
        $id = pg_fetch_row(pg_query($db_connection, $IDQuery))[0];
        $clientIDList[] = $id;
      }
      if (!$clientIDList) {
        $clientIDList[] = 1;
      }

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
      $clientIDPGArray = to_pg_array($clientIDList);
      $getClassIDsQuery = "SELECT id FROM classes WHERE class_type = '{$selectedClassType}' AND clients <@ '{$clientIDPGArray}' AND (archived IS NULL OR archived = '');";
      $classIDSQLObject = pg_fetch_all(pg_query($db_connection, $getClassIDsQuery));
      $classIDList = array();
      foreach ($classIDSQLObject as $row => $data) {
        $classIDList[] = $data['id'];
      }

      $classDataQuery = "SELECT * FROM classes WHERE classes.id = {$classIDList[0]};";

      $classData = pg_fetch_array(pg_query($db_connection, $classDataQuery), 0, PGSQL_ASSOC);

      $startDate = pg_fetch_array(pg_query($db_connection, "SELECT MIN (date_of_class) AS start_date FROM classes WHERE class_type = '{$selectedClassType}' AND clients <@ '{$clientIDPGArray}' AND (archived IS NULL OR archived = '');"), 0, 1)['start_date'];
      $endDate = pg_fetch_array(pg_query($db_connection, "SELECT MAX (date_of_class) AS end_date FROM classes WHERE class_type = '{$selectedClassType}' AND clients <@ '{$clientIDPGArray}' AND (archived IS NULL OR archived = '');"), 0, 1)['end_date'];


      $weekdaysBlocks = explode(";", $classData['all_weekdays_times']);
      $allWeekdaysTimesList = array();
      foreach ($weekdaysBlocks as $weekdayString) {
        if ($weekdayString == "") {continue;}
        $weekdayTriple = explode(",", $weekdayString);
        $allWeekdaysTimesList[$weekdayTriple[0]] = array($weekdayTriple[1], $weekdayTriple[2]);
      }

      $checkboxList = array("Monday" => "", "Tuesday" => "", "Wednesday" => "", "Thursday" => "", "Friday" => "", "Saturday" => "", "Sunday" => "", );
      foreach ($allWeekdaysTimesList as $day => $times) {
        $checkboxList[$day] = "checked";
      }

      $horseName = pg_fetch_array(pg_query($db_connection, "SELECT name FROM horses WHERE horses.id = {$classData['horse']};"), 0, 1)['name'];
      $instructorName = pg_fetch_array(pg_query($db_connection, "SELECT name FROM workers WHERE workers.id = {$classData['instructor']};"), 0, 1)['name'];
      $therapistName = pg_fetch_array(pg_query($db_connection, "SELECT name FROM workers WHERE workers.id = {$classData['therapist']};"), 0, 1)['name'];
      $esName = pg_fetch_array(pg_query($db_connection, "SELECT name FROM workers WHERE workers.id = {$classData['equine_specialist']};"), 0, 1)['name'];
      $leaderName = pg_fetch_array(pg_query($db_connection, "SELECT name FROM workers WHERE workers.id = {$classData['leader']};"), 0, 1)['name'];

      echo <<<EOT

      <form action="edit-class.php" method="post" class="main-form full-page-form">



        <p>Class Type:</p>
        <input type="text" name="old-class-type" value="{$classData['class_type']}" style="visibility: hidden;">
        <input type="text" name="class-type" list="class-type-list" value="{$classData['class_type']}" onclick="select()" required>
          <datalist id="class-type-list">
EOT;

              $query = "SELECT unnest(enum_range(NULL::CLASS_TYPE))::text EXCEPT SELECT name FROM archived_enums;";
              $result = pg_query($db_connection, $query);
              $classTypeNames = pg_fetch_all_columns($result);
              foreach ($classTypeNames as $key => $value) {
                echo "<option value='$value'>";
              }

      echo <<<EOT
          </datalist>

        <p>Dates:</p>
        <div style="max-width: 500px;">
          <label for="start-date">Start date:</label>
          <input type="date" id="start-date" name="start-date" value="{$startDate}" placeholder="from" required>
          <label for="end-date">End date:</label>
          <input type="date" id="end-date" name="end-date" value="{$endDate}" placeholder="to" required>
        </div>

        <div style="max-width: 430px;">
          <!-- MONDAY-->
          <label for="monday-checkbox">Monday: </label>
          <input type="checkbox" id="monday-checkbox" name="monday-checkbox" value="Monday" {$checkboxList['Monday']}>
          <label for="monday-start-time">from:</label>
          <input type="time" id="monday-start-time" name="monday-start-time" value="{$allWeekdaysTimesList['Monday'][0]}">
          <label for="monday-end-time">to:</label>
          <input type="time" id="monday-end-time" name="monday-end-time" value="{$allWeekdaysTimesList['Monday'][1]}">
          <!-- TUESDAY-->
          <label for="tuesday-checkbox">Tuesday: </label>
          <input type="checkbox" id="tuesday-checkbox" name="tuesday-checkbox" value="Tuesday" {$checkboxList['Tuesday']}>
          <label for="tuesday-start-time">from:</label>
          <input type="time" id="tuesday-start-time" name="tuesday-start-time" value="{$allWeekdaysTimesList['Tuesday'][0]}">
          <label for="tuesday-end-time">to:</label>
          <input type="time" id="tuesday-end-time" name="tuesday-end-time" value="{$allWeekdaysTimesList['Tuesday'][1]}">
          <!-- WEDNESDAY-->
          <label for="wednesday-checkbox">Wednesday: </label>
          <input type="checkbox" id="wednesday-checkbox" name="wednesday-checkbox" value="Wednesday" {$checkboxList['Wednesday']}>
          <label for="wednesday-start-time">from:</label>
          <input type="time" id="wednesday-start-time" name="wednesday-start-time" value="{$allWeekdaysTimesList['Wednesday'][0]}">
          <label for="wednesday-end-time">to:</label>
          <input type="time" id="wednesday-end-time" name="wednesday-end-time" value="{$allWeekdaysTimesList['Wednesday'][1]}">
          <!-- THURSDAY-->
          <label for="thursday-checkbox">Thursday: </label>
          <input type="checkbox" id="thursday-checkbox" name="thursday-checkbox" value="Thursday" {$checkboxList['Thursday']}>
          <label for="thursday-start-time">from:</label>
          <input type="time" id="thursday-start-time" name="thursday-start-time" value="{$allWeekdaysTimesList['Thursday'][0]}">
          <label for="thursday-end-time">to:</label>
          <input type="time" id="thursday-end-time" name="thursday-end-time" value="{$allWeekdaysTimesList['Thursday'][1]}">
          <!-- FRIDAY-->
          <label for="friday-checkbox">Friday: </label>
          <input type="checkbox" id="friday-checkbox" name="friday-checkbox" value="Friday" {$checkboxList['Friday']}>
          <label for="friday-start-time">from:</label>
          <input type="time" id="friday-start-time" name="friday-start-time" value="{$allWeekdaysTimesList['Friday'][0]}">
          <label for="friday-end-time">to:</label>
          <input type="time" id="friday-end-time" name="friday-end-time" value="{$allWeekdaysTimesList['Friday'][1]}">
          <!-- SATURDAY-->
          <label for="saturday-checkbox">Saturday: </label>
          <input type="checkbox" id="saturday-checkbox" name="saturday-checkbox" value="Saturday" {$checkboxList['Saturday']}>
          <label for="saturday-start-time">from:</label>
          <input type="time" id="saturday-start-time" name="saturday-start-time" value="{$allWeekdaysTimesList['Saturday'][0]}">
          <label for="saturday-end-time">to:</label>
          <input type="time" id="saturday-end-time" name="saturday-end-time" value="{$allWeekdaysTimesList['Saturday'][1]}">
          <!-- SUNDAY-->
          <label for="sunday-checkbox">Sunday: </label>
          <input type="checkbox" id="sunday-checkbox" name="sunday-checkbox" value="Sunday" {$checkboxList['Sunday']}>
          <label for="sunday-start-time">from:</label>
          <input type="time" id="sunday-start-time" name="sunday-start-time" value="{$allWeekdaysTimesList['Sunday'][0]}">
          <label for="sunday-end-time">to:</label>
          <input type="time" id="sunday-end-time" name="sunday-end-time" value="{$allWeekdaysTimesList['Sunday'][1]}">
        </div>

        <p>Arena:</p>
        <input type="text" name="arena" list="arena-list" value="{$classData['arena']}" onclick="select();">
          <datalist id="arena-list">
EOT;

              $query = "SELECT unnest(enum_range(NULL::ARENA))::text EXCEPT SELECT name FROM archived_enums;";
              $result = pg_query($db_connection, $query);
              $arenaNames = pg_fetch_all_columns($result);
              foreach ($arenaNames as $key => $value) {
                echo "<option value='$value'>";
              }
        echo <<<EOT
          </datalist>

        <p>Horse:</p>
        <input type="text" name="horse" list="horse-list" value="{$horseName}" onclick="select();">
          <datalist id="horse-list">
EOT;

              $query = "SELECT name FROM horses WHERE (archived IS NULL OR archived = '');";
              $result = pg_query($db_connection, $query);
              $horseNames = pg_fetch_all_columns($result);
              foreach ($horseNames as $key => $value) {
                echo "<option value='$value'>";
              }
        echo <<<EOT
          </datalist>

        <p>Tack:</p>
        <input type="text" name="tack" list="tack-list" value="{$classData['tack']}" onclick="select();">
          <datalist id="tack-list">
EOT;

              $query = "SELECT unnest(enum_range(NULL::TACK))::text EXCEPT SELECT name FROM archived_enums;";
              $result = pg_query($db_connection, $query);
              $tackNames = pg_fetch_all_columns($result);
              foreach ($tackNames as $key => $value) {
                echo "<option value='$value'>";
              }
        echo <<<EOT
          </datalist>

        <p>Special Tack:</p>
        <input type="text" name="special-tack" value="{$classData['special_tack']}">

        <p>Stirrup Leather Length:</p>
        <input type="text" name="stirrup-leather-length">

        <p>Pad:</p>
        <input type="text" name="pad" list="pad-list" value="{$classData['pad']}" onclick="select();">
          <datalist id="pad-list">
EOT;

              $query = "SELECT unnest(enum_range(NULL::PAD))::text EXCEPT SELECT name FROM archived_enums;";
              $result = pg_query($db_connection, $query);
              $padNames = pg_fetch_all_columns($result);
              foreach ($padNames as $key => $value) {
                echo "<option value='$value'>";
              }
        echo <<<EOT
          </datalist>

        <div id="client-section">
          <p>Client(s):</p>
EOT;
        $oldClientIDListPGArray = "{";
        foreach ($clientIDList as $id) {
          $clientName = pg_fetch_array(pg_query($db_connection, "SELECT name FROM clients WHERE clients.id = {$id}") , 0, 1)['name'];
          $oldClientIDListPGArray .= $id .',';
          echo <<<EOT
            <input type="text" name="clients[]" list="client-list" value="{$clientName}" onclick="select();">
EOT;
        }
        $oldClientIDListPGArray = rtrim($oldClientIDListPGArray, ',') . "}";

        echo <<<EOT

            <datalist id="client-list">
EOT;
                $query = "SELECT name FROM clients WHERE (archived IS NULL OR archived = '');";
                $result = pg_query($db_connection, $query);
                $clientNames = pg_fetch_all_columns($result);
                foreach ($clientNames as $key => $value) {
                  echo "<option value='$value'>";
                }
        echo <<<EOT
            </datalist>
        </div>
        <input type="text" name="old-client-id-list" value="{$oldClientIDListPGArray}" style="visibility: hidden; height: 1px;">

        <button type="button" id="add-client-button" onclick="newClientFunction();">Add Additional Client</button>

        <p>Instructor:</p>
        <input type="text" name="instructor" list="instructor-list" value="{$instructorName}" onclick="select();">
          <datalist id="instructor-list">
EOT;

              $query = "SELECT name FROM workers WHERE staff = TRUE AND (archived IS NULL OR archived = '');";
              $result = pg_query($db_connection, $query);
              $workerNames = pg_fetch_all_columns($result);
              foreach ($workerNames as $key => $value) {
                echo "<option value='$value'>";
              }
        echo <<<EOT
          </datalist>

        <p>Therapist:</p>
        <input type="text" name="therapist" list="therapist-list" value="{$therapistName}" onclick="select();">
          <datalist id="therapist-list">
EOT;

              $query = "SELECT name FROM workers WHERE staff = TRUE AND (archived IS NULL OR archived = '');";
              $result = pg_query($db_connection, $query);
              $workerNames = pg_fetch_all_columns($result);
              foreach ($workerNames as $key => $value) {
                echo "<option value='$value'>";
              }
        echo <<<EOT
          </datalist>

        <p>ES:</p>
        <input type="text" name="equine-specialist" list="es-list" value="{$esName}" onclick="select();">
          <datalist id="es-list">
EOT;
              $query = "SELECT name FROM workers WHERE staff = TRUE AND (archived IS NULL OR archived = '');";
              $result = pg_query($db_connection, $query);
              $workerNames = pg_fetch_all_columns($result);
              foreach ($workerNames as $key => $value) {
                echo "<option value='$value'>";
              }
        echo <<<EOT
          </datalist>

        <p>Leader:</p>
        <input type="text" name="leader" list="leader-list" value="{$leaderName}" onclick="select();">
          <datalist id="leader-list">
EOT;

              $query = "SELECT name FROM workers WHERE (archived IS NULL OR archived = '');";
              $result = pg_query($db_connection, $query);
              $workerNames = pg_fetch_all_columns($result);
              foreach ($workerNames as $key => $value) {
                echo "<option value='$value'>";
              }
        echo <<<EOT
          </datalist>

          <div id="sidewalker-section">
            <p>Sidewalker(s):</p>

EOT;
          $sidewalkerIDList = explode(',', ltrim(rtrim($classData['sidewalkers'], "}"), '{'));
          foreach ($sidewalkerIDList as $id) {
            $sidewalkerName = pg_fetch_array(pg_query($db_connection, "SELECT name FROM workers WHERE workers.id = {$id} AND (archived IS NULL OR archived = '');") , 0, 1)['name'];
            echo <<<EOT
            <input type="text" name="sidewalkers[]" list="sidewalker-list" value="{$sidewalkerName}" onclick="select();">
EOT;
          }

          echo <<<EOT
              <datalist id="sidewalker-list">
EOT;

                  $query = "SELECT name FROM workers WHERE (archived IS NULL OR archived = '');";
                  $result = pg_query($db_connection, $query);
                  $workerNames = pg_fetch_all_columns($result);
                  foreach ($workerNames as $key => $value) {
                    echo "<option value='$value'>";
                  }

          echo <<<EOT
              </datalist>
            </div>
            <br>
            <button type="button" id="add-sidewalker-button" onclick="newSidewalkerFunction();">Add Additional Sidewalker</button>

        <p style='font-size: 12pt; color: var(--dark-red)'>Archive: <input type="checkbox" name="archive" value="TRUE"> Saves class in database but removes from all schedules and menus</p>

        <div>
          <p style='font-size: 12pt; color: var(--dark-red)'>Delete Class?
          <input type="checkbox" id="delete-checkbox" name="DELETE" value="TRUE">
          WARNING: this will permanently delete all record of the class</p>
        </div>

        <br><br>
        <input type="submit" value="Submit Changes">

      </form>

      <footer>
        <script type="text/javascript">
        function newClientFunction() {
            newInput = document.createElement('input');
            newInput.setAttribute('type', 'text');
            newInput.setAttribute('name', 'clients[]');
            newInput.setAttribute('list', 'client-list');
            newInput.setAttribute('value', '');
            newInput.setAttribute('onclick', 'select()');
            var clientSection = document.getElementById('client-section');
            clientSection.appendChild(newInput);
          };

        function newSidewalkerFunction() {
          newInput = document.createElement('input');
          newInput.setAttribute('type', 'text');
          newInput.setAttribute('name', 'sidewalkers[]');
          newInput.setAttribute('list', 'sidewalker-list');
          newInput.setAttribute('value', '');
          newInput.setAttribute('onclick', 'select()');
          var sidewalkerSection = document.getElementById('sidewalker-section');
          sidewalkerSection.appendChild(newInput);
        };

        var today = new Date();
        var dd = today.getDate();
        var mm = today.getMonth()+1; //January is 0!
        var yyyy = today.getFullYear();

        if(dd<10) {
            dd = '0'+dd
        }

        if(mm<10) {
            mm = '0'+mm
        }

        today = yyyy + '-' + mm + '-' + dd;

        var startDateSelector = document.getElementById('start-date');
        var endDateSelector = document.getElementById('end-date');
        startDateSelector.onchange = function() {
          if (this.value < today) {
            alert("Please select a valid start date \u2014 cannot start in the past!");
            this.value = "";
          } else if (this.value > endDateSelector.value) {
            alert("Check your dates \u2014 end date cannot be before the start date!");
            this.value = "";
            endDateSelector.value = "";
          }
        };

        endDateSelector.onchange = function() {
          if (this.value < startDateSelector.value) {
            alert("Please select a valid end date \u2014 cannot end before the start date!");
            this.value = "";
          }
        };

        </script>
      </footer>
EOT;

} else { //IF CLASS HAS NOT YET BEEN SELECTED

    echo <<<EOT
      <form action="" method="post" class="main-form">
        <p>Select a class to edit:</p>
        <input type="text" name="selected-class" list="class-list">
          <datalist id="class-list">
EOT;
          $query = "SELECT DISTINCT class_type, clients FROM classes WHERE (archived IS NULL OR archived = '');";
          $result = pg_query($db_connection, $query);
          while ($row = pg_fetch_row($result)) {
            $getClientsQuery = <<<EOT
              SELECT clients.name FROM clients WHERE
              clients.id = ANY('{$row[1]}')
              ;
EOT;
            $clients = pg_fetch_all_columns(pg_query($db_connection, $getClientsQuery));
            $clientString = "";
            foreach ($clients as $name) {
              $clientString .= $name . ", ";
            }
            echo "<option value='$row[0]; $clientString'>";
          }

    echo <<<EOT
          </datalist>

          <br><br>
          <input type="submit" value="Submit">
      </form>
EOT;
    }
  ?>


</body>

</html>
