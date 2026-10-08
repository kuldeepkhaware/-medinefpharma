<?php

require_once "../config/database.php";

$name = "Medinef Admin";
$email = "admin@medinefpharma.online";
$password = password_hash("123456", PASSWORD_DEFAULT);

$stmt = $conn->prepare("
    INSERT INTO admins (name, email, password, status)
    VALUES (:name, :email, :password, 1)
");

$stmt->execute([
    ":name" => $name,
    ":email" => $email,
    ":password" => $password
]);

echo "Admin created successfully.";