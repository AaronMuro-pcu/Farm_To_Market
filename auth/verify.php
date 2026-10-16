<?php
// Opened from the link in the verification email: verify.php?token=...
require_once '../conn/conn.php';

$token   = (string)($_GET['token'] ?? '');
$success = false;

if (preg_match('/^[a-f0-9]{64}$/', $token)) {
    $stmt = $conn->prepare("SELECT id FROM users
                            WHERE verify_token = ? AND verify_expires >= NOW() AND status = 'unverified'");
    $stmt->execute([hash('sha256', $token)]);
    $id = $stmt->fetchColumn();

    if ($id) {
        $conn->prepare("UPDATE users SET status = 'active', email_verified_at = NOW(),
                        verify_token = NULL, verify_expires = NULL WHERE id = ?")->execute([$id]);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FarmLinked | Verify Email</title>
    <link rel="icon" href="../assets/images/logo.png">
    <style>
        body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f4f7f2;font-family:'Nunito Sans',system-ui,sans-serif;color:#1f2d1f}
        .box{background:#fff;padding:2rem 2.25rem;border-radius:14px;box-shadow:0 8px 30px rgba(0,0,0,.08);max-width:420px;text-align:center}
        h1{margin:0 0 .5rem;font-size:1.4rem}
        p{margin:0 0 1.25rem;color:#4a5a4a}
        a.btn{display:inline-block;padding:.7rem 1.4rem;border-radius:10px;background:#2f7d32;color:#fff;text-decoration:none;font-weight:700}
    </style>
</head>
<body>
    <div class="box">
        <?php if ($success): ?>
            <h1>Email verified</h1>
            <p>Your account is active. You can log in now.</p>
        <?php else: ?>
            <h1>Link invalid or expired</h1>
            <p>This verification link is not valid anymore. Log in to request a new one.</p>
        <?php endif; ?>
        <a class="btn" href="login.php">Go to login</a>
    </div>
</body>
</html>