<?php
/**
 * Header - Navbar Component
 * Include this at the top of each page after PHP logic
 */
$currentPage = basename($_SERVER['PHP_SELF']);
$isAdminPage = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
$basePath = $isAdminPage ? '../' : '';
$user = getCurrentUser();
$role = $user['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $pageTitle ?? 'Dashboard' ?> -
        <?= APP_NAME ?>
    </title>
    <link rel="icon" type="image/png" href="<?= $basePath ?>logo-pondok.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php if (isset($extraCss))
        echo $extraCss; ?>
    <style>
        :root {
            --primary-color: #3b82f6;
            --primary-hover: #2563eb;
            --bg-color: #f8fafc;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE 10+ */
        }

        body::-webkit-scrollbar,
        *::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari, Edge */
        }

        * {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .navbar-custom {
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
        }

        .stat-label {
            font-size: 0.85rem;
            color: #64748b;
        }

        .card-custom {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
            overflow: hidden;
        }

        .card-header-custom {
            padding: 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            font-weight: 600;
        }

        .main-content {
            margin-left: 250px;
            padding: 2rem;
            padding-top: 90px;
        }

        /* Mobile Responsive - Tablet */
        @media (max-width: 991px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
                padding-top: 80px;
            }

            .stat-card {
                padding: 1rem;
            }

            .stat-value {
                font-size: 1.5rem;
            }

            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .card-custom {
                border-radius: 12px;
            }

            .navbar-brand {
                font-size: 0.9rem;
            }

            .navbar-brand .me-2 {
                display: none;
            }

            /* Table responsive */
            .table-responsive {
                font-size: 0.85rem;
            }

            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }

            /* Modal adjustments */
            .modal-dialog {
                margin: 0.5rem;
            }

            .modal-body {
                padding: 1rem !important;
            }

            /* Form controls */
            .form-control-custom,
            .form-control,
            .form-select {
                font-size: 16px !important;
                /* Prevents zoom on iOS */
            }

            /* Card padding */
            .card-custom.p-4 {
                padding: 1rem !important;
            }

            /* Page titles */
            h4.fw-bold,
            .fw-bold.mb-4 {
                font-size: 1.25rem;
            }

            /* Row gaps */
            .row.g-4 {
                --bs-gutter-x: 1rem;
                --bs-gutter-y: 1rem;
            }
        }

        /* Mobile Responsive - Phone */
        @media (max-width: 767px) {
            .main-content {
                padding: 0.75rem;
                padding-top: 75px;
            }

            h4,
            .h4,
            h4.fw-bold {
                font-size: 1.1rem;
                margin-bottom: 0.75rem !important;
            }

            h5,
            .h5 {
                font-size: 1rem;
            }

            h6,
            .h6 {
                font-size: 0.9rem;
            }

            /* Cards */
            .card-custom {
                border-radius: 10px;
            }

            .card-custom.p-4,
            .card-custom.p-3 {
                padding: 0.875rem !important;
            }

            /* Stat cards */
            .stat-card {
                padding: 0.875rem;
            }

            .stat-value {
                font-size: 1.35rem;
            }

            .stat-label {
                font-size: 0.75rem;
            }

            .stat-icon {
                width: 36px;
                height: 36px;
                font-size: 0.9rem;
                border-radius: 8px;
            }

            /* Tables */
            .table {
                font-size: 0.8rem;
            }

            .table th,
            .table td {
                padding: 0.5rem 0.4rem;
            }

            .table-responsive {
                font-size: 0.8rem;
            }

            /* Buttons */
            .btn {
                font-size: 0.85rem;
                padding: 0.4rem 0.75rem;
            }

            .btn-sm {
                font-size: 0.7rem;
                padding: 0.2rem 0.4rem;
            }

            .btn-lg {
                font-size: 0.95rem;
                padding: 0.5rem 1rem;
            }

            /* Forms */
            .form-label {
                font-size: 0.85rem;
                margin-bottom: 0.3rem;
            }

            .form-control,
            .form-select {
                font-size: 0.9rem;
                padding: 0.5rem 0.75rem;
            }

            .form-control-lg,
            .form-select-lg {
                font-size: 0.95rem;
                padding: 0.5rem 0.75rem;
            }

            /* Alerts */
            .alert {
                font-size: 0.85rem;
                padding: 0.75rem;
            }

            /* Badges */
            .badge {
                font-size: 0.7rem;
                padding: 0.25rem 0.5rem;
            }

            /* Modals */
            .modal-dialog {
                margin: 0.5rem;
                max-width: calc(100% - 1rem);
            }

            .modal-header {
                padding: 0.875rem 1rem;
            }

            .modal-title {
                font-size: 1rem;
            }

            .modal-body {
                padding: 1rem !important;
            }

            .modal-footer {
                padding: 0.75rem 1rem;
            }

            /* Pagination */
            .pagination {
                flex-wrap: wrap;
                justify-content: center;
            }

            .page-link {
                padding: 0.35rem 0.6rem;
                font-size: 0.8rem;
            }

            /* Row adjustments */
            .row.g-4 {
                --bs-gutter-x: 0.75rem;
                --bs-gutter-y: 0.75rem;
            }

            .row.g-3 {
                --bs-gutter-x: 0.5rem;
                --bs-gutter-y: 0.5rem;
            }

            /* Flex utilities */
            .d-flex.gap-2 {
                gap: 0.4rem !important;
            }

            .d-flex.gap-3 {
                gap: 0.5rem !important;
            }

            /* Filter sections */
            .filter-section .form-control,
            .filter-section .form-select {
                font-size: 0.85rem;
            }
        }

        /* Mobile Responsive - Small Phone */
        @media (max-width: 575px) {
            .main-content {
                padding: 0.5rem;
                padding-top: 70px;
            }

            h4,
            .h4,
            h4.fw-bold {
                font-size: 1rem;
            }

            .d-flex.justify-content-between {
                flex-direction: column !important;
                gap: 0.5rem !important;
                align-items: stretch !important;
            }

            .d-flex.justify-content-between .btn {
                width: 100%;
            }

            /* Hide text on buttons, show only icons */
            .btn-action-text {
                display: none;
            }

            /* Cards */
            .card-custom {
                border-radius: 8px;
            }

            .card-custom.p-4,
            .card-custom.p-3 {
                padding: 0.75rem !important;
            }

            /* Stat cards in grid */
            .col-6 .stat-card,
            .col-sm-6 .stat-card {
                padding: 0.75rem;
            }

            .stat-value {
                font-size: 1.2rem;
            }

            .stat-label {
                font-size: 0.7rem;
            }

            .stat-icon {
                width: 32px;
                height: 32px;
                font-size: 0.8rem;
            }

            /* Tables */
            .table {
                font-size: 0.75rem;
            }

            .table th,
            .table td {
                padding: 0.4rem 0.3rem;
            }

            /* Buttons */
            .btn {
                font-size: 0.8rem;
                padding: 0.35rem 0.6rem;
            }

            .btn-sm {
                font-size: 0.65rem;
                padding: 0.15rem 0.35rem;
            }

            /* Forms */
            .form-label {
                font-size: 0.8rem;
            }

            .form-control,
            .form-select {
                font-size: 0.85rem;
                padding: 0.4rem 0.6rem;
            }

            .mb-3 {
                margin-bottom: 0.75rem !important;
            }

            .mb-4 {
                margin-bottom: 1rem !important;
            }

            /* Alerts */
            .alert {
                font-size: 0.8rem;
                padding: 0.6rem;
            }

            .alert ul {
                padding-left: 1.25rem;
                margin-bottom: 0;
            }

            /* Modals */
            .modal-dialog {
                margin: 0.25rem;
                max-width: calc(100% - 0.5rem);
            }

            .modal-header {
                padding: 0.75rem;
            }

            .modal-title {
                font-size: 0.95rem;
            }

            .modal-body {
                padding: 0.75rem !important;
            }

            /* Hide some columns on very small screens */
            .table .hide-xs {
                display: none !important;
            }

            /* Pagination */
            .page-link {
                padding: 0.3rem 0.5rem;
                font-size: 0.75rem;
            }

            /* Row adjustments */
            .row.g-4 {
                --bs-gutter-x: 0.5rem;
                --bs-gutter-y: 0.5rem;
            }
        }

        /* Hamburger button */
        .btn-hamburger {
            display: none;
            background: none;
            border: none;
            font-size: 1.25rem;
            color: var(--primary-color);
            padding: 0.5rem;
        }

        @media (max-width: 991px) {
            .btn-hamburger {
                display: block;
            }
        }

        /* Sortable Table Styles */
        .table-sortable thead th {
            cursor: pointer;
            user-select: none;
            position: relative;
            padding-right: 25px !important;
            transition: background-color 0.15s;
        }

        .table-sortable thead th:hover {
            background-color: #f1f5f9;
        }

        .table-sortable thead th::after {
            content: '\f0dc';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.3;
            font-size: 0.75rem;
        }

        .table-sortable thead th.sort-asc::after {
            content: '\f0de';
            opacity: 1;
            color: var(--primary-color);
        }

        .table-sortable thead th.sort-desc::after {
            content: '\f0dd';
            opacity: 1;
            color: var(--primary-color);
        }

        .table-sortable thead th.no-sort {
            cursor: default;
            padding-right: 12px !important;
        }

        .table-sortable thead th.no-sort::after {
            display: none;
        }
    </style>
    <?php if (isset($extraStyles))
        echo $extraStyles; ?>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-custom fixed-top">
        <div class="container-fluid px-3 px-md-4">
            <div class="d-flex align-items-center">
                <button class="btn-hamburger me-2" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#sidebarMobile">
                    <i class="fas fa-bars"></i>
                </button>
                <a class="navbar-brand" href="<?= $basePath ?>beranda.php">
                    <img src="<?= $basePath ?>logo-pondok.png" alt="Logo"
                        style="height: 28px; width: auto; margin-right: 8px;">
                    <?= APP_NAME ?>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 gap-md-3">
                <span class="text-muted small d-none d-sm-inline">
                    <?= e($user['name'] ?? 'Guest') ?> (<?= e($role) ?>)
                </span>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmLogout()">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </div>
        </div>
    </nav>