
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
            <div class="site-breadcrumb-bg" style="background: url(assets/img/breadcrumb/01.jpg)"></div>
            <div class="container">
                <div class="site-breadcrumb-wrap">
                    <h4 class="breadcrumb-title">Our Facilities</h4>
                    <ul class="breadcrumb-menu">
                        <li><a href="index.html"><i class="far fa-home"></i> Home</a></li>
                        <li class="active">Our Facilities</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- breadcrumb end -->

        <!-- facilities intro -->
        <div class="about-area py-100">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="about-left wow fadeInLeft" data-wow-delay=".25s">
                            <div class="about-img">
                                <div class="row">
                                    <div class="col-7">
                                        <img class="img-1" src="assets/img/about/01.jpg" alt="Medinef Pharma Facility">
                                    </div>
                                    <div class="col-5 align-self-end">
                                        <img class="img-2" src="assets/img/about/02.jpg" alt="Pharmaceutical Facility">
                                    </div>
                                </div>
                            </div>
                            <div class="about-experience">
                                <div class="about-experience-icon">
                                    <img src="assets/img/icon/experience.svg" alt="">
                                </div>
                                <b>Quality & <br> Reliability</b>
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
                                    <i class="flaticon-drive"></i> Our Facilities
                                </span>
                                <h2 class="site-title">
                                    Facilities Built Around <span>Quality Healthcare</span>
                                </h2>
                            </div>

                            <p>
                                Medinef Pharma focuses on quality pharmaceutical products and healthcare
                                solutions with an emphasis on reliability, quality and customer satisfaction.
                                Our facilities and service processes are designed to support an organized,
                                dependable and customer-focused healthcare experience.
                            </p>

                            <div class="about-list">
                                <ul>
                                    <li><i class="fas fa-check-double"></i> Quality-Focused Product Handling</li>
                                    <li><i class="fas fa-check-double"></i> Organized Pharmaceutical Operations</li>
                                    <li><i class="fas fa-check-double"></i> Reliable Healthcare Support</li>
                                    <li><i class="fas fa-check-double"></i> Customer-Focused Service</li>
                                </ul>
                            </div>

                            <a href="contact.html" class="theme-btn mt-4">
                                Contact Us <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- facilities intro end -->

      <!-- facilities area -->
<style>
    .facilities-section {
        padding: 90px 0 100px;
    }

    .facilities-heading {
        max-width: 750px;
        margin: 0 auto 50px;
    }

    .facilities-heading .site-title-tagline {
        margin-bottom: 12px;
    }

    .facilities-heading p {
        color: #65748b;
        font-size: 17px;
        line-height: 1.8;
        margin-top: 15px;
    }

    .facility-card {
        height: 100%;
        background: #fff;
        border-radius: 12px;
        padding: 35px 30px;
        text-align: center;
        border: 1px solid #eee;
        transition: all .35s ease;
        position: relative;
        overflow: hidden;
    }

    .facility-card:before {
        content: "";
        position: absolute;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: rgba(75, 64, 153, 0.06);
        top: -45px;
        right: -45px;
        transition: all .35s ease;
    }

    .facility-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
        border-color: #4B4099;
    }

    .facility-card:hover:before {
        transform: scale(1.5);
    }

    .facility-icon {
        width: 75px;
        height: 75px;
        margin: 0 auto 22px;
        border-radius: 50%;
        background: #4B4099;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        z-index: 1;
    }

    .facility-icon i {
        font-size: 31px;
        color: #fff;
        line-height: 1;
    }

    .facility-content {
        position: relative;
        z-index: 2;
    }

    .facility-content h4 {
        font-size: 21px;
        font-weight: 600;
        color: #163b55;
        margin-bottom: 12px;
    }

    .facility-content p {
        color: #65748b;
        font-size: 15px;
        line-height: 1.8;
        margin: 0;
    }

    @media (max-width: 991px) {
        .facilities-section {
            padding: 70px 0 80px;
        }

        .facility-card {
            padding: 30px 25px;
        }
    }

    @media (max-width: 575px) {
        .facilities-section {
            padding: 60px 0 70px;
        }

        .facilities-heading p {
            font-size: 15px;
        }

        .facility-card {
            padding: 28px 22px;
        }
    }
