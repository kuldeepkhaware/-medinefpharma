<?php

require_once __DIR__ . '/config/database.php';


/*
|--------------------------------------------------------------------------
| FRONTEND IMAGE URL
|--------------------------------------------------------------------------
*/

function frontendImageUrl($image, $folder = 'products')
{
    $image = trim((string)$image);

    if ($image === '') {
        return '';
    }


    /*
    |--------------------------------------------------------------------------
    | External Image URL
    |--------------------------------------------------------------------------
    */

    if (preg_match('/^(https?:)?\/\//i', $image)) {
        return $image;
    }


    $image = ltrim($image, '/');


    /*
    |--------------------------------------------------------------------------
    | Already Assets Path
    |--------------------------------------------------------------------------
    */

    if (strpos($image, 'assets/') === 0) {
        return $image;
    }


    /*
    |--------------------------------------------------------------------------
    | Already Uploads Path
    |--------------------------------------------------------------------------
    */

    if (strpos($image, 'uploads/') === 0) {
        return $image;
    }


    /*
    |--------------------------------------------------------------------------
    | Product Images
    |--------------------------------------------------------------------------
    |
    | Database contains filename only.
    |
    | Example:
    | product-3-1790669073.jpeg
    | 01.png
    |
    */

    if ($folder === 'products') {
        return 'uploads/products/' . basename($image);
    }


    /*
    |--------------------------------------------------------------------------
    | Category Images
    |--------------------------------------------------------------------------
    */

    if ($folder === 'categories') {

        $categoryPaths = [

            'uploads/categories/' . basename($image),

            'uploads/category/' . basename($image),

            'assets/img/category/' . basename($image),

            'assets/img/categories/' . basename($image),

            'assets/img/products/' . basename($image)

        ];


        foreach ($categoryPaths as $path) {

            if (
                file_exists(
                    __DIR__ . '/' . $path
                )
            ) {
                return $path;
            }
        }


        return '';
    }


    return 'assets/img/' .
        $folder .
        '/' .
        basename($image);
}


/*
|--------------------------------------------------------------------------
| PRODUCT IMAGE URL
|--------------------------------------------------------------------------
*/

function productImageUrl($image)
{
    $path = frontendImageUrl(
        $image,
        'products'
    );


    if ($path === '') {
        return 'assets/img/products/01.png';
    }


    return $path;
}


/*
|--------------------------------------------------------------------------
| CATEGORY IMAGE URL
|--------------------------------------------------------------------------
*/

