<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="/static/main.css">
  <?php INCLUDE $_SERVER['DOCUMENT_ROOT'] . "/static/scripts/connectdb.php"; ?>
  <title>Client Manage Classes</title>
</head>

<body>

  <header>
    <h1>Manage Classes</h1>
    <nav> <a href="../"><button id="back-button">Back</button></a>
      <a href="/"><button id="home-button">Home</button></a>
    </nav>
  </header>

  <?php
    $classID = explode(';', $_POST['buttonInfo'])[0];
    $clientString = explode(';', $_POST['buttonInfo'])[1];

    $getClassInfoQuery = "SELECT class_type, cancelled, date_of_class, lesson_plan, horses, horse_behavior, horse_behavior_notes, clients, attendance, client_notes, instructor, therapist, equine_specialist, leaders, sidewalkers FROM classes WHERE id = {$classID}";
    $classInfo = pg_fetch_all(pg_query($db_connection, $getClassInfoQuery))[0];
    echo "<h3 class='main-content-header'>{$classInfo['class_type']}, {$clientString} {$classInfo['date_of_class']}</h3>";
  ?>

  <form action="manage-class-back-end.php" method="post" class="main-form">

    <input type="text" name="id" value="<?php echo $classID ?>" style="visibility: hidden; height: 1px;">

    <p>Lesson Plan:</p>
    <textarea name="lesson-plan" rows="15" cols="30" readonly>
      <?php
        echo $classInfo['lesson_plan'];
      ?>
    </textarea>

    <?php $horseNameList = pg_fetch_all_columns(pg_query($db_connection, "SELECT name FROM horses WHERE id = ANY('{$classInfo['horses']}');")); ?>
    <p>Horse(s):</p>
    <?php
      foreach ($horseNameList as $name) {
        echo "<input type='text' list='horse-list' name='horses[]' value='{$name}' onclick='select()' readonly>";
      }
    ?>
      <datalist id="horse-list">
        <?php
          $query = "SELECT name FROM horses WHERE (archived IS NULL OR archived = '');";
          $result = pg_query($db_connection, $query);
          $horseNames = pg_fetch_all_columns($result);
          foreach ($horseNames as $key => $value) {
            echo "<option value='$value'>";
          }
        ?>
      </datalist>

      <p>Horse Behavior:</p>
      <input type="text" name="horse-behavior" list="horse-behavior-form" value="">
        <datalist id="horse-behavior-form">
          <?php
            $query = "SELECT unnest(enum_range(NULL::HORSE_BEHAVIOR))::text EXCEPT SELECT name FROM archived_enums;";
            $result = pg_query($db_connection, $query);
            $behaviorNames = pg_fetch_all_columns($result);
            foreach ($behaviorNames as $key => $value) {
              echo "<option value='$value'>";
            }
          ?>
        </datalist>

      <br>
      <p>Horse Behavior Notes:</p>
      <textarea name="horse-behavior-notes" rows="10" cols="30">
        <?php
          echo $classInfo['horse_behavior_notes'];
        ?>
      </textarea>

      <p>Attendance:</p>
      <?php
        $clientIDList = explode(',', rtrim(ltrim($classInfo['clients'], '{'), '}'));
        $clientNameList = explode(',', $clientString);
        $clientAttendanceList = explode(',', rtrim(ltrim($classInfo['attendance'], '{'), '}'));
        foreach ($clientIDList as $index => $id) {
          $checked = "";
          if (in_array($id, $clientAttendanceList)) {
            $checked = "checked";
          }
          echo <<<EOT
          <div>
            <label>{$clientNameList[$index]}</label>
            <input type="checkbox" name="attendance[]" value="$id" style="position: absolute; margin-left: 15px;" {$checked}>
          </div>
EOT;
        }
      ?>

      <p>Client Notes:</p>
      <textarea name="client-notes" rows="10" cols="30">
        <?php
          echo $classInfo['client_notes'];
        ?>
      </textarea>

      <?php $instructorName = pg_fetch_row(pg_query($db_connection, "SELECT name FROM workers WHERE id = '{$classInfo['instructor']}'"))[0]; ?>
      <p>Instructor:</p>
      <input type="text" list="instructor-list" name="instructor" value="<?php echo $instructorName?>" onclick="select()" readonly>
        <datalist id="instructor-list">
          <?php
            $query = "SELECT name FROM workers WHERE staff = TRUE AND (archived IS NULL OR archived = '');";
            $result = pg_query($db_connection, $query);
            $workerNames = pg_fetch_all_columns($result);
            foreach ($workerNames as $key => $name) {
              echo "<option value='$name'>";
            }
          ?>
        </datalist>

      <?php $therapistName = pg_fetch_row(pg_query($db_connection, "SELECT name FROM workers WHERE id = '{$classInfo['therapist']}'"))[0]; ?>
      <p>Therapist:</p>
      <input type="text" list="therapist-list" name="therapist" value="<?php echo $therapistName?>" onclick="select()" readonly>
        <datalist id="therapist-list">
          <?php
            $query = "SELECT name FROM workers WHERE staff = TRUE AND (archived IS NULL OR archived = '');";
            $result = pg_query($db_connection, $query);
            $workerNames = pg_fetch_all_columns($result);
            foreach ($workerNames as $key => $name) {
              echo "<option value='$name'>";
            }
          ?>
        </datalist>

        <?php $equineSpecialistName = pg_fetch_row(pg_query($db_connection, "SELECT name FROM workers WHERE id = '{$classInfo['equine_specialist']}'"))[0]; ?>
        <p>Equine Specialist:</p>
        <input type="text" list="equine-specialist-list" name="equine-specialist" value="<?php echo $equineSpecialistName?>" onclick="select()" readonly>
          <datalist id="equine-specialist-list">
            <?php
              $query = "SELECT name FROM workers WHERE staff = TRUE AND (archived IS NULL OR archived = '');";
              $result = pg_query($db_connection, $query);
              $workerNames = pg_fetch_all_columns($result);
              foreach ($workerNames as $key => $name) {
                echo "<option value='$name'>";
              }
            ?>
          </datalist>

          <?php $leaderNameList = pg_fetch_all_columns(pg_query($db_connection, "SELECT name FROM workers WHERE id = ANY('{$classInfo['leaders']}')")); ?>
          <p>Leader(s):</p>
          <?php
            foreach ($leaderNameList as $name) {
              echo "<input type='text' list='leader-list' name='leaders[]' value='{$name}' onclick='select()' readonly>";
            }
          ?>
            <datalist id="leader-list">
              <?php
                $query = "SELECT name FROM workers WHERE (archived IS NULL OR archived = '');";
                $result = pg_query($db_connection, $query);
                $workerNames = pg_fetch_all_columns($result);
                foreach ($workerNames as $key => $name) {
                  echo "<option value='$name'>";
                }
              ?>
            </datalist>

          <p>Sidewalker(s):</p>
            <datalist id="sidewalker-list">
              <?php
                $query = "SELECT name FROM workers WHERE (archived IS NULL OR archived = '');";
                $result = pg_query($db_connection, $query);
                $workerNames = pg_fetch_all_columns($result);
                foreach ($workerNames as $key => $name) {
                  echo "<option value='$name'>";
                }
              ?>
            </datalist>
          <?php
            $sidewalkerIDList = explode(',', rtrim(ltrim($classInfo['sidewalkers'], "{"), "}"));
            foreach ($sidewalkerIDList as $id) {
              $name = pg_fetch_row(pg_query($db_connection, "SELECT name FROM workers WHERE id = '{$id}'"))[0];
              echo "<input type='text' name='sidewalkers[]' list='sidewalker-list' value='{$name}' readonly>";
            }
          ?>
          <br>

    <?php if ($classInfo['cancelled'] == "t") {$checked = "checked";} else {$checked = "";} ?>
    <p>Cancel Class: <input type="checkbox" name="cancel" value="TRUE" <?php echo $checked; ?>></p>


    <br><br>
    <input type="submit" value="Submit">
  </form>


</body>

</html>