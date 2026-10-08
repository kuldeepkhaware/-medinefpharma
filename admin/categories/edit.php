<?php

session_start();

if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit;
}

require_once "../../config/database.php";

$adminName = $_SESSION['admin_name'] ?? 'Admin';

$pageTitle = "Edit Category";
$breadcrumb = "Medinef Pharma / Categories / Edit";
$activeMenu = "categories";

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

/* Get category */
$stmt = $conn->prepare("
    SELECT
        id,
        name,
        slug,
        image,
        description,
        status
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

$error = "";

$name = $category["name"];
$slug = $category["slug"];
$description = $category["description"];
$status = (int)$category["status"];
$currentImage = $category["image"];

function makeSlug($text) {
    $text = trim($text);
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $slug = trim($_POST["slug"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $status = isset($_POST["status"]) ? (int)$_POST["status"] : 1;

    if ($name === "") {
        $error = "Please enter category name.";
    }

    if ($slug === "" && $name !== "") {
        $slug = makeSlug($name);
    } else {
        $slug = makeSlug($slug);
    }

    if ($error === "" && $slug === "") {
        $error = "Please enter a valid slug.";
    }

    /* Duplicate check excluding current record */
    if ($error === "") {

        $check = $conn->prepare("
            SELECT id
            FROM categories
            WHERE (name = :name OR slug = :slug)
              AND id != :id
            LIMIT 1
        ");

        $check->execute([
            ":name" => $name,
            ":slug" => $slug,
            ":id" => $id
        ]);

        if ($check->fetch()) {
            $error = "Category name or slug already exists.";
        }
    }

    $newImage = $currentImage;

    /* New image */
    if ($error === "" && !empty($_FILES["image"]["name"])) {

        $uploadDir = "../../assets/img/category/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $file = $_FILES["image"];

        if ($file["error"] !== UPLOAD_ERR_OK) {
            $error = "Unable to upload image.";
        } else {

            $allowed = [
                "jpg"  => "image/jpeg",
                "jpeg" => "image/jpeg",
                "png"  => "image/png",
                "webp" => "image/webp"
            ];

            $extension = strtolower(
                pathinfo($file["name"], PATHINFO_EXTENSION)
            );

            $mime = mime_content_type($file["tmp_name"]);

            if (!isset($allowed[$extension]) || $allowed[$extension] !== $mime) {
                $error = "Only JPG, JPEG, PNG and WEBP images are allowed.";
            } elseif ($file["size"] > 5 * 1024 * 1024) {
                $error = "Image size must be less than 5 MB.";
            } else {

                $newImage =
                    $slug . "-" .
                    time() . "." .
                    $extension;

                if (!move_uploaded_file(
                    $file["tmp_name"],
                    $uploadDir . $newImage
                )) {
                    $error = "Image upload failed.";
                    $newImage = $currentImage;
                }
            }
        }
    }

    /* Update */
    if ($error === "") {

        $update = $conn->prepare("
            UPDATE categories
            SET
                name = :name,
                slug = :slug,
                image = :image,
                description = :description,
                status = :status,
                updated_at = NOW()
            WHERE id = :id
        ");

        $update->execute([
            ":name" => $name,
            ":slug" => $slug,
            ":image" => $newImage,
            ":description" => $description,
            ":status" => $status,
            ":id" => $id
        ]);

        /* Delete old image only after successful update */
        if (
            $newImage !== $currentImage &&
            !empty($currentImage)
        ) {

            $oldPath =
                "../../assets/img/category/" .
                basename($currentImage);

            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        header("Location: index.php?success=updated");
        exit;
    }
}

require_once "../includes/header.php";
require_once "../includes/sidebar.php";

?>

<main class="main">

    <header class="topbar">

        <div class="page-title">
            <h2>Edit Category</h2>
            <span>Medinef Pharma / Categories / Edit</span>
        </div>

        <div class="top-right">

            <div class="notification">♧</div>

            <div class="profile">

                <div class="profile-avatar">
                    <?= strtoupper(substr($adminName, 0, 1)) ?>
                </div>

                <div class="profile-info">
                    <strong><?= htmlspecialchars($adminName) ?></strong>
                    <span>Administrator</span>
                </div>

            </div>

        </div>

    </header>

    <section class="content">

        <div class="category-page-header">

            <div>
                <h1>Edit Category</h1>
                <p>Update your Medinef Pharma product category</p>
            </div>

           <a href="index.php" class="back-btn">
                ← Back to Categories
            </a>

        </div>

        <?php if ($error !== ""): ?>

            <div class="form-message error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>

        <div class="category-form-card">

            <form method="POST" enctype="multipart/form-data">

                <div class="category-form-grid">

                    <div class="form-group">

                        <label>Category Name *</label>

                        <input
                            type="text"
                            name="name"
                            value="<?= htmlspecialchars($name) ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>Slug</label>

                        <input
                            type="text"
                            name="slug"
                            value="<?= htmlspecialchars($slug) ?>"
                        >

                    </div>


                    <div class="form-group">

                        <label>Status *</label>

                        <select name="status">

                            <option value="1" <?= $status === 1 ? 'selected' : '' ?>>
                                Active
                            </option>

                            <option value="0" <?= $status === 0 ? 'selected' : '' ?>>
                                Inactive
                            </option>

                        </select>

                    </div>


                    <div class="form-group">

                        <label>Replace Image</label>

                        <input
                            type="file"
                            name="image"
                            accept=".jpg,.jpeg,.png,.webp"
                        >

                        <span class="help-text">
                            Leave blank to keep the current image.
                        </span>

                        <?php if (!empty($currentImage)): ?>

                            <img
                                src="../../assets/img/category/<?= htmlspecialchars($currentImage) ?>"
                                class="image-preview"
                                alt="Current category image"
                            >

                        <?php endif; ?>

                    </div>


                    <div class="form-group full">

                        <label>Description</label>

                        <textarea
                            name="description"
                        ><?= htmlspecialchars($description) ?></textarea>

                    </div>

                </div>


                <div class="form-actions">

                    <button
                        type="submit"
                        class="primary-btn"
                    >
                        Update Category
                    </button>

                    <a
                        href="index.php"
                        class="secondary-btn"
                    >
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </section>

</main>


<style>
.category-form-card{
    background:#fff;
    border:1px solid #eeeaf7;
    border-radius:18px;
    padding:32px;
    box-shadow:0 8px 25px rgba(75,64,153,.04);
    max-width:900px;
}
.category-form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:22px;
}
.form-group{
    display:flex;
    flex-direction:column;
    gap:8px;
}
.form-group.full{
    grid-column:1 / -1;
}
.form-group label{
    color:#282532;
    font-size:14px;
    font-weight:600;
}
.form-group input,
.form-group textarea,
.form-group select{
    width:100%;
    border:1px solid #e2dfeb;
    border-radius:9px;
    padding:12px 14px;
    font-family:inherit;
    font-size:14px;
    outline:none;
    background:#fff;
    color:#333;
}
.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus{
    border-color:#4B4099;
    box-shadow:0 0 0 3px rgba(75,64,153,.08);
}
.form-group textarea{
    min-height:130px;
    resize:vertical;
}
.help-text{
    color:#9994a5;
    font-size:12px;
}
.image-preview{
    width:110px;
    height:110px;
    object-fit:cover;
    border-radius:12px;
    border:1px solid #eeeaf7;
    margin-top:5px;
}
.form-actions{
    display:flex;
    gap:12px;
    margin-top:28px;
    padding-top:22px;
    border-top:1px solid #eeeaf7;
}
.primary-btn,
.secondary-btn{
    border:0;
    text-decoration:none;
    padding:12px 20px;
    border-radius:9px;
    font-size:14px;
    font-weight:600;
    cursor:pointer;
}
.primary-btn{
    background:#4B4099;
    color:#fff;
}
.primary-btn:hover{background:#3f3589;}
.secondary-btn{
    background:#f1eff9;
    color:#4B4099;
}
.form-message{
    padding:13px 16px;
    border-radius:9px;
    margin-bottom:20px;
    font-size:14px;
    font-weight:600;
}
.form-message.error{
    background:#fff0f0;
    color:#c33;
    border:1px solid #ffd4d4;
}
@media(max-width:700px){
    .category-form-card{padding:22px;}
    .category-form-grid{grid-template-columns:1fr;}
    .form-group.full{grid-column:auto;}
    .form-actions{flex-direction:column;}
}
</style>


<?php require_once "../includes/footer.php"; ?>
