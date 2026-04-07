<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../index.php");
    exit();
}

include '../koneksi.php'; 

if (isset($_POST['tambah'])) {
    $nis = trim($_POST['nis']);
    $nama = trim($_POST['nama']);
    $password = $_POST['password'];

    $cek_query = "SELECT nis FROM siswa WHERE nis = ?";
    $stmt_cek = mysqli_prepare($conn, $cek_query);
    mysqli_stmt_bind_param($stmt_cek, "s", $nis);
    mysqli_stmt_execute($stmt_cek);
    $result_cek = mysqli_stmt_get_result($stmt_cek);

    if (mysqli_num_rows($result_cek) > 0) {
        echo "<script>alert('Gagal! NIS tersebut sudah terdaftar.');</script>";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO siswa (nis, nama, password) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sss", $nis, $nama, $password_hash);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Siswa berhasil ditambahkan!'); window.location='dashboard.php';</script>";
        } else {
            echo "<script>alert('Gagal menambahkan data.');</script>";
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_stmt_close($stmt_cek);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Siswa - Admin</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Menggunakan background abstract dari main.css */
        body { padding: 0; margin: 0; }
        
        /* Tambahan sedikit style khusus untuk link kembali */
        .back-link-modern {
            display: inline-block;
            margin-top: 20px;
            color: #64748b;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: color 0.2s ease;
        }
        .back-link-modern:hover {
            color: #2563eb;
        }
    </style>
</head>
<body>

<div style="display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px;">
    
    <div class="glass-box" style="width: 100%; max-width: 450px; padding: 40px 35px;">
        <div style="text-align: center; margin-bottom: 25px;">
            <i class="fas fa-user-plus" style="font-size: 3rem; color: #2563eb; margin-bottom: 15px;"></i>
            <h2 style="margin: 0; font-size: 1.6rem; color: #1e293b; font-weight: 800;">Tambah Akun Siswa</h2>
            <p style="color: #64748b; margin-top: 5px; font-size: 0.9rem;">Buat akses portal untuk siswa baru</p>
        </div>
        
        <form action="" method="POST">
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 8px; color: #1e293b;">NIS (Nomor Induk Siswa)</label>
                <input type="text" name="nis" class="form-control" placeholder="Masukkan NIS..." required style="width: 100%; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 8px; color: #1e293b;">Nama Lengkap Siswa</label>
                <input type="text" name="nama" class="form-control" placeholder="Masukkan Nama Lengkap..." required style="width: 100%; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 8px; color: #1e293b;">Password Default</label>
                <input type="password" name="password" class="form-control" placeholder="Buat Password..." required style="width: 100%; box-sizing: border-box;">
            </div>

            <button type="submit" name="tambah" class="btn" style="background:#2563eb; color:white; width: 100%; padding: 14px; font-size: 1rem; border-radius: 8px; justify-content: center;"><i class="fas fa-save"></i> Simpan Data Siswa</button>
        </form>

        <div style="text-align: center;">
            <a href="dashboard.php" class="back-link-modern"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
        </div>
    </div>

</div>

</body>
</html>