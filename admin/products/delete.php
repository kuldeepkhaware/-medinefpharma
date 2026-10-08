<?php

session_start();

if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit;
}

require_once "../../config/database.php";


/*
|--------------------------------------------------------------------------
| PRODUCT ID
|--------------------------------------------------------------------------
*/

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| IMAGE PATH HELPER
|--------------------------------------------------------------------------
*/

function productImageFilePath($image)
{
    if (empty($image)) {
        return '';
    }

    $image = ltrim(trim($image), '/');

    $projectRoot = dirname(__DIR__, 2);

    $possiblePaths = [

        $projectRoot . DIRECTORY_SEPARATOR . $image,

        $projectRoot .
        DIRECTORY_SEPARATOR .
        'assets' .
        DIRECTORY_SEPARATOR .
        'img' .
        DIRECTORY_SEPARATOR .
        'products' .
        DIRECTORY_SEPARATOR .
        $image,

        $projectRoot .
        DIRECTORY_SEPARATOR .
        'assets' .
        DIRECTORY_SEPARATOR .
        'img' .
        DIRECTORY_SEPARATOR .
        $image
    ];


    foreach ($possiblePaths as $path) {

        if (is_file($path)) {
            return $path;
        }
    }


    return $projectRoot .
        DIRECTORY_SEPARATOR .
        'assets' .
        DIRECTORY_SEPARATOR .
        'img' .
        DIRECTORY_SEPARATOR .
        'products' .
        DIRECTORY_SEPARATOR .
        basename($image);
}


/*
|--------------------------------------------------------------------------
| GET PRODUCT
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT id, name, image
    FROM products
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ":id" => $id
]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$product) {

    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| DELETE PRODUCT
|--------------------------------------------------------------------------
*/

try {

    $conn->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | GET GALLERY IMAGES
    |--------------------------------------------------------------------------
    */

    $galleryStmt = $conn->prepare("
        SELECT id, image
        FROM product_images
        WHERE product_id = :product_id
    ");

    $galleryStmt->execute([
        ":product_id" => $id
    ]);

    $galleryImages =
        $galleryStmt->fetchAll(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | DELETE MAIN IMAGE
    |--------------------------------------------------------------------------
    */

    if (!empty($product["image"])) {

        $mainImagePath =
            productImageFilePath(
                $product["image"]
            );

        if (
            $mainImagePath !== "" &&
            is_file($mainImagePath)
        ) {

            unlink($mainImagePath);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE GALLERY IMAGE FILES
    |--------------------------------------------------------------------------
    */

    foreach ($galleryImages as $galleryImage) {

        if (!empty($galleryImage["image"])) {

            $galleryImagePath =
                productImageFilePath(
                    $galleryImage["image"]
                );

            if (
                $galleryImagePath !== "" &&
                is_file($galleryImagePath)
            ) {

                unlink($galleryImagePath);
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE GALLERY DATABASE RECORDS
    |--------------------------------------------------------------------------
    */

    $deleteGallery = $conn->prepare("
        DELETE FROM product_images
        WHERE product_id = :product_id
    ");

    $deleteGallery->execute([
        ":product_id" => $id
    ]);


    /*
    |--------------------------------------------------------------------------
    | DELETE PRODUCT
    |--------------------------------------------------------------------------
    */

    $deleteProduct = $conn->prepare("
        DELETE FROM products
        WHERE id = :id
    ");

    $deleteProduct->execute([
        ":id" => $id
    ]);


    /*
    |--------------------------------------------------------------------------
    | COMMIT
    |--------------------------------------------------------------------------
    */

    $conn->commit();


    /*
    |--------------------------------------------------------------------------
    | REDIRECT
    |--------------------------------------------------------------------------
    */

    header("Location: index.php?deleted=1");
    exit;


} catch (Exception $e) {

    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    header("Location: index.php?error=delete");
    exit;
}