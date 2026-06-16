<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
include '../config/koneksi.php';

// Proses Tambah / Edit Data Pelanggan
if (isset($_POST['simpan'])) {
    $id = $_POST['id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $telepon = mysqli_real_escape_string($conn, $_POST['telepon']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']); // <-- Tambahan Ambil Data Alamat

    if ($_POST['mode'] == 'edit') {
        $sql = "UPDATE pelanggan SET nama='$nama', email='$email', telepon='$telepon', alamat='$alamat' WHERE id='$id'"; // <-- Tambahan Update Alamat
    } else {
        $sql = "INSERT INTO pelanggan (nama, email, telepon, alamat) VALUES ('$nama', '$email', '$telepon', '$alamat')"; // <-- Tambahan Insert Alamat
    }
    if ($conn->query($sql)) { header("Location: data_pelanggan.php"); exit; }
}

// Proses Hapus Data Pelanggan
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $conn->query("DELETE FROM pelanggan WHERE id='$id'");
    header("Location: data_pelanggan.php"); exit;
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $edit_data = $conn->query("SELECT * FROM pelanggan WHERE id='$id_edit'")->fetch_assoc();
}

// =========================================================================
// LOGIKA FITUR PENCARIAN PELANGGAN (AMAN DAN ANTI-ERROR)
// =========================================================================
$keyword = "";
if (isset($_GET['search'])) {
    $keyword = mysqli_real_escape_string($conn, $_GET['keyword']);
    // Mencari data berdasarkan nama, alamat, atau nomor telepon
    $tabel_pelanggan = $conn->query("SELECT * FROM pelanggan WHERE 
                                    nama LIKE '%$keyword%' OR 
                                    alamat LIKE '%$keyword%' OR 
                                    telepon LIKE '%$keyword%' 
                                    ORDER BY nama ASC");
} else {
    // Jika tidak ada pencarian, tampilkan seperti semula
    $tabel_pelanggan = $conn->query("SELECT * FROM pelanggan ORDER BY nama ASC");
}
?>

<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f8fafc]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pelanggan</title>
    <script src="../assets/js/tailwind.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        html {
            font-size: 14px;
        }

        .scrollbar-none::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-none {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="h-screen text-slate-900 antialiased flex flex-col md:flex-row overflow-hidden bg-[#f8fafc]">

    <aside class="w-full md:w-64 bg-[#0B0F19] border-b md:border-b-0 md:border-r border-slate-800/40 flex flex-col z-20 flex-shrink-0">
        <div>
            <div class="p-4 md:p-5 flex items-center justify-between border-b border-slate-800/50 mb-2 md:mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-orange-500/10 border border-orange-500/20 flex items-center justify-center text-orange-500 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/><path d="M5 10h14"/></svg>
                    </div>
                    <div class="flex flex-col">
                        <h2 class="text-sm font-bold text-white tracking-wide leading-tight">Arif Car Rental</h2>
                        <span class="text-[10px] text-slate-500 font-medium tracking-wider uppercase">Panel Pemilik</span>
                    </div>
                </div>
            </div>

            <nav class="px-3 flex flex-row md:flex-col overflow-x-auto md:overflow-visible gap-1.5 pb-3 md:pb-0 scrollbar-none">
                <a href="dashboard.php" class="flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-slate-100 hover:bg-slate-800/30 rounded-lg text-sm font-medium transition-all group whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-slate-500 group-hover:text-slate-300 transition-colors">
                        <rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="katalog_mobil.php" class="flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-slate-100 hover:bg-slate-800/30 rounded-lg text-sm font-medium transition-all group whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-slate-500 group-hover:text-slate-300 transition-colors">
                        <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/><path d="M5 10h14"/>
                    </svg>
                    <span>Katalog Mobil</span>
                </a>

                <a href="data_mobil.php" class="flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-slate-100 hover:bg-slate-800/30 rounded-lg text-sm font-medium transition-all group whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-slate-500 group-hover:text-slate-300 transition-colors">
                        <ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5V19A9 3 0 0 0 21 19V5"/><path d="M3 12A9 3 0 0 0 21 12"/>
                    </svg>
                    <span>Data Mobil</span>
                </a>

                <a href="data_pelanggan.php" class="relative flex items-center gap-3 px-3 py-2 bg-gradient-to-r from-slate-800/60 to-slate-800/20 text-white rounded-lg text-sm font-medium transition-all group whitespace-nowrap">
                    <span class="absolute left-0 bottom-0 md:bottom-2 md:top-2 md:left-0 w-full md:w-1 h-0.5 md:h-auto bg-orange-500 rounded-t md:rounded-r"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-orange-500">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span>Data Pelanggan</span>
                </a>

                <a href="transaksi_rental.php" class="flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-slate-100 hover:bg-slate-800/30 rounded-lg text-sm font-medium transition-all group whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-slate-500 group-hover:text-slate-300 transition-colors">
                        <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/>
                    </svg>
                    <span>Transaksi Rental</span>
                </a>

                <a href="laporan.php" class="flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-slate-100 hover:bg-slate-800/30 rounded-lg text-sm font-medium transition-all group whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-slate-500 group-hover:text-slate-300 transition-colors">
                        <path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/>
                    </svg>
                    <span>Laporan</span>
                </a>
            </nav>
        </div>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 md:px-8 py-3 flex justify-between items-center z-10">
            <div class="flex items-center gap-2 text-xs font-medium text-slate-400"><span>Console</span><span>/</span><span class="text-slate-900 font-semibold">Data Pelanggan</span></div>
            <div class="relative ml-auto" id="profileDropdown">
                <button onclick="toggleDropdown()" class="flex items-center gap-3 hover:bg-slate-100 p-1.5 pr-3 rounded-full transition-all focus:outline-none">
                    <img src="https://ui-avatars.com/api/?name=Arif&background=0ea5e9&color=fff" alt="Avatar Admin" class="w-9 h-9 rounded-full shadow-sm border-2 border-white">
                    <div class="hidden md:block text-left">
                        <p class="text-sm font-bold text-slate-800 leading-none">Admin Rental</p>
                        <p class="text-[11px] font-medium text-slate-500 mt-0.5">Administrator</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-slate-400 ml-1">
                        <path d="m6 9 6 6 6-6"/>
                    </svg>
                </button>

                <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-[0_5px_15px_rgba(0,0,0,0.05)] border border-slate-100 py-1.5 z-50">
                    <a href="../logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar?')" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-red-500 hover:text-red-600 hover:bg-red-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        Logout
                    </a>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 md:p-8 bg-[#F8FAFC]">
            <div class="w-full space-y-6">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Direktori Pelanggan</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Kelola data identitas pengguna dan kontak penyewa.</p>
                </div>

                <div class="bg-white p-6 rounded-xl border border-slate-200/70 shadow-[0_2px_8px_rgba(0,0,0,0.01)]">
                    <h3 class="text-sm font-bold mb-4 text-slate-800 uppercase tracking-wide flex items-center gap-2">
                        <span class="w-1.5 h-3 bg-orange-500 rounded-sm"></span>
                        <?= $edit_data ? 'Edit Informasi Pelanggan' : 'Registrasi Pelanggan Baru'; ?>
                    </h3>
                    
                    <form action="data_pelanggan.php" method="POST" class="flex flex-col lg:flex-row gap-4 items-start lg:items-end">
                        <input type="hidden" name="mode" value="<?= $edit_data ? 'edit' : 'tambah'; ?>">
                        <input type="hidden" name="id" value="<?= $edit_data['id'] ?? ''; ?>">
                        
                        <div class="flex-1 w-full">
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                            <input type="text" name="nama" required placeholder="Contoh: Budi Santoso" class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-slate-400 transition" value="<?= $edit_data['nama'] ?? ''; ?>">
                        </div>

                        <div class="flex-1 w-full">
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Alamat Domisili</label>
                            <input type="text" name="alamat" required placeholder="Contoh: Jl. Mangga No. 12" class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-slate-400 transition" value="<?= $edit_data['alamat'] ?? ''; ?>">
                        </div>

                        <div class="flex-1 w-full">
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Alamat Email (Opsional)</label>
                            <input type="email" name="email" placeholder="contoh@mail.com" class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-slate-400 transition" value="<?= $edit_data['email'] ?? ''; ?>">
                        </div>

                        <div class="flex-1 w-full">
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">No. Telepon / WhatsApp</label>
                            <input type="text" name="telepon" required placeholder="0812xxxxxx" class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-slate-400 transition" value="<?= $edit_data['telepon'] ?? ''; ?>">
                        </div>
                        
                        <button type="submit" name="simpan" class="px-5 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 text-xs font-semibold shadow-sm transition w-full lg:w-auto h-9">
                            <?= $edit_data ? 'Simpan' : 'Tambah Baru'; ?>
                        </button>
                    </form>
                </div>

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-4 rounded-xl border border-slate-200/70 shadow-[0_2px_8px_rgba(0,0,0,0.01)]">
                    <span class="text-sm font-bold text-slate-800 uppercase tracking-wide flex items-center gap-2">
                        <span class="w-1.5 h-3 bg-orange-500 rounded-sm"></span>
                        Daftar Kontak Penyewa
                    </span>
                    <form action="data_pelanggan.php" method="GET" class="w-full sm:w-auto flex items-center gap-2">
                        <input type="text" name="keyword" placeholder="Cari nama, alamat, atau telp..." class="w-full sm:w-64 px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-slate-400 transition" value="<?= htmlspecialchars($keyword); ?>">
                        <button type="submit" name="search" class="px-4 py-1.5 bg-slate-900 text-white text-xs font-semibold rounded-lg hover:bg-slate-800 transition">
                            Cari
                        </button>
                        <?php if (!empty($keyword)): ?>
                            <a href="data_pelanggan.php" class="px-3 py-1.5 bg-slate-100 text-slate-600 text-xs font-semibold rounded-lg hover:bg-slate-200 transition">
                                Reset
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="bg-white rounded-xl border border-slate-200/70 shadow-[0_2px_8px_rgba(0,0,0,0.01)] overflow-x-auto scrollbar-none">
                    <table class="w-full text-left border-collapse min-w-[600px]">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider w-12 text-center">No</th>
                                <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Identitas Pelanggan</th>
                                <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alamat Domisili</th> <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kontak Email</th>
                                <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">No. Telepon</th>
                                <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <?php if ($tabel_pelanggan->num_rows > 0): ?>
                                <?php $no=1; while($row = $tabel_pelanggan->fetch_assoc()): ?>
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="p-4 text-center text-slate-400 font-medium"><?= $no++; ?></td>
                                        <td class="p-4 font-bold text-slate-900"><?= $row['nama']; ?></td>
                                        
                                        <td class="p-4 text-slate-600 max-w-xs truncate">
                                            <?= !empty($row['alamat']) ? $row['alamat'] : '<span class="text-slate-300 italic">Tidak ada alamat</span>'; ?>
                                        </td>

                                        <td class="p-4 text-slate-500"><?= !empty($row['email']) ? $row['email'] : '<span class="text-slate-300 italic">Tidak ada email</span>'; ?></td>
                                        <td class="p-4 font-mono font-medium text-slate-600"><?= $row['telepon']; ?></td>
                                        <td class="p-4 text-center space-x-3 text-xs font-semibold">
                                            <a href="data_pelanggan.php?edit=<?= $row['id']; ?>" class="text-slate-600 hover:text-slate-900 transition">Edit</a>
                                            <a href="data_pelanggan.php?hapus=<?= $row['id']; ?>" onclick="return confirm('Hapus pelanggan ini?')" class="text-rose-500 hover:text-rose-700 transition">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-slate-400 italic text-xs">Data pelanggan tidak ditemukan atau belum terdaftar.</td> </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleDropdown() {
            const menu = document.getElementById('dropdownMenu');
            menu.classList.toggle('hidden');
        }

        window.addEventListener('click', function(e) {
            const dropdown = document.getElementById('profileDropdown');
            if (!dropdown.contains(e.target)) {
                document.getElementById('dropdownMenu').classList.add('hidden');
            }
        });
    </script>
</body>
</html>