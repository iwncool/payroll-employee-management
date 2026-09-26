<?php
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_employee'])) {
    $id = $_POST['id_karyawan'] ?? '';
    $nik = trim($_POST['nik']);
    $nama = trim($_POST['nama_karyawan']);
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? null;
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? 'Laki-laki';
    $alamat = trim($_POST['alamat']);
    $nomor_telepon = trim($_POST['nomor_telepon']);
    $email = trim($_POST['email']);
    $id_departemen = $_POST['id_departemen'];
    $id_jabatan = $_POST['id_jabatan'];
    $tanggal_masuk = $_POST['tanggal_masuk'];
    $status_kerja = $_POST['status_kerja'] ?? 'Aktif';
    $gaji_pokok = $_POST['gaji_pokok'];

    if ($nik === '' || $nama === '' || $id_departemen === '' || $id_jabatan === '' || $tanggal_masuk === '' || $gaji_pokok === '') {
        $_SESSION['error'] = 'Semua field wajib diisi.';
        header('Location: index.php?page=karyawan');
        exit;
    }

    if ($id !== '') {
        $sql = 'UPDATE karyawan SET nik=?, nama_karyawan=?, tanggal_lahir=?, jenis_kelamin=?, alamat=?, nomor_telepon=?, email=?, id_departemen=?, id_jabatan=?, tanggal_masuk=?, status_kerja=?, gaji_pokok=? WHERE id_karyawan=?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssssssssi', $nik, $nama, $tanggal_lahir, $jenis_kelamin, $alamat, $nomor_telepon, $email, $id_departemen, $id_jabatan, $tanggal_masuk, $status_kerja, $gaji_pokok, $id);
    } else {
        $sql = 'INSERT INTO karyawan (nik, nama_karyawan, tanggal_lahir, jenis_kelamin, alamat, nomor_telepon, email, id_departemen, id_jabatan, tanggal_masuk, status_kerja, gaji_pokok) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssssssssss', $nik, $nama, $tanggal_lahir, $jenis_kelamin, $alamat, $nomor_telepon, $email, $id_departemen, $id_jabatan, $tanggal_masuk, $status_kerja, $gaji_pokok);
    }

    if ($stmt->execute()) {
        $_SESSION['success'] = $id !== '' ? 'Data karyawan berhasil diperbarui.' : 'Data karyawan berhasil ditambahkan.';
    } else {
        $_SESSION['error'] = 'Gagal menyimpan data karyawan.';
    }

    header('Location: index.php?page=karyawan');
    exit;
}

if (isset($_GET['delete_employee'])) {
    $id = (int) $_GET['delete_employee'];
    $stmt = $conn->prepare('DELETE FROM karyawan WHERE id_karyawan = ?');
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Data karyawan berhasil dihapus.';
    } else {
        $_SESSION['error'] = 'Gagal menghapus data karyawan.';
    }
    header('Location: index.php?page=karyawan');
    exit;
}

