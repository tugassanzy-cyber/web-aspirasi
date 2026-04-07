<?php
session_start();
// Redirect otomatis jika sudah login
if (isset($_SESSION['admin'])) { header("Location: admin/dashboard.php"); exit(); }
if (isset($_SESSION['siswa'])) { header("Location: siswa/dashboard.php"); exit(); }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Aspirasi Siswa | Welcome</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Animasi masuk untuk elemen */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-up { animation: slideUp 0.6s ease out forwards; }
        
        /* Overlay khusus untuk background agar teks tetap terbaca */
        .bg-overlay {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.8) 0%, rgba(30, 58, 138, 0.7) 100%);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center relative overflow-hidden">

    <div class="fixed inset-0 z-[-1]">
        <img src="assets/img/latar_index.jpg" class="w-full h-full object-cover scale-110" alt="Background">
        <div class="fixed inset-0 bg-overlay"></div>
    </div>

    <main class="w-full max-w-5xl px-6 py-12 flex flex-col items-center z-10">
        
        <header class="text-center mb-12 animate-up">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-600 rounded-3xl shadow-2xl shadow-blue-500/50 mb-6 rotate-3">
                <i class="fas fa-paper-plane text-white text-3xl"></i>
            </div>
            <h1 class="text-4xl md:text-6xl font-black text-white tracking-tighter mb-4">
                SUARA <span class="text-blue-400">SISWA</span>
            </h1>
            <p class="text-slate-300 text-lg md:text-xl max-w-2xl mx-auto leading-relaxed">
                Platform digital untuk menyampaikan aspirasi dan pengaduan demi kemajuan kualitas pendidikan di sekolah kita.
            </p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full max-w-4xl animate-up" style="animation-delay: 0.2s;">
            
            <div class="group bg-white/10 backdrop-blur-xl border border-white/20 p-8 rounded-[2.5rem] hover:bg-white hover:border-white transition-all duration-500 cursor-pointer shadow-2xl shadow-black/20" 
                 onclick="openModal('modalSiswa')">
                <div class="flex justify-between items-start mb-12">
                    <div class="w-14 h-14 bg-blue-500 rounded-2xl flex items-center justify-center text-white text-2xl group-hover:scale-110 transition-transform">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <span class="text-white/30 group-hover:text-blue-600 transition-colors">
                        <i class="fas fa-arrow-right text-2xl"></i>
                    </span>
                </div>
                <h3 class="text-2xl font-bold text-white group-hover:text-slate-900 mb-2">Portal Siswa</h3>
                <p class="text-slate-400 group-hover:text-slate-500 text-sm leading-relaxed">
                    Sampaikan keluhan, saran sarana prasarana, atau aspirasi kegiatan kesiswaan di sini.
                </p>
            </div>

            <div class="group bg-white/10 backdrop-blur-xl border border-white/20 p-8 rounded-[2.5rem] hover:bg-slate-900 hover:border-slate-800 transition-all duration-500 cursor-pointer shadow-2xl shadow-black/20"
                 onclick="openModal('modalAdmin')">
                <div class="flex justify-between items-start mb-12">
                    <div class="w-14 h-14 bg-slate-700 rounded-2xl flex items-center justify-center text-white text-2xl group-hover:bg-blue-600 transition-all">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <span class="text-white/30 group-hover:text-white transition-colors">
                        <i class="fas fa-arrow-right text-2xl"></i>
                    </span>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2">Manajemen Admin</h3>
                <p class="text-slate-400 group-hover:text-slate-400 text-sm leading-relaxed">
                    Masuk sebagai petugas untuk menanggapi dan memproses setiap aspirasi masuk.
                </p>
            </div>

        </div>

        <footer class="mt-16 text-slate-500 text-xs font-bold uppercase tracking-[0.2em] animate-up" style="animation-delay: 0.4s;">
            &copy; 2026 E-Aspirasi Sekolah • Integrated System
        </footer>
    </main>

    <div id="modalSiswa" class="modal-overlay invisible opacity-0 fixed inset-0 z-[100] flex items-center justify-center p-4 transition-all duration-300" onclick="closeModalOutside(event, 'modalSiswa')">
        <div class="modal-box bg-white w-full max-w-sm rounded-[2rem] p-10 relative shadow-[0_0_100px_rgba(0,0,0,0.5)] transform scale-95 transition-transform duration-300">
            <button onclick="closeModal('modalSiswa')" class="absolute top-6 right-6 text-slate-300 hover:text-red-500 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
            <div class="text-center mb-8">
                <h2 class="text-2xl font-black text-slate-800">Login Siswa</h2>
                <p class="text-slate-400 text-sm font-medium">Gunakan NIS sebagai identitas</p>
            </div>
            <form action="proses_login.php" method="POST" class="space-y-4">
                <input type="hidden" name="role" value="siswa">
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nomor Induk Siswa</label>
                    <input type="text" name="nis" required placeholder="Contoh: 1001" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-50 outline-none transition-all">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Password</label>
                    <input type="password" name="password_siswa" required placeholder="Masukkan sandi" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-50 outline-none transition-all">
                </div>
                <button type="submit" name="login" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-blue-200 transition-all active:scale-95">
                    Masuk Portal <i class="fas fa-sign-in-alt ml-2"></i>
                </button>
            </form>
        </div>
    </div>

    <div id="modalAdmin" class="modal-overlay invisible opacity-0 fixed inset-0 z-[100] flex items-center justify-center p-4 transition-all duration-300" onclick="closeModalOutside(event, 'modalAdmin')">
        <div class="modal-box bg-white w-full max-w-sm rounded-[2rem] p-10 relative shadow-[0_0_100px_rgba(0,0,0,0.5)] transform scale-95 transition-transform duration-300">
            <button onclick="closeModal('modalAdmin')" class="absolute top-6 right-6 text-slate-300 hover:text-red-500 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
            <div class="text-center mb-8">
                <h2 class="text-2xl font-black text-slate-800">Administrator</h2>
                <p class="text-slate-400 text-sm font-medium">Otoritas Pengelola Sistem</p>
            </div>
            <form action="proses_login.php" method="POST" class="space-y-4">
                <input type="hidden" name="role" value="admin">
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Username Admin</label>
                    <input type="text" name="username" required placeholder="ID Petugas" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:border-slate-800 outline-none transition-all">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Password</label>
                    <input type="password" name="password" required placeholder="Kunci Akses" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:border-slate-800 outline-none transition-all">
                </div>
                <button type="submit" name="login" class="w-full bg-slate-900 hover:bg-black text-white font-bold py-4 rounded-2xl shadow-lg shadow-slate-200 transition-all active:scale-95">
                    Verifikasi Login <i class="fas fa-lock-open ml-2 text-xs"></i>
                </button>
            </form>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('invisible', 'opacity-0');
            modal.querySelector('.modal-box').classList.remove('scale-95');
            modal.querySelector('.modal-box').classList.add('scale-100');
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.add('opacity-0');
            modal.querySelector('.modal-box').classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('invisible');
            }, 300);
        }

        function closeModalOutside(event, modalId) {
            if (event.target.id === modalId) {
                closeModal(modalId);
            }
        }
    </script>
</body>
</html>