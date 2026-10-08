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
| HELPERS
|--------------------------------------------------------------------------
*/

/**
 * Create slug
 */
function slugify($text)
{
    $text = trim($text);
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');

    return $text;
}


/**
 * Get product image URL
 *
 * Supports:
 *
 * 1. product-2.jpg
 * 2. uploads/products/product-2.jpg
 * 3. assets/img/products/product-2.jpg
 * 4. Full http/https URL
 */
function productImageUrl($image)
{
    if (empty($image)) {
        return '';
    }

    $image = trim($image);

    /*
    |--------------------------------------------------------------------------
    | Already full URL
    |--------------------------------------------------------------------------
    */

    if (preg_match('/^https?:\/\//i', $image)) {
        return $image;
    }


    /*
    |--------------------------------------------------------------------------
    | Remove leading slash
    |--------------------------------------------------------------------------
    */

    $image = ltrim($image, '/');


    /*
    |--------------------------------------------------------------------------
    | Project URL
    |--------------------------------------------------------------------------
    */

    $baseUrl = '/medinefpharma.online/';


    /*
    |--------------------------------------------------------------------------
    | If database contains uploads/products/...
    |--------------------------------------------------------------------------
    */

    if (strpos($image, 'uploads/') === 0) {

        return $baseUrl . $image;
    }


    /*
    |--------------------------------------------------------------------------
    | If database contains assets/img/products/...
    |--------------------------------------------------------------------------
    */

    if (strpos($image, 'assets/img/products/') === 0) {

        return $baseUrl . $image;
    }


    /*
    |--------------------------------------------------------------------------
    | Old/simple filename
    |--------------------------------------------------------------------------
    */

    return $baseUrl . 'assets/img/products/' . $image;
}


/**
 * Get physical file path for image
 *
 * Used when deleting old images.
 */
