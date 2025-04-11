<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D24</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo url('public/css/modern-table.css'); ?>">
    
    <!-- 页面专用样式 -->
    <?php if (strpos($_SERVER['REQUEST_URI'], '/user/subagents') !== false): ?>
    <style>
        /* 用户管理页面专用内联样式 */
        table.user-management-table {
            border-collapse: separate !important;
            border-spacing: 0 !important;
        }
        
        table.user-management-table thead th {
            background-color: #f8f9fa !important;
            padding: 0.75rem 1rem !important;
            font-weight: 600 !important;
            color: #1E293B !important;
            border: none !important;
        }
        
        table.user-management-table thead th:first-child {
            border-top-left-radius: 8px !important;
        }
        
        table.user-management-table thead th:last-child {
            border-top-right-radius: 8px !important;
        }
        
        table.user-management-table .actions-column .btn {
            padding: 0.25rem 0.5rem !important;
            margin: 0.125rem !important;
            font-size: 0.75rem !important;
            display: inline-block !important;
        }
        
        table.user-management-table .user-badge,
        table.user-management-table .status-badge {
            padding: 0.35rem 0.75rem !important;
            font-weight: 500 !important;
            font-size: 0.75rem !important;
            border-radius: 20px !important;
            display: inline-block !important;
            min-width: 60px !important;
            text-align: center !important;
        }
        
        /* 强制覆盖Bootstrap表格样式 */
        .card .table.modern-table {
            margin-bottom: 0 !important;
        }
        
        .card .table.modern-table tr td,
        .card .table.modern-table tr th {
            vertical-align: middle !important;
            border-color: rgba(0, 0, 0, 0.03) !important;
        }
        
        .card .table.modern-table tr:hover {
            background-color: rgba(99, 102, 241, 0.04) !important;
        }
    </style>
    <?php endif; ?>
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
            --secondary-gradient: linear-gradient(135deg, #10B981 0%, #3B82F6 100%);
            --accent-gradient: linear-gradient(135deg, #F43F5E 0%, #EC4899 100%);
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 80px;
            --header-height: 70px;
            --card-border-radius: 16px;
            --button-border-radius: 12px;
            --body-bg: #F5F7FA;
            --card-bg: rgba(255, 255, 255, 0.85);
            --sidebar-bg: rgba(15, 23, 42, 0.95);
            --sidebar-hover: rgba(255, 255, 255, 0.1);
            --sidebar-active: rgba(255, 255, 255, 0.15);
            --sidebar-border: rgba(255, 255, 255, 0.1);
            --sidebar-icon: rgba(255, 255, 255, 0.7);
            --sidebar-text: rgba(255, 255, 255, 0.7);
            --sidebar-active-text: rgba(255, 255, 255, 1);
            --sidebar-active-icon: #8B5CF6;
        }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--body-bg);
            margin: 0;
            padding: 0;
            color: #1E293B;
            overflow-x: hidden;
        }
        
        /* 磨砂玻璃效果 */
        .glassmorphism {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        }
        
        /* 侧边栏样式 */
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            z-index: 1000;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.12);
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
        }
        
        .sidebar::-webkit-scrollbar {
            width: 4px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
        }
        
        .sidebar-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            margin-bottom: 1rem;
            position: relative;
            border-bottom: 1px solid var(--sidebar-border);
        }
        
        .sidebar-logo img {
            height: 140px;
            width: auto;
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
            filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.7));
        }
        
        .sidebar-logo::after {
            content: '';
            position: absolute;
            width: 60px;
            height: 60px;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0) 70%);
            z-index: 1;
            opacity: 0.7;
            filter: blur(8px);
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0.5rem 0.8rem;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 4px;
            position: relative;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.9rem 1.2rem;
            color: var(--sidebar-text);
            text-decoration: none;
            transition: all 0.25s ease;
            border-radius: 12px;
            position: relative;
            overflow: hidden;
            font-weight: 500;
            letter-spacing: 0.2px;
        }
        
        .sidebar-menu a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 0;
            height: 100%;
            background: linear-gradient(90deg, rgba(139, 92, 246, 0.12), rgba(139, 92, 246, 0));
            transition: width 0.3s ease;
            z-index: -1;
        }
        
        .sidebar-menu a:hover::before {
            width: 100%;
        }
        
        .sidebar-menu a:hover {
            color: var(--sidebar-active-text);
            transform: translateX(4px);
        }
        
        .sidebar-menu a.active {
            color: var(--sidebar-active-text);
            background: var(--sidebar-active);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-menu a.active::after {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--primary-gradient);
            border-radius: 0 4px 4px 0;
        }
        
        /* 完全修复报表菜单项的紫色指示线 */
        .sidebar-menu a[href="#reportSubmenu"] {
            position: relative;
            background-image: none !important;
        }
        
        .sidebar-menu a[href="#reportSubmenu"].active {
            background: var(--sidebar-active) !important;
            background-image: none !important;
        }
        
        .sidebar-menu a[href="#reportSubmenu"].active::after {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: var(--primary-gradient);
            border-radius: 0 4px 4px 0;
            transform: none;
        }
        
        .sidebar-menu a[href="#reportSubmenu"]::before {
            background: transparent;
            display: none;
        }
        
        .sidebar-menu i {
            margin-right: 12px;
            font-size: 1.25rem;
            color: var(--sidebar-icon);
            transition: all 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 24px;
        }
        
        .sidebar-menu a:hover i {
            color: var(--sidebar-active-icon);
            transform: scale(1.1);
        }
        
        .sidebar-menu a.active i {
            color: var(--sidebar-active-icon);
        }
        
        .sidebar-submenu {
            list-style: none;
            padding: 0 0 0 2.5rem;
            margin: 0.5rem 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease, opacity 0.4s ease;
            opacity: 0;
        }
        
        .sidebar-submenu.show {
            max-height: 500px;
            opacity: 1;
        }
        
        .sidebar-submenu li {
            margin-bottom: 2px;
        }
        
        .sidebar-menu-section {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 1rem 1.2rem 0.5rem;
            position: relative;
        }
        
        .sidebar-menu-section:before {
            content: '';
            position: absolute;
            left: 0;
            top: 1rem;
            bottom: 0.5rem;
            width: 3px;
            background: #8B5CF6;
            border-radius: 0 3px 3px 0;
        }
        
        .sidebar-submenu a {
            font-size: 0.9rem;
            padding: 0.6rem 1rem;
            display: flex;
            align-items: center;
            transition: all 0.25s ease;
        }
        
        .sidebar-menu .dropdown-toggle::after {
            display: inline-block;
            margin-left: auto;
            content: '\F282';
            font-family: bootstrap-icons !important;
            vertical-align: -0.125em;
            border: none;
            transition: transform 0.3s ease;
        }
        
        .sidebar-menu .dropdown-toggle[aria-expanded="true"]::after {
            transform: rotate(90deg);
        }
        
        /* 顶部导航栏 */
        .topbar {
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            height: var(--header-height);
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            box-shadow: 0 1px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* 主内容区域 */
        main {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 2rem;
            min-height: calc(100vh - var(--header-height));
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* 卡片样式 */
        .modern-card {
            background: var(--card-bg);
            border-radius: var(--card-border-radius);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.02);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.7);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .modern-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.07);
        }
        
        /* 按钮样式 */
        .btn-gradient-primary {
            background: var(--primary-gradient);
            border: none;
            color: white;
            border-radius: var(--button-border-radius);
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            font-weight: 500;
            box-shadow: 0 4px 6px rgba(99, 102, 241, 0.25);
        }
        
        .btn-gradient-primary:hover {
            box-shadow: 0 6px 10px rgba(99, 102, 241, 0.4);
            transform: translateY(-2px);
        }
        
        .btn-gradient-secondary {
            background: var(--secondary-gradient);
            border: none;
            color: white;
            border-radius: var(--button-border-radius);
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            font-weight: 500;
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.25);
        }
        
        .btn-gradient-secondary:hover {
            box-shadow: 0 6px 10px rgba(16, 185, 129, 0.4);
            transform: translateY(-2px);
        }
        
        /* 用户下拉菜单 */
        .user-dropdown {
            display: flex;
            align-items: center;
            cursor: pointer;
            color: #1E293B;
            font-weight: 500;
            padding: 0.5rem 0.75rem;
            border-radius: 12px;
            transition: background 0.3s ease;
        }
        
        .user-dropdown:hover {
            background: rgba(0, 0, 0, 0.03);
        }
        
        .user-dropdown .avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            margin-right: 12px;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 8px rgba(99, 102, 241, 0.2);
        }
        
        /* 下拉菜单样式优化 */
        .dropdown-menu {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 0.75rem 0;
            margin-top: 10px;
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.7);
            overflow: hidden;
        }
        
        .dropdown-item {
            padding: 0.6rem 1.2rem;
            color: #1E293B;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .dropdown-item:hover {
            background-color: rgba(99, 102, 241, 0.08);
            color: #6366F1;
            transform: translateX(5px);
        }
        
        .dropdown-item i {
            color: #6366F1;
            margin-right: 8px;
        }
        
        /* 响应式侧边栏折叠 */
        .toggle-sidebar {
            display: none;
            cursor: pointer;
            border: none;
            background: transparent;
            color: #1E293B;
            font-size: 1.25rem;
            transition: all 0.3s ease;
        }
        
        .toggle-sidebar:hover {
            color: var(--sidebar-active-icon);
        }
        
        /* 波浪背景 */
        .wave-bg {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
            opacity: 0.05;
            background-image: url("data:image/svg+xml,%3Csvg width='100%25' height='100%25' xmlns='http://www.w3.org/2000/svg'%3E%3Cdefs%3E%3Cpattern id='pattern' width='100' height='100' patternUnits='userSpaceOnUse'%3E%3Cpath d='M50 0C22.4 0 0 22.4 0 50s22.4 50 50 50 50-22.4 50-50S77.6 0 50 0z' fill='%236366F1' fill-opacity='0.4'/%3E%3C/pattern%3E%3C/defs%3E%3Crect width='100%25' height='100%25' fill='url(%23pattern)'/%3E%3C/svg%3E");
        }
        
        /* 侧边栏折叠状态 */
        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }
        
        .sidebar.collapsed .sidebar-logo {
            padding: 1rem 0;
        }
        
        .sidebar.collapsed .sidebar-logo img {
            height: 50px;
        }
        
        .sidebar.collapsed .sidebar-menu a span {
            opacity: 0;
            width: 0;
            display: none;
        }
        
        .sidebar.collapsed .sidebar-menu i {
            margin-right: 0;
            font-size: 1.4rem;
        }
        
        .sidebar.collapsed .dropdown-toggle::after {
            display: none;
        }
        
        .sidebar.collapsed .sidebar-submenu {
            display: none;
        }
        
        .sidebar.collapsed + .topbar,
        .sidebar.collapsed ~ main {
            left: var(--sidebar-collapsed-width);
        }
        
        /* 移动设备样式调整 */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                box-shadow: none;
            }
            
            .sidebar.show {
                transform: translateX(0);
                box-shadow: 0 0 25px rgba(0, 0, 0, 0.15);
            }
            
            .topbar, main {
                left: 0;
                width: 100%;
            }
            
            main {
                margin-left: 0;
            }
            
            .toggle-sidebar {
                display: block;
            }
            
            /* 侧边栏显示时的遮罩 */
            .sidebar-backdrop {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }
            
            .sidebar-backdrop.show {
                opacity: 1;
                visibility: visible;
            }
        }
        
        /* 动画效果 */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        @keyframes glow {
            0% { box-shadow: 0 0 0 rgba(139, 92, 246, 0); }
            50% { box-shadow: 0 0 20px rgba(139, 92, 246, 0.3); }
            100% { box-shadow: 0 0 0 rgba(139, 92, 246, 0); }
        }

        /* Success Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1050; /* Ensure it's above other content */
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        .modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        .modal-content {
            background: white;
            padding: 30px 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transform: scale(0.9);
            transition: transform 0.3s ease;
            max-width: 400px;
            width: 90%;
        }
        .modal-overlay.show .modal-content {
            transform: scale(1);
        }
        .modal-icon {
            margin-bottom: 20px;
        }
        .modal-content h4 {
            color: #28a745;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .modal-content p {
            color: #6c757d;
            margin-bottom: 25px;
        }
        .modal-content .btn {
            padding: 10px 30px;
            border-radius: 8px;
        }

        /* Checkmark Animation */
        .checkmark__circle {
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            stroke-width: 2;
            stroke-miterlimit: 10;
            stroke: #4CAF50;
            fill: none;
            animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }
        .checkmark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: block;
            stroke-width: 3;
            stroke: #fff;
            stroke-miterlimit: 10;
            margin: 10px auto;
            box-shadow: inset 0px 0px 0px #4CAF50;
            animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
        }
        .checkmark__check {
            transform-origin: 50% 50%;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            stroke-width: 3;
            animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
        }
        @keyframes stroke {
            100% {
                stroke-dashoffset: 0;
            }
        }
        @keyframes scale {
            0%, 100% {
                transform: none;
            }
            50% {
                transform: scale3d(1.1, 1.1, 1);
            }
        }
        @keyframes fill {
            100% {
                box-shadow: inset 0px 0px 0px 40px #4CAF50;
            }
        }

        /* Error Modal Styles */
        .error-modal .modal-content h4 {
            color: #dc3545; /* Bootstrap danger color */
        }
        .error-cross__circle {
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            stroke-width: 2;
            stroke-miterlimit: 10;
            stroke: #dc3545;
            fill: none;
            animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }
        .error-cross {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: block;
            stroke-width: 3;
            stroke: #fff;
            stroke-miterlimit: 10;
            margin: 10px auto;
            box-shadow: inset 0px 0px 0px #dc3545;
            animation: fillError .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
        }
        .error-cross__line1,
        .error-cross__line2 {
            transform-origin: 50% 50%;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            stroke-width: 3;
            animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
        }
        @keyframes fillError {
            100% {
                box-shadow: inset 0px 0px 0px 40px #dc3545;
            }
        }

        /* Responsive Card-based Table Styles (Grid Inside TD) */
        @media (max-width: 767px) { 
            .modern-table thead,
            .user-management-table thead {
                display: none !important; 
            }
            body main div.table-responsive table.modern-table tr.user-row,
            body main div.table-responsive table.user-management-table tr {
                display: block !important;
                margin-bottom: 1em !important;
                border: 1px solid #ddd !important;
                border-radius: 8px !important;
                padding: 0.5em !important; /* Padding on the card row */
                box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
                background: #fff !important;
                width: 100%; 
                box-sizing: border-box;
                height: auto !important; 
            }
            body main div.table-responsive table.modern-table tr.user-row td,
            body main div.table-responsive table.user-management-table tr td {
                display: grid !important; /* TD is a grid container */
                grid-template-columns: auto 1fr; /* Label takes auto, value takes rest */
                gap: 0 10px; /* No row gap, 10px column gap */
                align-items: center; /* Center items vertically */
                border-bottom: 1px solid #eee !important;
                padding: 0.6em 0.5em !important; /* Padding within the cell */
                text-align: left !important; 
                min-height: 30px !important; /* Minimum height */
                height: auto !important;
                box-sizing: border-box !important;
            }
            body main div.table-responsive table.modern-table tr.user-row td:last-child,
            body main div.table-responsive table.user-management-table tr td:last-child {
                border-bottom: 0 !important;
            }

            /* Label Style */
            body main div.table-responsive table.modern-table tr.user-row td:before,
            body main div.table-responsive table.user-management-table tr td:before {
                content: attr(data-label) !important; 
                grid-column: 1 / 2; /* Place in first column */
                font-weight: bold !important;
                font-size: 0.8em;
                color: #555;
                text-align: left !important;
                white-space: nowrap;
                /* Reset other properties */
                display: inline !important;
                margin: 0 !important;
                padding: 0 !important;
                text-transform: none;
                position: static !important;
            }
            
            /* Value Styling */
            body main div.table-responsive table.modern-table tr.user-row td span.value,
            body main div.table-responsive table.user-management-table tr td span.value {
                 grid-column: 2 / 3; /* Place in second column */
                 display: block; /* Value content takes block */
                 font-size: 0.9em; 
                 word-wrap: break-word;
                 text-align: right; /* Align value right */
                 line-height: 1.4;
                 width: 100%; /* Ensure it uses the grid column width */
             }
             /* Left align specific values */
             body main div.table-responsive table.modern-table tr.user-row td[data-label="用户名"] span.value,
             body main div.table-responsive table.user-management-table tr td[data-label="用户名"] span.value,
             body main div.table-responsive table.modern-table tr.user-row td[data-label="昵称"] span.value,
             body main div.table-responsive table.user-management-table tr td[data-label="昵称"] span.value,
             body main div.table-responsive table.modern-table tr.user-row td[data-label="上级"] span.value,
             body main div.table-responsive table.user-management-table tr td[data-label="上级"] span.value,
             body main div.table-responsive table.modern-table tr.user-row td[data-label="创建时间"] span.value,
             body main div.table-responsive table.user-management-table tr td[data-label="创建时间"] span.value {
                  text-align: right !important;
             }
             /* Badge alignment */
             body main div.table-responsive table.modern-table tr.user-row td span.value .badge,
             body main div.table-responsive table.user-management-table tr td span.value .badge {
                 display: inline-block;
                 float: none; /* No float needed */
                 margin-left: 5px;
                 vertical-align: middle;
             }
             
            /* Actions Column adjustments */
            body main div.table-responsive table.modern-table tr.user-row td.actions-column {
                 display: block !important; /* Override grid */
                 text-align: center !important; 
                 padding: 10px 5px !important; 
                 grid-column: 1 / 3; /* Ensure it spans columns if grid wasn't overridden */
            }
             body main div.table-responsive table.modern-table tr.user-row td.actions-column:before,
             body main div.table-responsive table.user-management-table tr td.actions-column:before {
                  display: none !important;
             }
             
             /* Force hide original buttons on mobile */
             body main div.table-responsive .actions-column > a.d-md-inline-block {
                 display: none !important;
             }

            .shadow-sm {
                display: none !important;
             }

             /* Force show dropdown on mobile */
             body main div.table-responsive .actions-column .dropdown.d-md-none {
                 display: inline-block !important;
             }
        }
        
        /* Specific Mobile Styles for Order Create Page */
        @media (max-width: 767px) {
            /* Reduce main padding on betting page for more width */
            body.page-order-create main {
                padding: 1rem 0.5rem !important; /* Reduce left/right padding */
            }

            body.page-order-create .opening-dates-section,
            body.page-order-create .user-info-section,
            body.page-order-create .betting-instructions-section,
            body.page-order-create .betting-area .receipt-column { /* Hide receipt column */
                display: none !important; 
            }

            body.page-order-create .betting-area .input-column { /* Make input column full width */
                width: 100% !important; 
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }
             body.page-order-create .betting-area textarea#bet_content {
                min-height: 300px; /* Increase height */
                font-size: 1.1rem !important; /* Increase font size */
             }
             /* Ensure buttons are visible and sized appropriately */
             body.page-order-create .betting-area .input-column .d-flex .btn {
                 padding: 0.75rem 1rem;
                 font-size: 1rem;
             }
        }

        /* Specific Styles for Order List Table on Mobile */
        @media (max-width: 767px) { 
            .order-list-table thead {
                display: none !important; 
            }
            body main div.table-responsive table.order-list-table tr {
                display: block !important;
                margin-bottom: 1em !important;
                border: 1px solid #ddd !important;
                border-radius: 8px !important;
                padding: 0.8em !important; 
                box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
                background: #fff !important;
                width: 100%; 
                box-sizing: border-box;
                height: auto !important; 
            }
            body main div.table-responsive table.order-list-table tr td {
                display: grid !important; 
                grid-template-columns: 40% auto; 
                gap: 0 10px; 
                align-items: center; 
                border-bottom: 1px solid #eee !important;
                padding: 0.6em 0.5em !important; 
                text-align: left !important; 
                min-height: 30px !important; 
                height: auto !important;
                box-sizing: border-box !important;
            }
            body main div.table-responsive table.order-list-table tr td:last-child {
                border-bottom: 0 !important;
            }
            body main div.table-responsive table.order-list-table tr td:before {
                content: attr(data-label) !important; 
                grid-column: 1 / 2; 
                font-weight: bold !important;
                font-size: 1rem;
                color: #555;
                text-align: left !important;
                white-space: nowrap;
                display: inline !important;
                margin: 0 !important;
                padding: 0 !important;
                text-transform: none;
                position: static !important;
            }
            body main div.table-responsive table.order-list-table tr td span.value,
            body main div.table-responsive table.order-list-table tr td {
                grid-column: 2 / 3 !important; 
                text-align: right !important; 
                word-wrap: break-word;
            }
             body main div.table-responsive table.order-list-table tr td[data-label="用户"] span.value,
             body main div.table-responsive table.order-list-table tr td[data-label="创建时间"] span.value {
                  text-align: right !important;
             }
             body main div.table-responsive table.order-list-table tr td span.value .badge {
                 grid-column: 2 / 3; 
                 text-align: center;
                 display: inline-block; 
                 width: auto;
                 float: none; 
                 justify-self: end; 
                 margin-left: 5px; 
             }
            body main div.table-responsive table.order-list-table tr td.actions-column {
                 display: block !important; 
                 text-align: center !important; 
                 padding: 10px 5px !important; 
                 grid-column: 1 / 3; 
            }
             body main div.table-responsive table.order-list-table tr td.actions-column:before {
                  display: none !important;
             }
             body main div.table-responsive table.order-list-table tr td.actions-column .btn,
             body main div.table-responsive table.order-list-table tr td.actions-column > a {
                 display: inline-block !important; 
                 margin: 3px !important;
                 /* Hide original buttons on mobile */
                 &.d-md-inline-block {
                     display: none !important;
                 }
             }
             /* Ensure dropdown shows */
             body main div.table-responsive table.order-list-table tr td.actions-column .dropdown.d-md-none {
                 display: inline-block !important;
             }
        }
    </style>
