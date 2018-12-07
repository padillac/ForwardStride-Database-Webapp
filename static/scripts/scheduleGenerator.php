
  <?php
    //MAKE SURE POST INCLUDES THESE TWO PARAMETERS!!
    $selectedName = $_POST['selected-name'];
    $selectedDate = $_POST['selected-date'];

    $QUERY_NAME = $_POST['selected-name'];
    include $_SERVER['DOCUMENT_ROOT']."/static/scripts/getWorkerInvolvedClasses.php";
    include $_SERVER['DOCUMENT_ROOT']."/static/scripts/getWorkerInvolvedShifts.php";
    //these scripts generate the variables $allClasses, $allOfficeShifts, $allHorseCareShifts


    //filter classes by date
    $todaysClasses = array();
    $todaysHorseCareShifts = array();
    $todaysOfficeShifts = array();

    //foreach class/shift check if date_of_class/date_of_shift matches $selectedDate, if so, append to $todaysClasses / $todaysShifts
    if ($allClasses) {
      foreach ($allClasses as $key => $class) {
        if ($class['date_of_class'] == $selectedDate){
          $todaysClasses[] = $class;
        }
      }
    }
    if ($allHorseCareShifts) {
      foreach ($allHorseCareShifts as $key => $horseCareShift) {
        if ($horseCareShift['date_of_shift'] == $selectedDate){
          $todaysHorseCareShifts[] = $horseCareShift;
        }
      }
    }
    if ($allOfficeShifts) {
      foreach ($allOfficeShifts as $key => $officeShift) {
        if ($officeShift['date_of_shift'] == $selectedDate){
          $todaysOfficeShifts[] = $officeShift;
        }
      }
    }

    //If no classes/shifts are found for a volunteer
    if (!$todaysClasses and !$todaysHorseCareShifts and !$todaysOfficeShifts) {
      echo "<br><h3 class='main-content-header'>No scheduled events today!</h3>";
      //possibly display an empty schedule?
      return;
    }

    //CREATE AND DISPLAY SCHEDULE FOR GIVEN WORKER AND DATE

    //an array containing all class and shift data indexed by start time.
    $masterList = array();

    foreach ($todaysClasses as $value) {
      $masterList[$value['start_time']] = $value;
    }
    foreach ($todaysHorseCareShifts as $value) {
      $masterList[$value['start_time']] = $value;
    }
    foreach ($todaysOfficeShifts as $value) {
      $masterList[$value['start_time']] = $value;
    }
    //sort masterlist by time.
    ksort($masterList);


    //Display the schedule
    echo <<<EOT
    <div class="schedule-display">
    <p class="schedule-time" style="height: 5vh;">Time:</p>
    <p class="schedule-event-type" style="height: 5vh;">Class/Shift:</p>
    <p class="schedule-staff" style="height: 5vh;">Staff:</p>
    <p class="schedule-volunteers" style="height: 5vh;">Volunteers:</p>
    <p class="schedule-horse-info" style="height: 5vh;">Horse:</p>
    <p class="schedule-clients" style="height: 5vh;">Clients:</p>
    <p class="schedule-lesson-plan" style="height: 5vh;">Lesson Plan:</p>
EOT;

    foreach ($masterList as $time => $event) {

      //Time
      $newTimeString = date("g:i a", strtotime($time)) . "<br> &#8212 <br>" . date("g:i a", strtotime($event['end_time']));
      echo "<p class='schedule-time'>{$newTimeString}</p>";

      //Event Type
      echo "<p class='schedule-event-type'>{$event['class_type']}{$event['care_type']}{$event['office_shift_type']}</p>";

      //Staff
      $staffString = "";
      if ($event['instructor']) {
        $staffString .= "<i>Instructor: </i>" . $event['instructor'];
      }
      if ($event['therapist'] != "" and $event['therapist']) {
        $staffString .= "<br><i>Therapist: </i>" . $event['therapist'];
      }
      if ($event['equine_specialist'] != "" and $event['equine_specialist']) {
        $staffString .= "<br><i>ES: </i>" . $event['equine_specialist'];
      }
      if ($staffString == "") {
        $staffString = "&#8212";
      }
      if (strpos($staffString, $selectedName) !== false) {
        $style = "style='background-color: var(--accent-purple);'";
      } else {
        $style = "";
      }
      echo "<p class='schedule-staff' {$style}>{$staffString}</p>";

      //Volunteers
      $volunteerString = "";
      if ($event['leader'] != "") {
        $volunteerString .= "<i>Leader: </i>" . $event['leader'];
      }
      if ($event['volunteers']) {
        foreach ($event['volunteers'] as $volunteerName) {
          $volunteerString .= "<br><i>Volunteer: </i>" . $volunteerName;
        }
      }
      if ($event['sidewalkers']) {
        foreach ($event['sidewalkers'] as $volunteerName) {
          if ($volunteerName != "") {
            $volunteerString .= "<br><i>Sidewalker: </i>" . $volunteerName;
          }
        }
      }
      if ($volunteerString == "") {
        $volunteerString = "&#8212";
      }
      if (strpos($volunteerString, $selectedName) !== false) {
        $style = "style='background-color: var(--accent-purple);'";
      } else {
        $style = "";
      }
      echo "<p class='schedule-volunteers' {$style}>{$volunteerString}</p>";

      //Horse
      $horseString = "";
      if ($event['horse'] and $event['horse'] != "") {
        $horseString .= "<i>Horse: </i>" . $event['horse'] . ", ";
      }
      if ($event['tack'] and $event['tack'] != "") {
        $horseString .= "<br><i>Tack: </i>" . $event['tack'] . ", ";
      }
      if ($event['special_tack'] and $event['special_tack'] != "") {
        $horseString .= "<i><br>Special Tack: </i>" . $event['special_tack'] . ", ";
      }
      if ($event['stirrup_leather_length'] and $event['stirrup_leather_length'] != "") {
        $horseString .= "<i><br>Stirrup Leather Length: </i>" . $event['stirrup_leather_length'] . ", ";
      }
      if ($event['pad'] and $event['pad'] != "") {
        $horseString .= "<i><br>Pad: </i>" . $event['pad'];
      }
      if ($horseString == "") {
        $horseString = "&#8212";
      }
      echo "<p class='schedule-horse-info'>{$horseString}</p>";

      //Clients
      $clientString = "";
      if ($event['clients']) {
        $clientString = "<i>Clients: </i>";
        foreach ($event['clients'] as $clientName) {
          $clientString .= $clientName . ", ";
        }
      }
      if ($clientString == "") {
        $clientString = "&#8212";
      }
      if (strpos($clientString, $selectedName) !== false) {
        $style = "style='background-color: var(--accent-purple);'";
      } else {
        $style = "";
      }
      echo "<p class='schedule-clients' {$style}>{$clientString}</p>";

      //Lesson Plan
      if ($event['lesson_plan']) {
        $lessonplan = $event['lesson_plan'];
      } else {
        $lessonplan = "&#8212";
      }
      echo "<p class='schedule-lesson-plan'>{$lessonplan}</p>";

    }

    echo "</div>";

  ?>