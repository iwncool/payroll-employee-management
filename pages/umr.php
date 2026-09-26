<?php
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_umr'])) {
    $tahun = (int) $_POST['tahun'];
    $bulan = (int) $_POST['bulan'];
    $jumlah = (float) $_POST['jumlah_umr'];

    $sql = 'INSERT INTO umr (tahun, bulan, jumlah_umr) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE jumlah_umr = VALUES(jumlah_umr)';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iid', $tahun, $bulan, $jumlah);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Data UMR berhasil disimpan.';
    } else {
        $_SESSION['error'] = 'Gagal menyimpan data UMR.';
    }

    header('Location: index.php?page=umr');
    exit;
}

$umrList = $conn->query('SELECT * FROM umr ORDER BY tahun DESC, bulan DESC');
?>

<h2>Data UMR</h2>

<form method="POST" action="index.php?page=umr">
    <div class="form-wrap">
        <div>
            <label for="tahun">Tahun</label>
            <input type="number" id="tahun" name="tahun" min="2020" value="2026" required>
        </div>
        <div>
            <label for="bulan">Bulan</label>
            <select id="bulan" name="bulan" required>
                <?php for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?= $i ?>"><?= date('F', mktime(0,0,0,$i,1,2024)) ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div>
            <label for="jumlah_umr">Nilai UMR</label>
            <input type="number" id="jumlah_umr" name="jumlah_umr" step="1000" min="0" required>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" name="save_umr">Simpan UMR</button>
    </div>
</form>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Tahun</th>
                <th>Bulan</th>
                <th>Nilai UMR</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $umrList->fetch_assoc()): ?>
                <tr>
                    <td><?= e($row['tahun']) ?></td>
                    <td><?= e($row['bulan']) ?></td>
                    <td><?= format_rp($row['jumlah_umr']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
