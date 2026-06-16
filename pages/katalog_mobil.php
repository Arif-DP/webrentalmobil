<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}
include '../config/koneksi.php';

// ==========================================
// 1. LOGIKA PENCARIAN & PAGINASI
// ==========================================
$limit = 6; // Menampilkan 6 mobil per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

$where_clauses = [];
if ($search != '') {
    $where_clauses[] = "(merk LIKE '%$search%' OR model LIKE '%$search%' OR plat_nomor LIKE '%$search%')";
}
if ($filter_status != '' && $filter_status != 'Semua') {
    $where_clauses[] = "status = '$filter_status'";
}

$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = " WHERE " . implode(' AND ', $where_clauses);
}

// Hitung total data
$sql_count = "SELECT COUNT(*) AS total FROM mobil" . $where_sql;
$result_count = $conn->query($sql_count);
$row_count = $result_count->fetch_assoc();
$total_data = $row_count['total'];
$total_pages = ceil($total_data / $limit);

// Ambil data sesuai halaman & filter
$sql_data = "SELECT * FROM mobil" . $where_sql . " ORDER BY status ASC, merk ASC LIMIT $limit OFFSET $offset";
$katalog_mobil = $conn->query($sql_data);
?>

<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f8fafc]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Mobil</title>
    <script src="../assets/js/tailwind.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        html { font-size: 14px; }

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
            <div class="p-5 flex items-center justify-between border-b border-slate-800/50 mb-4">
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

            <a href="katalog_mobil.php" class="relative flex items-center gap-3 px-3 py-2 bg-gradient-to-r from-slate-800/60 to-slate-800/20 text-white rounded-lg text-sm font-medium transition-all group whitespace-nowrap">
                <span class="absolute left-0 bottom-0 md:bottom-2 md:top-2 md:left-0 w-full md:w-1 h-0.5 md:h-auto bg-orange-500 rounded-t md:rounded-r"></span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-orange-500">
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

            <a href="data_pelanggan.php" class="flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-slate-100 hover:bg-slate-800/30 rounded-lg text-sm font-medium transition-all group whitespace-nowrap">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-slate-500 group-hover:text-slate-300 transition-colors">
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
            <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
                <span>Console</span><span>/</span><span class="text-slate-900 font-semibold">Katalog Mobil</span>
            </div>
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
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        Logout
                    </a>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 md:p-8 bg-[#F8FAFC]">
            <div class="w-full space-y-6">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Ketersediaan Armada</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Status dan tarif operasional kendaraan aktif saat ini.</p>
                </div>

                <form method="GET" action="" class="flex flex-col md:flex-row gap-4 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <div class="flex-1 relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </span>
                        <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Cari merk, model, atau plat..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white text-sm">
                    </div>

                    <div class="md:w-48 relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                            </svg>
                        </span>
                        
                        <select name="status" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm appearance-none cursor-pointer">
                            <option value="Semua" <?= $filter_status == 'Semua' ? 'selected' : ''; ?>>Semua Status</option>
                            <option value="Tersedia" <?= $filter_status == 'Tersedia' ? 'selected' : ''; ?>>Tersedia</option>
                            <option value="Disewa" <?= $filter_status == 'Disewa' ? 'selected' : ''; ?>>Disewa</option>
                            <option value="Maintenance" <?= $filter_status == 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                        </select>
                        
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </span>
                    </div>

                    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-5 py-2.5 rounded-lg font-semibold text-sm transition-all duration-200 flex items-center justify-center gap-2 active:scale-95 shadow-sm group">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-slate-300 group-hover:text-white transition-colors">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <span>Cari</span>
                    </button>
                </form>

                <?php if ($total_data == 0): ?>
                    <div class="text-center py-12 text-slate-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto mb-4 text-slate-300" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <circle cx="7" cy="17" r="2" />
                            <circle cx="17" cy="17" r="2" />
                            <path d="M5 17h-2v-6l2 -5h9l4 5h1a2 2 0 0 1 2 2v4h-2m-4 0h-6m-6 -6h15m-6 0v-5" />
                        </svg>
                        
                        <p class="font-medium text-slate-600">Maaf, tidak ada mobil yang sesuai dengan pencarian Anda.</p>
                        <p class="text-sm text-slate-400 mt-1">Coba gunakan kata kunci lain atau ubah filter status.</p>
                    </div>
                <?php else: ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 text-left">
                        <?php while($m = $katalog_mobil->fetch_assoc()): ?>
                            <div class="bg-white rounded-xl border border-slate-200/70 shadow-[0_2px_8px_rgba(0,0,0,0.01)] overflow-hidden hover:border-slate-300 transition-all group">
                                
                                <div class="h-44 w-full bg-slate-50 flex items-center justify-center overflow-hidden relative border-b border-slate-100">
                                    <?php if (!empty($m['foto'])): ?>
                                        <img src="../assets/img/mobil/<?= $m['foto']; ?>" alt="<?= $m['merk']; ?>" class="w-full h-full object-cover group-hover:scale-102 transition duration-500">
                                    <?php else: ?>
                                        <div class="flex flex-col items-center gap-1.5 text-slate-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                            </svg>
                                            <span class="text-[11px] font-medium">Belum ada foto</span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php 
                                        $badge_class = "bg-emerald-600 text-white border-emerald-700"; 
                                        if ($m['status'] == 'Disewa') $badge_class = "bg-orange-500 text-white border-orange-600";
                                        if ($m['status'] == 'Maintenance') $badge_class = "bg-rose-600 text-white border-rose-700";
                                    ?>
                                    <span class="absolute top-3 right-3 text-[10px] font-bold px-2.5 py-1 rounded-md border shadow-sm <?= $badge_class; ?>">
                                        ● <?= $m['status']; ?>
                                    </span>
                                </div>

                                <div class="p-5">
                                    <h3 class="text-lg font-bold text-slate-900 mb-1">
                                        <?= $m['merk']; ?> <?= $m['model']; ?>
                                    </h3>
                                    <p class="text-sm text-slate-500 mb-4">
                                        <?= $m['tahun']; ?> &bull; <?= $m['plat_nomor']; ?>
                                    </p>

                                    <div class="mb-5">
                                        <span class="text-2xl font-bold text-orange-500 tracking-tight">
                                            Rp <?= number_format($m['harga_per_hari'], 0, ',', '.'); ?>
                                        </span>
                                        <span class="text-sm text-slate-500">/hari</span>
                                    </div>

                                    <div>
                                        <?php if ($m['status'] == 'Tersedia'): ?>
                                            <a href="transaksi_rental.php?plat_nomor=<?= $m['plat_nomor']; ?>" class="block w-full bg-orange-500 hover:bg-orange-600 text-white text-center text-sm font-bold py-2.5 rounded-lg transition-colors shadow-sm">
                                                Sewa Sekarang
                                            </a>
                                        <?php else: ?>
                                            <button disabled class="block w-full bg-slate-100 text-slate-400 text-center text-sm font-bold py-2.5 rounded-lg cursor-not-allowed">
                                                <?= $m['status']; ?>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <?php if ($total_pages > 1): ?>
                        <div class="mt-8 flex justify-center items-center gap-2">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1; ?>&search=<?= urlencode($search); ?>&status=<?= urlencode($filter_status); ?>" class="px-4 py-2 bg-white border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50 font-medium text-sm transition">
                                    &laquo; Prev
                                </a>
                            <?php endif; ?>

                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?= $i; ?>&search=<?= urlencode($search); ?>&status=<?= urlencode($filter_status); ?>" class="w-10 h-10 flex items-center justify-center rounded-lg border font-medium text-sm transition <?= $i == $page ? 'bg-orange-500 text-white border-orange-500' : 'bg-white border-slate-300 text-slate-600 hover:bg-slate-50'; ?>">
                                    <?= $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?= $page + 1; ?>&search=<?= urlencode($search); ?>&status=<?= urlencode($filter_status); ?>" class="px-4 py-2 bg-white border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50 font-medium text-sm transition">
                                    Next &raquo;
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                <?php endif; ?> </div>
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