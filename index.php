<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database settings
$db_host = "localhost"; // Use "localhost" if the database is on the same Raspberry Pi
$db_user = "logger";    // Your database username
$db_pass = "password";  // Your database password
$db_name = "babylogger"; // Your database name

// Connect to the database
$connectdb = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$connectdb) {
    die("Cannot reach database: " . mysqli_connect_error());
}

// Default days to 2 if not set
$days = isset($_POST['days']) ? intval($_POST['days']) : 2;

// Validate and sanitize type input
$type_condition = "";
$valid_types = ["pee", "poo", "breastfed", "bottle", "sleep", "wake", "bath", "cry"];

if (isset($_POST['type']) && $_POST['type'] !== "all" && in_array($_POST['type'], $valid_types)) {
    $type = $_POST['type'];
    $type_condition = "AND type = ?";
}

// Build SQL query for table `buttondata`
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
    <title>Gabi Logger 👶🏻</title>
    <style>
        body {
            background-color: #e6f2ff;
        }
        .header-text {
            font-size: 36px; /* Larger font size */
            font-weight: bold;
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 20px 0;
        }
        table {
            background-color: white;
            border: 1px solid black;
            border-spacing: 0px 0px;
        }
        th, td {
            font-family: Arial, sans-serif;
        }
        td {
            text-align: right;
            font-size: 32px;
            padding: 2px;
        }
        .pee {
            background-color: #ffff66;
        }
        .poo {
            background-color: #996600;
        }
        .breastfed {
            background-color: #ffffff;
        }
        .bottle {
            background-color: #ffffff;
        }
        .sleep {
            background-color: rgb(98, 43, 201);
        }
        .wake {
            background-color: rgb(241, 217, 0);
        }
        .bath {
            background-color: rgb(19, 119, 233);
        }
        .cry {
            background-color: rgb(20, 201, 247);
        }
    </style>
</head>
<body>
<form method="POST">
    <center>
        <div class="header-text">
        <?php
        echo "Gabi's vital functions for the last $days " . ($days > 1 ? "days" : "day") . ".";
        ?>
        </div>
        <hr width="200" size="1">
        Show 
        <select name="type">
            <option value="all" <?php echo (!isset($_POST['type']) || $_POST['type'] === "all") ? "selected" : ""; ?>>All</option>
            <option value="breastfed" <?php echo (isset($_POST['type']) && $_POST['type'] === "breastfed") ? "selected" : ""; ?>>Breastfed</option>
            <option value="bottle" <?php echo (isset($_POST['type']) && $_POST['type'] === "bottle") ? "selected" : ""; ?>>Bottle</option>
            <option value="pee" <?php echo (isset($_POST['type']) && $_POST['type'] === "pee") ? "selected" : ""; ?>>Diaper Change Pee Only</option>
            <option value="poo" <?php echo (isset($_POST['type']) && $_POST['type'] === "poo") ? "selected" : ""; ?>>Diaper Change Poo</option>
            <option value="sleep" <?php echo (isset($_POST['type']) && $_POST['type'] === "sleep") ? "selected" : ""; ?>>Sleep</option>
            <option value="wake" <?php echo (isset($_POST['type']) && $_POST['type'] === "wake") ? "selected" : ""; ?>>Wake</option>
            <option value="bath" <?php echo (isset($_POST['type']) && $_POST['type'] === "bath") ? "selected" : ""; ?>>Bath</option>
            <option value="cry" <?php echo (isset($_POST['type']) && $_POST['type'] === "cry") ? "selected" : ""; ?>>Cry</option>
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
    while ($event = mysqli_fetch_assoc($results)) {
        $event_count++;
        $date = date("M d Y", strtotime($event['tdate']));
        $time = date("g:i a", strtotime($event['ttime']));
        $class = htmlspecialchars($event['type']);
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
        $icon = $icons[$class] ?? "&#x2753;"; // Default to question mark
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
