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
                    <h4 class="breadcrumb-title">Quality</h4>
                    <ul class="breadcrumb-menu">
                        <li>
                            <a href="index.php">
                                <i class="far fa-home"></i> Home
                            </a>
                        </li>
                        <li class="active">Quality</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- breadcrumb end -->

        <!-- quality area -->
        <style>
            .quality-area {
                padding: 90px 0 100px;
            }

            .quality-heading {
                max-width: 790px;
                margin: 0 auto 55px;
            }

            .quality-heading p {
                color: #65748b;
                font-size: 16px;
                line-height: 1.8;
                margin-top: 15px;
            }

            .quality-intro {
                background: #f7f8fc;
                border-radius: 14px;
                padding: 45px;
                margin-bottom: 55px;
            }

            .quality-intro h3 {
                color: #163b55;
                font-size: 28px;
                margin-bottom: 14px;
            }

            .quality-intro p {
                color: #65748b;
                line-height: 1.85;
                margin: 0;
            }

            .quality-intro-icon {
                width: 90px;
                height: 90px;
                border-radius: 50%;
                background: #4B4099;
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 38px;
                margin: 0 auto;
            }

            .quality-card {
                height: 100%;
                background: #fff;
                border: 1px solid #eee;
                border-radius: 12px;
                padding: 32px 28px;
                transition: all .35s ease;
            }

            .quality-card:hover {
                transform: translateY(-7px);
                border-color: #4B4099;
                box-shadow: 0 15px 40px rgba(0, 0, 0, .08);
            }

            .quality-icon {
                width: 68px;
                height: 68px;
                border-radius: 50%;
                background: #4B4099;
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 27px;
                margin-bottom: 22px;
            }

            .quality-card h4 {
                color: #163b55;
                font-size: 21px;
                margin-bottom: 12px;
            }

            .quality-card p {
                color: #65748b;
                font-size: 15px;
                line-height: 1.8;
                margin: 0;
            }

            .quality-principles {
                margin-top: 60px;
            }

            .quality-principle {
                height: 100%;
                padding: 30px 25px;
                text-align: center;
                background: #fff;
                border: 1px solid #eee;
                border-radius: 12px;
            }

            .quality-number {
                width: 45px;
                height: 45px;
                margin: 0 auto 18px;
                border-radius: 50%;
                background: #4B4099;
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 600;
            }

            .quality-principle h4 {
                color: #163b55;
                font-size: 19px;
                margin-bottom: 10px;
            }

            .quality-principle p {
                color: #65748b;
                font-size: 14px;
                line-height: 1.75;
                margin: 0;
            }

            .quality-commitment {
                margin-top: 60px;
                background: #4B4099;
                border-radius: 14px;
                padding: 45px;
                text-align: center;
            }

            .quality-commitment h3 {
                color: #fff;
                font-size: 28px;
                margin-bottom: 12px;
            }

            .quality-commitment p {
                color: rgba(255,255,255,.9);
                max-width: 760px;
                margin: 0 auto;
                line-height: 1.85;
            }

            @media (max-width: 767px) {
                .quality-area {
                    padding: 65px 0 75px;
                }

                .quality-intro,
                .quality-commitment {
                    padding: 30px 22px;
                }

                .quality-intro-icon {
                    margin-top: 25px;
                }
            }
        </style>

        <div class="quality-area">
            <div class="container">

                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="site-heading text-center quality-heading">

                            <span class="site-title-tagline">
                                Quality Commitment
                            </span>

                            <h2 class="site-title">
                                Quality &amp; <span>Excellence</span>
                            </h2>

                            <p>
                                Quality is an important part of our approach to
                                pharmaceutical and healthcare products, services
                                and customer experience.
                            </p>

                        </div>
                    </div>
                </div>

                <!-- Intro -->
                <div class="quality-intro">
                    <div class="row align-items-center">

                        <div class="col-lg-9">
                            <h3>Our Commitment to Quality</h3>
                            <p>
                                We follow a quality-focused approach across our
                                product and service activities. Our aim is to
                                maintain consistency, responsible practices and
                                dependable support while meeting the needs of
                                our customers and healthcare partners.
                            </p>
                        </div>

                        <div class="col-lg-3 text-center">
                            <div class="quality-intro-icon">
                                <i class="fal fa-shield-check"></i>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Quality focus -->
                <div class="row g-4">

                    <div class="col-md-6 col-lg-4">
                        <div class="quality-card">
                            <div class="quality-icon">
                                <i class="fal fa-badge-check"></i>
                            </div>
                            <h4>Quality Products</h4>
                            <p>
                                We maintain a quality-focused approach to the
                                pharmaceutical and healthcare products we offer.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="quality-card">
                            <div class="quality-icon">
                                <i class="fal fa-clipboard-check"></i>
                            </div>
                            <h4>Process Focus</h4>
                            <p>
                                Organized processes help us maintain consistency
                                across day-to-day product and service activities.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="quality-card">
                            <div class="quality-icon">
                                <i class="fal fa-search"></i>
                            </div>
                            <h4>Regular Review</h4>
                            <p>
                                We encourage regular review of products, processes
                                and customer requirements for continuous improvement.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="quality-card">
                            <div class="quality-icon">
                                <i class="fal fa-user-check"></i>
                            </div>
                            <h4>Customer Focus</h4>
                            <p>
                                Customer requirements remain an important part of
                                our quality and service approach.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="quality-card">
                            <div class="quality-icon">
                                <i class="fal fa-file-check"></i>
                            </div>
                            <h4>Responsible Practices</h4>
                            <p>
                                We emphasize responsible working practices and
                                organized handling of information and products.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="quality-card">
                            <div class="quality-icon">
                                <i class="fal fa-chart-line"></i>
                            </div>
                            <h4>Continuous Improvement</h4>
                            <p>
                                We seek practical opportunities to improve our
                                products, processes and customer service.
                            </p>
                        </div>
                    </div>

                </div>

                <!-- Quality principles -->
                <div class="quality-principles">

                    <div class="site-heading text-center mb-45">
                        <span class="site-title-tagline">
                            Our Approach
                        </span>

                        <h2 class="site-title">
                            Quality <span>Principles</span>
                        </h2>
                    </div>

                    <div class="row g-4">

                        <div class="col-md-6 col-lg-3">
                            <div class="quality-principle">
                                <div class="quality-number">01</div>
                                <h4>Consistency</h4>
                                <p>
                                    Focus on consistent products, processes
                                    and service.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="quality-principle">
                                <div class="quality-number">02</div>
                                <h4>Responsibility</h4>
                                <p>
                                    Responsible practices across our business
                                    and service activities.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="quality-principle">
                                <div class="quality-number">03</div>
                                <h4>Customer Needs</h4>
                                <p>
                                    Understand customer requirements and support
                                    them with dependable service.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="quality-principle">
                                <div class="quality-number">04</div>
                                <h4>Improvement</h4>
                                <p>
                                    Keep learning and improving our products,
                                    processes and services.
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Commitment -->
                <div class="quality-commitment">
                    <h3>Quality at the Heart of Our Service</h3>
                    <p>
                        We believe that a quality-focused approach helps build
                        dependable relationships with customers and healthcare
                        partners. We remain committed to learning, reviewing
                        and improving our way of working.
                    </p>
                </div>

            </div>
        </div>
        <!-- quality area end -->

    </main>


<?php include 'includes/footer.php'; ?>