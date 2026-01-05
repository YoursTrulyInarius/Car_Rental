<?php 
require_once 'config.php'; 

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if(empty($email) || empty($password)){
        $error = "Please enter both email and password.";
    } else {
        $sql = "SELECT id, name, password, role FROM users WHERE email = ?";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("s", $email);
            if($stmt->execute()){
                $stmt->store_result();
                if($stmt->num_rows == 1){
                    $stmt->bind_result($id, $name, $hashed_password, $role);
                    if($stmt->fetch()){
                        if(password_verify($password, $hashed_password)){
                            // Password is correct, start session
                            $_SESSION['user_id'] = $id;
                            $_SESSION['username'] = $name;
                            $_SESSION['role'] = $role;

                            if($role == 'admin'){
                                header("location: admin/dashboard.php");
                            } else {
                                header("location: dashboard.php");
                            }
                            exit;
                        } else {
                            $error = "Invalid email or password.";
                        }
                    }
                } else {
                    $error = "Invalid email or password.";
                }
            } else {
                $error = "Something went wrong. Please try again.";
            }
            $stmt->close();
        }
    }
}
require_once 'includes/header.php';
// Navbar removed as per request
?>

<style>
    body {
        overflow: hidden; /* Force no scroll for auth pages */
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
        color: rgba(108, 117, 125, 0.8); /* Darker text for visibility on white, user can adjust */
        border: none;
        z-index: 10;
        pointer-events: none;
    }
    /* Ensure footer text visible on blue side if needed, but centering typically handles it. 
       Let's keep it consistent. */
</style>
<div class="container-fluid auth-container p-0">
    <div class="row g-0 w-100 h-100">
        <!-- Left Side: Animated Branding -->
        <div class="col-lg-7 d-none d-lg-flex flex-column align-items-center justify-content-center auth-left-panel text-white text-center">
            <!-- Animated Background Shapes -->
            <div class="circle-shape shape-1"></div>
            <div class="circle-shape shape-2"></div>
            <div class="circle-shape shape-3"></div>
            
            <div class="position-relative z-1 p-5">
                <div class="mb-4 animate-fade-up">
                    <i class="bi bi-car-front-fill display-1"></i>
                </div>
                <h1 class="display-1 fw-bold mb-0 animate-fade-up delay-100">VEGA'S</h1>
                <h2 class="display-4 fw-light animate-fade-up delay-200">CAR RENTAL</h2>
                <div class="mt-4 border-top border-white border-opacity-25 pt-4 animate-fade-up delay-300" style="max-width: 400px; margin: 0 auto;">
                    <p class="lead fs-5 text-white-50">Experience the future of mobility.</p>
                </div>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="col-lg-5 d-flex align-items-center justify-content-center bg-white">
            <div class="auth-card border-0 shadow-none w-75 p-lg-5 p-3"> <!-- Added responsive padding -->
                <div class="mb-4 text-center"> <!-- Center text and reduced margin -->
                    <h2 class="fw-bold display-6 mb-1">Welcome Back</h2>
                    <p class="text-muted small">Sign in to continue.</p>
                </div>
                
                <?php if(!empty($error)): ?>
                    <div class="alert alert-danger rounded-3 border-0 bg-danger-subtle text-danger mb-3 py-2 small"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-floating mb-3">
                        <input type="email" name="email" class="form-control" id="floatingInput" placeholder="name@example.com" required>
                        <label for="floatingInput">Email Address</label>
                    </div>
                    
                    <div class="password-wrapper mb-3">
                        <div class="form-floating">
                            <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" required>
                            <label for="floatingPassword">Password</label>
                        </div>
                        <i class="bi bi-eye eye-toggle" id="toggleLogin" onclick="togglePassword('floatingPassword', 'toggleLogin')"></i>
                    </div>

                    <div class="d-flex justify-content-between mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rememberMe">
                            <label class="form-check-label text-muted small" for="rememberMe">Remember me</label>
                        </div>
                        <a href="#" class="text-decoration-none small fw-bold">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-3 hover-float">Sign In</button> <!-- Added hover-float class -->
                    
                    <div class="text-center">
                        <p class="text-muted mb-0 small">Don't have an account? <a href="register.php" class="text-decoration-none fw-bold">Create Account</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
