<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


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
    <title>Subscriber List</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../styling/style.css">
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

                        <li class="active">
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

                         <li>
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

                    <!-- Notifications Menu -->
                    <div class="notifications-menu" id="notificationsMenu" style="display: none;">
                        <div class="notification-item">
                            <a href="#">New Users Sign Up <span class="badge badge-primary"><?php echo $new_users_count; ?></span></a>
                        </div>
                        <div class="notification-item">
                            <a href="#">New Orders <span class="badge badge-primary"><?php echo $new_orders_count; ?></span></a>
                        </div>
                        <div class="notification-item">
                            <a href="#">New Customers</a>
                            <!-- Display new customer details -->
                            <ul>
                                <?php foreach ($new_customers as $customer): ?>
                                    <li><?php echo $customer['id']; ?>: <?php echo $customer['customer_name']; ?></li>
                                    <!-- Add more customer details as needed -->
                                <?php endforeach; ?>
                            </ul>
                        </div>
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
                        <h1 class="page-heading"> Contact Form Data </h1>
                        <!-- Statistics -->
                        <div class="subscriber-data">
                            <?php
                            // Fetch data from the Formdata table
                            $query = "SELECT * FROM `formdata`";
                            $stmt = $connection->prepare($query);
                            
                            // Execute the query
                            if ($stmt->execute()) {
                                // Fetch all rows from the result set
                                $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            } else {
                                // Handle query execution error
                                echo "Error fetching subscribers: " . $stmt->errorInfo()[2];
                                exit; // Stop further execution
                            }
                            ?>
                            
                            <!-- Display fetched data in a table -->
                            <div class="table-responsive mt-4">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Service</th>
                                            <th>Web Package</th>
                                            <th>Message</th>
                                            <th>Created At</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($subscribers as $subscriber) : ?>
                                            <tr>
                                                <td><?php echo $subscriber['id']; ?></td>
                                                <td><?php echo $subscriber['name']; ?></td>
                                                <td><?php echo $subscriber['email']; ?></td>
                                                <td><?php echo $subscriber['phone']; ?></td>
                                                <td><?php echo $subscriber['service']; ?></td>
                                                <td><?php echo $subscriber['web_package']; ?></td>
                                                <td><?php echo $subscriber['message']; ?></td>
                                                <td><?php echo $subscriber['created_at']; ?></td>
<?php

// Check if user is logged in and session ID is set
if($isAdmin==1) {

?>
        <td>
            <a href="replayCustomer.php?id=<?php echo $subscriber['id']; ?>" class="btn btn-primary">Reply Email</a>
        </td>
<?php
    }
?>

                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
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


    <!-- Notifications -->
    <script>
        // Get references to the notifications icon and menu
        const notificationsIcon = document.getElementById('notificationsDropdown');
        const notificationsMenu = document.getElementById('notificationsMenu');

        // Add a click event listener to the notifications icon
        notificationsIcon.addEventListener('click', function() {
            // Toggle the display of the notifications menu
            if (notificationsMenu.style.display === 'none') {
                notificationsMenu.style.display = 'block';
            } else {
                notificationsMenu.style.display = 'none';
            }
        });
    </script>

    <script>
        function toggleUserOptions() {
            var options = document.getElementById("userOptions");
            options.style.display = (options.style.display === 'flex') ? 'none' : 'flex';
        }
            // script.js
        document.addEventListener('DOMContentLoaded', function () {
            const wrapperIcon = document.querySelector('.app-sidebar-mb');
            const appWrapperS = document.querySelector('.app-wrapper');
            const deskNav =  document.getElementById("des-nav");

        wrapperIcon.addEventListener('click', function () {
                appWrapperS.classList.toggle('show-sidebar');
            });
        deskNav.addEventListener('click', function () {
                appWrapperS.classList.remove('show-sidebar');
            });
        });
    </script>
    <script src="js/main.js"></script>
</body>
</html>
