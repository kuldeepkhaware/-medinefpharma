
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


    <main class="main">

        <!-- breadcrumb -->
        <div class="site-breadcrumb">
            <div class="site-breadcrumb-bg" style="background: url(assets/img/breadcrumb/01.jpg)"></div>
            <div class="container">
                <div class="site-breadcrumb-wrap">
                    <h4 class="breadcrumb-title">About Us</h4>
                    <ul class="breadcrumb-menu">
                        <li><a href="index.php"><i class="far fa-home"></i> Home</a></li>
                        <li class="active">About Us</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- breadcrumb end -->


        <!-- about area -->
        <div class="about-area py-100">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="about-left wow fadeInLeft" data-wow-delay=".25s">
                            <div class="about-img">
                                <div class="row">
                                    <div class="col-7">
                                        <img class="img-1" src="assets/img/about/01.jpg" alt="">
                                    </div>
                                    <div class="col-5 align-self-end">
                                        <img class="img-2" src="assets/img/about/02.jpg" alt="">
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
                                    <i class="flaticon-drive"></i> About Us
                                </span>
                                <h2 class="site-title">
                                    We Provide Quality <span>Pharmaceutical</span> & Healthcare Products For You
                                </h2>
                            </div>
                            <p>
                                Medinef Pharma is focused on providing quality pharmaceutical and
                                healthcare products with an emphasis on reliability, quality and
                                customer satisfaction. We aim to make dependable healthcare
                                products accessible through a customer-focused approach.
                            </p>
                            <div class="about-list">
                                <ul>
                                    <li><i class="fas fa-check-double"></i> Quality Pharmaceutical Products</li>
                                    <li><i class="fas fa-check-double"></i> Reliable Healthcare Solutions</li>
                                    <li><i class="fas fa-check-double"></i> Customer-Focused Service</li>
                                    <li><i class="fas fa-check-double"></i> Strong Focus On Quality</li>
                                </ul>
                            </div>
                            <a href="contact.html" class="theme-btn mt-4">Contact Us<i
                                    class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- about area end -->


        <!-- counter area -->
        <div class="counter-area pt-50 pb-50">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-sm-6">
                        <div class="counter-box">
                            <div class="icon">
                                <img src="assets/img/icon/sale.svg" alt="">
                            </div>
                            <div class="counter-info">
                                <div class="counter-amount">
                                    <span class="counter" data-count="+" data-to="0" data-speed="3000">0</span>
                                    <span class="counter-sign">+</span>
                                </div>
                                <h6 class="title">Quality Products</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="counter-box">
                            <div class="icon">
                                <img src="assets/img/icon/rate.html" alt="">
                            </div>
                            <div class="counter-info">
                                <div class="counter-amount">
                                    <span class="counter" data-count="+" data-to="0" data-speed="3000">0</span>
                                    <span class="counter-sign">+</span>
                                </div>
                                <h6 class="title">Healthcare Solutions</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="counter-box">
                            <div class="icon">
                                <img src="assets/img/icon/employee.html" alt="">
                            </div>
                            <div class="counter-info">
                                <div class="counter-amount">
                                    <span class="counter" data-count="+" data-to="0" data-speed="3000">0</span>
                                    <span class="counter-sign">+</span>
                                </div>
                                <h6 class="title">Customer Support</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="counter-box">
                            <div class="icon">
                                <img src="assets/img/icon/award.svg" alt="">
                            </div>
                            <div class="counter-info">
                                <div class="counter-amount">
                                    <span class="counter" data-count="+" data-to="0" data-speed="3000">0</span>
                                    <span class="counter-sign">+</span>
                                </div>
                                <h6 class="title">Pharma Focus</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- counter area end -->


        <!-- testimonial area -->
        <div class="testimonial-area bg py-80">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 mx-auto wow fadeInDown" data-wow-delay=".25s">
                        <div class="site-heading text-center">
                            <span class="site-title-tagline">Testimonials</span>
                            <h2 class="site-title">What Our Clients Say <span>About Us</span></h2>
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
                                <h4>Rahul Sharma</h4>
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
                        <div class="testimonial-quote-icon"><img src="assets/img/icon/quote.html" alt=""></div>
                    </div>
                    <div class="testimonial-item">
                        <div class="testimonial-author">
                            <div class="testimonial-author-img">
                                <img src="assets/img/testimonial/02.jpg" alt="">
                            </div>
                            <div class="testimonial-author-info">
                                <h4>Priya Verma</h4>
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
                        <div class="testimonial-quote-icon"><img src="assets/img/icon/quote.html" alt=""></div>
                    </div>
                    <div class="testimonial-item">
                        <div class="testimonial-author">
                            <div class="testimonial-author-img">
                                <img src="assets/img/testimonial/03.jpg" alt="">
                            </div>
                            <div class="testimonial-author-info">
                                <h4>Amit Kumar</h4>
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
                        <div class="testimonial-quote-icon"><img src="assets/img/icon/quote.html" alt=""></div>
                    </div>
                    <div class="testimonial-item">
                        <div class="testimonial-author">
                            <div class="testimonial-author-img">
                                <img src="assets/img/testimonial/04.jpg" alt="">
                            </div>
                            <div class="testimonial-author-info">
                                <h4>Neha Singh</h4>
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
                        <div class="testimonial-quote-icon"><img src="assets/img/icon/quote.html" alt=""></div>
                    </div>
                    <div class="testimonial-item">
                        <div class="testimonial-author">
                            <div class="testimonial-author-img">
                                <img src="assets/img/testimonial/05.jpg" alt="">
                            </div>
                            <div class="testimonial-author-info">
                                <h4>Ankit Gupta</h4>
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
                        <div class="testimonial-quote-icon"><img src="assets/img/icon/quote.html" alt=""></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- testimonial area end -->


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


        <!-- team-area -->
        <div class="team-area pt-100 pb-50">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 mx-auto">
                        <div class="site-heading text-center">
                            <span class="site-title-tagline">Our Team</span>
                            <h2 class="site-title">Our <span>Pharma Team</span></h2>
                        </div>
                    </div>
                </div>
                <div class="row mt-5">
                    <div class="col-md-6 col-lg-3">
                        <div class="team-item wow fadeInUp" data-wow-delay=".25s">
                            <div class="team-img">
                                <img src="assets/img/team/01.jpg" alt="thumb">
                            </div>
                            <div class="team-content">
                                <div class="team-bio">
                                    <h5><a href="#">Pharmaceutical Team</a></h5>
                                    <span>Pharma Operations</span>
                                </div>
                            </div>
                            <div class="team-social">
                                <a href="#"><i class="fab fa-facebook-f"></i></a>
                                <a href="#"><i class="fab fa-x-twitter"></i></a>
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                <a href="#"><i class="fab fa-youtube"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="team-item wow fadeInUp" data-wow-delay=".50s">
                            <div class="team-img">
                                <img src="assets/img/team/02.jpg" alt="thumb">
                            </div>
                            <div class="team-content">
                                <div class="team-bio">
                                    <h5><a href="#">Healthcare Team</a></h5>
                                    <span>Healthcare Support</span>
                                </div>
                            </div>
                            <div class="team-social">
                                <a href="#"><i class="fab fa-facebook-f"></i></a>
                                <a href="#"><i class="fab fa-x-twitter"></i></a>
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                <a href="#"><i class="fab fa-youtube"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="team-item wow fadeInUp" data-wow-delay=".75s">
                            <div class="team-img">
                                <img src="assets/img/team/03.jpg" alt="thumb">
                            </div>
                            <div class="team-content">
                                <div class="team-bio">
                                    <h5><a href="#">Quality Team</a></h5>
                                    <span>Quality & Compliance</span>
                                </div>
                            </div>
                            <div class="team-social">
                                <a href="#"><i class="fab fa-facebook-f"></i></a>
                                <a href="#"><i class="fab fa-x-twitter"></i></a>
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                <a href="#"><i class="fab fa-youtube"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="team-item wow fadeInUp" data-wow-delay="1s">
                            <div class="team-img">
                                <img src="assets/img/team/04.jpg" alt="thumb">
                            </div>
                            <div class="team-content">
                                <div class="team-bio">
                                    <h5><a href="#">Customer Care</a></h5>
                                    <span>Customer Support</span>
                                </div>
                            </div>
                            <div class="team-social">
                                <a href="#"><i class="fab fa-facebook-f"></i></a>
                                <a href="#"><i class="fab fa-x-twitter"></i></a>
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                <a href="#"><i class="fab fa-youtube"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- team-area end -->


        <!-- feature area -->
        <div class="feature-area">
            <div class="container wow fadeInUp" data-wow-delay=".25s">
                <div class="feature-wrap">
                    <div class="row g-0">
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fal fa-truck"></i>
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
                                    <i class="fal fa-sync"></i>
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
                                    <i class="fal fa-wallet"></i>
                                </div>
                                <div class="feature-content">
                                    <h4>Secure Service</h4>
                                    <p>Reliable Healthcare Support</p>
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
                    <div class="brand-item">
                        <img src="assets/img/brand/01.png" alt="">
                    </div>
                    <div class="brand-item">
                        <img src="assets/img/brand/02.png" alt="">
                    </div>
                    <div class="brand-item">
                        <img src="assets/img/brand/03.png" alt="">
                    </div>
                    <div class="brand-item">
                        <img src="assets/img/brand/04.png" alt="">
                    </div>
                    <div class="brand-item">
                        <img src="assets/img/brand/05.png" alt="">
                    </div>
                    <div class="brand-item">
                        <img src="assets/img/brand/06.png" alt="">
                    </div>
                </div>
                <div class="text-center">
                    <a href="#" class="theme-btn">View Our Products <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
        <!-- brand area end -->

    </main>



<?php include 'includes/footer.php'; ?>