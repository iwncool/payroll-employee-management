<?php
require_once __DIR__ . '/../db.php';

if (isset($_GET['slip_id'])) {
    $id = (int) $_GET['slip_id'];
    $sql = 'SELECT p.*, k.nama_karyawan, k.nik, k.status_kerja, d.nama_departemen, j.nama_jabatan
            FROM payroll p
            LEFT JOIN karyawan k ON k.id_karyawan = p.id_karyawan
            LEFT JOIN departemen d ON d.id_departemen = k.id_departemen
            LEFT JOIN jabatan j ON j.id_jabatan = k.id_jabatan
            WHERE p.id_payroll = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $pay = $stmt->get_result()->fetch_assoc();

    if (!$pay) {
        $_SESSION['error'] = 'Slip gaji tidak ditemukan.';
        header('Location: index.php?page=payroll');
        exit;
    }

    echo '<!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Slip Gaji</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 30px; color: #1f2937; }
            .box { max-width: 760px; margin: 0 auto; border: 2px solid #1d4ed8; border-radius: 12px; padding: 24px; }
            .header { display: flex; justify-content: space-between; border-bottom: 2px solid #dbeafe; padding-bottom: 12px; margin-bottom: 18px; }
            h2 { margin: 0; color: #1d4ed8; }
            table { width: 100%; border-collapse: collapse; margin-top: 12px; }
            th, td { border: 1px solid #dbeafe; padding: 10px; text-align: left; }
            th { background: #eff6ff; }
            .total { background: #f8fafc; font-weight: 700; }
            .btn { display: inline-block; margin-top: 18px; padding: 10px 16px; background: #2563eb; color: white; text-decoration: none; border-radius: 8px; }
        </style>
    </head>
    <body>
        <div class="box">
            <div class="header">
                <div>
                    <h2>Slip Gaji</h2>
                    <p>Periode: ' . e($pay['bulan']) . '/' . e($pay['tahun']) . '</p>
                </div>
                <div>
                    <p><strong>Nomor Slip</strong><br>#' . e($pay['id_payroll']) . '</p>
                </div>
            </div>

            <p><strong>Nama Karyawan:</strong> ' . e($pay['nama_karyawan']) . '</p>
            <p><strong>NIK:</strong> ' . e($pay['nik']) . ' | <strong>Jabatan:</strong> ' . e($pay['nama_jabatan']) . ' | <strong>Departemen:</strong> ' . e($pay['nama_departemen']) . '</p>

            <table>
                <tr><th>Komponen</th><th>Nominal</th></tr>
                <tr><td>Gaji Pokok</td><td>' . format_rp($pay['gaji_pokok']) . '</td></tr>
                <tr><td>Tunjangan</td><td>' . format_rp($pay['total_tunjangan']) . '</td></tr>
                <tr><td>UMR Referensi</td><td>' . format_rp($pay['umr_referensi']) . '</td></tr>
                <tr><td>BPJS Kesehatan</td><td>' . format_rp($pay['bpjs_kesehatan']) . '</td></tr>
                <tr><td>BPJS Ketenagakerjaan</td><td>' . format_rp($pay['bpjs_ketenagakerjaan']) . '</td></tr>
                <tr><td>BPJS Pensiun</td><td>' . format_rp($pay['bpjs_pensiun']) . '</td></tr>
                <tr><td>Total Potongan BPJS</td><td>' . format_rp($pay['total_bpjs']) . '</td></tr>
                <tr class="total"><td><strong>Gaji Bersih</strong></td><td><strong>' . format_rp($pay['gaji_bersih']) . '</strong></td></tr>
            </table>

            <a class="btn" href="#" onclick="window.print(); return false;">Cetak Slip</a>
            <a class="btn" href="index.php?page=payroll" style="background:#64748b; margin-left:10px;">Kembali</a>
        </div>
    </body>
    </html>';
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
                <th>Aksi</th>
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
                    <td><a href="index.php?page=payroll&slip_id=<?= $row['id_payroll'] ?>" target="_blank">Slip</a></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
