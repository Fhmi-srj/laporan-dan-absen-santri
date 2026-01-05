<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin SPMB' ?></title>
    <link href="../images/logo-pondok.png" rel="icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>

        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#E67E22',
                        'primary-dark': '#D35400',
                        'primary-light': '#F39C12',
                    }
                }
            }
        }
    </script>
    <style>
        /* Layout: Fixed sidebar, scrollable content */
        .admin-wrapper {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            flex-shrink: 0;
            transition: transform 0.3s ease;
        }

        .main-content {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }

        /* Mobile sidebar behavior */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed !important;
                max-height: 100vh;
                overflow-y: auto;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-top: 56px;
            }
        }

        /* Scrollbar styling */
        .main-content::-webkit-scrollbar {
            width: 8px;
        }

        .main-content::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .main-content::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .main-content::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }

        /* ===== MODAL STYLING ===== */
        .modal-overlay {
            backdrop-filter: blur(4px);
            animation: fadeIn 0.2s ease-out;
        }

        .modal-container {
            animation: slideUp 0.3s ease-out;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .modal-header {
            background: linear-gradient(135deg, #E67E22 0%, #F39C12 100%);
            color: white;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem 0.75rem 0 0;
        }

        .modal-header h3 {
            font-weight: 700;
            font-size: 1rem;
        }

        .modal-header .close-btn {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.2s;
        }

        .modal-header .close-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 1.25rem;
        }

        .modal-body .form-group {
            margin-bottom: 1rem;
        }

        .modal-body label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.375rem;
        }

        .modal-body input,
        .modal-body select,
        .modal-body textarea {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s;
            background: #fafafa;
        }

        .modal-body input:focus,
        .modal-body select:focus,
        .modal-body textarea:focus {
            outline: none;
            border-color: #E67E22;
            box-shadow: 0 0 0 3px rgba(0, 79, 79, 0.1);
            background: white;
        }

        .modal-body input::placeholder {
            color: #9ca3af;
        }

        .modal-footer {
            display: flex;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            border-top: 1px solid #f3f4f6;
            background: #fafafa;
            border-radius: 0 0 0.75rem 0.75rem;
        }

        .modal-footer .btn {
            flex: 1;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: all 0.2s;
            cursor: pointer;
        }

        .modal-footer .btn-cancel {
            background: white;
            border: 1.5px solid #e5e7eb;
            color: #6b7280;
        }

        .modal-footer .btn-cancel:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        .modal-footer .btn-primary {
            background: linear-gradient(135deg, #E67E22 0%, #F39C12 100%);
            border: none;
            color: white;
        }

        .modal-footer .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 79, 79, 0.3);
        }

        .modal-footer .btn-danger {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
            border: none;
            color: white;
        }

        .modal-footer .btn-danger:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        /* Delete Modal Special Styling */
        .delete-modal-icon {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            animation: pulse 2s infinite;
        }

        .delete-modal-icon i {
            font-size: 1.75rem;
            color: #dc2626;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }
    </style>
</head>

<body class="bg-gray-100">