<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../../index.php");
    exit;
}

// Include config file
require_once "../auth/db-connection/config.php";

// Handle form submission for adding a new category
$name_err = $description_err = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $description = "";

    // Validate input
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter a category name.";
    } else {
        $name = trim($_POST["name"]);
    }

    if (empty(trim($_POST["description"]))) {
        $description = ""; // Optional
    } else {
        $description = trim($_POST["description"]);
    }

    // If no errors, insert the category into the database
    if (empty($name_err)) {
        $sql = "INSERT INTO categories (name, description, created_at) VALUES (:name, :description, NOW())";

        if ($stmt = $connection->prepare($sql)) {
            $stmt->bindParam(":name", $name, PDO::PARAM_STR);
            $stmt->bindParam(":description", $description, PDO::PARAM_STR);

            if ($stmt->execute()) {
                header("location: manage_categories.php");
                exit;
            } else {
                echo "Error adding category.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="../styling/style.css">
</head>
<body style="background: #f7f7f7;">
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

            <!-- Header and Main Content -->
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
                        <h1 class="page-heading">Add New Category</h1>
                        <form method="POST">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Category Name</label>
                                    <input type="text" name="name" id="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name ?? ''); ?>">
                                    <div class="invalid-feedback"><?php echo $name_err; ?></div>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea name="description" id="description" class="form-control"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-success">Add Category</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>