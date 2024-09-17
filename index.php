<?php
session_start();

$servername = "localhost";
$username = "gitaalli_trans";
$password = "Gb4J7guPmkz5BEyJ3du3";
$dbname = "gitaalli_trans";

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $role = $_POST['role'];
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, username, role FROM admin WHERE username = ? AND password = ? AND role = ?");
    $stmt->bind_param("sss", $user, $pass, $role);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $name, $role);
        $stmt->fetch();

        $_SESSION['userid'] = $id;
        $_SESSION['username'] = $name;
        $_SESSION['role'] = $role;

        if ($role == 'admin') {
            header("Location: transport.php");
            exit;
        } else if ($role == 'driver') {
            header("Location: driverPage.php");
            exit;
        }
    } else {
        $message = "Invalid username or password.";
    }

    $stmt->close();
    $conn->close();
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brightway Day Center</title>
    <link rel="shortcut icon" href="images/logo.png">
    <script src="https://kit.fontawesome.com/f0fb58e769.js" crossorigin="anonymous"></script>
<link rel="stylesheet" href="style.css">
</head>
<body>
    <header id="header" style="position:sticky;top:0">
        <div class="div1">
          <img src="images/logo.png">
        </div>
        <div class="div2">
        
    
        </div>
      </header>
     <aside> 
    <div>
      <img src="images/logo.png" alt="" >
  </div> 
      <div onclick="openNav()" >
          <!-- <div class="container" onclick="myFunction(this)" id="sideNav">
              <div class="bar1"></div>
              <div class="bar2"></div>
              <div class="bar3"></div>
            </div> -->
          </div>
  </aside>

  <!-- <nav style="z-index: 1;">
    <div id="mySidenav" class="sidenav">
        <img src="images/logo.png" alt="">
        <a href="index.php">Home</a>      
      <a >About Us</a>
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
</nav> -->
<main>
 

    <div id="list" style="background-color: #fff;">
       <h2>User Login</h2> 
<p>Login to access dashboard</p>


    <form action="" method="post" >
        <select name="role" id="role">
            <option value="">Select Role</option>
            <option value="driver">Driver</option>
            <option value="admin">Admin</option>
        </select><br>
        <input type="text" name="username" placeholder="User name"><br>
        <input type="password" name="password" placeholder="Password"><br>
        <input type="submit" id="submit" value="Login"><br>
        <?php
    if (!empty($message)) {
        echo '<p>' . $message . '</p>';
    }
    ?>
    </form>




    </div>
</main>



 <script src="script.js"></script>
</body>
</html>