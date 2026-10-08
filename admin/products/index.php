<?php

session_start();

if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit;
}

$adminName = $_SESSION['admin_name'] ?? 'Admin';

$pageTitle  = "Products";
$breadcrumb = "Medinef Pharma / Products";
$activeMenu = "products";

require_once "../includes/header.php";
require_once "../includes/sidebar.php";
require_once "../../config/database.php";


// =====================================================
// SEARCH
// =====================================================

$search = trim($_GET['search'] ?? '');


// =====================================================
// FETCH PRODUCTS
// =====================================================

if ($search !== '') {

    $stmt = $conn->prepare("
        SELECT
            p.*,
            c.name AS category_name
        FROM products p
        LEFT JOIN categories c
            ON c.id = p.category_id
        WHERE
            p.name LIKE :search
            OR p.slug LIKE :search
            OR c.name LIKE :search
        ORDER BY p.id DESC
    ");

    $stmt->execute([
        ':search' => '%' . $search . '%'
    ]);

} else {

    $stmt = $conn->prepare("
        SELECT
            p.*,
            c.name AS category_name
        FROM products p
        LEFT JOIN categories c
            ON c.id = p.category_id
        ORDER BY p.id DESC
    ");

    $stmt->execute();
}


$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalProducts = count($products);

?>

<style>

/* =====================================================
   PRODUCTS PAGE
===================================================== */

.products-page {
    padding: 30px 40px;
}


/* =====================================================
   PAGE HEADER
===================================================== */

.products-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 28px;
}


.products-page-title h1 {
    margin: 0 0 7px;
    font-size: 30px;
    font-weight: 700;
    color: #17152a;
}


.products-page-title p {
    margin: 0;
    color: #8c879d;
    font-size: 15px;
}


/* =====================================================
   ADD PRODUCT BUTTON
===================================================== */

.add-product-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;

    min-width: 155px;
    height: 48px;

    padding: 0 20px;

    background: #4B4099;
    color: #ffffff;

    border-radius: 9px;

    text-decoration: none;

    font-size: 14px;
    font-weight: 600;

    transition: .2s;
}


.add-product-btn:hover {
    background: #3f3589;
    color: #ffffff;
    transform: translateY(-1px);
}


/* =====================================================
   SEARCH TOOLBAR
===================================================== */

.products-toolbar {
    background: #ffffff;

    border: 1px solid #eeeaf7;

    border-radius: 14px;

    padding: 18px;

    margin-bottom: 20px;

    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 20px;
}


.product-search {
    display: flex;
    align-items: center;

    width: 100%;
    max-width: 650px;

    gap: 10px;
}


.product-search input {
    width: 100%;
    height: 44px;

    border: 1px solid #e2dfed;

    border-radius: 8px;

    padding: 0 14px;

    outline: none;

    font-size: 14px;

    color: #333;

    background: #ffffff;
}


.product-search input:focus {
    border-color: #4B4099;
}


.search-btn {
    height: 44px;

    padding: 0 20px;

    border: 0;

    border-radius: 8px;

    background: #4B4099;

    color: #ffffff;

    cursor: pointer;

    font-size: 14px;

    font-weight: 600;
}


.search-btn:hover {
    background: #3f3589;
}


.clear-search {
    height: 44px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 0 15px;

    border-radius: 8px;

    background: #f3f1fb;

    color: #4B4099;

    text-decoration: none;

    font-size: 14px;

    font-weight: 600;

    white-space: nowrap;
}


.clear-search:hover {
    background: #e9e6f7;
}


.product-count {
    color: #777287;

    font-size: 14px;

    white-space: nowrap;
}


/* =====================================================
   TABLE
===================================================== */

.products-table-wrapper {
    background: #ffffff;

    border: 1px solid #eeeaf7;

    border-radius: 14px;

    overflow: hidden;
}


.products-table {
    width: 100%;

    border-collapse: collapse;
}


.products-table thead {
    background: #f8f7fc;
}


.products-table th {
    padding: 15px 18px;

    text-align: left;

    color: #777287;

    font-size: 12px;

    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: .4px;

    border-bottom: 1px solid #eeeaf7;

    white-space: nowrap;
}


.products-table td {
    padding: 16px 18px;

    color: #444052;

    font-size: 14px;

    border-bottom: 1px solid #f0eef6;

    vertical-align: middle;
}


.products-table tbody tr:last-child td {
    border-bottom: 0;
}


.products-table tbody tr:hover {
    background: #fbfaff;
}


