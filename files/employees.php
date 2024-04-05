<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to the login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../../index.php");
    exit;
}

// Include config file
require_once "../auth/db-connection/config.php";

// Fetch additional user information from the database using the user ID
$userId = $_SESSION["id"];
$sql = "SELECT profile_photo, is_admin FROM admin_users WHERE id = :userId";

if ($stmt = $connection->prepare($sql)) {
    $stmt->bindParam(":userId", $userId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $stmt->bindColumn("profile_photo", $profilePhoto);
        $stmt->bindColumn("is_admin", $isAdmin); 
        if ($stmt->fetch()) {
            // User profile photo found, update the session
            $_SESSION["profile_photo"] = $profilePhoto;
             $_SESSION["is_admin"] = $isAdmin;
        } else {
            // User not found or profile photo not set, you can handle this case
        }
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }

    unset($stmt); // Close statement
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../styling/style.css">
    <script src="../files/js/main.js"></script>
</head>
<body style="background:#f7f7f7;">
    <main>
        <div class="app-wrapper">
            <div class="app-sidebar">
                <div class="side-header flex pr-3">
                    <div class="logo flex">
                        <img src="images/logo.webp" alt="logo">
                    </div>
                    <div id="des-nav" class="wrapper-n-icon">
                        <i class="fa-solid fa-bars"></i>
                        <i class="fa-solid fa-xmark close"></i>
                    </div>
                </div>
                <div class="sidebard-nav">
                    <ul>
                        <li class="">
                            <a href="dashboard.php">
                                <i class="fa-solid fa-table-columns"></i>
                                <span class="block">Dashboard</span>
                            </a>
                        </li>
                        
                        <li>
                            <a href="subscriber.php">
                                <i class="fa-solid fa-list"></i>
                                <span class="block">Subscriber</span>
                            </a>
                        </li>

                        <li class="=">
                            <a href="contact.php">
                               <i class="fa-solid fa-cart-flatbed-suitcase"></i>
                                <span class="block">Contact</span>
                            </a>
                        </li>

                        <li class="active">
                            <a href="employees.php">
                                <i class="fa-regular fa-user"></i>
                                <span class="block">Employees</span>
                            </a>
                        </li>

                         <li class="">
                            <a href="projects.php">
                                <i class="fa-solid fa-file"></i>
                                <span class="block">Projects</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="header-body">
                <div class="app-sidebar-mb">
                    <div class="nav-mb-icon">
                        <i class="fa-solid fa-bars"></i>
                    </div>
                </div>
                <div class="user flex-end">
                    <div class="search">
                        <form class="d-flex gap-3" role="search">
                            <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                            <button class="btn btn-outline-success" type="submit">Search</button>
                        </form>
                    </div>
                    <div class="account">
                        <!-- Notifications -->
                        <div class="notifications" id="notificationsDropdown">
                            <i class="far fa-bell"></i>
                        </div>
                        <!-- User  -->
                        <div class="wrap-u" onclick="toggleUserOptions()">
                            <div class="user-pro flex">
                                <?php if (isset($_SESSION["profile_photo"])) : ?>
                                    <img src="<?php echo $_SESSION["profile_photo"]; ?>" alt="Profile Photo">
                                <?php else : ?>
                                    <!-- Provide a default image or alternative content -->
                                    <img src="default_profile_photo.jpg" alt="Default Profile Photo">
                                <?php endif; ?>
                            </div>
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <!-- User Dropdown -->
                        <div id="userOptions" class="u-pro-options">
                            <div class="flex-col w-full">
                                <div class="u-name">
                                    <div class="user-pro flex">
                                        <?php if (isset($_SESSION["profile_photo"])) : ?>
                                            <img src="<?php echo $_SESSION["profile_photo"]; ?>" alt="Profile Photo">
                                        <?php else : ?>
                                            <!-- Provide a default image or alternative content -->
                                            <img src="default_profile_photo.jpg" alt="Default Profile Photo">
                                        <?php endif; ?>
                                    </div>

                                    <div class="flex-col">
                                        <span class="block"><?php echo strtoupper(htmlspecialchars($_SESSION["username"])); ?></span>
                                        <?php
                                        if($isAdmin==1){
                                            echo '<span class="block"> Super Admin</span>';
                                        }else{
                                            echo '<span class="block"> Admin </span>';
                                        }
                                        ?>
                                    </div>
                                </div>

                                <ul class="pro-menu">
                                    <li><a href="">Profile</a></li>
                                    <li><a href="admin-settings.php">Admin Settings</a></li>
                                    <li><a href="../auth/backend-assets/logout.php" class="">Log out</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="h-container">
                    <div class="main">
                        <div class="header flex">
                            <h1 class="page-heading"> Employees </h1>
                            <?php
                            if($isAdmin){
                            ?>
                           <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addEmployeesModal">Add Member</button>
                           <?php
                            }
                            ?>
                        </div>

                        <!-- Modal -->
                        <div class="modal fade" id="addEmployeesModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLongTitle">Add New Employee</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="employeeForm" enctype="multipart/form-data">
                                            <div class="form-group mb-2">
                                                <label for="employeeName">Employee Name:</label>
                                                <input type="text" class="form-control" id="employeeName" placeholder="Enter employee name">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label for="employeePhoto">Your Photo:</label>
                                                <input type="file" class="form-control-file" id="employeePhoto">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label for="employeeDesignation">Designation:</label>
                                                <input type="text" class="form-control" id="employeeDesignation" placeholder="Enter employee designation">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label for="totalExperience">Total Experience:</label>
                                                <input type="text" class="form-control" id="totalExperience" placeholder="Enter total experience">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label for="joiningDate">Joining Date:</label>
                                                <input type="date" class="form-control" id="joiningDate">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label for="fieldOfExpertise">Field of Expertise:</label>
                                                <input type="text" class="form-control" id="fieldOfExpertise" placeholder="Enter field of expertise (e.g., programming, web design)">
                                                <small id="fieldOfExpertiseHelp" class="form-text text-muted">You can add multiple skills separated by commas.</small>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label for="currentAddress">Current Address:</label>
                                                <input type="text" class="form-control" id="currentAddress" placeholder="Enter current address">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label for="presentAddress">Present Address:</label>
                                                <input type="text" class="form-control" id="presentAddress" placeholder="Enter present address">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label for="hireType">Hire Type:</label>
                                                <select class="form-control" id="hireType">
                                                    <option value="remote">Remote</option>
                                                    <option value="hybrid">Hybrid</option>
                                                    <option value="inhouse">In-house</option>
                                                </select>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label for="expertType">Expert In</label>
                                                <select class="form-control" id="expertType">
                                                    <option value="ui/ux">UI/UX</option>
                                                    <option value="fulltsack dev">Full-stack</option>
                                                    <option value="front-end dev">Front-end</option>
                                                    <option value="back-end dev">Back-end</option>
                                                    <option value="dev-ops">Dev-Ops</option>
                                                    <option value="wp-customization">WP Customization</option>
                                                    <option value="plugin-dev">Plugin Development</option>
                                                    <option value="theme-dev">Theme Development</option>
                                                    <option value="ml-engineer">Machine Learning</option>
                                                </select>
                                            </div>
                                            <div class="modal-footer mt-3">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Save changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php
                        try {
                            // Fetch employees data from the database
                            $sql = "SELECT * FROM employees";
                            $result = $connection->query($sql);
                        ?>

                        <div class="employees-data-table">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered mt-4">
                                    <thead class="thead-dark">
                                        <tr>
                                        <th>Employee ID</th>
                                        <th>Photo</th>
                                        <th>Employee Name</th>
                                        <th>Designation</th>
                                        <th>Total Experience</th>
                                        <th>Expert In</th>
                                        <th>Joining Date</th>
                                        <th>Field of Expertise</th>
                                        <th>Current Address</th>
                                        <th>Present Address</th>
                                        <th>Hire Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($result as $row): ?>
                                        <tr>
                                            <td><?php echo $row['employeeID']; ?></td>
                                            <td><img src="../auth/backend-assets/employee-photo/<?php echo $row['photo']; ?>" alt="Employee Photo" style="width: 90px; height: 90px; object-fit: contain;"></td>
                                            <td><?php echo $row['employeeName']; ?></td>
                                            <td><?php echo $row['designation']; ?></td>
                                            <td><?php echo $row['totalExperience']; ?></td>
                                            <td><?php echo $row['expertType']; ?></td>
                                            <td><?php echo $row['joiningDate']; ?></td>
                                            <td><?php echo $row['fieldOfExpertise']; ?></td>
                                            <td><?php echo $row['currentAddress']; ?></td>
                                            <td><?php echo $row['presentAddress']; ?></td>
                                            <td><?php echo $row['hireType']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <?php
                        } catch (PDOException $e) {
                            echo "Error: " . $e->getMessage();
                        }
                        ?>
                                        
                        <footer class="footer mt-5">
                            <p class="mb-0">
                                Copyright © <span>2024</span> Lyzerslab . All Rights Reserved.
                            </p>
                        </footer>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="./js/employees.js"></script>

    <script>
        // Function to handle form submission
        document.getElementById("employeeForm").addEventListener("submit", function(event) {
            event.preventDefault(); // Prevent default form submission

            // Retrieve input values
            var employeeName = document.getElementById("employeeName").value;
            var employeePhoto = document.getElementById("employeePhoto").files[0]; // Get the first file
            var employeeDesignation = document.getElementById("employeeDesignation").value;
            var totalExperience = document.getElementById("totalExperience").value;
            var joiningDate = document.getElementById("joiningDate").value;
            var fieldOfExpertise = document.getElementById("fieldOfExpertise").value;
            var currentAddress = document.getElementById("currentAddress").value;
            var presentAddress = document.getElementById("presentAddress").value;
            var expertType = document.getElementById("expertType").value;
            var hireType = document.getElementById("hireType").value;

            // Split skills input by comma and trim whitespace
            var skillsArray = fieldOfExpertise.split(",").map(function(skill) {
                return skill.trim();
            });

            // Create a FormData object to send form data to the server
            var formData = new FormData();
            formData.append("employeeName", employeeName);
            formData.append("employeePhoto", employeePhoto);
            formData.append("employeeDesignation", employeeDesignation);
            formData.append("totalExperience", totalExperience);
            formData.append("joiningDate", joiningDate);
            formData.append("skillsArray", JSON.stringify(skillsArray)); // Convert skills array to JSON string
            formData.append("currentAddress", currentAddress);
            formData.append("presentAddress", presentAddress);
            formData.append("expertType", expertType);
            formData.append("hireType", hireType);

            // Send form data to the server using fetch API
            fetch("../auth/backend-assets/submit_employee.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                console.log(data); // Output response from the server
                // Clear form fields on successful submission
                document.getElementById("employeeForm").reset();
            })
            .catch(error => {
                console.error("Error:", error);
                // Handle any errors that occur during the fetch request
            });
        });
    </script>


    
</body>
</html>
