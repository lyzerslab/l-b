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


// Initialize error variables
$title_err = $slug_err = $content_err = $category_id_err = $status_err = $image_err = $tags_err = $success = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $slug = $content = $featured_image = $category_id = $status = $tags = "";

    // Validate input
    if (empty(trim($_POST["title"]))) {
        $title_err = "Please enter the title.";
    } else {
        $title = trim($_POST["title"]);
        // Generate slug from title
        $slug = strtolower(str_replace(" ", "-", $title)); // Simple slug generation

        // Check if the slug already exists in the database
        $check_slug_sql = "SELECT id FROM blogs WHERE slug = :slug";
        if ($check_slug_stmt = $connection->prepare($check_slug_sql)) {
            $check_slug_stmt->bindParam(":slug", $slug, PDO::PARAM_STR);
            $check_slug_stmt->execute();

            // If the slug exists, modify it to make it unique
            $slug_exists = $check_slug_stmt->fetch(PDO::FETCH_ASSOC);
            if ($slug_exists) {
                $slug_err = "This slug is already in use.";
                // Append a unique number to the slug
                $original_slug = $slug;
                $counter = 1;
                while ($slug_exists) {
                    $slug = $original_slug . '-' . $counter;
                    $check_slug_stmt->execute();  // Re-run the query
                    $slug_exists = $check_slug_stmt->fetch(PDO::FETCH_ASSOC);
                    $counter++;
                }
            }
        }
    }

    if (empty(trim($_POST["content"]))) {
        $content_err = "Please enter the content of the post.";
    } else {
        $content = trim($_POST["content"]);
    }

    if (empty(trim($_POST["category_id"]))) {
        $category_id_err = "Please select a category.";
    } else {
        $category_id = trim($_POST["category_id"]);
    }

    if (empty(trim($_POST["status"]))) {
        $status_err = "Please select a status.";
    } else {
        $status = trim($_POST["status"]);
    }

    // Image upload validation (Featured Image)
    if ($_FILES["featured_image"]["error"] == 0) {
        $allowed_types = ["image/jpeg", "image/png", "image/gif", "image/webp", "image/avif"];
        if (!in_array($_FILES["featured_image"]["type"], $allowed_types)) {
            $image_err = "Only JPG, PNG, GIF, webp, and AVIF images are allowed.";
        } else {
            // Create the directory structure for featured images (e.g., 'files/blog/uploads/featured-images/YYYY-MM/')
            $currentYearMonth = date('Y-m');  // Example: '2025-01'
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/files/blog/uploads/featured-images/' . $currentYearMonth . '/';
            $webDir = 'https://www.dashboard.lyzerslab.com/files/blog/uploads/featured-images/' . $currentYearMonth . '/';
            
            // Create the directory if it doesn't exist
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
                $image_err = "Failed to create the upload directory.";
            }

            // Generate a safe name for the image
            $featured_image_name = basename($_FILES["featured_image"]["name"]);
            $featured_image_name = strtolower(preg_replace('/[^a-z0-9_\-\.]/', '-', $featured_image_name));  // Slugify
            $target_file = $uploadDir . $featured_image_name;

            if (move_uploaded_file($_FILES["featured_image"]["tmp_name"], $target_file)) {
                $featured_image = $webDir . $featured_image_name;
            } else {
                $image_err = "Sorry, there was an error uploading your file.";
            }
        }
    }

    // Get tags and process them
    if (!empty(trim($_POST["tags"]))) {
        $tags = trim($_POST["tags"]);
        // Split tags by commas
        $tags_array = array_map('trim', explode(',', $tags));
    } else {
        $tags_err = "Please enter at least one tag.";
    }

    // If no errors, insert the post into the database
    if (empty($title_err) && empty($content_err) && empty($category_id_err) && empty($status_err) && empty($image_err) && empty($tags_err) && empty($slug_err)) {
        $sql = "INSERT INTO blogs (title, slug, content, featured_image, category_id, author_id, created_at, updated_at, status)
                VALUES (:title, :slug, :content, :featured_image, :category_id, :author_id, NOW(), NOW(), :status)";

        if ($stmt = $connection->prepare($sql)) {
            $stmt->bindParam(":title", $title, PDO::PARAM_STR);
            $stmt->bindParam(":slug", $slug, PDO::PARAM_STR);
            $stmt->bindParam(":content", $content, PDO::PARAM_STR);
            $stmt->bindParam(":featured_image", $featured_image, PDO::PARAM_STR);
            $stmt->bindParam(":category_id", $category_id, PDO::PARAM_INT);
            $stmt->bindParam(":author_id", $_SESSION["id"], PDO::PARAM_INT);
            $stmt->bindParam(":status", $status, PDO::PARAM_STR);

            if ($stmt->execute()) {
                // After blog is added, get the blog ID
                $blog_id = $connection->lastInsertId();

                // Insert tags into the blog_tags table
                foreach ($tags_array as $tag) {
                    $tag = trim($tag); // Clean up the tag
                    if (!empty($tag)) {
                        $tag_sql = "INSERT INTO blog_tags (blog_id, tag) VALUES (:blog_id, :tag)";
                        if ($tag_stmt = $connection->prepare($tag_sql)) {
                            $tag_stmt->bindParam(":blog_id", $blog_id, PDO::PARAM_INT);
                            $tag_stmt->bindParam(":tag", $tag, PDO::PARAM_STR);
                            $tag_stmt->execute();
                        }
                    }
                }

                $success = "Blog post added successfully.";
            } else {
                $success = "Error adding post.";
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
    <title>Dashbaord</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../styling/style.css">

    <script src="../files/js/main.js"></script>
    <script src="https://cdn.tiny.cloud/1/cpa4sj1jmm21qus4f9oxpb6hqfw7mvdp7ea40b4k1trzpmuj/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        tinymce.init({
            selector: 'textarea#content',
            plugins: 'link image lists media table wordcount code', // Add 'code' to plugins
            toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image | code', // Add 'code' to toolbar
        });
    });
