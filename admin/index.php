


<?php

session_start();

require_once "../config/database.php";

if (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {

        $error = "Please enter email and password.";

    } else {

        $stmt = $conn->prepare("
            SELECT id, name, email, password, status
            FROM admins
            WHERE email = :email
            LIMIT 1
        ");

        $stmt->execute([
            ":email" => $email
        ]);

        $admin = $stmt->fetch();

        if ($admin && (int)$admin["status"] === 1) {

            if (password_verify($password, $admin["password"])) {

                session_regenerate_id(true);

                $_SESSION["admin_id"] = (int)$admin["id"];
                $_SESSION["admin_name"] = $admin["name"];
                $_SESSION["admin_email"] = $admin["email"];

                header("Location: index.php");
                exit;

            } else {

                $error = "Invalid email or password.";

            }

        } else {

            $error = "Invalid email or password.";

        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login - Medinef Pharma</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            background: #f5f4fb;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-wrapper {
            width: 420px;
            max-width: 92%;
        }

        .login-card {
            background: #ffffff;
            padding: 40px;
            border-radius: 18px;
            box-shadow: 0 15px 45px rgba(75, 64, 153, 0.12);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #4B4099;
            font-size: 28px;
            margin-bottom: 8px;
        }

        .logo p {
            color: #777;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        input {
            width: 100%;
            height: 48px;
            border: 1px solid #e0dfeb;
            border-radius: 8px;
            padding: 0 14px;
            font-size: 14px;
            outline: none;
        }

        input:focus {
            border-color: #4B4099;
        }

        .login-btn {
            width: 100%;
            height: 50px;
            border: none;
            border-radius: 8px;
            background: #4B4099;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }

        .login-btn:hover {
            opacity: .92;
        }

        .error {
            background: #fff0f0;
            color: #d33;
            border: 1px solid #ffd4d4;
            padding: 11px 14px;
            border-radius: 7px;
            margin-bottom: 20px;
            font-size: 14px;
        }

    </style>

</head>

<body>

<div class="login-wrapper">

    <div class="login-card">

        <div class="logo">

            <a href="index.php">
    <img src="../assets/img/logo/logo.png" alt="Medinef Pharma" style="height:50px; width:auto;">
</a>

            <p>Administrator Login</p>

        </div>

        <?php if ($error !== ""): ?>

            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="form-group">

                <label>Email Address</label>

                <input
                    type="email"
                    name="email"
                    placeholder="admin@medinefpharma.online"
                    required
                >

            </div>

            <div class="form-group">

                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Enter password"
                    required
                >

            </div>

            <button type="submit" class="login-btn">
                Login to Dashboard
            </button>

        </form>

    </div>

</div>

</body>

</html>