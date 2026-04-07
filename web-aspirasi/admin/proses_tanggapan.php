<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: ../index.php"); exit(); }
include '../koneksi.php';

$id_pelaporan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_pelaporan <= 0) { die("Akses Ilegal!"); }

// --- AMBIL DATA LAPORAN (Prepared Statement) ---
$stmt_lap = mysqli_prepare($conn, "SELECT ia.*, s.nama, k.ket_kategori 
                                   FROM input_aspirasi ia 
                                   JOIN siswa s ON ia.nis = s.nis 
                                   JOIN kategori k ON ia.id_kategori = k.id_kategori 
                                   WHERE id_pelaporan = ?");
mysqli_stmt_bind_param($stmt_lap, "i", $id_pelaporan);
mysqli_stmt_execute($stmt_lap);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_lap));

if (!$data) { die("Data Laporan Tidak Ditemukan!"); }

// --- AMBIL TANGGAPAN JIKA SUDAH ADA ---
$stmt_tang = mysqli_prepare($conn, "SELECT * FROM aspirasi WHERE id_pelaporan = ?");
mysqli_stmt_bind_param($stmt_tang, "i", $id_pelaporan);
mysqli_stmt_execute($stmt_tang);
$data_tanggapan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_tang));

// --- LOGIKA SIMPAN ---
if (isset($_POST['simpan'])) {
    $status = $_POST['status'];
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
    $id_kat = $data['id_kategori'];

    if ($data_tanggapan) {
        $q = "UPDATE aspirasi SET status='$status', feedback='$feedback' WHERE id_pelaporan='$id_pelaporan'";
    } else {
        $q = "INSERT INTO aspirasi (id_pelaporan, status, id_kategori, feedback) VALUES ('$id_pelaporan', '$status', '$id_kat', '$feedback')";
    }
    
    if(mysqli_query($conn, $q)) {
        echo "<script>alert('Berhasil Memperbarui Laporan!'); window.location='laporan_masuk.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Laporan #<?= $id_pelaporan ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen font-sans antialiased text-slate-900">

    <div class="max-w-6xl mx-auto p-4 md:p-8">
        
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">PROSES TANGGAPAN</h1>
                <p class="text-slate-500 text-sm font-medium">Tiket Pelaporan ID: #<?= $id_pelaporan ?></p>
            </div>
            <a href="laporan_masuk.php" class="bg-white border border-slate-200 px-5 py-2.5 rounded-2xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition flex items-center gap-2">
                <i class="fas fa-times text-xs"></i> Batal
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-5 space-y-6">
                
                <div class="bg-white rounded-[32px] p-8 shadow-sm border border-slate-200 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-6 opacity-10">
                        <i class="fas fa-quote-right text-6xl"></i>
                    </div>
                    
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-xl">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Identitas Pelapor</p>
                            <h3 class="font-bold text-slate-800 text-lg"><?= htmlspecialchars($data['nama']) ?></h3>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                            <p class="text-[10px] font-black text-blue-500 uppercase mb-1">Kategori & Lokasi</p>
                            <p class="text-sm font-bold text-slate-700 leading-relaxed italic">
                                <i class="fas fa-tag mr-1 text-slate-300"></i> <?= htmlspecialchars($data['ket_kategori']) ?> <br>
                                <i class="fas fa-map-marker-alt mr-1 text-slate-300"></i> <?= htmlspecialchars($data['lokasi']) ?>
                            </p>
                        </div>
                        
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-2">Pesan Aspirasi</p>
                            <div class="text-sm text-slate-600 leading-loose bg-white p-4 border rounded-2xl italic border-dashed border-slate-300">
                                "<?= nl2br(htmlspecialchars($data['pesan'])) ?>"
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-800 rounded-[32px] p-6 shadow-xl shadow-slate-200 overflow-hidden text-white">
                    <h4 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4 flex items-center gap-2">
                        <i class="fas fa-paperclip"></i> Lampiran Visual
                    </h4>
                    <?php if(!empty($data['foto'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($data['foto']) ?>" class="w-full h-48 object-cover rounded-2xl shadow-inner brightness-90 hover:brightness-100 transition">
                    <?php else: ?>
                        <div class="py-12 border-2 border-dashed border-slate-600 rounded-2xl text-center text-slate-500">
                            <i class="fas fa-eye-slash text-3xl mb-2"></i>
                            <p class="text-xs font-medium">Tidak ada foto dilampirkan</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <div class="lg:col-span-7">
                <form method="POST" class="bg-white rounded-[32px] p-8 md:p-10 shadow-sm border border-slate-200 min-h-full">
                    <div class="mb-8">
                        <h3 class="text-xl font-black text-slate-800">Form Tindakan</h3>
                        <p class="text-sm text-slate-400">Tentukan status akhir dan berikan alasan/balasan</p>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-3 ml-1">Update Status Laporan</label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <?php 
                                $curr_status = $data_tanggapan['status'] ?? 'Menunggu';
                                $options = [
                                    ['val' => 'Menunggu', 'icon' => 'fa-clock', 'color' => 'peer-checked:bg-amber-500', 'bg' => 'bg-amber-50'],
                                    ['val' => 'Proses', 'icon' => 'fa-spinner', 'color' => 'peer-checked:bg-blue-600', 'bg' => 'bg-blue-50'],
                                    ['val' => 'Selesai', 'icon' => 'fa-check-double', 'color' => 'peer-checked:bg-emerald-600', 'bg' => 'bg-emerald-50'],
                                ];
                                foreach($options as $opt):
                                ?>
                                <label class="cursor-pointer group">
                                    <input type="radio" name="status" value="<?= $opt['val'] ?>" class="hidden peer" <?= $curr_status == $opt['val'] ? 'checked' : '' ?>>
                                    <div class="p-4 rounded-2xl border-2 border-slate-100 text-center transition-all group-hover:border-slate-300 peer-checked:border-transparent <?= $opt['color'] ?> peer-checked:text-white <?= $opt['bg'] ?> peer-checked:shadow-lg">
                                        <i class="fas <?= $opt['icon'] ?> block text-lg mb-1"></i>
                                        <span class="text-[10px] font-black uppercase"><?= $opt['val'] ?></span>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-3 ml-1">Tanggapan Resmi (Feedback)</label>
                            <textarea name="feedback" rows="8" required
                                class="w-full px-6 py-5 bg-slate-50 border border-slate-200 rounded-3xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-50 outline-none transition-all text-sm leading-relaxed"
                                placeholder="Jelaskan tindakan yang telah diambil atau berikan jawaban kepada siswa..."><?= $data_tanggapan['feedback'] ?? '' ?></textarea>
                        </div>

                        <div class="pt-4">
                            <button type="submit" name="simpan" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black text-sm uppercase tracking-widest py-5 rounded-[24px] shadow-xl shadow-blue-200 transition-all transform active:scale-95 flex items-center justify-center gap-3">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>

</body>
</html>