function productImageFilePath($image)
{
    if (empty($image)) {
        return '';
    }

    $image = ltrim(trim($image), '/');

    /*
    |--------------------------------------------------------------------------
    | Project root
    |--------------------------------------------------------------------------
    */

    $projectRoot = dirname(__DIR__, 2);


    /*
    |--------------------------------------------------------------------------
    | Full relative path from database
    |--------------------------------------------------------------------------
    */

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
        $image,

    ];


    /*
    |--------------------------------------------------------------------------
    | Simple filename
    |--------------------------------------------------------------------------
    */

    if (
        strpos($image, '/') === false &&
        strpos($image, '\\') === false
    ) {

        $possiblePaths[] =
            $projectRoot .
            DIRECTORY_SEPARATOR .
            'assets' .
            DIRECTORY_SEPARATOR .
            'img' .
            DIRECTORY_SEPARATOR .
            'products' .
            DIRECTORY_SEPARATOR .
            $image;
    }


    /*
    |--------------------------------------------------------------------------
    | Find existing file
    |--------------------------------------------------------------------------
    */

    foreach ($possiblePaths as $path) {

        if (is_file($path)) {
            return $path;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Default path
    |--------------------------------------------------------------------------
    */

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
    SELECT *
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
| GET CATEGORIES
|--------------------------------------------------------------------------
*/

$catStmt = $conn->query("
    SELECT id, name
    FROM categories
    WHERE status = 1
    ORDER BY name ASC
");

$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| GET PRODUCT IMAGES
|--------------------------------------------------------------------------
*/

$imageStmt = $conn->prepare("
    SELECT id, image
    FROM product_images
    WHERE product_id = :product_id
    ORDER BY id ASC
");

$imageStmt->execute([
    ":product_id" => $id
]);

$productImages = $imageStmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| VARIABLES
|--------------------------------------------------------------------------
*/

$error = "";
$success = "";


/*
|--------------------------------------------------------------------------
| DELETE GALLERY IMAGE
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["delete_image"])
) {

    $imageId = (int)($_POST["image_id"] ?? 0);

    if ($imageId > 0) {

        $imgStmt = $conn->prepare("
            SELECT image
            FROM product_images
            WHERE id = :id
            AND product_id = :product_id
            LIMIT 1
        ");

        $imgStmt->execute([
            ":id" => $imageId,
            ":product_id" => $id
        ]);

        $imageRow = $imgStmt->fetch(PDO::FETCH_ASSOC);

        if ($imageRow) {

            /*
            |--------------------------------------------------------------------------
            | Resolve actual image path
            |--------------------------------------------------------------------------
            */

            $filePath = productImageFilePath(
                $imageRow["image"]
            );


            /*
            |--------------------------------------------------------------------------
            | Delete physical file
            |--------------------------------------------------------------------------
            */

            if ($filePath !== "" && is_file($filePath)) {

                unlink($filePath);
            }


            /*
            |--------------------------------------------------------------------------
            | Delete database record
            |--------------------------------------------------------------------------
            */

            $deleteStmt = $conn->prepare("
                DELETE FROM product_images
                WHERE id = :id
                AND product_id = :product_id
            ");

            $deleteStmt->execute([
                ":id" => $imageId,
                ":product_id" => $id
            ]);
        }
    }

    header("Location: edit.php?id=" . $id);
    exit;
}


/*
|--------------------------------------------------------------------------
| UPDATE PRODUCT
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["update_product"])
) {

    $categoryId = (int)($_POST["category_id"] ?? 0);

    $name = trim($_POST["name"] ?? "");

    $slug = trim($_POST["slug"] ?? "");

    $shortDescription =
        trim($_POST["short_description"] ?? "");

    $description =
        trim($_POST["description"] ?? "");

    $composition =
        trim($_POST["composition"] ?? "");

    $dosageForm =
        trim($_POST["dosage_form"] ?? "");

    $productType =
        trim($_POST["product_type"] ?? "");

    $packSize =
        trim($_POST["pack_size"] ?? "");

    $manufacturer =
        trim($_POST["manufacturer"] ?? "");

    $availability =
        trim($_POST["availability"] ?? "In Stock");

    $prescription =
        trim($_POST["prescription"] ?? "Not Required");

    $uses =
        trim($_POST["uses"] ?? "");

    $safetyInformation =
        trim($_POST["safety_information"] ?? "");

    $storageInformation =
        trim($_POST["storage_information"] ?? "");

    $status =
        isset($_POST["status"])
        ? (int)$_POST["status"]
        : 1;


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($categoryId <= 0) {

        $error = "Please select a category.";

    } elseif ($name === "") {

        $error = "Please enter product name.";

    } else {


        /*
        |--------------------------------------------------------------------------
        | SLUG
        |--------------------------------------------------------------------------
        */

        if ($slug === "") {

            $slug = slugify($name);

        } else {

            $slug = slugify($slug);
        }


        /*
        |--------------------------------------------------------------------------
        | CHECK UNIQUE SLUG
        |--------------------------------------------------------------------------
        */

        $slugStmt = $conn->prepare("
            SELECT id
            FROM products
            WHERE slug = :slug
            AND id != :id
            LIMIT 1
        ");

        $slugStmt->execute([
            ":slug" => $slug,
            ":id" => $id
        ]);

        if ($slugStmt->fetch()) {

            $slug = $slug . "-" . $id;
        }


        /*
        |--------------------------------------------------------------------------
        | MAIN IMAGE
        |--------------------------------------------------------------------------
        */

        $mainImage = $product["image"];


        /*
        |--------------------------------------------------------------------------
        | IMAGE UPLOAD FOLDER
        |--------------------------------------------------------------------------
        */

        $uploadDir = "../../assets/img/products/";


        if (!is_dir($uploadDir)) {

            mkdir($uploadDir, 0777, true);
        }


        /*
        |--------------------------------------------------------------------------
        | REPLACE MAIN IMAGE
        |--------------------------------------------------------------------------
        */

        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] === UPLOAD_ERR_OK
        ) {

            $allowedTypes = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];


            $fileType =
                mime_content_type(
                    $_FILES["image"]["tmp_name"]
                );


            if (!in_array($fileType, $allowedTypes)) {

                $error =
                    "Only JPG, PNG or WEBP images are allowed.";

            } elseif (
                $_FILES["image"]["size"] >
                5 * 1024 * 1024
            ) {

                $error =
                    "Main image must be less than 5 MB.";

            } else {

                $extension =
                    strtolower(
                        pathinfo(
                            $_FILES["image"]["name"],
                            PATHINFO_EXTENSION
                        )
                    );


                $newImageName =
                    "product-" .
                    $id .
                    "-" .
                    time() .
                    "." .
                    $extension;


                $targetPath =
                    $uploadDir .
                    $newImageName;


                if (
                    move_uploaded_file(
                        $_FILES["image"]["tmp_name"],
                        $targetPath
                    )
                ) {


                    /*
                    |--------------------------------------------------------------------------
                    | Delete old image
                    |--------------------------------------------------------------------------
                    */

                    if (!empty($mainImage)) {

                        $oldImagePath =
                            productImageFilePath(
                                $mainImage
                            );


                        if (
                            $oldImagePath !== "" &&
                            is_file($oldImagePath)
                        ) {

                            unlink($oldImagePath);
                        }
                    }


                    $mainImage =
                        $newImageName;

                } else {

                    $error =
                        "Failed to upload main image.";
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE DATABASE
        |--------------------------------------------------------------------------
        */

        if ($error === "") {

            try {

                $conn->beginTransaction();


                $updateStmt = $conn->prepare("
                    UPDATE products
                    SET
                        category_id = :category_id,
                        name = :name,
                        slug = :slug,
                        image = :image,
                        short_description = :short_description,
                        description = :description,
                        composition = :composition,
                        dosage_form = :dosage_form,
                        product_type = :product_type,
                        pack_size = :pack_size,
                        manufacturer = :manufacturer,
                        availability = :availability,
                        prescription = :prescription,
                        uses = :uses,
                        safety_information = :safety_information,
                        storage_information = :storage_information,
                        status = :status
                    WHERE id = :id
                ");


                $updateStmt->execute([

                    ":category_id" =>
                        $categoryId,

                    ":name" =>
                        $name,

                    ":slug" =>
                        $slug,

                    ":image" =>
                        $mainImage,

                    ":short_description" =>
                        $shortDescription,

                    ":description" =>
                        $description,

                    ":composition" =>
                        $composition,

                    ":dosage_form" =>
                        $dosageForm,

                    ":product_type" =>
                        $productType,

                    ":pack_size" =>
                        $packSize,

                    ":manufacturer" =>
                        $manufacturer,

                    ":availability" =>
                        $availability,

                    ":prescription" =>
                        $prescription,

                    ":uses" =>
                        $uses,

                    ":safety_information" =>
                        $safetyInformation,

                    ":storage_information" =>
                        $storageInformation,

                    ":status" =>
                        $status,

                    ":id" =>
                        $id
                ]);


                /*
                |--------------------------------------------------------------------------
                | MULTIPLE IMAGES
                |--------------------------------------------------------------------------
                */

                if (
                    isset($_FILES["gallery_images"]) &&
                    isset($_FILES["gallery_images"]["name"]) &&
                    is_array($_FILES["gallery_images"]["name"])
                ) {

                    $allowedTypes = [
                        "image/jpeg",
                        "image/png",
                        "image/webp"
                    ];


                    foreach (
                        $_FILES["gallery_images"]["name"]
                        as $key => $originalName
                    ) {

                        if (
                            empty($originalName) ||
                            $_FILES["gallery_images"]["error"][$key]
                            !== UPLOAD_ERR_OK
                        ) {

                            continue;
                        }


                        $tmpName =
                            $_FILES["gallery_images"]["tmp_name"][$key];


                        $fileSize =
                            $_FILES["gallery_images"]["size"][$key];


                        $fileType =
                            mime_content_type($tmpName);


                        if (
                            !in_array(
                                $fileType,
                                $allowedTypes
                            )
                        ) {

                            continue;
                        }


                        if (
                            $fileSize >
                            5 * 1024 * 1024
                        ) {

                            continue;
                        }


                        $extension =
                            strtolower(
                                pathinfo(
                                    $originalName,
                                    PATHINFO_EXTENSION
                                )
                            );


                        $galleryName =
                            "product-" .
                            $id .
                            "-gallery-" .
                            time() .
                            "-" .
                            $key .
                            "." .
                            $extension;


                        $galleryPath =
                            $uploadDir .
                            $galleryName;


                        if (
                            move_uploaded_file(
                                $tmpName,
                                $galleryPath
                            )
                        ) {

                            $galleryStmt =
                                $conn->prepare("
                                    INSERT INTO product_images
                                    (
                                        product_id,
                                        image
                                    )
                                    VALUES
                                    (
                                        :product_id,
                                        :image
                                    )
                                ");


                            $galleryStmt->execute([

                                ":product_id" =>
                                    $id,

                                ":image" =>
                                    $galleryName
                            ]);
                        }
                    }
                }


                $conn->commit();


                header(
                    "Location: edit.php?id=" .
                    $id .
                    "&updated=1"
                );

                exit;


            } catch (Exception $e) {

                if ($conn->inTransaction()) {

                    $conn->rollBack();
                }

                $error =
                    "Product update failed. Please try again.";
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| SUCCESS MESSAGE
|--------------------------------------------------------------------------
*/

if (
    isset($_GET["updated"]) &&
    $_GET["updated"] == "1"
) {

    $success =
        "Product updated successfully.";
}


/*
|--------------------------------------------------------------------------
| REFRESH PRODUCT DATA
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT *
    FROM products
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ":id" => $id
]);

$product =
    $stmt->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| REFRESH GALLERY
|--------------------------------------------------------------------------
*/

$imageStmt = $conn->prepare("
    SELECT id, image
    FROM product_images
    WHERE product_id = :product_id
    ORDER BY id ASC
");

$imageStmt->execute([
    ":product_id" => $id
]);

$productImages =
    $imageStmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| PAGE VARIABLES
|--------------------------------------------------------------------------
*/

$pageTitle = "Edit Product";

$breadcrumb =
    "Medinef Pharma / Products / Edit";

$activeMenu = "products";


require_once "../includes/header.php";

require_once "../includes/sidebar.php";

?>


<style>

.product-page {
    padding: 38px 55px 80px;
}


.product-page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 30px;
    margin-bottom: 28px;
}


.product-page-header h1 {
    margin: 0 0 7px;
    font-size: 38px;
    color: #17152b;
}


.product-page-header p {
    margin: 0;
    color: #77738b;
    font-size: 16px;
}


.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;

    padding: 13px 20px;

    background: #ffffff;
    border: 1px solid #e4e0f1;

    border-radius: 10px;

    color: #4B4099;

    text-decoration: none;

    font-weight: 600;

    white-space: nowrap;

    transition: .2s;
}


.back-btn:hover {
    background: #f1effb;
}


.form-card {
    background: #ffffff;

    border: 1px solid #eeeaf7;

    border-radius: 18px;

    padding: 38px 42px;

    box-shadow:
        0 10px 35px rgba(75,64,153,.05);
}


.form-grid {
    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 26px 32px;
}


.form-group {
    margin-bottom: 2px;
}


.form-group.full {
    grid-column: 1 / -1;
}


.form-group label {
    display: block;

    margin-bottom: 9px;

    color: #17152b;

    font-size: 15px;

    font-weight: 700;
}


.form-group input,
.form-group select,
.form-group textarea {

    width: 100%;

    border: 1px solid #ded9ec;

    border-radius: 10px;

    background: #ffffff;

    color: #4e4a5b;

    font-family: inherit;

    font-size: 15px;

    outline: none;

    transition: .2s;

    box-sizing: border-box;
}


.form-group input,
.form-group select {

    height: 54px;

    padding: 0 16px;
}


.form-group textarea {

    min-height: 130px;

    padding: 15px 16px;

    resize: vertical;
}


.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {

    border-color: #4B4099;

    box-shadow:
        0 0 0 3px rgba(75,64,153,.08);
}


.help-text {

    display: block;

    margin-top: 7px;

    color: #9993aa;

    font-size: 13px;
}


.alert {

    padding: 14px 17px;

    border-radius: 10px;

    margin-bottom: 24px;

    font-size: 14px;
}


.alert-error {

    background: #fff0f0;

    border: 1px solid #ffd3d3;

    color: #c62828;
}


.alert-success {

    background: #eefaf2;

    border: 1px solid #ccebd6;

    color: #267343;
}


.image-box {

    border: 1px solid #ded9ec;

    border-radius: 12px;

    padding: 16px;

    background: #faf9fd;
}


.current-image {

    width: 150px;

    height: 150px;

    border-radius: 12px;

    border: 1px solid #e5e1ef;

    background: #ffffff;

    display: flex;

    align-items: center;

    justify-content: center;

    overflow: hidden;

    margin-bottom: 14px;
}


.current-image img {

    width: 100%;

    height: 100%;

    object-fit: contain;

    display: block;
}


.no-image {

    color: #aaa5b9;

    font-size: 13px;
}


.image-box input[type="file"] {

    width: 100%;

    height: auto;

    padding: 10px;

    background: #ffffff;

    box-sizing: border-box;
}


.gallery-title {

    margin: 35px 0 18px;

    padding-top: 28px;

    border-top: 1px solid #eeeaf7;

    color: #17152b;

    font-size: 21px;
}


.gallery-grid {

    display: grid;

    grid-template-columns:
        repeat(5, 1fr);

    gap: 18px;
}


.gallery-item {

    position: relative;

    border: 1px solid #e5e1ef;

    border-radius: 12px;

    padding: 10px;

    background: #ffffff;
}


.gallery-item img {

    display: block;

    width: 100%;

    height: 145px;

    object-fit: contain;

    border-radius: 8px;

    background: #fafafa;
}


.delete-image-form {

    margin-top: 9px;
}


.delete-image-btn {

    width: 100%;

    border: none;

    border-radius: 7px;

    background: #fff0f0;

    color: #d33;

    padding: 8px;

    font-size: 12px;

    font-weight: 600;

    cursor: pointer;
}


.delete-image-btn:hover {

    background: #ffe0e0;
}


.form-actions {

    display: flex;

    align-items: center;

    justify-content: flex-end;

    gap: 12px;

    margin-top: 35px;

    padding-top: 25px;

    border-top: 1px solid #eeeaf7;
}


.cancel-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    height: 50px;

    padding: 0 24px;

    border: 1px solid #ddd8eb;

    border-radius: 9px;

    background: #ffffff;

    color: #5e596b;

    text-decoration: none;

    font-weight: 600;
}


.save-btn {

    height: 50px;

    padding: 0 28px;

    border: none;

    border-radius: 9px;

    background: #4B4099;

    color: #ffffff;

    font-size: 15px;

    font-weight: 700;

    cursor: pointer;

    box-shadow:
        0 7px 18px rgba(75,64,153,.18);
}


.save-btn:hover {

    background: #40358c;
}


@media(max-width:1000px) {

    .product-page {

        padding: 30px;
    }


    .gallery-grid {

        grid-template-columns:
            repeat(3, 1fr);
    }
}


@media(max-width:750px) {

    .product-page {

        padding: 25px 18px 60px;
    }


    .product-page-header {

        flex-direction: column;
    }


    .form-card {

        padding: 25px 20px;
    }


    .form-grid {

        grid-template-columns: 1fr;
    }


    .form-group.full {

        grid-column: auto;
    }


    .gallery-grid {

        grid-template-columns:
            repeat(2, 1fr);
    }
}

</style>


<main class="main">


    <!-- TOPBAR -->

    <header class="topbar">

        <div class="page-title">

            <h2>
                Edit Product
            </h2>

            <span>
                <?= htmlspecialchars($breadcrumb) ?>
            </span>

        </div>


        <div class="top-right">

            <div class="notification">
                ♧
            </div>


            <div class="profile">

                <div class="profile-avatar">

                    <?= strtoupper(
                        substr(
                            $_SESSION["admin_name"] ?? "A",
                            0,
                            1
                        )
                    ) ?>

                </div>


                <div class="profile-info">

                    <strong>
                        <?= htmlspecialchars(
                            $_SESSION["admin_name"] ?? "Admin"
                        ) ?>
                    </strong>

                    <span>
                        Administrator
                    </span>

                </div>

            </div>

        </div>

    </header>


    <!-- CONTENT -->

    <section class="content">


        <div class="product-page">


            <!-- PAGE HEADER -->

            <div class="product-page-header">

                <div>

                    <h1>
                        Edit Product
                    </h1>

                    <p>
                        Update Medinef Pharma product information
                    </p>

                </div>


                <a
                    href="index.php"
                    class="back-btn"
                >
                    ← Back to Products
                </a>

            </div>


            <!-- ALERTS -->

            <?php if ($error !== ""): ?>

                <div class="alert alert-error">

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>


            <?php if ($success !== ""): ?>

                <div class="alert alert-success">

                    <?= htmlspecialchars($success) ?>

                </div>

            <?php endif; ?>


            <!-- FORM -->

            <form
                method="POST"
                enctype="multipart/form-data"
            >


                <div class="form-card">


                    <div class="form-grid">


                        <!-- CATEGORY -->

                        <div class="form-group">

                            <label>
                                Category *
                            </label>

                            <select
                                name="category_id"
                                required
                            >

                                <option value="">
                                    Select Category
                                </option>


                                <?php foreach ($categories as $category): ?>

                                    <option
                                        value="<?= (int)$category["id"] ?>"
                                        <?= (int)$product["category_id"] === (int)$category["id"]
                                            ? "selected"
                                            : "" ?>
                                    >

                                        <?= htmlspecialchars(
                                            $category["name"]
                                        ) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- PRODUCT NAME -->

                        <div class="form-group">

                            <label>
                                Product Name *
                            </label>

                            <input
                                type="text"
                                name="name"
                                value="<?= htmlspecialchars(
                                    $product["name"] ?? ""
                                ) ?>"
                                placeholder="e.g. Nemozid-P"
                                required
                            >

                        </div>


                        <!-- SLUG -->

                        <div class="form-group">

                            <label>
                                Slug
                            </label>

                            <input
                                type="text"
                                name="slug"
                                value="<?= htmlspecialchars(
                                    $product["slug"] ?? ""
                                ) ?>"
                                placeholder="product-slug"
                            >

                            <span class="help-text">
                                Leave blank to generate automatically.
                            </span>

                        </div>


                        <!-- STATUS -->

                        <div class="form-group">

                            <label>
                                Status *
                            </label>

                            <select
                                name="status"
                            >

                                <option
                                    value="1"
                                    <?= (int)$product["status"] === 1
                                        ? "selected"
                                        : "" ?>
                                >
                                    Active
                                </option>

                                <option
                                    value="0"
                                    <?= (int)$product["status"] === 0
                                        ? "selected"
                                        : "" ?>
                                >
                                    Inactive
                                </option>

                            </select>

                        </div>


                        <!-- MAIN IMAGE -->

                        <div class="form-group">

                            <label>
                                Product Image
                            </label>

                            <div class="image-box">


                                <div class="current-image">

                                    <?php if (!empty($product["image"])): ?>

                                        <img
                                            src="<?= htmlspecialchars(
                                                productImageUrl(
                                                    $product["image"]
                                                )
                                            ) ?>"
                                            alt="<?= htmlspecialchars(
                                                $product["name"]
                                            ) ?>"
                                            onerror="this.style.display='none';"
                                        >

                                    <?php else: ?>

                                        <span class="no-image">
                                            No image
                                        </span>

                                    <?php endif; ?>

                                </div>


                                <input
                                    type="file"
                                    name="image"
                                    accept=".jpg,.jpeg,.png,.webp"
                                >


                                <span class="help-text">
                                    Select a new image only if you want to replace the current image. JPG, PNG or WEBP. Maximum 5 MB.
                                </span>

                            </div>

                        </div>


                        <!-- GALLERY -->

                        <div class="form-group">

                            <label>
                                Add More Images
                            </label>

                            <div class="image-box">

                                <input
                                    type="file"
                                    name="gallery_images[]"
                                    accept=".jpg,.jpeg,.png,.webp"
                                    multiple
                                >

                                <span class="help-text">
                                    You can select multiple product images. Maximum 5 MB each.
                                </span>

                            </div>

                        </div>


                        <!-- SHORT DESCRIPTION -->

                        <div class="form-group full">

                            <label>
                                Short Description
                            </label>

                            <textarea
                                name="short_description"
                                placeholder="Enter short product description..."
                            ><?= htmlspecialchars(
                                $product["short_description"] ?? ""
                            ) ?></textarea>

                        </div>


                        <!-- COMPOSITION -->

                        <div class="form-group">

                            <label>
                                Composition
                            </label>

                            <textarea
                                name="composition"
                                placeholder="Enter product composition..."
                            ><?= htmlspecialchars(
                                $product["composition"] ?? ""
                            ) ?></textarea>

                        </div>


                        <!-- DOSAGE FORM -->

                        <div class="form-group">

                            <label>
                                Dosage Form
                            </label>

                            <input
                                type="text"
                                name="dosage_form"
                                value="<?= htmlspecialchars(
                                    $product["dosage_form"] ?? ""
                                ) ?>"
                                placeholder="e.g. Tablet, Syrup, Capsule"
                            >

                        </div>


                        <!-- PRODUCT TYPE -->

                        <div class="form-group">

                            <label>
                                Product Type
                            </label>

                            <input
                                type="text"
                                name="product_type"
                                value="<?= htmlspecialchars(
                                    $product["product_type"] ?? ""
                                ) ?>"
                                placeholder="e.g. Pharmaceutical"
                            >

                        </div>


                        <!-- PACK SIZE -->

                        <div class="form-group">

                            <label>
                                Pack Size
                            </label>

                            <input
                                type="text"
                                name="pack_size"
                                value="<?= htmlspecialchars(
                                    $product["pack_size"] ?? ""
                                ) ?>"
                                placeholder="e.g. 10 Tablets"
                            >

                        </div>


                        <!-- MANUFACTURER -->

                        <div class="form-group">

                            <label>
                                Manufacturer
                            </label>

                            <input
                                type="text"
                                name="manufacturer"
                                value="<?= htmlspecialchars(
                                    $product["manufacturer"] ?? ""
                                ) ?>"
                                placeholder="Manufacturer name"
                            >

                        </div>


                        <!-- AVAILABILITY -->

                        <div class="form-group">

                            <label>
                                Availability
                            </label>

                            <select name="availability">

                                <option
                                    value="In Stock"
                                    <?= ($product["availability"] ?? "")
                                        === "In Stock"
                                        ? "selected"
                                        : "" ?>
                                >
                                    In Stock
                                </option>

                                <option
                                    value="Out of Stock"
                                    <?= ($product["availability"] ?? "")
                                        === "Out of Stock"
                                        ? "selected"
                                        : "" ?>
                                >
                                    Out of Stock
                                </option>

                                <option
                                    value="Available Soon"
                                    <?= ($product["availability"] ?? "")
                                        === "Available Soon"
                                        ? "selected"
                                        : "" ?>
                                >
                                    Available Soon
                                </option>

                            </select>

                        </div>


                        <!-- PRESCRIPTION -->

                        <div class="form-group">

                            <label>
                                Prescription
                            </label>

                            <select name="prescription">

                                <option
                                    value="Not Required"
                                    <?= ($product["prescription"] ?? "")
                                        === "Not Required"
                                        ? "selected"
                                        : "" ?>
                                >
                                    Not Required
                                </option>

                                <option
                                    value="Required"
                                    <?= ($product["prescription"] ?? "")
                                        === "Required"
                                        ? "selected"
                                        : "" ?>
                                >
                                    Required
                                </option>

                            </select>

                        </div>


                        <!-- DESCRIPTION -->

                        <div class="form-group full">

                            <label>
                                Description
                            </label>

                            <textarea
                                name="description"
                                style="min-height:180px;"
                                placeholder="Enter complete product description..."
                            ><?= htmlspecialchars(
                                $product["description"] ?? ""
                            ) ?></textarea>

                        </div>


                        <!-- USES -->

                        <div class="form-group">

                            <label>
                                Uses
                            </label>

                            <textarea
                                name="uses"
                                placeholder="Enter product uses..."
                            ><?= htmlspecialchars(
                                $product["uses"] ?? ""
                            ) ?></textarea>

                        </div>


                        <!-- SAFETY -->

                        <div class="form-group">

                            <label>
                                Safety Information
                            </label>

                            <textarea
                                name="safety_information"
                                placeholder="Enter safety information..."
                            ><?= htmlspecialchars(
                                $product["safety_information"] ?? ""
                            ) ?></textarea>

                        </div>


                        <!-- STORAGE -->

                        <div class="form-group full">

                            <label>
                                Storage Information
                            </label>

                            <textarea
                                name="storage_information"
                                placeholder="Enter storage information..."
                            ><?= htmlspecialchars(
                                $product["storage_information"] ?? ""
                            ) ?></textarea>

                        </div>


                    </div>


                    <!-- EXISTING GALLERY -->

                    <?php if (!empty($productImages)): ?>

                        <h3 class="gallery-title">
                            Product Gallery
                        </h3>


                        <div class="gallery-grid">


                            <?php foreach (
                                $productImages
                                as $galleryImage
                            ): ?>

                                <div class="gallery-item">


                                    <img
                                        src="<?= htmlspecialchars(
                                            productImageUrl(
                                                $galleryImage["image"]
                                            )
                                        ) ?>"
                                        alt="Product Image"
                                        onerror="this.style.display='none';"
                                    >


                                    <form
                                        method="POST"
                                        class="delete-image-form"
                                        onsubmit="return confirm('Delete this image?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="image_id"
                                            value="<?= (int)$galleryImage["id"] ?>"
                                        >


                                        <button
                                            type="submit"
                                            name="delete_image"
                                            class="delete-image-btn"
                                        >
                                            Delete Image
                                        </button>

                                    </form>


                                </div>

                            <?php endforeach; ?>


                        </div>

                    <?php endif; ?>


                    <!-- ACTIONS -->

                    <div class="form-actions">


                        <a
                            href="index.php"
                            class="cancel-btn"
                        >
                            Cancel
                        </a>


                        <button
                            type="submit"
                            name="update_product"
                            class="save-btn"
                        >
                            Save Changes
                        </button>


                    </div>


                </div>


            </form>


        </div>

    </section>

</main>


<?php require_once "../includes/footer.php"; ?>