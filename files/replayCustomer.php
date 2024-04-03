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
$sql = "SELECT profile_photo FROM admin_users WHERE id = :userId";

if ($stmt = $connection->prepare($sql)) {
    $stmt->bindParam(":userId", $userId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $stmt->bindColumn("profile_photo", $profilePhoto);
        if ($stmt->fetch()) {
            // User profile photo found, update the session
            $_SESSION["profile_photo"] = $profilePhoto;
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
                                <i class="fa-solid fa-gear"></i>
                                <span class="block">Employees</span>
                            </a>
                        </li>

                         <li>
                            <a href="projects.php">
                                <i class="fa-solid fa-gear"></i>
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
                                        <span class="block"> Super Admin</span>
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
                        <h1 class="page-heading"> Replay Customer </h1>

                            <!-- replaySubscriber.php -->
                            
<?php
// Include PHPMailer autoload
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to send email using PHPMailer
function sendEmail($recipientEmail, $subject, $message) {
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
        $mail->Subject = $subject;
        $mail->Body = $message;

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
    if(isset($_POST['subscriber_id'])) {
        $subscriber_id = $_POST['subscriber_id']; // Subscriber ID is now retrieved from the form
    } else {
        echo "Subscriber ID not provided.";
        exit;
    }
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    // Check if the subject and message are not empty
    if(empty($subject) || empty($message)) {
        echo "Subject and email body cannot be empty.";
        exit;
    }

    // Fetch subscriber's email based on the ID
    $query = "SELECT email FROM `Formdata` WHERE id = :subscriber_id";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':subscriber_id', $subscriber_id, PDO::PARAM_INT);

    // Execute the query
    if ($stmt->execute()) {
        // Fetch the subscriber's email
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $recipientEmail = $result['email'];

            // Prepare email content
            $emailTemplate = "
                <!DOCTYPE html>
                <html lang='en'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>Welcome to Lyzerslab</title>
                    <!-- Bootstrap CSS -->
                    <link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>
                    <style>
                        /* Your email template styles here */
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1 style='color: #333;'>Welcome to Lyzerslab</h1>
                        </div>
                        <div class='content'>
                            <p style='color: #666;'>$message</p>
                            <img src='https://lyzerslab.com/lyzerslab-digital-agency.webp' style='display: block; margin: 0 auto;'>
                        </div>
                        <div class='footer'>
                            <p style='color: #666;'>Best Regards,</p>
                            <p style='color: #666; margin-top: -4px; margin-bottom: 0;'>CEO, Lyzerslab</p>
                            <p style='color: #666; margin-bottom: 5px;'>Address: Uttara, Joynal Market, Dhaka-1230, Bangladesh.</p>
                            <p style='color: #666; margin-bottom: 5px;'>Contact: Mobile: +880-1824-228-717, Mail: support@lyzerslab.com</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            // Send email
            sendEmail($recipientEmail, $subject, $emailTemplate);
            echo "Email sent successfully to $recipientEmail";
        } else {
            echo "Subscriber not found.";
        }
    } else {
        echo "Error fetching subscriber's email.";
    }
}

// Retrieve subscriber ID from URL
if(isset($_GET['id'])) {
    $subscriber_id = $_GET['id'];
} else {
    echo "Subscriber ID not provided.";
    exit;
}
?>



    
    <!-- HTML form for replying to the subscriber -->
    <div class="col-md-8">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <input type="hidden" name="subscriber_id" value="<?php echo $subscriber_id; ?>">
            <label for="subject">Subject:</label><br>
            <input type="text" id="subject" name="subject" class="form-control"><br>
            <label for="message">Message:</label><br>
            <textarea id="message" name="message" rows="5" cols="50" class="form-control"></textarea><br>
            <input type="submit" value="Send Reply" class="btn btn-primary">
        </form>
    </div>




                        
                        <footer class="footer mt-5">
                            <p class="mb-0">
                                Copyright © <span>2024</span> Ecommerce . All Rights Reserved.
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
