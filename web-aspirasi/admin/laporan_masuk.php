<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: ../index.php"); exit(); }
include '../koneksi.php'; 

// --- LOGIKA FILTER & SEARCH ---
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$filter_status = isset($_GET['filter_status']) ? trim($_GET['filter_status']) : '';

$where_clause = "(a.status IS NULL OR a.status = 'Menunggu' OR a.status = 'Proses')";
if ($filter_status != '') {
    $where_clause = ($filter_status == 'Menunggu') ? "(a.status IS NULL OR a.status = 'Menunggu')" : "a.status = 'Proses'";
}

$query = "SELECT ia.*, s.nama, k.ket_kategori, a.status 
          FROM input_aspirasi ia
          JOIN siswa s ON ia.nis = s.nis
          JOIN kategori k ON ia.id_kategori = k.id_kategori
          LEFT JOIN aspirasi a ON ia.id_pelaporan = a.id_pelaporan
          WHERE $where_clause AND (
              s.nama LIKE ? OR ia.nis LIKE ? OR ia.pesan LIKE ? OR 
              k.ket_kategori LIKE ? OR ia.lokasi LIKE ? OR ia.tanggal LIKE ?
          )
          ORDER BY ia.id_pelaporan DESC";

$stmt = mysqli_prepare($conn, $query);
$search_term = "%$cari%";
mysqli_stmt_bind_param($stmt, "ssssss", $search_term, $search_term, $search_term, $search_term, $search_term, $search_term);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Antrean Laporan | Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .status-line { width: 4px; height: 100%; position: absolute; left: 0; top: 0; }
        .img-preview:hover { transform: scale(2.5); z-index: 50; position: relative; transition: 0.3s; box-shadow: 0 10px 15px rgba(0,0,0,0.2); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen font-sans antialiased text-slate-900">

    <header class="bg-white/80 backdrop-blur-md border-b sticky top-0 z-40 px-6 py-4">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-4">
                <a href="dashboard.php" class="h-10 w-10 flex items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:bg-blue-600 hover:text-white transition-all">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-slate-800">Antrean Laporan</h1>
                    <p class="text-xs text-slate-400 font-medium tracking-wide italic">Menampilkan laporan yang perlu segera ditanggapi</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2 bg-slate-100 p-1 rounded-xl">
                <div class="px-3 py-1 text-[10px] font-bold uppercase text-slate-500">Urutkan: Terbaru</div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto p-6">
        
        <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200 mb-8">
            <form method="GET" class="flex flex-col lg:flex-row gap-4">
                <div class="relative flex-1 group">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                    <input type="text" name="cari" value="<?= htmlspecialchars($cari) ?>" placeholder="Cari pelapor, lokasi, atau isi laporan..." 
                        class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all text-sm">
                </div>
                
                <div class="flex gap-2">
                    <select name="filter_status" class="bg-slate-50 border border-slate-200 px-4 py-3 rounded-2xl text-sm font-semibold outline-none focus:border-blue-500">
                        <option value="">Semua Status</option>
                        <option value="Menunggu" <?= $filter_status == 'Menunggu' ? 'selected' : '' ?>>🟡 Menunggu</option>
                        <option value="Proses" <?= $filter_status == 'Proses' ? 'selected' : '' ?>>🔵 Proses</option>
                    </select>
                    
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-bold text-sm transition-all shadow-lg shadow-blue-100 flex items-center gap-2">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    
                    <?php if($cari != '' || $filter_status != ''): ?>
                        <a href="laporan_masuk.php" class="bg-red-50 text-red-500 hover:bg-red-500 hover:text-white px-4 py-3 rounded-2xl transition-all flex items-center">
                            <i class="fas fa-undo-alt"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500 text-[11px] font-bold uppercase tracking-widest border-b border-slate-100">
                            <th class="px-6 py-5">Info Pelapor</th>
                            <th class="px-6 py-5">Detail Kejadian</th>
                            <th class="px-6 py-5">Pesan Laporan</th>
                            <th class="px-6 py-5 text-center">Lampiran</th>
                            <th class="px-6 py-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php 
                        if (mysqli_num_rows($result) > 0):
                            while ($row = mysqli_fetch_assoc($result)): 
                                $status = !empty($row['status']) ? $row['status'] : 'Menunggu';
                                $line_color = ($status == 'Menunggu') ? 'bg-amber-400' : 'bg-blue-500';
                                $badge_style = ($status == 'Menunggu') ? 'bg-amber-50 text-amber-600' : 'bg-blue-50 text-blue-600';
                        ?>
                        <tr class="hover:bg-slate-50/80 transition-colors relative">
                            <td class="px-6 py-6 relative">
                                <div class="status-line <?= $line_color ?>"></div>
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($row['nama']) ?></span>
                                    <span class="text-[11px] font-mono text-slate-400 italic">NIS: <?= htmlspecialchars($row['nis']) ?></span>
                                    <div class="mt-2 inline-flex w-fit <?= $badge_style ?> px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-tighter">
                                        <?= $status ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2 text-xs font-bold text-slate-700">
                                        <i class="fas fa-tag text-blue-500 text-[10px]"></i> <?= htmlspecialchars($row['ket_kategori']) ?>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-slate-500">
                                        <i class="fas fa-map-marker-alt text-red-400 text-[10px]"></i> <?= htmlspecialchars($row['lokasi']) ?>
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-1">
                                        <?= !empty($row['tanggal']) ? date('d/m/y H:i', strtotime($row['tanggal'])) : '-' ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6 max-w-xs">
                                <p class="text-sm text-slate-600 leading-relaxed line-clamp-3 italic">
                                    "<?= nl2br(htmlspecialchars($row['pesan'])) ?>"
                                </p>
                            </td>
                            <td class="px-6 py-6 text-center">
                                <?php if(!empty($row['foto'])): ?>
                                    <img src="../uploads/<?= htmlspecialchars($row['foto']) ?>" class="w-12 h-12 rounded-xl object-cover shadow-sm mx-auto cursor-pointer img-preview ring-2 ring-white">
                                <?php else: ?>
                                    <div class="w-12 h-12 rounded-xl bg-slate-50 flex items-center justify-center text-slate-300 mx-auto border border-dashed border-slate-200">
                                        <i class="fas fa-image text-xs"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-6 text-right">
                                <a href="proses_tanggapan.php?id=<?= $row['id_pelaporan'] ?>" 
                                   class="inline-flex items-center gap-2 bg-slate-900 text-white hover:bg-blue-600 px-5 py-2.5 rounded-xl text-xs font-bold transition-all transform active:scale-95 shadow-lg shadow-slate-200">
                                    Tanggapi <i class="fas fa-chevron-right text-[10px]"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center text-slate-200 text-4xl border border-slate-100">
                                        <i class="fas fa-coffee"></i>
                                    </div>
                                    <h3 class="text-slate-400 font-bold">Semua Beres!</h3>
                                    <p class="text-xs text-slate-300">Tidak ada laporan baru yang menunggu di antrean.</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 flex items-center gap-6 px-4">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Menunggu Respon</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Dalam Proses</span>
            </div>
        </div>
    </main>

</body>
</html>