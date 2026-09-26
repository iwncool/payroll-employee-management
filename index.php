<?php
session_start();
require_once 'connection.php';

// Tentukan halaman yang aktif
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Proses Hapus Karyawan
if ($action == 'delete' && isset($_GET['id'])) {
    $id_karyawan = sanitasi($_GET['id']);
    $query = "DELETE FROM karyawan WHERE id_karyawan = '$id_karyawan'";
    if ($conn->query($query)) {
        $_SESSION['success'] = "Data karyawan berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus data karyawan!";
    }
    header("Location: ?page=karyawan");
    exit;
}

// Proses Tambah Karyawan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_karyawan'])) {
    $nik = sanitasi($_POST['nik']);
    $nama = sanitasi($_POST['nama_karyawan']);
    $tgl_lahir = validasi_input($_POST['tanggal_lahir'], 'date');
    $jk = sanitasi($_POST['jenis_kelamin']);
    $alamat = sanitasi($_POST['alamat']);
    $telepon = sanitasi($_POST['nomor_telepon']);
    $email = validasi_input($_POST['email'], 'email');
    $id_dept = sanitasi($_POST['id_departemen']);
    $id_jab = sanitasi($_POST['id_jabatan']);
    $tgl_masuk = validasi_input($_POST['tanggal_masuk'], 'date');
    $status = sanitasi($_POST['status_kerja']);
    $gaji_pokok = validasi_input($_POST['gaji_pokok'], 'number');
    $no_rek = sanitasi($_POST['no_rekening']);
    $nama_bank = sanitasi($_POST['nama_bank']);
    
    if ($nik && $nama && $id_dept && $id_jab && $tgl_masuk && $gaji_pokok > 0) {
        $query = "INSERT INTO karyawan (nik, nama_karyawan, tanggal_lahir, jenis_kelamin, alamat, nomor_telepon, email, id_departemen, id_jabatan, tanggal_masuk, status_kerja, gaji_pokok, no_rekening, nama_bank) 
                  VALUES ('$nik', '$nama', '$tgl_lahir', '$jk', '$alamat', '$telepon', '$email', '$id_dept', '$id_jab', '$tgl_masuk', '$status', '$gaji_pokok', '$no_rek', '$nama_bank')";
        
        if ($conn->query($query)) {
            $_SESSION['success'] = "Data karyawan berhasil ditambahkan!";
            header("Location: ?page=karyawan");
            exit;
        } else {
            $_SESSION['error'] = "Gagal menambahkan data karyawan: " . $conn->error;
        }
    } else {
        $_SESSION['error'] = "Silakan isi semua field yang wajib!";
    }
}

// Proses Update Karyawan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_karyawan'])) {
    $id_karyawan = sanitasi($_POST['id_karyawan']);
    $nik = sanitasi($_POST['nik']);
    $nama = sanitasi($_POST['nama_karyawan']);
    $tgl_lahir = validasi_input($_POST['tanggal_lahir'], 'date');
    $jk = sanitasi($_POST['jenis_kelamin']);
    $alamat = sanitasi($_POST['alamat']);
    $telepon = sanitasi($_POST['nomor_telepon']);
    $email = validasi_input($_POST['email'], 'email');
    $id_dept = sanitasi($_POST['id_departemen']);
    $id_jab = sanitasi($_POST['id_jabatan']);
    $tgl_masuk = validasi_input($_POST['tanggal_masuk'], 'date');
    $status = sanitasi($_POST['status_kerja']);
    $gaji_pokok = validasi_input($_POST['gaji_pokok'], 'number');
    $no_rek = sanitasi($_POST['no_rekening']);
    $nama_bank = sanitasi($_POST['nama_bank']);
    
    if ($nik && $nama && $id_dept && $id_jab && $tgl_masuk && $gaji_pokok > 0) {
        $query = "UPDATE karyawan SET nik='$nik', nama_karyawan='$nama', tanggal_lahir='$tgl_lahir', jenis_kelamin='$jk', alamat='$alamat', nomor_telepon='$telepon', email='$email', id_departemen='$id_dept', id_jabatan='$id_jab', tanggal_masuk='$tgl_masuk', status_kerja='$status', gaji_pokok='$gaji_pokok', no_rekening='$no_rek', nama_bank='$nama_bank' WHERE id_karyawan='$id_karyawan'";
        
        if ($conn->query($query)) {
            $_SESSION['success'] = "Data karyawan berhasil diperbarui!";
            header("Location: ?page=karyawan");
            exit;
        } else {
            $_SESSION['error'] = "Gagal memperbarui data karyawan!";
        }
    } else {
        $_SESSION['error'] = "Silakan isi semua field yang wajib!";
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Payroll & Master Karyawan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <h1>💼 Sistem Payroll & Master Karyawan</h1>
            <p>Manajemen Data Karyawan dan Perhitungan Payroll Terintegrasi</p>
        </div>
    </header>

    <!-- Navigation -->
    <nav>
        <ul>
            <li><a href="?page=dashboard" class="<?php echo $page === 'dashboard' ? 'active' : ''; ?>">📊 Dashboard</a></li>
            <li><a href="?page=karyawan" class="<?php echo $page === 'karyawan' ? 'active' : ''; ?>">👥 Master Karyawan</a></li>
            <li><a href="?page=tunjangan" class="<?php echo $page === 'tunjangan' ? 'active' : ''; ?>">💰 Tunjangan</a></li>
            <li><a href="?page=payroll" class="<?php echo $page === 'payroll' ? 'active' : ''; ?>">💸 Payroll</a></li>
            <li><a href="?page=umr" class="<?php echo $page === 'umr' ? 'active' : ''; ?>">📋 UMR</a></li>
        </ul>
    </nav>

    <div class="container">
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                ✓ <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                ✗ <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <main>
            <?php
            // Include halaman sesuai parameter
            switch($page) {
                case 'dashboard':
                    include 'pages/dashboard.php';
                    break;
                case 'karyawan':
                    include 'pages/karyawan.php';
                    break;
                case 'tunjangan':
                    include 'pages/tunjangan.php';
                    break;
                case 'payroll':
                    include 'pages/payroll.php';
                    break;
                case 'umr':
                    include 'pages/umr.php';
                    break;
                default:
                    include 'pages/dashboard.php';
            }
            ?>
        </main>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 Sistem Payroll & Master Karyawan. All Rights Reserved.</p>
        <p>Developed with ❤️ | Versi 1.0</p>
    </footer>
</body>
</html>