function categoryImageUrl($image, $slug = '')
{
    $image = trim((string)$image);
    $slug  = trim((string)$slug);


    /*
    |--------------------------------------------------------------------------
    | Database Image
    |--------------------------------------------------------------------------
    */

    if ($image !== '') {

        $path = frontendImageUrl(
            $image,
            'categories'
        );


        if ($path !== '') {
            return $path;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Category Icons
    |--------------------------------------------------------------------------
    */

    $icons = [

        'medicine' =>
            'assets/img/icon/medicine.svg',

        'healthcare' =>
            'assets/img/icon/health-care.svg',

        'beauty-care' =>
            'assets/img/icon/beauty-care.svg',

        'sexual-wellness' =>
            'assets/img/icon/sexual.svg',

        'fitness' =>
            'assets/img/icon/fitness.svg',

        'lab-test' =>
            'assets/img/icon/lab-test.svg',

        'baby-mom-care' =>
            'assets/img/icon/baby-mom-care.svg',

        'vitamins-supplement' =>
            'assets/img/icon/supplements.svg',

        'supplement' =>
            'assets/img/icon/supplements.svg',

        'food-nutrition' =>
            'assets/img/icon/food-nutrition.svg',

        'medical-equipments' =>
            'assets/img/icon/medical-equipements.svg',

        'equipments' =>
            'assets/img/icon/medical-equipements.svg',

        'medical-supplies' =>
            'assets/img/icon/medical-supplies.svg',

        'pet-care' =>
            'assets/img/icon/pet-care.svg'

    ];


    $slugKey = strtolower($slug);


    if (
        $slugKey !== '' &&
        isset($icons[$slugKey])
    ) {
        return $icons[$slugKey];
    }


    /*
    |--------------------------------------------------------------------------
    | Default Icon
    |--------------------------------------------------------------------------
    */

    return 'assets/img/icon/medicine.svg';
}


/*
|--------------------------------------------------------------------------
| SELECTED CATEGORY ID
|--------------------------------------------------------------------------
|
| Example:
| products.php?category_id=3
|
*/

$selectedCategoryId = isset($_GET['category_id'])
    ? (int)$_GET['category_id']
    : 0;


/*
|--------------------------------------------------------------------------
| GET ACTIVE CATEGORIES
|--------------------------------------------------------------------------
*/

$categoryStmt = $conn->prepare("
    SELECT
        id,
        name,
        slug,
        image,
        description
    FROM categories
    WHERE status = 1
    ORDER BY id ASC
");

$categoryStmt->execute();

$frontendCategories =
    $categoryStmt->fetchAll();


/*
|--------------------------------------------------------------------------
| GET SELECTED CATEGORY
|--------------------------------------------------------------------------
*/

$selectedCategory = null;


if ($selectedCategoryId > 0) {

    $selectedCategoryStmt = $conn->prepare("
        SELECT
            id,
            name,
            slug,
            image,
            description
        FROM categories
        WHERE id = ?
          AND status = 1
        LIMIT 1
    ");

    $selectedCategoryStmt->execute([
        $selectedCategoryId
    ]);

    $selectedCategory =
        $selectedCategoryStmt->fetch();


    /*
    |--------------------------------------------------------------------------
    | Invalid Category
    |--------------------------------------------------------------------------
    */

    if (!$selectedCategory) {
        $selectedCategoryId = 0;
    }
}


/*
|--------------------------------------------------------------------------
| GET PRODUCTS
|--------------------------------------------------------------------------
|
| CATEGORY SELECTED:
|     Get ALL products of that category.
|
| NO CATEGORY:
|     Get latest 12 products.
|
*/

if ($selectedCategoryId > 0) {

    $productStmt = $conn->prepare("
        SELECT
            p.*,
            c.name AS category_name,
            c.slug AS category_slug
        FROM products p
        LEFT JOIN categories c
            ON c.id = p.category_id
        WHERE p.category_id = ?
        ORDER BY p.id DESC
    ");

    $productStmt->execute([
        $selectedCategoryId
    ]);

} else {

    $productStmt = $conn->prepare("
        SELECT
            p.*,
            c.name AS category_name,
            c.slug AS category_slug
        FROM products p
        LEFT JOIN categories c
            ON c.id = p.category_id
        ORDER BY p.id DESC
        LIMIT 12
    ");

    $productStmt->execute();
}


$frontendProducts =
    $productStmt->fetchAll();


/*
|--------------------------------------------------------------------------
| PRODUCT LIST
|--------------------------------------------------------------------------
|
| Category selected:
| ALL products of selected category.
|
| No category:
| Latest 12 products.
|
*/

if ($selectedCategoryId > 0) {

    $productListStmt = $conn->prepare("
        SELECT
            p.*,
            c.name AS category_name,
            c.slug AS category_slug
        FROM products p
        LEFT JOIN categories c
            ON c.id = p.category_id
        WHERE p.category_id = ?
        ORDER BY p.id DESC
    ");

    $productListStmt->execute([
        $selectedCategoryId
    ]);

} else {

    $productListStmt = $conn->prepare("
        SELECT
            p.*,
            c.name AS category_name,
            c.slug AS category_slug
        FROM products p
        LEFT JOIN categories c
            ON c.id = p.category_id
        ORDER BY p.id DESC
        LIMIT 12
    ");

    $productListStmt->execute();
}


$productList =
    $productListStmt->fetchAll();


/*
|--------------------------------------------------------------------------
| POPULAR PRODUCTS BY CATEGORY
|--------------------------------------------------------------------------
*/

$popularByCategory = [];


$popularCategoryStmt = $conn->prepare("
    SELECT
        id,
        name,
        slug
    FROM categories
    WHERE status = 1
      AND slug IN (
          'tablets',
          'capsules',
          'syrups',
          'injections'
      )
    ORDER BY FIELD(
        slug,
        'tablets',
        'capsules',
        'syrups',
        'injections'
    )
");

$popularCategoryStmt->execute();

$popularCategories =
    $popularCategoryStmt->fetchAll();


foreach (
    $popularCategories as $popularCategory
) {

    $stmt = $conn->prepare("
        SELECT
            p.*,
            c.name AS category_name
        FROM products p
        LEFT JOIN categories c
            ON c.id = p.category_id
        WHERE p.category_id = ?
        ORDER BY p.id DESC
        LIMIT 4
    ");

    $stmt->execute([
        (int)$popularCategory['id']
    ]);


    $popularByCategory[
        $popularCategory['slug']
    ] = $stmt->fetchAll();
}


/*
|--------------------------------------------------------------------------
| PAGE CATEGORY TITLE
|--------------------------------------------------------------------------
*/

$pageCategoryTitle = $selectedCategory
    ? $selectedCategory['name']
    : 'All Products';


/*
|--------------------------------------------------------------------------
| INCLUDE HEADER
|--------------------------------------------------------------------------
*/

include 'includes/header.php';

?>




    <main class="main">

        <!-- breadcrumb -->
        <div class="site-breadcrumb">
            <div class="site-breadcrumb-bg" style="background: url(assets/img/breadcrumb/01.jpg)"></div>
            <div class="container">
                <div class="site-breadcrumb-wrap">
                    <h4 class="breadcrumb-title">Category Two</h4>
                    <ul class="breadcrumb-menu">
                        <li><a href="index-2.html"><i class="far fa-home"></i> Home</a></li>
                        <li class="active">Category Two</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- breadcrumb end -->


        <!-- category area -->
      <!-- category area -->
<div class="category-area2 py-120">

    <div class="container">

        <!-- Section Heading -->
        <div class="row">

            <div class="col-lg-6 mx-auto">

                <div class="site-heading text-center">

                    <span class="site-title-tagline">
                        Our Category
                    </span>

                    <h2 class="site-title">
                        Our Popular <span>Category</span>
                    </h2>

                </div>

            </div>

        </div>
        <!-- Section Heading End -->


        <!-- Dynamic Categories -->
        <div class="row g-3">

            <?php if (!empty($frontendCategories)): ?>

                <?php foreach ($frontendCategories as $category): ?>

                    <?php

                    /* =========================================
                       CATEGORY DATA
                    ========================================= */

                    $categoryId = (int)($category['id'] ?? 0);

                    $categoryName = trim(
                        (string)($category['name'] ?? '')
                    );

                    $categorySlug = trim(
                        (string)($category['slug'] ?? '')
                    );

                    $categoryImage = trim(
                        (string)($category['image'] ?? '')
                    );


                    /* =========================================
                       CATEGORY ICON
                    ========================================= */

                    /*
                     * Agar database mein category image nahi hai,
                     * to slug ke according template icon use hoga.
                     */

                    $categoryIcons = [

                        'medicine' =>
                            'assets/img/icon/medicine.svg',

                        'healthcare' =>
                            'assets/img/icon/health-care.svg',

                        'beauty-care' =>
                            'assets/img/icon/beauty-care.svg',

                        'sexual-wellness' =>
                            'assets/img/icon/sexual.svg',

                        'fitness' =>
                            'assets/img/icon/fitness.svg',

                        'lab-test' =>
                            'assets/img/icon/lab-test.svg',

                        'baby-mom-care' =>
                            'assets/img/icon/baby-mom-care.svg',

                        'vitamins-supplement' =>
                            'assets/img/icon/supplements.svg',

                        'supplement' =>
                            'assets/img/icon/supplements.svg',

                        'food-nutrition' =>
                            'assets/img/icon/food-nutrition.svg',

                        'medical-equipments' =>
                            'assets/img/icon/medical-equipements.svg',

                        'equipments' =>
                            'assets/img/icon/medical-equipements.svg',

                        'medical-supplies' =>
                            'assets/img/icon/medical-supplies.svg',

                        'pet-care' =>
                            'assets/img/icon/pet-care.svg'

                    ];


                    /* =========================================
                       DEFAULT ICON
                    ========================================= */

                    $categoryIcon =
                        'assets/img/icon/medicine.svg';


                    /* =========================================
                       SLUG BASED ICON
                    ========================================= */

                    if ($categorySlug !== '') {

                        $slugKey = strtolower(
                            trim($categorySlug)
                        );

                        if (
                            isset(
                                $categoryIcons[$slugKey]
                            )
                        ) {

                            $categoryIcon =
                                $categoryIcons[$slugKey];

                        }

                    }


                    /* =========================================
                       DATABASE IMAGE
                    ========================================= */

                    if ($categoryImage !== '') {

                        /*
                         * External image
                         */
                        if (
                            preg_match(
                                '/^(https?:)?\/\//i',
                                $categoryImage
                            )
                        ) {

                            $categoryIcon =
                                $categoryImage;

                        }

                        /*
                         * Already assets path
                         */
                        elseif (
                            strpos(
                                ltrim(
                                    $categoryImage,
                                    '/'
                                ),
                                'assets/'
                            ) === 0
                        ) {

                            $categoryIcon =
                                ltrim(
                                    $categoryImage,
                                    '/'
                                );

                        }

                        /*
                         * Already uploads path
                         */
                        elseif (
                            strpos(
                                ltrim(
                                    $categoryImage,
                                    '/'
                                ),
                                'uploads/'
                            ) === 0
                        ) {

                            $categoryIcon =
                                ltrim(
                                    $categoryImage,
                                    '/'
                                );

                        }

                        /*
                         * Filename only
                         */
                        else {

                            $imageName =
                                basename(
                                    $categoryImage
                                );

                            $possiblePaths = [

                                'uploads/categories/' .
                                    $imageName,

                                'uploads/category/' .
                                    $imageName,

                                'assets/img/categories/' .
                                    $imageName,

                                'assets/img/category/' .
                                    $imageName,

                                'assets/img/products/' .
                                    $imageName

                            ];


                            foreach (
                                $possiblePaths
                                as $possiblePath
                            ) {

                                if (
                                    file_exists(
                                        __DIR__ .
                                        '/' .
                                        $possiblePath
                                    )
                                ) {

                                    $categoryIcon =
                                        $possiblePath;

                                    break;

                                }

                            }

                        }

                    }


                    /* =========================================
                       CATEGORY PRODUCT COUNT
                    ========================================= */

                    $categoryCountStmt =
                        $conn->prepare("
                            SELECT COUNT(*)
                            FROM products
                            WHERE category_id = ?
                        ");

                    $categoryCountStmt->execute([
                        $categoryId
                    ]);

                    $categoryCount =
                        (int)$categoryCountStmt->fetchColumn();


                    /* =========================================
                       CATEGORY LINK
                    ========================================= */

                    $categoryLink =
                        'products.php?category_id=' .
                        $categoryId;

                    ?>


                    <!-- Dynamic Category -->
                    <div class="col-6 col-md-4 col-lg-2">

                        <div class="category-item">

                            <a
                                href="<?= htmlspecialchars(
                                    $categoryLink,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                                <div class="category-info">

                                    <!-- Category Icon -->
                                    <div class="icon">

                                        <img
                                            src="<?= htmlspecialchars(
                                                $categoryIcon,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            alt="<?= htmlspecialchars(
                                                $categoryName,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            loading="lazy"
                                            onerror="
                                                this.onerror=null;
                                                this.src='assets/img/icon/medicine.svg';
                                            "
                                        >

                                    </div>
                                    <!-- Category Icon End -->


                                    <!-- Category Content -->
                                    <div class="content">

                                        <h4>

                                            <?= htmlspecialchars(
                                                $categoryName,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </h4>

                                        <p>

                                            <?= $categoryCount ?>

                                            <?= $categoryCount == 1
                                                ? 'Item'
                                                : 'Items'
                                            ?>

                                        </p>

                                    </div>
                                    <!-- Category Content End -->


                                </div>

                            </a>

                        </div>

                    </div>
                    <!-- Dynamic Category End -->


                <?php endforeach; ?>


            <?php else: ?>


                <!-- No Categories -->
                <div class="col-12">

                    <div class="category-item">

                        <div class="category-info">

                            <div class="icon">

                                <img
                                    src="assets/img/icon/medicine.svg"
                                    alt="No Categories"
                                >

                            </div>

                            <div class="content">

                                <h4>
                                    No Categories Found
                                </h4>

                                <p>
                                    Please add categories from admin panel.
                                </p>

                            </div>

                        </div>

                    </div>

                </div>
                <!-- No Categories End -->


            <?php endif; ?>

        </div>
        <!-- Dynamic Categories End -->


    </div>

</div>
<!-- category area end -->
        <!-- category area end-->

    </main>


  <?php include 'includes/footer.php'; ?>