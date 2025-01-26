<?php
// Initialize the session
session_start();


// Include config file
require_once "../auth/db-connection/config.php";

// Check if the user is logged in, if not then redirect him to the login page
// Use BASE_URL for redirects
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: " . BASE_URL . "index.php");
    exit;
}

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
    <title>Dashbaord</title>
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

                        <li class="">
                            <a href="employees.php">
                                <i class="fa-regular fa-user"></i>
                                <span class="block">Employees</span>
                            </a>
                        </li>

                         <li class="active">
                            <a href="projects.php">
                                <i class="fa-solid fa-file"></i>
                                <span class="block">Projects</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="media.php">
                                <i class="fa-regular fa-user"></i>
                                <span class="block">Media Manager</span>
                            </a>
                        </li>
                         <!-- Blog Menu with Dropdown -->
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-blog"></i>
                                <span class="block">Blog</span>
                            </a>
                                                       <ul class="dropdown-menu" style="margin-top: -2px;">
                                <li><a href="manage_posts.php"><i class="fa-solid fa-list"></i> Manage Posts</a></li>
                                <li><a href="add_post.php"><i class="fa-solid fa-plus"></i> Add New Post</a></li>
                                <li><a href="manage_categories.php"><i class="fa-solid fa-tags"></i> Manage Categories</a></li>
                                <li><a href="comments.php"><i class="fa-solid fa-tags"></i> Manage Comments</a></li>
                            </ul>
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
                            <h1 class="page-heading"> Projects </h1>
                            
                            <?php
                            if($isAdmin){
                            ?>
                           <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addProjectsModal">Add Projects</button>

                           <?php
                            }
                           ?>

                        </div>

                        <!-- Modal -->
                        <div class="modal fade" id="addProjectsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLongTitle">Add New Project</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="addProjectForm" method="POST" action="../auth/backend-assets/submit_project.php">
                                            <div class="form-group">
                                                <label for="projectName">Project Name:</label>
                                                <input type="text" class="form-control" id="projectName" name="projectName" placeholder="Enter project name">
                                            </div>
                                            <div class="form-group">
                                                <label for="projectDescription">Project Description:</label>
                                                <textarea class="form-control" id="projectDescription" name="projectDescription" rows="3" placeholder="Enter project description"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="clientsName">Client's Name:</label>
                                                <input type="text" class="form-control" id="clientsName" name="clientsName" placeholder="Enter client's name">
                                            </div>
                                            <div class="form-group">
                                                <label for="projectType">Project Type:</label>
                                                <input type="text" class="form-control" id="projectType" name="projectType" placeholder="Enter project type">
                                            </div>
                                            <div class="form-group">
                                                <label for="duration">Duration:</label>
                                                <input type="text" class="form-control" id="duration" name="duration" placeholder="Enter duration">
                                            </div>
                                            <div class="form-group">
                                                <label for="projectStart">Project Start:</label>
                                                <input type="date" class="form-control" id="projectStart" name="projectStart">
                                            </div>
                                            <div class="form-group">
                                                <label for="projectEnd">Project End:</label>
                                                <input type="date" class="form-control" id="projectEnd" name="projectEnd">
                                            </div>

                                            <?php
                                            $sql = "SELECT * FROM employees";
                                            $result = $connection->query($sql);

                                            // Check if the query was successful
                                            if ($result) {
                                                // If there are rows in the result set, fetch them as an associative array
                                                $employees = $result->fetchAll(PDO::FETCH_ASSOC);
                                            } else {
                                                // If there was an error in the query, handle it appropriately
                                                echo "Error: " . $connection->errorInfo()[2]; // Display error message
                                                // You may want to handle the error differently based on your application's requirements
                                            }
                                            ?>

                                            <div class="form-group">
                                                <label for="assign">Assign Employees:</label>
                                                <div class="list-group maxH">
                                                    <?php foreach ($employees as $employee): ?>
                                                        <label class="list-group-item">
                                                            <input type="checkbox" name="employees[]" value="<?php echo $employee['employeeID']; ?>">
                                                            <?php echo $employee['employeeName']; ?>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>

                                            <!-- Modal footer with submit button -->
                                            <div class="modal-footer mt-2">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Save changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Running Projects -->
                        <div class="running-projects table-responsive mt-4">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Project Name</th>
                                        <th>Project Description</th>
                                        <th>Client's Name</th>
                                        <th>Project Type</th>
                                        <th>Duration</th>
                                        <th>Project Start</th>
                                        <th>Project End</th>
                                        <th>Notes</th>
                                        <th>Assigned Employees</th>
                                        <th>Actions</th> <!-- New column for actions -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Fetch projects data from the database
                                    $sql = "SELECT * FROM projects";
                                    $result = $connection->query($sql);
                                    if ($result && $result->rowCount() > 0) {
                                        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                            // Split assigned employees into an array
                                            $assignedEmployees = explode(',', $row['AssignTo']);
                                            // Fetch employee names
                                            $employeeNames = array();
                                            foreach ($assignedEmployees as $employeeID) {
                                                // Fetch employee name from database using $employeeID
                                                $sql_employee = "SELECT employeeName FROM employees WHERE employeeID = :employeeID";
                                                $stmt_employee = $connection->prepare($sql_employee);
                                                $stmt_employee->execute([':employeeID' => $employeeID]);
                                                $employee = $stmt_employee->fetch(PDO::FETCH_ASSOC);
                                                if ($employee) {
                                                    $employeeNames[] = $employee['employeeName'];
                                                }
                                            }
                                            ?>
                                            <tr>
                                                <td><?= $row['ProjectName'] ?></td>
                                                <td><?= $row['ProjectDescription'] ?></td>
                                                <td><?= $row['ClientName'] ?></td>
                                                <td><?= $row['ProjectType'] ?></td>
                                                <td><?= $row['Duration'] ?></td>
                                                <td><?= $row['ProjectStart'] ?></td>
                                                <td><?= $row['ProjectEnd'] ?></td>
                                                <td><?= $row['ProjectNote'] ?></td>
                                                <td>
                                                    <?php foreach ($employeeNames as $employeeName): ?>
                                                        <span class="badge bg-success p-1"><?= $employeeName ?></span>
                                                    <?php endforeach; ?>
                                                </td>
                                                <td>
                                                    <!-- Button trigger modal -->
                                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#noteModal<?= $row['ProjectID'] ?>">
                                                        Add Note
                                                    </button>
                                                </td>
                                            </tr>
                                            <!-- Modal -->
                                            <div class="modal fade" id="noteModal<?= $row['ProjectID'] ?>" tabindex="-1" role="dialog" aria-labelledby="noteModalLabel<?= $row['ProjectID'] ?>" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="noteModalLabel<?= $row['ProjectID'] ?>">Add Note for <?= $row['ProjectName'] ?></h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <!-- Note form -->
                                                            <form id="noteForm<?= $row['ProjectID'] ?>">
                                                                <div class="form-group">
                                                                    <label for="note">Note:</label>
                                                                    <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                                                                </div>
                                                            </form>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                            <button type="button" class="btn btn-primary" onclick="saveNote(<?= $row['ProjectID'] ?>)">Save Note</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                    <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="9">No projects found.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
              
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

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="./js/employees.js"></script>

    <script>
        // When the form is submitted
        document.getElementById("addProjectForm").addEventListener("submit", function(event) {
            event.preventDefault(); // Prevent the default form submission

            // Get the form data
            var formData = new FormData(this);

            // Send the form data to the server using AJAX
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(data => {
                console.log(data); // Log the response from the server
                // Clear the form inputs
                document.getElementById("addProjectForm").reset();
                // Show a success message popup (you can replace this with your preferred method)
                alert("Project added successfully.");
            })
            .catch(error => {
                console.error('There was a problem with your fetch operation:', error);
                // Handle errors here
            });
        });
    </script>

    <!-- JavaScript for saving notes -->
    <script>
        function saveNote(projectID) {
            // Get the note from the form
            var note = document.getElementById('note').value;

            // Send an AJAX request to save the note
            fetch('../auth/backend-assets/save_note.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ projectID: projectID, note: note })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(data => {
                console.log(data); // Log the response from the server
                // Display success alert
                alert('Note saved successfully!');
                // You can perform additional actions here, such as hiding the modal after successfully saving the note
                $('#noteModal' + projectID).modal('hide');
            })
            .catch(error => {
                console.error('There was a problem with your fetch operation:', error);
                // Handle errors here
            });
        }
    </script>


    
</body>
</html>
