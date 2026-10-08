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
                    <h4 class="breadcrumb-title">Careers</h4>
                    <ul class="breadcrumb-menu">
                        <li>
                            <a href="index.html">
                                <i class="far fa-home"></i> Home
                            </a>
                        </li>
                        <li class="active">Careers</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- breadcrumb end -->

        <!-- careers area -->
        <style>
            .careers-area {
                padding: 90px 0 100px;
            }

            .careers-heading {
                max-width: 760px;
                margin: 0 auto 55px;
            }

            .careers-heading p {
                color: #65748b;
                font-size: 16px;
                line-height: 1.8;
                margin-top: 15px;
            }

            .career-intro {
                background: #f7f8fc;
                border-radius: 14px;
                padding: 45px;
                margin-bottom: 45px;
            }

            .career-intro h3 {
                color: #163b55;
                font-size: 28px;
                margin-bottom: 15px;
            }

            .career-intro p {
                color: #65748b;
                line-height: 1.8;
                margin-bottom: 0;
            }

            .career-card {
                height: 100%;
                background: #fff;
                border: 1px solid #eee;
                border-radius: 12px;
                padding: 32px 28px;
                transition: all .35s ease;
            }

            .career-card:hover {
                transform: translateY(-7px);
                border-color: #4B4099;
                box-shadow: 0 15px 40px rgba(0, 0, 0, .08);
            }

            .career-icon {
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

            .career-card h4 {
                color: #163b55;
                font-size: 21px;
                margin-bottom: 12px;
            }

            .career-card p {
                color: #65748b;
                font-size: 15px;
                line-height: 1.8;
                margin-bottom: 0;
            }

            .open-position {
                margin-top: 55px;
            }

            .position-card {
                background: #fff;
                border: 1px solid #e8e8ef;
                border-radius: 12px;
                padding: 28px 30px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 25px;
                margin-bottom: 20px;
            }

            .position-card h4 {
                color: #163b55;
                font-size: 20px;
                margin-bottom: 7px;
            }

            .position-card p {
                color: #65748b;
                margin: 0;
                line-height: 1.7;
            }

            .career-btn {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: #4B4099;
                color: #fff;
                padding: 12px 22px;
                border-radius: 6px;
                white-space: nowrap;
                transition: all .3s ease;
            }

            .career-btn:hover {
                color: #fff;
                background: #163b55;
            }

            .career-apply {
                background: #4B4099;
                border-radius: 14px;
                padding: 45px;
                margin-top: 50px;
                text-align: center;
            }

            .career-apply h3 {
                color: #fff;
                font-size: 28px;
                margin-bottom: 12px;
            }

            .career-apply p {
                color: rgba(255,255,255,.9);
                line-height: 1.8;
                max-width: 700px;
                margin: 0 auto 22px;
            }

            .career-apply .career-btn {
                background: #fff;
                color: #4B4099;
            }

            .career-apply .career-btn:hover {
                background: #163b55;
                color: #fff;
            }

            @media (max-width: 767px) {
                .careers-area {
                    padding: 65px 0 75px;
                }

                .career-intro,
                .career-apply {
                    padding: 30px 22px;
                }

                .position-card {
                    display: block;
                }

                .position-card .career-btn {
                    margin-top: 18px;
                }
            }
        </style>

        <div class="careers-area">
            <div class="container">

                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="site-heading text-center careers-heading">

                            <span class="site-title-tagline">
                                Join Our Team
                            </span>

                            <h2 class="site-title">
                                Build Your <span>Career With Us</span>
                            </h2>

                            <p>
                                Join a team working to support pharmaceutical and
                                healthcare needs through quality products,
                                dependable service and customer-focused solutions.
                            </p>

                        </div>
                    </div>
                </div>

                <!-- Intro -->
                <div class="career-intro">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h3>Grow With Medinef Pharma</h3>
                            <p>
                                We value people who are responsible, committed and
                                willing to learn. Our workplace encourages teamwork,
                                professional growth and a customer-focused approach
                                to healthcare services.
                            </p>
                        </div>

                        <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                            <i class="fal fa-users-medical"
                               style="font-size:80px;color:#4B4099;"></i>
                        </div>
                    </div>
                </div>

                <!-- Why join us -->
                <div class="row g-4">

                    <div class="col-md-6 col-lg-4">
                        <div class="career-card">
                            <div class="career-icon">
                                <i class="fal fa-users"></i>
                            </div>
                            <h4>Team Environment</h4>
                            <p>
                                Work with a collaborative team that values
                                communication, responsibility and mutual support.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="career-card">
                            <div class="career-icon">
                                <i class="fal fa-chart-line"></i>
                            </div>
                            <h4>Professional Growth</h4>
                            <p>
                                Opportunities to learn new skills and develop
                                professionally through meaningful work.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="career-card">
                            <div class="career-icon">
                                <i class="fal fa-lightbulb-on"></i>
                            </div>
                            <h4>Learning & Ideas</h4>
                            <p>
                                We encourage practical ideas, continuous learning
                                and improvements in our everyday work.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="career-card">
                            <div class="career-icon">
                                <i class="fal fa-handshake"></i>
                            </div>
                            <h4>Work Together</h4>
                            <p>
                                Teamwork and clear communication help us deliver
                                dependable service to our customers.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="career-card">
                            <div class="career-icon">
                                <i class="fal fa-bullseye-arrow"></i>
                            </div>
                            <h4>Customer Focus</h4>
                            <p>
                                Every role contributes to providing a better
                                experience for healthcare customers and partners.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="career-card">
                            <div class="career-icon">
                                <i class="fal fa-award"></i>
                            </div>
                            <h4>Quality Mindset</h4>
                            <p>
                                We encourage attention to quality, consistency and
                                responsible professional practices.
                            </p>
                        </div>
                    </div>

                </div>

                <!-- Current openings -->
                <div class="open-position">

                    <div class="site-heading text-center mb-45">
                        <span class="site-title-tagline">
                            Opportunities
                        </span>

                        <h2 class="site-title">
                            Current <span>Openings</span>
                        </h2>
                    </div>

                    <div class="position-card">
                        <div>
                            <h4>Sales & Business Development</h4>
                            <p>
                                Explore opportunities in pharmaceutical and
                                healthcare product sales and customer relationships.
                            </p>
                        </div>

                        <a href="contact.html" class="career-btn">
                            Apply Now <i class="far fa-arrow-right"></i>
                        </a>
                    </div>

                    <div class="position-card">
                        <div>
                            <h4>Customer Support</h4>
                            <p>
                                Support customers with product enquiries,
                                communication and service-related requirements.
                            </p>
                        </div>

                        <a href="contact.html" class="career-btn">
                            Apply Now <i class="far fa-arrow-right"></i>
                        </a>
                    </div>

                    <div class="position-card">
                        <div>
                            <h4>Operations & Administration</h4>
                            <p>
                                Help coordinate day-to-day operations and maintain
                                organized business processes.
                            </p>
                        </div>

                        <a href="contact.html" class="career-btn">
                            Apply Now <i class="far fa-arrow-right"></i>
                        </a>
                    </div>

                </div>

                <!-- Apply -->
                <div class="career-apply">
                    <h3>Interested in Joining Us?</h3>

                    <p>
                        Send us your updated resume and a brief introduction.
                        Our team can review your profile for suitable opportunities.
                    </p>

                    <a href="contact.html" class="career-btn">
                        Send Your Resume
                        <i class="far fa-paper-plane"></i>
                    </a>
                </div>

            </div>
        </div>
        <!-- careers area end -->

    </main>


<?php include 'includes/footer.php'; ?>