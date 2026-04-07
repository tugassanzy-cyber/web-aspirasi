<?php
session_start();
if (!isset($_SESSION['siswa'])) {
    header("Location: ../index.php");
    exit();
}

include '../koneksi.php';
$nis = $_SESSION['siswa'];

// Pesan notifikasi
$status_msg = "";
$status_type = "";

if (isset($_POST['ubah_password'])) {
    $pass_lama = $_POST['pass_lama'];
    $pass_baru = $_POST['pass_baru'];
    $konfirmasi = $_POST['konfirmasi'];

    // 1. Ambil password lama dari DB
    $stmt = mysqli_prepare($conn, "SELECT password FROM siswa WHERE nis = ?");
    mysqli_stmt_bind_param($stmt, "s", $nis);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($res);

    // 2. Validasi
    if (password_verify($pass_lama, $data['password'])) {
        if (strlen($pass_baru) < 6) {
            $status_msg = "Password baru minimal 6 karakter!";
            $status_type = "error";
        } elseif ($pass_baru === $konfirmasi) {
            // 3. Update Password
            $hash_baru = password_hash($pass_baru, PASSWORD_DEFAULT);
            $stmt_up = mysqli_prepare($conn, "UPDATE siswa SET password = ? WHERE nis = ?");
            mysqli_stmt_bind_param($stmt_up, "ss", $hash_baru, $nis);

            if (mysqli_stmt_execute($stmt_up)) {
                echo "<script>alert('Sukses! Password berhasil diperbarui.'); window.location='dashboard.php';</script>";
                exit();
            } else {
                $status_msg = "Gagal memperbarui database.";
                $status_type = "error";
            }
        } else {
            $status_msg = "Konfirmasi password tidak cocok!";
            $status_type = "error";
        }
    } else {
        $status_msg = "Password lama Anda salah!";
        $status_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Keamanan | Suara Siswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/siswa.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-md">
        <a href="dashboard.php" class="inline-flex items-center text-slate-400 hover:text-blue-600 font-bold text-xs uppercase tracking-widest mb-6 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Dashboard
        </a>

        <div class="card-box shadow-2xl border-none p-8 md:p-10">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl shadow-inner">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Perbarui Sandi</h2>
                <p class="text-sm text-slate-400 mt-1">Gunakan kombinasi yang sulit ditebak</p>
            </div>

            <?php if($status_msg): ?>
                <div class="mb-6 p-4 rounded-xl text-xs font-bold flex items-center gap-3 <?= $status_type == 'error' ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100' ?>">
                    <i class="fas <?= $status_type == 'error' ? 'fa-circle-xmark' : 'fa-circle-check' ?> text-lg"></i>
                    <?= $status_msg ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-5">
                <div class="form-group">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Password Saat Ini</label>
                    <div class="relative mt-1">
                        <input type="password" name="pass_lama" id="pass_lama" required 
                               class="form-control pr-12 !bg-slate-50 focus:!bg-white" placeholder="••••••••">
                        <button type="button" onclick="togglePass('pass_lama')" class="absolute right-4 top-3.5 text-slate-300 hover:text-slate-500">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>

                <div class="divider h-px bg-slate-100 w-full my-2"></div>

                <div class="form-group">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Password Baru</label>
                    <input type="password" name="pass_baru" id="pass_baru" required 
                           class="form-control mt-1 !bg-slate-50 focus:!bg-white" placeholder="Min. 6 karakter">
                </div>

                <div class="form-group">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Ulangi Password Baru</label>
                    <input type="password" name="konfirmasi" id="konfirmasi" required 
                           class="form-control mt-1 !bg-slate-50 focus:!bg-white" placeholder="Ketik ulang password">
                </div>

                <button type="submit" name="ubah_password" 
                        class="btn-submit w-full py-4 text-white shadow-xl shadow-blue-100 mt-4 flex items-center justify-center gap-2">
                    <i class="fas fa-save text-sm"></i> Simpan Perubahan
                </button>
            </form>
        </div>

        <p class="text-center text-slate-300 text-[10px] font-bold uppercase tracking-[0.2em] mt-10">
            &copy; 2026 E-Aspirasi Safety System
        </p>
    </div>

    <script>
        // Fungsi untuk melihat password
        function togglePass(id) {
            const input = document.getElementById(id);
            const icon = event.currentTarget.querySelector('i');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>

</body>
</html>