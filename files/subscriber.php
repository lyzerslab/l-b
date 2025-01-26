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
                        
                        <li class="active">
                            <a href="subscriber.php">
                                <i class="fa-solid fa-list"></i>
                                <span class="block">Subscriber</span>
                            </a>
                        </li>

                        <li>
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
                        <h1 class="page-heading"> Subscriber </h1>
                        <!-- Statistics -->
                        <div class="subscriber-data">
                            <?php
                            
                            // Fetch data from the subscribers table
                            $query = "SELECT * FROM subscribers";
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

                            <?php
                            use PHPMailer\PHPMailer\PHPMailer;
                            use PHPMailer\PHPMailer\Exception;
                            
                            require 'vendor/autoload.php'; // Path to PHPMailer autoload.php file
                            
                            // Function to send email using PHPMailer
                            function sendEmail($recipientEmail, $emailBody) {
                                $mail = new PHPMailer(true);
                            
                                try {
                                    // SMTP configuration
                                    $mail->isSMTP();
                                    $mail->Host = 'ecommerce.glassfittingserviceinriyadh.com'; // SMTP server
                                    $mail->SMTPAuth = true;
                                    $mail->Username = 'lyzerslab@ecommerce.glassfittingserviceinriyadh.com'; // SMTP username
                                    $mail->Password = '@FXS-udGTq];'; // SMTP password
                                    $mail->SMTPSecure = 'ssl'; // Enable SSL encryption
                                    $mail->Port = 465; // SMTP port
                            
                                    // Email content
                                    $mail->setFrom('lyzerslab@ecommerce.glassfittingserviceinriyadh.com', 'Lyzers Lab');
                                    $mail->addAddress($recipientEmail); // Recipient email
                                    $mail->isHTML(true);
                                    $mail->Subject = 'Greetings';
                                    $mail->Body = $emailBody;
                            
                                    // Send email
                                    $mail->send();
                                    return true;
                                } catch (Exception $e) {
                                    return false;
                                }
                            }
                            
                            // Check if form is submitted
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                // Get form data
                                $selectedSubscriberIds = $_POST['selectedSubscriberIds'];
                                $recipientEmails = explode(',', $_POST['recipientEmail']);
                                $emailBody = $_POST['emailBody'];
                            
                                // Send email to each recipient
                                foreach ($recipientEmails as $recipientEmail) {
                                    // Modify the email body to include the HTML content with image URL
                                    $formattedEmailBody = '<div style="background-color: #f8f8f8; padding: 20px; border-radius: 5px; font-family: Arial, sans-serif;">
                                        <h1 style="color: #333; margin-bottom: 20px;">Welcome to Lyzerslab</h1>
                                        <p style="color: #666; margin-bottom: 10px;">' . $emailBody . '</p>
                                        <img src="https://lyzerslab.com/lyzerslab-digital-agency.webp">
                                        <p style="color: #666; margin-bottom: 2px;">Best regards,</p>
                                        <p style="color: #666; margin-bottom: 0;">
                                            CEO, Lyzerslab</br>
                                            <span style="margin-top:8px; display: block; font-weight:bold;">Address:</span>
                                            Uttara, Joynal Market,</br>
                                            Dhaka-1230, Bangladesh.</br>
                                           <span style="margin-top:8px; display: block; font-weight:bold;">Contact:</span>
                                            Mobile: +880-1824-228-717</br>
                                            Mail: support@lyzerslab.com
                                        </p>
                                        </div>';
                            
                                    sendEmail($recipientEmail, $formattedEmailBody);
                                }
                            }
                            ?>

                            
                            <!-- Display fetched data in a table -->
                            <div class="table-responsive mt-4">
                                <?php
                                // Check if a message is provided in the URL
                                if (isset($_GET['message'])) {
                                    // Decode the message
                                    $message = urldecode($_GET['message']);
                                
                                    // Display the message with Bootstrap styling
                                    echo '<div id="alertMessage" class="alert alert-success" role="alert" style="display:inline-block">' . $message . '</div>';
                                }
                                ?>

                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Email</th>
                                            <th>Created At</th>
                                            <th>Action</th>
                                            <?php
                                            if($isAdmin == 1){
                                            ?>
                                            <th class="d-flex align-items-center" colspan="3">
                                                <input type="checkbox" class="form-check-input w-3 h-3" id="selectAll"> Select All
                                            </th>

                                            <?php 
                                            }
                                        ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($subscribers as $subscriber) : ?>
                                            <tr>
                                                <td><?php echo $subscriber['id']; ?></td>
                                                <td class="subscriber-email" data-id="<?php echo $subscriber['id']; ?>"><?php echo $subscriber['email']; ?></td>
                                                <td><?php echo $subscriber['created_at']; ?></td>
                                                <?php
                                                if($isAdmin == 1){
                                                ?>

                                                <td><a href="deleteSubs.php?id=<?php echo $subscriber['id']; ?>"><button type="button" class="btn btn-danger">Delete</button></a></td>
                                                <td>
                                                    <input type="checkbox" name="subscriber[]" value="<?php echo $subscriber['id']; ?>" class="form-check-input w-3 h-3 subscriber-checkbox">
                                                </td>
                                                <?php 
                                                    }
                                                ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                
                                <!-- Send Email form (hidden by default) -->
                                <form id="sendEmailForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" style="display: none;">
                                    <input type="hidden" id="selectedSubscriberIds" name="selectedSubscriberIds">
                                    <input type="hidden" id="recipientEmail" name="recipientEmail">
                                
                                    <div class="email-template">
                                        <div style="background-color: #f8f8f8; padding: 20px; border-radius: 5px; font-family: Arial, sans-serif;">
                                            <h1 style="color: #333; margin-bottom: 20px;">Welcome to Lyzerslab</h1>
                                            <textarea style="width: 100%; border: 1px solid #ccc; padding: 10px; margin-bottom: 20px;" id="emailBody" name="emailBody" rows="4" placeholder="Enter your email body"></textarea>
                                            <p style="color: #666; margin-bottom: 10px;">Best regards,</p>
                                            <p style="color: #666; margin-bottom: 0;">
                                                CEO, Lyzerslab</br>
                                                <span style="margin-top:8px; display: block; font-weight:bold;">Address:</span>
                                                Uttara, Joynal Market,</br>
                                                Dhaka-1230, Bangladesh.</br>
                                               <span style="margin-top:8px; display: block; font-weight:bold;">Contact:</span>
                                                Mobile: +880-1824-228-717</br>
                                                Mail: support@lyzerslab.com
                                            </p>
                                        </div>
                                    </div>
                                    <div class="form-group mt-3">
                                        <button type="submit" class="btn btn-primary">Send Email</button>
                                    </div>
                                </form>
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
    
    <script>
        // Function to show the send email form for selected subscribers
        function showSendEmailForm() {
            var selectedSubscriberIds = [];
            var recipientEmails = [];
            var checkboxes = document.querySelectorAll('.subscriber-checkbox:checked');
            checkboxes.forEach(function(checkbox) {
                var subscriberId = checkbox.value;
                var email = document.querySelector('.subscriber-email[data-id="' + subscriberId + '"]').textContent.trim();
                selectedSubscriberIds.push(subscriberId);
                recipientEmails.push(email);
            });
            document.getElementById('selectedSubscriberIds').value = selectedSubscriberIds.join(',');
            document.getElementById('recipientEmail').value = recipientEmails.join(',');
            
            // Show or hide the send email form based on the number of selected checkboxes
            var sendEmailForm = document.getElementById('sendEmailForm');
            sendEmailForm.style.display = selectedSubscriberIds.length > 0 ? 'block' : 'none';
        }
    
        // Add click event listeners to email cells
        document.querySelectorAll('.subscriber-email').forEach(function(cell) {
            cell.addEventListener('click', function() {
                var subscriberId = this.getAttribute('data-id');
                var checkbox = document.querySelector('.subscriber-checkbox[value="' + subscriberId + '"]');
                checkbox.checked = !checkbox.checked; // Toggle checkbox state
                showSendEmailForm();
            });
        });
    
        // Add change event listener to subscriber checkboxes
        document.querySelectorAll('.subscriber-checkbox').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                showSendEmailForm();
            });
        });
    
        // Select all checkboxes when the "Select All" checkbox is clicked
        document.getElementById('selectAll').addEventListener('change', function() {
            var checkboxes = document.querySelectorAll('.subscriber-checkbox');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = document.getElementById('selectAll').checked;
            });
            showSendEmailForm();
        });
    </script>


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
        <script>
        // Wait for the DOM to be fully loaded
        document.addEventListener("DOMContentLoaded", function() {
            // Select the alert message element
            var alertMessage = document.getElementById("alertMessage");
            
            // Hide the alert message after 5 seconds
            setTimeout(function() {
                alertMessage.style.display = "none";
            }, 5000); // 5000 milliseconds = 5 seconds
        });
    </script>
    <script src="js/main.js"></script>
</body>
</html>
