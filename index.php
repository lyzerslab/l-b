<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styling/style.css">
</head>
<body>
    <div class="wrapper">
        <div class="card card-w">
            <h2>Admin Login</h2>
            <p>Welcome back comrade!</p>

            <?php 
            // Check for error parameter in the URL
            if (isset($_GET["error"])) {
                $errorMessage = '';

                switch ($_GET["error"]) {
                    case '1':
                        $errorMessage = "Invalid username or password. Please try again.";
                        break;
                    case 'blocked':
                    $errorMessage = "You're blocked. Please contact the administrator.";
                    break;
                    case 'account_locked':
                        $errorMessage = "Your account is temporarily locked. Please try again later.";
                        break;
                    default:
                        $errorMessage = "An error occurred. Please try again.";
                        break;
                }

                echo "<div id='error' class='alert alert-danger'>$errorMessage</div>";
            }
            ?>

            <form action="auth/backend-assets/login.php" method="post">
                <div class="form-group">
                    <label>Username or Email</label>
                    <input type="text" name="input" class="form-control <?php echo (!empty($input_err)) ? 'is-invalid' : ''; ?>" required>
                    <span class="invalid-feedback"><?php echo $input_err; ?></span>
                </div>    
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" required>
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Login">
                </div>
                <input type="hidden" name="timezone" id="timezone" value="">
                <p>Don't have an account? <a href="./files/register.php">Sign up now</a>.</p>
            </form>

        </div>
    </div>

    <script src="files/js/main.js"></script>
</body>
</html>
