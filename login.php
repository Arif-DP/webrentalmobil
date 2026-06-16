<?php
session_start();
include 'config/koneksi.php';

$error = "";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Username dan Password wajib diisi!";
    } else {
        // Cari user berdasarkan username
        $result = $conn->query("SELECT * FROM users WHERE username = '$username'");
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Verifikasi password yang di-hash
            if (password_verify($password, $row['password'])) {
                // Simpan data login ke dalam Session Server
                $_SESSION['login'] = true;
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];

                header("Location: pages/dashboard.php");
                exit;
            } else {
                $error = "Username atau Password salah!";
            }
        } else {
            $error = "Username atau Password salah!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arif Car Rental</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        html {
            font-size: 14px; /* Default browser adalah 16px. Mengubahnya ke 14px akan mengecilkan semua elemen Tailwind secara proporsional */
        }
    </style>
</head>
<body class="bg-slate-100 h-screen flex items-center justify-center font-sans">

    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-xl border border-gray-100">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-slate-800">Selamat Datang</h2>
            <p class="text-sm text-gray-500 mt-2">Di Sistem Manajemen Arif Car Rental</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 text-red-700 p-3 rounded-lg text-sm font-medium mb-4 border border-red-200 text-center"><?= $error; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">Username</label>
                <input type="text" name="username" placeholder="Masukkan username" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
            </div>
            
            <button type="submit" name="login" class="w-full py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-200">Sign In</button>
        </form>
    </div>

</body>
</html>