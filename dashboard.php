<?php
require_once("dashboardPHP.php");
require_once 'db_conn.php';
session_start();

// Check if the session ID stored in the cookie matches the current session
if (!(isset($_COOKIE['auth']) && $_COOKIE['auth'] == session_id() && isset($_SESSION['user_type']) && $_SESSION["user_type"] == "admin")) {
    // If no valid session, redirect to login page
    header('Location: faculty.php');
    exit();
}

$conn = connect_db();
$user_id = $_SESSION['user_id'] ?? null;

// Get the counts using the functions from dashboardPHP.php
$totalCount = get_all_student_count();
$rotcCount = get_rotc_student_count();
$cwtsCount = get_cwts_student_count();

// Update department data query
$departmentData = mysqli_query($conn, "
    SELECT department, 
           SUM(CASE WHEN nstp = 'ROTC' THEN 1 ELSE 0 END) AS rotc_count,
           SUM(CASE WHEN nstp = 'CWTS' THEN 1 ELSE 0 END) AS cwts_count
    FROM tbl_cwts
    WHERE department IS NOT NULL
    AND LEFT(school_id, 2) REGEXP '^[0-9]{2}'
    GROUP BY department
") or die('query failed');

// Prepare data for the first chart
$chartData = [];
while ($row = mysqli_fetch_assoc($departmentData)) {
    $chartData[] = [
        'department' => $row['department'],
        'rotc' => (int)$row['rotc_count'],
        'cwts' => (int)$row['cwts_count']
    ];
}

// Convert PHP array to JSON
$chartDataJson = json_encode($chartData);

// Update year data query to handle any two-digit year
$yearData = mysqli_query($conn, "
    SELECT 
        CONCAT('20', LEFT(school_id, 2)) as year,
        SUM(CASE WHEN nstp = 'ROTC' THEN 1 ELSE 0 END) as rotc_count,
        SUM(CASE WHEN nstp = 'CWTS' THEN 1 ELSE 0 END) as cwts_count
    FROM tbl_cwts 
    WHERE LEFT(school_id, 2) REGEXP '^[0-9]{2}'
    GROUP BY LEFT(school_id, 2)
    ORDER BY LEFT(school_id, 2) ASC
") or die('query failed');

$lineChartData = [];
while ($row = mysqli_fetch_assoc($yearData)) {
    $lineChartData[] = [
        'year' => $row['year'],
        'rotc' => (int)$row['rotc_count'],
        'cwts' => (int)$row['cwts_count']
    ];
}

// Convert PHP array to JSON
$lineChartDataJson = json_encode($lineChartData);
?>

<!DOCTYPE html>
<html lang="en">
<meta charset="UTF-8">
    <link rel="icon" type="image/png" href="slsulogo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    
    <!-- Add jQuery first since Highcharts depends on it -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    
    <!-- Highcharts scripts with integrity checks -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/data.js"></script>
    <script src="https://code.highcharts.com/modules/drilldown.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    
    <!-- Your other resources -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="dashboard.css">
    
    <!-- Add this script to verify loading -->
    <script>
        window.onload = function() {
            if (typeof Highcharts === 'undefined') {
                console.error('Highcharts failed to load');
            } else {
                console.log('Highcharts loaded successfully');
                // Initialize your charts here
                initializeCharts();
            }
        };
    </script>
</head>

<body>
    <style>

body {
    background: url('backgroundss.jpg') no-repeat center center fixed;
    background-size: cover;
    height: 100vh;
    display: flex;
    flex-direction: column;
    margin: 0;
    padding: 0;
    overflow-x: hidden;
}

        .header p {
            margin-left: 150px;
            font-size: 20px;
            color: white;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: -250px;
            top: 0;
            width: 250px;
            height: 100%;
            background: #096c37;
            transition: all .5s ease;
            z-index: 1000;
            overflow-y: auto;
        }

        /* Sidebar header */
        .sidebar header {
    margin-top: -5px;
    font-size: 22px;
    color: white;
    text-align: center;
    line-height: 43.5px;
    background: #096c37;
    user-select: none;
}

        /* Sidebar links styling */
        .sidebar ul a {
            display: block;
            line-height: 65px;
            font-size: 20px;
            color: white;
            padding-left: 40px;
            box-sizing: border-box;
            border-top: 1px solid rgba(255, 255, 255, .1);
            border-bottom: 1px solid black;
            transition: .4s;
        }

        /* Hover effect for sidebar links */
        ul li:hover a {
            padding-left: 50px;
        }

        /* Icon styles inside sidebar */
        .sidebar ul a i {
            margin-right: 16px;
        }

        /* Logout link specific styling */
        .sidebar ul a.logout-link {
            color: white; /* Set the text color to red */
        }

        /* Logout link hover effect */
        ul li:hover a.logout-link {
            padding-left: 50px;
            color: #ff5c5c; /* Lighter red on hover */
        }

        /* Sidebar toggle button */
        #check {
            display: none;
        }

        /* Styling for the open button */
        label #btn,
        label #cancel {
            position: absolute;
            cursor: pointer;
            background: #0a3a20;
            border-radius: 3px;
            z-index: 1001;
        }

        /* Button to open the sidebar */
        label #btn {
            left: 20px;
            top: 130px;
            font-size: 35px;
            color: white;
            padding: 6px 12px;
            transition: all .5s;
        }

        /* Button to close the sidebar */
        label #cancel {
            z-index: 1111;
            left: -195px;
            top: 170px;
            font-size: 30px;
            color: #fff;
            padding: 4px 9px;
            transition: all .5s ease;
        }

        /* Toggle: When checked, open the sidebar */
        #check:checked~.sidebar {
            left: 0;
        }

        /* Hide the open button and show the close button when the sidebar is open */
        #check:checked~label #btn {
            left: 200px;
            opacity: 0;
            pointer-events: none;
        }

        /* Move the close button when the sidebar is open */
        #check:checked~label #cancel {
            left: 195px;
        }

        /* Ensure the content shifts when the sidebar is open */
        #check:checked~body {
            margin-left: 250px;
        }

        .user-avatar {
            width: 80px; /* Adjust the size as needed */
            height: 80px; /* Keep it the same as width for a circle */
            border-radius: 50%; /* Makes the image circular */
            object-fit: cover; /* Ensures the image covers the area without distortion */
            margin-top: 11px; /* Center the image in the sidebar */
        }

        h2 {
            margin-top: -30px;
        }

        h5 {
    margin-bottom: -10px;
    margin-top: -15px;
    font-size: 20px;
}

        .dashboard {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 50px;
        }

        .box {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            width: 150px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .box h3 {
            margin-bottom: 10px;
            font-size: 1.2em;
            color: #333;
        }

        .box p {
            font-size: 2em;
            color: #555;
        }

        /* Highcharts */
        .charts-container {
            display: flex;
            justify-content: space-around;
            max-width: 1200px;
            margin: 50px auto;
            gap: 30px;
        }

        .highcharts-figure {
            flex: 1;
            max-width: 600px;
        }

        .highcharts-figure,
        .highcharts-data-table table {
            min-width: 320px;
            max-width: 800px;
            margin: 1em auto;
        }

        .highcharts-data-table table {
            font-family: Verdana, sans-serif;
            border-collapse: collapse;
            border: 1px solid #ebebeb;
            margin: 10px auto;
            text-align: center;
            width: 100%;
            max-width: 500px;
        }

        .highcharts-data-table caption {
            padding: 1em 0;
            font-size: 1.2em;
            color: #555;
        }

        .highcharts-data-table th {
            font-weight: 600;
            padding: 0.5em;
        }

        .highcharts-data-table td,
        .highcharts-data-table th,
        .highcharts-data-table caption {
            padding: 0.5em;
        }

        .highcharts-data-table thead tr,
        .highcharts-data-table tr:nth-child(even) {
            background: #f8f8f8;
        }

        .highcharts-data-table tr:hover {
            background: #f1f7ff;
        }

        input[type="number"] {
            min-width: 50px;
        }

.header {
    overflow: hidden;
    background-color: #0a3a20;
    color: white;
    width: 100%;
    position: relative;
    transition: all .5s ease;
}

h1 {
    margin-top: 30px;
    margin-left: 150px;
}

.header p{
    margin-left: 150px;
    font-size: 20px;
    color: white;
}

.headlogo{
    width:-100%;
    height:100px;   
    float:left;
    margin-top:10px;
    margin-left:20px;
    margin-bottom: 10px;
 }

 .firstlogo{
    width:-150%;
    height:150px;   
    float:right;
    margin-top:10px;
    margin-left:20px;
    margin-bottom: 10px;
 }

 .secondlogo{
    width:-150%;
    height:150px;   
    float:left;
    margin-top:10px;
    margin-left:20px;
    margin-bottom: 10px;
 }
 
.header {
    overflow: hidden;
    background-color: #0a3a20;
    color: white;
}

.header .p{
    color:white;
}

/* Create a wrapper for all content except sidebar */
.content-wrapper {
    transition: all .5s ease;
    position: relative;
    width: 100%;
    margin-left: 0;
    z-index: 1;
}

/* Adjust the content when sidebar is open */
#check:checked ~ .content-wrapper {
    margin-left: 150px;
}