/* =====================================================
   PRODUCT IMAGE
===================================================== */

.product-image {
    width: 58px;
    height: 58px;

    border-radius: 9px;

    border: 1px solid #eeeaf7;

    background: #ffffff;

    display: flex;

    align-items: center;
    justify-content: center;

    overflow: hidden;
}


.product-image img {
    width: 100%;
    height: 100%;

    object-fit: contain;

    display: block;
}


.no-image {
    color: #aaa5b9;

    font-size: 11px;

    text-align: center;
}


/* =====================================================
   PRODUCT NAME
===================================================== */

.product-name {
    font-weight: 600;

    color: #252238;

    margin-bottom: 4px;
}


.product-slug {
    color: #9993a8;

    font-size: 12px;
}


/* =====================================================
   CATEGORY
===================================================== */

.category-badge {
    display: inline-block;

    background: #f1effb;

    color: #4B4099;

    padding: 6px 10px;

    border-radius: 6px;

    font-size: 12px;

    font-weight: 600;

    white-space: nowrap;
}


/* =====================================================
   PRODUCT TYPE
===================================================== */

.product-type {
    color: #5f5a6d;

    white-space: nowrap;
}


/* =====================================================
   AVAILABILITY
===================================================== */

.availability {
    color: #5f5a6d;

    font-size: 13px;

    white-space: nowrap;
}


/* =====================================================
   STATUS
===================================================== */

.status-badge {
    display: inline-block;

    padding: 6px 10px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 600;

    white-space: nowrap;
}


.status-active {
    background: #eaf8ef;

    color: #218642;
}


.status-inactive {
    background: #fff0f0;

    color: #c53b3b;
}


/* =====================================================
   ACTION BUTTONS
===================================================== */

.action-buttons {
    display: flex;

    align-items: center;

    gap: 7px;
}


.action-btn {
    width: 36px;
    height: 36px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    border-radius: 7px;

    text-decoration: none;

    font-size: 15px;

    transition: .2s;
}


.edit-btn {
    background: #f0effa;

    color: #4B4099;
}


.edit-btn:hover {
    background: #4B4099;

    color: #ffffff;
}


.delete-btn {
    background: #fff1f1;

    color: #d33b3b;
}


.delete-btn:hover {
    background: #d33b3b;

    color: #ffffff;
}


/* =====================================================
   EMPTY PRODUCTS
===================================================== */

.empty-products {
    padding: 80px 20px;

    text-align: center;
}


.empty-icon {
    width: 65px;
    height: 65px;

    margin: 0 auto 15px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: #f1effb;

    color: #4B4099;

    font-size: 25px;
}


.empty-products h3 {
    margin: 0 0 7px;

    color: #514d5e;

    font-size: 18px;
}


