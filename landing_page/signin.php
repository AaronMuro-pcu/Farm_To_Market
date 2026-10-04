<?php
session_start();

// If already logged in, skip straight to the right dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['user_role'] === 'admin' ? 'admin_page.php' : 'user_page.php'));
    exit();
}

$activeForm      = $_SESSION['active_form']      ?? 'login-form';
$loginError      = $_SESSION['login_error']      ?? null;
$registerError   = $_SESSION['register_error']   ?? null;
$registerSuccess = $_SESSION['register_success'] ?? null;

// Clear one-time messages
unset($_SESSION['active_form'], $_SESSION['login_error'], $_SESSION['register_error'], $_SESSION['register_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login and Register</title>
    <link rel="stylesheet" href="designs/signin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

    <div class="container">
    
        <!-- LEFT: welcome side -->
        <div class="brand">
            <div class="brand-logo">LOGO</div>
            <a href="landing.php" class="back-link">&larr; Back to homepage</a>

            <h1>Welcome to the Farm-to-Market Linkage</h1>
            <p>Sign in to continue, or create a new account</p>
        </div>

        <!-- RIGHT: form card -->
        <div class="card">
            <div class="tabs">
                <button type="button" class="tab-btn <?php echo $activeForm === 'login-form' ? 'active' : ''; ?>" data-target="login-form">Login</button>
                <button type="button" class="tab-btn <?php echo $activeForm === 'register-form' ? 'active' : ''; ?>" data-target="register-form">Register</button>
            </div>

            <div class="form-panel">

                <!-- Login Form -->
                <div class="form-box <?php echo $activeForm === 'login-form' ? 'active' : ''; ?>" id="login-form">
                    <h2>Sign In</h2>
                    <p class="form-sub">Enter your credentials to continue</p>

                    <?php if ($loginError): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($loginError); ?></div>
                    <?php endif; ?>
                    <?php if ($registerSuccess): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($registerSuccess); ?></div>
                    <?php endif; ?>

                    <form action="login.php" method="post">
                        <div class="field">
                            <label for="login-email">Email</label>
                            <input type="email" id="login-email" name="email" placeholder="you@example.com" required>
                        </div>

                        <div class="field">
                            <label for="login-password">Password</label>
                            <input type="password" id="login-password" name="password" placeholder="••••••••" required>
                        </div>

                        <button type="submit" name="login" class="btn-primary">Log In</button>

                        <p class="switch-line">
                            Don't have an account?
                            <a href="#" class="switch-link" data-target="register-form">Register</a>
                        </p>
                    </form>
                </div>

                <!-- Register Form -->
                <div class="form-box <?php echo $activeForm === 'register-form' ? 'active' : ''; ?>" id="register-form">
                    <h2>Create Account</h2>
                    <p class="form-sub">Fill in your details to get started</p>

                    <?php if ($registerError): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($registerError); ?></div>
                    <?php endif; ?>

                    <form action="register.php" method="post">
                        <div class="field">
                            <label for="reg-name">Name</label>
                            <input type="text" id="reg-name" name="name" placeholder="Jane Doe" required>
                        </div>

                        <div class="field">
                            <label for="reg-email">Email</label>
                            <input type="email" id="reg-email" name="email" placeholder="you@example.com" required>
                        </div>

                        <div class="field">
                            <label for="reg-password">Password</label>
                            <input type="password" id="reg-password" name="password" placeholder="At least 8 characters" required minlength="8">
                        </div>

                        <div class="field">
                            <label for="reg-role">Role</label>
                            <select id="reg-role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select>
                        </div>

                        <button type="submit" name="register" class="btn-primary">Create Account</button>

                        <p class="switch-line">
                            Already have an account?
                            <a href="#" class="switch-link" data-target="login-form">Login</a>
                        </p>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script src="/scripts/signin.js"></script>
</body>
</html>