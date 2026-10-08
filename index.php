<?php
require_once __DIR__ . '/config/database.php';

function frontendImageUrl($image, $folder = 'products')
{
    $image = trim((string)$image);

    if ($image === '') {
        return '';
    }

    // External image URL
    if (preg_match('/^(https?:)?\/\//i', $image)) {
        return $image;
    }

    $image = ltrim($image, '/');

    // If DB already contains a complete relative path
    if (
        strpos($image, 'assets/') === 0
    ) {
        return $image;
    }

    /*
     * Product images uploaded by the admin are stored in:
     * assets/img/products/
     *
     * The products.image column stores only the filename, for example:
     * product-3-1790669073.jpeg
     * 01.png
     */
    if ($folder === 'products') {
        return 'uploads/products/' . $image;
    }

    /*
     * Category images can use the category folder if a filename is stored.
     * Keep a fallback to the existing medicine icon when no category image exists.
     */
    if ($folder === 'categories') {
        $categoryPaths = [
            'assets/img/category/' . $image,
            'assets/img/categories/' . $image,
            'assets/img/products/' . $image
        ];

        foreach ($categoryPaths as $path) {
            if (file_exists(__DIR__ . '/' . $path)) {
                return $path;
            }
        }

        return '';
    }

    return 'assets/img/' . $folder . '/' . $image;
}

function productImageUrl($image)
{
    $path = frontendImageUrl($image, 'products');

    if ($path === '') {
        return 'assets/img/products/01.png';
    }

    return $path;
}

