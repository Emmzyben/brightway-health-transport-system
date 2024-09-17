<?php
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$servername = "localhost";
$username = "gitaalli_dbuser";
$password = "microsoft3500";
$dbname = "gitaalli_mregnew";
$message = '';
$messageType = '';

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['updatePatient'])) {
    $id = $_POST['sno'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $houseNumber = $_POST['houseNumber'];
    $status = $_POST['status']; // Make sure the 'status' is collected from the form

    $sql = "UPDATE tbl_user SET lastname=?, firstname=?, houseNumber=?, status=? WHERE sno=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $lastname, $firstname, $houseNumber, $status, $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Patient record updated successfully.";
        $_SESSION['messageType'] = "success";
        header("Location: patient.php");
        exit;
    } else {
        $message = "Error updating patient record: " . $stmt->error;
        $messageType = "error";
    }

    $stmt->close();
}

$id = $_GET['sno'] ?? '';

$sql = "SELECT sno, lastname, firstname, houseNumber, status FROM tbl_user WHERE sno=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($patient_id, $last_name, $first_name, $house_number, $status);

if ($stmt->num_rows === 1) {
    $stmt->fetch();
    $admin = [
        'sno' => $patient_id,
        'lastname' => $last_name,
        'firstname' => $first_name,
        'houseNumber' => $house_number,
        'status' => $status,
    ];
} else {
    $_SESSION['message'] = "Patient record not found.";
    $_SESSION['messageType'] = "error";
    header("Location: patient.php");
    exit;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/logo.png">
    <script src="https://kit.fontawesome.com/f0fb58e769.js" crossorigin="anonymous"></script>
    <title>Admin Page</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header id="header" style="position:sticky;top:0">
        <div class="div1">
            <img src="images/logo.png">
        </div>
        <div class="div2">
            <ul>
                <li style="color:white"><?php echo "Welcome, " . htmlspecialchars($_SESSION['username']) . "!"; ?></li>
            </ul>
        </div>
    </header>
    <aside>
        <div>
            <img src="images/logo.png" alt="">
        </div>
        <div onclick="openNav()">
            <div class="container" onclick="myFunction(this)" id="sideNav">
                <p style="border: 1px solid white;padding: 10px;border-radius: 7px;color: white;">Menu</p>
            </div>
        </div>
    </aside>
    <nav style="z-index: 1;">
        <div id="mySidenav" class="sidenav">
            <a href="admin.php">Driver & Bus Record</a>
            <a href="patient.php">Patient Records</a>
            <a href="transport.php">Transport Records</a>
            <a href="generate.php">Generate Report</a>
            <a href="logout.php">Log Out</a>
        </div>
        <script>
            function myFunction(x) {
                x.classList.toggle("change");
            }

            function openNav() {
                var sideNav = document.getElementById("mySidenav");

                if (sideNav.style.width === "0px" || sideNav.style.width === "") {
                    sideNav.style.width = "250px";
                } else {
                    sideNav.style.width = "0";
                }
            }
        </script>
    </nav>
    <main>
        <div id="divideAdmin">
            <div class="divideAdmin2">
                <ul id="myList">
                    <h3>Admin Dashboard</h3>
                    <li><a href="admin.php">Driver and Bus Record</a></li>
                    <li><a href="patient.php">Patient Records</a></li>
                    <li><a href="transport.php">Transport Records</a></li>
                    <li><a href="generate.php">Generate Report</a></li>
                    <li><a href="logout.php">Log Out</a></li>
                </ul>
            </div>
            <div class="divideAdmin1">
                <div id="list" style="background-color: #fff;">
                    <h2>Edit Patient</h2>
                    <?php if (!empty($message)): ?>
                        <p class="<?php echo htmlspecialchars($messageType); ?>"><?php echo htmlspecialchars($message); ?></p>
                    <?php endif; ?>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <input type="hidden" name="sno" value="<?php echo htmlspecialchars($admin['sno']); ?>">
                        <label for="firstname">First Name:</label><br>
                        <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($admin['firstname']); ?>" required><br>
                        <label for="lastname">Last Name:</label><br>
                        <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($admin['lastname']); ?>" required><br>
                        <label for="houseNumber">House Number:</label><br>
                        <input type="text" id="houseNumber" name="houseNumber" value="<?php echo htmlspecialchars($admin['houseNumber']); ?>" required><br>
                        <label for="status">status:</label><br>
                         <select class="select2" name='status'>
                <option value="">Select status </option>
                <option value="Active" <?php if($admin['status'] == 'Active') echo 'selected'; ?>>Active</option>
                <option value="Inactive" <?php if($admin['status'] == 'Inactive') echo 'selected'; ?>>Inactive</option>
            </select><br><br>
                       <button type="submit" name="updatePatient" id="submit" style="border:none;padding:7px">Update</button>
                        <a href="patient.php" style="margin-left:10px">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <p>© Business All Rights Reserved.</p>
    </footer>
    <script src="script.js"></script>
</body>
</html>
