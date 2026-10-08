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
                    <h4 class="breadcrumb-title">Certifications</h4>
                    <ul class="breadcrumb-menu">
                        <li>
                            <a href="index.php">
                                <i class="far fa-home"></i> Home
                            </a>
                        </li>
                        <li class="active">Certifications</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- breadcrumb end -->

        <!-- certifications area -->
        <style>
            .certifications-area {
                padding: 90px 0 100px;
            }

            .certifications-heading {
                max-width: 790px;
                margin: 0 auto 55px;
            }

            .certifications-heading p {
                color: #65748b;
                font-size: 16px;
                line-height: 1.8;
                margin-top: 15px;
            }

            .certifications-intro {
                background: #f7f8fc;
                border-radius: 14px;
                padding: 45px;
                margin-bottom: 55px;
            }

            .certifications-intro h3 {
                color: #163b55;
                font-size: 28px;
                margin-bottom: 14px;
            }

            .certifications-intro p {
                color: #65748b;
                line-height: 1.85;
                margin: 0;
            }

            .certifications-intro-icon {
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

            .certificate-card {
                height: 100%;
                background: #fff;
                border: 1px solid #eee;
                border-radius: 12px;
                padding: 32px 28px;
                text-align: center;
                transition: all .35s ease;
            }

            .certificate-card:hover {
                transform: translateY(-7px);
                border-color: #4B4099;
                box-shadow: 0 15px 40px rgba(0, 0, 0, .08);
            }

            .certificate-icon {
                width: 75px;
                height: 75px;
                border-radius: 50%;
                background: #4B4099;
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 30px;
                margin: 0 auto 22px;
            }

            .certificate-card h4 {
                color: #163b55;
                font-size: 21px;
                margin-bottom: 12px;
            }

            .certificate-card p {
                color: #65748b;
                font-size: 15px;
                line-height: 1.8;
                margin: 0;
            }

            .certification-note {
                margin-top: 55px;
                background: #fff;
                border: 1px solid #eee;
                border-radius: 12px;
                padding: 35px;
            }

            .certification-note h4 {
                color: #163b55;
                font-size: 22px;
                margin-bottom: 12px;
            }

            .certification-note p {
                color: #65748b;
                line-height: 1.8;
                margin: 0;
            }

            .certification-values {
                margin-top: 60px;
            }

            .certification-value {
                height: 100%;
                padding: 30px 25px;
                background: #fff;
                border: 1px solid #eee;
                border-radius: 12px;
                text-align: center;
            }

            .certification-value .number {
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

            .certification-value h4 {
                color: #163b55;
                font-size: 19px;
                margin-bottom: 10px;
            }

            .certification-value p {
                color: #65748b;
                font-size: 14px;
                line-height: 1.75;
                margin: 0;
            }

            .certification-commitment {
                margin-top: 60px;
                background: #4B4099;
                border-radius: 14px;
                padding: 45px;
                text-align: center;
            }

            .certification-commitment h3 {
                color: #fff;
                font-size: 28px;
                margin-bottom: 12px;
            }

            .certification-commitment p {
                color: rgba(255,255,255,.9);
                max-width: 760px;
                margin: 0 auto;
                line-height: 1.85;
            }

            @media (max-width: 767px) {
                .certifications-area {
                    padding: 65px 0 75px;
                }

                .certifications-intro,
                .certification-note,
                .certification-commitment {
                    padding: 30px 22px;
                }

                .certifications-intro-icon {
                    margin-top: 25px;
                }
            }
        </style>

        <div class="certifications-area">
            <div class="container">

                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="site-heading text-center certifications-heading">

                            <span class="site-title-tagline">
                                Trust &amp; Compliance
                            </span>

                            <h2 class="site-title">
                                Our <span>Certifications</span>
                            </h2>

                            <p>
                                We believe in maintaining responsible practices,
                                quality-focused processes and appropriate standards
                                across our pharmaceutical and healthcare activities.
                            </p>

                        </div>
                    </div>
                </div>

                <!-- Intro -->
                <div class="certifications-intro">
                    <div class="row align-items-center">

                        <div class="col-lg-9">
                            <h3>Commitment to Standards</h3>
                            <p>
                                Certifications and compliance practices help
                                demonstrate our commitment to organized processes,
                                quality management and responsible business
                                operations. Relevant certificates can be displayed
                                here for customers and business partners to review.
                            </p>
                        </div>

                        <div class="col-lg-3 text-center">
                            <div class="certifications-intro-icon">
                                <i class="fal fa-certificate"></i>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Certification cards -->
                <div class="row g-4">

                    <div class="col-md-6 col-lg-4">
                        <div class="certificate-card">
                            <div class="certificate-icon">
                                <i class="fal fa-award"></i>
                            </div>
                            <h4>Quality Certification</h4>
                            <p>
                                Space for an applicable quality certification
                                and its verified details.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="certificate-card">
                            <div class="certificate-icon">
                                <i class="fal fa-file-certificate"></i>
                            </div>
                            <h4>Compliance Documents</h4>
                            <p>
                                Relevant compliance documents can be presented
                                here for customers and partners.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="certificate-card">
                            <div class="certificate-icon">
                                <i class="fal fa-shield-check"></i>
                            </div>
                            <h4>Quality Standards</h4>
                            <p>
                                Information about applicable quality standards
                                followed by the organization.
                            </p>
                        </div>
                    </div>

                </div>

                <!-- Note -->
                <div class="certification-note">
                    <h4>Certificate Information</h4>
                    <p>
                        Add the actual certificate name, certificate number,
                        issuing organization, issue date and validity period
                        here once the official certification documents are
                        available. This section is intentionally kept factual
                        and can be updated with verified certificate details.
                    </p>
                </div>

                <!-- Values -->
                <div class="certification-values">

                    <div class="site-heading text-center mb-45">
                        <span class="site-title-tagline">
                            Our Commitment
                        </span>

                        <h2 class="site-title">
                            Standards We <span>Value</span>
                        </h2>
                    </div>

                    <div class="row g-4">

                        <div class="col-md-6 col-lg-3">
                            <div class="certification-value">
                                <div class="number">01</div>
                                <h4>Quality</h4>
                                <p>
                                    A quality-focused approach across products
                                    and services.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="certification-value">
                                <div class="number">02</div>
                                <h4>Compliance</h4>
                                <p>
                                    Responsible attention to applicable
                                    requirements and processes.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="certification-value">
                                <div class="number">03</div>
                                <h4>Transparency</h4>
                                <p>
                                    Clear and accurate information about
                                    applicable certifications.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="certification-value">
                                <div class="number">04</div>
                                <h4>Improvement</h4>
                                <p>
                                    Continuous improvement of quality-focused
                                    business practices.
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Commitment -->
                <div class="certification-commitment">
                    <h3>Building Trust Through Quality</h3>
                    <p>
                        We aim to maintain dependable processes and transparent
                        information so that customers and business partners can
                        make informed decisions about our products and services.
                    </p>
                </div>

            </div>
        </div>
        <!-- certifications area end -->

    </main>


<?php include 'includes/footer.php'; ?>