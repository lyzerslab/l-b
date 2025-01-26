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


// Fetch posts and tags from the database
$sql = "SELECT 
    b.id, 
    b.title, 
    b.slug, 
    b.content, 
    b.status, 
    b.created_at, 
    c.name AS category, 
    u.username AS author, 
    GROUP_CONCAT(bt.tag) AS tags
FROM blogs b
LEFT JOIN categories c ON b.category_id = c.id
LEFT JOIN blog_tags bt ON b.id = bt.blog_id
LEFT JOIN admin_users u ON b.author_id = u.id
GROUP BY b.id
ORDER BY b.created_at DESC;";

$stmt = $connection->prepare($sql);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                        <h1 class="page-heading">Manage Posts</h1>

                        <?php if (isset($_GET['message'])): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($_GET['message']); ?>
                            </div>
                        <?php elseif (isset($_GET['error'])): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($_GET['error']); ?>
                            </div>
                        <?php endif; ?>
                        <!-- Add New Post Button -->
                        <div class="mb-3 text-end">
                            <a href="add_post.php" class="btn btn-success">
                                <i class="fa-solid fa-plus"></i> Add New Post
                            </a>
                        </div>

                        <!-- Blog Posts Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Slug</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Content</th>
                                        <th>Tags</th>
                                        <th>Author</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($posts)) : ?>
                                        <?php foreach ($posts as $post) : ?>
                                            <tr>
                                                <td><?php echo $post['id']; ?></td>
                                                <td><?php echo htmlspecialchars($post['title']); ?></td>
                                                <td><?php echo htmlspecialchars($post['slug']); ?></td>
                                                <td><?php echo htmlspecialchars($post['category'] ?? 'Uncategorized'); ?></td>
                                                <td>
                                                    <?php echo $post['status'] == 'published' ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Draft</span>'; ?>
                                                </td>
                                                <td><?php echo date('Y-m-d', strtotime($post['created_at'])); ?></td>
                                               
                                                <td>
                                                    <!-- Display a snippet of the content (first 100 characters) -->
                                                    <?php 
                                                    $content_snippet = substr(strip_tags($post['content']), 0, 100);
                                                    echo $content_snippet . (strlen($post['content']) > 100 ? '...' : ''); 
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($post['tags']); ?></td>
                                                <td><?php echo htmlspecialchars($post['author']); ?></td>
                                                <td>
                                                    <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary btn-sm">
                                                        <i class="fa-solid fa-edit"></i> Edit
                                                    </a>
                                                    <a href="../auth/backend-assets/delete_post.php?id=<?php echo $post['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this post?');">
                                                        <i class="fa-solid fa-trash"></i> Delete
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No posts found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
</body>
</html>