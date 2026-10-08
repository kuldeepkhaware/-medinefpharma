<?php

$pageTitle = "Dashboard";
$breadcrumb = "Medinef Pharma / Dashboard";
$activeMenu = "dashboard";

require_once "includes/header.php";
require_once "includes/sidebar.php";

?>

<!-- MAIN CONTENT -->

<main class="main">

    <!-- TOPBAR -->

    <header class="topbar">

        <div class="page-title">

            <h2>
                Dashboard
            </h2>

            <span>
                Medinef Pharma / Dashboard
            </span>

        </div>


        <div class="top-right">

            <div class="notification">
                ♧
            </div>


            <div class="profile">

                <div class="profile-avatar">

                    <?= strtoupper(
                        substr($adminName, 0, 1)
                    ) ?>

                </div>


                <div class="profile-info">

                    <strong>
                        <?= htmlspecialchars($adminName) ?>
                    </strong>

                    <span>
                        Administrator
                    </span>

                </div>

            </div>

        </div>

    </header>


    <!-- PAGE CONTENT -->

    <section class="content">


        <!-- WELCOME -->

        <div class="welcome">

            <h1>
                Welcome back,
                <?= htmlspecialchars($adminName) ?>
                👋
            </h1>

            <p>
                Manage your Medinef Pharma website from your admin dashboard.
            </p>

        </div>


        <!-- STAT CARDS -->

        <div class="cards">


            <!-- PRODUCTS -->

            <div class="card">

                <div class="card-top">

                    <h4>
                        Total Products
                    </h4>

                    <div class="card-icon">
                        ▣
                    </div>

                </div>

                <div class="number">
                    0
                </div>

            </div>


            <!-- CATEGORIES -->

            <div class="card">

                <div class="card-top">

                    <h4>
                        Categories
                    </h4>

                    <div class="card-icon">
                        ▦
                    </div>

                </div>

                <div class="number">
                    0
                </div>

            </div>


            <!-- ORDERS -->

            <div class="card">

                <div class="card-top">

                    <h4>
                        Total Orders
                    </h4>

                    <div class="card-icon">
                        ▤
                    </div>

                </div>

                <div class="number">
                    0
                </div>

            </div>


            <!-- CUSTOMERS -->

            <div class="card">

                <div class="card-top">

                    <h4>
                        Customers
                    </h4>

                    <div class="card-icon">
                        ♙
                    </div>

                </div>

                <div class="number">
                    0
                </div>

            </div>


        </div>


        <!-- BOTTOM GRID -->

        <div class="bottom-grid">


            <!-- RECENT PRODUCTS -->

            <div class="panel">

                <div class="panel-header">

                    <h3>
                        Recent Products
                    </h3>

                    <a
                        href="products/index.php"
                        class="view-all"
                    >
                        View All
                    </a>

                </div>


                <div class="empty">

                    <div class="empty-icon">
                        ▣
                    </div>

                    <p>
                        No products added yet.
                    </p>

                </div>

            </div>


            <!-- QUICK ACTIONS -->

            <div class="panel">

                <div class="panel-header">

                    <h3>
                        Quick Actions
                    </h3>

                </div>


                <div class="quick-action">


                    <!-- ADD CATEGORY -->

                    <a href="categories/add.php">

                        <div class="quick-icon">
                            +
                        </div>

                        <span>
                            Add Category
                        </span>

                    </a>


                    <!-- ADD PRODUCT -->

                    <a href="products/add.php">

                        <div class="quick-icon">
                            +
                        </div>

                        <span>
                            Add Product
                        </span>

                    </a>


                    <!-- ADD BRAND -->

                    <a href="brands/add.php">

                        <div class="quick-icon">
                            +
                        </div>

                        <span>
                            Add Brand
                        </span>

                    </a>


                    <!-- ADD BANNER -->

                    <a href="banners/add.php">

                        <div class="quick-icon">
                            +
                        </div>

                        <span>
                            Add Banner
                        </span>

                    </a>


                </div>

            </div>


        </div>


    </section>

</main>


<?php

require_once "includes/footer.php";

?>