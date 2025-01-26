<?php
// Initialize the session
session_start();


// Include config file
require_once "../auth/db-connection/config.php";

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
    <title>Admin Settings</title>
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
                        <li class="active">
                            <a href="dashboard.php">
                                <i class="fa-solid fa-table-columns"></i>
                                <span class="block">Dashboard</span>
                            </a>
                        </li>
                        
                        <li class="">
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
                        <div class="notifications">
                            <i class="fa-regular fa-bell"></i>
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
                    <div class="main flex">
                        <h1 class="page-heading"> Admin Settings </h1>
                        <div class="admin-s-wrapper flex max-w-400px">
                            <button type="button" class="btn btn-danger" onclick="showBlockedIP()">Blocked IP</button>
                            <button type="button" class="btn btn-secondary" onclick="showAccessLogs()">Access Logs</button>
                        </div>
                    </div>
                    <div class="admin-s-body mt-5">
                        <div id="blockedIPSection" class="hidden">
                            <h3>Blocked IP Section</h3>
                            <?php
                                // Check for success parameter in the URL
                                if (isset($_GET["success"]) && $_GET["success"] == 1) {
                                    echo "<div class='alert alert-success'>Blocked IP deleted successfully.</div>";
                                } elseif (isset($_GET["error"]) && $_GET["error"] == 1) {
                                    echo "<div class='alert alert-danger'>Error deleting blocked IP. Please try again.</div>";
                                }
                            ?>

                            <table id="blockedIPTable" class="table table-striped mt-3">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>IP Address</th>
                                        <th>Blocked Until</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- PHP code to fetch and display blocked IP data -->
                                    <?php
                                    // Include your database connection code here

                                    // Fetch blocked IP data from the database with pagination
                                    $limit = 20; // Number of records per page
                                    $page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? $_GET['page'] : 1;
                                    $start = ($page - 1) * $limit;

                                    $sql = "SELECT * FROM blocked_ips LIMIT $start, $limit";
                                    $stmt = $connection->prepare($sql);
                                    $stmt->execute();
                                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                    // Display the data in the table
                                    foreach ($result as $row) {
                                        echo "<tr>";
                                        echo "<td>{$row['id']}</td>";
                                        echo "<td>{$row['ip_address']}</td>";
                                        echo "<td>{$row['blocked_until']}</td>";
                                        echo "<td>
                                        <a href='../auth/backend-assets/admin-settings/del_block_ip?id={$row['id']}'>Delete</a>
                                        </td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>

                            <!-- Pagination -->
                            <ul class="pagination">
                                <?php
                                $sql = "SELECT COUNT(*) as count FROM blocked_ips";
                                $stmt = $connection->prepare($sql);
                                $stmt->execute();
                                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                $total_pages = ceil($row['count'] / $limit);

                                for ($i = 1; $i <= $total_pages; $i++) {
                                    echo "<li class='page-item'><a class='page-link' href='?page=$i'>$i</a></li>";
                                }
                                ?>
                            </ul>
                        </div>

                        <div id="accessLogsSection" class="hidden">
                            <h3>Access Logs Section</h3>
                            <?php
                                // Check for success parameter in the URL
                                if (isset($_GET["success"]) && $_GET["success"] == 1) {
                                    echo "<div id='error' class='alert alert-success'>Access log deleted successfully.</div>";
                                } elseif (isset($_GET["error"]) && $_GET["error"] == 1) {
                                    echo "<div id='error' class='alert alert-danger'>Error deleting Access log. Please try again.</div>";
                                }
                            ?>

                            <table id="accessLogsTable" class="table table-striped mt-3">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>IP Address</th>
                                        <th>Access Time</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- PHP code to fetch and display access logs data -->
                                    <?php
                                    // Fetch access logs data from the database with pagination
                                    $limit = 20; // Number of records per page
                                    $page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? $_GET['page'] : 1;
                                    $start = ($page - 1) * $limit;

                                    $sql = "SELECT * FROM access_logs LIMIT $start, $limit";
                                    $stmt = $connection->prepare($sql);
                                    $stmt->execute();
                                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);


                                    // Display the data in the table
                                    foreach ($result as $row) {
                                        echo "<tr>";
                                        echo "<td>{$row['id']}</td>";
                                        echo "<td>{$row['ip_address']}</td>";
                                        echo "<td>{$row['access_time']}</td>";
                                        echo "<td>
                                                <a href='../auth/backend-assets/admin-settings/del_aces_logs.php?id={$row['id']}'>Delete</a> |  
                                                <button class='block-unblock-btn btn btn-danger' data-id='{$row['id']}' data-blocked='{$row['blocked']}'>
                                                    " . ($row['blocked'] ? "Unblock" : "Block") . "
                                                </button>
                                            </td>";
                                        echo "</tr>";
                                    }

                                    ?>
                                </tbody>
                            </table>

                            <!-- Pagination -->
                            <ul class="pagination">
                                <?php
                                $sql = "SELECT COUNT(*) as count FROM access_logs";
                                $stmt = $connection->prepare($sql);
                                $stmt->execute();
                                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                $total_pages = ceil($row['count'] / $limit);

                                for ($i = 1; $i <= $total_pages; $i++) {
                                    echo "<li class='page-item'><a class='page-link' href='?page=$i'>$i</a></li>";
                                }
                                ?>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </main>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    
    <script>
        function toggleUserOptions() {
            var options = document.getElementById("userOptions");
            options.style.display = (options.style.display === 'flex') ? 'none' : 'flex';
        }
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
