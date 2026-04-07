<?php
session_start();
include 'koneksi.php';

// Fungsi helper untuk mengembalikan response alert
function sendError($message) {
    echo "<script>alert('$message'); window.location='index.php';</script>";
    exit();
}

if (!isset($_POST['login'])) {
    header("Location: index.php");
    exit();
}

$role = $_POST['role'];

// --- LOGIKA LOGIN ADMIN ---
if ($role === 'admin') {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT username, password FROM admin WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($data = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $data['password'])) {
            // Pengamanan Sesi
            session_unset();
            session_regenerate_id(true);
            
            $_SESSION['admin'] = $data['username'];
            $_SESSION['role']  = 'admin';
            $_SESSION['last_login'] = time();

            header("Location: admin/dashboard.php");
            exit();
        }
        sendError("Password Admin tidak valid!");
    }
    sendError("Username Admin tidak ditemukan!");
}

// --- LOGIKA LOGIN SISWA ---
else if ($role === 'siswa') {
    $nis = mysqli_real_escape_string($conn, trim($_POST['nis']));
    $password = $_POST['password_siswa'];

    $stmt = mysqli_prepare($conn, "SELECT nis, nama, password FROM siswa WHERE nis = ?");
    mysqli_stmt_bind_param($stmt, "s", $nis);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($data = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $data['password'])) {
            // Pengamanan Sesi
            session_unset();
            session_regenerate_id(true);
            
            $_SESSION['siswa']      = $data['nis'];
            $_SESSION['nama_siswa'] = $data['nama'];
            $_SESSION['role']       = 'siswa';
            
            header("Location: siswa/dashboard.php");
            exit();
        }
        sendError("Password Siswa salah!");
    }
    sendError("Nomor Induk Siswa (NIS) tidak terdaftar!");
}

mysqli_close($conn);
?>