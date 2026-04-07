<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: ../index.php"); exit(); }
include '../koneksi.php'; 

// --- LOGIKA HAPUS (Ditingkatkan) ---
if (isset($_GET['hapus'])) {
    $nis_hapus = mysqli_real_escape_string($conn, $_GET['hapus']);
    
    // Menggunakan Prepared Statement untuk penghapusan
    // Catatan: Pastikan di Database, Foreign Key ke tabel laporan diset "ON DELETE SET NULL" atau "ON DELETE CASCADE"
    $query_hapus = "DELETE FROM siswa WHERE nis = ?";
    $stmt_hapus = mysqli_prepare($conn, $query_hapus);
    mysqli_stmt_bind_param($stmt_hapus, "s", $nis_hapus);
    
    if (mysqli_stmt_execute($stmt_hapus)) {
        echo "<script>alert('Data berhasil dihapus dari sistem.'); window.location='data_siswa.php';</script>";
    } else {
        // Jika gagal karena Foreign Key, beri pesan yang lebih edukatif
        echo "<script>alert('Gagal menghapus! Siswa masih memiliki keterkaitan data lain.'); window.location='data_siswa.php';</script>";
    }
    mysqli_stmt_close($stmt_hapus);
}

// --- LOGIKA PENCARIAN & PAGINATION ---
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search_term = "%$cari%";

// Hitung Total Data
$query_count = "SELECT COUNT(*) AS total FROM siswa WHERE nis LIKE ? OR nama LIKE ?";
$stmt_count = mysqli_prepare($conn, $query_count);
mysqli_stmt_bind_param($stmt_count, "ss", $search_term, $search_term);
mysqli_stmt_execute($stmt_count);
$total_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_count))['total'];
$total_pages = ceil($total_data / $limit);

// Ambil Data Siswa
$query_siswa = "SELECT * FROM siswa WHERE nis LIKE ? OR nama LIKE ? ORDER BY nama ASC LIMIT ? OFFSET ?";
$stmt_siswa = mysqli_prepare($conn, $query_siswa);
mysqli_stmt_bind_param($stmt_siswa, "ssii", $search_term, $search_term, $limit, $offset);
mysqli_stmt_execute($stmt_siswa);
$result_siswa = mysqli_stmt_get_result($stmt_siswa);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Siswa | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen font-sans text-slate-900">

    <header class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-slate-200 px-6 py-4">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="bg-blue-600 p-2.5 rounded-xl shadow-lg shadow-blue-200 text-white">
                    <i class="fas fa-graduation-cap text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-slate-800">Data Master Siswa</h1>
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-widest">Panel Administrasi</p>
                </div>
            </div>
            
            <div class="flex gap-2 w-full md:w-auto">
                <a href="tambah_siswa.php" class="flex-1 md:flex-none bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-plus-circle"></i> Tambah Baru
                </a>
                <a href="dashboard.php" class="flex-1 md:flex-none bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-5 py-2.5 rounded-xl font-semibold text-sm transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto p-6">
        
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 mb-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div>
                <span class="text-sm font-semibold text-blue-600 bg-blue-50 px-3 py-1 rounded-full">Total: <?= $total_data ?> Siswa</span>
                <h2 class="text-lg font-bold mt-2">Daftar Akun Terdaftar</h2>
            </div>

            <form method="GET" class="relative group w-full md:w-96">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                <input type="text" name="cari" value="<?= htmlspecialchars($cari) ?>" placeholder="Cari NIS atau Nama..." 
                    class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all text-sm">
                <?php if($cari != ''): ?>
                    <a href="data_siswa.php" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 hover:text-red-500">RESET</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500 text-xs font-bold uppercase tracking-wider border-b border-slate-100">
                            <th class="px-8 py-5">No</th>
                            <th class="px-6 py-5">Identitas Siswa</th>
                            <th class="px-6 py-5">NIS</th>
                            <th class="px-6 py-5 text-center">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php 
                        $no = $offset + 1;
                        if(mysqli_num_rows($result_siswa) > 0):
                            while($s = mysqli_fetch_assoc($result_siswa)): 
                        ?>
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-8 py-5 text-sm text-slate-400 font-medium"><?= $no++ ?></td>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-tr from-slate-100 to-slate-200 rounded-full flex items-center justify-center text-slate-500 font-bold text-xs shadow-inner">
                                        <?= strtoupper(substr($s['nama'], 0, 1)) ?>
                                    </div>
                                    <span class="font-bold text-slate-700 group-hover:text-blue-600 transition-colors"><?= htmlspecialchars($s['nama']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-sm font-mono text-slate-500"><?= htmlspecialchars($s['nis']) ?></td>
                            <td class="px-6 py-5">
                                <div class="flex justify-center gap-2">
                                    <a href="edit_siswa.php?nis=<?= $s['nis'] ?>" class="w-9 h-9 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-600 hover:text-white transition-all shadow-sm" title="Edit">
                                        <i class="fas fa-pen text-xs"></i>
                                    </a>
                                    <a href="data_siswa.php?hapus=<?= $s['nis'] ?>" 
                                       onclick="return confirm('Apakah Anda yakin? Data laporan siswa ini tetap aman namun identitas pengirim akan terputus.')"
                                       class="w-9 h-9 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Hapus">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="w-16 h-16 bg-slate-100 text-slate-300 rounded-full flex items-center justify-center text-3xl">
                                        <i class="fas fa-user-slash"></i>
                                    </div>
                                    <p class="text-slate-400 font-medium italic text-sm">Tidak ada data siswa ditemukan</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($total_pages > 1): ?>
            <div class="p-6 bg-slate-50/30 border-t border-slate-100 flex justify-center gap-2">
                <?php for($i=1; $i<=$total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>&cari=<?= urlencode($cari) ?>" 
                       class="w-10 h-10 flex items-center justify-center rounded-xl text-sm font-bold transition-all <?= ($page == $i) ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-white border border-slate-200 text-slate-500 hover:border-blue-400 hover:text-blue-600' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>