/* Remove the existing body margin rule if present */
#check:checked ~ body {
    margin-left: 0;
}

/* Ensure header stays full width but shifts with content */
.header {
    width: 100%;
    transition: margin-left .5s ease;
}

#check:checked ~ .content-wrapper .header {
    margin-left: 100px;
}

text.highcharts-credits {
    display: none;
}
    </style>

    <input type="checkbox" id="check">
    <label for="check">
        <i class="fas fa-bars" id="btn"></i>
        <i class="fas fa-times" id="cancel"></i>
    </label>

    <div class="sidebar">
        <header>
            <!-- Move the avatar and name above the "Administrator" text -->
            <?php
                $select = mysqli_query($conn, "SELECT * FROM `user_info` WHERE id = '$user_id'") or die('query failed');
                $fetch = mysqli_fetch_assoc($select);

                if ($fetch['photo'] == '') {
                    echo '<img src="default/avatar.png" class="user-avatar">';
                }  else {
                    // Fetch the photo as a blob
                    $photoBlob = $fetch['photo'];

                    // Check if the blob is not empty
                    if (!empty($photoBlob)) {
                        // Output the image
                        echo "<img src=\"$photoBlob\" class=\"user-avatar\" >";
                    } else {
                        // Debugging output if the blob is empty
                        echo '<img src="default/avatar.png" class="user-avatar">';
                    }
                }
            ?>
            <h5><?php echo $fetch['first_name'] . ' ' . $fetch['last_name']; ?></h5>
            <header>Administrator</header>
        </header>
        <ul>
            <li><a href="homepage.php"><i class="fa-solid fa-house"></i>Homepage</a></li>
            <li><a href="dashboard.php"><i class="fas fa-qrcode"></i>Dashboard</a></li>
            <li><a href="viewgrades.php"><i class="fas fa-link"></i>View Grades</a></li>
            <li><a href="cwtsStud.php"><i class="fa-solid fa-user"></i>CWTS Students</a></li>
            <li><a href="rotcStud.php"><i class="fa-solid fa-user"></i>ROTC Students</a></li>
            <li><a href="instructor.php"><i class="fa-regular fa-user"></i>Instructor</a></li>
            <li><a href="audit_log.php"><i class="fa-solid fa-folder-open"></i>Audit Log</a></li>
            <li><a href="#" onclick="confirmLogout()" class="logout-link"><i class="fa-solid fa-power-off"></i>Logout</a></li>
        </ul>
    </div>

    <div class="content-wrapper">
        <div class="header">
            <a href="homepage.php"><img src="slsulogo.png" class="headlogo"></a>
            <h1>Southern Luzon State University</h1>
            <p>National Service Training Program</p>
        </div>
    </div>

        <div class="dashboard">
            <div class="box">
                <h3>Total Students</h3>
                <p id="total-students"><?php echo $totalCount; ?></p>
            </div>
            <div class="box">
                <h3>ROTC Students</h3>
                <p id="rotc-students"><?php echo $rotcCount; ?></p>
            </div>
            <div class="box">
                <h3>CWTS Students</h3>
                <p id="cwts-students"><?php echo $cwtsCount; ?></p>
            </div>
        </div>

        <div class="charts-container">
            <figure class="highcharts-figure">
                <div id="container1"></div>
                <table id="datatable" style="display: none;">
                    <thead>
                        <tr>
                            <th></th>
                            <th>CWTS</th>
                            <th>ROTC</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>CABHA</th>
                            <td>100</td>
                            <td>200</td>
                        </tr>
                        <tr>
                            <th>CAg</th>
                            <td>300</td>
                            <td>100</td>
                        </tr>
                        <tr>
                            <th>CAM</th>
                            <td>400</td>
                            <td>100</td>
                        </tr>
                        <tr>
                            <th>CAS</th>
                            <td>400</td>
                            <td>600</td>
                        </tr>
                        <tr>
                            <th>CEN</th>
                            <td>800</td>
                            <td>400</td>
                        </tr>
                        <tr>
                            <th>CIT</th>
                            <td>200</td>
                            <td>700</td>
                        </tr>
                    </tbody>
                </table>
            </figure>

            <figure class="highcharts-figure">
                <div id="container2"></div>
            </figure>
            
            <!-- New third chart -->
            <figure class="highcharts-figure">
                <div id="container3"></div>
            </figure>
        </div>
    </div>



    <script>
        function initializeCharts() {
            // First chart (column chart)
            Highcharts.chart('container1', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: 'Student Count by Department (CWTS vs ROTC)'
                },
                xAxis: {
                    categories: <?php echo json_encode(array_column($chartData, 'department')); ?>,
                    title: {
                        text: 'Department'
                    }
                },
                yAxis: {
                    allowDecimals: false,
                    title: {
                        text: 'Student Count'
                    }
                },
                series: [{
                    name: 'ROTC',
                    data: <?php echo json_encode(array_column($chartData, 'rotc')); ?>
                }, {
                    name: 'CWTS',
                    data: <?php echo json_encode(array_column($chartData, 'cwts')); ?>
                }]
            });

            // Second chart (pie chart)
            Highcharts.chart('container2', {
                chart: {
                    type: 'pie'
                },
                title: {
                    text: 'Distribution of Students by Program'
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            distance: -50,
                            format: '{point.percentage:.1f}%',
                            style: {
                                fontSize: '14px',
                                fontWeight: 'bold',
                                color: 'white',
                                textOutline: 'none'
                            }
                        },
                        showInLegend: true
                    }
                },
                legend: {
                    enabled: true,
                    align: 'right',
                    layout: 'vertical',
                    verticalAlign: 'middle'
                },
                series: [{
                    name: 'Students',
                    colorByPoint: true,
                    data: [{
                        name: 'ROTC',
                        y: <?php echo get_rotc_student_count(); ?>,
                        sliced: true,
                        selected: true
                    }, {
                        name: 'CWTS',
                        y: <?php echo get_cwts_student_count(); ?>
                    }]
                }]
            });

            // Third chart (line graph - students per school year)
            Highcharts.chart('container3', {
                chart: {
                    type: 'line'
                },
                title: {
                    text: 'Number of Students Each School Year'
                },
                xAxis: {
                    categories: <?php echo json_encode(array_column($lineChartData, 'year')); ?>,
                    title: {
                        text: 'School Year'
                    }
                },
                yAxis: {
                    title: {
                        text: 'Number of Students'
                    },
                    min: 0,
                    allowDecimals: false
                },
                plotOptions: {
                    line: {
                        dataLabels: {
                            enabled: true,
                            format: '{y}'
                        },
                        marker: {
                            enabled: true,
                            radius: 4
                        }
                    }
                },
                tooltip: {
                    formatter: function() {
                        return '<b>' + this.series.name + '</b><br/>' +
                               'Year: ' + this.x + '<br/>' +
                               'Students: ' + this.y;
                    }
                },
                series: [{
                    name: 'ROTC',
                    data: <?php echo json_encode(array_column($lineChartData, 'rotc')); ?>
                }, {
                    name: 'CWTS',
                    data: <?php echo json_encode(array_column($lineChartData, 'cwts')); ?>
                }]
            });
        }

        // Add this to check if data is available
        console.log('Chart Data:', <?php echo json_encode($chartData); ?>);
        console.log('Line Chart Data:', <?php echo json_encode($lineChartData); ?>);

        function confirmLogout() {
            if (confirm("Do you want to Logout?")) {
                window.location.href = "logout.php";
            }
        }

    </script>

</body>
</html>