<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="/static/main.css">
<link href="https://fonts.googleapis.com/css?family=Nunito:700&display=swap" rel="stylesheet">
  <?php include $_SERVER['DOCUMENT_ROOT'] . "/static/scripts/initialization.php"; ?>
  <title>Admin | Edit Misc. Object</title>
</head>

<body>

  <header>
    <h1>Edit Object</h1>
    <nav> <a href="../"><button id="back-button">Back</button></a>
      <a href="/"><button id="home-button">Home</button></a>
    </nav>
  </header>

  <?php
    if ($_POST['archive'] == "TRUE") {
      $result = pg_query($db_connection, "INSERT INTO archived_enums (name) VALUES ('{$_POST['selected-object']}');");
    } else {
      $objectName = pg_escape_string(trim($_POST['new-object-name']));
      $query = "UPDATE pg_enum SET enumlabel = '{$objectName}' WHERE enumlabel = '{$_POST['selected-object']}' AND enumtypid = (SELECT oid FROM pg_type WHERE typname = '{$_POST['object-type']}');";
      $result = pg_query($db_connection, $query);
    }

    if ($result) {
      echo "<h3 class='main-content-header'>Success</h3";
    } else {
      echo "<h3 class='main-content-header'>An error occurred.</h3><p class='main-content-header'>Please try again, ensure that all data is correctly formatted.</p>";
    }

  ?>


</body>

</html>
