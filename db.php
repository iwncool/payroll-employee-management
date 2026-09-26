<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'payroll_db';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die('Koneksi database gagal: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

session_start();

const APP_ADMIN_USER = 'admin';
const APP_ADMIN_PASS = 'admin123';

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function format_rp($value) {
    return 'Rp ' . number_format((float) $value, 2, ',', '.');
}

function require_login() {
    if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
        header('Location: login.php');
        exit;
    }
}

function is_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function get_departments() {
    global $conn;
    $query = 'SELECT * FROM departemen ORDER BY nama_departemen ASC';
    return $conn->query($query);
}

function get_positions() {
    global $conn;
    $query = 'SELECT * FROM jabatan ORDER BY nama_jabatan ASC';
    return $conn->query($query);
}

function get_allowance_options() {
    global $conn;
    $query = 'SELECT * FROM tunjangan WHERE status = "Aktif" ORDER BY nama_tunjangan ASC';
    return $conn->query($query);
}

function get_umr_value($year, $month) {
    global $conn;
    $sql = "SELECT jumlah_umr FROM umr WHERE tahun = ? AND bulan = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $year, $month);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return (float) $row['jumlah_umr'];
    }
    return 0;
}

function calc_bpjs_from_umr($umr) {
    $bpjsKesehatan = $umr * 0.04;
    $bpjsKetenagakerjaan = $umr * 0.0054;
    $bpjsPensiun = $umr * 0.03;

    return [
        'bpjs_kesehatan' => $bpjsKesehatan,
        'bpjs_ketenagakerjaan' => $bpjsKetenagakerjaan,
        'bpjs_pensiun' => $bpjsPensiun,
        'total_bpjs' => $bpjsKesehatan + $bpjsKetenagakerjaan + $bpjsPensiun,
    ];
}
