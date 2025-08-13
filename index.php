<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database settings
$db_host = "localhost"; 
$db_user = "logger";    
$db_pass = "password";  
$db_name = "babylogger"; 

// Connect to the database
$connectdb = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$connectdb) {
    die("Cannot reach database: " . mysqli_connect_error());
}

// Default days to 2 if not set
$days = isset($_POST['days']) ? intval($_POST['days']) : 2;

// Event type validation
$valid_types = ["breastfed", "bottle", "pee", "poo", "sleep", "wake", "bath", "cry"];
$type_condition = "";
if (isset($_POST['type']) && $_POST['type'] !== "all" && in_array($_POST['type'], $valid_types)) {
    $type = $_POST['type'];
    $type_condition = "AND type = ?";
}

// Build SQL query
$sql = "SELECT * FROM buttondata 
        WHERE tdate >= CURDATE() - INTERVAL ? DAY 
        $type_condition 
        ORDER BY tdate DESC, ttime DESC";
$stmt = mysqli_prepare($connectdb, $sql);
if (!$stmt) {
    die("Query preparation failed: " . mysqli_error($connectdb));
}

if ($type_condition) {
    mysqli_stmt_bind_param($stmt, "is", $days, $type);
} else {
    mysqli_stmt_bind_param($stmt, "i", $days);
}

mysqli_stmt_execute($stmt);
$results = mysqli_stmt_get_result($stmt);
if (!$results) {
    die("Query execution failed: " . mysqli_error($connectdb));
}
?>
<html>
<head>
    <title>Baby Logger 👶🏻</title>
    <style>
        body { background-color: #e6f2ff; font-family: Arial, sans-serif; }
        .header-text { font-size: 36px; font-weight: bold; text-align: center; margin: 20px 0; }
        table { background-color: white; border: 1px solid black; border-spacing: 0; margin: 10px auto; }
        th, td { font-family: Arial, sans-serif; }
        td { text-align: right; font-size: 32px; padding: 2px; }
        .pee { background-color: #ffff66; }
        .poo { background-color: #996600; }
        .breastfed { background-color: #ffffff; }
        .bottle { background-color: #ffffff; }
        .sleep { background-color: rgb(98, 43, 201); }
        .wake { background-color: rgb(241, 217, 0); }
        .bath { background-color: rgb(19, 119, 233); }
        .cry { background-color: rgb(20, 201, 247); }
        .legend { margin: 10px auto; width: 400px; font-size: 18px; }
        .legend td { text-align: left; }
    </style>
</head>
<body>
<form method="POST">
    <center>
        <div class="header-text">
            <?php
            echo "Baby's vital functions for the last $days " . ($days > 1 ? "days" : "day") . ".";
            ?>
        </div>
        <table class="legend" border="1" cellpadding="4">
            <tr><th>Numpad Key</th><th>Event</th></tr>
            <tr><td>1</td><td>Breastfed</td></tr>
            <tr><td>2</td><td>Bottle</td></tr>
            <tr><td>3</td><td>Pee</td></tr>
            <tr><td>4</td><td>Poo</td></tr>
            <tr><td>5</td><td>Sleep</td></tr>
            <tr><td>6</td><td>Wake</td></tr>
            <tr><td>7</td><td>Bath</td></tr>
            <tr><td>8</td><td>Cry</td></tr>
        </table>
        <hr width="200" size="1">
        Show 
        <select name="type">
            <option value="all" <?php echo (!isset($_POST['type']) || $_POST['type'] === "all") ? "selected" : ""; ?>>All</option>
            <?php
            foreach ($valid_types as $t) {
                $sel = (isset($_POST['type']) && $_POST['type'] === $t) ? "selected" : "";
                echo "<option value='$t' $sel>" . ucfirst($t) . "</option>";
            }
            ?>
        </select>
        events for past 
        <select name="days">
            <?php
            foreach ([1, 2, 3, 4, 5, 6, 7, 14, 21, 31, 365] as $day_option) {
                $selected = $days == $day_option ? "selected" : "";
                echo "<option value='$day_option' $selected>$day_option</option>";
            }
            ?>
        </select> days.
        <input type="submit" value="Update">
    </center>
</form>

<table width="600" border="1" cellpadding="1" cellspacing="1" align="center">
    <tr>
        <th width="200px">Date</th>
        <th width="200px">Time</th>
        <th width="200px">Event</th>
    </tr>
    <?php
    $event_count = 0;
    $icons = [
        "pee" => "&#128166;",
        "poo" => "&#128169;",
        "breastfed" => "&#x1F9D1;&#x1F3FB;&#x200D;&#x1F37C;",
        "bottle" => "&#x1f37c;",
        "sleep" => "&#x1F4A4;",
        "wake" => "&#x23F0;",
        "bath" => "&#128705;",
        "cry" => "&#x1F622;&#x1F3FB;"
    ];
    while ($event = mysqli_fetch_assoc($results)) {
        $event_count++;
        $date = date("M d Y", strtotime($event['tdate']));
        $time = date("g:i a", strtotime($event['ttime']));
        $class = htmlspecialchars($event['type']);
        $icon = $icons[$class] ?? "&#x2753;";
        echo "<tr class='$class'>";
        echo "<td class='$class'>$date</td>";
        echo "<td class='$class'>$time</td>";
        echo "<td class='$class'><center>$icon</center></td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<center>Event count: $event_count</center>";
    ?>
</body>
</html>
