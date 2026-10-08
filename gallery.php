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

        <!-- breadcrumb -->
        <div class="site-breadcrumb">
            <div class="site-breadcrumb-bg"
                 style="background: url(assets/img/breadcrumb/01.jpg)"></div>
            <div class="container">
                <div class="site-breadcrumb-wrap">
                    <h4 class="breadcrumb-title">Gallery</h4>
                    <ul class="breadcrumb-menu">
                        <li>
                            <a href="index.html">
                                <i class="far fa-home"></i> Home
                            </a>
                        </li>
                        <li class="active">Gallery</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- breadcrumb end -->

        <!-- gallery area -->
        <style>
            .pharma-gallery-area {
                padding: 90px 0 100px;
            }

            .pharma-gallery-heading {
                max-width: 760px;
                margin: 0 auto 50px;
            }

            .pharma-gallery-heading p {
                color: #65748b;
                font-size: 16px;
                line-height: 1.8;
                margin-top: 15px;
            }

            .pharma-gallery-item {
                position: relative;
                overflow: hidden;
                border-radius: 12px;
                background: #f7f8fc;
                margin-bottom: 18px;
            }

            .pharma-gallery-item img {
                width: 100%;
                height: 280px;
                object-fit: cover;
                display: block;
                transition: transform .5s ease;
            }

            .pharma-gallery-overlay {
                position: absolute;
                inset: 0;
                background: rgba(75, 64, 153, .78);
                display: flex;
                align-items: center;
                justify-content: center;
                opacity: 0;
                transition: all .35s ease;
            }

            .pharma-gallery-overlay a {
                width: 55px;
                height: 55px;
                border-radius: 50%;
                background: #fff;
                color: #4B4099;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
            }

            .pharma-gallery-item:hover img {
                transform: scale(1.08);
            }

            .pharma-gallery-item:hover .pharma-gallery-overlay {
                opacity: 1;
            }

            .pharma-gallery-caption {
                text-align: center;
                padding: 0 10px 28px;
            }

            .pharma-gallery-caption h4 {
                color: #163b55;
                font-size: 19px;
                margin-bottom: 6px;
            }

            .pharma-gallery-caption p {
                color: #65748b;
                font-size: 14px;
                margin: 0;
            }

            @media (max-width: 767px) {
                .pharma-gallery-area {
                    padding: 65px 0 75px;
                }

                .pharma-gallery-item img {
                    height: 240px;
                }
            }
        </style>

        <div class="pharma-gallery-area">
            <div class="container">

                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="site-heading text-center pharma-gallery-heading">

                            <span class="site-title-tagline">
                                Our Gallery
                            </span>

                            <h2 class="site-title">
                                Explore Our <span>Healthcare World</span>
                            </h2>

                            <p>
                                Take a look at our pharmaceutical products, healthcare
                                solutions and the Medinef Pharma experience.
                            </p>

                        </div>
                    </div>
                </div>

                <div class="row">

                    <!-- Gallery 01 -->
                    <div class="col-md-6 col-lg-4">
                        <div class="pharma-gallery-item">
                            <img src="assets/img/gallery/01.jpg"
                                 alt="Pharmaceutical Products">
                            <div class="pharma-gallery-overlay">
                                <a href="assets/img/gallery/01.jpg"
                                   class="popup-image">
                                    <i class="far fa-search-plus"></i>
                                </a>
                            </div>
                        </div>
                        <div class="pharma-gallery-caption">
                            <h4>Pharmaceutical Products</h4>
                            <p>Quality healthcare products</p>
                        </div>
                    </div>

                    <!-- Gallery 02 -->
                    <div class="col-md-6 col-lg-4">
                        <div class="pharma-gallery-item">
                            <img src="assets/img/gallery/02.jpg"
                                 alt="Healthcare Products">
                            <div class="pharma-gallery-overlay">
                                <a href="assets/img/gallery/02.jpg"
                                   class="popup-image">
                                    <i class="far fa-search-plus"></i>
                                </a>
                            </div>
                        </div>
                        <div class="pharma-gallery-caption">
                            <h4>Healthcare Products</h4>
                            <p>Healthcare and wellness range</p>
                        </div>
                    </div>

                    <!-- Gallery 03 -->
                    <div class="col-md-6 col-lg-4">
                        <div class="pharma-gallery-item">
                            <img src="assets/img/gallery/03.jpg"
                                 alt="Medicine Collection">
                            <div class="pharma-gallery-overlay">
                                <a href="assets/img/gallery/03.jpg"
                                   class="popup-image">
                                    <i class="far fa-search-plus"></i>
                                </a>
                            </div>
                        </div>
                        <div class="pharma-gallery-caption">
                            <h4>Medicine Collection</h4>
                            <p>Pharmaceutical product range</p>
                        </div>
                    </div>

                    <!-- Gallery 04 -->
                    <div class="col-md-6 col-lg-4">
                        <div class="pharma-gallery-item">
                            <img src="assets/img/gallery/04.jpg"
                                 alt="Beauty and Personal Care">
                            <div class="pharma-gallery-overlay">
                                <a href="assets/img/gallery/04.jpg"
                                   class="popup-image">
                                    <i class="far fa-search-plus"></i>
                                </a>
                            </div>
                        </div>
                        <div class="pharma-gallery-caption">
                            <h4>Beauty & Personal Care</h4>
                            <p>Everyday personal care products</p>
                        </div>
                    </div>

                    <!-- Gallery 05 -->
                    <div class="col-md-6 col-lg-4">
                        <div class="pharma-gallery-item">
                            <img src="assets/img/gallery/05.jpg"
                                 alt="Baby and Mom Care">
                            <div class="pharma-gallery-overlay">
                                <a href="assets/img/gallery/05.jpg"
                                   class="popup-image">
                                    <i class="far fa-search-plus"></i>
                                </a>
                            </div>
                        </div>
                        <div class="pharma-gallery-caption">
                            <h4>Baby & Mom Care</h4>
                            <p>Care products for families</p>
                        </div>
                    </div>

                    <!-- Gallery 06 -->
                    <div class="col-md-6 col-lg-4">
                        <div class="pharma-gallery-item">
                            <img src="assets/img/gallery/06.jpg"
                                 alt="Medical Equipment">
                            <div class="pharma-gallery-overlay">
                                <a href="assets/img/gallery/06.jpg"
                                   class="popup-image">
                                    <i class="far fa-search-plus"></i>
                                </a>
                            </div>
                        </div>
                        <div class="pharma-gallery-caption">
                            <h4>Medical Equipment</h4>
                            <p>Healthcare and medical essentials</p>
                        </div>
                    </div>

                    <!-- Gallery 07 -->
                    <div class="col-md-6 col-lg-4">
                        <div class="pharma-gallery-item">
                            <img src="assets/img/gallery/07.jpg"
                                 alt="Food and Nutrition">
                            <div class="pharma-gallery-overlay">
                                <a href="assets/img/gallery/07.jpg"
                                   class="popup-image">
                                    <i class="far fa-search-plus"></i>
                                </a>
                            </div>
                        </div>
                        <div class="pharma-gallery-caption">
                            <h4>Food & Nutrition</h4>
                            <p>Nutrition and wellness products</p>
                        </div>
                    </div>

                    <!-- Gallery 08 -->
                    <div class="col-md-6 col-lg-4">
                        <div class="pharma-gallery-item">
                            <img src="assets/img/gallery/08.jpg"
                                 alt="Pharma Care">
                            <div class="pharma-gallery-overlay">
                                <a href="assets/img/gallery/08.jpg"
                                   class="popup-image">
                                    <i class="far fa-search-plus"></i>
                                </a>
                            </div>
                        </div>
                        <div class="pharma-gallery-caption">
                            <h4>Pharma Care</h4>
                            <p>Reliable healthcare solutions</p>
                        </div>
                    </div>

                    <!-- Gallery 09 -->
                    <div class="col-md-6 col-lg-4">
                        <div class="pharma-gallery-item">
                            <img src="assets/img/gallery/09.jpg"
                                 alt="Medinef Pharma">
                            <div class="pharma-gallery-overlay">
                                <a href="assets/img/gallery/09.jpg"
                                   class="popup-image">
                                    <i class="far fa-search-plus"></i>
                                </a>
                            </div>
                        </div>
                        <div class="pharma-gallery-caption">
                            <h4>Medinef Pharma</h4>
                            <p>Committed to better healthcare</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- gallery area end -->

        <!-- feature area -->
        <div class="feature-area">
            <div class="container wow fadeInUp" data-wow-delay=".25s">
                <div class="feature-wrap">
                    <div class="row g-0">

                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fal fa-shield-check"></i>
                                </div>
                                <div class="feature-content">
                                    <h4>Quality Products</h4>
                                    <p>Quality-Focused Service</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fal fa-box-open"></i>
                                </div>
                                <div class="feature-content">
                                    <h4>Reliable Service</h4>
                                    <p>Customer-Focused Support</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fal fa-clipboard-check"></i>
                                </div>
                                <div class="feature-content">
                                    <h4>Secure Process</h4>
                                    <p>Organized Healthcare Support</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fal fa-headset"></i>
                                </div>
                                <div class="feature-content">
                                    <h4>Customer Support</h4>
                                    <p>We Are Here To Help</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!-- feature area end -->

        <!-- instagram area -->
        <div class="instagram-area py-100">
            <div class="container wow fadeInUp" data-wow-delay=".25s">

                <div class="row">
                    <div class="col-lg-6 mx-auto">
                        <div class="site-heading text-center">
                            <h2 class="site-title">
                                Instagram <span>@medinefpharma</span>
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="instagram-slider owl-carousel owl-theme">

                    <div class="instagram-item">
                        <div class="instagram-img">
                            <img src="assets/img/instagram/01.jpg"
                                 alt="Medinef Pharma">
                            <a href="https://www.instagram.com/medinefpharmacy/">
                                <i class="fab fa-instagram"></i>
                            </a>
                        </div>
                    </div>

                    <div class="instagram-item">
                        <div class="instagram-img">
                            <img src="assets/img/instagram/02.jpg"
                                 alt="Medinef Pharma">
                            <a href="https://www.instagram.com/medinefpharmacy/">
                                <i class="fab fa-instagram"></i>
                            </a>
                        </div>
                    </div>

                    <div class="instagram-item">
                        <div class="instagram-img">
                            <img src="assets/img/instagram/03.jpg"
                                 alt="Medinef Pharma">
                            <a href="https://www.instagram.com/medinefpharmacy/">
                                <i class="fab fa-instagram"></i>
                            </a>
                        </div>
                    </div>

                    <div class="instagram-item">
                        <div class="instagram-img">
                            <img src="assets/img/instagram/04.jpg"
                                 alt="Medinef Pharma">
                            <a href="https://www.instagram.com/medinefpharmacy/">
                                <i class="fab fa-instagram"></i>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- instagram area end -->

    </main>


<?php include 'includes/footer.php'; ?>