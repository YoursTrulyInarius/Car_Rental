<?php 
require_once 'config.php'; 

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'] ?? 'user'; // Capture Role
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if(empty($name) || empty($email) || empty($password) || empty($confirm_password)){
        $error = "All fields are required.";
    } elseif($password != $confirm_password){
        $error = "Passwords do not match.";
    } elseif(strlen($password) < 6){
        $error = "Password must be at least 6 characters.";
    } else {
        // Check if email exists
        $sql = "SELECT id FROM users WHERE email = ?";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("s", $email);
            if($stmt->execute()){
                $stmt->store_result();
                if($stmt->num_rows == 1){
                    $error = "This email is already taken.";
                } else {
                    $stmt->close();
                    
                    // Sanitize role
                    $allowed_roles = ['user', 'admin'];
                    if(!in_array($role, $allowed_roles)) $role = 'user';

                    // Insert new user
                    $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
                    if($stmt = $mysqli->prepare($sql)){
                        $param_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt->bind_param("ssss", $name, $email, $param_password, $role);
                        if($stmt->execute()){
                            $success = "Registration successful! <a href='login.php'>Login here</a>.";
                        } else {
                            $error = "Something went wrong. Please try again.";
                        }
                    }
                }
            } else {
                $error = "Oops! Something went wrong.";
            }
        }
        if (isset($stmt)) $stmt->close();
    }
}
require_once 'includes/header.php';
// Navbar removed
?>

<style>
    body {
        overflow: hidden;
    }
    .auth-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 5;
        background-color: #fff;
    }
    footer {
        position: fixed;
        bottom: 0;
        width: 100%;
        background: transparent;
        color: rgba(108, 117, 125, 0.8);
        border: none;
        z-index: 10;
        pointer-events: none;
    }
</style>
<div class="container-fluid auth-container p-0">
    <div class="row g-0 w-100 h-100">
        <!-- Left Side: Animated Branding -->
        <div class="col-lg-7 d-none d-lg-flex flex-column align-items-center justify-content-center auth-left-panel text-white text-center">
            <div class="circle-shape shape-1"></div>
            <div class="circle-shape shape-2"></div>
            <div class="circle-shape shape-3"></div>
            
            <div class="position-relative z-1 p-5">
                <div class="mb-4 animate-fade-up">
                    <i class="bi bi-stars display-1"></i>
                </div>
                <h1 class="display-1 fw-bold mb-0 animate-fade-up delay-100">VEGA'S</h1>
                <h2 class="display-4 fw-light animate-fade-up delay-200">CAR RENTAL</h2>
                <div class="mt-4 border-top border-white border-opacity-25 pt-4 animate-fade-up delay-300" style="max-width: 400px; margin: 0 auto;">
                    <p class="lead fs-5 text-white-50">Join the exclusive club today.</p>
                </div>
            </div>
        </div>

        <!-- Right Side: Register Form -->
        <div class="col-lg-5 d-flex align-items-center justify-content-center bg-white">
            <div class="auth-card border-0 shadow-none w-75 p-lg-5 p-3">
                <div class="mb-4 text-center">
                    <h2 class="fw-bold display-6 mb-1">Create Account</h2>
                    <p class="text-muted small">Start your journey with us.</p>
                </div>

                <?php if(!empty($error)): ?>
                    <div class="alert alert-danger rounded-3 border-0 bg-danger-subtle text-danger mb-3 py-2 small"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if(!empty($success)): ?>
                    <div class="alert alert-success rounded-3 border-0 bg-success-subtle text-success mb-3 py-2 small"><?php echo $success; ?></div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-floating mb-3">
                        <input type="text" name="name" class="form-control" id="floatingName" placeholder="John Doe" required>
                        <label for="floatingName">Full Name</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="email" name="email" class="form-control" id="floatingEmail" placeholder="name@example.com" required>
                        <label for="floatingEmail">Email Address</label>
                    </div>

                    <div class="password-wrapper mb-3">
                        <div class="form-floating">
                            <input type="password" name="password" class="form-control" id="regPass" placeholder="Password" required>
                            <label for="regPass">Password</label>
                        </div>
                        <i class="bi bi-eye eye-toggle" id="toggleReg" onclick="togglePassword('regPass', 'toggleReg')"></i>
                    </div>

                    <div class="password-wrapper mb-3">
                        <div class="form-floating">
                            <input type="password" name="confirm_password" class="form-control" id="regConfirmPass" placeholder="Confirm Password" required>
                            <label for="regConfirmPass">Confirm Password</label>
                        </div>
                        <i class="bi bi-eye eye-toggle" id="toggleRegConfirm" onclick="togglePassword('regConfirmPass', 'toggleRegConfirm')"></i>
                    </div>

                    <div class="form-floating mb-4">
                        <select name="role" class="form-select" id="floatingRole">
                            <option value="user">User (Customer)</option>
                            <option value="admin">Admin (Manager)</option>
                        </select>
                        <label for="floatingRole">Select Role</label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-3 hover-float">Register</button>
                    
                    <div class="text-center">
                        <p class="text-muted mb-0 small">Already have an account? <a href="login.php" class="text-decoration-none fw-bold">Sign In</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
