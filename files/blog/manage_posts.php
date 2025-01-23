<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../../index.php");
    exit;
}

// Include config file
require_once "../auth/db-connection/config.php";

// Fetch blog posts
$sql = "SELECT b.id, b.title, b.slug, b.status, b.created_at, c.name AS category 
        FROM blogs b
        LEFT JOIN categories c ON b.category_id = c.id
        ORDER BY b.created_at DESC";

$stmt = $connection->prepare($sql);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Posts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../styling/style.css">
</head>
<body style="background: #f7f7f7;">
    <main>
        <div class="app-wrapper">
            <!-- Sidebar -->
            <div class="app-sidebar">
                <div class="side-header flex pr-3">
                    <div class="logo flex">
                        <img src="images/logo.webp" alt="logo">
                    </div>
                </div>
                <div class="sidebard-nav">
                    <ul>
                        <li>
                            <a href="dashboard.php">
                                <i class="fa-solid fa-table-columns"></i>
                                <span class="block">Dashboard</span>
                            </a>
                        </li>
                        <li class="dropdown active">
                            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-blog"></i>
                                <span class="block">Blog</span>
                                <i class="fa-solid fa-chevron-down dropdown-arrow"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="manage_posts.php" class="active"><i class="fa-solid fa-list"></i> Manage Posts</a></li>
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
                </div>

                <div class="h-container">
                    <div class="main">
                        <h1 class="page-heading">Manage Posts</h1>
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
                                                    <?php echo $post['status'] == 1 ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Draft</span>'; ?>
                                                </td>
                                                <td><?php echo date('Y-m-d', strtotime($post['created_at'])); ?></td>
                                                <td>
                                                    <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary btn-sm">
                                                        <i class="fa-solid fa-edit"></i> Edit
                                                    </a>
                                                    <a href="delete_post.php?id=<?php echo $post['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this post?');">
                                                        <i class="fa-solid fa-trash"></i> Delete
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No posts found.</td>
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