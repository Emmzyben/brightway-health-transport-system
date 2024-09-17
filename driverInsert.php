<?php
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['role'] !== 'driver') {
    header("Location: index.php");
    exit;
}

// Database connection for transport database
$servername = "localhost";
$username = "gitaalli_trans";
$password = "Gb4J7guPmkz5BEyJ3du3";
$dbname = "gitaalli_trans";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Database connection for patient database
$servername1 = "localhost";
$username1 = "gitaalli_dbuser";
$password1 = "microsoft3500";
$dbname1 = "gitaalli_mregnew";

$conn1 = new mysqli($servername1, $username1, $password1, $dbname1);
if ($conn1->connect_error) {
    die("Connection failed: " . $conn1->connect_error);
}
$driver_username = $_SESSION['username'];
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
// Fetch patients from the second database
$patients = [];
$result = $conn1->query("SELECT sno, lastname, firstname, houseNumber FROM tbl_user WHERE status='Active' ORDER BY firstname, lastname");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }
}
$result->free();
$conn1->close(); // Close the second database connection

// Fetch drivers
$drivers = [];
$result = $conn->query("SELECT id, name FROM admin WHERE role = 'driver'");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $drivers[] = $row;
    }
}
$result->free();

// Fetch buses
$buses = [];
$result = $conn->query("SELECT id, bus_name, plate_number FROM bus_record");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $buses[] = $row;
    }
}
$result->free();
$conn->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pickup_date = $_POST['pickup_date'];
    $pickup_time = $_POST['pickup_time'];
    $pickup_driver = $_POST['pickup_driver'];
    $recordType = $_POST['recordType'];
    $pickup_bus_name = $_POST['pickup_bus_name'];
    $pickup_bus_number = $_POST['pickup_bus_number'];
    $location = $_POST['location']; // Ensure you include location in the POST request

    $selected_patients = [];
    if (isset($_POST['selected_patients']) && is_array($_POST['selected_patients'])) {
        $selected_patients = $_POST['selected_patients'];
    }

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT COUNT(*) FROM detailed_records WHERE patient_first_name = ? AND patient_last_name = ? AND record_date = ? AND record_type = ? AND location = ?");
    $insert_stmt = $conn->prepare("INSERT INTO detailed_records (patient_first_name, patient_last_name, record_date, record_time, driver, record_type, bus_name, bus_number, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $duplicate_found = false;
    foreach ($selected_patients as $patient) {
        // Split the patient name into firstname and lastname
        $name_parts = explode(' ', $patient, 2);
        if (count($name_parts) == 2) {
            $firstname = $name_parts[0];
            $lastname = $name_parts[1];
            $stmt->bind_param("sssss", $firstname, $lastname, $pickup_date, $recordType, $location);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->free_result();
            
            if ($count > 0) {
                $duplicate_found = true;
                $message = "Error: A $recordType record for $firstname $lastname at $location already exists on $pickup_date.";
                $messageType = 'error';
                break;
            } else {
                $insert_stmt->bind_param("sssssssss", $firstname, $lastname, $pickup_date, $pickup_time, $pickup_driver, $recordType, $pickup_bus_name, $pickup_bus_number, $location);
                $insert_stmt->execute();
            }
        } else {
            error_log("Invalid patient name: " . $patient);
        }
    }

    $stmt->close();
    $insert_stmt->close();
    $conn->close();

    if (!$duplicate_found) {
        $_SESSION['message'] = "Transport records submitted successfully!";
        $_SESSION['messageType'] = 'success';
    } else {
        $_SESSION['message'] = $message;
        $_SESSION['messageType'] = $messageType;
    }

    header("Location: driverPage.php");
    exit;
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

    <title>insert record</title>
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
        .divide2{
            padding:20px;width:20%;
        }
        .divide2 form>select{
      border-radius: 6px;padding: 10px;margin-bottom: 10px;border: 1px solid rgb(224, 219, 219);
    width:100%;
  }
  .divider{
    position: absolute;left:0
  }
  @media screen and (max-width:900px) {
    .divider{
        position: unset;
    }
    .divide2{
width: auto;
    }
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
           <li><a href="driverinsert.php">Insert Record</a></li>
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
        <a href="driverinsert.php">Insert record</a> 
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
<div id="divide">
   <div class="divider">
    <h3>New Transport Record</h3>
<p>Select Patient</p>
<div>
<?php
    if (!empty($message)) {
        echo '<div id="notificationBar" class="notification-bar notification-' . $messageType . '">';
        echo $message;
        echo '<span class="close-btn" onclick="closeNotification()">&times;</span>';
        echo '</div>';
    }
    ?>
        <form id="patientForm">
            <table id="myTable">
                <thead>
                  <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>House Number</th>
                    <th>Select All <input type="checkbox" id="selectAll"></th>
                </tr>   
                </thead>
               <tbody>
                 <?php foreach ($patients as $patient): ?>
                <tr>
                    <td><?php echo htmlspecialchars($patient['firstname']); ?></td>
                    <td><?php echo htmlspecialchars($patient['lastname']); ?></td>
                    <td><?php echo htmlspecialchars($patient['houseNumber']); ?></td>
                    <td><input type="checkbox" name="selected_patients[]" value="<?php echo htmlspecialchars($patient['firstname'] . ' ' . $patient['lastname']); ?>"></td>
                </tr>
                <?php endforeach; ?>
               </tbody>
               <script>
    document.getElementById('selectAll').addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('#patientForm input[type="checkbox"]');
        for (var checkbox of checkboxes) {
            checkbox.checked = this.checked;
        }
    });
