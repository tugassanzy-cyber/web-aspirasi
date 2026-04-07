<?php
session_start();
if (!isset($_SESSION['siswa'])) {
    header("Location: ../index.php");
    exit();
}

include '../koneksi.php';
$nis = $_SESSION['siswa'];

// --- PROSES HAPUS (CANCEL) LAPORAN ---
if (isset($_GET['hapus'])) {
    $id_hapus = (int) $_GET['hapus'];

    // Cek kepemilikan dan status laporan
    $stmt_cek = mysqli_prepare($conn, "SELECT ia.foto, a.status FROM input_aspirasi ia 
                                      LEFT JOIN aspirasi a ON ia.id_pelaporan = a.id_pelaporan 
                                      WHERE ia.id_pelaporan = ? AND ia.nis = ?");
    mysqli_stmt_bind_param($stmt_cek, "is", $id_hapus, $nis);
    mysqli_stmt_execute($stmt_cek);
    $res_cek = mysqli_stmt_get_result($stmt_cek);

    if ($data = mysqli_fetch_assoc($res_cek)) {
        $status = $data['status'] ?? 'Menunggu';
        if ($status === 'Menunggu') {
            // Hapus file fisik
            if ($data['foto']) {
                @unlink("../uploads/" . $data['foto']);
            }
            // Hapus record
            $stmt_del = mysqli_prepare($conn, "DELETE FROM input_aspirasi WHERE id_pelaporan = ?");
            mysqli_stmt_bind_param($stmt_del, "i", $id_hapus);
            mysqli_stmt_execute($stmt_del);
            
            $_SESSION['msg'] = "Laporan berhasil dibatalkan!";
        } else {
            $_SESSION['err'] = "Laporan sudah diproses, tidak bisa dibatalkan.";
        }
    }
    header("Location: dashboard.php");
    exit();
}

// --- PROSES KIRIM LAPORAN ---
if (isset($_POST['kirim'])) {
    $id_kat = (int)$_POST['id_kategori'];
    $lokasi = mysqli_real_escape_string($conn, trim($_POST['lokasi']));
    $pesan  = mysqli_real_escape_string($conn, trim($_POST['pesan']));
    $foto_name = NULL;

    // Logika Upload
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png']) && $_FILES['foto']['size'] <= 2*1024*1024) {
            $foto_name = uniqid('IMG_') . '.' . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], "../uploads/" . $foto_name);
        }
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO input_aspirasi (nis, id_kategori, tanggal, lokasi, pesan, foto) VALUES (?, ?, NOW(), ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sisss", $nis, $id_kat, $lokasi, $pesan, $foto_name);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['msg'] = "Aspirasi Anda telah terkirim!";
    } else {
        $_SESSION['err'] = "Gagal mengirim aspirasi.";
    }
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siswa Dashboard | E-Aspirasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/siswa.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen pb-12">

    <nav class="top-bar sticky top-4 mx-4 z-50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div>
                <h2 class="text-sm font-bold leading-none"><?= htmlspecialchars($_SESSION['nama_siswa']) ?></h2>
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">NIS: <?= $nis ?></span>
            </div>
        </div>
        <div class="action-links flex gap-2">
            <a href="ubah_password.php" class="btn-outline text-xs"><i class="fas fa-key mr-1"></i> Sandi</a>
            <a href="../logout.php" class="btn-danger text-xs"><i class="fas fa-sign-out-alt mr-1"></i> Keluar</a>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 mt-8">
        
        <?php if(isset($_SESSION['msg'])): ?>
            <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 mb-6 rounded-r-xl animate-pulse">
                <p class="font-bold">Sukses!</p>
                <p class="text-sm"><?= $_SESSION['msg']; unset($_SESSION['msg']); ?></p>
            </div>
        <?php endif; ?>

        <div class="dashboard-grid items-start">
            
            <aside class="card-box sticky top-24">
                <h3><i class="fas fa-pen-nib mr-2 text-blue-500"></i>Buat Laporan</h3>
                <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <div class="form-group">
                        <label>Pilih Kategori</label>
                        <select name="id_kategori" class="form-control" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php
                            $kat_res = mysqli_query($conn, "SELECT * FROM kategori");
                            while($k = mysqli_fetch_assoc($kat_res)) echo "<option value='{$k['id_kategori']}'>{$k['ket_kategori']}</option>";
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Lokasi Spesifik</label>
                        <input type="text" name="lokasi" class="form-control" placeholder="Misal: Lab Komputer 2" required>
                    </div>

                    <div class="form-group">
                        <label>Detail Aspirasi</label>
                        <textarea name="pesan" class="form-control h-32" placeholder="Jelaskan masalah atau saran Anda..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Unggah Bukti</label>
                        <div class="relative group border-2 border-dashed border-slate-200 rounded-2xl p-4 transition-all hover:border-blue-400">
                            <input type="file" name="foto" id="fotoInput" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                            <div class="text-center" id="previewContainer">
                                <i class="fas fa-cloud-upload-alt text-2xl text-slate-300 group-hover:text-blue-500 mb-2"></i>
                                <p class="text-[10px] text-slate-400 font-bold uppercase">Klik untuk pilih foto</p>
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="kirim" class="btn-submit w-full py-4 text-white">
                        Kirim Laporan <i class="fas fa-paper-plane ml-2"></i>
                    </button>
                </form>
            </aside>

            <section class="space-y-4">
                <div class="flex justify-between items-center mb-2 px-2">
                    <h3 class="font-bold text-slate-800 text-lg">Riwayat Laporan</h3>
                    <span class="bg-slate-200 text-slate-600 px-3 py-1 rounded-full text-[10px] font-black uppercase">Terbaru</span>
                </div>

                <div class="table-wrapper border-none shadow-none bg-transparent">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-slate-400 text-[10px] uppercase tracking-widest font-black">
                                <th class="pb-4 px-4">Info Dasar</th>
                                <th class="pb-4">Pesan</th>
                                <th class="pb-4">Status & Balasan</th>
                                <th class="pb-4 text-right">Opsi</th>
                            </tr>
                        </thead>
                        <tbody class="space-y-4">
                            <?php
                            $q = "SELECT ia.*, k.ket_kategori, a.status, a.feedback 
                                  FROM input_aspirasi ia 
                                  JOIN kategori k ON ia.id_kategori = k.id_kategori 
                                  LEFT JOIN aspirasi a ON ia.id_pelaporan = a.id_pelaporan 
                                  WHERE ia.nis = ? ORDER BY ia.id_pelaporan DESC";
                            $stmt_q = mysqli_prepare($conn, $q);
                            mysqli_stmt_bind_param($stmt_q, "s", $nis);
                            mysqli_stmt_execute($stmt_q);
                            $riwayat = mysqli_stmt_get_result($stmt_q);

                            if(mysqli_num_rows($riwayat) > 0):
                                while($row = mysqli_fetch_assoc($riwayat)):
                                    $st = $row['status'] ?? 'Menunggu';
                            ?>
                            <tr class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-all group">
                                <td class="py-6 px-4 rounded-l-2xl">
                                    <div class="flex items-center gap-4">
                                        <?php if($row['foto']): ?>
                                            <img src="../uploads/<?= $row['foto'] ?>" class="w-12 h-12 rounded-lg object-cover ring-2 ring-slate-100">
                                        <?php else: ?>
                                            <div class="w-12 h-12 bg-slate-50 rounded-lg flex items-center justify-center text-slate-300"><i class="fas fa-image text-xl"></i></div>
                                        <?php endif; ?>
                                        <div>
                                            <p class="text-[10px] font-black text-blue-500 uppercase"><?= $row['ket_kategori'] ?></p>
                                            <p class="text-xs text-slate-400"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-6">
                                    <p class="text-xs font-bold text-slate-400 mb-1"><i class="fas fa-map-marker-alt mr-1"></i> <?= $row['lokasi'] ?></p>
                                    <p class="text-sm text-slate-700 line-clamp-2 italic">"<?= $row['pesan'] ?>"</p>
                                </td>
                                <td class="py-6">
                                    <span class="badge status-<?= strtolower($st) ?> mb-2 inline-block"><?= $st ?></span>
                                    <p class="text-xs text-slate-500 max-w-xs leading-relaxed">
                                        <?= $row['feedback'] ? '<strong>Admin:</strong> '.htmlspecialchars($row['feedback']) : '<i class="text-slate-300">Belum ada respon...</i>' ?>
                                    </p>
                                </td>
                                <td class="py-6 px-4 rounded-r-2xl text-right">
                                    <?php if($st === 'Menunggu'): ?>
                                        <a href="?hapus=<?= $row['id_pelaporan'] ?>" class="btn-hapus px-4 py-2" onclick="return confirm('Batalkan laporan?')">Batal</a>
                                    <?php else: ?>
                                        <i class="fas fa-check-circle text-emerald-400 text-xl" title="Sudah diverifikasi"></i>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="4" class="text-center py-20 text-slate-400 font-medium">Belum ada aktivitas laporan.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <script>
        // Preview Foto Sederhana
        document.getElementById('fotoInput').onchange = function(e) {
            const [file] = e.target.files;
            if (file) {
                document.getElementById('previewContainer').innerHTML = `
                    <p class="text-xs text-blue-600 font-bold mb-1">${file.name}</p>
                    <p class="text-[10px] text-slate-400">File terpilih</p>
                `;
            }
        };
    </script>
</body>
</html>