<?php
/* Database connection */
$host = "localhost";
$dbname = "medinextpharma";
$username = "root";
$password = "";

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($productId <= 0) { http_response_code(404); die('Product not found.'); }

$stmt = $conn->prepare("
    SELECT p.*, c.name AS category_name, c.slug AS category_slug
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.id = :id
    LIMIT 1
");
$stmt->execute([':id' => $productId]);
$product = $stmt->fetch();

if (!$product) { http_response_code(404); die('Product not found.'); }

function productImageUrl($image) {
    $image = trim((string)$image);
    if ($image === '') return 'assets/img/products/01.png';
    if (preg_match('#^https?://#i', $image)) return $image;
    $image = ltrim(str_replace('\\', '/', $image), '/');
    if (strpos($image, 'assets/') === 0) return $image;

    foreach ([
        'assets/img/products/' . basename($image),
        'assets/img/product/' . basename($image),
        'uploads/products/' . basename($image),
        'uploads/product/' . basename($image)
    ] as $path) {
        if (file_exists(__DIR__ . '/' . $path)) return $path;
    }
    return 'assets/img/products/' . basename($image);
}

$galleryImages = [productImageUrl($product['image'] ?? '')];

try {
    $g = $conn->prepare("SELECT image FROM product_images WHERE product_id = :id ORDER BY id ASC");
    $g->execute([':id' => $productId]);
    while ($row = $g->fetch()) {
        $img = productImageUrl($row['image'] ?? '');
        if ($img && !in_array($img, $galleryImages, true)) $galleryImages[] = $img;
    }
} catch (PDOException $e) {}

$productName = $product['name'] ?? 'Product';
$categoryName = $product['category_name'] ?? 'Pharmaceutical Product';
$description = trim((string)($product['description'] ?? ''));
$shortDescription = trim((string)($product['short_description'] ?? ''));
$composition = trim((string)($product['composition'] ?? ''));
$dosageForm = trim((string)($product['dosage_form'] ?? ''));
$productType = trim((string)($product['product_type'] ?? ''));
$packSize = trim((string)($product['pack_size'] ?? ''));
$manufacturer = trim((string)($product['manufacturer'] ?? ''));
$availability = trim((string)($product['availability'] ?? 'In Stock'));
$prescription = trim((string)($product['prescription'] ?? 'Not Required'));
$uses = trim((string)($product['uses'] ?? ''));
$safetyInformation = trim((string)($product['safety_information'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">


<head>
    <!-- meta tags -->
      <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Medinef Pharma - Pharmaceutical products, healthcare solutions and quality-focused medicines.">
    <meta name="keywords" content="Medinef Pharma, pharmaceutical products, medicines, healthcare, pharma company">

    <!-- title -->
   <title>Medinef Pharma | Pharmaceutical Products & Healthcare</title>

    <!-- favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/logo/favicon.png">

    <!-- css -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all-fontawesome.min.css">
    <link rel="stylesheet" href="assets/css/animate.min.css">
    <link rel="stylesheet" href="assets/css/magnific-popup.min.css">
    <link rel="stylesheet" href="assets/css/owl.carousel.min.css">
    <link rel="stylesheet" href="assets/css/jquery-ui.min.css">
    <link rel="stylesheet" href="assets/css/nice-select.min.css">
    <link rel="stylesheet" href="assets/css/flex-slider.min.css">
    <link rel="stylesheet" href="assets/css/style.css">


    <style>
        .shop-single-content .site-title-tagline {
            color: #4B4099;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 8px;
        }

        .shop-single-title {
            color: #163b55;
            font-size: 34px;
            margin-bottom: 12px;
        }

        .shop-single-rating i {
            color: #4B4099;
        }

        .shop-single-list {
            margin-top: 25px;
        }

        .shop-single-list .title {
            color: #163b55;
            margin-bottom: 12px;
        }

        .shop-single-list ul {
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .shop-single-list li {
            color: #65748b;
            padding: 7px 0;
            border-bottom: 1px solid #eee;
        }

        .shop-single-list li span {
            color: #163b55;
            font-weight: 600;
            margin-right: 6px;
        }

        .shop-single-main-img {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 12px;
            min-height: 450px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
        }

        .shop-single-main-img img {
            max-height: 430px;
            width: auto;
            max-width: 100%;
            object-fit: contain;
        }

        .shop-single-details {
            border-top: 1px solid #eee;
            padding-top: 35px;
        }

        .shop-single-details .nav-tabs {
            border-bottom: 1px solid #ddd;
        }

        .shop-single-details .nav-link {
            color: #163b55;
            font-weight: 600;
            border: 0;
            padding: 12px 22px;
        }

        .shop-single-details .nav-link.active {
            color: #4B4099;
            border-bottom: 2px solid #4B4099;
        }

        .shop-single-details p {
            color: #65748b;
            line-height: 1.85;
        }

        .related-item .product-item {
            height: 100%;
        }

        .related-item .product-content p {
            color: #65748b;
            font-size: 14px;
        }

        @media (max-width: 767px) {
            .shop-single-title {
                font-size: 28px;
            }

            .shop-single-main-img {
                min-height: 330px;
                margin-bottom: 30px;
            }
        }
    </style>





<style>
    .pharma-product-image img {
        max-width: 100%;
        height: auto;
        object-fit: contain;
    }

    .product-detail-box {
        margin-top: 22px;
    }

    .product-detail-box ul {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .product-detail-box li {
        padding: 7px 0;
        border-bottom: 1px solid #eee;
    }

    .product-detail-box li:last-child {
        border-bottom: 0;
    }

    .product-detail-box li span {
        font-weight: 600;
        margin-right: 5px;
    }

    .safety-box {
        padding: 20px;
        border: 1px solid #eee;
        border-radius: 8px;
    }
</style>


<style>
    .pharma-gallery {
        width: 100%;
    }

    .pharma-product-image {
        overflow: hidden;
        cursor: zoom-in;
    }

    .pharma-product-image img {
        width: 100%;
        max-height: 500px;
        object-fit: contain;
        transition: opacity .2s ease, transform .35s ease;
    }

    .pharma-product-image:hover img {
        transform: scale(1.03);
    }

    .product-thumbnails {
        display: flex;
        gap: 12px;
        margin-top: 15px;
        overflow-x: auto;
        padding: 3px 2px 8px;
    }

    .product-thumb {
        flex: 0 0 78px;
        width: 78px;
        height: 78px;
        padding: 5px;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        background: #fff;
        cursor: pointer;
        transition: all .25s ease;
    }

    .product-thumb img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .product-thumb:hover,
    .product-thumb.active {
        border-color: #4B4099;
        box-shadow: 0 0 0 1px #4B4099;
    }

    @media (max-width: 575px) {
        .product-thumb {
            flex-basis: 65px;
            width: 65px;
            height: 65px;
        }
    }
</style>


<style>
    .product-gallery-controls {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 15px;
    }

    .product-gallery-controls .product-thumbnails {
        flex: 1;
        margin-top: 0;
    }

    .gallery-arrow {
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        border: 1px solid #e5e5e5;
        border-radius: 50%;
        background: #fff;
        color: #4B4099;
        cursor: pointer;
        transition: all .25s ease;
    }

    .gallery-arrow:hover {
        background: #4B4099;
        color: #fff;
        border-color: #4B4099;
    }

    .medicine-info {
        margin-top: 22px;
    }

    .medicine-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0 25px;
    }

    .medicine-detail-item {
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }

    .medicine-detail-item span {
        display: block;
        color: #65748b;
        font-size: 13px;
        margin-bottom: 3px;
    }

    .medicine-detail-item strong {
        display: block;
        color: #163b55;
        font-size: 14px;
        font-weight: 600;
    }

    .medicine-short-details {
        margin-top: 20px;
    }

    .medicine-short-details h5 {
        color: #163b55;
        margin-bottom: 8px;
    }

    .medicine-short-details p {
        color: #65748b;
        line-height: 1.8;
        margin-bottom: 0;
    }

    .medicine-feature-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px 20px;
        margin-top: 20px;
    }

    .medicine-feature-list div {
        color: #65748b;
    }

    .medicine-feature-list i {
        color: #4B4099;
        margin-right: 6px;
    }

    @media (max-width: 575px) {
        .medicine-detail-grid,
        .medicine-feature-list {
            grid-template-columns: 1fr;
        }

        .gallery-arrow {
            flex: 0 0 32px;
            width: 32px;
            height: 32px;
        }
    }
</style>


<style>
    .pharma-product-image {
        position: relative;
        overflow: hidden;
    }

    .pharma-product-image #mainProductImage {
        display: block;
        width: 100%;
        height: auto;
        object-fit: contain;
        cursor: crosshair;
    }

    .zoom-preview {
        display: none;
        position: absolute;
        z-index: 20;
        width: 300px;
        height: 300px;
        right: 20px;
        top: 20px;
        border: 2px solid #4B4099;
        border-radius: 10px;
        background-color: #fff;
        background-repeat: no-repeat;
        box-shadow: 0 10px 30px rgba(0,0,0,.15);
        pointer-events: none;
    }

    .zoom-lens {
        display: none;
        position: absolute;
        z-index: 10;
        border: 2px solid #4B4099;
        background: rgba(75,64,153,.10);
        pointer-events: none;
    }

    .zoom-hint {
        position: absolute;
        left: 15px;
        bottom: 15px;
        z-index: 11;
        background: rgba(255,255,255,.94);
        color: #4B4099;
        border-radius: 20px;
        padding: 7px 12px;
        font-size: 12px;
        font-weight: 600;
        box-shadow: 0 3px 12px rgba(0,0,0,.10);
        pointer-events: none;
    }

    @media (max-width: 767px) {
        .zoom-preview,
        .zoom-lens {
            display: none !important;
        }

        .zoom-hint {
            display: none;
        }
    }
</style>


<style>
    .main-header-logo {
        max-width: 225px;
        height: auto;
        display: block;
    }

    @media (max-width: 991px) {
        .main-header-logo {
            max-width: 190px;
        }
    }
</style>

</head>

<body>

    <!-- preloader -->
    <div class="preloader">
        <div class="loader-ripple">
            <div></div>
            <div></div>
        </div>
    </div>
    <!-- preloader end -->


   <!-- header area -->
    <header class="header">

        <!-- header top -->
        <div class="header-top">
            <div class="container">
                <div class="header-top-wrap">
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-6 col-xl-5">
                            <div class="header-top-left">
                                <ul class="header-top-list">
                                    <li><a href="mailto:info@example.com"><i class="far fa-envelopes"></i>
                                            info@medinefpharma.online</a></li>
                                    <li><a href="tel:+91-9939926862"><i class="far fa-headset"></i>+9939926862 </a></li>
                                    <li class="help"><a href="contact.php"><i class="far fa-comment-question"></i> Enquiry</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6 col-xl-7">
                            <div class="header-top-right">
                                <ul class="header-top-list">
                                    <li>
                                        <div class="dropdown">
                                            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                <i class="far fa-globe-americas"></i> EN
                                            </a>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="#">EN</a>
                                                <a class="dropdown-item" href="#">FR</a>
                                                <a class="dropdown-item" href="#">DE</a>
                                                <a class="dropdown-item" href="#">RU</a>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="social">
                                        <div class="header-top-social">
                                            <span>Follow Us: </span>
                                            <a href="#"><i class="fab fa-facebook"></i></a>
                                            <a href="#"><i class="fab fa-x-twitter"></i></a>
                                            <a href="#"><i class="fab fa-instagram"></i></a>
                                            <a href="#"><i class="fab fa-linkedin"></i></a>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- header top end -->


        <!-- navbar -->
        <div class="main-navigation">
            <nav class="navbar navbar-expand-lg">
                <div class="container position-relative">
                    <a class="navbar-brand" href="index.php">
                        <img src="assets/img/logo/logo.png" alt="Medinef Pharma" class="main-header-logo">
                    </a>
                    <div class="mobile-menu-right">
                        <div class="mobile-menu-btn">
                            <a href="#" class="nav-right-link search-box-outer"><i class="far fa-search"></i></a>
                            <a href="wishlist.php" class="nav-right-link"><i
                                    class="far fa-heart"></i><span>2</span></a>
                            <a href="shop-cart.php" class="nav-right-link"><i
                                    class="far fa-shopping-bag"></i><span>5</span></a>
                        </div>
                        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar"
                            aria-label="Toggle navigation">
                            <span></span>
                            <span></span>
                            <span></span>
                        </button>
                    </div>
                    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar"
                        aria-labelledby="offcanvasNavbarLabel">
                        <div class="offcanvas-header">
                            <a href="index.php" class="offcanvas-brand" id="offcanvasNavbarLabel">
                                <img src="assets/img/logo/logo.png" alt="">
                            </a>
                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                                aria-label="Close"></button>
                        </div>

                        <div class="offcanvas-body">
                            <ul class="navbar-nav justify-content-end flex-grow-1">
                                <li class="nav-item">
                                    <a class="nav-link active" href="index.php">Home</a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="about.php">About Us</a>
                                </li>

                                <li class="nav-item mega-menu dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Products</a>
                                    <div class="dropdown-menu fade-down">
                                        <div class="mega-content">
                                            <div class="container-fluid px-lg-0">
                                                <div class="row">
                                                    <div class="col-12 col-lg-3">
                                                        <h5 class="mega-menu-title">Products</h5>
                                                        <ul class="mega-menu-item">
                                                            <li><a class="dropdown-item" href="products.php">All Products</a></li>
                                                            <li><a class="dropdown-item" href="products.php?category=tablets">Tablets</a></li>
                                                            <li><a class="dropdown-item" href="products.php?category=capsules">Capsules</a></li>
                                                            <li><a class="dropdown-item" href="products.php?category=syrups">Syrups</a></li>
                                                            <li><a class="dropdown-item" href="products.php?category=other">Other Products</a></li>
                                                        </ul>
                                                    </div>
                                                    <div class="col-12 col-lg-3">
                                                        <h5 class="mega-menu-title">Therapeutic Areas</h5>
                                                        <ul class="mega-menu-item">
                                                            <li><a class="dropdown-item" href="products.php">General Medicine</a></li>
                                                            <li><a class="dropdown-item" href="products.php">Pain Management</a></li>
                                                            <li><a class="dropdown-item" href="products.php">Gastro Care</a></li>
                                                            <li><a class="dropdown-item" href="products.php">Anti-Infective</a></li>
                                                            <li><a class="dropdown-item" href="products.php">Nutraceuticals</a></li>
                                                        </ul>
                                                    </div>
                                                    <div class="col-12 col-lg-3">
                                                        <h5 class="mega-menu-title">Featured Products</h5>
                                                        <ul class="mega-menu-item">
                                                            <li><a class="dropdown-item" href="product-details.php">Nemozid-P</a></li>
                                                            <li><a class="dropdown-item" href="product-details.php">MediMam-SP</a></li>
                                                            <li><a class="dropdown-item" href="product-details.php">Medorab-LSR</a></li>
                                                            <li><a class="dropdown-item" href="product-details.php">Nefotix</a></li>
                                                            <li><a class="dropdown-item" href="products.php">View All Products</a></li>
                                                        </ul>
                                                    </div>
                                                    <div class="col-12 col-lg-3">
                                                        <div class="mega-menu-img">
                                                            <a href="products.php"><img src="assets/img/banner/mega-menu-banner.jpg"
                                                                alt="Medinef Pharma Products"></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Quality</a>
                                    <ul class="dropdown-menu fade-down">
                                        <li><a class="dropdown-item" href="quality.php">Quality Assurance</a></li>
                                        <li><a class="dropdown-item" href="certifications.php">Certifications</a></li>
                                        <li><a class="dropdown-item" href="manufacturing.php">Manufacturing</a></li>
                                    </ul>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="research.php">Research & Development</a>
                                </li>

                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Company</a>
                                    <ul class="dropdown-menu fade-down">
                                        <li><a class="dropdown-item" href="about.php">About Us</a></li>
                                        <li><a class="dropdown-item" href="facilities.php">Our Facilities</a></li>
                                        <li><a class="dropdown-item" href="gallery.php">Gallery</a></li>
                                        <li><a class="dropdown-item" href="careers.php">Careers</a></li>
                                        <li><a class="dropdown-item" href="news.php">News & Updates</a></li>
                                    </ul>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="contact.php">Contact Us</a>
                                </li>
                            </ul>

                            <!-- nav-right -->
                            
                        </div><!-- nav-right -->
                            <!-- <div class="nav-right">
                                <a href="#" class="nav-right-link search-box-outer">
                                    <i class="far fa-search"></i>
                                </a>
                                <a href="wishlist.php" class="nav-right-link"><i
                                    class="far fa-heart"></i><span>2</span></a>
                                <a href="shop-cart.php" class="nav-right-link"><i
                                    class="far fa-shopping-bag"></i><span>5</span></a>
                            </div> -->
                        </div>


                    </div>
                </div>
            </nav>
        </div>
        <!-- navbar end -->

    </header>
    <!-- header area end -->





    <main class="main">

        <!-- breadcrumb -->
        <div class="site-breadcrumb">
            <div class="site-breadcrumb-bg" style="background: url(assets/img/breadcrumb/01.jpg)"></div>
            <div class="container">
                <div class="site-breadcrumb-wrap">
                    <h4 class="breadcrumb-title">Product Details</h4>
                    <ul class="breadcrumb-menu">
                        <li><a href="index.html"><i class="far fa-home"></i> Home</a></li>
                        <li class="active">Product Details</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- breadcrumb end -->


        <!-- product details -->
        <div class="shop-single py-80">
            <div class="container">
                <div class="row align-items-start">

                    <div class="col-lg-5">
                        <div class="shop-single-gallery pharma-gallery">

                            <div class="shop-single-main-img pharma-product-image" id="productZoomBox">
                                <img id="mainProductImage"
                                     src="<?= htmlspecialchars($galleryImages[0]) ?>"
                                     alt="<?= htmlspecialchars($productName) ?>"
                                     class="img-fluid"
                                     onerror="this.onerror=null;this.src='assets/img/products/01.png';">

                                <div class="zoom-lens" id="zoomLens"></div>
                                <div class="zoom-preview" id="zoomPreview"></div>

                                <span class="zoom-hint">
                                    <i class="fas fa-search-plus"></i> Move mouse to zoom
                                </span>
                            </div>

                            <div class="product-gallery-controls">
                                <?php if (count($galleryImages) > 1): ?>
                                    <button type="button" class="gallery-arrow" onclick="galleryPrevious()">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                <?php endif; ?>

                                <div class="product-thumbnails">
                                    <?php foreach ($galleryImages as $index => $galleryImage): ?>
                                        <button type="button"
                                                class="product-thumb <?= $index === 0 ? 'active' : '' ?>"
                                                onclick="changeProductImage(this, '<?= htmlspecialchars($galleryImage, ENT_QUOTES) ?>')">
                                            <img src="<?= htmlspecialchars($galleryImage) ?>"
                                                 alt="<?= htmlspecialchars($productName) ?> Image <?= $index + 1 ?>"
                                                 onerror="this.onerror=null;this.src='assets/img/products/01.png';">
                                        </button>
                                    <?php endforeach; ?>
                                </div>

                                <?php if (count($galleryImages) > 1): ?>
                                    <button type="button" class="gallery-arrow" onclick="galleryNext()">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="shop-single-content">

                            <span class="site-title-tagline"><?= htmlspecialchars($categoryName) ?></span>

                            <h2 class="shop-single-title"><?= htmlspecialchars($productName) ?></h2>

                            <?php if ($shortDescription !== '' || $description !== ''): ?>
                                <p class="shop-single-desc">
                                    <?= nl2br(htmlspecialchars($shortDescription !== '' ? $shortDescription : $description)) ?>
                                </p>
                            <?php endif; ?>

                            <div class="product-detail-box medicine-info">
                                <h5>Medicine Details</h5>

                                <div class="medicine-detail-grid">

                                    <div class="medicine-detail-item">
                                        <span>Brand</span>
                                        <strong><?= htmlspecialchars($manufacturer !== '' ? $manufacturer : 'Medinef Pharma') ?></strong>
                                    </div>

                                    <div class="medicine-detail-item">
                                        <span>Product Name</span>
                                        <strong><?= htmlspecialchars($productName) ?></strong>
                                    </div>

                                    <div class="medicine-detail-item">
                                        <span>Category</span>
                                        <strong><?= htmlspecialchars($categoryName) ?></strong>
                                    </div>

                                    <div class="medicine-detail-item">
                                        <span>Product Type</span>
                                        <strong><?= htmlspecialchars($productType !== '' ? $productType : 'Pharmaceutical') ?></strong>
                                    </div>

                                    <div class="medicine-detail-item">
                                        <span>Dosage Form</span>
                                        <strong><?= htmlspecialchars($dosageForm !== '' ? $dosageForm : 'Refer Product Pack') ?></strong>
                                    </div>

                                    <div class="medicine-detail-item">
                                        <span>Pack Size</span>
                                        <strong><?= htmlspecialchars($packSize !== '' ? $packSize : 'As mentioned on pack') ?></strong>
                                    </div>

                                    <div class="medicine-detail-item">
                                        <span>Manufacturer</span>
                                        <strong><?= htmlspecialchars($manufacturer !== '' ? $manufacturer : 'Medinef Pharma') ?></strong>
                                    </div>

                                    <div class="medicine-detail-item">
                                        <span>Availability</span>
                                        <strong><?= htmlspecialchars($availability) ?></strong>
                                    </div>

                                    <div class="medicine-detail-item">
                                        <span>Prescription</span>
                                        <strong><?= htmlspecialchars($prescription) ?></strong>
                                    </div>

                                </div>

                                <?php if ($uses !== ''): ?>
                                    <div class="medicine-short-details">
                                        <h5>Uses</h5>
                                        <p><?= nl2br(htmlspecialchars($uses)) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="shop-single-action mt-25">
                                <a href="contact.php?product=<?= urlencode($productName) ?>" class="theme-btn">
                                    <i class="far fa-envelope"></i> Enquire About Product
                                </a>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="shop-single-details mt-60">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#description" type="button">
                                Product Details
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#composition" type="button">
                                Composition
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#safety" type="button">
                                Safety Information
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-35">
                        <div class="tab-pane fade show active" id="description">
                            <h4 class="mb-15">About <?= htmlspecialchars($productName) ?></h4>
                            <p>
                                <?= nl2br(htmlspecialchars($description !== '' ? $description : ($shortDescription !== '' ? $shortDescription : 'Product information is available on the approved product packaging and product literature.'))) ?>
                            </p>

                            <div class="medicine-feature-list">
                                <div><i class="far fa-check-circle"></i> Pharmaceutical product</div>
                                <div><i class="far fa-check-circle"></i> <?= htmlspecialchars($categoryName) ?></div>
                                <div><i class="far fa-check-circle"></i> <?= htmlspecialchars($availability) ?></div>
                                <div><i class="far fa-check-circle"></i> Professional guidance recommended</div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="composition">
                            <h4 class="mb-15">Composition &amp; Strength</h4>
                            <p>
                                <?= nl2br(htmlspecialchars($composition !== '' ? $composition : 'Composition and strength should be verified against the approved product packaging and product literature.')) ?>
                            </p>
                        </div>

                        <div class="tab-pane fade" id="safety">
                            <h4 class="mb-15">Important Safety Information</h4>
                            <p>
                                <?= nl2br(htmlspecialchars($safetyInformation !== '' ? $safetyInformation : 'Medicines should be used according to approved product information and professional healthcare guidance. Do not start, stop or change a medicine based only on information displayed on this website.')) ?>
                            </p>
                            <p>
                                For questions about dosage, interactions, contraindications or side effects, consult a qualified healthcare professional.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="product-area related-item pt-70">
                    <div class="site-heading-inline mb-35">
                        <h2 class="site-title">Related Products</h2>
                        <a href="products.php">View All <i class="fas fa-arrow-right"></i></a>
                    </div>

                    <div class="row g-4">
                        <?php
                        $relatedProducts = [];
                        try {
                            $r = $conn->prepare("
                                SELECT p.*, c.name AS category_name
                                FROM products p
                                LEFT JOIN categories c ON c.id = p.category_id
                                WHERE p.category_id = :category_id
                                  AND p.id != :product_id
                                ORDER BY p.id DESC
                                LIMIT 4
                            ");
                            $r->execute([
                                ':category_id' => (int)$product['category_id'],
                                ':product_id' => $productId
                            ]);
                            $relatedProducts = $r->fetchAll();
                        } catch (PDOException $e) {}
                        ?>

                        <?php if ($relatedProducts): ?>
                            <?php foreach ($relatedProducts as $related): ?>
                                <div class="col-md-6 col-lg-3">
                                    <div class="product-item">
                                        <div class="product-img">
                                            <a href="product-details.php?id=<?= (int)$related['id'] ?>">
                                                <img src="<?= htmlspecialchars(productImageUrl($related['image'] ?? '')) ?>"
                                                     alt="<?= htmlspecialchars($related['name']) ?>"
                                                     onerror="this.onerror=null;this.src='assets/img/products/01.png';">
                                            </a>
                                        </div>
                                        <div class="product-content">
                                            <h3 class="product-title">
                                                <a href="product-details.php?id=<?= (int)$related['id'] ?>">
                                                    <?= htmlspecialchars($related['name']) ?>
                                                </a>
                                            </h3>
                                            <p><?= htmlspecialchars($related['category_name'] ?? 'Pharmaceutical Product') ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
        <!-- product details end -->

    </main>



 <!-- footer area -->
<footer class="footer-area ft-bg">
    <div class="footer-widget">
        <div class="container">
            <div class="row footer-widget-wrapper pt-100 pb-40">

                <!-- About -->
                <div class="col-md-6 col-lg-3">
                    <div class="footer-widget-box about-us">

                        <a href="index.php" class="footer-logo">
                            <img src="assets/img/logo/logo-light.png"
                                 alt="Medinef Pharma">
                        </a>

                        <p class="mb-3">
                            Medinef Pharma is focused on quality
                            pharmaceutical products and healthcare
                            solutions with an emphasis on reliability
                            and quality.
                        </p>

                        <ul class="footer-contact">

                            <li>
                                <a href="tel:+919939926862">
                                    <i class="far fa-phone"></i>
                                    09939926862
                                </a>
                            </li>

                            <li>
                                <i class="far fa-map-marker-alt"></i>
                                India
                            </li>

                            <li>
                                <a href="mailto:info@medinefpharma.online">
                                    <i class="far fa-envelope"></i>
                                    info@medinefpharma.online
                                </a>
                            </li>

                            <li>
                                <i class="far fa-clock"></i>
                                Mon - Sat (9:00 AM - 6:00 PM)
                            </li>

                        </ul>
                    </div>
                </div>


                <!-- Quick Links -->
                <div class="col-md-6 col-lg-2">
                    <div class="footer-widget-box list">

                        <h4 class="footer-widget-title">
                            Quick Links
                        </h4>

                        <ul class="footer-list">

                            <li>
                                <a href="about.php">About Us</a>
                            </li>

                            <li>
                                <a href="products.php">Our Products</a>
                            </li>

                            <li>
                                <a href="blog.php">Pharma Insights</a>
                            </li>

                            <li>
                                <a href="contact.php">Contact Us</a>
                            </li>

                            <li>
                                <a href="about.php">Why Choose Us</a>
                            </li>

                            <li>
                                <a href="privacy.php">Privacy Policy</a>
                            </li>

                            <li>
                                <a href="terms.php">Terms & Conditions</a>
                            </li>

                        </ul>

                    </div>
                </div>


                <!-- Products -->
                <div class="col-md-6 col-lg-2">
                    <div class="footer-widget-box list">

                        <h4 class="footer-widget-title">
                            Our Products
                        </h4>

                        <ul class="footer-list">

                            <li>
                                <a href="products.php">Nemozid-P</a>
                            </li>

                            <li>
                                <a href="products.php">MediMam-SP</a>
                            </li>

                            <li>
                                <a href="products.php">StoneCrusher</a>
                            </li>

                            <li>
                                <a href="products.php">Medorab-LSR</a>
                            </li>

                            <li>
                                <a href="products.php">Nefotix</a>
                            </li>

                            <li>
                                <a href="products.php">Medoflox-200</a>
                            </li>

                            <li>
                                <a href="products.php">View All Products</a>
                            </li>

                        </ul>

                    </div>
                </div>


                <!-- Healthcare -->
                <div class="col-md-6 col-lg-2">
                    <div class="footer-widget-box list">

                        <h4 class="footer-widget-title">
                            Healthcare
                        </h4>

                        <ul class="footer-list">

                            <li>
                                <a href="products.php">
                                    Pharmaceutical Products
                                </a>
                            </li>

                            <li>
                                <a href="products.php">
                                    Healthcare Products
                                </a>
                            </li>

                            <li>
                                <a href="products.php">
                                    Product Portfolio
                                </a>
                            </li>

                            <li>
                                <a href="about.php">
                                    Quality Focus
                                </a>
                            </li>

                            <li>
                                <a href="about.php">
                                    Our Commitment
                                </a>
                            </li>

                            <li>
                                <a href="contact.php">
                                    Business Enquiry
                                </a>
                            </li>

                            <li>
                                <a href="contact.php">
                                    Contact Support
                                </a>
                            </li>

                        </ul>

                    </div>
                </div>


                <!-- Contact -->
                <div class="col-md-6 col-lg-3">
                    <div class="footer-widget-box list">

                        <h4 class="footer-widget-title">
                            Contact Us
                        </h4>

                        <p>
                            For product information, business enquiries
                            and other pharmaceutical related queries,
                            please get in touch with our team.
                        </p>

                        <div class="footer-download">

                            <h5>
                                Get In Touch
                            </h5>

                            <ul class="footer-contact">

                            <li>
                                <a href="tel:+91 7979014035">
                                    <i class="far fa-phone"></i>
                                     +91-7979014035
                                </a>
                            </li>

                           

                            <li>
                                <a href="mailto:Medinefpharma2019@gmail.com">
                                    <i class="far fa-envelope"></i>
                                    hr@medinefpharma.online
                                </a>
                            </li>

                            <li>
                                <i class="far fa-clock"></i>
                                Mon - Sat (9:00 AM - 6:00 PM)
                            </li>

                        </ul>

                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>


    <!-- Copyright -->
    <div class="copyright">
        <div class="container">

            <div class="copyright-wrap">

                <div class="row">

                    <div class="col-12 col-lg-6 align-self-center">

                        <p class="copyright-text">
                            &copy; Copyright
                            <span id="date"></span>

                            <a href="index.php">
                                Medinef Pharma
                            </a>

                            All Rights Reserved.
                        </p>

                    </div>


                    <div class="col-12 col-lg-6 align-self-center">

                        <div class="footer-social">

                            <span>Follow Us:</span>

                            <a href="#">
                                <i class="fab fa-facebook-f"></i>
                            </a>

                            <a href="#">
                                <i class="fab fa-x-twitter"></i>
                            </a>

                            <a href="#">
                                <i class="fab fa-linkedin-in"></i>
                            </a>

                            <a href="#">
                                <i class="fab fa-youtube"></i>
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>

</footer>
<!-- footer area end -->


    <!-- scroll-top -->
    <a href="#" id="scroll-top"><i class="far fa-arrow-up-from-arc"></i></a>
    <!-- scroll-top end -->





    <!-- js -->
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/modernizr.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/imagesloaded.pkgd.min.js"></script>
    <script src="assets/js/jquery.magnific-popup.min.js"></script>
    <script src="assets/js/isotope.pkgd.min.js"></script>
    <script src="assets/js/jquery.appear.min.js"></script>
    <script src="assets/js/jquery.easing.min.js"></script>
    <script src="assets/js/owl.carousel.min.js"></script>
    <script src="assets/js/counter-up.js"></script>
    <script src="assets/js/jquery-ui.min.js"></script>
    <script src="assets/js/jquery.nice-select.min.js"></script>
    <script src="assets/js/countdown.min.js"></script>
    <script src="assets/js/wow.min.js"></script>
    <script src="assets/js/flex-slider.min.js"></script>
    <script src="assets/js/main.js"></script>


<script>
    let currentProductImage = 0;

    function changeProductImage(button, imagePath) {
        const mainImage = document.getElementById('mainProductImage');
        const thumbs = Array.from(document.querySelectorAll('.product-thumb'));

        if (!mainImage) return;

        const index = thumbs.indexOf(button);
        if (index >= 0) currentProductImage = index;

        mainImage.style.opacity = '0';

        setTimeout(function () {
            mainImage.src = imagePath;
            mainImage.style.opacity = '1';
            mainImage.onload = updateZoomImage;
        }, 120);

        thumbs.forEach(function (thumb) {
            thumb.classList.remove('active');
        });

        button.classList.add('active');
    }

    function showGalleryImage(index) {
        const thumbs = Array.from(document.querySelectorAll('.product-thumb'));
        if (!thumbs.length) return;

        if (index < 0) index = thumbs.length - 1;
        if (index >= thumbs.length) index = 0;

        currentProductImage = index;
        const thumb = thumbs[index];
        const img = thumb.querySelector('img');

        if (img) {
            changeProductImage(thumb, img.getAttribute('src'));
        }
    }

    function galleryPrevious() {
        showGalleryImage(currentProductImage - 1);
    }

    function galleryNext() {
        showGalleryImage(currentProductImage + 1);
    }
</script>


<script>
(function () {
    const box = document.getElementById('productZoomBox');
    const image = document.getElementById('mainProductImage');
    const preview = document.getElementById('zoomPreview');
    const lens = document.getElementById('zoomLens');

    if (!box || !image || !preview || !lens) return;

    const zoom = 2.5;

    function updateZoomImage() {
        preview.style.backgroundImage = 'url("' + image.src + '")';
        preview.style.backgroundSize =
            (image.clientWidth * zoom) + 'px ' +
            (image.clientHeight * zoom) + 'px';
    }

    function moveZoom(e) {
        const rect = image.getBoundingClientRect();

        let x = e.clientX - rect.left;
        let y = e.clientY - rect.top;

        x = Math.max(0, Math.min(x, rect.width));
        y = Math.max(0, Math.min(y, rect.height));

        const lensW = lens.offsetWidth;
        const lensH = lens.offsetHeight;

        let lensX = x - lensW / 2;
        let lensY = y - lensH / 2;

        lensX = Math.max(0, Math.min(lensX, rect.width - lensW));
        lensY = Math.max(0, Math.min(lensY, rect.height - lensH));

        lens.style.left = lensX + 'px';
        lens.style.top = lensY + 'px';

        const bgX = -(x * zoom - preview.clientWidth / 2);
        const bgY = -(y * zoom - preview.clientHeight / 2);

        preview.style.backgroundPosition = bgX + 'px ' + bgY + 'px';
    }

    box.addEventListener('mouseenter', function () {
        if (window.innerWidth <= 767) return;

        updateZoomImage();

        const w = Math.min(150, image.clientWidth * 0.30);
        const h = Math.min(150, image.clientHeight * 0.30);

        lens.style.width = w + 'px';
        lens.style.height = h + 'px';

        lens.style.display = 'block';
        preview.style.display = 'block';
        document.querySelector('.zoom-hint').style.display = 'none';
    });

    box.addEventListener('mousemove', moveZoom);

    box.addEventListener('mouseleave', function () {
        lens.style.display = 'none';
        preview.style.display = 'none';

        const hint = document.querySelector('.zoom-hint');
        if (hint) hint.style.display = '';
    });

    image.addEventListener('load', updateZoomImage);
})();
</script>

</body>
</html>