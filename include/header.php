<?php
/**
 * Header - Navbar Component
 * Include this at the top of each page after PHP logic
 */
$currentPage = basename($_SERVER['PHP_SELF']);
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
    <link rel="icon" type="image/png"
        href="<?= strpos($currentPage, 'admin/') !== false ? '../logo-pondok.png' : 'logo-pondok.png' ?>">
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

        /* Mobile Responsive */
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
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 0.75rem;
                padding-top: 75px;
            }

            h4,
            .h4 {
                font-size: 1.1rem;
            }

            .d-flex.justify-content-between {
                flex-direction: column !important;
                gap: 0.75rem !important;
            }

            /* Hide text on buttons, show only icons */
            .btn-action-text {
                display: none;
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
                <a class="navbar-brand"
                    href="<?= strpos($currentPage, 'admin/') !== false ? '../dashboard.php' : 'dashboard.php' ?>">
                    <img src="<?= strpos($currentPage, 'admin/') !== false ? '../logo-pondok.png' : 'logo-pondok.png' ?>"
                        alt="Logo" style="height: 28px; width: auto; margin-right: 8px;">
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