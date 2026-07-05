<?php
/**
 * Web Application: Cloud Server & Hosting Cost Estimator
 * Lab Topic: PHP & FORM INPUT
 * Author: Nattawut Pumprasert (Tan)
 */

// Initialize default values for form state retention
$server_name = "My Production Server";
$cpu_cores = 2;
$ram_gb = 4;
$server_tier = "shared";
$billing_cycle = "monthly";
$selected_addons = [];

$is_calculated = false;
$errors = [];

// Results calculation variables
$cpu_rate = 5; // Base price for Shared CPU per core per month
$cpu_cost = 0;
$ram_cost = 0;
$spec_subtotal = 0;
$addons_subtotal = 0;
$monthly_subtotal = 0;
$total_cost = 0;
$yearly_savings = 0;

// Mapping values for display
$tier_names = [
    "shared" => "Shared CPU (ประหยัดพลังงาน)",
    "dedicated" => "Dedicated CPU (ประสิทธิภาพสูง)",
    "high_memory" => "High-Memory CPU (สำหรับฐานข้อมูลขนาดใหญ่)"
];

$addon_details = [
    "backup" => ["name" => "ระบบสำรองข้อมูลอัตโนมัติ (Daily Backup)", "price" => 5],
    "database" => ["name" => "บริการจัดการฐานข้อมูล (Managed Database)", "price" => 15],
    "security" => ["name" => "ใบรับรอง SSL & ระบบป้องกัน DDoS Protection", "price" => 10],
    "cdn" => ["name" => "เครือข่ายกระจายข้อมูลทั่วโลก (Global CDN)", "price" => 8]
];

