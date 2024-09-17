<?php
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$data = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servername = "localhost";
    $username = "gitaalli_trans";
    $password = "Gb4J7guPmkz5BEyJ3du3";
    $dbname = "gitaalli_trans";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if (isset($_POST['record_date'])) {
        $record_date = $_POST['record_date'];
        $sql = "SELECT patient_first_name, patient_last_name, record_time, record_type, location 
                FROM detailed_records 
                WHERE record_date = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $record_date);
        $stmt->execute();
        $stmt->bind_result($patient_first_name, $patient_last_name, $record_time, $record_type, $location);

        while ($stmt->fetch()) {
            $name = $patient_first_name . ' ' . $patient_last_name;
            if (!isset($data[$name])) {
                $data[$name] = array();
            }
            if (!isset($data[$name][$location])) {
                $data[$name][$location] = array('Pick up' => '', 'Drop off' => '');
            }
            if ($record_type === 'Pick up') {
                $data[$name][$location]['Pick up'] = $record_time;
            } else if ($record_type === 'Drop off') {
                $data[$name][$location]['Drop off'] = $record_time;
            }
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/logo.png">
    <script src="https://kit.fontawesome.com/f0fb58e769.js" crossorigin="anonymous"></script>
    <title>Generate Data</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table, tr, td, th {
            border: 1px solid black;
            border-collapse: collapse;padding:0;
        }
        th,td{
            background-color: #fff;text-align:center;padding:4px;font-size:13px;
        }
        #reportSection {
            display: none;
            width: auto;
            height: auto;
            background-color: #fff;
            z-index: 2;
            padding: 20px;
            overflow: auto;font-size:13px;
        }
        #topz{
            display:flex;flex-direction:row;margin-bottom:10px;flex-wrap:nowrap;font-size:13px;
        }
        .num{
            width: 40%;border:1px solid black;padding-left:10px;font-weight:500;
        }
      
        .num1{
            width: 20%;padding-left:10px;font-weight:500
        }
       
        #num2{
            border:1px solid black;
        }
    </style>
      <style>
        @media print {
            @page {
                margin: 20mm;
            }
            body {
                margin: 0;
            }
            #reportSection {
                page-break-inside: avoid;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                border: 1px solid black;
                padding: 8px;
                text-align: center;
            }
            .page-break {
                display: block;
                page-break-before: always;
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
        </div>
    </header>
    <aside> 
        <div>
            <img src="images/logo.png" alt="">
        </div> 
        <div onclick="openNav()">
            <div class="container" onclick="myFunction(this)" id="sideNav">
                <p style="border: 1px solid white; padding: 10px; border-radius: 7px; color: white;">Menu</p>
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
                    <h2>Generate Daily Transportation Record</h2> 
                    <p>Enter Record date to generate transport record</p>
                    <form id="generateForm" method="POST">
                        <label for="record_date">Record date</label><br>
                        <input type="date" name="record_date" id="record_date" required><br>
                        <input type="submit" id="submit" value="Generate">
                    </form>
                </div>
            

        <section id="reportSection" <?php if ($_SERVER['REQUEST_METHOD'] === 'POST') echo 'style="display:block"'; ?>>
    <?php
    function formatDate($date) {
        return date('d/m/Y', strtotime($date));
    }

    function formatTime($time) {
        return date('g:i A', strtotime($time));
    }

    if (!empty($data)) {
        $rowsPerPage = 15; 
        $numPages = ceil(count($data) / $rowsPerPage);
        $pageIndex = 1;

        foreach (array_chunk($data, $rowsPerPage, true) as $pageData) {
            if ($pageIndex > 1) {
                echo '<div class="page-break"></div>';
            }
            echo '<div class="printed-page">';
            echo '<div class="header">';
            echo '<div style="display:flex;flex-direction:row;justify-content:space-between">';
            echo '<span><img src="images/texas.png" alt="" width="200px"></span>';
            echo '<span>';
            echo '<p style="font-size:12px"><b>Form 3682</b></p>';
            echo '<p style="font-size:12px">October 2004-E</p>';
            echo '</span>';
            echo '</div>';

            echo '<div style="text-align: center; line-height: 10px; font-size: 14px">';
            echo '<p>Day Activity and Health Services</p>';
            echo '<h3>Daily Transportation Record</h3>';
            echo '</div>';

            echo '<div id="topz">';
            echo '<div class="num">';
            echo '<p>Name of Facility:</p>';
            echo '<p> Brightway Day Center</p>';
            echo '</div>';
            echo '<div class="num1" id="num2">';
            echo '<p>Vendor No:</p>';
            echo '<p>105655</p>';
            echo '</div>';
            echo '<div class="num1" id="num2">';
            echo '<p>Date: </p>';
            echo '<p> ' . formatDate($record_date) . '</p>';
            echo '</div>';
            echo '<div class="num1">';
            echo "<p>Page {$pageIndex} of {$numPages}</p>";
            echo '</div>';
            echo '</div>';
            echo '</div>'; 

            echo '<table id="reportTable">';
            echo '<tr>';
            echo '<th rowspan="2">No</th>';
            echo '<th rowspan="2">Individual Name</th>';
            echo '<th colspan="2">Time</th>';
            echo '<th colspan="2">Time</th>';
            echo '</tr>';
            echo '<tr>';
            echo '<th>Pick Up</th>';
            echo '<th>Drop Off</th>';
            echo '<th>Pick Up</th>';
            echo '<th>Drop Off</th>';
            echo '</tr>';

            $i = 1 + ($rowsPerPage * ($pageIndex - 1));
            foreach ($pageData as $name => $times) {
                echo "<tr>";
                echo "<td>{$i}</td>";
                echo "<td>{$name}</td>";
                echo "<td>" . (!empty($times['Home']['Pick up']) ? formatTime($times['Home']['Pick up']) : '') . "</td>";
                echo "<td>" . (!empty($times['Daycare Facility']['Drop off']) ? formatTime($times['Daycare Facility']['Drop off']) : '') . "</td>";
                echo "<td>" . (!empty($times['Daycare Facility']['Pick up']) ? formatTime($times['Daycare Facility']['Pick up']) : '') . "</td>";
                echo "<td>" . (!empty($times['Home']['Drop off']) ? formatTime($times['Home']['Drop off']) : '') . "</td>";
                echo "</tr>";
                $i++;
            }

            echo '</table>';

            echo '<div class="footer">';
            echo '<div style="text-align:right;font-size:13px">';
            echo '<p><b>I certify that this information is true and correct:</b>____________________________________</p>';
            echo '<p style="position:relative;right:5%">Signature - driver</p>';
            echo '</div>';
            echo '</div>'; 

            echo '</div>'; 
            $pageIndex++;
        }
    }
    ?>

    <button id="button1" onclick="closeReport()">Close</button>
    <button id="button2" onclick="printReport()">Print</button>
</section>


            </div>  
        </div>
    </main>

    

    <script>
    function closeReport() {
        document.getElementById('reportSection').style.display = 'none';
    }

    function printReport() {
        document.getElementById('button1').style.display = 'none';
        document.getElementById('button2').style.display = 'none';
        var printContents = document.getElementById('reportSection').innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        window.location.reload();
    }
</script>
 <script src="script.js"></script>
</body>
</html>
