<?php
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_allowance'])) {
    $nama = trim($_POST['nama_tunjangan']);
    $tipe = $_POST['tipe_tunjangan'] ?? 'Fixed';
    $jumlah = (float) ($_POST['jumlah'] ?? 0);
    $status = $_POST['status'] ?? 'Aktif';
    $keterangan = trim($_POST['keterangan']);

    $sql = 'INSERT INTO tunjangan (nama_tunjangan, tipe_tunjangan, jumlah, status, keterangan) VALUES (?, ?, ?, ?, ?)';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssss', $nama, $tipe, $jumlah, $status, $keterangan);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Data tunjangan berhasil ditambahkan.';
    } else {
        $_SESSION['error'] = 'Gagal menambahkan tunjangan.';
    }

    header('Location: index.php?page=tunjangan');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_allowance'])) {
    $id_karyawan = (int) $_POST['id_karyawan'];
    $id_tunjangan = (int) $_POST['id_tunjangan'];
    $jumlah = (float) ($_POST['jumlah'] ?? 0);

    $sql = 'INSERT INTO detail_tunjangan_karyawan (id_karyawan, id_tunjangan, jumlah, status) VALUES (?, ?, ?, "Aktif")';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iid', $id_karyawan, $id_tunjangan, $jumlah);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Tunjangan berhasil ditetapkan ke karyawan.';
    } else {
        $_SESSION['error'] = 'Gagal menetapkan tunjangan.';
    }

    header('Location: index.php?page=tunjangan');
    exit;
}

if (isset($_GET['delete_allowance'])) {
    $id = (int) $_GET['delete_allowance'];
    $stmt = $conn->prepare('DELETE FROM tunjangan WHERE id_tunjangan = ?');
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Tunjangan berhasil dihapus.';
    } else {
        $_SESSION['error'] = 'Gagal menghapus tunjangan.';
    }
    header('Location: index.php?page=tunjangan');
    exit;
}

$allowances = $conn->query('SELECT * FROM tunjangan ORDER BY nama_tunjangan ASC');
$employees = $conn->query('SELECT * FROM karyawan ORDER BY nama_karyawan ASC');
$assigned = $conn->query('SELECT dtk.*, k.nama_karyawan, t.nama_tunjangan FROM detail_tunjangan_karyawan dtk LEFT JOIN karyawan k ON k.id_karyawan = dtk.id_karyawan LEFT JOIN tunjangan t ON t.id_tunjangan = dtk.id_tunjangan ORDER BY k.nama_karyawan ASC');
?>

<h2>Manajemen Tunjangan</h2>

<form method="POST" action="index.php?page=tunjangan">
    <div class="form-wrap">
        <div>
            <label for="nama_tunjangan">Nama Tunjangan</label>
            <input type="text" id="nama_tunjangan" name="nama_tunjangan" required>
        </div>
        <div>
            <label for="tipe_tunjangan">Tipe</label>
            <select id="tipe_tunjangan" name="tipe_tunjangan">
                <option value="Fixed">Fixed</option>
                <option value="Percentage">Percentage</option>
            </select>
        </div>
        <div>
            <label for="jumlah">Nilai</label>
            <input type="number" id="jumlah" name="jumlah" step="1000" min="0" required>
        </div>
        <div>
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="Aktif">Aktif</option>
                <option value="Nonaktif">Nonaktif</option>
            </select>
        </div>
    </div>

    <div style="margin-top: 16px;">
        <label for="keterangan">Keterangan</label>
        <textarea id="keterangan" name="keterangan"></textarea>
    </div>

    <div class="form-actions">
        <button type="submit" name="save_allowance">Simpan Tunjangan</button>
    </div>
</form>

<h3>Penetapan Tunjangan ke Karyawan</h3>

<form method="POST" action="index.php?page=tunjangan">
    <div class="form-wrap">
        <div>
            <label for="id_karyawan">Karyawan</label>
            <select id="id_karyawan" name="id_karyawan" required>
                <option value="">-- Pilih Karyawan --</option>
                <?php while ($emp = $employees->fetch_assoc()): ?>
                    <option value="<?= $emp['id_karyawan'] ?>"><?= e($emp['nama_karyawan']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label for="id_tunjangan">Tunjangan</label>
            <select id="id_tunjangan" name="id_tunjangan" required>
                <option value="">-- Pilih Tunjangan --</option>
                <?php while ($item = $allowances->fetch_assoc()): ?>
                    <option value="<?= $item['id_tunjangan'] ?>"><?= e($item['nama_tunjangan']) ?> (<?= format_rp($item['jumlah']) ?>)</option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label for="jumlah_tunjangan">Nilai Khusus</label>
            <input type="number" id="jumlah_tunjangan" name="jumlah" step="1000" min="0" value="0">
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" name="assign_allowance">Tetapkan Tunjangan</button>
    </div>
</form>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Karyawan</th>
                <th>Tunjangan</th>
                <th>Nilai</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $assigned->fetch_assoc()): ?>
                <tr>
                    <td><?= e($row['nama_karyawan']) ?></td>
                    <td><?= e($row['nama_tunjangan']) ?></td>
                    <td><?= format_rp($row['jumlah']) ?></td>
                    <td><span class="badge success">Aktif</span></td>
                    <td>
                        <a href="index.php?page=tunjangan&delete_allowance=<?= $row['id_tunjangan'] ?>" onclick="return confirm('Hapus tunjangan ini?')">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
