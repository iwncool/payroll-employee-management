<?php
require_once __DIR__ . '/db.php';

$page = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Payroll Karyawan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>💼 Payroll & Master Data Karyawan</h1>
            <p>Management sistem penggajian dan data karyawan</p>
        </div>
    </header>

    <nav class="top-nav">
        <div class="container nav-wrap">
            <a href="index.php?page=dashboard" class="<?= $page === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
            <a href="index.php?page=karyawan" class="<?= $page === 'karyawan' ? 'active' : '' ?>">Karyawan</a>
            <a href="index.php?page=tunjangan" class="<?= $page === 'tunjangan' ? 'active' : '' ?>">Tunjangan</a>
            <a href="index.php?page=umr" class="<?= $page === 'umr' ? 'active' : '' ?>">UMR</a>
            <a href="index.php?page=payroll" class="<?= $page === 'payroll' ? 'active' : '' ?>">Payroll</a>
        </div>
    </nav>

    <main class="container main-box">
        <?php
        if (isset($_SESSION['success'])) {
            echo '<div class="alert success">' . e($_SESSION['success']) . '</div>';
            unset($_SESSION['success']);
        }

        if (isset($_SESSION['error'])) {
            echo '<div class="alert danger">' . e($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }

        switch ($page) {
            case 'karyawan':
                include __DIR__ . '/pages/karyawan.php';
                break;
            case 'tunjangan':
                include __DIR__ . '/pages/tunjangan.php';
                break;
            case 'umr':
                include __DIR__ . '/pages/umr.php';
                break;
            case 'payroll':
                include __DIR__ . '/pages/payroll.php';
                break;
            case 'dashboard':
            default:
                include __DIR__ . '/pages/dashboard.php';
                break;
        }
        ?>
    </main>

    <footer>
        <div class="container">
            <p>© 2026 Payroll Management System</p>
        </div>
    </footer>
</body>
</html>
