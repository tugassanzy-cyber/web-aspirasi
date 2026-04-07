<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: ../index.php"); exit(); }
include '../koneksi.php'; 

// --- LOGIKA SEARCH & PAGINATION ---
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$limit = 5; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search_term = "%$cari%";

// Count Total (Status Selesai)
$query_count = "SELECT COUNT(*) AS total FROM input_aspirasi ia
                LEFT JOIN siswa s ON ia.nis = s.nis
                JOIN aspirasi a ON ia.id_pelaporan = a.id_pelaporan
                WHERE a.status = 'Selesai' AND (COALESCE(s.nama, '[Akun Dihapus]') LIKE ? OR ia.nis LIKE ? OR ia.pesan LIKE ?)";
$stmt_count = mysqli_prepare($conn, $query_count);
mysqli_stmt_bind_param($stmt_count, "sss", $search_term, $search_term, $search_term);
mysqli_stmt_execute($stmt_count);
$total_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_count))['total'];
$total_pages = ceil($total_data / $limit);

// Main Query
$query = "SELECT ia.*, s.nama, k.ket_kategori, a.status 
          FROM input_aspirasi ia
          LEFT JOIN siswa s ON ia.nis = s.nis
          JOIN kategori k ON ia.id_kategori = k.id_kategori
          JOIN aspirasi a ON ia.id_pelaporan = a.id_pelaporan
          WHERE a.status = 'Selesai' AND (COALESCE(s.nama, '[Akun Dihapus]') LIKE ? OR ia.nis LIKE ? OR ia.pesan LIKE ?)
          ORDER BY ia.id_pelaporan DESC LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "sssii", $search_term, $search_term, $search_term, $limit, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Riwayat Selesai</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-50 min-h-screen font-sans">

    <nav class="bg-white/80 backdrop-blur-md border-b sticky top-0 z-50 px-8 py-4 flex justify-between items-center shadow-sm">
        <div class="flex items-center gap-2">
            <div class="bg-blue-600 p-2 rounded-lg text-white">
                <i class="fas fa-shield-halved"></i>
            </div>
            <span class="font-bold text-slate-800 text-lg">AdminPanel</span>
        </div>
        <div class="flex gap-3">
            <a href="laporan_masuk.php" class="hidden md:flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm font-semibold transition">
                <i class="fas fa-inbox text-xs"></i> Laporan Masuk
            </a>
            <a href="data_siswa.php" class="hidden md:flex items-center gap-2 px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-sm font-semibold transition">
                <i class="fas fa-users text-xs"></i> Siswa
            </a>
            <a href="../logout.php" class="p-2.5 bg-red-50 text-red-500 rounded-xl hover:bg-red-500 hover:text-white transition">
                <i class="fas fa-power-off"></i>
            </a>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto p-6 lg:p-8">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
            
            <div class="lg:col-span-4 bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
                <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-chart-pie text-blue-500"></i> Statistik
                </h3>
                <?php include 'grafik.php'; ?>
            </div>

            <div class="lg:col-span-8 bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div>
                        <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                            <i class="fas fa-history text-emerald-500"></i> Riwayat Selesai
                        </h3>
                        <p class="text-xs text-slate-400 font-medium">Laporan yang telah tuntas ditangani</p>
                    </div>

                    <form method="GET" class="flex gap-2 w-full md:w-auto">
                        <div class="relative flex-1">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text" name="cari" value="<?= htmlspecialchars($cari) ?>" placeholder="Cari laporan..." 
                                class="w-full md:w-48 pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition">
                            <i class="fas fa-filter"></i>
                        </button>
                        <?php if($cari != ''): ?>
                            <a href="dashboard.php" class="bg-slate-100 text-slate-500 px-4 py-2 rounded-xl hover:bg-slate-200 transition flex items-center">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-slate-500 text-[10px] uppercase font-bold tracking-widest border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4">Siswa</th>
                                <th class="px-6 py-4">Isi Laporan</th>
                                <th class="px-6 py-4">Lampiran</th>
                                <th class="px-6 py-4 text-right">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-700 text-sm">
                                                <?= !empty($row['nama']) ? htmlspecialchars($row['nama']) : '<span class="text-red-400 italic text-xs">[Akun Dihapus]</span>' ?>
                                            </span>
                                            <span class="text-[10px] text-blue-500 font-bold uppercase"><?= htmlspecialchars($row['ket_kategori']) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600 leading-relaxed">
                                        <?= substr(htmlspecialchars($row['pesan']), 0, 45) ?>...
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if(!empty($row['foto'])): ?>
                                            <img src="../uploads/<?= htmlspecialchars($row['foto']) ?>" class="w-10 h-10 rounded-lg object-cover ring-2 ring-slate-100">
                                        <?php else: ?>
                                            <span class="text-xs text-slate-300 italic">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="text-xs font-mono text-slate-400">
                                            <?= !empty($row['tanggal']) ? date('d M Y', strtotime($row['tanggal'])) : '-' ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <i class="fas fa-folder-open text-slate-200 text-4xl mb-3 block"></i>
                                        <span class="text-slate-400 text-sm">Belum ada riwayat laporan selesai.</span>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if($total_pages > 1): ?>
                <div class="p-4 bg-slate-50/50 flex justify-center gap-1 border-t border-slate-100">
                    <?php for($i=1; $i<=$total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>&cari=<?= urlencode($cari) ?>" 
                           class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition-all <?= ($page == $i) ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-white border text-slate-500 hover:border-blue-500 hover:text-blue-500' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex flex-col md:flex-row justify-between items-center text-slate-400 text-[10px] font-bold uppercase tracking-widest bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p>&copy; 2026 Pengaduan Siswa Digital</p>
            <div class="flex gap-4 mt-2 md:mt-0">
                <span><i class="fas fa-circle text-emerald-500 text-[6px] mr-1"></i> System Online</span>
                <span>Security Protected</span>
            </div>
        </div>
    </div>

</body>
</html>