</head>
<?php 
// Determine body class based on current page/controller
$body_class = '';
// Example: Detect if we are on the order create page
if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/order/create') !== false) {
    $body_class = 'page-order-create';
}
// Add more conditions for other pages if needed
?>
<body class="<?php echo $body_class; ?>">
    <div class="wave-bg"></div>
    <div class="sidebar-backdrop"></div>
    
    <!-- 侧边栏 -->
    <div class="sidebar">
        <div class="sidebar-logo">
            <img src="<?php echo asset('images/logo-white.png'); ?>" alt="D24 Logo">
        </div>
        
        <ul class="sidebar-menu">
            <li>
                <a href="<?php echo url('home'); ?>" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/home') !== false ? 'active' : ''; ?>">
                    <i class="bi bi-house-door"></i>
                    <span>首页</span>
                </a>
            </li>
            
            <?php if (isset($_SESSION['user'])): ?>
                <li>
                    <a href="<?php echo url('user/subagents'); ?>" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/user/subagents') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-people"></i>
                        <span>代理管理</span>
                    </a>
                </li>
                
                <li>
                    <a href="<?php echo url('user/create'); ?>" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/user/create') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-person-plus"></i>
                        <span>创建下线</span>
                    </a>
                </li>
                
                <li>
                    <a href="<?php echo url('order/create'); ?>" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/order/create') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-cash"></i>
                        <span>下注</span>
                    </a>
                </li>
                
                <li>
                    <a href="<?php echo url('order'); ?>" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/order') !== false && strpos($_SERVER['REQUEST_URI'], '/order/create') === false ? 'active' : ''; ?>">
                        <i class="bi bi-list-check"></i>
                        <span>订单查询</span>
                    </a>
                </li>
                
                <li>
                    <a href="#reportSubmenu" class="dropdown-toggle <?php echo strpos($_SERVER['REQUEST_URI'], '/report') !== false ? 'active' : ''; ?>" data-bs-toggle="collapse" aria-expanded="<?php echo strpos($_SERVER['REQUEST_URI'], '/report') !== false ? 'true' : 'false'; ?>">
                        <i class="bi bi-graph-up" style="display: inline-flex; width: 24px; height: 24px; justify-content: center; align-items: center; flex-shrink: 0;"></i>
                        <span>报表</span>
                    </a>
                    <ul class="sidebar-submenu collapse <?php echo strpos($_SERVER['REQUEST_URI'], '/report') !== false ? 'show' : ''; ?>" id="reportSubmenu">
                        <?php if (isset($_SESSION['user']['role']) && in_array($_SESSION['user']['role'], ['admin', 'super_admin', 'agent'])):
                            $user_role = $_SESSION['user']['role'];
                        ?>
                            <li class="sidebar-menu-section">报表 & 统计</li>
                            <li>
                                <a href="<?php echo url('report/financial'); ?>" <?php echo strpos($_SERVER['REQUEST_URI'], '/report/financial') !== false ? 'class="active"' : ''; ?>>
                                    <i class="bi bi-currency-exchange"></i> 财务报表
                                </a>
                            </li>
                            
                            <li>
                                <a href="<?php echo url('report/user'); ?>" <?php echo strpos($_SERVER['REQUEST_URI'], '/report/user') !== false ? 'class="active"' : ''; ?>>
                                    <i class="bi bi-people"></i> 用户报表
                                </a>
                            </li>

                            <li>
                                <a href="<?php echo url('report/transactions'); ?>" <?php echo strpos($_SERVER['REQUEST_URI'], '/report/transactions') !== false ? 'class="active"' : ''; ?>>
                                    <i class="bi bi-clipboard-data"></i> 交易记录
                                </a>
                            </li>
                            <?php /* Remove Commission Report Link
                            <li>
                                <a href="<?php echo url('report/commission'); ?>" <?php echo strpos($_SERVER['REQUEST_URI'], '/report/commission') !== false ? 'class="active"' : ''; ?>>
                                    <i class="bi bi-wallet2"></i> 佣金记录
                                </a>
                            </li>
                            */ ?>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- 顶部导航栏 -->
    <div class="topbar">
        <div class="d-flex align-items-center">
            <button class="toggle-sidebar me-3">
                <i class="bi bi-list"></i>
            </button>
            <div class="d-none d-lg-block">
                <!-- 页面标题可以在这里添加 -->
            </div>
        </div>
        
        <div class="d-flex align-items-center">
            <div class="search-bar d-none d-md-block me-3">
                <!-- 搜索框可以在这里添加 -->
            </div>
            
            <?php if (isset($_SESSION['user'])): ?>
                <div class="user-dropdown" id="userDropdown" data-bs-toggle="dropdown">
                    <div class="avatar">
                        <?php echo substr($_SESSION['user']['username'], 0, 1); ?>
                    </div>
                    <div class="user-info d-none d-md-block">
                        <div><?php echo $_SESSION['user']['username']; ?></div>
                        <small class="text-muted"><?php echo $_SESSION['user']['role']; ?></small>
                    </div>
                    <i class="bi bi-chevron-down ms-2"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item d-flex align-items-center" href="<?php echo url('auth/logout'); ?>"><i class="bi bi-box-arrow-right me-2"></i>退出登录</a></li>
                </ul>
            <?php else: ?>
                <a href="<?php echo url('auth/login'); ?>" class="btn-gradient-primary">
                    <i class="bi bi-box-arrow-in-right me-2"></i>登录
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- 主内容区域 -->
    <main>