.empty-products p {
    margin: 0 0 20px;

    color: #9993a8;

    font-size: 14px;
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media(max-width: 1000px) {

    .products-page {
        padding: 25px 20px;
    }

    .products-page-header {
        align-items: flex-start;

        flex-direction: column;
    }

    .products-toolbar {
        align-items: flex-start;

        flex-direction: column;
    }

    .product-search {
        max-width: 100%;
    }

    .products-table-wrapper {
        overflow-x: auto;
    }

    .products-table {
        min-width: 1000px;
    }

}

</style>


<!-- =====================================================
     MAIN
===================================================== -->

<main class="main">


    <!-- =================================================
         TOPBAR
    ================================================== -->

    <header class="topbar">

        <div class="page-title">

            <h2>
                Products
            </h2>

            <span>
                Medinef Pharma / Products
            </span>

        </div>


        <div class="top-right">

            <div class="notification">
                ♧
            </div>


            <div class="profile">

                <div class="profile-avatar">

                    <?= strtoupper(
                        substr($adminName, 0, 1)
                    ) ?>

                </div>


                <div class="profile-info">

                    <strong>
                        <?= htmlspecialchars(
                            $adminName,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </strong>

                    <span>
                        Administrator
                    </span>

                </div>

            </div>

        </div>

    </header>


    <!-- =================================================
         CONTENT
    ================================================== -->

    <section class="content">


        <div class="products-page">


            <!-- =========================================
                 PAGE HEADER
            ========================================== -->

            <div class="products-page-header">


                <div class="products-page-title">

                    <h1>
                        Products
                    </h1>

                    <p>
                        Manage your Medinef Pharma products
                    </p>

                </div>


                <a
                    href="add.php"
                    class="add-product-btn"
                >
                    + Add Product
                </a>


            </div>


            <!-- =========================================
                 SEARCH
            ========================================== -->

            <div class="products-toolbar">


                <form
                    method="GET"
                    action="index.php"
                    class="product-search"
                >


                    <input
                        type="text"
                        name="search"
                        value="<?= htmlspecialchars(
                            $search,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        placeholder="Search product, slug or category..."
                    >


                    <button
                        type="submit"
                        class="search-btn"
                    >
                        Search
                    </button>


                    <?php if ($search !== ''): ?>

                        <a
                            href="index.php"
                            class="clear-search"
                        >
                            Clear
                        </a>

                    <?php endif; ?>


                </form>


                <div class="product-count">

                    <?= $totalProducts ?>

                    <?= $totalProducts === 1
                        ? 'Product'
                        : 'Products'
                    ?>

                </div>


            </div>


            <!-- =========================================
                 PRODUCTS TABLE
            ========================================== -->

        <div class="products-table-wrapper">

    <?php if (!empty($products)): ?>

        <table class="products-table">

            <thead>
                <tr>

                    <th>
                        Image
                    </th>

                    <th>
                        Product
                    </th>

                    <th>
                        Category
                    </th>

                    <th>
                        Product Type
                    </th>

                    <th>
                        Availability
                    </th>

                    <th>
                        Status
                    </th>

                    <th>
                        Actions
                    </th>

                </tr>
            </thead>


            <tbody>

            <?php foreach ($products as $product): ?>

                <?php

                /*
                ==========================================================
                PRODUCT IMAGE PATH
                ==========================================================

                Actual folder:

                C:/xampp/htdocs/medinefpharma.online/
                uploads/products/

                Example database value:

                product-31790669073.jpeg

                Browser URL:

                /medinefpharma.online/uploads/products/
                product-31790669073.jpeg
                */


                $imageName = trim(
                    (string)($product['image'] ?? '')
                );

                $imageUrl = '';


                if ($imageName !== '') {

                    /*
                    ------------------------------------------------------
                    EXTERNAL IMAGE
                    ------------------------------------------------------
                    */

                    if (
                        strpos($imageName, 'http://') === 0 ||
                        strpos($imageName, 'https://') === 0
                    ) {

                        $imageUrl = $imageName;

                    } else {

                        /*
                        --------------------------------------------------
                        GET ONLY FILE NAME
                        --------------------------------------------------
                        */

                        $fileName = basename(
                            str_replace(
                                '\\',
                                '/',
                                $imageName
                            )
                        );


                        /*
                        --------------------------------------------------
                        PROJECT ROOT
                        --------------------------------------------------

                        If file is:

                        /admin/product/page.php

                        dirname(__DIR__, 2)
                        = medinefpharma.online
                        */

                        $projectRoot = dirname(__DIR__, 2);


                        /*
                        --------------------------------------------------
                        ACTUAL PRODUCT UPLOAD FOLDER
                        --------------------------------------------------
                        */

                        $productDiskPath =
                            $projectRoot .
                            DIRECTORY_SEPARATOR .
                            'uploads' .
                            DIRECTORY_SEPARATOR .
                            'products' .
                            DIRECTORY_SEPARATOR .
                            $fileName;


                        /*
                        --------------------------------------------------
                        IMAGE EXISTS
                        --------------------------------------------------
                        */

                        if (is_file($productDiskPath)) {

                            $imageUrl =
                                '/medinefpharma.online/' .
                                'uploads/products/' .
                                rawurlencode($fileName);

                        }


                        /*
                        --------------------------------------------------
                        DATABASE ALREADY HAS uploads/products/
                        --------------------------------------------------
                        */

                        elseif (
                            strpos(
                                ltrim($imageName, '/'),
                                'uploads/products/'
                            ) === 0
                        ) {

                            $imageUrl =
                                '/medinefpharma.online/' .
                                ltrim($imageName, '/');

                        }


                        /*
                        --------------------------------------------------
                        DATABASE HAS FULL PROJECT URL
                        --------------------------------------------------
                        */

                        elseif (
                            strpos(
                                $imageName,
                                '/medinefpharma.online/'
                            ) === 0
                        ) {

                            $imageUrl = $imageName;

                        }


                        /*
                        --------------------------------------------------
                        DATABASE HAS assets/ PATH
                        --------------------------------------------------
                        */

                        elseif (
                            strpos(
                                ltrim($imageName, '/'),
                                'assets/'
                            ) === 0
                        ) {

                            $imageUrl =
                                '/medinefpharma.online/' .
                                ltrim($imageName, '/');

                        }


                        /*
                        --------------------------------------------------
                        FINAL FALLBACK
                        --------------------------------------------------

                        If DB only contains filename,
                        use uploads/products/
                        --------------------------------------------------
                        */

                        else {

                            $imageUrl =
                                '/medinefpharma.online/' .
                                'uploads/products/' .
                                rawurlencode($fileName);

                        }

                    }

                }


                /*
                ----------------------------------------------------------
                DEFAULT IMAGE
                ----------------------------------------------------------
                */

                if ($imageUrl === '') {

                    $imageUrl =
                        '/medinefpharma.online/' .
                        'assets/img/products/01.png';

                }

                ?>


                <tr>


                    <!-- =================================================
                         IMAGE
                    ================================================== -->

                    <td>

                        <div class="product-image">

                            <img
                                src="<?= htmlspecialchars(
                                    $imageUrl,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                alt="<?= htmlspecialchars(
                                    $product['name'] ?? 'Product',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                loading="lazy"
                                onerror="
                                    this.onerror=null;
                                    this.src='/medinefpharma.online/assets/img/products/01.png';
                                "
                            >

                        </div>

                    </td>


                    <!-- =================================================
                         PRODUCT
                    ================================================== -->

                    <td>

                        <div class="product-name">

                            <?= htmlspecialchars(
                                $product['name'] ?? 'N/A',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </div>


                        <div class="product-slug">

                            <?= htmlspecialchars(
                                $product['slug'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </div>

                    </td>


                    <!-- =================================================
                         CATEGORY
                    ================================================== -->

                    <td>

                        <?php if (
                            !empty($product['category_name'])
                        ): ?>

                            <span class="category-badge">

                                <?= htmlspecialchars(
                                    $product['category_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </span>

                        <?php else: ?>

                            <span>—</span>

                        <?php endif; ?>

                    </td>


                    <!-- =================================================
                         PRODUCT TYPE
                    ================================================== -->

                    <td>

                        <span class="product-type">

                            <?= !empty(
                                $product['product_type']
                            )
                                ? htmlspecialchars(
                                    $product['product_type'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                )
                                : '—'
                            ?>

                        </span>

                    </td>


                    <!-- =================================================
                         AVAILABILITY
                    ================================================== -->

                    <td>

                        <span class="availability">

                            <?= !empty(
                                $product['availability']
                            )
                                ? htmlspecialchars(
                                    $product['availability'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                )
                                : '—'
                            ?>

                        </span>

                    </td>


                    <!-- =================================================
                         STATUS
                    ================================================== -->

                    <td>

                        <?php if (
                            (int)($product['status'] ?? 0) === 1
                        ): ?>

                            <span class="
                                status-badge
                                status-active
                            ">
                                Active
                            </span>

                        <?php else: ?>

                            <span class="
                                status-badge
                                status-inactive
                            ">
                                Inactive
                            </span>

                        <?php endif; ?>

                    </td>


                    <!-- =================================================
                         ACTIONS
                    ================================================== -->

                    <td>

                        <div class="action-buttons">


                            <!-- EDIT -->

                            <a
                                href="edit.php?id=<?= (int)($product['id'] ?? 0) ?>"
                                class="
                                    action-btn
                                    edit-btn
                                "
                                title="Edit Product"
                            >
                                ✎
                            </a>


                            <!-- DELETE -->

                            <a
                                href="delete.php?id=<?= (int)($product['id'] ?? 0) ?>"
                                class="
                                    action-btn
                                    delete-btn
                                "
                                title="Delete Product"
                                onclick="
                                    return confirm(
                                        'Are you sure you want to delete this product?'
                                    );
                                "
                            >
                                🗑
                            </a>


                        </div>

                    </td>


                </tr>


            <?php endforeach; ?>


            </tbody>

        </table>


    <?php else: ?>


        <!-- =========================================================
             EMPTY STATE
        ========================================================== -->

        <div class="empty-products">

            <div class="empty-icon">
                ▣
            </div>


            <h3>
                No Products Found
            </h3>


            <p>
                Start by adding your first
                Medinef Pharma product.
            </p>


            <a
                href="add.php"
                class="add-product-btn"
            >
                + Add Product
            </a>

        </div>


    <?php endif; ?>


</div>


        </div>


    </section>


</main>
f

<?php

require_once "../includes/footer.php";

?>