</style>

<div class="facilities-section">
    <div class="container">

        <!-- heading -->
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="site-heading text-center facilities-heading">

                    <span class="site-title-tagline">
                        Our Capabilities
                    </span>

                    <h2 class="site-title">
                        Our <span>Facilities</span>
                    </h2>

                    <p>
                        We support pharmaceutical and healthcare requirements through
                        quality-focused processes and dependable customer service.
                    </p>

                </div>
            </div>
        </div>

        <!-- facilities -->
        <div class="row g-4">

            <!-- Quality Focus -->
            <div class="col-md-6 col-lg-4">
                <div class="facility-card">

                    <div class="facility-icon">
                        <i class="fal fa-shield-check"></i>
                    </div>

                    <div class="facility-content">
                        <h4>Quality Focus</h4>
                        <p>
                            Quality-focused practices for pharmaceutical and
                            healthcare products.
                        </p>
                    </div>

                </div>
            </div>

            <!-- Product Handling -->
            <div class="col-md-6 col-lg-4">
                <div class="facility-card">

                    <div class="facility-icon">
                        <i class="fal fa-box-open"></i>
                    </div>

                    <div class="facility-content">
                        <h4>Product Handling</h4>
                        <p>
                            Organized handling of products to support dependable
                            service and fulfilment.
                        </p>
                    </div>

                </div>
            </div>

            <!-- Pharma Portfolio -->
            <div class="col-md-6 col-lg-4">
                <div class="facility-card">

                    <div class="facility-icon">
                        <i class="fal fa-pills"></i>
                    </div>

                    <div class="facility-content">
                        <h4>Pharma Portfolio</h4>
                        <p>
                            A focused range of pharmaceutical and healthcare
                            product categories.
                        </p>
                    </div>

                </div>
            </div>

            <!-- Order Processing -->
            <div class="col-md-6 col-lg-4">
                <div class="facility-card">

                    <div class="facility-icon">
                        <i class="fal fa-clipboard-check"></i>
                    </div>

                    <div class="facility-content">
                        <h4>Order Processing</h4>
                        <p>
                            Structured processes to keep product requests and
                            orders organized.
                        </p>
                    </div>

                </div>
            </div>

            <!-- Customer Support -->
            <div class="col-md-6 col-lg-4">
                <div class="facility-card">

                    <div class="facility-icon">
                        <i class="fal fa-headset"></i>
                    </div>

                    <div class="facility-content">
                        <h4>Customer Support</h4>
                        <p>
                            Support for product information, enquiries and
                            healthcare-related queries.
                        </p>
                    </div>

                </div>
            </div>

            <!-- Reliable Service -->
            <div class="col-md-6 col-lg-4">
                <div class="facility-card">

                    <div class="facility-icon">
                        <i class="fal fa-truck"></i>
                    </div>

                    <div class="facility-content">
                        <h4>Reliable Service</h4>
                        <p>
                            A customer-focused approach for dependable
                            pharmaceutical service.
                        </p>
                    </div>

                </div>
            </div>

        </div>

    </div>
</div>
<!-- facilities area end -->

 <!-- why us area -->
