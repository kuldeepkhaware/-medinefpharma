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
                    <h4 class="breadcrumb-title">Manufacturing</h4>
                    <ul class="breadcrumb-menu">
                        <li>
                            <a href="index.html">
                                <i class="far fa-home"></i> Home
                            </a>
                        </li>
                        <li class="active">Manufacturing</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- breadcrumb end -->

        <!-- manufacturing area -->
        <style>
            .manufacturing-area {
                padding: 90px 0 100px;
            }

            .manufacturing-heading {
                max-width: 800px;
                margin: 0 auto 55px;
            }

            .manufacturing-heading p {
                color: #65748b;
                font-size: 16px;
                line-height: 1.8;
                margin-top: 15px;
            }

            .manufacturing-intro {
                background: #f7f8fc;
                border-radius: 14px;
                padding: 45px;
                margin-bottom: 55px;
            }

            .manufacturing-intro h3 {
                color: #163b55;
                font-size: 28px;
                margin-bottom: 14px;
            }

            .manufacturing-intro p {
                color: #65748b;
                line-height: 1.85;
                margin: 0;
            }

            .manufacturing-intro-icon {
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

            .manufacturing-card {
                height: 100%;
                background: #fff;
                border: 1px solid #eee;
                border-radius: 12px;
                padding: 32px 28px;
                transition: all .35s ease;
            }

            .manufacturing-card:hover {
                transform: translateY(-7px);
                border-color: #4B4099;
                box-shadow: 0 15px 40px rgba(0, 0, 0, .08);
            }

            .manufacturing-icon {
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

            .manufacturing-card h4 {
                color: #163b55;
                font-size: 21px;
                margin-bottom: 12px;
            }

            .manufacturing-card p {
                color: #65748b;
                font-size: 15px;
                line-height: 1.8;
                margin: 0;
            }

            .manufacturing-process {
                margin-top: 60px;
            }

            .manufacturing-process-item {
                height: 100%;
                padding: 30px 25px;
                text-align: center;
                background: #fff;
                border: 1px solid #eee;
                border-radius: 12px;
            }

            .manufacturing-number {
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

            .manufacturing-process-item h4 {
                color: #163b55;
                font-size: 19px;
                margin-bottom: 10px;
            }

            .manufacturing-process-item p {
                color: #65748b;
                font-size: 14px;
                line-height: 1.75;
                margin: 0;
            }

            .manufacturing-quality {
                margin-top: 60px;
                background: #4B4099;
                border-radius: 14px;
                padding: 45px;
                text-align: center;
            }

            .manufacturing-quality h3 {
                color: #fff;
                font-size: 28px;
                margin-bottom: 12px;
            }

            .manufacturing-quality p {
                color: rgba(255,255,255,.9);
                max-width: 780px;
                margin: 0 auto;
                line-height: 1.85;
            }

            @media (max-width: 767px) {
                .manufacturing-area {
                    padding: 65px 0 75px;
                }

                .manufacturing-intro,
                .manufacturing-quality {
                    padding: 30px 22px;
                }

                .manufacturing-intro-icon {
                    margin-top: 25px;
                }
            }
        </style>

        <div class="manufacturing-area">
            <div class="container">

                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="site-heading text-center manufacturing-heading">

                            <span class="site-title-tagline">
                                Manufacturing Excellence
                            </span>

                            <h2 class="site-title">
                                Pharmaceutical <span>Manufacturing</span>
                            </h2>

                            <p>
                                Our manufacturing approach focuses on quality,
                                consistency, responsible processes and dependable
                                pharmaceutical and healthcare product support.
                            </p>

                        </div>
                    </div>
                </div>

                <!-- Intro -->
                <div class="manufacturing-intro">
                    <div class="row align-items-center">

                        <div class="col-lg-9">
                            <h3>Quality-Focused Manufacturing Approach</h3>
                            <p>
                                Manufacturing activities require organized
                                processes, appropriate handling and continuous
                                attention to quality. We focus on maintaining
                                structured practices that support consistent
                                products and dependable healthcare services.
                            </p>
                        </div>

                        <div class="col-lg-3 text-center">
                            <div class="manufacturing-intro-icon">
                                <i class="fal fa-industry"></i>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Manufacturing capabilities -->
                <div class="row g-4">

                    <div class="col-md-6 col-lg-4">
                        <div class="manufacturing-card">
                            <div class="manufacturing-icon">
                                <i class="fal fa-industry"></i>
                            </div>
                            <h4>Manufacturing Operations</h4>
                            <p>
                                Organized manufacturing activities designed to
                                support consistent pharmaceutical product
                                requirements.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="manufacturing-card">
                            <div class="manufacturing-icon">
                                <i class="fal fa-flask"></i>
                            </div>
                            <h4>Process Management</h4>
                            <p>
                                Structured processes help maintain consistency,
                                documentation and responsible operations.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="manufacturing-card">
                            <div class="manufacturing-icon">
                                <i class="fal fa-shield-check"></i>
                            </div>
                            <h4>Quality Control</h4>
                            <p>
                                Quality-focused checks and reviews support
                                dependable product and service outcomes.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="manufacturing-card">
                            <div class="manufacturing-icon">
                                <i class="fal fa-box-check"></i>
                            </div>
                            <h4>Product Handling</h4>
                            <p>
                                Careful and organized handling supports product
                                integrity throughout relevant activities.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="manufacturing-card">
                            <div class="manufacturing-icon">
                                <i class="fal fa-file-check"></i>
                            </div>
                            <h4>Documentation</h4>
                            <p>
                                Clear documentation supports traceability,
                                organization and consistent working practices.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="manufacturing-card">
                            <div class="manufacturing-icon">
                                <i class="fal fa-chart-line"></i>
                            </div>
                            <h4>Continuous Improvement</h4>
                            <p>
                                We encourage regular review and practical
                                improvements across manufacturing-related
                                processes.
                            </p>
                        </div>
                    </div>

                </div>

                <!-- Process -->
                <div class="manufacturing-process">

                    <div class="site-heading text-center mb-45">
                        <span class="site-title-tagline">
                            Our Approach
                        </span>

                        <h2 class="site-title">
                            Manufacturing <span>Process</span>
                        </h2>
                    </div>

                    <div class="row g-4">

                        <div class="col-md-6 col-lg-3">
                            <div class="manufacturing-process-item">
                                <div class="manufacturing-number">01</div>
                                <h4>Planning</h4>
                                <p>
                                    Plan requirements, resources and processes
                                    before relevant manufacturing activities.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="manufacturing-process-item">
                                <div class="manufacturing-number">02</div>
                                <h4>Production</h4>
                                <p>
                                    Follow organized processes to support
                                    consistent production activities.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="manufacturing-process-item">
                                <div class="manufacturing-number">03</div>
                                <h4>Quality Review</h4>
                                <p>
                                    Review relevant quality requirements and
                                    process information.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="manufacturing-process-item">
                                <div class="manufacturing-number">04</div>
                                <h4>Improvement</h4>
                                <p>
                                    Identify practical opportunities to improve
                                    processes and operational consistency.
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Quality commitment -->
                <div class="manufacturing-quality">
                    <h3>Committed to Quality &amp; Consistency</h3>
                    <p>
                        We aim to maintain a responsible, quality-focused approach
                        to manufacturing-related activities while continuously
                        improving our processes and supporting dependable healthcare
                        products and services.
                    </p>
                </div>

            </div>
        </div>
        <!-- manufacturing area end -->

    </main>

<?php include 'includes/footer.php'; ?>