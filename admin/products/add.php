<?php

session_start();

if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit;
}

$adminName = $_SESSION['admin_name'] ?? 'Admin';

$pageTitle  = "Add Product";
$breadcrumb = "Medinef Pharma / Products / Add";
$activeMenu = "products";

require_once "../../config/database.php";


// ==========================================
// VARIABLES
// ==========================================

$error = "";

$name = "";
$slug = "";
$category_id = "";
$short_description = "";
$description = "";
$composition = "";
$dosage_form = "";
$product_type = "";
$pack_size = "";
$manufacturer = "";
$availability = "In Stock";
$prescription = "Not Required";
$uses = "";
$safety_information = "";
$storage_information = "";
$status = "1";


// ==========================================
// FETCH CATEGORIES
// ==========================================

$categoryStmt = $conn->prepare("
    SELECT id, name
    FROM categories
    WHERE status = 1
    ORDER BY name ASC
");

$categoryStmt->execute();

$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);


// ==========================================
// SLUG FUNCTION
// ==========================================

function createSlug($text)
{
    $text = strtolower(trim($text));

    $text = preg_replace('/[^a-z0-9]+/', '-', $text);

    $text = trim($text, '-');

    return $text;
}


// ==========================================
// UPLOAD DIRECTORY
// ==========================================

$uploadDir = "../../uploads/products/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}