$categoryStmt = $conn->prepare("
    SELECT id, name, slug, image, description
    FROM categories
    WHERE status = 1
    ORDER BY id ASC
");
$categoryStmt->execute();
$frontendCategories = $categoryStmt->fetchAll();

$productStmt = $conn->prepare("
    SELECT p.*, c.name AS category_name, c.slug AS category_slug
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    ORDER BY p.id DESC
    LIMIT 12
");
$productStmt->execute();
$frontendProducts = $productStmt->fetchAll();

$productListStmt = $conn->prepare("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    ORDER BY p.id DESC
    LIMIT 12
");
$productListStmt->execute();
$productList = $productListStmt->fetchAll();

$popularByCategory = [];

$popularCategoryStmt = $conn->prepare("
    SELECT id, name, slug
    FROM categories
    WHERE status = 1
      AND slug IN ('tablets', 'capsules', 'syrups', 'injections')
    ORDER BY FIELD(slug, 'tablets', 'capsules', 'syrups', 'injections')
");
$popularCategoryStmt->execute();
$popularCategories = $popularCategoryStmt->fetchAll();

foreach ($popularCategories as $popularCategory) {
    $stmt = $conn->prepare("
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE p.category_id = ?
        ORDER BY p.id DESC
        LIMIT 4
    ");
    $stmt->execute([(int)$popularCategory['id']]);
    $popularByCategory[$popularCategory['slug']] = $stmt->fetchAll();
}

include 'includes/header.php';
?>




    <!-- popup search -->
    <!-- <div class="search-popup">
        <button class="close-search"><span class="far fa-times"></span></button>
        <form action="#">
            <div class="form-group">
                <input type="search" name="search-field" class="form-control" placeholder="Search Here..." required>
                <button type="submit"><i class="far fa-search"></i></button>
            </div>
        </form>
    </div> -->
    <!-- popup search end -->


    <main class="main">

      <!-- hero section -->
<div class="hero-section hs-1">
    <div class="hero-single" style="background-image: url(assets/img/hero/bg.png);">
        <div class="container position-relative">
            <div class="row align-items-center">

                <div class="col-lg-6">
                    <div class="hero-content">

                        <h6 class="hero-sub-title"
                            data-animation="fadeInUp"
                            data-delay=".25s">
                            Quality Healthcare Solutions
                        </h6>

                        <h1 class="hero-title"
                            data-animation="fadeInRight"
                            data-delay=".50s">
                            Committed To
                            <span>Quality Pharmaceutical</span>
                            Healthcare
                        </h1>

                        <p data-animation="fadeInLeft" data-delay=".75s">
                            Medinef Pharma is dedicated to providing quality
                            pharmaceutical products and healthcare solutions,
                            with a strong focus on reliability, quality and
                            better healthcare.
                        </p>

                        <div class="hero-btn"
                            data-animation="fadeInUp"
                            data-delay="1s">

                            <a href="products.php" class="theme-btn">
                                Explore Products
                                <i class="fas fa-arrow-right"></i>
                            </a>

                            <a href="about.php" class="theme-btn theme-btn2">
                                About Us
                                <i class="fas fa-arrow-right"></i>
                            </a>

                        </div>

                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="hero-right">

                        <div class="hero-img">
                            <img src="assets/img/hero/hero-1.png"
                                 alt="Medinef Pharma">
                        </div>

                        <div class="hero-img-info">
                            <div class="icon">
                                <img src="assets/img/icon/delivery.svg"
                                     alt="Quality Healthcare">
                            </div>

                            <h6>Quality Products & Healthcare</h6>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<!-- hero section end -->


      <!-- search-product -->
<div class="search-product">
    <div class="container">
        <div class="col-lg-12 col-xl-9">
            <div class="search-form">

                <h5>Search Our Products</h5>

                <form action="products.php" method="get">
                    <div class="row">

                        <!-- Product Category -->
                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <select class="select" name="category">
                                    <option value="">All Categories</option>

                                    <?php foreach ($frontendCategories as $searchCategory): ?>
                                        <option value="<?= htmlspecialchars($searchCategory['slug']) ?>">
                                            <?= htmlspecialchars($searchCategory['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Therapeutic Area -->
                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <select class="select" name="therapeutic_area">
                                    <option value="">Therapeutic Area</option>
                                    <option value="general-medicine">General Medicine</option>
                                    <option value="pain-management">Pain Management</option>
                                    <option value="gastro-care">Gastro Care</option>
                                    <option value="anti-infective">Anti-Infective</option>
                                    <option value="nutraceuticals">Nutraceuticals</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <!-- Product Search -->
                        <div class="col-md-6 col-lg-4">
                            <div class="form-group">
                                <input type="text"
                                       class="form-control"
                                       name="key"
                                       placeholder="Enter product name...">
                            </div>
                        </div>

                        <!-- Search Button -->
                        <div class="col-md-6 col-lg-2">
                            <button type="submit" class="theme-btn">
                                <span class="far fa-search"></span>
                                Search
                            </button>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
<!-- search-product -->


<!-- category area -->
<div class="category-area pt-80 pb-100">

    <div class="container">

        <!-- Heading -->
        <div class="row">

            <div class="col-12 wow fadeInDown" data-wow-delay=".25s">

                <div class="site-heading-inline">

                    <h2 class="site-title">
                        Product Categories
                    </h2>

                    <a href="products.php">
                        View All
                        <i class="fas fa-angle-double-right"></i>
                    </a>

                </div>

            </div>

        </div>
        <!-- Heading End -->


        <!-- Category Slider -->
        <div
            class="category-slider owl-carousel owl-theme wow fadeInUp"
            data-wow-delay=".25s"
        >

            <?php if (!empty($frontendCategories)): ?>

                <?php foreach ($frontendCategories as $category): ?>

                    <?php

                    /* ==========================================
                       CATEGORY DATA
                    ========================================== */

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


                    /* ==========================================
                       CATEGORY IMAGE
                    ========================================== */

                    $finalCategoryImage = '';


                    /* ------------------------------------------
                       1. External URL
                    ------------------------------------------ */

                    if ($categoryImage !== '') {

                        if (
                            strpos($categoryImage, 'http://') === 0 ||
                            strpos($categoryImage, 'https://') === 0
                        ) {

                            $finalCategoryImage = $categoryImage;

                        }

                        /* --------------------------------------
                           2. Already complete assets path
                        -------------------------------------- */

                        elseif (
                            strpos(
                                ltrim($categoryImage, '/'),
                                'assets/'
                            ) === 0
                        ) {

                            $finalCategoryImage =
                                ltrim($categoryImage, '/');

                        }

                        /* --------------------------------------
                           3. Already uploads path
                        -------------------------------------- */

                        elseif (
                            strpos(
                                ltrim($categoryImage, '/'),
                                'uploads/'
                            ) === 0
                        ) {

                            $finalCategoryImage =
                                ltrim($categoryImage, '/');

                        }

                        /* --------------------------------------
                           4. Only filename
                        -------------------------------------- */

                        else {

                            $imageName = basename(
                                $categoryImage
                            );

                            $categoryImagePaths = [

                                'uploads/categories/' . $imageName,

                                'uploads/category/' . $imageName,

                                'assets/img/categories/' . $imageName,

                                'assets/img/category/' . $imageName,

                                'assets/img/products/' . $imageName,

                                'assets/img/product/' . $imageName

                            ];


                            foreach (
                                $categoryImagePaths
                                as $imagePath
                            ) {

                                if (
                                    file_exists(
                                        __DIR__ . '/' . $imagePath
                                    )
                                ) {

                                    $finalCategoryImage =
                                        $imagePath;

                                    break;

                                }

                            }

                        }

                    }


                    /* ==========================================
                       5. TRY SLUG IMAGE
                    ========================================== */

                    if (
                        $finalCategoryImage === '' &&
                        $categorySlug !== ''
                    ) {

                        $extensions = [

                            'png',
                            'jpg',
                            'jpeg',
                            'webp',
                            'svg'

                        ];


                        $categoryFolders = [

                            'uploads/categories/',
                            'uploads/category/',
                            'assets/img/categories/',
                            'assets/img/category/',
                            'assets/img/products/',
                            'assets/img/product/'

                        ];


                        foreach (
                            $categoryFolders
                            as $folder
                        ) {

                            foreach (
                                $extensions
                                as $extension
                            ) {

                                $possibleImage =
                                    $folder .
                                    $categorySlug .
                                    '.' .
                                    $extension;


                                if (
                                    file_exists(
                                        __DIR__ .
                                        '/' .
                                        $possibleImage
                                    )
                                ) {

                                    $finalCategoryImage =
                                        $possibleImage;

                                    break 2;

                                }

                            }

                        }

                    }


                    /* ==========================================
                       6. DEFAULT IMAGE
                    ========================================== */

                    if (
                        $finalCategoryImage === ''
                    ) {

                        $finalCategoryImage =
                            'assets/img/products/01.png';

                    }


                    /* ==========================================
                       CATEGORY LINK
                    ========================================== */

                    $categoryLink =
                        'products.php?category_id=' .
                        $categoryId;

                    ?>


                    <!-- Category Item -->
                    <div class="category-item">

                        <a
                            href="<?= htmlspecialchars(
                                $categoryLink,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >

                            <div class="category-info">


                                <!-- Category Image -->
                                <div class="icon">

                                    <img
                                        src="<?= htmlspecialchars(
                                            $finalCategoryImage,
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
                                            this.src='assets/img/products/01.png';
                                        "
                                    >

                                </div>
                                <!-- Category Image End -->


                                <!-- Category Content -->
                                <div class="content">

                                    <h4>

                                        <?= htmlspecialchars(
                                            $categoryName,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </h4>

                                </div>
                                <!-- Category Content End -->


                            </div>

                        </a>

                    </div>
                    <!-- Category Item End -->


                <?php endforeach; ?>


            <?php else: ?>


                <!-- No Categories -->
                <div class="category-item">

                    <div class="category-info">

                        <div class="icon">

                            <img
                                src="assets/img/products/01.png"
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
                <!-- No Categories End -->


            <?php endif; ?>

        </div>
        <!-- Category Slider End -->


    </div>

</div>
<!-- category area end -->


      <!-- small banner -->
<div class="small-banner pb-100">
    <div class="container wow fadeInUp" data-wow-delay=".25s">
        <div class="row g-4">

            <!-- Banner 1 -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="banner-item">
                    <img src="assets/img/banner/mini-banner-1.png"
                         alt="Quality Pharmaceutical Products">
                    <div class="banner-content">
                        <p>Medinef Pharma</p>
                        <h3>Quality <br>  Products</h3>
                        <a href="#">View Products</a>
                    </div>
                </div>
            </div>

            <!-- Banner 2 -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="banner-item">
                    <img src="assets/img/banner/mini-banner-2.png"
                         alt="Healthcare Solutions">
                    <div class="banner-content">
                        <p>Healthcare</p>
                        <h3>Reliable <br> Healthcare  </h3>
                        <a href="#">Discover More</a>
                    </div>
                </div>
            </div>

            <!-- Banner 3 -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="banner-item">
                    <img src="assets/img/banner/mini-banner-3.png"
                         alt="Pharmaceutical Portfolio">
                    <div class="banner-content">
                        <p>Our Portfolio</p>
                        <h3>Explore Our <br> <span>Pharmaceutical</span> <br>  Products</h3>
                        <a href="#">Explore Now</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- small banner end -->


<!-- featured products -->
<div class="product-area pb-100">
    <div class="container">

        <!-- Section Heading -->
        <div class="row">
            <div class="col-12 wow fadeInDown" data-wow-delay=".25s">
                <div class="site-heading-inline">

                    <h2 class="site-title">
                        Featured Products
                    </h2>

                    <a href="products.php">
                        View All
                        <i class="fas fa-angle-double-right"></i>
                    </a>

                </div>
            </div>
        </div>

        <!-- Product Slider -->
        <div class="product-wrap wow fadeInUp" data-wow-delay=".25s">

            <div class="product-slider owl-carousel owl-theme">

                <?php if (!empty($frontendProducts)): ?>

                    <?php foreach ($frontendProducts as $product): ?>

                        <?php

                        /*
                         * Product image
                         *
                         * Actual folder:
                         * uploads/products/
                         *
                         * Database should contain only filename:
                         * product_abc.jpg
                         */

                        $imageName = trim(
                            (string)($product['image'] ?? '')
                        );

                        $productImage = '';

                        // External image URL
                        if (
                            $imageName !== '' &&
                            preg_match(
                                '/^(https?:)?\/\//i',
                                $imageName
                            )
                        ) {

                            $productImage = $imageName;

                        }

                        // Already contains uploads/products/
                        elseif (
                            $imageName !== '' &&
                            strpos(
                                ltrim($imageName, '/'),
                                'uploads/products/'
                            ) === 0
                        ) {

                            $productImage = ltrim(
                                $imageName,
                                '/'
                            );

                        }

                        // Already contains assets/
                        elseif (
                            $imageName !== '' &&
                            strpos(
                                ltrim($imageName, '/'),
                                'assets/'
                            ) === 0
                        ) {

                            $productImage = ltrim(
                                $imageName,
                                '/'
                            );

                        }

                        // Database contains only filename
                        elseif ($imageName !== '') {

                            $productImage =
                                'uploads/products/' .
                                basename($imageName);

                        }

                        // Final fallback
                        if ($productImage === '') {

                            $productImage =
                                'assets/img/products/01.png';

                        }

                        ?>

                        <!-- Product Item -->
                        <div class="product-item">

                            <!-- Product Image -->
                            <div class="product-img">

                                <a href="product-details.php?id=<?= (int)$product['id'] ?>">

                                    <img
                                        src="<?= htmlspecialchars(
                                            $productImage,
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
                                            this.src='assets/img/products/01.png';
                                        "
                                    >

                                </a>

                                <!-- Product Action -->
                                <div class="product-action-wrap">

                                    <div class="product-action">

                                        <a
                                            href="product-details.php?id=<?= (int)$product['id'] ?>"
                                            data-tooltip="tooltip"
                                            title="View Product"
                                        >
                                            <i class="far fa-eye"></i>
                                        </a>

                                    </div>

                                </div>

                            </div>
                            <!-- Product Image End -->


                            <!-- Product Content -->
                            <div class="product-content">

                                <!-- Category -->
                                <?php if (!empty($product['category_name'])): ?>

                                    <div class="product-category">
                                        <?= htmlspecialchars(
                                            $product['category_name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </div>

                                <?php endif; ?>


                                <!-- Product Name -->
                                <h3 class="product-title">

                                    <a href="product-details.php?id=<?= (int)$product['id'] ?>">

                                        <?= htmlspecialchars(
                                            $product['name'] ?? 'Pharmaceutical Product',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </a>

                                </h3>


                                <!-- Product Bottom -->
                                <div class="product-bottom">

                                    <div class="product-price">

                                        <span>
                                            <?= htmlspecialchars(
                                                $product['category_name']
                                                ?? 'Pharmaceutical Product',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </span>

                                    </div>

                                </div>

                            </div>
                            <!-- Product Content End -->

                        </div>
                        <!-- Product Item End -->


                    <?php endforeach; ?>


                <?php else: ?>

                    <!-- No Products -->
                    <div class="product-item">

                        <div class="product-content">

                            <h3 class="product-title">
                                No Products Found
                            </h3>

                            <p>
                                Add products from the admin panel.
                            </p>

                        </div>

                    </div>

                <?php endif; ?>

            </div>

        </div>
        <!-- Product Slider End -->

    </div>
</div>
<!-- featured products end -->


     <!-- feature area -->
<div class="feature-area pb-100">
    <div class="container wow fadeInUp" data-wow-delay=".25s">
        <div class="feature-wrap">
            <div class="row g-0">

                <!-- Feature 1 -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fal fa-shield-check"></i>
                        </div>
                        <div class="feature-content">
                            <h4>Quality Focused</h4>
                            <p>Commitment To Quality</p>
                        </div>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fal fa-pills"></i>
                        </div>
                        <div class="feature-content">
                            <h4>Pharma Products</h4>
                            <p>Wide Product Portfolio</p>
                        </div>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fal fa-microscope"></i>
                        </div>
                        <div class="feature-content">
                            <h4>Quality Assurance</h4>
                            <p>Focused On Standards</p>
                        </div>
                    </div>
                </div>

                <!-- Feature 4 -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fal fa-headset"></i>
                        </div>
                        <div class="feature-content">
                            <h4>Customer Support</h4>
                            <p>We're Here To Help</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<!-- feature area end -->


<!-- popular item -->
<div class="product-area pb-100">
    <div class="container">

        <div class="row g-4">

            <!-- Popular Products -->
            <div class="col-lg-9">

                <div class="row">
                    <div class="col-12 wow fadeInDown" data-wow-delay=".25s">

                        <div class="site-heading-inline">

                            <h2 class="site-title">
                                Popular Items
                            </h2>

                            <a href="products.php">
                                All Products
                                <i class="fas fa-angle-double-right"></i>
                            </a>

                        </div>

                        <!-- Category Tabs -->
                        <div class="item-tab">

                            <ul
                                class="nav nav-pills mt-40 mb-50"
                                id="item-tab"
                                role="tablist"
                            >

                                <?php
                                $tabIndex = 1;

                                foreach ($popularCategories as $popularCategory):
                                ?>

                                    <li
                                        class="nav-item"
                                        role="presentation"
                                    >

                                        <button
                                            class="nav-link <?= $tabIndex === 1 ? 'active' : '' ?>"
                                            id="item-tab<?= $tabIndex ?>"
                                            data-bs-toggle="pill"
                                            data-bs-target="#pill-item-tab<?= $tabIndex ?>"
                                            type="button"
                                            role="tab"
                                            aria-controls="pill-item-tab<?= $tabIndex ?>"
                                            aria-selected="<?= $tabIndex === 1 ? 'true' : 'false' ?>"
                                        >
                                            <?= htmlspecialchars(
                                                $popularCategory['name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </button>

                                    </li>

                                <?php
                                    $tabIndex++;
                                endforeach;
                                ?>

                            </ul>

                        </div>

                    </div>
                </div>


                <!-- Tab Content -->
                <div
                    class="tab-content wow fadeInUp"
                    data-wow-delay=".25s"
                    id="item-tabContent"
                >

                    <?php

                    $tabIndex = 1;

                    foreach ($popularCategories as $popularCategory):

                        $slug = $popularCategory['slug'];

                        $tabProducts =
                            $popularByCategory[$slug] ?? [];

                    ?>

                        <!-- Tab -->
                        <div
                            class="tab-pane <?= $tabIndex === 1 ? 'show active' : '' ?>"
                            id="pill-item-tab<?= $tabIndex ?>"
                            role="tabpanel"
                            aria-labelledby="item-tab<?= $tabIndex ?>"
                            tabindex="0"
                        >

                            <div class="row g-3">

                                <?php if (!empty($tabProducts)): ?>


                                    <?php foreach ($tabProducts as $product): ?>

                                        <?php

                                        /*
                                         * PRODUCT IMAGE
                                         *
                                         * Actual folder:
                                         * uploads/products/
                                         *
                                         * DB example:
                                         * product_abc.jpg
                                         */

                                        $imageName = trim(
                                            (string)($product['image'] ?? '')
                                        );

                                        $productImage = '';


                                        // External image URL
                                        if (
                                            $imageName !== '' &&
                                            preg_match(
                                                '/^(https?:)?\/\//i',
                                                $imageName
                                            )
                                        ) {

                                            $productImage = $imageName;

                                        }


                                        // Already uploads/products/ path
                                        elseif (
                                            $imageName !== '' &&
                                            strpos(
                                                ltrim($imageName, '/'),
                                                'uploads/products/'
                                            ) === 0
                                        ) {

                                            $productImage = ltrim(
                                                $imageName,
                                                '/'
                                            );

                                        }


                                        // Already assets path
                                        elseif (
                                            $imageName !== '' &&
                                            strpos(
                                                ltrim($imageName, '/'),
                                                'assets/'
                                            ) === 0
                                        ) {

                                            $productImage = ltrim(
                                                $imageName,
                                                '/'
                                            );

                                        }


                                        // Only filename in database
                                        elseif ($imageName !== '') {

                                            $productImage =
                                                'uploads/products/' .
                                                basename($imageName);

                                        }


                                        // Fallback image
                                        if ($productImage === '') {

                                            $productImage =
                                                'assets/img/products/01.png';

                                        }

                                        ?>


                                        <!-- Product Column -->
                                        <div
                                            class="col-md-6 col-lg-4 col-xl-3"
                                        >

                                            <div class="product-item">


                                                <!-- Product Image -->
                                                <div class="product-img">

                                                    <a
                                                        href="product-details.php?id=<?= (int)$product['id'] ?>"
                                                    >

                                                        <img
                                                            src="<?= htmlspecialchars(
                                                                $productImage,
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
                                                                this.src='assets/img/products/01.png';
                                                            "
                                                        >

                                                    </a>


                                                    <!-- Product Action -->
                                                    <div class="product-action-wrap">

                                                        <div class="product-action">

                                                            <a
                                                                href="product-details.php?id=<?= (int)$product['id'] ?>"
                                                                data-placement="top"
                                                                data-tooltip="tooltip"
                                                                title="View Product"
                                                            >

                                                                <i class="far fa-eye"></i>

                                                            </a>

                                                        </div>

                                                    </div>

                                                </div>
                                                <!-- Product Image End -->


                                                <!-- Product Content -->
                                                <div class="product-content">


                                                    <!-- Product Name -->
                                                    <h3 class="product-title">

                                                        <a
                                                            href="product-details.php?id=<?= (int)$product['id'] ?>"
                                                        >

                                                            <?= htmlspecialchars(
                                                                $product['name'] ?? 'Pharmaceutical Product',
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            ) ?>

                                                        </a>

                                                    </h3>


                                                    <!-- Category -->
                                                    <?php if (!empty($product['category_name'])): ?>

                                                        <div class="product-category">

                                                            <?= htmlspecialchars(
                                                                $product['category_name'],
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            ) ?>

                                                        </div>

                                                    <?php endif; ?>


                                                    <!-- Rating -->
                                                    <div class="product-rate">

                                                        <i class="fas fa-star"></i>
                                                        <i class="fas fa-star"></i>
                                                        <i class="fas fa-star"></i>
                                                        <i class="fas fa-star"></i>
                                                        <i class="far fa-star"></i>

                                                    </div>


                                                </div>
                                                <!-- Product Content End -->


                                            </div>

                                        </div>
                                        <!-- Product Column End -->


                                    <?php endforeach; ?>


                                <?php else: ?>


                                    <!-- No Products -->
                                    <div class="col-12">

                                        <div class="product-item">

                                            <div class="product-content">

                                                <h3 class="product-title">
                                                    No Products Available
                                                </h3>

                                                <p>

                                                    Add products under

                                                    <?= htmlspecialchars(
                                                        $popularCategory['name'],
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>

                                                    from the admin panel.

                                                </p>

                                            </div>

                                        </div>

                                    </div>


                                <?php endif; ?>

                            </div>

                        </div>
                        <!-- Tab End -->


                    <?php

                        $tabIndex++;

                    endforeach;

                    ?>


                    <?php if (empty($popularCategories)): ?>

                        <div class="tab-pane show active">

                            <div class="row">

                                <div class="col-12">

                                    <p>
                                        No product categories available.
                                    </p>

                                </div>

                            </div>

                        </div>

                    <?php endif; ?>


                </div>
                <!-- Tab Content End -->

            </div>
            <!-- Popular Products End -->


            <!-- Right Banner -->
            <div class="col-lg-3">

                <div
                    class="product-banner wow fadeInRight"
                    data-wow-delay=".25s"
                >

                    <a href="products.php">

                        <img
                            src="assets/img/banner/product-banner.jpg"
                            alt="Medinef Pharma Products"
                        >

                    </a>

                </div>

            </div>
            <!-- Right Banner End -->


        </div>

    </div>
</div>
<!-- popular item end -->


        <!-- big banner -->
        <div class="big-banner">
            <div class="container wow fadeInUp" data-wow-delay=".25s">
                <div class="banner-wrap" style="background-image: url(assets/img/banner/big-banner.png);">
                    <div class="row">
                        <div class="col-lg-8 mx-auto">
                            <div class="banner-content">
                                <div class="banner-info">
                                    <h6>Medinef Pharma</h6>
                                      <h3>
                                Quality Products For  
                                <span>Better <br> Healthcare</span>
                            </h3>
                                   <p>
                                Trusted pharmaceutical solutions focused on <br>
                                quality, reliability and healthcare.
                            </p>
                                </div>
                                <a href="#" class="theme-btn">Shop Now<i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- big banner end -->


      <!-- brand area  Portfolio -->
<div class="brand-area pt-80">
    <div class="container">

        <div class="row">
            <div class="col-12">
                <div class="site-heading-inline">

                    <h2 class="site-title">Our Product Portfolio</h2>

                    <a href="products.php">
                        View All
                        <i class="fas fa-angle-double-right"></i>
                    </a>

                </div>
            </div>
        </div>

        <div class="brand-slider owl-carousel owl-theme">

            <div class="brand-item">
                <a href="products.php">
                    <img src="assets/img/brand/01.png"
                         alt="Medinef Pharma Products">
                </a>
            </div>

            <div class="brand-item">
                <a href="products.php">
                    <img src="assets/img/brand/02.png"
                         alt="Medinef Pharma Products">
                </a>
            </div>

            <div class="brand-item">
                <a href="products.php">
                    <img src="assets/img/brand/03.png"
                         alt="Medinef Pharma Products">
                </a>
            </div>

            <div class="brand-item">
                <a href="products.php">
                    <img src="assets/img/brand/04.png"
                         alt="Medinef Pharma Products">
                </a>
            </div>

            <div class="brand-item">
                <a href="products.php">
                    <img src="assets/img/brand/05.png"
                         alt="Medinef Pharma Products">
                </a>
            </div>

            <div class="brand-item">
                <a href="products.php">
                    <img src="assets/img/brand/06.png"
                         alt="Medinef Pharma Products">
                </a>
            </div>

        </div>
    </div>
</div>
<!-- brand area end -->


       <!-- video area -->
<div class="video-area pt-100">
    <div class="container-fluid px-0">
        <div class="video-content"
            style="background-image: url(assets/img/video/01.jpg);">

            <div class="video-wrapper">
                <a class="play-btn popup-youtube"
                    href="YOUR_MEDINEF_PHARMA_YOUTUBE_VIDEO_URL">
                    <i class="fas fa-play"></i>
                </a>
            </div>

        </div>
    </div>
</div>
<!-- video area end -->


<!-- product list -->
<div class="product-list pl-negative pb-100">

    <div class="container wow fadeInUp" data-wow-delay=".25s">

        <div class="row g-4">

            <?php
            $productGroups = [
                [
                    'title' => 'Featured Products',
                    'items' => array_slice($productList, 0, 3)
                ],
                [
                    'title' => 'Our Products',
                    'items' => array_slice($productList, 3, 3)
                ],
                [
                    'title' => 'Latest Products',
                    'items' => array_slice($productList, 6, 3)
                ]
            ];
            ?>

            <?php foreach ($productGroups as $group): ?>

                <div class="col-12 col-md-6 col-lg-6 col-xl-4">

                    <div class="product-list-box">

                        <h2 class="product-list-title">
                            <?= htmlspecialchars(
                                $group['title'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </h2>

                        <?php if (!empty($group['items'])): ?>

                            <?php foreach ($group['items'] as $product): ?>

                                <?php
                                /*
                                 * PRODUCT IMAGE PATH
                                 *
                                 * Website root:
                                 * C:/xampp/htdocs/medinefpharma.online/
                                 *
                                 * Images:
                                 * /uploads/products/
                                 */

                                $imageName = trim(
                                    (string)($product['image'] ?? '')
                                );

                                if ($imageName !== '') {

                                    // External image URL
                                    if (
                                        preg_match(
                                            '#^(https?:)?//#i',
                                            $imageName
                                        )
                                    ) {

                                        $productImage = $imageName;

                                    }

                                    // Already contains uploads/products/
                                    elseif (
                                        strpos(
                                            ltrim($imageName, '/'),
                                            'uploads/products/'
                                        ) === 0
                                    ) {

                                        $productImage =
                                            ltrim($imageName, '/');

                                    }

                                    // Already contains assets/
                                    elseif (
                                        strpos(
                                            ltrim($imageName, '/'),
                                            'assets/'
                                        ) === 0
                                    ) {

                                        $productImage =
                                            ltrim($imageName, '/');

                                    }

                                    // Database contains only filename
                                    else {

                                        $productImage =
                                            'uploads/products/' .
                                            basename($imageName);
                                    }

                                } else {

                                    $productImage =
                                        'assets/img/products/01.png';
                                }

                                $productName =
                                    $product['name']
                                    ?? 'Pharmaceutical Product';

                                $productCategory =
                                    $product['category_name']
                                    ?? 'Pharmaceutical Product';
                                ?>

                                <!-- Product Item -->
                                <div class="product-list-item">

                                    <!-- Product Image -->
                                    <div class="product-list-img">

                                        <a
                                            href="product-details.php?id=<?= (int)$product['id'] ?>"
                                        >

                                            <img
                                                src="<?= htmlspecialchars(
                                                    $productImage,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                                alt="<?= htmlspecialchars(
                                                    $productName,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                                loading="lazy"
                                                onerror="
                                                    this.onerror=null;
                                                    this.src='assets/img/products/01.png';
                                                "
                                            >

                                        </a>

                                    </div>
                                    <!-- Product Image End -->


                                    <!-- Product Content -->
                                    <div class="product-list-content">

                                        <h4>

                                            <a
                                                href="product-details.php?id=<?= (int)$product['id'] ?>"
                                            >

                                                <?= htmlspecialchars(
                                                    $productName,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>

                                            </a>

                                        </h4>

                                        <p>

                                            <?= htmlspecialchars(
                                                $productCategory,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </p>

                                    </div>
                                    <!-- Product Content End -->


                                    <!-- View Product -->
                                    <a
                                        href="product-details.php?id=<?= (int)$product['id'] ?>"
                                        class="product-list-btn"
                                        data-bs-placement="left"
                                        data-tooltip="tooltip"
                                        title="View Product"
                                    >

                                        <i class="far fa-eye"></i>

                                    </a>

                                </div>
                                <!-- Product Item End -->

                            <?php endforeach; ?>

                        <?php else: ?>

                            <!-- No Products -->
                            <div class="product-list-item">

                                <div class="product-list-content">

                                    <h4>
                                        No Products Found
                                    </h4>

                                    <p>
                                        Add products from the admin panel.
                                    </p>

                                </div>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</div>
<!-- product list end -->

<!-- deal area -->
<div class="deal-area pt-50 pb-50">
    <div class="deal-text-shape">Care</div>

    <div class="container">
        <div class="deal-wrap wow fadeInUp" data-wow-delay=".25s">

            <div class="deal-slider owl-carousel owl-theme">

                <!-- Slide 1 -->
                <div class="deal-item">
                    <div class="row align-items-center">

                        <div class="col-lg-6">
                            <div class="deal-content">

                                <div class="deal-info">
                                    <span>Medinef Pharma</span>

                                    <h1>
                                        Quality Focused
                                        Pharmaceutical Products
                                    </h1>

                                    <p>
                                        We are committed to providing quality
                                        pharmaceutical products and healthcare
                                        solutions with a focus on reliability,
                                        quality and better healthcare.
                                    </p>
                                </div>

                                <a href="products.php"
                                   class="theme-btn theme-btn2">
                                    Explore Products
                                    <i class="fas fa-arrow-right"></i>
                                </a>

                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="deal-img">
                                <img src="assets/img/deal/01.png"
                                     alt="Medinef Pharma Products">
                            </div>
                        </div>

                    </div>
                </div>


                <!-- Slide 2 -->
                <div class="deal-item">
                    <div class="row align-items-center">

                        <div class="col-lg-6">
                            <div class="deal-content">

                                <div class="deal-info">
                                    <span>Our Commitment</span>

                                    <h1>
                                        Supporting Better
                                        Healthcare
                                    </h1>

                                    <p>
                                        Medinef Pharma focuses on quality,
                                        consistency and dependable
                                        pharmaceutical solutions for
                                        healthcare needs.
                                    </p>
                                </div>

                                <a href="about.php"
                                   class="theme-btn theme-btn2">
                                    About Us
                                    <i class="fas fa-arrow-right"></i>
                                </a>

                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="deal-img">
                                <img src="assets/img/deal/02.png"
                                     alt="Healthcare Solutions">
                            </div>
                        </div>

                    </div>
                </div>


                <!-- Slide 3 -->
                <div class="deal-item">
                    <div class="row align-items-center">

                        <div class="col-lg-6">
                            <div class="deal-content">

                                <div class="deal-info">
                                    <span>Pharmaceutical Portfolio</span>

                                    <h1>
                                        Discover Our
                                        Product Range
                                    </h1>

                                    <p>
                                        Explore the Medinef Pharma product
                                        portfolio designed around quality
                                        pharmaceutical and healthcare needs.
                                    </p>
                                </div>

                                <a href="products.php"
                                   class="theme-btn theme-btn2">
                                    View Products
                                    <i class="fas fa-arrow-right"></i>
                                </a>

                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="deal-img">
                                <img src="assets/img/deal/03.png"
                                     alt="Pharmaceutical Portfolio">
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
<!-- deal area end -->


       <!-- about area -->
<div class="about-area py-100">
    <div class="container">
        <div class="row align-items-center">

            <div class="col-lg-6">
                <div class="about-left wow fadeInLeft" data-wow-delay=".25s">

                    <div class="about-img">
                        <div class="row">
                            <div class="col-7">
                                <img class="img-1"
                                     src="assets/img/about/01.jpg"
                                     alt="Medinef Pharma">
                            </div>

                            <div class="col-5 align-self-end">
                                <img class="img-2"
                                     src="assets/img/about/02.jpg"
                                     alt="Medinef Pharma Products">
                            </div>
                        </div>
                    </div>

                    <div class="about-experience">
                        <div class="about-experience-icon">
                            <img src="assets/img/icon/experience.svg"
                                 alt="Quality Healthcare">
                        </div>

                        <b>Quality &amp; <br>Reliability</b>
                    </div>

                    <div class="about-shape">
                        <img src="assets/img/shape/01.png" alt="">
                    </div>

                </div>
            </div>


            <div class="col-lg-6">
                <div class="about-right wow fadeInRight" data-wow-delay=".25s">

                    <div class="site-heading mb-3">

                        <span class="site-title-tagline justify-content-start">
                            <i class="flaticon-drive"></i>
                            About Medinef Pharma
                        </span>

                        <h2 class="site-title">
                            Committed To Quality
                            <span>Pharmaceutical</span> Healthcare
                        </h2>

                    </div>

                    <p>
                        Medinef Pharma is focused on providing quality
                        pharmaceutical products and healthcare solutions.
                        Our approach is centered on quality, reliability
                        and a commitment to supporting better healthcare.
                    </p>

                    <div class="about-list">
                        <ul>

                            <li>
                                <i class="fas fa-check-double"></i>
                                Quality Focused Pharmaceutical Products
                            </li>

                            <li>
                                <i class="fas fa-check-double"></i>
                                Reliable Healthcare Solutions
                            </li>

                            <li>
                                <i class="fas fa-check-double"></i>
                                Focus On Product Quality
                            </li>

                            <li>
                                <i class="fas fa-check-double"></i>
                                Growing Pharmaceutical Product Portfolio
                            </li>

                        </ul>
                    </div>

                    <a href="about.php" class="theme-btn mt-4">
                        Discover More
                        <i class="fas fa-arrow-right"></i>
                    </a>

                </div>
            </div>

        </div>
    </div>
</div>
<!-- about area end -->

       <!-- choose-area -->
<div class="choose-area bg py-100">
    <div class="container">

        <div class="row g-4 align-items-center wow fadeInDown" data-wow-delay=".25s">

            <div class="col-lg-4">
                <div class="choose-img">
                    <img src="assets/img/choose/01.jpg"
                         alt="Medinef Pharma">
                </div>
            </div>

            <div class="col-lg-4">
                <span class="site-title-tagline">Why Choose Medinef Pharma</span>

                <h2 class="site-title">
                    Focused On Quality And Reliable
                    Pharmaceutical Products
                </h2>
            </div>

            <div class="col-lg-4">
                <p>
                    Medinef Pharma is focused on delivering quality
                    pharmaceutical products with an emphasis on reliability,
                    consistency and better healthcare solutions.
                </p>
            </div>

        </div>


        <div class="choose-content wow fadeInUp" data-wow-delay=".25s">

            <div class="row g-4">

                <!-- Quality -->
                <div class="col-lg-4">
                    <div class="choose-item">

                        <div class="choose-icon">
                            <img src="assets/img/icon/warranty.svg"
                                 alt="Quality Products">
                        </div>

                        <div class="choose-info">
                            <h4>Quality Products</h4>

                            <p>
                                Our product portfolio is focused on quality
                                pharmaceutical and healthcare products.
                            </p>
                        </div>

                    </div>
                </div>


                <!-- Reliability -->
                <div class="col-lg-4">
                    <div class="choose-item">

                        <div class="choose-icon">
                            <img src="assets/img/icon/price.svg"
                                 alt="Reliable Healthcare">
                        </div>

                        <div class="choose-info">
                            <h4>Reliable Solutions</h4>

                            <p>
                                We focus on dependable products and consistent
                                solutions for healthcare requirements.
                            </p>
                        </div>

                    </div>
                </div>


                <!-- Healthcare -->
                <div class="col-lg-4">
                    <div class="choose-item">

                        <div class="choose-icon">
                            <img src="assets/img/icon/delivery.svg"
                                 alt="Healthcare Focus">
                        </div>

                        <div class="choose-info">
                            <h4>Healthcare Focused</h4>

                            <p>
                                Our approach is centered around supporting
                                better healthcare through quality products.
                            </p>
                        </div>

                    </div>
                </div>

            </div>

        </div>

    </div>
</div>
<!-- choose-area end -->


           <!-- gallery-area -->
        <div class="gallery-area py-100">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 mx-auto">
                        <div class="site-heading text-center">
                            <span class="site-title-tagline">Our Gallery</span>
                            <h2 class="site-title">Let's Check Our Photo <span>Gallery</span></h2>
                        </div>
                    </div>
                </div>
                <div class="row g-4 popup-gallery">
                    <div class="col-md-8 col-lg-6">
                        <div class="gallery-item gallery-btn-active wow fadeInUp" data-wow-delay=".25s">
                            <div class="gallery-img">
                                <img src="assets/img/gallery/01.jpg" alt="">
                                <a class="popup-img gallery-link" href="assets/img/gallery/01.jpg"><i
                                    class="fal fa-plus"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <div class="gallery-item wow fadeInDown" data-wow-delay=".25s">
                            <div class="gallery-img">
                                <img src="assets/img/gallery/02.jpg" alt="">
                                <a class="popup-img gallery-link" href="assets/img/gallery/02.jpg"><i
                                        class="fal fa-plus"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <div class="gallery-item wow fadeInUp" data-wow-delay=".25s">
                            <div class="gallery-img">
                                <img src="assets/img/gallery/03.jpg" alt="">
                                <a class="popup-img gallery-link" href="assets/img/gallery/03.jpg"><i
                                        class="fal fa-plus"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <div class="gallery-item wow fadeInDown" data-wow-delay=".25s">
                            <div class="gallery-img">
                                <img src="assets/img/gallery/04.jpg" alt="">
                                <a class="popup-img gallery-link" href="assets/img/gallery/04.jpg"><i
                                        class="fal fa-plus"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <div class="gallery-item wow fadeInUp" data-wow-delay=".25s">
                            <div class="gallery-img">
                                <img src="assets/img/gallery/05.jpg" alt="">
                                <a class="popup-img gallery-link" href="assets/img/gallery/05.jpg"><i
                                        class="fal fa-plus"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8 col-lg-6">
                        <div class="gallery-item wow fadeInDown" data-wow-delay=".25s">
                            <div class="gallery-img">
                                <img src="assets/img/gallery/06.jpg" alt="">
                                <a class="popup-img gallery-link" href="assets/img/gallery/06.jpg"><i
                                        class="fal fa-plus"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- gallery-area end -->


        <!-- testimonial area -->
        <div class="testimonial-area ts-bg py-80">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 mx-auto wow fadeInDown" data-wow-delay=".25s">
                        <div class="site-heading text-center">
                            <span class="site-title-tagline">Testimonials</span>
                            <h2 class="site-title text-white">What Our Client Say's <span>About Us</span></h2>
                        </div>
                    </div>
                </div>
                <div class="testimonial-slider owl-carousel owl-theme wow fadeInUp" data-wow-delay=".25s">
                    <div class="testimonial-item">
                        <div class="testimonial-author">
                            <div class="testimonial-author-img">
                                <img src="assets/img/testimonial/01.jpg" alt="">
                            </div>
                            <div class="testimonial-author-info">
                                <h4>kuldeep</h4>
                                <p>Customer</p>
                            </div>
                        </div>
                        <div class="testimonial-quote">
                            <p>
                                There are many variations of long passages available but the content majority have
                                suffered to the editor page when looking at its layout alteration in some injected.
                            </p>
                        </div>
                        <div class="testimonial-rate">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="testimonial-quote-icon"><img src="assets/img/icon/quote.php" alt=""></div>
                    </div>
                    <div class="testimonial-item">
                        <div class="testimonial-author">
                            <div class="testimonial-author-img">
                                <img src="assets/img/testimonial/02.jpg" alt="">
                            </div>
                            <div class="testimonial-author-info">
                                <h4>Salman Khan</h4>
                                <p>Customer</p>
                            </div>
                        </div>
                        <div class="testimonial-quote">
                            <p>
                                There are many variations of long passages available but the content majority have
                                suffered to the editor page when looking at its layout alteration in some injected.
                            </p>
                        </div>
                        <div class="testimonial-rate">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="testimonial-quote-icon"><img src="assets/img/icon/quote.php" alt=""></div>
                    </div>
                    <div class="testimonial-item">
                        <div class="testimonial-author">
                            <div class="testimonial-author-img">
                                <img src="assets/img/testimonial/03.jpg" alt="">
                            </div>
                            <div class="testimonial-author-info">
                                <h4>Aliya</h4>
                                <p>Customer</p>
                            </div>
                        </div>
                        <div class="testimonial-quote">
                            <p>
                                There are many variations of long passages available but the content majority have
                                suffered to the editor page when looking at its layout alteration in some injected.
                            </p>
                        </div>
                        <div class="testimonial-rate">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="testimonial-quote-icon"><img src="assets/img/icon/quote.php" alt=""></div>
                    </div>
                    <div class="testimonial-item">
                        <div class="testimonial-author">
                            <div class="testimonial-author-img">
                                <img src="assets/img/testimonial/04.jpg" alt="">
                            </div>
                            <div class="testimonial-author-info">
                                <h4>Swita</h4>
                                <p>Customer</p>
                            </div>
                        </div>
                        <div class="testimonial-quote">
                            <p>
                                There are many variations of long passages available but the content majority have
                                suffered to the editor page when looking at its layout alteration in some injected.
                            </p>
                        </div>
                        <div class="testimonial-rate">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="testimonial-quote-icon"><img src="assets/img/icon/quote.php" alt=""></div>
                    </div>
                    <div class="testimonial-item">
                        <div class="testimonial-author">
                            <div class="testimonial-author-img">
                                <img src="assets/img/testimonial/05.jpg" alt="">
                            </div>
                            <div class="testimonial-author-info">
                                <h4>Sanju</h4>
                                <p>Customer</p>
                            </div>
                        </div>
                        <div class="testimonial-quote">
                            <p>
                                There are many variations of long passages available but the content majority have
                                suffered to the editor page when looking at its layout alteration in some injected.
                            </p>
                        </div>
                        <div class="testimonial-rate">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="testimonial-quote-icon"><img src="assets/img/icon/quote.php" alt=""></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- testimonial area end -->


      <!-- blog area -->
<div class="blog-area py-100">
    <div class="container">

        <div class="row">
            <div class="col-lg-6 mx-auto">
                <div class="site-heading text-center">

                    <span class="site-title-tagline">
                        Pharma Insights
                    </span>

                    <h2 class="site-title">
                        Latest <span>Updates</span>
                    </h2>

                </div>
            </div>
        </div>


        <div class="row g-4">

            <!-- Blog 1 -->
            <div class="col-md-6 col-lg-4">
                <div class="blog-item wow fadeInUp" data-wow-delay=".25s">

                    <div class="blog-item-img">
                        <img src="assets/img/blog/01.jpg"
                             alt="Pharmaceutical Products">

                        <span class="blog-date">
                            <i class="far fa-calendar-alt"></i>
                            Pharma
                        </span>
                    </div>

                    <div class="blog-item-info">

                        <div class="blog-item-meta">
                            <ul>
                                <li>
                                    <a href="#">
                                        <i class="far fa-user-circle"></i>
                                        Medinef Pharma
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <h4 class="blog-title">
                            <a href="#">
                                Understanding The Importance Of
                                Quality Pharmaceutical Products
                            </a>
                        </h4>

                        <p>
                            Learn more about the importance of quality,
                            reliability and consistency in pharmaceutical
                            products.
                        </p>

                        <a class="theme-btn" href="blog-details.php">
                            Read More
                            <i class="fas fa-arrow-right"></i>
                        </a>

                    </div>
                </div>
            </div>


            <!-- Blog 2 -->
            <div class="col-md-6 col-lg-4">
                <div class="blog-item wow fadeInDown" data-wow-delay=".25s">

                    <div class="blog-item-img">
                        <img src="assets/img/blog/02.jpg"
                             alt="Healthcare Solutions">

                        <span class="blog-date">
                            <i class="far fa-calendar-alt"></i>
                            Healthcare
                        </span>
                    </div>

                    <div class="blog-item-info">

                        <div class="blog-item-meta">
                            <ul>
                                <li>
                                    <a href="#">
                                        <i class="far fa-user-circle"></i>
                                        Medinef Pharma
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <h4 class="blog-title">
                            <a href="#">
                                Building Better Healthcare Through
                                Reliable Pharmaceutical Solutions
                            </a>
                        </h4>

                        <p>
                            Explore how dependable pharmaceutical solutions
                            can support better healthcare requirements.
                        </p>

                        <a class="theme-btn" href="blog-details.php">
                            Read More
                            <i class="fas fa-arrow-right"></i>
                        </a>

                    </div>
                </div>
            </div>


            <!-- Blog 3 -->
            <div class="col-md-6 col-lg-4">
                <div class="blog-item wow fadeInUp" data-wow-delay=".25s">

                    <div class="blog-item-img">
                        <img src="assets/img/blog/03.jpg"
                             alt="Pharmaceutical Portfolio">

                        <span class="blog-date">
                            <i class="far fa-calendar-alt"></i>
                            Pharma Updates
                        </span>
                    </div>

                    <div class="blog-item-info">

                        <div class="blog-item-meta">
                            <ul>
                                <li>
                                    <a href="#">
                                        <i class="far fa-user-circle"></i>
                                        Medinef Pharma
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <h4 class="blog-title">
                            <a href="#">
                                Exploring Our Growing Pharmaceutical
                                Product Portfolio
                            </a>
                        </h4>

                        <p>
                            Discover more about the products and healthcare
                            solutions included in the Medinef Pharma portfolio.
                        </p>

                        <a class="theme-btn" href="blog-details.php">
                            Read More
                            <i class="fas fa-arrow-right"></i>
                        </a>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- blog area end -->

        <!-- newsletter area -->
        <div class="newsletter-area pb-100">
            <div class="container wow fadeInUp" data-wow-delay=".25s">
                <div class="newsletter-wrap">
                    <div class="row">
                        <div class="col-lg-6 mx-auto">
                            <div class="newsletter-content">
                                <h3>Get <span>20%</span> Off Discount Coupon</h3>
                                <p>By Subscribe Our Newsletter</p>
                                <div class="subscribe-form">
                                    <form action="#">
                                        <input type="email" class="form-control" placeholder="Your Email Address">
                                        <button class="theme-btn" type="submit">
                                            Subscribe <i class="far fa-paper-plane"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- newsletter area end -->


        <!-- instagram-area -->
        <div class="instagram-area pb-100">
            <div class="container wow fadeInUp" data-wow-delay=".25s">
                <div class="row">
                    <div class="col-lg-6 mx-auto">
                        <div class="site-heading text-center">
                            <h2 class="site-title">Instagram <span>@medion</span></h2>
                        </div>
                    </div>
                </div>
                <div class="instagram-slider owl-carousel owl-theme">
                    <div class="instagram-item">
                        <div class="instagram-img">
                            <img src="assets/img/instagram/01.jpg" alt="Thumb">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="instagram-item">
                        <div class="instagram-img">
                            <img src="assets/img/instagram/02.jpg" alt="Thumb">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="instagram-item">
                        <div class="instagram-img">
                            <img src="assets/img/instagram/03.jpg" alt="Thumb">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="instagram-item">
                        <div class="instagram-img">
                            <img src="assets/img/instagram/04.jpg" alt="Thumb">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="instagram-item">
                        <div class="instagram-img">
                            <img src="assets/img/instagram/05.jpg" alt="Thumb">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="instagram-item">
                        <div class="instagram-img">
                            <img src="assets/img/instagram/06.jpg" alt="Thumb">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="instagram-item">
                        <div class="instagram-img">
                            <img src="assets/img/instagram/07.jpg" alt="Thumb">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- instagram-area end -->

    </main>


<?php include 'includes/footer.php'; ?>