</script>
            </table>
        </form>
    </div>
</div>
</div>  

<div class="divide2">
    <br><br>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" onsubmit="copySelectedPatients()">
            <label for="pickup_date">Date</label><br>
            <input type="date" id="pickup_date" name="pickup_date" required><br>
            <label for="pickup_time">Time</label><br>
            <input type="time" id="pickup_time" name="pickup_time" required><br>
            <input type="text" name="pickup_driver" id="pickup_driver" value="<?php echo $driver_name; ?>" hidden>
             <label for="recordType">Transport record type</label><br>
            <select id="recordType" name="recordType" required>
                <option value="">Select record type</option>
                <option value="Pick up">Pick Up</option>
                <option value="Drop off">Drop Off</option>
            </select><br>
            <input type="text" id="pickup_bus_name" name="pickup_bus_name" hidden>
     <label for="location">Location</label>
<select name="location" id="">
    <option value="">Select Location</option>
    <option value="Home">Home</option>
    <option value="Daycare Facility">Daycare Facility</option>
</select>
<label for="pickup_bus_number">Bus number</label><br>
<select id="pickup_bus_number" name="pickup_bus_number" required>
    <option value="">Select Bus Number</option>
    <?php foreach ($buses as $bus): ?>
        <option value="<?php echo htmlspecialchars($bus['plate_number']); ?>" 
                data-bus-name="<?php echo htmlspecialchars($bus['bus_name']); ?>">
            <?php echo htmlspecialchars($bus['plate_number']); ?>
        </option>
    <?php endforeach; ?>
</select><br>
<script>
    document.getElementById('pickup_bus_number').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        var busName = selectedOption.getAttribute('data-bus-name');
        document.getElementById('pickup_bus_name').value = busName;
    });
</script>

            <div id="selectedPatientsContainer"></div>
            <input type="submit" value="Submit" style='background-color:#007aff;color:white'>
        </form>
</div> 
</div>

</main>

<footer>
   <p>© Business All Rights Reserved.</p> 
</footer>
<script>
function copySelectedPatients() {
    const checkboxes = document.querySelectorAll('#patientForm input[type="checkbox"]:checked');
    const container = document.getElementById('selectedPatientsContainer');
    container.innerHTML = '';

    checkboxes.forEach((checkbox) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'selected_patients[]';
        input.value = checkbox.value;
        container.appendChild(input);
    });
}
</script>
<script>
        function closeNotification() {
            var notificationBar = document.getElementById("notificationBar");
            notificationBar.style.display = "none";
        }

        // Automatically show and dismiss the notification bar after 5 seconds
        window.onload = function() {
            var notificationBar = document.getElementById("notificationBar");
            if (notificationBar) {
                notificationBar.style.display = "block";
                setTimeout(function() {
                    notificationBar.style.display = "none";
                }, 5000);
            }
        }
    </script>
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
        window.onload = function() {
            const now = new Date();
            
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const currentDate = `${year}-${month}-${day}`;
            
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const currentTime = `${hours}:${minutes}`;
            
            document.getElementById('pickup_date').value = currentDate;
            document.getElementById('pickup_time').value = currentTime;
        };
    </script>
 <script src="script.js"></script>
</body>
</html>