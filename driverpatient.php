<?php
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['role'] !== 'driver') {
    header("Location: index.php");
    exit;
}

$servername = "localhost";
$username = "gitaalli_dbuser";
$password = "microsoft3500";
$dbname = "gitaalli_mregnew";

$message = '';
$messageType = '';
$patients = [];

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['addPatient'])) {
        $firstName = $conn->real_escape_string($_POST['firstName']);
        $lastName = $conn->real_escape_string($_POST['lastName']);
        $houseNumber = $conn->real_escape_string($_POST['houseNumber']);

        // Check if the patient already exists
        $stmt = $conn->prepare("SELECT sno FROM tbl_user WHERE firstname = ? AND lastname = ?");
        $stmt->bind_param("ss", $firstName, $lastName);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $_SESSION['message'] = "Patient already exists.";
            $_SESSION['messageType'] = 'error';
        } else {
            $stmt = $conn->prepare("INSERT INTO tbl_user (firstname, lastname, houseNumber) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $firstName, $lastName, $houseNumber);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Patient added successfully.";
                $_SESSION['messageType'] = 'success';
            } else {
                $_SESSION['message'] = "Error adding patient: " . $stmt->error;
                $_SESSION['messageType'] = 'error';
            }
        }

        $stmt->close();
    }

    if (isset($_POST['deletePatient'])) {
        $patientId = (int) $_POST['patientId'];

        // Delete the patient
        $stmt = $conn->prepare("DELETE FROM tbl_user WHERE sno = ?");
        $stmt->bind_param("i", $patientId);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Patient deleted successfully.";
            $_SESSION['messageType'] = 'success';
        } else {
            $_SESSION['message'] = "Error deleting patient: " . $stmt->error;
            $_SESSION['messageType'] = 'error';
        }

        $stmt->close();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$result = $conn->query("SELECT sno, lastname, firstname, houseNumber FROM tbl_user WHERE status='Active' ORDER BY firstname, lastname");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }
}

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

    <title>Patients</title>
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
    <div style="overflow: auto;">
  
     <div>
              
                  <div id="enter">
                    <div>
                        <h1>Form for Entering Patient Names</h1>
                        <p>Fill in the form with the required details</p>
                    </div>
                    <div>
                        <form action="" method="post" id="form">
                            <input type="text" name="firstName" placeholder="Patient first name"><br>
                            <input type="text" name="lastName" placeholder="Patient last name"><br>
                            <input type="text" name="houseNumber" placeholder="House Number"><br><br>
                            <input type="submit" name="addPatient" value="Add Patient" id="submit">
                        </form>
                    </div> 
                </div>
                <div id="list">
                    <p>Patient Information</p>
                    <h2>List of All Patients</h2>
                    <div>
                        <?php if (!empty($patients)): ?>
                            <table id="myTable">
                                <thead>
                                    <tr>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>House Number</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($patients as $patient): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($patient['firstname']); ?></td>
                                            <td><?php echo htmlspecialchars($patient['lastname']); ?></td>
                                            <td><?php echo htmlspecialchars($patient['houseNumber']); ?></td>
                                            <td>
                                           <button onclick="editAdmin(<?php echo $patient['sno']; ?>)">Edit</button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div> 
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
    function editAdmin(id) {
        window.location.href = `update_patient2.php?sno=${id}`;
    }
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