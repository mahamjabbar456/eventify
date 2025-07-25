<?php 
session_start();
include './includes/header.php';
include 'connect.php';

if(isset($_SESSION['userId'])) {
    header("Location: index.php");
    exit();
}

if(isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($con, trim($_POST['email']));
    $password = trim($_POST['password']);
    
    // Validate inputs
    $errors = [];
    
    if(empty($email)) {
        echo '<script>alert("Email is required");</script>';
    } 
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<script>alert("Invalid email format");</script>';
    }
    
    if(empty($password)) {
        echo '<script>alert("Password is required");</script>';
    }
    
    // Only proceed if no errors
    if(empty($errors)) {
        $sql = "SELECT u.userId, u.username, u.image, u.email, u.password, r.roleName 
                FROM user u
                JOIN role r ON u.roleId = r.roleId
                WHERE u.email='$email'";
        $result = mysqli_query($con, $sql);
        
        if(mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // Verify password (assuming passwords are hashed in database)
            if(password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['userId'] = $user['userId'];
                $_SESSION['userName'] = $user['username'];
                $_SESSION['userImage'] = $user['image'];
                $_SESSION['userEmail'] = $user['email'];
                $_SESSION['roleName'] = $user['roleName'];
                
                echo '<script>alert("Login successful! Welcome back, '.$user['username'].', '.$user['roleName'].'");';
                echo 'window.location.href = "index.php";</script>';
                exit();
            } else {
                echo '<script>alert("Invalid email or password");</script>';
            }
        } else {
            echo '<script>alert("Invalid email or password");</script>';
        }
    }
}
?>

<div class="loginForm">
    <h2 class="logo">Welcome to Eventify <span class="icon">
                        <ion-icon name="logo-deviantart"></ion-icon>
                    </span></h2>
    <div class="form-box login">
        <h2>Login</h2>
        <form method="POST">
            <div class="input-box">
                <input id="email" name="email" type="email" required>
                <label for="email">Email</label>
                 <!-- <ion-icon name="person-circle"></ion-icon> -->
                <ion-icon name="mail"></ion-icon>
            </div>
            <div class="input-box">
                <input id="password" name="password" type="password" required>
                <label for="password">Password</label>
                 <ion-icon name="eye" class="togglePassword"></ion-icon>
            </div>
            <div class="regi-link">
                <a href="forgetPassword.php" class="signuplink">Forget Password?</a>
            </div>
            <button type="submit" name="submit" class="btn1">Login</button>
        </form>
    </div>
</div>
<!-- mainContainer -->
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
        const togglePassword = document.querySelector('.togglePassword');
        const passwordInput = document.getElementById('password');
        
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle eye icon
                const iconName = this.getAttribute('name') === 'eye' ? 'eye-off' : 'eye';
                this.setAttribute('name', iconName);
            });
        }
    });
</script>
<?php include './includes/footer.php'; ?>