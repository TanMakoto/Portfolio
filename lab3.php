<?php
/**
 * Web Application: Effective Interest Rate Loan Calculator & Lesson Summary
 * Lab Topic: PHP & Dynamic Input/Output (Form Handling, Validation & Rendering)
 * Author: Nattawut Pumprasert (Tan)
 * Version: Lab 3
 */

// Initialize default values for form state retention
$principal = 100000;       // Default Principal amount: 100,000 THB
$interest_rate = 5.0;      // Default annual interest rate: 5%
$installments = 12;        // Default number of periods: 12 months

$is_calculated = false;
$errors = [];
$schedule = [];
$total_interest = 0;
$total_payment = 0;

// Handle Form Submission via POST Method
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $is_calculated = true;

    // 1. Sanitize and validate Principal (Text Input)
    if (isset($_POST["principal"])) {
        // Remove commas if any
        $clean_principal = str_replace(',', '', trim($_POST["principal"]));
        if ($clean_principal === "") {
            $errors[] = "กรุณากรอกจำนวนเงินต้นทั้งหมด";
            $principal = "";
        } else {
            $principal = floatval($clean_principal);
            if ($principal <= 0) {
                $errors[] = "จำนวนเงินต้นต้องเป็นตัวเลขที่มากกว่า 0";
            }
        }
    } else {
        $errors[] = "กรุณากรอกจำนวนเงินต้นทั้งหมด";
    }

    // 2. Validate Interest Rate (Select Element: 3% - 12%)
    if (isset($_POST["interest_rate"])) {
        $interest_rate = floatval($_POST["interest_rate"]);
        if ($interest_rate < 3.0 || $interest_rate > 12.0) {
            $errors[] = "อัตราดอกเบี้ยต้องอยู่ระหว่าง 3% ถึง 12% เท่านั้น";
        }
    } else {
        $errors[] = "กรุณาเลือกอัตราดอกเบี้ย";
    }

    // 3. Validate Installments (Select Element: 1 - 60)
    if (isset($_POST["installments"])) {
        $installments = intval($_POST["installments"]);
        if ($installments < 1 || $installments > 60) {
            $errors[] = "จำนวนงวดผ่อนชำระต้องอยู่ระหว่าง 1 ถึง 60 งวด";
        }
    } else {
        $errors[] = "กรุณาเลือกจำนวนงวดผ่อนชำระ";
    }

    // 4. Calculate reducing balance (ลดต้นลดดอก) if there are no errors
    if (empty($errors)) {
        $monthly_rate = ($interest_rate / 12) / 100; // Monthly interest rate (decimal)

        // Calculate standard monthly payment (PMT formula)
        // PMT = P * [r(1+r)^n] / [(1+r)^n - 1]
        if ($monthly_rate > 0) {
            $monthly_payment = $principal * ($monthly_rate * pow(1 + $monthly_rate, $installments)) / (pow(1 + $monthly_rate, $installments) - 1);
        } else {
            $monthly_payment = $principal / $installments;
        }

        // Round to 2 decimal places for payment consistency
        $monthly_payment = round($monthly_payment, 2);

        $remaining_principal = $principal;
        $temp_schedule = [];
        $calculated_total_interest = 0;

        // Loop to generate schedule
        for ($i = 1; $i <= $installments; $i++) {
            // Interest for this period
            $interest_of_period = round($remaining_principal * $monthly_rate, 2);

            // Principal paid for this period
            if ($i === $installments) {
                // Final period adjustment to fully clear remaining principal
                $principal_of_period = $remaining_principal;
                $payment_of_period = $principal_of_period + $interest_of_period;
            } else {
                $principal_of_period = round($monthly_payment - $interest_of_period, 2);

                // Safety check to avoid overpaying principal
                if ($principal_of_period > $remaining_principal) {
                    $principal_of_period = $remaining_principal;
                }
                $payment_of_period = $principal_of_period + $interest_of_period;
            }

            // Deduct principal
            $remaining_principal = round($remaining_principal - $principal_of_period, 2);
            if ($remaining_principal < 0) {
                $remaining_principal = 0;
            }

            $calculated_total_interest += $interest_of_period;

            $temp_schedule[] = [
                "period" => $i,
                "payment" => $payment_of_period,
                "principal_paid" => $principal_of_period,
                "interest_paid" => $interest_of_period,
                "remaining_principal" => $remaining_principal
            ];
        }

        // Calculate remaining interest for each period (backward accumulation or running deduction)
        $running_interest_paid = 0;
        foreach ($temp_schedule as $row) {
            $running_interest_paid += $row["interest_paid"];
            $remaining_interest = round($calculated_total_interest - $running_interest_paid, 2);

            $schedule[] = [
                "period" => $row["period"],
                "payment" => $row["payment"],
                "principal_paid" => $row["principal_paid"],
                "interest_paid" => $row["interest_paid"],
                "remaining_principal" => $row["remaining_principal"],
                "remaining_interest" => $remaining_interest >= 0 ? $remaining_interest : 0
            ];

            $total_payment += $row["payment"];
        }

        $total_interest = $calculated_total_interest;
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 3: Loan Interest Calculator & Lesson Summary | PHP Dynamic I/O</title>
    <meta name="description"
        content="ระบบคำนวณดอกเบี้ยแบบลดต้นลดดอกและเอกสารสรุปบทเรียน PHP & Dynamic Input/Output สไตล์ Google Doc A4 และ Interface สุดพรีเมียม">

    <!-- Google Fonts: Prompt & Outfit & Sarabun for Academic Document -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Prompt:wght@300;400;500;600;700&family=Sarabun:ital,wght@0,300;0,400;0,600;0,700;1,400&display=swap"
        rel="stylesheet">

    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-color: #6366f1;
            /* Indigo */
            --secondary-color: #06b6d4;
            /* Cyan */
            --dark-bg: #0b0f19;
            --card-bg: rgba(255, 255, 255, 0.03);
            --card-border: rgba(255, 255, 255, 0.08);
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
            --gradient-main: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            --accent-pink: #ec4899;
            --accent-green: #10b981;
        }

        body {
            font-family: 'Prompt', sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-light);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        /* Ambient Glow Spheres */
        .glow-sphere {
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.08) 0%, rgba(6, 182, 212, 0) 70%);
            filter: blur(65px);
            z-index: -1;
            pointer-events: none;
        }

        .glow-left {
            top: 10%;
            left: -200px;
        }

        .glow-right {
            bottom: 15%;
            right: -200px;
        }

        /* Glassmorphism Navigation */
        .custom-nav {
            background: rgba(11, 15, 25, 0.85) !important;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-bottom: 1px solid var(--card-border);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            letter-spacing: 1px;
            background: var(--gradient-main);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-link {
            color: var(--text-muted) !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--text-light) !important;
        }

        /* Hero Header */
        .page-header {
            padding-top: 110px;
            padding-bottom: 25px;
            text-align: center;
        }

        .gradient-text {
            background: var(--gradient-main);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-family: 'Outfit', 'Prompt', sans-serif;
            font-weight: 700;
        }

        /* Navigation Tabs styled as pills */
        .custom-tabs-container {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--card-border);
            border-radius: 50px;
            padding: 5px;
            display: inline-flex;
            margin-bottom: 30px;
        }

        .tab-btn {
            background: transparent;
            border: none;
            color: var(--text-muted);
            padding: 10px 24px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-btn:hover {
            color: var(--text-light);
        }

        .tab-btn.active {
            background: var(--gradient-main);
            color: white;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        /* Calculator UI Styles */
        .glass-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 18px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }

        .glass-card:hover {
            border-color: rgba(99, 102, 241, 0.25);
        }

        .form-label-custom {
            font-weight: 500;
            color: var(--text-light);
            margin-bottom: 8px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-control-custom,
        .form-select-custom {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            border-radius: 12px;
            padding: 12px 16px;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .form-control-custom:focus,
        .form-select-custom:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary-color);
            box-shadow: 0 0 12px rgba(99, 102, 241, 0.25);
            color: var(--text-light);
            outline: none;
        }

        .form-select-custom option {
            background: var(--dark-bg);
            color: var(--text-light);
        }

        /* Stat Badges */
        .stat-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--card-border);
            border-radius: 14px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.04);
        }

        /* Amortization Table Styles */
        .table-responsive-custom {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--card-border);
        }

        .custom-table {
            margin-bottom: 0;
            color: var(--text-light);
        }

        .custom-table th {
            background: rgba(255, 255, 255, 0.03);
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            padding: 15px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .custom-table td {
            padding: 14px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            vertical-align: middle;
            font-size: 0.95rem;
        }

        .custom-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        /* GOOGLE DOCS A4 SIMULATOR */
        .doc-workspace {
            background-color: #1e2230;
            padding: 40px 20px;
            border-radius: 18px;
            border: 1px solid var(--card-border);
            box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.5);
            overflow-x: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .doc-page {
            background-color: #ffffff;
            color: #222222;
            width: 210mm;
            /* A4 Width */
            min-height: 297mm;
            /* A4 Height */
            padding: 25.4mm;
            /* Standard 1-inch margins */
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
            border-radius: 2px;
            position: relative;
            font-family: 'Sarabun', sans-serif;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .doc-page::after {
            content: "หน้า " attr(data-page);
            position: absolute;
            bottom: 15mm;
            right: 25.4mm;
            font-size: 12px;
            color: #777777;
            font-family: 'Sarabun', sans-serif;
        }

        .doc-header-meta {
            border-bottom: 1px solid #dddddd;
            padding-bottom: 8px;
            margin-bottom: 25px;
            font-size: 12px;
            color: #666666;
            display: flex;
            justify-content: space-between;
        }

        .doc-title {
            font-size: 26px;
            font-weight: 700;
            color: #111111;
            text-align: center;
            margin-bottom: 5px;
            line-height: 1.3;
        }

        .doc-subtitle {
            font-size: 16px;
            color: #555555;
            text-align: center;
            margin-bottom: 35px;
            font-weight: 500;
        }

        .doc-section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1a73e8;
            /* Google Blue */
            border-left: 4px solid #1a73e8;
            padding-left: 10px;
            margin-top: 25px;
            margin-bottom: 15px;
        }

        .doc-p {
            text-indent: 1.5cm;
            margin-bottom: 12px;
            text-align: justify;
        }

        .doc-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 14px;
        }

        .doc-table th,
        .doc-table td {
            border: 1px solid #cccccc;
            padding: 10px 12px;
            text-align: left;
        }

        .doc-table th {
            background-color: #f1f3f4;
            font-weight: 600;
            color: #3c4043;
        }

        .doc-table tr:nth-child(even) {
            background-color: #fafafa;
        }

        .doc-code-block {
            background-color: #f8f9fa;
            border: 1px solid #dadce0;
            border-radius: 4px;
            padding: 15px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            color: #202124;
            margin: 15px 0;
            overflow-x: auto;
            white-space: pre;
        }

        /* Footer styling */
        footer {
            margin-top: auto;
            border-top: 1px solid var(--card-border);
            padding: 20px 0;
            background: rgba(11, 15, 25, 0.9);
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        /* PRINT STYLESHEET */
        @media print {
            body {
                background: white !important;
                color: black !important;
            }

            .custom-nav,
            .page-header,
            .custom-tabs-container,
            footer,
            .glow-sphere,
            .btn,
            .glass-card,
            .doc-workspace-header {
                display: none !important;
            }

            .doc-workspace {
                background: transparent !important;
                padding: 0 !important;
                box-shadow: none !important;
            }

            .doc-page {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                min-height: auto !important;
                page-break-after: always;
            }

            .doc-page::after {
                display: block;
            }
        }
    </style>
</head>

<body>
    <!-- Glow Effects -->
    <div class="glow-sphere glow-left"></div>
    <div class="glow-sphere glow-right"></div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg custom-nav fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.html">
                <i class="bi bi-cpu-fill text-info fs-4 animate-glow"></i>
                <span>TAN PORTFOLIO</span>
            </a>
            <button class="navbar-toggler border-0 text-light" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto gap-2">
                    <li class="nav-item">
                        <a class="nav-link" href="index.html"><i class="bi bi-house-door-fill me-1"></i>หน้าแรก</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="lab2.php"><i class="bi bi-2-circle-fill me-1"></i>Lab 2 (Server
                            cost)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="lab3.php"><i class="bi bi-3-circle-fill me-1"></i>Lab 3 (Loan &
                            Summary)</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container py-4">

        <!-- Header -->
        <div class="page-header">
            <h1 class="display-5 font-weight-bold"><span class="gradient-text">Lab 3: PHP & Dynamic Input/Output</span>
            </h1>
            <p class="text-white col-lg-8 mx-auto mt-2">
                นวัตกรรมการคำนวณทางการเงินและการบริหารจัดการข้อมูลแบบไดนามิก พร้อมสรุปบทเรียนวิชาการในรูปแบบจำลอง Google
                Doc พิมพ์ได้เสมือนจริง
            </p>
        </div>

        <!-- Tab Toggle buttons -->
        <div class="text-center">
            <div class="custom-tabs-container">
                <button class="tab-btn active" id="calc-tab-btn" onclick="switchTab('calc')">
                    <i class="bi bi-calculator-fill"></i> เครื่องคำนวณดอกเบี้ยลดต้นลดดอก
                </button>
                <button class="tab-btn" id="doc-tab-btn" onclick="switchTab('doc')">
                    <i class="bi bi-file-earmark-word-fill"></i> เอกสารสรุปบทเรียน (Google Doc A4)
                </button>
            </div>
        </div>

        <!-- TAB 1: CALCULATOR SECTION -->
        <div id="calculator-section">
            <div class="row g-4">
                <!-- Form Input Card -->
                <div class="col-lg-4">
                    <div class="glass-card p-4 h-100">
                        <h4 class="mb-4 d-flex align-items-center gap-2">
                            <i class="bi bi-sliders text-info"></i>
                            <span>กำหนดข้อมูลการกู้ยืม</span>
                        </h4>

                        <?php if ($is_calculated && !empty($errors)): ?>
                            <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger rounded-4 mb-4">
                                <ul class="mb-0 ps-3">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="lab3.php">
                            <!-- Principal (Text Element) -->
                            <div class="mb-3">
                                <label for="principal" class="form-label-custom">
                                    <i class="bi bi-cash-stack text-success"></i> เงินต้นทั้งหมด (บาท)
                                </label>
                                <input type="text" class="form-control form-control-custom" id="principal"
                                    name="principal" placeholder="ตัวอย่าง: 100,000"
                                    value="<?php echo htmlspecialchars(number_format($principal)); ?>" required>
                                <small class="text-white d-block mt-1">กรอกจำนวนเงินต้นเต็มจำนวนเป็นตัวเลข</small>
                            </div>

                            <!-- Interest Rate (Select Element: 3% - 12%) -->
                            <div class="mb-3">
                                <label for="interest_rate" class="form-label-custom">
                                    <i class="bi bi-percent text-danger"></i> อัตราดอกเบี้ยต่อปี (%)
                                </label>
                                <select class="form-select form-select-custom" id="interest_rate" name="interest_rate"
                                    required>
                                    <?php
                                    // Generate options from 3% to 12% dynamically with 0.5% steps
                                    for ($rate = 3.0; $rate <= 12.0; $rate += 0.5) {
                                        $selected = (abs($interest_rate - $rate) < 0.01) ? "selected" : "";
                                        printf('<option value="%.2f" %s>%.2f%% ต่อปี</option>', $rate, $selected, $rate);
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Installments (Select Element: 1 - 60) -->
                            <div class="mb-4">
                                <label for="installments" class="form-label-custom">
                                    <i class="bi bi-calendar-range text-primary"></i> จำนวนงวดผ่อนชำระ (เดือน)
                                </label>
                                <select class="form-select form-select-custom" id="installments" name="installments"
                                    required>
                                    <?php
                                    // Generate options from 1 to 60 months dynamically
                                    for ($m = 1; $m <= 60; $m++) {
                                        $selected = ($installments == $m) ? "selected" : "";
                                        $year_text = ($m >= 12) ? sprintf(' (%.1f ปี)', $m / 12) : '';
                                        echo "<option value='$m' $selected>{$m} งวด / เดือน{$year_text}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <button type="submit"
                                class="btn btn-primary w-100 rounded-3 py-3 font-weight-bold d-flex align-items-center justify-content-center gap-2 shadow-sm"
                                style="background: var(--gradient-main); border: none;">
                                <i class="bi bi-calculator"></i> คำนวณดอกเบี้ยแบบลดต้นลดดอก
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Calculator Output -->
                <div class="col-lg-8">
                    <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between">
                        <div>
                            <h4 class="mb-4 d-flex align-items-center gap-2">
                                <i class="bi bi-file-earmark-bar-graph text-info"></i>
                                <span>รายงานและวิเคราะห์ตารางการชำระเงิน</span>
                            </h4>

                            <?php if ($is_calculated && empty($errors)): ?>
                                <!-- Stat Summary Badges -->
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <div class="stat-card">
                                            <div class="text-white small mb-1">เงินต้นทั้งหมด</div>
                                            <div class="fs-4 fw-bold text-light">
                                                ฿<?php echo number_format($principal, 2); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="stat-card">
                                            <div class="text-white small mb-1">รวมดอกเบี้ยที่ต้องจ่าย</div>
                                            <div class="fs-4 fw-bold text-danger">
                                                ฿<?php echo number_format($total_interest, 2); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="stat-card">
                                            <div class="text-white small mb-1">รวมเงินต้น + ดอกเบี้ย</div>
                                            <div class="fs-4 fw-bold text-info">
                                                ฿<?php echo number_format($total_payment, 2); ?></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dynamic Amortization Table -->
                                <div class="table-responsive table-responsive-custom">
                                    <table class="table custom-table text-center table-hover">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="text-white">งวดที่</th>
                                                <th scope="col" class="text-white">ยอดผ่อนชำระ<br><small
                                                        class="text-white">(ต่องวด)</small>
                                                </th>
                                                <th scope="col" class="text-white">จำนวนเงินต้น<br><small
                                                        class="text-white">(ต่องวด)</small>
                                                </th>
                                                <th scope="col" class="text-white">จำนวนดอกเบี้ย<br><small
                                                        class="text-white">(ต่องวด)</small>
                                                </th>
                                                <th scope="col" class="text-info">จำนวนเงินต้น<br><small
                                                        class="text-info-emphasis">(คงเหลือ)</small></th>
                                                <th scope="col" class="text-danger">จำนวนดอกเบี้ย<br><small
                                                        class="text-danger-emphasis">(คงเหลือ)</small></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($schedule as $row): ?>
                                                <tr>
                                                    <td class="fw-bold"><?php echo $row["period"]; ?></td>
                                                    <td>฿<?php echo number_format($row["payment"], 2); ?></td>
                                                    <td class="text-success-emphasis">
                                                        ฿<?php echo number_format($row["principal_paid"], 2); ?></td>
                                                    <td class="text-danger-emphasis">
                                                        ฿<?php echo number_format($row["interest_paid"], 2); ?></td>
                                                    <td class="text-info font-weight-bold">
                                                        ฿<?php echo number_format($row["remaining_principal"], 2); ?></td>
                                                    <td class="text-danger font-weight-bold">
                                                        ฿<?php echo number_format($row["remaining_interest"], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <!-- Empty State -->
                                <div class="text-center py-5 my-5">
                                    <i class="bi bi-wallet2 text-muted display-1 mb-3"></i>
                                    <h5>ยินดีต้อนรับสู่ระบบคำนวณดอกเบี้ยแบบลดต้นลดดอก</h5>
                                    <p class="text-muted col-md-8 mx-auto">กรุณากรอกเงินต้น อัตราดอกเบี้ยต่อปี
                                        และจำนวนงวดในแบบฟอร์มด้านซ้าย
                                        จากนั้นกดปุ่มคำนวณเพื่อแสดงตารางเปรียบเทียบการชำระเงินโดยละเอียด</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: LESSON SUMMARY (GOOGLE DOCS SIMULATOR) -->
        <div id="document-section" style="display: none;">
            <div class="doc-workspace-header d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted"><i class="bi bi-info-circle-fill text-info me-1"></i> โหมดการจำลองเอกสารวิชาการ
                    A4 เพื่อส่งงานอาจารย์ (กดพิมพ์ได้จริงเพื่อส่งเป็น PDF)</span>
                <button class="btn btn-outline-light rounded-pill btn-sm d-flex align-items-center gap-2"
                    onclick="window.print()">
                    <i class="bi bi-printer"></i> สั่งพิมพ์ / บันทึกเป็น PDF
                </button>
            </div>

            <div class="doc-workspace">
                <!-- Page 1 -->
                <div class="doc-page" data-page="1">
                    <div class="doc-header-meta">
                        <span>สรุปบทเรียนวิชา Web Application Development</span>
                        <span>วิชาการโปรแกรมเว็บเบื้องต้น</span>
                    </div>

                    <div class="doc-title">สรุปประเด็นสำคัญ: PHP & Dynamic Input/Output</div>
                    <div class="doc-subtitle">วิชาการพัฒนาเว็บแอปพลิเคชันและการจัดการฟอร์มประมวลผลข้อมูลไดนามิก</div>

                    <div style="margin-bottom: 25px;">
                        <strong>จัดทำโดย:</strong> นายนัตถวุฒิ พุ่มประเสริฐ (Tan) <br>
                        <strong>รหัสนักศึกษา:</strong> 6x-xxxxx-xxxx-x <br>
                        <strong>หัวข้อบทเรียน:</strong> การทำงานกับ Form Input, วิธีการร้องขอข้อมูล (HTTP Methods)
                        และการกรองความปลอดภัย
                    </div>

                    <div class="doc-section-title">1. บทนำและแนวคิดเกี่ยวกับ Dynamic Input/Output ใน PHP</div>
                    <p class="doc-p">
                        หัวใจสำคัญของการสร้างเว็บแอปพลิเคชันที่มีการปฏิสัมพันธ์กับผู้ใช้ (Interactive Web Application)
                        คือการรับข้อมูลจากผู้ใช้ผ่านส่วนต่อประสาน (User Interface)
                        แล้วประมวลผลข้อมูลดังกล่าวเพื่อแสดงผลลัพธ์กลับไปอย่างไดนามิก ภาษา PHP เป็นภาษาประเภท Server-Side
                        Scripting ที่ออกแบบมาสำหรับการประมวลผลที่เครื่องแม่ข่ายโดยเฉพาะ โดยตัวภาษาเตรียมตัวแปร Global
                        Array ชนิดพิเศษเพื่อรองรับการส่งผ่านข้อมูลที่เกิดจากการร้องขอผ่านโปรโตคอล HTTP ของเบราว์เซอร์
                    </p>
                    <p class="doc-p">
                        ในบทเรียนนี้ เราศึกษาโครงสร้างอินพุตในหลากหลายรูปแบบ เช่น กล่องข้อความ (Text Element),
                        เมนูตัวเลือก (Select Element) และการตอบรับโดยตรงจากผู้ใช้งาน
                        เพื่อนำข้อมูลเหล่านั้นมาแปลงเป็นอัลกอริทึมที่ซับซ้อน เช่น
                        การทำตารางแจกแจงภาระหนี้สินและการคำนวณการผ่อนชำระที่เป็นประโยชน์ในชีวิตประจำวัน
                    </p>

                    <div class="doc-section-title">2. การเปรียบเทียบวิธีการร้องขอ HTTP: GET เทียบกับ POST</div>
                    <p class="doc-p">
                        การส่งผ่านข้อมูลในสถาปัตยกรรมเว็บอาศัยวิธีการร้องขอแบบ HTTP Request Methods สองประเภทหลักคือ GET
                        และ POST ซึ่งมีคุณสมบัติและการใช้งานแตกต่างกันอย่างชัดเจน ดังที่สรุปในตารางด้านล่างนี้:
                    </p>

                    <table class="doc-table">
                        <thead>
                            <tr>
                                <th>คุณสมบัติ</th>
                                <th>HTTP GET Method</th>
                                <th>HTTP POST Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>เส้นทางการส่งข้อมูล</strong></td>
                                <td>แนบไปกับ URL ในรูปแบบ Query String (?key=value)</td>
                                <td>แนบไปกับส่วนเนื้อหา (HTTP Request Body)</td>
                            </tr>
                            <tr>
                                <td><strong>ความปลอดภัย</strong></td>
                                <td>ต่ำ (ข้อมูลปรากฏบน URL History, Cache บันทึกได้)</td>
                                <td>สูงกว่า (ไม่แสดงบน URL และเหมาะสำหรับรหัสผ่าน)</td>
                            </tr>
                            <tr>
                                <td><strong>ขนาดของข้อมูล</strong></td>
                                <td>จำกัดความยาว (ประมาณ 2,048 ตัวอักษร)</td>
                                <td>ไม่จำกัดขนาดตามทฤษฎี (ขึ้นกับคอนฟิก PHP/Server)</td>
                            </tr>
                            <tr>
                                <td><strong>การนำไปใช้งานที่เหมาะสม</strong></td>
                                <td>งานสืบค้น ค้นหา หรือคั่นหน้าเว็บ (Bookmarking)</td>
                                <td>งานส่งค่าแบบฟอร์ม, บัญชีผู้ใช้, การสร้างและแก้ไขข้อมูล</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Page 2 -->
                <div class="doc-page" data-page="2">
                    <div class="doc-header-meta">
                        <span>สรุปบทเรียนวิชา Web Application Development</span>
                        <span>วิชาการโปรแกรมเว็บเบื้องต้น</span>
                    </div>

                    <div class="doc-section-title">3. ความปลอดภัยในการรับส่งข้อมูลและการตรวจสอบความถูกต้อง (Sanitization
                        & Validation)</div>
                    <p class="doc-p">
                        ความปลอดภัยของเว็บแอปพลิเคชันขึ้นอยู่กับกฎเหล็กที่สำคัญคือ <em>"อย่าไว้ใจข้อมูลใด ๆ
                            ที่มาจากฝั่งไคลเอนต์ (Never Trust User Input)"</em> การประมวลผลฟอร์มอินพุตในภาษา PHP
                        จำเป็นต้องมีการปกป้องระบบจากช่องโหว่ความปลอดภัย เช่น Cross-Site Scripting (XSS)
                        และการป้อนข้อมูลที่ไม่ถูกต้องผ่าน 2 กระบวนการสำคัญ:
                    </p>
                    <p class="doc-p">
                        <strong>1) การกรองความสะอาดของข้อมูล (Sanitization):</strong>
                        เป็นการทำความสะอาดค่าที่ผู้ใช้กรอกเข้ามาโดยการแปลงตัวอักษรพิเศษที่มีผลทางความปลอดภัย เช่น
                        ฟังก์ชัน <code>htmlspecialchars()</code> ใน PHP ซึ่งจะแปลงอักขระพิเศษอย่าง <code>&lt;</code>
                        หรือ <code>&gt;</code> ให้กลายเป็น HTML Entity เพื่อป้องกันผู้ประสงค์ร้ายป้อนสคริปต์ JavaScript
                        เข้ามาทำงานในหน้าเว็บ
                    </p>
                    <p class="doc-p">
                        <strong>2) การตรวจสอบความถูกต้อง (Validation):</strong>
                        เป็นการยืนยันว่าข้อมูลอยู่ในรูปแบบและช่วงที่แอปพลิเคชันต้องการ เช่น
                        การแปลงชนิดตัวแปรด้วยฟังก์ชัน <code>intval()</code> หรือ <code>floatval()</code>
                        และใช้การเปรียบเทียบตรรกะตรวจเช็คว่าตัวเลขที่กรอกมาต้องไม่เป็นค่าติดลบ หรืออยู่นอกขอบเขตที่กำหนด
                    </p>

                    <div class="doc-code-block">// ตัวอย่างโค้ดโครงสร้างการดักจับค่าและทำความสะอาดตัวแปรใน PHP
                        if ($_SERVER["REQUEST_METHOD"] === "POST") {
                        // 1. ตรวจสอบและแปลงค่าเงินต้นให้ปลอดภัย
                        $principal = 0;
                        if (isset($_POST['principal'])) {
                        $clean_val = str_replace(',', '', $_POST['principal']);
                        $principal = floatval($clean_val);
                        }

                        // 2. การแปลงสเปเชียลคาแรกเตอร์เพื่อป้องกัน XSS
                        $server_name = htmlspecialchars($_POST['server_name'] ?? '', ENT_QUOTES, 'UTF-8');
                        }</div>

                    <div class="doc-section-title">4. การแสดงผลข้อมูลไดนามิกแบบลดต้นลดดอก (Reducing Balance Algorithm)
                    </div>
                    <p class="doc-p">
                        การคำนวณดอกเบี้ยแบบลดต้นลดดอก (Effective Interest Rate) แตกต่างจากการคำนวณแบบเงินต้นคงที่ (Flat
                        Rate) เนื่องจากดอกเบี้ยในงวดถัดไปจะคิดจากเงินต้นคงเหลือจริงของงวดก่อนหน้า
                        อัลกอริทึมในการประมวลผลตารางผ่อนชำระใน PHP จะต้องอาศัยโครงสร้างควบคุมแบบลูป (เช่น
                        <code>for</code>) ในการประมวลผลทีละรอบของงวด โดยใช้ความสัมพันธ์ทางคณิตศาสตร์ดังต่อไปนี้:
                    </p>
                    <p class="doc-p" style="text-indent: 0; padding-left: 1.5cm;">
                        • อัตราดอกเบี้ยต่อเดือน: $r = (R_{annual} / 12) / 100$<br>
                        • ค่างวดผ่อนชำระที่ต้องจ่ายในแต่ละงวดคงที่คำนวณตามสูตรค่างวดสากล (PMT Formula):<br>
                        <span
                            style="display: block; text-align: center; margin: 10px 0; font-weight: bold; font-family: monospace;">PMT
                            = P * [ r * (1 + r)^n ] / [ (1 + r)^n - 1 ]</span>
                        • ดอกเบี้ยในงวดนั้นๆ: $I_t = P_{remaining} \times r$<br>
                        • เงินต้นชำระจริงในงวดนั้นๆ: $Pr_t = PMT - I_t$<br>
                        • ยอดเงินต้นคงเหลือใหม่: $P_{remaining, new} = P_{remaining, old} - Pr_t$
                    </p>
                    <p class="doc-p">
                        การสร้าง Dynamic Output ตารางผ่อนชำระจะเกิดขึ้นใน PHP โดยใช้การวนลูปสร้างอาร์เรย์แบบ 2
                        มิติเพื่อเก็บข้อมูลผลลัพธ์ทีละแถว และแสดงผลลัพธ์ในฝั่ง HTML ผ่านลูป <code>foreach</code>
                        ซึ่งเพิ่มความยืดหยุ่นในการจัดระเบียบข้อมูลและรักษาสภาพความสมบูรณ์ในการปรับเปลี่ยน UI
                    </p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="text-center py-4">
        <div class="container">
            <p class="mb-1">&copy; 2026 Tan Nattawut. Developed for Web Application Development Course.</p>
            <p class="mb-0 text-muted small">Designed with <i class="bi bi-heart-fill text-danger"></i> & PHP by Miku
                Assistant</p>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Tab switching logic
        function switchTab(tabName) {
            const calcTabBtn = document.getElementById('calc-tab-btn');
            const docTabBtn = document.getElementById('doc-tab-btn');
            const calcSection = document.getElementById('calculator-section');
            const docSection = document.getElementById('document-section');

            if (tabName === 'calc') {
                calcTabBtn.classList.add('active');
                docTabBtn.classList.remove('active');
                calcSection.style.display = 'block';
                docSection.style.display = 'none';
            } else {
                docTabBtn.classList.add('active');
                calcTabBtn.classList.remove('active');
                calcSection.style.display = 'none';
                docSection.style.display = 'block';
            }
        }

        // Format principal input dynamically with commas
        const principalInput = document.getElementById('principal');
        if (principalInput) {
            principalInput.addEventListener('input', function (e) {
                // Clear any non-digits
                let value = this.value.replace(/[^0-9.]/g, '');

                // Keep only one decimal point
                const parts = value.split('.');
                if (parts.length > 2) {
                    value = parts[0] + '.' + parts.slice(1).join('');
                }

                // Format with commas for integer part
                if (parts[0]) {
                    parts[0] = parseInt(parts[0], 10).toLocaleString('en-US');
                }

                this.value = parts.join('.');
            });
        }
    </script>
</body>

</html>