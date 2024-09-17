<?php
session_start();

// Redirect if user is not logged in or not a driver
if (!isset($_SESSION['userid']) || $_SESSION['role'] !== 'driver') {
    header("Location: index.php");
    exit;
}

$servername = "localhost";
$username = "gitaalli_trans";
$password = "Gb4J7guPmkz5BEyJ3du3";
$dbname = "gitaalli_trans";

$detailed_records = [];
$driver_username = $_SESSION['username'];

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check the admin table for the driver
$sql = "SELECT role, name FROM admin WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $driver_username);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($role, $driver_name);
$stmt->fetch();
$stmt->close();

if ($role !== 'driver') {
    // Redirect if the user is not a driver
    header("Location: index.php");
    exit;
}

// Fetch records from detailed_records table using driver's name
$sql = "SELECT id, patient_first_name, patient_last_name, record_date, record_time, driver, record_type, bus_name, bus_number, location FROM detailed_records WHERE driver = ? ORDER BY record_date DESC, record_time DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $driver_name);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($id, $patient_first_name, $patient_last_name, $record_date, $record_time, $driver, $record_type, $bus_name, $bus_number, $location);

while ($stmt->fetch()) {
    $detailed_records[] = [
        'id' => $id,
        'patient_first_name' => $patient_first_name,
        'patient_last_name' => $patient_last_name,
        'record_date' => $record_date,
        'record_time' => $record_time,
        'driver' => $driver,
        'record_type' => $record_type,
        'bus_name' => $bus_name,
        'bus_number' => $bus_number,
        'location' => $location,
    ];
}

$stmt->close();
$conn->close();

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['messageType'];
    unset($_SESSION['message']);
    unset($_SESSION['messageType']);
}
?>


<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <link rel="shortcut icon" href="images/logo.png">
    <script src="https://kit.fontawesome.com/f0fb58e769.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">

    <title>Driver Page</title>
<link rel="stylesheet" href="style.css">
<style>
        .notification-bar {
            padding: 10px;
            text-align: center;
            z-index: 1050;
            display: none;
        }
        .notification-success {
            background-color: #d4edda;
            color: #155724;
        }
        .notification-error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .close-btn {
            margin-left: 15px;
            color: #000;
            font-weight: bold;
            float: right;
            font-size: 20px;
            line-height: 20px;
            cursor: pointer;
            transition: 0.3s;
        }
        .close-btn:hover {
            color: #999;
        }
    </style>
</head>
<body>
    <header id="header" style="position:sticky;top:0">
        <div class="div1">
          <img src="images/logo.png">
        </div>
        <div class="div2">
        <ul>
        
            <li><a href="driverPage.php">Home</a></li>
           <li><a href="driverInsert.php">Insert Record</a></li>
            <li> <a href="driverpatient.php">Patients</a> </li>
            <li> <a href="logout.php">Log Out</a></li>
           
            
    </ul>
    
        </div>
      </header>
     <aside> 
    <div>
      <img src="images/logo.png" alt="" >
  </div> 
      <div onclick="openNav()" >
          <div class="container" onclick="myFunction(this)" id="sideNav" >
              <p style="border: 1px solid white;padding: 10px;border-radius: 7px;color: white;">Menu</p>
            </div>
          </div>
  </aside>

  <nav style="z-index: 1;">
    <div id="mySidenav" class="sidenav">
        <img src="images/logo.png" alt="">
        <a href="driverPage.php">Home</a>
        <a href="driverInsert.php">Insert record</a> 
 <a href="driverpatient.php">Patients</a> 
            <a href="logout.php">Log Out</a>
    </div>
    <script>
    
function myFunction(x) {
    x.classList.toggle("change");
  }

  var open = false;

function openNav() {
    var sideNav = document.getElementById("mySidenav");
    
    if (sideNav.style.width === "0px" || sideNav.style.width === "") {
        sideNav.style.width = "250px";
        open = true;
    } else {
        sideNav.style.width = "0";
        open = false;
    }
}
    </script>
</nav>
<main>
<div style="padding: 20px;height: 700px;">
<div>
<div id="notificationBar" class="notification-bar <?php echo !empty($messageType) ? 'notification-'.$messageType : ''; ?>" <?php echo !empty($message) ? 'style="display: block;"' : ''; ?>>
        <span class="close-btn" onclick="closeNotification()">&times;</span>
        <span id="notificationMessage"><?php echo !empty($message) ? $message : ''; ?></span>
    </div>
<h3><?php echo "Welcome, " . $_SESSION['username'] . "!"?></h3>
    <h4>Transport Records</h4>
    <p>Record for pick-up and drop-off</p>
    <div style="overflow: auto;">
  
    <table id="myTable">
        <thead>
            <tr>
                <th>Patient First Name</th>
                <th>Patient Last Name</th>
                <th>Record Date</th>
                <th>Record Time</th>
                <th>Driver</th>
                <th>Record Type</th>
                <th>Bus Name</th>
                <th>Bus Number</th>
                <th>Location</th>
            </tr>
        </thead>
        <tbody>
            <?php
            function formatDate($date) {
                return date('d/m/Y', strtotime($date));
            }

            function formatTime($time) {
                return date('g:i A', strtotime($time));
            }
            ?>
            <?php foreach ($detailed_records as $record) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($record['patient_first_name']); ?></td>
                    <td><?php echo htmlspecialchars($record['patient_last_name']); ?></td>
                    <td><?php echo htmlspecialchars(formatDate($record['record_date'])); ?></td>
                    <td><?php echo htmlspecialchars(formatTime($record['record_time'])); ?></td>
                    <td><?php echo htmlspecialchars($record['driver']); ?></td>
                    <td><?php echo htmlspecialchars($record['record_type']); ?></td>
                    <td><?php echo htmlspecialchars($record['bus_name']); ?></td>
                    <td><?php echo htmlspecialchars($record['bus_number']); ?></td>
                    <td><?php echo htmlspecialchars($record['location']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
</div>

</main>

<footer>
   <p>© Business All Rights Reserved.</p> 
</footer>
<script>
        $(document).ready(function() {
            $('#myTable').DataTable({
                paging: true,
                searching: true,
                ordering: true
            });
        });
    </script>
  <script>
        function closeNotification() {
            var notificationBar = document.getElementById("notificationBar");
            notificationBar.style.display = "none";
        }
    </script>
 <script src="script.js"></script>
</body>
</html>