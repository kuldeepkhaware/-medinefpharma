<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

$adminName = $_SESSION['admin_name'] ?? 'Admin';

$pageTitle = $pageTitle ?? 'Dashboard';
$breadcrumb = $breadcrumb ?? 'Dashboard';

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= htmlspecialchars($pageTitle) ?> - Medinef Pharma</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background: #f6f5fb;
    color: #292735;
}

a {
    text-decoration: none;
}

/* =========================
   TOPBAR
========================= */

.topbar {
    position: fixed;
    top: 0;
    right: 0;
    left: 260px;
    height: 78px;
    background: #ffffff;
    border-bottom: 1px solid #eeeaf7;

    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 0 32px;

    z-index: 900;
}

.page-title h2 {
    font-size: 21px;
    color: #282532;
    margin-bottom: 4px;
}

.page-title span {
    color: #9994a5;
    font-size: 12px;
}

.top-right {
    display: flex;
    align-items: center;
    gap: 22px;
}

.notification {
    width: 40px;
    height: 40px;

    border-radius: 10px;

    background: #f5f3fb;
    color: #4B4099;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 18px;
}

.profile {
    display: flex;
    align-items: center;
    gap: 11px;
}

.profile-avatar {
    width: 40px;
    height: 40px;

    border-radius: 50%;

    background: #4B4099;
    color: #fff;

    display: flex;
    align-items: center;
    justify-content: center;

    font-weight: 700;
}

.profile-info strong {
    display: block;
    font-size: 13px;
}

.profile-info span {
    color: #9994a5;
    font-size: 11px;
}


/* =========================
   MAIN
========================= */

.main {
    margin-left: 260px;
    min-height: 100vh;
    padding-top: 78px;
}


/* =========================
   CONTENT
========================= */

.content {
    padding: 30px;
}


/* =========================
   FOOTER SPACE
========================= */

.admin-footer-space {
    height: 20px;
}


/* =========================
   RESPONSIVE
========================= */

@media(max-width: 750px) {

    .topbar {
        left: 80px;
        padding: 0 18px;
    }

    .main {
        margin-left: 80px;
    }

    .content {
        padding: 18px;
    }

    .profile-info {
        display: none;
    }

}

@media(max-width: 550px) {

    .notification {
        display: none;
    }

}



/* =========================
   DASHBOARD
========================= */

.content {
    padding: 30px 40px 40px;
}

/* Welcome Banner */

.welcome {
    background: #4B4099;
    color: #ffffff;
    border-radius: 20px;
    padding: 38px 40px;
    margin-bottom: 34px;
    position: relative;
    overflow: hidden;
}

.welcome::after {
    content: "";
    position: absolute;
    width: 320px;
    height: 320px;
    border: 40px solid rgba(255,255,255,.08);
    border-radius: 50%;
    right: 30px;
    top: -180px;
}

.welcome h1 {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 10px;
    position: relative;
    z-index: 2;
}

.welcome p {
    font-size: 16px;
    color: rgba(255,255,255,.9);
    position: relative;
    z-index: 2;
}


/* =========================
   STAT CARDS
========================= */

.cards {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
    margin-bottom: 34px;
}

.card {
    background: #ffffff;
    border: 1px solid #eeeaf7;
    border-radius: 18px;
    padding: 28px 30px;
    min-height: 165px;
    box-shadow: 0 8px 25px rgba(75,64,153,.05);
    transition: .25s;
}

.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(75,64,153,.09);
}

.card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
}

.card h4 {
    color: #858093;
    font-size: 16px;
    font-weight: 500;
}

.card-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    background: #f1effb;
    color: #4B4099;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 23px;
}

.number {
    margin-top: 22px;
    font-size: 34px;
    line-height: 1;
    font-weight: 700;
    color: #252331;
}


/* =========================
   BOTTOM GRID
========================= */

.bottom-grid {
    display: grid;
    grid-template-columns: 1.55fr 1fr;
    gap: 28px;
}

.panel {
    background: #ffffff;
    border: 1px solid #eeeaf7;
    border-radius: 18px;
    min-height: 300px;
    padding: 28px;
    box-shadow: 0 8px 25px rgba(75,64,153,.04);
}

.panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 25px;
}

.panel-header h3 {
    font-size: 20px;
    color: #282532;
}

.view-all {
    color: #4B4099;
    font-size: 14px;
    font-weight: 600;
}

.empty {
    min-height: 205px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    color: #aaa5b9;
    text-align: center;
}

.empty-icon {
    width: 55px;
    height: 55px;
    border-radius: 12px;
    background: #f5f3fb;
    color: #aaa5b9;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    margin-bottom: 15px;
}

.empty p {
    font-size: 14px;
}


/* =========================
   QUICK ACTIONS
========================= */

.quick-action {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.quick-action a {
    display: flex;
    align-items: center;
    gap: 14px;

    min-height: 58px;
    padding: 10px 15px;

    background: #f8f7fc;
    border-radius: 12px;

    color: #4d4a58;
    font-size: 14px;
    font-weight: 500;

    transition: .2s;
}

.quick-action a:hover {
    background: #f1effb;
    color: #4B4099;
    transform: translateX(3px);
}

.quick-icon {
    width: 38px;
    height: 38px;

    border-radius: 10px;

    background: #eeebfa;
    color: #4B4099;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 20px;
    font-weight: 500;
}


/* =========================
   RESPONSIVE
========================= */

@media(max-width: 1100px) {

    .cards {
        grid-template-columns: repeat(2, 1fr);
    }

    .bottom-grid {
        grid-template-columns: 1fr;
    }

}

@media(max-width: 750px) {

    .content {
        padding: 22px 18px 30px;
    }

    .welcome {
        padding: 28px;
    }

    .welcome h1 {
        font-size: 25px;
    }

    .cards {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .bottom-grid {
        grid-template-columns: 1fr;
    }

}

@media(max-width: 500px) {

    .welcome {
        padding: 24px;
    }

    .welcome h1 {
        font-size: 22px;
    }

    .welcome p {
        font-size: 14px;
    }

    .panel {
        padding: 20px;
    }

}


.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;

    padding: 11px 18px;

    background: #ffffff;
    color: #4B4099;

    border: 1px solid #e4e1f0;
    border-radius: 9px;

    font-size: 14px;
    font-weight: 600;

    text-decoration: none;

    transition: .2s;
}

.back-btn:hover {
    background: #4B4099;
    color: #ffffff;
    border-color: #4B4099;
}


.category-page-header {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 30px;
    margin-bottom: 25px;
}

.category-page-header > div {
    flex: 1;
}

.category-page-header h1 {
    margin: 0 0 6px;
    color: #17152a;
    font-size: 32px;
    line-height: 1.2;
}

.category-page-header p {
    margin: 0;
    color: #817d91;
    font-size: 16px;
}

.back-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;

    flex-shrink: 0;

    padding: 12px 20px;

    background: #ffffff;
    color: #4B4099;

    border: 1px solid #e2dfed;
    border-radius: 9px;

    font-size: 14px;
    font-weight: 600;

    text-decoration: none;
    white-space: nowrap;

    transition: all .2s ease;
}

.back-btn:hover {
    background: #4B4099;
    color: #ffffff;
    border-color: #4B4099;
}

@media(max-width:750px) {

    .category-page-header {
        flex-direction: column;
        align-items: flex-start;
    }

}
</style>



</head>

<body>