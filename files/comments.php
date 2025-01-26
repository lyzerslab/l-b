<?php
// Initialize the session
session_start();


// Include config file
require_once "../auth/db-connection/config.php";
// Check if the user is logged in
// Use BASE_URL for redirects
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: " . BASE_URL . "index.php");
    exit;
}


// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_status"])) {
    $commentId = $_POST["comment_id"];
    $status = $_POST["status"];

    if ($status === "spam") {
        // Delete comment if marked as spam
        $deleteQuery = "DELETE FROM comments WHERE id = :commentId";
        $stmt = $connection->prepare($deleteQuery);
        $stmt->bindParam(":commentId", $commentId, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        // Update status otherwise
        $updateQuery = "UPDATE comments SET status = :status WHERE id = :commentId";
        $stmt = $connection->prepare($updateQuery);
        $stmt->bindParam(":status", $status, PDO::PARAM_STR);
        $stmt->bindParam(":commentId", $commentId, PDO::PARAM_INT);
        $stmt->execute();
    }
}

// Fetch comments from the database
$query = "SELECT * FROM comments ORDER BY created_at DESC";
$stmt = $connection->prepare($query);
$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                        <li class="active">
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
                            <a href="media.php">
                                <i class="fa-regular fa-user"></i>
                                <span class="block">Media</span>
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
                    <h1 class="mb-4">Manage Comments</h1>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Blog ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Content</th>
                    <th>Created At</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comments as $comment): ?>
                <tr>
                    <td><?= htmlspecialchars($comment["id"]) ?></td>
                    <td><?= htmlspecialchars($comment["blog_id"]) ?></td>
                    <td><?= htmlspecialchars($comment["name"]) ?></td>
                    <td><?= htmlspecialchars($comment["email"]) ?></td>
                    <td><?= htmlspecialchars($comment["content"]) ?></td>
                    <td><?= htmlspecialchars($comment["created_at"]) ?></td>
                    <td><?= htmlspecialchars($comment["status"]) ?></td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="comment_id" value="<?= $comment["id"] ?>">
                            <select name="status" class="form-select form-select-sm mb-2" required>
                                <option value="pending" <?= $comment["status"] === "pending" ? "selected" : "" ?>>Pending</option>
                                <option value="approved" <?= $comment["status"] === "approved" ? "selected" : "" ?>>Approved</option>
                                <option value="spam">Spam</option>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-primary btn-sm">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

                
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
    
</body>
</html>
