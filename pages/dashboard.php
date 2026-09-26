<?php
require_once __DIR__ . '/../db.php';

$summary = [
    'karyawan' => (int) $conn->query('SELECT COUNT(*) AS total FROM karyawan')->fetch_assoc()['total'],
    'tunjangan' => (int) $conn->query('SELECT COUNT(*) AS total FROM tunjangan')->fetch_assoc()['total'],
    'umr' => (int) $conn->query('SELECT COUNT(*) AS total FROM umr')->fetch_assoc()['total'],
    'payroll' => (int) $conn->query('SELECT COUNT(*) AS total FROM payroll')->fetch_assoc()['total'],
];

$totalGajiBersih = $conn->query('SELECT COALESCE(SUM(gaji_bersih), 0) AS total FROM payroll')->fetch_assoc()['total'];
$gajiTertinggi = $conn->query('SELECT COALESCE(MAX(gaji_bersih), 0) AS total FROM payroll')->fetch_assoc()['total'];
?>

<h2>Dashboard</h2>

<div class="grid">
    <div class="card">
        <p class="card-title">Total Karyawan</p>
        <p class="card-value"><?= $summary['karyawan'] ?></p>
    </div>
    <div class="card warning">
        <p class="card-title">Data Tunjangan</p>
        <p class="card-value"><?= $summary['tunjangan'] ?></p>
    </div>
    <div class="card success">
        <p class="card-title">UMR Terdaftar</p>
        <p class="card-value"><?= $summary['umr'] ?></p>
    </div>
    <div class="card danger">
        <p class="card-title">Jumlah Payroll</p>
        <p class="card-value"><?= $summary['payroll'] ?></p>
    </div>
</div>

<div class="grid" style="margin-top: 18px;">
    <div class="card success">
        <p class="card-title">Total Gaji Bersih</p>
        <p class="card-value"><?= format_rp($totalGajiBersih) ?></p>
    </div>
    <div class="card warning">
        <p class="card-title">Gaji Tertinggi</p>
        <p class="card-value"><?= format_rp($gajiTertinggi) ?></p>
    </div>
</div>