// Handle Form Submission via POST Method
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $is_calculated = true;
    
    // 1. Sanitize and validate Server Name (Text Input)
    if (isset($_POST["server_name"]) && trim($_POST["server_name"]) !== "") {
        $server_name = htmlspecialchars(trim($_POST["server_name"]), ENT_QUOTES, 'UTF-8');
    } else {
        $errors[] = "กรุณากรอกชื่อเซิร์ฟเวอร์โครงการของคุณ";
        $server_name = "";
    }
    
    // 2. Validate CPU Cores (Number Input)
    if (isset($_POST["cpu_cores"])) {
        $cpu_cores = intval($_POST["cpu_cores"]);
        if ($cpu_cores < 1) {
            $cpu_cores = 1;
            $errors[] = "จำนวน CPU Cores ต้องมีอย่างน้อย 1 Core";
        } elseif ($cpu_cores > 64) {
            $cpu_cores = 64;
            $errors[] = "จำนวน CPU Cores สูงสุดไม่เกิน 64 Cores";
        }
    }
    
    // 3. Validate RAM Size (Number Input)
    if (isset($_POST["ram_gb"])) {
        $ram_gb = intval($_POST["ram_gb"]);
        if ($ram_gb < 1) {
            $ram_gb = 1;
            $errors[] = "ขนาด RAM ต้องมีอย่างน้อย 1 GB";
        } elseif ($ram_gb > 512) {
            $ram_gb = 512;
            $errors[] = "ขนาด RAM สูงสุดไม่เกิน 512 GB";
        }
    }
    
    // 4. Validate Server Tier (Radio Group 1)
    if (isset($_POST["server_tier"]) && in_array($_POST["server_tier"], ["shared", "dedicated", "high_memory"])) {
        $server_tier = $_POST["server_tier"];
    } else {
        $errors[] = "กรุณาเลือกประเภทระดับประสิทธิภาพเซิร์ฟเวอร์";
    }
    
    // 5. Validate Billing Cycle (Radio Group 2)
    if (isset($_POST["billing_cycle"]) && in_array($_POST["billing_cycle"], ["monthly", "yearly"])) {
        $billing_cycle = $_POST["billing_cycle"];
    } else {
        $billing_cycle = "monthly";
    }
    
    // 6. Validate Addons (Checkboxes)
    if (isset($_POST["addons"]) && is_array($_POST["addons"])) {
        foreach ($_POST["addons"] as $addon) {
            if (array_key_exists($addon, $addon_details)) {
                $selected_addons[] = htmlspecialchars($addon, ENT_QUOTES, 'UTF-8');
            }
        }
    }
    
    // Cost calculation logic if no errors occur
    if (empty($errors)) {
        // Adjust CPU Rate according to Tier
        if ($server_tier === "dedicated") {
            $cpu_rate = 15;
        } elseif ($server_tier === "high_memory") {
            $cpu_rate = 25;
        } else {
            $cpu_rate = 5;
        }
        
        $cpu_cost = $cpu_cores * $cpu_rate;
        $ram_cost = $ram_gb * 1.50; // $1.50 per GB of RAM
        $spec_subtotal = $cpu_cost + $ram_cost;
        
        // Addons Summation
        foreach ($selected_addons as $addon) {
            if (isset($addon_details[$addon])) {
                $addons_subtotal += $addon_details[$addon]["price"];
            }
        }
        
        $monthly_subtotal = $spec_subtotal + $addons_subtotal;
        
        if ($billing_cycle === "monthly") {
            $total_cost = $monthly_subtotal;
        } else {
            // Yearly billing receives 2 months free discount (Subtotal * 10)
            $total_cost = $monthly_subtotal * 10;
            $yearly_savings = $monthly_subtotal * 2;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cloud Server Cost Estimator | PHP Form Calculation Lab</title>
    <meta name="description" content="ระบบคำนวณค่าบริการเซิร์ฟเวอร์ระบบคลาวด์ พัฒนาขึ้นด้วยเทคโนโลยี PHP และการประมวลผล Form Input POST Method สไตล์ Glassmorphism สวยงาม">
    
    <!-- Google Fonts: Prompt & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-color: #6366f1; /* Indigo */
            --secondary-color: #06b6d4; /* Cyan */
            --dark-bg: #0b0f19;
            --card-bg: rgba(255, 255, 255, 0.03);
            --card-border: rgba(255, 255, 255, 0.08);
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
            --gradient-main: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            --accent-green: #10b981;
        }

        body {
            font-family: 'Prompt', 'Outfit', sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-light);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        /* Ambient Glow Spheres in background */
        .glow-sphere {
            position: absolute;
            width: 450px;
            height: 450px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, rgba(6, 182, 212, 0) 70%);
            filter: blur(60px);
            z-index: -1;
            pointer-events: none;
        }

        .glow-left {
            top: 15%;
            left: -150px;
        }

        .glow-right {
            bottom: 10%;
            right: -150px;
        }

        /* Custom Glassmorphism Navigation */
        .custom-nav {
            background: rgba(11, 15, 25, 0.8) !important;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-bottom: 1px solid var(--card-border);
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: 700;
            letter-spacing: 1px;
            background: var(--gradient-main);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-link {
            color: var(--text-muted) !important;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--text-light) !important;
        }

        /* Hero Header */
        .page-header {
            padding-top: 120px;
            padding-bottom: 30px;
            text-align: center;
        }

        .gradient-text {
            background: var(--gradient-main);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Glassmorphic Calculation Cards */
        .glass-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 18px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .glass-card:hover {
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: 0 15px 35px rgba(99, 102, 241, 0.1);
        }

        /* Styled Labels & Controls */
        .form-label-custom {
            font-weight: 500;
            color: var(--text-light);
            margin-bottom: 8px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-control-custom {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: var(--text-light);
            border-radius: 10px;
            padding: 12px 16px;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .form-control-custom:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary-color);
            box-shadow: 0 0 12px rgba(99, 102, 241, 0.25);
            color: var(--text-light);
            outline: none;
        }

        /* Interactive Radio Card Group */
        .radio-card-group {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        @media (min-width: 576px) {
            .radio-card-group {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .radio-card-label {
            cursor: pointer;
            width: 100%;
        }

        .radio-card-input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .radio-card-inner {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 20px 16px;
            text-align: center;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            gap: 8px;
        }

        .custom-radio-indicator {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 2px solid var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            background: transparent;
        }

        .custom-radio-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: transparent;
            transition: all 0.3s ease;
            transform: scale(0);
        }

        .radio-card-input:checked + .radio-card-inner {
            background: rgba(99, 102, 241, 0.06);
            border-color: var(--primary-color);
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.15);
        }

        .radio-card-input:checked + .radio-card-inner .custom-radio-indicator {
            border-color: var(--primary-color);
        }

        .radio-card-input:checked + .radio-card-inner .custom-radio-dot {
            background: var(--primary-color);
            transform: scale(1);
        }

        /* Billing Switch Styled Radios */
        .billing-selector {
            display: flex;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 30px;
            padding: 4px;
            margin-bottom: 20px;
        }

        .billing-selector-option {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .billing-selector-input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .billing-selector-label {
            display: block;
            padding: 10px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            color: var(--text-muted);
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .billing-selector-input:checked + .billing-selector-label {
            background: var(--gradient-main);
            color: white;
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
        }

        /* Checkbox Custom styling */
        .checkbox-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .checkbox-custom-item {
            display: flex;
            align-items: center;
            padding: 14px 18px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .checkbox-custom-item:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(99, 102, 241, 0.2);
        }

        .checkbox-custom-input {
            width: 20px;
            height: 20px;
            border-radius: 5px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            background-color: transparent;
            cursor: pointer;
            margin-right: 14px;
            flex-shrink: 0;
            accent-color: var(--primary-color);
        }

        .checkbox-custom-label {
            flex-grow: 1;
            font-size: 0.95rem;
            color: var(--text-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .checkbox-price-tag {
            font-weight: 600;
            color: var(--secondary-color);
        }

        /* Calculate Button */
        .btn-calculate {
            background: var(--gradient-main);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 14px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .btn-calculate:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
            color: white;
        }

        .btn-calculate:active {
            transform: translateY(1px);
        }

        /* Invoice Result Design */
        .invoice-card {
            border-top: 5px solid var(--primary-color) !important;
            position: sticky;
            top: 100px;
        }

        .invoice-title {
            letter-spacing: 1px;
            text-transform: uppercase;
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        .invoice-item-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.08);
            font-size: 0.95rem;
        }

        .invoice-item-row.total {
            border-bottom: none;
            font-size: 1.3rem;
            font-weight: 700;
            color: white;
            padding-top: 15px;
        }

        .saving-badge {
            background: rgba(16, 185, 129, 0.15);
            color: var(--accent-green);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }

        .spec-badge {
            background: rgba(6, 182, 212, 0.1);
            color: var(--secondary-color);
            border: 1px solid rgba(6, 182, 212, 0.2);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Welcome/Empty State Placeholder */
        .calculator-placeholder {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            min-height: 350px;
        }

        .calculator-placeholder i {
            font-size: 4rem;
            margin-bottom: 20px;
            background: var(--gradient-main);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: pulse-icon 2s infinite ease-in-out;
        }

        @keyframes pulse-icon {
            0%, 100% {
                transform: scale(1);
                opacity: 0.8;
            }
            50% {
                transform: scale(1.08);
                opacity: 1;
            }
        }

        /* Visual bar indicators for Resource Specs */
        .resource-bar {
            height: 6px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 3px;
            margin-top: 5px;
            overflow: hidden;
        }

        .resource-progress {
            height: 100%;
            background: var(--gradient-main);
            border-radius: 3px;
        }

        footer {
            border-top: 1px solid var(--card-border);
            padding: 30px 0;
            background: rgba(7, 10, 17, 0.95);
            margin-top: auto;
        }
    </style>
</head>

<body>
    <!-- Ambient Background Glows -->
    <div class="glow-sphere glow-left"></div>
    <div class="glow-sphere glow-right"></div>

    <!-- Custom Glassmorphic Navigation -->
    <nav class="navbar navbar-expand-lg custom-nav fixed-top py-3">
        <div class="container">
            <a class="navbar-brand fs-4" href="index.html">TAN_NATTAWUT</a>
            <button class="navbar-toggler border-0 text-light" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="bi bi-list fs-2"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto gap-3 text-center">
                    <li class="nav-item">
                        <a class="nav-link" href="index.html#home">หน้าแรก</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.html#about">ประวัติส่วนตัว</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.html#projects">ผลงาน</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="lab2.php"><i class="bi bi-calculator me-1"></i>เครื่องคำนวณราคาคลาวด์</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.html#contact">ติดต่อฉัน</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header Title Section -->
    <header class="page-header container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <span class="badge bg-indigo-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill mb-3">
                    ใบงานเรื่อง PHP & FORM INPUT
                </span>
                <h1 class="display-5 fw-bold mb-3">Cloud Server <span class="gradient-text">Cost Estimator</span></h1>
                <p class="text-white-50">
                    ออกแบบจัดแจงสเปคของเซิร์ฟเวอร์ที่คุณต้องการ และคำนวณราคาค่าบริการทั้งหมดแบบเรียลไทม์ผ่านระบบเซิร์ฟเวอร์ไซด์ของ PHP
                </p>
            </div>
        </div>
    </header>

    <!-- Main App Content Container -->
    <main class="container mb-5">
        <div class="row g-4">
            
            <!-- Left Column: Input Form -->
            <div class="col-lg-7">
                <div class="card glass-card p-4 p-md-5 h-100">
                    <h2 class="h4 fw-bold mb-4 text-white"><i class="bi bi-sliders text-primary me-2"></i>ปรับแต่งค่าสเปคเซิร์ฟเวอร์</h2>
                    
                    <!-- Display validation errors if any -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger border-danger-subtle bg-danger-subtle bg-opacity-10 text-danger-emphasis mb-4" role="alert" id="error-box">
                            <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-octagon-fill me-2"></i>เกิดข้อผิดพลาดในการประมวลผล:</h6>
                            <ul class="mb-0 small ps-3">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Form submission using POST method to current page -->
                    <form action="lab2.php" method="POST" id="calculator-form">
                        
                        <!-- 1. Text Input Element: Server Name -->
                        <div class="mb-4">
                            <label for="server_name" class="form-label-custom" id="label-server-name">
                                <i class="bi bi-tag text-secondary"></i> 1. ตั้งชื่อเครื่องเซิร์ฟเวอร์ (Server Project Name)
                            </label>
                            <input type="text" 
                                   id="server_name" 
                                   name="server_name" 
                                   class="form-control form-control-custom" 
                                   placeholder="เช่น Web Server Portfolio, Database Node 1" 
                                   value="<?php echo htmlspecialchars($server_name, ENT_QUOTES, 'UTF-8'); ?>" 
                                   required>
                        </div>

                        <!-- 2. Number Input Elements: Cores & RAM -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="cpu_cores" class="form-label-custom" id="label-cpu-cores">
                                    <i class="bi bi-cpu text-secondary"></i> 2. จำนวน CPU (Cores)
                                </label>
                                <input type="number" 
                                       id="cpu_cores" 
                                       name="cpu_cores" 
                                       class="form-control form-control-custom" 
                                       min="1" 
                                       max="64" 
                                       value="<?php echo $cpu_cores; ?>" 
                                       required>
                                <div class="text-white-50 small mt-1">ขอบเขต: 1 - 64 Cores</div>
                            </div>
                            <div class="col-md-6">
                                <label for="ram_gb" class="form-label-custom" id="label-ram-gb">
                                    <i class="bi bi-memory text-secondary"></i> 3. หน่วยความจำ RAM (GB)
                                </label>
                                <input type="number" 
                                       id="ram_gb" 
                                       name="ram_gb" 
                                       class="form-control form-control-custom" 
                                       min="1" 
                                       max="512" 
                                       value="<?php echo $ram_gb; ?>" 
                                       required>
                                <div class="text-white-50 small mt-1">ขอบเขต: 1 - 512 GB</div>
                            </div>
                        </div>

                        <!-- 3. Radio Group Button Element: Server Performance Tier -->
                        <div class="mb-4">
                            <label class="form-label-custom" id="label-server-tier">
                                <i class="bi bi-speedometer2 text-secondary"></i> 4. ระดับประสิทธิภาพคลาวด์ (Server Performance Tier)
                            </label>
                            <div class="radio-card-group">
                                
                                <!-- Radio Button: Shared -->
                                <label class="radio-card-label" id="tier-shared-label">
                                    <input type="radio" 
                                           name="server_tier" 
                                           value="shared" 
                                           class="radio-card-input" 
                                           <?php echo ($server_tier === 'shared') ? 'checked' : ''; ?>>
                                    <div class="radio-card-inner">
                                        <div class="custom-radio-indicator">
                                            <div class="custom-radio-dot"></div>
                                        </div>
                                        <span class="fw-semibold text-white d-block">Shared CPU</span>
                                        <span class="text-white-50 small">$5 / Core / เดือน</span>
                                    </div>
                                </label>
                                
                                <!-- Radio Button: Dedicated -->
                                <label class="radio-card-label" id="tier-dedicated-label">
                                    <input type="radio" 
                                           name="server_tier" 
                                           value="dedicated" 
                                           class="radio-card-input" 
                                           <?php echo ($server_tier === 'dedicated') ? 'checked' : ''; ?>>
                                    <div class="radio-card-inner">
                                        <div class="custom-radio-indicator">
                                            <div class="custom-radio-dot"></div>
                                        </div>
                                        <span class="fw-semibold text-white d-block">Dedicated CPU</span>
                                        <span class="text-white-50 small">$15 / Core / เดือน</span>
                                    </div>
                                </label>
                                
                                <!-- Radio Button: High Memory -->
                                <label class="radio-card-label" id="tier-highmem-label">
                                    <input type="radio" 
                                           name="server_tier" 
                                           value="high_memory" 
                                           class="radio-card-input" 
                                           <?php echo ($server_tier === 'high_memory') ? 'checked' : ''; ?>>
                                    <div class="radio-card-inner">
                                        <div class="custom-radio-indicator">
                                            <div class="custom-radio-dot"></div>
                                        </div>
                                        <span class="fw-semibold text-white d-block">High-Memory</span>
                                        <span class="text-white-50 small">$25 / Core / เดือน</span>
                                    </div>
                                </label>
                                
                            </div>
                        </div>

                        <!-- 4. Radio Group Button Element: Billing Cycle (Switch Style) -->
                        <div class="mb-4">
                            <label class="form-label-custom" id="label-billing-cycle">
                                <i class="bi bi-calendar-check text-secondary"></i> 5. รอบชำระเงินค่าบริการ (Billing Cycle)
                            </label>
                            <div class="billing-selector">
                                <div class="billing-selector-option">
                                    <input type="radio" 
                                           id="billing_monthly" 
                                           name="billing_cycle" 
                                           value="monthly" 
                                           class="billing-selector-input" 
                                           <?php echo ($billing_cycle === 'monthly') ? 'checked' : ''; ?>>
                                    <label for="billing_monthly" class="billing-selector-label" id="billing-monthly-label">
                                        รายเดือน (Monthly Rate)
                                    </label>
                                </div>
                                <div class="billing-selector-option">
                                    <input type="radio" 
                                           id="billing_yearly" 
                                           name="billing_cycle" 
                                           value="yearly" 
                                           class="billing-selector-input" 
                                           <?php echo ($billing_cycle === 'yearly') ? 'checked' : ''; ?>>
                                    <label for="billing_yearly" class="billing-selector-label" id="billing-yearly-label">
                                        รายปี (ประหยัด 2 เดือน! 🎉)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- 5. Checkbox Element: Addons (Array type) -->
                        <div class="mb-5">
                            <label class="form-label-custom" id="label-addons">
                                <i class="bi bi-plus-square text-secondary"></i> 6. บริการเสริมพิเศษ (Add-on Modules)
                            </label>
                            <div class="checkbox-container">
                                
                                <!-- Addon 1: Backup -->
                                <label class="checkbox-custom-item" id="addon-backup-label">
                                    <input type="checkbox" 
                                           name="addons[]" 
                                           value="backup" 
                                           class="checkbox-custom-input"
                                           <?php echo in_array('backup', $selected_addons) ? 'checked' : ''; ?>>
                                    <span class="checkbox-custom-label">
                                        <span><i class="bi bi-shield-check text-indigo me-2"></i>ระบบสำรองข้อมูลอัตโนมัติ (Daily Backups)</span>
                                        <span class="checkbox-price-tag">+$5 / เดือน</span>
                                    </span>
                                </label>

                                <!-- Addon 2: Managed Database -->
                                <label class="checkbox-custom-item" id="addon-database-label">
                                    <input type="checkbox" 
                                           name="addons[]" 
                                           value="database" 
                                           class="checkbox-custom-input"
                                           <?php echo in_array('database', $selected_addons) ? 'checked' : ''; ?>>
                                    <span class="checkbox-custom-label">
                                        <span><i class="bi bi-database-check text-indigo me-2"></i>จัดการฐานข้อมูลให้ครบวงจร (Managed Database)</span>
                                        <span class="checkbox-price-tag">+$15 / เดือน</span>
                                    </span>
                                </label>

                                <!-- Addon 3: Security & DDoS -->
                                <label class="checkbox-custom-item" id="addon-security-label">
                                    <input type="checkbox" 
                                           name="addons[]" 
                                           value="security" 
                                           class="checkbox-custom-input"
                                           <?php echo in_array('security', $selected_addons) ? 'checked' : ''; ?>>
                                    <span class="checkbox-custom-label">
                                        <span><i class="bi bi-file-earmark-lock text-indigo me-2"></i>DDoS Protection + SSL Certificate</span>
                                        <span class="checkbox-price-tag">+$10 / เดือน</span>
                                    </span>
                                </label>

                                <!-- Addon 4: CDN -->
                                <label class="checkbox-custom-item" id="addon-cdn-label">
                                    <input type="checkbox" 
                                           name="addons[]" 
                                           value="cdn" 
                                           class="checkbox-custom-input"
                                           <?php echo in_array('cdn', $selected_addons) ? 'checked' : ''; ?>>
                                    <span class="checkbox-custom-label">
                                        <span><i class="bi bi-globe text-indigo me-2"></i>เครือข่ายกระจาย CDN ทั่วโลก (Global CDN)</span>
                                        <span class="checkbox-price-tag">+$8 / เดือน</span>
                                    </span>
                                </label>

                            </div>
                        </div>

                        <!-- 6. Submit Element -->
                        <button type="submit" class="btn btn-calculate w-100 fs-5" id="btn-submit">
                            <i class="bi bi-calculator-fill me-2"></i>ประมวลผลคำนวณราคาคลาวด์
                        </button>

                    </form>
                </div>
            </div>

            <!-- Right Column: Results Output Display Area -->
            <div class="col-lg-5">
                <div class="card glass-card p-4 p-md-5 h-100 invoice-card" id="result-container">
                    
                    <?php if ($is_calculated && empty($errors)): ?>
                        <!-- Result State: Calculations outputs display -->
                        <div class="invoice-header text-center mb-4">
                            <div class="display-6 text-primary mb-2"><i class="bi bi-file-earmark-check-fill"></i></div>
                            <h3 class="h4 fw-bold text-white mb-1">ใบประเมินค่าบริการระบบ</h3>
                            <p class="text-white-50 small">ใบประเมินราคาชั่วคราวผ่าน POST Method</p>
                        </div>
                        
                        <div class="invoice-body">
                            <div class="invoice-title">สรุปข้อมูลเซิร์ฟเวอร์</div>
                            
                            <!-- Project/Server details -->
                            <div class="invoice-item-row">
                                <span class="text-white-50">ชื่อโครงการเซิร์ฟเวอร์:</span>
                                <span class="fw-bold text-white text-end text-wrap" style="max-width: 200px; word-break: break-all;">
                                    <?php echo $server_name; ?>
                                </span>
                            </div>
                            
                            <div class="invoice-item-row">
                                <span class="text-white-50">ระดับประสิทธิภาพ (Tier):</span>
                                <span class="fw-semibold text-info"><?php echo $tier_names[$server_tier]; ?></span>
                            </div>

                            <div class="invoice-item-row">
                                <span class="text-white-50">สเปคความจุหลัก:</span>
                                <span class="text-white font-monospace">
                                    <span class="spec-badge me-1"><?php echo $cpu_cores; ?> Cores</span>
                                    <span class="spec-badge"><?php echo $ram_gb; ?> GB RAM</span>
                                </span>
                            </div>

                            <!-- Resource Graph Visuals -->
                            <div class="mb-4 mt-2">
                                <div class="d-flex justify-content-between small text-white-50">
                                    <span>ภาระประมวลผลเครื่องจำลอง:</span>
                                    <span><?php echo ($cpu_cores * 4 + $ram_gb) . ' pts'; ?></span>
                                </div>
                                <div class="resource-bar">
                                    <div class="resource-progress" style="width: <?php echo min(100, (($cpu_cores * 4 + $ram_gb) / 100) * 100); ?>%"></div>
                                </div>
                            </div>

                            <div class="invoice-title mt-4">รายการแยกรายละเอียดราคา</div>
                            
                            <div class="invoice-item-row">
                                <span class="text-white-50">ค่าประมวลผล CPU ($<?php echo $cpu_rate; ?>/Core):</span>
                                <span class="text-white">$<?php echo number_format($cpu_cost, 2); ?>/เดือน</span>
                            </div>

                            <div class="invoice-item-row">
                                <span class="text-white-50">ค่าหน่วยความจำ RAM ($1.50/GB):</span>
                                <span class="text-white">$<?php echo number_format($ram_cost, 2); ?>/เดือน</span>
                            </div>

                            <?php if (!empty($selected_addons)): ?>
                                <div class="invoice-item-row">
                                    <span class="text-white-50">ค่าบริการเสริม (<?php echo count($selected_addons); ?> รายการ):</span>
                                    <span class="text-white">$<?php echo number_format($addons_subtotal, 2); ?>/เดือน</span>
                                </div>
                                
                                <!-- List detailed active addons -->
                                <div class="p-3 my-2 rounded" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.04);">
                                    <ul class="list-unstyled mb-0 small text-white-50">
                                        <?php foreach ($selected_addons as $addon): ?>
                                            <li class="d-flex justify-content-between mb-1">
                                                <span><i class="bi bi-plus-circle text-success me-1"></i> <?php echo $addon_details[$addon]["name"]; ?></span>
                                                <span>$<?php echo number_format($addon_details[$addon]["price"], 2); ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <div class="invoice-item-row">
                                    <span class="text-white-50">ค่าบริการเสริมพิเศษ:</span>
                                    <span class="text-white-50">ไม่มีบริการเสริม</span>
                                </div>
                            <?php endif; ?>

                            <div class="invoice-item-row">
                                <span class="text-white-50">รวมค่าบริการรายเดือนจริง:</span>
                                <span class="text-white fw-bold">$<?php echo number_format($monthly_subtotal, 2); ?>/เดือน</span>
                            </div>

                            <div class="invoice-item-row">
                                <span class="text-white-50">รอบการเก็บค่าบริการ:</span>
                                <span class="badge <?php echo ($billing_cycle === 'yearly') ? 'bg-success' : 'bg-secondary'; ?>">
                                    <?php echo ($billing_cycle === 'yearly') ? 'ชำระรายปี (คุ้มค่ากว่า)' : 'ชำระรายเดือน'; ?>
                                </span>
                            </div>

                            <?php if ($billing_cycle === 'yearly'): ?>
                                <div class="invoice-item-row">
                                    <span class="text-white-50">ส่วนลดโปรโมชั่นรายปี (2 เดือน):</span>
                                    <span class="text-success fw-bold">-$<?php echo number_format($yearly_savings, 2); ?></span>
                                </div>
                            <?php endif; ?>

                            <!-- Total Display -->
                            <div class="invoice-item-row total mt-3">
                                <span>ยอดชำระสุทธิ:</span>
                                <div class="text-end">
                                    <span class="gradient-text">$<?php echo number_format($total_cost, 2); ?></span>
                                    <div class="text-white-50 small fw-normal" style="font-size: 0.75rem; margin-top:-5px;">
                                        (ชำระต่อ<?php echo ($billing_cycle === 'yearly') ? 'ปี' : 'เดือน'; ?>)
                                    </div>
                                </div>
                            </div>

                            <!-- Year Discount Badge Notification -->
                            <?php if ($billing_cycle === 'yearly'): ?>
                                <div class="text-center mt-3">
                                    <div class="saving-badge">
                                        <i class="bi bi-fire me-1"></i> ประหยัดเงินรวม $<?php echo number_format($yearly_savings, 2); ?> / ปี เมื่อเทียบกับชำระแบบรายเดือน!
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                    
                    <?php else: ?>
                        <!-- Initial State / Empty State (GET Method) -->
                        <div class="calculator-placeholder" id="placeholder-container">
                            <i class="bi bi-clouds-fill"></i>
                            <h3 class="h5 text-white fw-semibold mb-2">รอดำเนินการประมวลผลคำนวณ</h3>
                            <p class="text-white-50 small mb-0 px-md-4">
                                ปรับแต่งขนาดทรัพยากรเซิร์ฟเวอร์ ระดับการประมวลผล และบริการเสริมที่ท่านต้องการทางด้านซ้าย แล้วกดปุ่มคำนวณเพื่อรับใบประเมินราคา
                            </p>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
            
        </div>
    </main>

    <!-- Footer Page -->
    <footer class="text-center">
        <div class="container">
            <p class="mb-3 text-white-50">&copy; 2026 Nattawut Pumprasert (Tan). All rights reserved.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="https://github.com/TanMakoto" class="text-muted fs-4" title="GitHub" target="_blank"><i class="bi bi-github"></i></a>
                <a href="#" class="text-muted fs-4" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
                <a href="https://www.facebook.com/tan.makotoi" class="text-muted fs-4" title="Facebook" target="_blank"><i class="bi bi-facebook"></i></a>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle with Popper JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