// ==========================================
// FORM SUBMIT
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");

    $slug = trim($_POST["slug"] ?? "");

    $category_id = (int)($_POST["category_id"] ?? 0);

    $short_description = trim($_POST["short_description"] ?? "");

    $description = trim($_POST["description"] ?? "");

    $composition = trim($_POST["composition"] ?? "");

    $dosage_form = trim($_POST["dosage_form"] ?? "");

    $product_type = trim($_POST["product_type"] ?? "");

    $pack_size = trim($_POST["pack_size"] ?? "");

    $manufacturer = trim($_POST["manufacturer"] ?? "");

    $availability = trim($_POST["availability"] ?? "In Stock");

    $prescription = trim($_POST["prescription"] ?? "Not Required");

    $uses = trim($_POST["uses"] ?? "");

    $safety_information = trim($_POST["safety_information"] ?? "");

    $storage_information = trim($_POST["storage_information"] ?? "");

    $status = isset($_POST["status"]) ? (int)$_POST["status"] : 1;


    // ==========================================
    // VALIDATION
    // ==========================================

    if ($name === "") {

        $error = "Product name is required.";

    } elseif ($category_id <= 0) {

        $error = "Please select a category.";

    } else {


        // ==========================================
        // AUTO SLUG
        // ==========================================

        if ($slug === "") {
            $slug = createSlug($name);
        } else {
            $slug = createSlug($slug);
        }


        // ==========================================
        // CHECK SLUG
        // ==========================================

        $slugCheck = $conn->prepare("
            SELECT id
            FROM products
            WHERE slug = :slug
            LIMIT 1
        ");

        $slugCheck->execute([
            ":slug" => $slug
        ]);

        if ($slugCheck->fetch()) {

            $error = "This product slug already exists.";

        } else {


            // ==========================================
            // MAIN IMAGE
            // ==========================================

            $mainImage = null;

            if (
                isset($_FILES["image"]) &&
                $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
            ) {

                if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {

                    $error = "Main product image upload failed.";

                } else {

                    $file = $_FILES["image"];

                    $maxSize = 5 * 1024 * 1024;

                    if ($file["size"] > $maxSize) {

                        $error = "Main image must be maximum 5 MB.";

                    } else {

                        $allowedTypes = [
                            "image/jpeg" => "jpg",
                            "image/png"  => "png",
                            "image/webp" => "webp"
                        ];

                        $mime = mime_content_type($file["tmp_name"]);

                        if (!isset($allowedTypes[$mime])) {

                            $error = "Only JPG, PNG and WEBP images are allowed.";

                        } else {

                            $extension = $allowedTypes[$mime];

                            $fileName = uniqid("product_", true) . "." . $extension;

                            $destination = $uploadDir . $fileName;

                            if (move_uploaded_file($file["tmp_name"], $destination)) {

                                $mainImage = "uploads/products/" . $fileName;

                            } else {

                                $error = "Unable to save main product image.";
                            }
                        }
                    }
                }
            }


            // ==========================================
            // INSERT PRODUCT
            // ==========================================

            if ($error === "") {

                try {

                    $conn->beginTransaction();


                    $stmt = $conn->prepare("
                        INSERT INTO products
                        (
                            category_id,
                            name,
                            slug,
                            image,
                            short_description,
                            description,
                            composition,
                            dosage_form,
                            product_type,
                            pack_size,
                            manufacturer,
                            availability,
                            prescription,
                            uses,
                            safety_information,
                            storage_information,
                            status
                        )
                        VALUES
                        (
                            :category_id,
                            :name,
                            :slug,
                            :image,
                            :short_description,
                            :description,
                            :composition,
                            :dosage_form,
                            :product_type,
                            :pack_size,
                            :manufacturer,
                            :availability,
                            :prescription,
                            :uses,
                            :safety_information,
                            :storage_information,
                            :status
                        )
                    ");


                    $stmt->execute([

                        ":category_id" => $category_id,

                        ":name" => $name,

                        ":slug" => $slug,

                        ":image" => $mainImage,

                        ":short_description" => $short_description,

                        ":description" => $description,

                        ":composition" => $composition,

                        ":dosage_form" => $dosage_form,

                        ":product_type" => $product_type,

                        ":pack_size" => $pack_size,

                        ":manufacturer" => $manufacturer,

                        ":availability" => $availability,

                        ":prescription" => $prescription,

                        ":uses" => $uses,

                        ":safety_information" => $safety_information,

                        ":storage_information" => $storage_information,

                        ":status" => $status

                    ]);


                    $productId = $conn->lastInsertId();


                    // ==========================================
                    // MULTIPLE IMAGES
                    // ==========================================

                    if (
                        isset($_FILES["product_images"]) &&
                        is_array($_FILES["product_images"]["name"])
                    ) {

                        $imageCount = count(
                            $_FILES["product_images"]["name"]
                        );


                        for ($i = 0; $i < $imageCount; $i++) {

                            if (
                                $_FILES["product_images"]["error"][$i]
                                === UPLOAD_ERR_NO_FILE
                            ) {
                                continue;
                            }


                            if (
                                $_FILES["product_images"]["error"][$i]
                                !== UPLOAD_ERR_OK
                            ) {
                                continue;
                            }


                            $tmpName = $_FILES["product_images"]["tmp_name"][$i];

                            $fileSize = $_FILES["product_images"]["size"][$i];


                            if ($fileSize > 5 * 1024 * 1024) {
                                continue;
                            }


                            $mime = mime_content_type($tmpName);


                            $allowedTypes = [
                                "image/jpeg" => "jpg",
                                "image/png"  => "png",
                                "image/webp" => "webp"
                            ];


                            if (!isset($allowedTypes[$mime])) {
                                continue;
                            }


                            $extension = $allowedTypes[$mime];

                            $fileName = uniqid(
                                "product_gallery_",
                                true
                            ) . "." . $extension;


                            $destination = $uploadDir . $fileName;


                            if (
                                move_uploaded_file(
                                    $tmpName,
                                    $destination
                                )
                            ) {

                                $imagePath =
                                    "uploads/products/" . $fileName;


                                $imageStmt = $conn->prepare("
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


                                $imageStmt->execute([

                                    ":product_id" => $productId,

                                    ":image" => $imagePath

                                ]);
                            }
                        }
                    }


                    $conn->commit();


                    header("Location: index.php?success=added");

                    exit;


                } catch (Exception $e) {

                    if ($conn->inTransaction()) {
                        $conn->rollBack();
                    }

                    $error =
                        "Product could not be added. " .
                        $e->getMessage();
                }
            }
        }
    }
}


/*
 * IMPORTANT:
 * header.php/sidebar.php are included only AFTER POST processing.
 * This prevents "headers already sent" when redirecting after save.
 */
require_once "../includes/header.php";
require_once "../includes/sidebar.php";

?>

<style>

/* ==========================================
   ADD PRODUCT PAGE
========================================== */

.product-add-page {
    padding: 30px 40px;
}

.product-add-header {
    margin-bottom: 25px;
}

.product-add-header h1 {
    margin: 0 0 7px;

    color: #17152a;

    font-size: 30px;
    font-weight: 700;
}

.product-add-header p {
    margin: 0;

    color: #8c879d;

    font-size: 15px;
}

.back-btn {
    display: inline-flex;

    align-items: center;

    gap: 7px;

    margin-top: 15px;

    padding: 10px 16px;

    border: 1px solid #e3e0ed;

    border-radius: 8px;

    background: #ffffff;

    color: #4B4099;

    text-decoration: none;

    font-size: 14px;

    font-weight: 600;
}

.back-btn:hover {
    background: #f3f1fb;
}


/* ERROR */

.form-error {
    background: #fff1f1;

    border: 1px solid #ffd5d5;

    color: #c43838;

    padding: 13px 16px;

    border-radius: 8px;

    margin-bottom: 20px;

    font-size: 14px;
}


/* FORM CARD */

.product-form-card {
    background: #ffffff;

    border: 1px solid #eeeaf7;

    border-radius: 15px;

    padding: 30px;
}

.form-section {
    margin-bottom: 32px;
}

.form-section:last-child {
    margin-bottom: 0;
}

.form-section-title {
    margin-bottom: 20px;

    padding-bottom: 12px;

    border-bottom: 1px solid #eeeaf7;

    color: #252238;

    font-size: 18px;

    font-weight: 700;
}


/* GRID */

.form-grid {
    display: grid;

    grid-template-columns: repeat(2, minmax(0, 1fr));

    gap: 22px 25px;
}

.form-group.full {
    grid-column: 1 / -1;
}

.form-group label {
    display: block;

    margin-bottom: 8px;

    color: #29263a;

    font-size: 14px;

    font-weight: 600;
}

.required {
    color: #d33;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;

    border: 1px solid #e0ddeb;

    border-radius: 8px;

    padding: 12px 14px;

    outline: none;

    background: #ffffff;

    color: #403c4d;

    font-family: inherit;

    font-size: 14px;
}

.form-group input,
.form-group select {
    height: 48px;
}

.form-group textarea {
    min-height: 120px;

    resize: vertical;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #4B4099;

    box-shadow: 0 0 0 3px rgba(75,64,153,.07);
}

.help-text {
    margin-top: 7px;

    color: #9993a8;

    font-size: 12px;
}


/* IMAGE */

.image-upload {
    border: 1px dashed #d7d2e7;

    border-radius: 10px;

    padding: 16px;

    background: #faf9fd;
}

.image-upload input {
    border: 0;

    padding: 0;

    height: auto;

    background: transparent;
}


/* BUTTONS */

.form-actions {
    display: flex;

    align-items: center;

    justify-content: flex-end;

    gap: 12px;

    margin-top: 30px;

    padding-top: 25px;

    border-top: 1px solid #eeeaf7;
}

.cancel-btn {
    height: 46px;

    padding: 0 20px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    border-radius: 8px;

    background: #f3f1fb;

    color: #4B4099;

    text-decoration: none;

    font-size: 14px;

    font-weight: 600;
}

.save-btn {
    height: 46px;

    padding: 0 25px;

    border: 0;

    border-radius: 8px;

    background: #4B4099;

    color: #ffffff;

    font-size: 14px;

    font-weight: 600;

    cursor: pointer;
}

.save-btn:hover {
    background: #3f3589;
}


/* MOBILE */

@media(max-width: 850px) {

    .product-add-page {
        padding: 25px 20px;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .form-group.full {
        grid-column: auto;
    }

    .product-form-card {
        padding: 20px;
    }

}

</style>


<main class="main">

    <!-- TOPBAR -->

    <header class="topbar">

        <div class="page-title">

            <h2>
                Add Product
            </h2>

            <span>
                Medinef Pharma / Products / Add
            </span>

        </div>


        <div class="top-right">

            <div class="notification">
                ♧
            </div>


            <div class="profile">

                <div class="profile-avatar">
                    <?= strtoupper(substr($adminName, 0, 1)) ?>
                </div>

                <div class="profile-info">

                    <strong>
                        <?= htmlspecialchars($adminName) ?>
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

        <div class="product-add-page">


            <!-- HEADER -->

            <div class="category-page-header">

            <div>
                <h1>Add product</h1>
                <p>Create a new Medinef Pharma product </p>
            </div>

            <a href="index.php" class="back-btn">
                ← Back to product
            </a>

        </div>


            <?php if ($error !== ""): ?>

                <div class="form-error">

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>


            <!-- FORM -->

            <form
                method="POST"
                enctype="multipart/form-data"
                class="product-form-card"
            >


                <!-- BASIC INFORMATION -->

                <div class="form-section">

                    <div class="form-section-title">
                        Basic Information
                    </div>


                    <div class="form-grid">


                        <!-- NAME -->

                        <div class="form-group">

                            <label>
                                Product Name
                                <span class="required">*</span>
                            </label>

                            <input
                                type="text"
                                name="name"
                                value="<?= htmlspecialchars($name) ?>"
                                placeholder="e.g. Nemozid-P"
                                required
                            >

                        </div>


                        <!-- CATEGORY -->

                        <div class="form-group">

                            <label>
                                Category
                                <span class="required">*</span>
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
                                        value="<?= (int)$category['id'] ?>"
                                        <?= $category_id == $category['id'] ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- SLUG -->

                        <div class="form-group">

                            <label>
                                Slug
                            </label>

                            <input
                                type="text"
                                name="slug"
                                value="<?= htmlspecialchars($slug) ?>"
                                placeholder="nemozid-p"
                            >

                            <div class="help-text">
                                Leave blank to generate automatically.
                            </div>

                        </div>


                        <!-- PRODUCT TYPE -->

                        <div class="form-group">

                            <label>
                                Product Type
                            </label>

                            <input
                                type="text"
                                name="product_type"
                                value="<?= htmlspecialchars($product_type) ?>"
                                placeholder="e.g. Pharmaceutical"
                            >

                        </div>


                    </div>

                </div>


                <!-- PRODUCT IMAGES -->

                <div class="form-section">

                    <div class="form-section-title">
                        Product Images
                    </div>


                    <div class="form-grid">


                        <!-- MAIN IMAGE -->

                        <div class="form-group">

                            <label>
                                Main Product Image
                            </label>

                            <div class="image-upload">

                                <input
                                    type="file"
                                    name="image"
                                    accept=".jpg,.jpeg,.png,.webp"
                                >

                            </div>

                            <div class="help-text">
                                JPG, PNG or WEBP. Maximum 5 MB.
                            </div>

                        </div>


                        <!-- MULTIPLE -->

                        <div class="form-group">

                            <label>
                                Additional Product Images
                            </label>

                            <div class="image-upload">

                                <input
                                    type="file"
                                    name="product_images[]"
                                    accept=".jpg,.jpeg,.png,.webp"
                                    multiple
                                >

                            </div>

                            <div class="help-text">
                                You can select multiple images. Maximum 5 MB each.
                            </div>

                        </div>


                    </div>

                </div>


                <!-- PRODUCT DETAILS -->

                <div class="form-section">

                    <div class="form-section-title">
                        Product Details
                    </div>


                    <div class="form-grid">


                        <div class="form-group">

                            <label>
                                Composition
                            </label>

                            <input
                                type="text"
                                name="composition"
                                value="<?= htmlspecialchars($composition) ?>"
                                placeholder="e.g. Paracetamol 500mg"
                            >

                        </div>


                        <div class="form-group">

                            <label>
                                Dosage Form
                            </label>

                            <input
                                type="text"
                                name="dosage_form"
                                value="<?= htmlspecialchars($dosage_form) ?>"
                                placeholder="e.g. Tablet"
                            >

                        </div>


                        <div class="form-group">

                            <label>
                                Pack Size
                            </label>

                            <input
                                type="text"
                                name="pack_size"
                                value="<?= htmlspecialchars($pack_size) ?>"
                                placeholder="e.g. 10 Tablets"
                            >

                        </div>


                        <div class="form-group">

                            <label>
                                Manufacturer
                            </label>

                            <input
                                type="text"
                                name="manufacturer"
                                value="<?= htmlspecialchars($manufacturer) ?>"
                                placeholder="Manufacturer name"
                            >

                        </div>


                        <div class="form-group">

                            <label>
                                Availability
                            </label>

                            <select name="availability">

                                <option value="In Stock"
                                    <?= $availability === "In Stock" ? "selected" : "" ?>>
                                    In Stock
                                </option>

                                <option value="Out of Stock"
                                    <?= $availability === "Out of Stock" ? "selected" : "" ?>>
                                    Out of Stock
                                </option>

                                <option value="Coming Soon"
                                    <?= $availability === "Coming Soon" ? "selected" : "" ?>>
                                    Coming Soon
                                </option>

                            </select>

                        </div>


                        <div class="form-group">

                            <label>
                                Prescription
                            </label>

                            <select name="prescription">

                                <option value="Not Required"
                                    <?= $prescription === "Not Required" ? "selected" : "" ?>>
                                    Not Required
                                </option>

                                <option value="Required"
                                    <?= $prescription === "Required" ? "selected" : "" ?>>
                                    Required
                                </option>

                            </select>

                        </div>


                    </div>

                </div>


                <!-- DESCRIPTION -->

                <div class="form-section">

                    <div class="form-section-title">
                        Product Description
                    </div>


                    <div class="form-grid">


                        <div class="form-group full">

                            <label>
                                Short Description
                            </label>

                            <textarea
                                name="short_description"
                                placeholder="Short product description..."
                            ><?= htmlspecialchars($short_description) ?></textarea>

                        </div>


                        <div class="form-group full">

                            <label>
                                Full Description
                            </label>

                            <textarea
                                name="description"
                                placeholder="Complete product description..."
                            ><?= htmlspecialchars($description) ?></textarea>

                        </div>


                        <div class="form-group full">

                            <label>
                                Uses
                            </label>

                            <textarea
                                name="uses"
                                placeholder="Product uses..."
                            ><?= htmlspecialchars($uses) ?></textarea>

                        </div>


                        <div class="form-group full">

                            <label>
                                Safety Information
                            </label>

                            <textarea
                                name="safety_information"
                                placeholder="Safety information..."
                            ><?= htmlspecialchars($safety_information) ?></textarea>

                        </div>


                        <div class="form-group full">

                            <label>
                                Storage Information
                            </label>

                            <textarea
                                name="storage_information"
                                placeholder="Storage information..."
                            ><?= htmlspecialchars($storage_information) ?></textarea>

                        </div>


                    </div>

                </div>


                <!-- STATUS -->

                <div class="form-section">

                    <div class="form-section-title">
                        Status
                    </div>


                    <div class="form-grid">

                        <div class="form-group">

                            <label>
                                Product Status
                            </label>

                            <select name="status">

                                <option value="1"
                                    <?= $status == 1 ? "selected" : "" ?>>
                                    Active
                                </option>

                                <option value="0"
                                    <?= $status == 0 ? "selected" : "" ?>>
                                    Inactive
                                </option>

                            </select>

                        </div>

                    </div>

                </div>


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
                        class="save-btn"
                    >
                        Save Product
                    </button>

                </div>


            </form>

        </div>

    </section>

</main>


<?php require_once "../includes/footer.php"; ?>