<?php

session_start();

if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit;
}

require_once "../../config/database.php";


/*
|--------------------------------------------------------------------------
| CATEGORY ID
|--------------------------------------------------------------------------
*/

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| CHECK CATEGORY
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT id, name, image
    FROM categories
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ":id" => $id
]);

$category = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$category) {

    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| CHECK PRODUCTS
|--------------------------------------------------------------------------
*/

$productStmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM products
    WHERE category_id = :category_id
");

$productStmt->execute([
    ":category_id" => $id
]);

$productCount = (int)$productStmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| CATEGORY HAS PRODUCTS
|--------------------------------------------------------------------------
*/

if ($productCount > 0) {

    header(
        "Location: index.php?error=category_has_products&count=" .
        $productCount
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| DELETE CATEGORY
|--------------------------------------------------------------------------
*/

try {

    /*
    |--------------------------------------------------------------------------
    | DELETE CATEGORY IMAGE
    |--------------------------------------------------------------------------
    */

    if (!empty($category["image"])) {

        $image = ltrim(
            trim($category["image"]),
            '/'
        );

        $projectRoot = dirname(__DIR__, 2);

        $possiblePaths = [

            $projectRoot .
            DIRECTORY_SEPARATOR .
            $image,

            $projectRoot .
            DIRECTORY_SEPARATOR .
            "assets" .
            DIRECTORY_SEPARATOR .
            "img" .
            DIRECTORY_SEPARATOR .
            "categories" .
            DIRECTORY_SEPARATOR .
            basename($image)
        ];


        foreach ($possiblePaths as $imagePath) {

            if (is_file($imagePath)) {

                unlink($imagePath);

                break;
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE CATEGORY
    |--------------------------------------------------------------------------
    */

    $deleteStmt = $conn->prepare("
        DELETE FROM categories
        WHERE id = :id
    ");

    $deleteStmt->execute([
        ":id" => $id
    ]);


    /*
    |--------------------------------------------------------------------------
    | REDIRECT
    |--------------------------------------------------------------------------
    */

    header("Location: index.php?deleted=1");
    exit;


} catch (PDOException $e) {

    header("Location: index.php?error=delete");
    exit;
}