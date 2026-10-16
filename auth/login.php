<?php
session_start();
require_once '../conn/conn.php';
require_once '../admin/includes/tracking.php';

// ---------- Settings ----------
const APP_ORIGIN    = 'http://localhost'; // used to build the verification link
const DEV_SHOW_LINK = true;               // if mail() fails, show the link on screen. Set to false in production!

// Where each role goes after login
$redirects = [
    'admin' => '../admin/admin.php',
    'user'  => '../buyer/buyer.php',   // adjust to your real user dashboard path
];

// Already logged in? Send to the right dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: " . ($redirects[$_SESSION['user_role']] ?? 'login.php'));
    exit();
}

// ---------- Helpers ----------
// Creates a fresh 24-hour token (only its hash is stored) and returns the link to email.
function verification_link(PDO $conn, int $userId): string {
    $token = bin2hex(random_bytes(32));
    $conn->prepare("UPDATE users SET verify_token = ?, verify_expires = DATE_ADD(NOW(), INTERVAL 24 HOUR) WHERE id = ?")
         ->execute([hash('sha256', $token), $userId]);
    return APP_ORIGIN . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/verify.php?token=' . $token;
}

function send_verification(string $to, string $name, string $link): bool {
    $body = "Hi $name,\n\nConfirm your email to activate your FarmLinked account:\n$link\n\nThis link expires in 24 hours.\n";
    $headers = "From: FarmLinked <no-reply@farmlinked.local>\r\nContent-Type: text/plain; charset=UTF-8";
    return @mail($to, 'Verify your FarmLinked account', $body, $headers);
}

$activeForm      = 'login-form';
$loginError      = null;
$registerError   = null;
$registerSuccess = null;
$resendEmail     = null;   // set when a correct login belongs to an unverified account
$devLink         = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ---------- LOGIN ----------
    if (isset($_POST['login'])) {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $pass  = $_POST['password'] ?? '';

        $stmt = $conn->prepare("SELECT id, name, password, role, status FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($pass, $user['password'])) {
            $loginError = 'Incorrect email or password.';
        } elseif ($user['status'] === 'unverified') {
            $loginError  = 'Please verify your email first. Check your inbox for the link.';
            $resendEmail = $email;
        } elseif ($user['status'] === 'suspended') {
            $loginError = 'Your account has been suspended. Please contact the admin.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];   // 'user' or 'admin'
            track_login((int)$user['id']);
            header("Location: " . ($redirects[$user['role']] ?? 'login.php'));
            exit();
        }
    }

    // ---------- RESEND VERIFICATION ----------
    if (isset($_POST['resend'])) {
        $email = strtolower(trim($_POST['email'] ?? ''));
        // Only if still unverified and the last link was sent more than ~10 minutes ago
        $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ? AND status = 'unverified'
                                AND (verify_expires IS NULL OR verify_expires < DATE_ADD(NOW(), INTERVAL 1430 MINUTE))");
        $stmt->execute([$email]);
        if ($u = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $link = verification_link($conn, (int)$u['id']);
            if (!send_verification($email, $u['name'], $link) && DEV_SHOW_LINK) $devLink = $link;
        }
        // Same message either way so nobody can probe which emails exist
        $registerSuccess = 'If that account needs verification, a new link has been sent.';
    }

    // ---------- REGISTER ----------
    if (isset($_POST['register'])) {
        $activeForm = 'register-form';
        $name  = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $pass  = $_POST['password'] ?? '';

        if ($name === '' || mb_strlen($name) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $registerError = 'Please enter a valid name and email.';
        } elseif (strlen($pass) < 8) {
            $registerError = 'Password must be at least 8 characters.';
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->fetchColumn()) {
                $registerError = 'That email is already registered.';
            } else {
                try {
                    $hash = password_hash($pass, PASSWORD_DEFAULT);
                    $conn->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'user', 'unverified')")
                         ->execute([$name, $email, $hash]);
                    $link = verification_link($conn, (int)$conn->lastInsertId());

                    if (!send_verification($email, $name, $link) && DEV_SHOW_LINK) $devLink = $link;
                    $registerSuccess = 'Account created! We sent a verification link to your email. Verify it, then log in.';
                    $activeForm = 'login-form';
                } catch (PDOException $e) {
                    $registerError = 'That email is already registered.';   // unique-key race
                }
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
    <title>FarmLinked | Login and Register</title>
    <link rel="icon" href="../assets/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@500;700;800&family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/login.css?v=2">
</head>
<body>

    <div class="container">

        <!-- LEFT: welcome side -->
        <div class="brand">
            <a href="../Landing/landing.php" class="brand-logo" aria-label="FarmLinked home">
                <img src="../assets/images/logo.png" alt="" class="logo-img">
                <span>FarmLinked</span>
            </a>
            <a href="../Landing/landing.php" class="back-link">&larr; Back to Homepage</a>

            <h1>Welcome to the Farm-to-Market Linkage</h1>
            <p>Login to continue, or create a new account</p>
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
                    <?php if ($resendEmail): ?>
                        <form action="login.php" method="post" style="margin-bottom:12px">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($resendEmail); ?>">
                            <button type="submit" name="resend" class="btn-primary">Resend verification email</button>
                        </form>
                    <?php endif; ?>
                    <?php if ($registerSuccess): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($registerSuccess); ?></div>
                    <?php endif; ?>
                    <?php if ($devLink): ?>
                        <div class="alert alert-success">
                            Dev mode (email could not be sent): <a href="<?php echo htmlspecialchars($devLink); ?>">click here to verify</a>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="post">
                        <div class="field">
                            <label for="login-email">Email</label>
                            <input type="email" id="login-email" name="email" placeholder="name@example.com" required>
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

                    <form action="login.php" method="post">
                        <div class="field">
                            <label for="reg-name">Name</label>
                            <input type="text" id="reg-name" name="name" placeholder="Edwardo" required maxlength="100">
                        </div>

                        <div class="field">
                            <label for="reg-email">Email</label>
                            <input type="email" id="reg-email" name="email" placeholder="name@example.com" required>
                        </div>

                        <div class="field">
                            <label for="reg-password">Password</label>
                            <input type="password" id="reg-password" name="password" placeholder="At least 8 characters" required minlength="8">
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

    <script src="../assets/js/signin.js"></script>
</body>
</html>