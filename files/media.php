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

                         <li>
                            <a href="projects.php">
                                <i class="fa-solid fa-file"></i>
                                <span class="block">Projects</span>
                            </a>
                        </li>
                        <li class="active">
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
                                        // Use $_SESSION['is_admin'] to check the user role
                                        if ($_SESSION['is_admin'] == 1) {
                                            echo '<span class="block"> Super Admin</span>';
                                        } else {
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
                        <div class="container my-5">
                            <h1 class="text-center mb-4">Media Manager</h1>

                            <!-- Upload Form -->
                            <div class="card p-4 shadow-sm">
                                <h4 class="mb-3">Upload Media</h4>
                                <form id="uploadForm" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <input type="file" class="form-control" name="file" id="fileInput" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Upload</button>
                                </form>
                                <div id="message" class="mt-3 text-center"></div>
                            </div>

                            <!-- Media Library -->
                            <div class="mt-5">
                                <h2 class="mb-4">Uploaded Files</h2>
                                <div class="row row-cols-2 row-cols-md-4 g-4" id="fileList"></div>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>

    <script src="../files/js/userchart.js"></script>

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
        // Upload file via AJAX
        document.getElementById('uploadForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            const response = await fetch('../auth/backend-assets/mediaupload.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            const message = document.getElementById('message');

            if (result.success) {
                message.textContent = result.success;
                message.style.color = "green";
                loadFiles(); // Refresh the file list
            } else {
                message.textContent = result.error;
                message.style.color = "red";
            }
        });

        // Load uploaded files
        async function loadFiles() {
                const response = await fetch('../auth/backend-assets/getmedia.php'); // Fetch media data from backend
                const files = await response.json(); // Parse the response to JSON

                const fileList = document.getElementById('fileList');
                fileList.innerHTML = ''; // Clear the existing list of files

                files.forEach(file => {
                    const col = document.createElement('div');
                    col.className = 'col mb-4'; // Bootstrap column class for spacing

                    let content;

                    // Determine how to render the file based on its type
                    if (file.file_type.startsWith('image')) {
                        // Image preview
                        content = `<img src="${file.file_path}" alt="${file.file_name}" style="width: 100%; height: 150px; object-fit: cover;" class="rounded shadow">`;
                    } else if (file.file_type.startsWith('video')) {
                        // Video preview
                        content = `<video controls style="width: 100%; height: 150px; object-fit: cover;" class="rounded shadow">
                                    <source src="${file.file_path}" type="${file.file_type}">
                                    Your browser does not support the video tag.
                                </video>`;
                    } else {
                        // Link for other file types
                        content = `<div class="text-center">
                                    <a href="${file.file_path}" target="_blank" class="btn btn-outline-primary">
                                        View File
                                    </a>
                                </div>`;
                    }

                    // Build the card HTML, show the file path and a button to copy the path
                    col.innerHTML = `
                        <div class="card shadow-sm">
                            ${content}
                            <div class="card-body text-center">
                                <button class="btn btn-outline-secondary" onclick="copyFilePath('${file.file_path}')">Copy Path</button>
                            </div>
                        </div>
                    `;
                    fileList.appendChild(col);
                });
            }

            // Copy file path to clipboard
            function copyFilePath(filePath) {
                const tempInput = document.createElement('input');
                document.body.appendChild(tempInput);
                tempInput.value = filePath;
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                alert('File path copied to clipboard!');
            }

            // Load files on page load
            loadFiles();
    </script>
    
</body>
</html>