</script>
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
                                <li><a href="comments.php"><i class="fa-solid fa-tags"></i> Manage Comments</a></li>
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
                    <div class="app-wrapper">
                        <div class="header-body">
                            <div class="main">
                                <h1 class="page-heading">Add New Post</h1>

                                <!-- Display success or error message -->
                                <?php if (!empty($success)) : ?>
                                    <div class="alert alert-success">
                                        <?php echo $success; ?>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" action="add_post.php" enctype="multipart/form-data" onsubmit="return validateForm();">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title</label>
                                        <input type="text" class="form-control" id="title" name="title" required>
                                        <small class="text-danger"><?php echo $title_err; ?></small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="slug" class="form-label">Slug</label>
                                        <input type="text" class="form-control" id="slug" name="slug" placeholder="Auto-generated if left blank">
                                        <small class="text-muted">The slug will be auto-generated based on the title if left blank.</small>
                                        <small class="text-danger"><?php echo $slug_err; ?></small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="content" class="form-label">Content</label>
                                        <textarea name="content" rows="10" id="content"></textarea>
                                        <small class="text-danger"><?php echo $content_err; ?></small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Category</label>
                                        <select class="form-control" id="category_id" name="category_id" required>
                                            <option value="">Select a category</option>
                                            <?php
                                            // Fetch categories dynamically from the database
                                            $sql = "SELECT id, name FROM categories";
                                            $result = $connection->query($sql);
                                            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                                echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</option>';
                                            }
                                            ?>
                                        </select>
                                        <small class="text-danger"><?php echo $category_id_err; ?></small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-control" id="status" name="status" required>
                                            <option value="draft">Draft</option>
                                            <option value="published">Published</option>
                                        </select>
                                        <small class="text-danger"><?php echo $status_err; ?></small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="tags" class="form-label">Tags</label>
                                        <input type="text" class="form-control" id="tags" name="tags" placeholder="Enter tags, separated by commas">
                                        <small class="text-danger"><?php echo $tags_err; ?></small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="featured_image" class="form-label">Featured Image</label>
                                        <input type="file" class="form-control" id="featured_image" name="featured_image">
                                        <small class="text-danger"><?php echo $image_err; ?></small>
                                    </div>

                                    <button type="submit" class="btn btn-primary">Add Post</button>
                                </form>
                            </div>
                        </div>
                    </div>

                        <!-- Footer -->
                        <footer class="footer mt-5">
                            <p class="mb-0">
                                Copyright © <span>2024</span> Lyzerslab. All Rights Reserved.
                            </p>
                        </footer>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function validateForm() {
            // Trigger TinyMCE to save its content into the textarea
            tinymce.triggerSave();

            // Get the TinyMCE content from the hidden textarea
            const content = document.getElementById("content").value.trim();

            // Check if the content is empty
            if (!content) {
                alert("Content cannot be empty.");
                return false;
            }

            return true;
        }
</script>
</body>
</html>