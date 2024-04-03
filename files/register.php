<?php
// Include config file
require_once "../auth/db-connection/config.php";

// Define variables and initialize with empty values
$username = $password = $confirm_password = $email = "";
$username_err = $password_err = $confirm_password_err = $email_err = $photo_err = "";
$upload_dir = "super-admin/"; 

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))) {
        $username_err = "Username can only contain letters, numbers, and underscores.";
    } else {
            // Prepare a select statement
            $sql = "SELECT id FROM admin_users WHERE username = :username";
                
            if ($stmt = $connection->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);

                // Set parameters
                $param_username = trim($_POST["username"]);
                
                // Attempt to execute the prepared statement
                if ($stmt->execute()) {
                    // Store result
                    if ($stmt->rowCount() == 1) {
                        $username_err = "This username is already taken.";
                    } else {
                        $username = trim($_POST["username"]);
                    }
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }

                // Close statement
                unset($stmt);
            }

    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";     
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";     
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Invalid email format.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Process photo upload
    $photo = $_FILES["photo"]["name"];
    $temp = $_FILES["photo"]["tmp_name"];
    
    // Check if a photo is uploaded
    if (!empty($photo)) {
        // Create user-specific directory inside the upload directory
        $user_directory = $upload_dir . $username . "/";
        
        // Create the user-specific directory if it doesn't exist
        if (!file_exists($user_directory)) {
            if (!mkdir($user_directory, 0755, true)) {
                die('Failed to create user directory...');
            }
        }

        // Set the target path within the user-specific directory
        $target = $user_directory . $photo;

        // Move the uploaded file
        if (move_uploaded_file($temp, $target)) {
            // File uploaded successfully
        } else {
            $photo_err = "Error uploading photo.";
        }
    } else {
        $photo_err = "Please select a photo.";
    }

    // Check input errors before inserting into the database
    if (empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($email_err) && empty($photo_err)) {

// Prepare an insert statement
$sql = "INSERT INTO admin_users (username, password, email, profile_photo) VALUES (?, ?, ?, ?)";

if ($stmt = $connection->prepare($sql)) {
    // Bind parameters
    $stmt->bindParam(1, $param_username, PDO::PARAM_STR);
    $stmt->bindParam(2, $param_password, PDO::PARAM_STR);
    $stmt->bindParam(3, $param_email, PDO::PARAM_STR);
    $stmt->bindParam(4, $param_photo, PDO::PARAM_STR);

    // Set parameters
    $param_username = $username;
    $param_password = password_hash($password, PASSWORD_DEFAULT);
    $param_email = $email;
    $param_photo = $target;

    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        // Redirect to login page
        header("location: ../index.php");
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }

    // Close statement
    unset($stmt);
}
    }
    // Close connection
    mysqli_close($connection);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../styling/style.css">
</head>
<body>
    <div class="wrapper">
        <div class="card card-w">
            <h2>Sign Up</h2>
            <p>Please fill this form to create an account.</p>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                    <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="text" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                    <span class="invalid-feedback"><?php echo $email_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Photo</label>
                    <input type="file" name="photo" class="form-control <?php echo (!empty($photo_err)) ? 'is-invalid' : ''; ?>">
                    <span class="invalid-feedback"><?php echo $photo_err; ?></span>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Submit">
                    <input type="reset" class="btn btn-secondary ml-2" value="Reset">
                </div>
                <p>Already have an account? <a href="../index.php">Login here</a>.</p>
            </form>
        </div>
    </div>
</body>
</html>
