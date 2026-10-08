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
                    <h4 class="breadcrumb-title">News</h4>
                    <ul class="breadcrumb-menu">
                        <li>
                            <a href="index.html">
                                <i class="far fa-home"></i> Home
                            </a>
                        </li>
                        <li class="active">News</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- breadcrumb end -->

        <!-- news area -->
        <style>
            .pharma-news-area {
                padding: 90px 0 100px;
            }

            .pharma-news-heading {
                max-width: 760px;
                margin: 0 auto 50px;
            }

            .pharma-news-heading p {
                color: #65748b;
                font-size: 16px;
                line-height: 1.8;
                margin-top: 15px;
            }

            .pharma-news-card {
                height: 100%;
                background: #fff;
                border: 1px solid #eee;
                border-radius: 12px;
                overflow: hidden;
                transition: all .35s ease;
            }

            .pharma-news-card:hover {
                transform: translateY(-7px);
                border-color: #4B4099;
                box-shadow: 0 15px 40px rgba(0, 0, 0, .08);
            }

            .pharma-news-img {
                position: relative;
                overflow: hidden;
            }

            .pharma-news-img img {
                width: 100%;
                height: 245px;
                object-fit: cover;
                display: block;
                transition: transform .5s ease;
            }

            .pharma-news-card:hover .pharma-news-img img {
                transform: scale(1.07);
            }

            .pharma-news-date {
                position: absolute;
                left: 18px;
                bottom: 18px;
                background: #4B4099;
                color: #fff;
                padding: 8px 14px;
                border-radius: 5px;
                font-size: 13px;
                font-weight: 600;
            }

            .pharma-news-content {
                padding: 25px 25px 28px;
            }

            .pharma-news-category {
                display: inline-block;
                color: #4B4099;
                font-size: 13px;
                font-weight: 600;
                margin-bottom: 10px;
            }

            .pharma-news-content h4 {
                font-size: 21px;
                line-height: 1.4;
                color: #163b55;
                margin-bottom: 12px;
            }

            .pharma-news-content p {
                color: #65748b;
                font-size: 15px;
                line-height: 1.8;
                margin-bottom: 18px;
            }

            .news-read-more {
                color: #4B4099;
                font-size: 14px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }

            .news-read-more:hover {
                color: #163b55;
            }

            .news-highlight {
                background: #f7f8fc;
                border-radius: 14px;
                padding: 45px;
                margin-bottom: 55px;
            }

            .news-highlight h3 {
                color: #163b55;
                font-size: 28px;
                margin-bottom: 12px;
            }

            .news-highlight p {
                color: #65748b;
                line-height: 1.8;
                margin-bottom: 0;
            }

            .news-highlight-icon {
                width: 75px;
                height: 75px;
                border-radius: 50%;
                background: #4B4099;
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 30px;
                margin: 0 auto;
            }

            @media (max-width: 767px) {
                .pharma-news-area {
                    padding: 65px 0 75px;
                }

                .news-highlight {
                    padding: 30px 22px;
                }

                .pharma-news-img img {
                    height: 225px;
                }
            }
        </style>

        <div class="pharma-news-area">
            <div class="container">

                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="site-heading text-center pharma-news-heading">

                            <span class="site-title-tagline">
                                Latest Updates
                            </span>

                            <h2 class="site-title">
                                News & <span>Updates</span>
                            </h2>

                            <p>
                                Stay informed about Medinef Pharma, healthcare
                                products, services and the latest updates from our
                                organization.
                            </p>

                        </div>
                    </div>
                </div>

                <!-- highlight -->
                <div class="news-highlight">
                    <div class="row align-items-center">

                        <div class="col-lg-2 text-center mb-4 mb-lg-0">
                            <div class="news-highlight-icon">
                                <i class="fal fa-newspaper"></i>
                            </div>
                        </div>

                        <div class="col-lg-10">
                            <h3>Healthcare News & Information</h3>
                            <p>
                                Explore our latest announcements, product updates,
                                healthcare information and company activities.
                                This section can be updated regularly with the
                                latest news from Medinef Pharma.
                            </p>
                        </div>

                    </div>
                </div>

                <!-- news cards -->
                <div class="row g-4">

                    <!-- News 01 -->
                    <div class="col-md-6 col-lg-4">
                        <article class="pharma-news-card">

                            <div class="pharma-news-img">
                                <img src="assets/img/blog/01.jpg"
                                     alt="Pharmaceutical Products">

                                <span class="pharma-news-date">
                                    Latest
                                </span>
                            </div>

                            <div class="pharma-news-content">
                                <span class="pharma-news-category">
                                    Pharmaceutical
                                </span>

                                <h4>
                                    Quality Products for Better Healthcare
                                </h4>

                                <p>
                                    Learn more about our pharmaceutical and
                                    healthcare product portfolio and our focus
                                    on dependable service.
                                </p>

                                <a href="#" class="news-read-more">
                                    Read More
                                    <i class="far fa-arrow-right"></i>
                                </a>
                            </div>

                        </article>
                    </div>

                    <!-- News 02 -->
                    <div class="col-md-6 col-lg-4">
                        <article class="pharma-news-card">

                            <div class="pharma-news-img">
                                <img src="assets/img/blog/02.jpg"
                                     alt="Healthcare Solutions">

                                <span class="pharma-news-date">
                                    Update
                                </span>
                            </div>

                            <div class="pharma-news-content">
                                <span class="pharma-news-category">
                                    Healthcare
                                </span>

                                <h4>
                                    Supporting Everyday Healthcare Needs
                                </h4>

                                <p>
                                    Discover healthcare categories and solutions
                                    designed to support everyday customer needs.
                                </p>

                                <a href="#" class="news-read-more">
                                    Read More
                                    <i class="far fa-arrow-right"></i>
                                </a>
                            </div>

                        </article>
                    </div>

                    <!-- News 03 -->
                    <div class="col-md-6 col-lg-4">
                        <article class="pharma-news-card">

                            <div class="pharma-news-img">
                                <img src="assets/img/blog/03.jpg"
                                     alt="Healthcare Awareness">

                                <span class="pharma-news-date">
                                    News
                                </span>
                            </div>

                            <div class="pharma-news-content">
                                <span class="pharma-news-category">
                                    Wellness
                                </span>

                                <h4>
                                    Healthcare & Wellness Information
                                </h4>

                                <p>
                                    Read useful updates about healthcare,
                                    wellness and products available through
                                    our platform.
                                </p>

                                <a href="#" class="news-read-more">
                                    Read More
                                    <i class="far fa-arrow-right"></i>
                                </a>
                            </div>

                        </article>
                    </div>

                    <!-- News 04 -->
                    <div class="col-md-6 col-lg-4">
                        <article class="pharma-news-card">

                            <div class="pharma-news-img">
                                <img src="assets/img/blog/04.jpg"
                                     alt="Medicine Collection">

                                <span class="pharma-news-date">
                                    Update
                                </span>
                            </div>

                            <div class="pharma-news-content">
                                <span class="pharma-news-category">
                                    Products
                                </span>

                                <h4>
                                    Explore Our Growing Product Range
                                </h4>

                                <p>
                                    Get updates about product categories and
                                    healthcare essentials available from
                                    Medinef Pharma.
                                </p>

                                <a href="#" class="news-read-more">
                                    Read More
                                    <i class="far fa-arrow-right"></i>
                                </a>
                            </div>

                        </article>
                    </div>

                    <!-- News 05 -->
                    <div class="col-md-6 col-lg-4">
                        <article class="pharma-news-card">

                            <div class="pharma-news-img">
                                <img src="assets/img/blog/05.jpg"
                                     alt="Customer Service">

                                <span class="pharma-news-date">
                                    News
                                </span>
                            </div>

                            <div class="pharma-news-content">
                                <span class="pharma-news-category">
                                    Service
                                </span>

                                <h4>
                                    Customer-Focused Healthcare Service
                                </h4>

                                <p>
                                    Our service approach is focused on helping
                                    customers find suitable products and
                                    information.
                                </p>

                                <a href="#" class="news-read-more">
                                    Read More
                                    <i class="far fa-arrow-right"></i>
                                </a>
                            </div>

                        </article>
                    </div>

                    <!-- News 06 -->
                    <div class="col-md-6 col-lg-4">
                        <article class="pharma-news-card">

                            <div class="pharma-news-img">
                                <img src="assets/img/blog/06.jpg"
                                     alt="Medinef Pharma News">

                                <span class="pharma-news-date">
                                    News
                                </span>
                            </div>

                            <div class="pharma-news-content">
                                <span class="pharma-news-category">
                                    Company
                                </span>

                                <h4>
                                    Updates From Medinef Pharma
                                </h4>

                                <p>
                                    Follow this space for company announcements,
                                    new services, activities and other updates.
                                </p>

                                <a href="#" class="news-read-more">
                                    Read More
                                    <i class="far fa-arrow-right"></i>
                                </a>
                            </div>

                        </article>
                    </div>

                </div>

            </div>
        </div>
        <!-- news area end -->

    </main>


<?php include 'includes/footer.php'; ?>