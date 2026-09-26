<?php
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_payroll'])) {
    $id_karyawan = (int) $_POST['id_karyawan'];
    $bulan = (int) $_POST['bulan'];
    $tahun = (int) $_POST['tahun'];

    $employee = $conn->query('SELECT * FROM karyawan WHERE id_karyawan = ' . $id_karyawan)->fetch_assoc();
    if (!$employee) {
        $_SESSION['error'] = 'Karyawan tidak ditemukan.';
        header('Location: index.php?page=payroll');
        exit;
    }

    $umr = get_umr_value($tahun, $bulan);
    if ($umr <= 0) {
        $_SESSION['error'] = 'UMR untuk bulan dan tahun tersebut belum tersedia.';
        header('Location: index.php?page=payroll');
        exit;
    }

    $gajiPokok = (float) $employee['gaji_pokok'];
    $totalTunjangan = 0;
    $allowanceRows = $conn->query('SELECT dtk.*, t.nama_tunjangan, t.jumlah AS nilai_default FROM detail_tunjangan_karyawan dtk LEFT JOIN tunjangan t ON t.id_tunjangan = dtk.id_tunjangan WHERE dtk.id_karyawan = ' . $id_karyawan . ' AND dtk.status = "Aktif"');
    while ($allowance = $allowanceRows->fetch_assoc()) {
        $totalTunjangan += (float) ($allowance['jumlah'] ?: $allowance['nilai_default']);
    }

    $bpjs = calc_bpjs_from_umr($umr);
    $potonganTotal = (float) $bpjs['total_bpjs'];
    $gajiBersih = ($gajiPokok + $totalTunjangan) - $potonganTotal;

    $sql = 'INSERT INTO payroll (id_karyawan, tahun, bulan, gaji_pokok, total_tunjangan, umr_referensi, bpjs_kesehatan, bpjs_ketenagakerjaan, bpjs_pensiun, total_bpjs, total_potongan, gaji_bersih, status_payroll, tanggal_bayar)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "Approved", CURDATE())
            ON DUPLICATE KEY UPDATE
                gaji_pokok = VALUES(gaji_pokok),
                total_tunjangan = VALUES(total_tunjangan),
                umr_referensi = VALUES(umr_referensi),
                bpjs_kesehatan = VALUES(bpjs_kesehatan),
                bpjs_ketenagakerjaan = VALUES(bpjs_ketenagakerjaan),
                bpjs_pensiun = VALUES(bpjs_pensiun),
                total_bpjs = VALUES(total_bpjs),
                total_potongan = VALUES(total_potongan),
                gaji_bersih = VALUES(gaji_bersih),
                status_payroll = VALUES(status_payroll),
                tanggal_bayar = CURDATE()';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiiddddddddd', $id_karyawan, $tahun, $bulan, $gajiPokok, $totalTunjangan, $umr, $bpjs['bpjs_kesehatan'], $bpjs['bpjs_ketenagakerjaan'], $bpjs['bpjs_pensiun'], $bpjs['total_bpjs'], $potonganTotal, $gajiBersih);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Payroll berhasil dihitung dan disimpan.';
    } else {
        $_SESSION['error'] = 'Gagal menghitung payroll.';
    }

    header('Location: index.php?page=payroll');
    exit;
}

$employees = $conn->query('SELECT * FROM karyawan ORDER BY nama_karyawan ASC');
$payrolls = $conn->query('SELECT p.*, k.nama_karyawan FROM payroll p LEFT JOIN karyawan k ON k.id_karyawan = p.id_karyawan ORDER BY p.tahun DESC, p.bulan DESC, k.nama_karyawan ASC');
?>

<h2>Payroll</h2>

<form method="POST" action="index.php?page=payroll">
    <div class="form-wrap">
        <div>
            <label for="id_karyawan">Pilih Karyawan</label>
            <select id="id_karyawan" name="id_karyawan" required>
                <option value="">-- Pilih Karyawan --</option>
                <?php while ($emp = $employees->fetch_assoc()): ?>
                    <option value="<?= $emp['id_karyawan'] ?>"><?= e($emp['nama_karyawan']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label for="bulan">Bulan</label>
            <select id="bulan" name="bulan" required>
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>"><?= date('F', mktime(0,0,0,$m,1,2024)) ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div>
            <label for="tahun">Tahun</label>
            <input type="number" id="tahun" name="tahun" min="2020" value="2026" required>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" name="generate_payroll">Hitung Payroll</button>
    </div>
</form>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Periode</th>
                <th>Gaji Pokok</th>
                <th>Tunjangan</th>
                <th>UMR</th>
                <th>BPJS</th>
                <th>Total Potongan</th>
                <th>Gaji Bersih</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $payrolls->fetch_assoc()): ?>
                <tr>
                    <td><?= e($row['nama_karyawan']) ?></td>
                    <td><?= e($row['bulan']) . '/' . e($row['tahun']) ?></td>
                    <td><?= format_rp($row['gaji_pokok']) ?></td>
                    <td><?= format_rp($row['total_tunjangan']) ?></td>
                    <td><?= format_rp($row['umr_referensi']) ?></td>
                    <td><?= format_rp($row['total_bpjs']) ?></td>
                    <td><?= format_rp($row['total_potongan']) ?></td>
                    <td><?= format_rp($row['gaji_bersih']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