<div class="counter-area pt-50 pb-50">
    <div class="container">
        <div class="row">

            <!-- Quality Products -->
            <div class="col-lg-3 col-sm-6">
                <div class="counter-box">

                    <div class="icon">
                        <i class="fal fa-box-check"></i>
                    </div>

                    <div class="counter-info">
                        <div class="counter-amount">
                            <span class="counter"
                                  data-count="+"
                                  data-to="100"
                                  data-speed="3000">100</span>
                            <span class="counter-sign">+</span>
                        </div>

                        <h6 class="title">Quality Products</h6>
                    </div>

                </div>
            </div>

            <!-- Healthcare Solutions -->
            <div class="col-lg-3 col-sm-6">
                <div class="counter-box">

                    <div class="icon">
                        <i class="fal fa-pills"></i>
                    </div>

                    <div class="counter-info">
                        <div class="counter-amount">
                            <span class="counter"
                                  data-count="+"
                                  data-to="25"
                                  data-speed="3000">25</span>
                            <span class="counter-sign">+</span>
                        </div>

                        <h6 class="title">Healthcare Solutions</h6>
                    </div>

                </div>
            </div>

            <!-- Customer Support -->
            <div class="col-lg-3 col-sm-6">
                <div class="counter-box">

                    <div class="icon">
                        <i class="fal fa-headset"></i>
                    </div>

                    <div class="counter-info">
                        <div class="counter-amount">
                            <span class="counter"
                                  data-count="+"
                                  data-to="100"
                                  data-speed="3000">100</span>
                            <span class="counter-sign">+</span>
                        </div>

                        <h6 class="title">Customer Support</h6>
                    </div>

                </div>
            </div>

            <!-- Quality Focus -->
            <div class="col-lg-3 col-sm-6">
                <div class="counter-box">

                    <div class="icon">
                        <i class="fal fa-award"></i>
                    </div>

                    <div class="counter-info">
                        <div class="counter-amount">
                            <span class="counter"
                                  data-count="+"
                                  data-to="100"
                                  data-speed="3000">100</span>
                            <span class="counter-sign">%</span>
                        </div>

                        <h6 class="title">Quality Focus</h6>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
<!-- why us area end -->

        <!-- video area -->
        <div class="video-area">
            <div class="container-fluid px-0">
                <div class="video-content" style="background-image: url(assets/img/video/01.jpg);">
                    <div class="video-wrapper">
                        <a class="play-btn popup-youtube" href="https://www.youtube.com/watch?v=ckHzmP1evNU">
                            <i class="fas fa-play"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- video area end -->

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

        <!-- instagram-area -->
        <div class="instagram-area py-100">
            <div class="container wow fadeInUp" data-wow-delay=".25s">
                <div class="row">
                    <div class="col-lg-6 mx-auto">
                        <div class="site-heading text-center">
                            <h2 class="site-title">Instagram <span>@medinefpharma</span></h2>
                        </div>
                    </div>
                </div>

                <div class="instagram-slider owl-carousel owl-theme">
                    <div class="instagram-item">
                        <div class="instagram-img">
                            <img src="assets/img/instagram/01.jpg" alt="Medinef Pharma">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="instagram-item">
                        <div class="instagram-img">
                            <img src="assets/img/instagram/02.jpg" alt="Medinef Pharma">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="instagram-item">
                        <div class="instagram-img">
                            <img src="assets/img/instagram/03.jpg" alt="Medinef Pharma">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="instagram-item">
                        <div class="instagram-img">
                            <img src="assets/img/instagram/04.jpg" alt="Medinef Pharma">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="instagram-item">
                        <div class="instagram-img">
                            <img src="assets/img/instagram/05.jpg" alt="Medinef Pharma">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="instagram-item">
                        <div class="instagram-img">
                            <img src="assets/img/instagram/06.jpg" alt="Medinef Pharma">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="instagram-item">
                        <div class="instagram-img">
                            <img src="assets/img/instagram/07.jpg" alt="Medinef Pharma">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- instagram-area end -->

        <!-- brand area -->
        <div class="brand-area bg pt-50 pb-50">
            <div class="container wow fadeInUp" data-wow-delay=".25s">
                <div class="row">
                    <div class="col-12">
                        <div class="text-center">
                            <h2 class="site-title">Quality <span>Healthcare</span> Solutions</h2>
                        </div>
                    </div>
                </div>

                <div class="brand-slider pt-40 pb-40 owl-carousel owl-theme">
                    <div class="brand-item"><img src="assets/img/brand/01.png" alt=""></div>
                    <div class="brand-item"><img src="assets/img/brand/02.png" alt=""></div>
                    <div class="brand-item"><img src="assets/img/brand/03.png" alt=""></div>
                    <div class="brand-item"><img src="assets/img/brand/04.png" alt=""></div>
                    <div class="brand-item"><img src="assets/img/brand/05.png" alt=""></div>
                    <div class="brand-item"><img src="assets/img/brand/06.png" alt=""></div>
                </div>

                <div class="text-center">
                    <a href="products.html" class="theme-btn">
                        View Our Products <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <!-- brand area end -->

    </main>



<?php include 'includes/footer.php'; ?>