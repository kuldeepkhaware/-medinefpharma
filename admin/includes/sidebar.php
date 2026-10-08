<aside class="sidebar">

    <style>

    .sidebar {
        position: fixed;
        left: 0;
        top: 0;

        width: 260px;
        height: 100vh;

        background: #ffffff;
        border-right: 1px solid #eeeaf7;

        padding: 25px 18px;

        z-index: 1000;

        overflow-y: auto;
    }


    .brand {
        display: flex;
        align-items: center;
        justify-content: center;

        height: 70px;
        margin-bottom: 25px;
    }


    .brand a {
        display: flex;
        align-items: center;
        justify-content: center;

        width: 100%;
    }


    .brand img {
        max-width: 190px;
        max-height: 65px;

        width: auto;
        height: auto;

        object-fit: contain;

        display: block;
    }


    .menu-title {
        font-size: 11px;
        font-weight: 700;

        color: #aaa5b9;

        text-transform: uppercase;

        padding: 0 13px;

        margin: 15px 0 10px;

        letter-spacing: 1px;
    }


    .menu {
        list-style: none;
        margin: 0;
        padding: 0;
    }


    .menu li {
        margin-bottom: 5px;
    }


    .menu a {
        display: flex;
        align-items: center;

        gap: 13px;

        color: #625f6d;

        padding: 12px 14px;

        border-radius: 9px;

        font-size: 14px;
        font-weight: 500;

        text-decoration: none;

        transition: .2s;
    }


    .menu a:hover {
        background: #f1effb;
        color: #4B4099;
    }


    .menu a.active {
        background: #4B4099;
        color: #ffffff;

        box-shadow:
            0 7px 18px rgba(75,64,153,.18);
    }


    .menu-icon {
        width: 20px;
        min-width: 20px;

        text-align: center;

        font-size: 16px;
    }


    @media(max-width:750px) {

        .sidebar {
            width: 80px;
            padding: 20px 10px;
        }

        .brand img {
            max-width: 55px;
        }

        .menu a {
            justify-content: center;
            padding: 13px;
        }

        .menu a span:not(.menu-icon),
        .menu-title {
            display: none;
        }

    }

    </style>


    <!-- =========================================
         LOGO
    ========================================== -->

    <div class="brand">

        <a href="/medinefpharma.online/admin/dashboard.php">

            <img
                src="/medinefpharma.online/assets/img/logo/logo.png"
                alt="Medinef Pharma"
            >

        </a>

    </div>


    <!-- =========================================
         MAIN MENU
    ========================================== -->

    <div class="menu-title">
        Main Menu
    </div>


    <ul class="menu">


        <!-- DASHBOARD -->

        <li>

            <a
                href="/medinefpharma.online/admin/dashboard.php"
                class="<?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>"
            >

                <span class="menu-icon">
                    ⌂
                </span>

                <span>
                    Dashboard
                </span>

            </a>

        </li>


        <!-- CATEGORIES -->

        <li>

            <a
                href="/medinefpharma.online/admin/categories/index.php"
                class="<?= ($activeMenu ?? '') === 'categories' ? 'active' : '' ?>"
            >

                <span class="menu-icon">
                    ▦
                </span>

                <span>
                    Categories
                </span>

            </a>

        </li>


        <!-- PRODUCTS -->

        <li>

            <a
                href="/medinefpharma.online/admin/products/index.php"
                class="<?= ($activeMenu ?? '') === 'products' ? 'active' : '' ?>"
            >

                <span class="menu-icon">
                    ▣
                </span>

                <span>
                    Products
                </span>

            </a>

        </li>


        <!-- BRANDS -->

        <li>

            <a
                href="/medinefpharma.online/admin/brands/index.php"
                class="<?= ($activeMenu ?? '') === 'brands' ? 'active' : '' ?>"
            >

                <span class="menu-icon">
                    ◇
                </span>

                <span>
                    Brands
                </span>

            </a>

        </li>


        <!-- BANNERS -->

        <li>

            <a
                href="/medinefpharma.online/admin/banners/index.php"
                class="<?= ($activeMenu ?? '') === 'banners' ? 'active' : '' ?>"
            >

                <span class="menu-icon">
                    ▤
                </span>

                <span>
                    Banners
                </span>

            </a>

        </li>


        <!-- BLOGS -->

        <li>

            <a
                href="/medinefpharma.online/admin/blogs/index.php"
                class="<?= ($activeMenu ?? '') === 'blogs' ? 'active' : '' ?>"
            >

                <span class="menu-icon">
                    ✎
                </span>

                <span>
                    Blogs
                </span>

            </a>

        </li>

    </ul>


    <!-- =========================================
         MANAGEMENT
    ========================================== -->

    <div class="menu-title">
        Management
    </div>


    <ul class="menu">


        <!-- ORDERS -->

        <li>

            <a
                href="/medinefpharma.online/admin/orders/index.php"
                class="<?= ($activeMenu ?? '') === 'orders' ? 'active' : '' ?>"
            >

                <span class="menu-icon">
                    ▤
                </span>

                <span>
                    Orders
                </span>

            </a>

        </li>


        <!-- CUSTOMERS -->

        <li>

            <a
                href="/medinefpharma.online/admin/customers/index.php"
                class="<?= ($activeMenu ?? '') === 'customers' ? 'active' : '' ?>"
            >

                <span class="menu-icon">
                    ♙
                </span>

                <span>
                    Customers
                </span>

            </a>

        </li>


        <!-- TESTIMONIALS -->

        <li>

            <a
                href="/medinefpharma.online/admin/testimonials/index.php"
                class="<?= ($activeMenu ?? '') === 'testimonials' ? 'active' : '' ?>"
            >

                <span class="menu-icon">
                    ☆
                </span>

                <span>
                    Testimonials
                </span>

            </a>

        </li>


        <!-- SETTINGS -->

        <li>

            <a
                href="/medinefpharma.online/admin/settings/index.php"
                class="<?= ($activeMenu ?? '') === 'settings' ? 'active' : '' ?>"
            >

                <span class="menu-icon">
                    ⚙
                </span>

                <span>
                    Settings
                </span>

            </a>

        </li>


        <!-- LOGOUT -->

        <li>

             <a href="/medinefpharma.online/admin/logout.php">

                <span class="menu-icon">
                    ↪
                </span>

                <span>
                    Logout
                </span>

            </a>

        </li>

    </ul>

</aside>