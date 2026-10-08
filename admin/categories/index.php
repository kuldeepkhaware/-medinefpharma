<?php

/* =========================================================
   CATEGORY MODULE - AUTH + DATABASE
   ========================================================= */

session_start();

/* Check Admin Login */
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit;
}

/* Database Connection */
require_once "../../config/database.php";

/* Logged-in Admin */
$adminName = $_SESSION['admin_name'] ?? 'Admin';

/* Page Settings */
$pageTitle = "Categories";
$breadcrumb = "Medinef Pharma / Categories";
$activeMenu = "categories";

/* Get Categories */
try {

    $stmt = $conn->query("
        SELECT
            id,
            name,
            slug,
            image,
            description,
            status,
            created_at
        FROM categories
        ORDER BY id DESC
    ");

    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $categories = [];

    $dbError = "Unable to load categories.";

}

/* Common Admin UI */
require_once "../includes/header.php";
require_once "../includes/sidebar.php";

?>

<main class="main">

    <header class="topbar">

        <div class="page-title">
            <h2>Categories</h2>

            <span>
                Medinef Pharma / Categories
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


    <section class="content">

        <div class="category-page-header">

            <div>
                <h1>Categories</h1>

                <p>
                    Manage your Medinef Pharma product categories
                </p>
            </div>

            <a
                href="add.php"
                class="add-btn"
            >
                + Add Category
            </a>

        </div>


        <?php if (isset($dbError)): ?>

            <div class="error-message">
                <?= htmlspecialchars($dbError) ?>
            </div>

        <?php endif; ?>


        <?php if (isset($_GET['success'])): ?>

            <div class="success-message">

                <?php

                if ($_GET['success'] === 'added') {
                    echo "Category added successfully.";
                }

                elseif ($_GET['success'] === 'updated') {
                    echo "Category updated successfully.";
                }

                elseif ($_GET['success'] === 'deleted') {
                    echo "Category deleted successfully.";
                }

                ?>

            </div>

        <?php endif; ?>


        <div class="category-card">

            <?php if (count($categories) > 0): ?>

                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Image</th>

                                <th>Category Name</th>

                                <th>Slug</th>

                                <th>Status</th>

                                <th>Created</th>

                                <th>Action</th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php foreach ($categories as $key => $category): ?>

                            <tr>

                                <td>
                                    <?= $key + 1 ?>
                                </td>


                                <td>

                                    <?php if (!empty($category['image'])): ?>

                                        <img
                                            class="category-image"
                                            src="../../assets/img/category/<?= htmlspecialchars($category['image']) ?>"
                                            alt="<?= htmlspecialchars($category['name']) ?>"
                                        >

                                    <?php else: ?>

                                        <div class="no-image">
                                            —
                                        </div>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <strong class="category-name">

                                        <?= htmlspecialchars(
                                            $category['name']
                                        ) ?>

                                    </strong>

                                    <?php if (!empty($category['description'])): ?>

                                        <small>

                                            <?= htmlspecialchars(
                                                mb_strimwidth(
                                                    $category['description'],
                                                    0,
                                                    70,
                                                    '...'
                                                )
                                            ) ?>

                                        </small>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <span class="slug">

                                        <?= htmlspecialchars(
                                            $category['slug']
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <?php if ((int)$category['status'] === 1): ?>

                                        <span class="status active">
                                            Active
                                        </span>

                                    <?php else: ?>

                                        <span class="status inactive">
                                            Inactive
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?= !empty($category['created_at'])
                                        ? date(
                                            'd M Y',
                                            strtotime(
                                                $category['created_at']
                                            )
                                        )
                                        : '-'
                                    ?>

                                </td>


                                <td>

                                    <div class="actions">

                                        <a
                                            href="edit.php?id=<?= (int)$category['id'] ?>"
                                            class="edit-btn"
                                        >
                                            Edit
                                        </a>


                                        <a
                                            href="delete.php?id=<?= (int)$category['id'] ?>"
                                            class="delete-btn"
                                            onclick="return confirm('Are you sure you want to delete this category?');"
                                        >
                                            Delete
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="empty-category">

                    <div class="folder-icon">
                        📁
                    </div>

                    <h3>
                        No Categories Found
                    </h3>

                    <p>
                        Start by adding your first product category.
                    </p>

                    <a
                        href="add.php"
                        class="empty-add-btn"
                    >
                        + Add Category
                    </a>

                </div>

            <?php endif; ?>

        </div>

    </section>

</main>


<style>

.category-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
}

.category-page-header h1 {
    font-size: 32px;
    color: #282532;
    margin-bottom: 6px;
}

.category-page-header p {
    color: #858093;
    font-size: 16px;
}

.add-btn {
    background: #4B4099;
    color: #fff;
    padding: 14px 22px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    transition: .2s;
}

.add-btn:hover {
    background: #3f3589;
    transform: translateY(-1px);
}

.error-message {
    background: #fff0f0;
    color: #c33;
    border: 1px solid #ffd4d4;
    padding: 13px 16px;
    border-radius: 9px;
    margin-bottom: 20px;
    font-size: 14px;
    font-weight: 600;
}

.success-message {
    background: #ecf9f1;
    color: #218653;
    border: 1px solid #cceedd;
    padding: 13px 16px;
    border-radius: 9px;
    margin-bottom: 20px;
    font-size: 14px;
    font-weight: 600;
}

.category-card {
    background: #fff;
    border: 1px solid #eeeaf7;
    border-radius: 18px;
    box-shadow: 0 8px 25px rgba(75,64,153,.04);
    overflow: hidden;
}

.table-wrapper {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: #faf9fd;
}

th {
    text-align: left;
    padding: 17px 20px;
    color: #777285;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    white-space: nowrap;
}

td {
    padding: 16px 20px;
    border-top: 1px solid #f0eef5;
    color: #55515f;
    font-size: 13px;
    vertical-align: middle;
}

tbody tr:hover {
    background: #fcfbff;
}

.category-image {
    width: 58px;
    height: 58px;
    object-fit: cover;
    border-radius: 10px;
    border: 1px solid #eeeaf7;
}

.no-image {
    width: 58px;
    height: 58px;
    border-radius: 10px;
    background: #f5f3fb;
    color: #aaa5b9;
    display: flex;
    align-items: center;
    justify-content: center;
}

.category-name {
    display: block;
    color: #282532;
    font-size: 14px;
    margin-bottom: 4px;
}

td small {
    display: block;
    color: #9994a5;
    max-width: 220px;
}

.slug {
    background: #f5f3fb;
    color: #6257a8;
    padding: 6px 9px;
    border-radius: 6px;
    font-size: 11px;
}

.status {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

.status.active {
    background: #eaf8f0;
    color: #218653;
}

.status.inactive {
    background: #fff0f0;
    color: #c33;
}

.actions {
    display: flex;
    gap: 7px;
}

.edit-btn,
.delete-btn {
    padding: 7px 11px;
    border-radius: 7px;
    font-size: 11px;
    font-weight: 600;
}

.edit-btn {
    background: #f0eef9;
    color: #4B4099;
}

.delete-btn {
    background: #fff0f0;
    color: #d33;
}

.edit-btn:hover {
    background: #4B4099;
    color: #fff;
}

.delete-btn:hover {
    background: #d33;
    color: #fff;
}

.empty-category {
    min-height: 360px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 40px;
}

.folder-icon {
    font-size: 52px;
    margin-bottom: 15px;
}

.empty-category h3 {
    font-size: 22px;
    color: #777;
    margin-bottom: 6px;
}

.empty-category p {
    color: #999;
    margin-bottom: 20px;
}

.empty-add-btn {
    background: #4B4099;
    color: #fff;
    padding: 11px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
}

@media(max-width: 750px) {

    .category-page-header {
        align-items: flex-start;
        flex-direction: column;
        gap: 18px;
    }

    .category-page-header h1 {
        font-size: 26px;
    }

    .add-btn {
        width: 100%;
        text-align: center;
    }

}

</style>


<?php require_once "../includes/footer.php"; ?>