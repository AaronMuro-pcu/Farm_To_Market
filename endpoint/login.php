<?php
session_start();
include('../conn/conn.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT tbl_user_id, password FROM tbl_user WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch();
        $stored_password = $row['password'];

        if ($password === $stored_password) { // Replace this with password_verify() if you hash passwords
            $_SESSION['user_id'] = $row['tbl_user_id'];
            $_SESSION['username'] = $username;

            echo "
            <script>
                alert('Login Successfully!');
                window.location.href = 'nicol_hci/home.php';
            </script>
            ";
        } else {
            echo "
            <script>
                alert('Login Failed, Incorrect Password!');
                window.location.href = 'nicol_hci/index.php';
            </script>
            ";
        }
    } else {
        echo "
            <script>
                alert('Login Failed, User Not Found!');
                window.location.href = 'nicol_hci/index.php';
            </script>
            ";
    }
}
?>