$edit = null;
if (isset($_GET['edit_employee'])) {
    $id = (int) $_GET['edit_employee'];
    $stmt = $conn->prepare('SELECT * FROM karyawan WHERE id_karyawan = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}

$departments = get_departments();
$positions = get_positions();

$employees = $conn->query('SELECT k.*, d.nama_departemen, j.nama_jabatan FROM karyawan k LEFT JOIN departemen d ON d.id_departemen = k.id_departemen LEFT JOIN jabatan j ON j.id_jabatan = k.id_jabatan ORDER BY k.nama_karyawan ASC');
?>

<h2>Master Karyawan</h2>

<form method="POST" action="index.php?page=karyawan">
    <input type="hidden" name="id_karyawan" value="<?= e($edit['id_karyawan'] ?? '') ?>">

    <div class="form-wrap">
        <div>
            <label for="nik">NIK</label>
            <input type="text" id="nik" name="nik" value="<?= e($edit['nik'] ?? '') ?>" required>
        </div>
        <div>
            <label for="nama_karyawan">Nama Karyawan</label>
            <input type="text" id="nama_karyawan" name="nama_karyawan" value="<?= e($edit['nama_karyawan'] ?? '') ?>" required>
        </div>
        <div>
            <label for="tanggal_lahir">Tanggal Lahir</label>
            <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="<?= e($edit['tanggal_lahir'] ?? '') ?>">
        </div>
        <div>
            <label for="jenis_kelamin">Jenis Kelamin</label>
            <select id="jenis_kelamin" name="jenis_kelamin">
                <option value="Laki-laki" <?= (($edit['jenis_kelamin'] ?? 'Laki-laki') === 'Laki-laki') ? 'selected' : '' ?>>Laki-laki</option>
                <option value="Perempuan" <?= (($edit['jenis_kelamin'] ?? 'Laki-laki') === 'Perempuan') ? 'selected' : '' ?>>Perempuan</option>
            </select>
        </div>
        <div>
            <label for="id_departemen">Departemen</label>
            <select id="id_departemen" name="id_departemen" required>
                <option value="">-- Pilih Departemen --</option>
                <?php while ($dept = $departments->fetch_assoc()): ?>
                    <option value="<?= $dept['id_departemen'] ?>" <?= (($edit['id_departemen'] ?? '') == $dept['id_departemen']) ? 'selected' : '' ?>><?= e($dept['nama_departemen']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label for="id_jabatan">Jabatan</label>
            <select id="id_jabatan" name="id_jabatan" required>
                <option value="">-- Pilih Jabatan --</option>
                <?php while ($pos = $positions->fetch_assoc()): ?>
                    <option value="<?= $pos['id_jabatan'] ?>" <?= (($edit['id_jabatan'] ?? '') == $pos['id_jabatan']) ? 'selected' : '' ?>><?= e($pos['nama_jabatan']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label for="tanggal_masuk">Tanggal Masuk</label>
            <input type="date" id="tanggal_masuk" name="tanggal_masuk" value="<?= e($edit['tanggal_masuk'] ?? '') ?>" required>
        </div>
        <div>
            <label for="status_kerja">Status Kerja</label>
            <select id="status_kerja" name="status_kerja">
                <option value="Aktif" <?= (($edit['status_kerja'] ?? 'Aktif') === 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                <option value="Cuti" <?= (($edit['status_kerja'] ?? 'Aktif') === 'Cuti') ? 'selected' : '' ?>>Cuti</option>
                <option value="Resign" <?= (($edit['status_kerja'] ?? 'Aktif') === 'Resign') ? 'selected' : '' ?>>Resign</option>
            </select>
        </div>
        <div>
            <label for="gaji_pokok">Gaji Pokok</label>
            <input type="number" id="gaji_pokok" name="gaji_pokok" min="0" step="1000" value="<?= e($edit['gaji_pokok'] ?? 0) ?>" required>
        </div>
        <div>
            <label for="nomor_telepon">Nomor Telepon</label>
            <input type="text" id="nomor_telepon" name="nomor_telepon" value="<?= e($edit['nomor_telepon'] ?? '') ?>">
        </div>
        <div>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= e($edit['email'] ?? '') ?>">
        </div>
    </div>

    <div style="margin-top: 16px;">
        <label for="alamat">Alamat</label>
        <textarea id="alamat" name="alamat"><?= e($edit['alamat'] ?? '') ?></textarea>
    </div>

    <div class="form-actions">
        <button type="submit" name="save_employee">Simpan</button>
        <?php if ($edit): ?>
            <a href="index.php?page=karyawan" class="btn-link"><button type="button" class="secondary">Batal</button></a>
        <?php endif; ?>
    </div>
</form>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>NIK</th>
                <th>Nama</th>
                <th>Departemen</th>
                <th>Jabatan</th>
                <th>Gaji Pokok</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $employees->fetch_assoc()): ?>
                <tr>
                    <td><?= e($row['nik']) ?></td>
                    <td><?= e($row['nama_karyawan']) ?></td>
                    <td><?= e($row['nama_departemen']) ?></td>
                    <td><?= e($row['nama_jabatan']) ?></td>
                    <td><?= format_rp($row['gaji_pokok']) ?></td>
                    <td><span class="badge <?= $row['status_kerja'] === 'Aktif' ? 'success' : 'warning' ?>"><?= e($row['status_kerja']) ?></span></td>
                    <td>
                        <a href="index.php?page=karyawan&edit_employee=<?= $row['id_karyawan'] ?>">Edit</a> |
                        <a href="index.php?page=karyawan&delete_employee=<?= $row['id_karyawan'] ?>" onclick="return confirm('Hapus data ini?')">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
