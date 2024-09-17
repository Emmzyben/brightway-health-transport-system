<?php
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$servername = "localhost";
$username = "gitaalli_trans";
$password = "Gb4J7guPmkz5BEyJ3du3";
$dbname = "gitaalli_trans";

$message = '';
$messageType = '';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['updateBusRecord'])) {
    $id = $_POST['id'];
    $busName = $_POST['bus_name'];
    $plateNumber = $_POST['plate_number'];

    $sql = "UPDATE bus_record SET bus_name=?, plate_number=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $busName, $plateNumber, $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Bus record updated successfully.";
        $_SESSION['messageType'] = "success";
        header("Location: admin.php");
        exit;
    } else {
        $message = "Error updating bus record: " . $stmt->error;
        $messageType = "error";
    }

    $stmt->close();
}

$id = $_GET['id'] ?? '';

$sql = "SELECT id, bus_name, plate_number FROM bus_record WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($bus_id, $bus_name, $plate_number);

if ($stmt->num_rows === 1) {
    $stmt->fetch();
    $busRecord = [
        'id' => $bus_id,
        'bus_name' => $bus_name,
        'plate_number' => $plate_number,
    ];
} else {
    $_SESSION['message'] = "Bus record not found.";
    $_SESSION['messageType'] = "error";
    header("Location: admin.php");
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
    <title>Admin page</title>
    <link rel="stylesheet" href="style.css">
    
</head>
<body>
    <header id="header" style="position:sticky;top:0">
        <div class="div1">
            <img src="images/logo.png">
        </div>
        <div class="div2">
            <ul>
                <li style="color:white"><?php echo "Welcome, " . $_SESSION['username'] . "!" ?></li>
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
            <a href="transport.php">Transport records</a>
            <a href="generate.php">Generate report</a>
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
        <div id="divideAdmin">
            <div class="divideAdmin2">
               <ul id="myList">
                <h3>Admin dashboard</h3>
                    <li><a href="admin.php">Driver and Bus record</a></li>
                  <li><a href="patient.php">Patient Records</a></li> 
                    <li><a href="transport.php">Transport records</a></li>
                    <li><a href="generate.php">Generate report</a></li> 
                    <li><a href="logout.php">Log Out</a></li>
                </ul>
            </div> 
            <div class="divideAdmin1">
                <div id="list" style="background-color: #fff;">
                <h2>Edit Bus Record</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($busRecord['id']); ?>">
            <label for="bus_name">Bus Name:</label><br>
            <input type="text" id="bus_name" name="bus_name" value="<?php echo htmlspecialchars($busRecord['bus_name']); ?>"><br>
            <label for="plate_number">Plate Number:</label><br>
            <input type="text" id="plate_number" name="plate_number" value="<?php echo htmlspecialchars($busRecord['plate_number']); ?>"><br><br>
            <button type="submit" name="updateBusRecord" id="submit" style="border:none;padding:7px">Update</button>
            <a href="admin.php" style="margin-left:10px">Cancel</a>
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
