<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}
include '../config/koneksi.php';

// Atur zona waktu ke Indonesia agar perhitungan hari akurat
date_default_timezone_set('Asia/Jakarta');

// Simpan Transaksi Baru
if (isset($_POST['simpan_transaksi'])) {
    $pelanggan_id = $_POST['pelanggan_id'];
    $plat_nomor = $_POST['plat_nomor'];
    $tanggal_sewa = $_POST['tanggal_sewa'];
    $durasi_hari = $_POST['durasi_hari'];
    $total_harga = $_POST['total_harga'];

    $sql_transaksi = "INSERT INTO transaksi (pelanggan_id, plat_nomor, tanggal_sewa, durasi_hari, total_harga, status_transaksi) VALUES ('$pelanggan_id', '$plat_nomor', '$tanggal_sewa', '$durasi_hari', '$total_harga', 'Berjalan')";
    if ($conn->query($sql_transaksi)) {
        $conn->query("UPDATE mobil SET status = 'Disewa' WHERE plat_nomor = '$plat_nomor'");
        header("Location: transaksi_rental.php"); exit;
    }
}

// Pengembalian Mobil
if (isset($_GET['kembali'])) {
    $transaksi_id = $_GET['kembali'];
    $plat_mobil = $_GET['plat'];
    $conn->query("UPDATE transaksi SET status_transaksi = 'Selesai' WHERE id = '$transaksi_id'");
    $conn->query("UPDATE mobil SET status = 'Tersedia' WHERE plat_nomor = '$plat_mobil'");
    header("Location: transaksi_rental.php"); exit;
}

$list_pelanggan = $conn->query("SELECT * FROM pelanggan ORDER BY nama ASC");
$list_mobil = $conn->query("SELECT * FROM mobil WHERE status = 'Tersedia' ORDER BY merk ASC");
$tabel_transaksi = $conn->query("SELECT t.*, p.nama AS nama_pelanggan, m.merk, m.model FROM transaksi t JOIN pelanggan p ON t.pelanggan_id = p.id JOIN mobil m ON t.plat_nomor = m.plat_nomor ORDER BY t.status_transaksi ASC, t.id DESC");

$plat_nomor_dipilih = isset($_GET['plat_nomor']) ? $_GET['plat_nomor'] : '';
?>

<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f8fafc]">
<head>
    <meta charset="UTF-8">
    <title>Transaksi Rental</title>
    <script src="../assets/js/tailwind.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        html {
            font-size: 14px;
        }
        
        /* CSS tambahan untuk menyembunyikan elemen saat mode print (Cetak Struk) */
        @media print {
            body * { visibility: hidden; }
            .cetak-area, .cetak-area * { visibility: visible; }
            .cetak-area { position: absolute; left: 0; top: 0; width: 100%; }
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

            <a href="data_pelanggan.php" class="flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-slate-100 hover:bg-slate-800/30 rounded-lg text-sm font-medium transition-all group whitespace-nowrap">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-slate-500 group-hover:text-slate-300 transition-colors">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <span>Data Pelanggan</span>
            </a>

            <a href="transaksi_rental.php" class="relative flex items-center gap-3 px-3 py-2 bg-gradient-to-r from-slate-800/60 to-slate-800/20 text-white rounded-lg text-sm font-medium transition-all group whitespace-nowrap">
                <span class="absolute left-0 bottom-0 md:bottom-2 md:top-2 md:left-0 w-full md:w-1 h-0.5 md:h-auto bg-orange-500 rounded-t md:rounded-r"></span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-orange-500">
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
            <div class="flex items-center gap-2 text-xs font-medium text-slate-400"><span>Console</span><span>/</span><span class="text-slate-900 font-semibold">Transaksi Rental</span></div>
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
        </header>

        <main class="flex-1 overflow-y-auto p-4 md:p-8 bg-[#F8FAFC]">
            <div class="w-full space-y-6">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Checkout Transaksi</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Sistem pencatatan keluar-masuk kendaraan operasional.</p>
                </div>

                <div class="bg-white p-6 rounded-xl border border-slate-200/70 shadow-[0_2px_8px_rgba(0,0,0,0.01)]">
                    <h3 class="text-sm font-bold mb-4 text-slate-800 uppercase tracking-wide flex items-center gap-2">
                        <span class="w-1.5 h-3 bg-orange-500 rounded-sm"></span>Buka Sesi Sewa Baru
                    </h3>
                    <form action="transaksi_rental.php" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div class="space-y-1.5">
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pemohon (Pelanggan)</label>
                            <select name="pelanggan_id" required class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-slate-400 bg-white">
                                <option value="">-- Pilih Penyewa --</option>
                                <?php while($p = $list_pelanggan->fetch_assoc()): ?><option value="<?= $p['id']; ?>"><?= $p['nama']; ?></option><?php endwhile; ?>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pilih Armada (Tersedia)</label>
                            <select id="select_mobil" name="plat_nomor" required onchange="hitungOtomatisTotal()" class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-slate-400 bg-white">
                                <option value="" data-harga="0">-- Pilih Kendaraan --</option>
                                
                                <?php while($m = $list_mobil->fetch_assoc()): ?>
                                    <?php 
                                        // Cek apakah plat nomor di baris ini sama dengan yang ada di URL (?plat_nomor=...)
                                        $terpilih = (isset($_GET['plat_nomor']) && $m['plat_nomor'] == $_GET['plat_nomor']) ? 'selected' : ''; 
                                    ?>
                                    <option value="<?= $m['plat_nomor']; ?>" data-harga="<?= $m['harga_per_hari']; ?>" <?= $terpilih; ?>>
                                        <?= $m['plat_nomor']; ?> - <?= $m['merk']; ?> <?= $m['model']; ?> (Rp <?= number_format($m['harga_per_hari'], 0, ',', '.'); ?>)
                                    </option>
                                <?php endwhile; ?>

                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tgl Pinjam & Durasi</label>
                            <div class="flex gap-2">
                                <input type="date" name="tanggal_sewa" required value="<?= date('Y-m-d'); ?>" class="w-2/3 px-3.5 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-slate-400">
                                <input type="number" id="durasi_hari" name="durasi_hari" placeholder="Hari" required oninput="hitungOtomatisTotal()" class="w-1/3 px-3.5 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-slate-400">
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Estimasi Biaya</label>
                            <div class="flex gap-2">
                                <input type="hidden" id="total_harga_raw" name="total_harga" value="0">
                                <input type="text" id="total_harga_view" readonly placeholder="Rp 0" class="flex-1 px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg font-bold text-orange-600 focus:outline-none text-sm text-right">
                                <button type="submit" name="simpan_transaksi" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-sm font-semibold transition">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-xl border border-slate-200/70 shadow-[0_2px_8px_rgba(0,0,0,0.01)] overflow-x-auto scrollbar-none">
                    <table class="w-full text-left border-collapse min-w-[600px]">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Informasi Peminjam</th>
                                <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Armada Rental</th>
                                <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Durasi & Waktu</th>
                                <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tagihan Total</th>
                                <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status Sewa</th>
                                <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <?php if ($tabel_transaksi->num_rows > 0): ?>
                                <?php while($row = $tabel_transaksi->fetch_assoc()): ?>
                                    
                                    <?php 
                                        // ---- LOGIKA PERHITUNGAN TANGGAL KEMBALI DAN OVERDUE ----
                                        $tgl_pinjam = strtotime($row['tanggal_sewa']);
                                        $durasi = $row['durasi_hari'];
                                        // Tambahkan durasi ke tanggal pinjam
                                        $tgl_kembali = strtotime("+$durasi days", $tgl_pinjam);
                                        
                                        // Cek Overdue (Apakah hari ini lebih besar dari tanggal kembali dan status masih Berjalan)
                                        $tgl_sekarang = strtotime(date('Y-m-d'));
                                        $is_overdue = ($tgl_sekarang > $tgl_kembali) && ($row['status_transaksi'] == 'Berjalan');
                                    ?>

                                    <tr class="hover:bg-slate-50/50 transition cetak-area">
                                        <td class="p-4 font-bold text-slate-900"><?= $row['nama_pelanggan']; ?></td>
                                        <td class="p-4">
                                            <div class="font-medium text-slate-800"><?= $row['merk']; ?></div>
                                            <div class="text-[10px] font-mono text-slate-400 font-bold tracking-wider"><?= $row['plat_nomor']; ?></div>
                                        </td>
                                        <td class="p-4">
                                            <div class="font-medium text-slate-800"><?= $row['durasi_hari']; ?> Hari</div>
                                            <div class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                                                <div class="flex items-center gap-1"><span class="text-slate-400 w-12 block">Pinjam:</span> <span class="font-medium"><?= date('d M Y', $tgl_pinjam); ?></span></div>
                                                <div class="flex items-center gap-1"><span class="text-slate-400 w-12 block">Kembali:</span> <span class="font-medium <?php echo $is_overdue ? 'text-red-500 font-bold' : ''; ?>"><?= date('d M Y', $tgl_kembali); ?></span></div>
                                            </div>
                                        </td>
                                        <td class="p-4 font-black text-slate-800 tracking-tight">Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?></td>
                                        <td class="p-4">
                                            <?php if ($row['status_transaksi'] == 'Berjalan'): ?>
                                                <?php if ($is_overdue): ?>
                                                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold border bg-red-500/10 text-red-600 border-red-500/20">Overdue</span>
                                                <?php else: ?>
                                                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold border bg-orange-500/10 text-orange-600 border-orange-500/20">Active Run</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold border bg-emerald-500/10 text-emerald-600 border-emerald-500/20">Returned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4">
                                            <div class="flex items-center justify-center gap-2">
                                                <?php if ($row['status_transaksi'] == 'Berjalan'): ?>
                                                    <a href="?kembali=<?= $row['id']; ?>&plat=<?= $row['plat_nomor']; ?>" class="px-3 py-1.5 border border-slate-300 hover:border-slate-800 hover:bg-slate-900 hover:text-white rounded-md text-[11px] font-bold text-slate-700 transition" title="Selesaikan Transaksi">Garasi Masuk</a>
                                                <?php else: ?>
                                                    <span class="px-3 py-1.5 bg-slate-100 rounded-md text-[11px] font-bold text-slate-400 cursor-not-allowed">Selesai</span>
                                                <?php endif; ?>
                                                
                                                <button onclick="window.print()" class="p-1.5 border border-transparent text-slate-400 hover:text-blue-600 hover:bg-blue-50 hover:border-blue-200 rounded-md transition" title="Cetak Transaksi">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-slate-400 italic text-xs">Belum ada aktivitas transaksi yang tercatat di sistem.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        function hitungOtomatisTotal() {
            const selectMobil = document.getElementById('select_mobil');
            const inputDurasi = document.getElementById('durasi_hari');
            const viewTotal = document.getElementById('total_harga_view');
            const rawTotal = document.getElementById('total_harga_raw');

            const hargaPerHari = parseInt(selectMobil.options[selectMobil.selectedIndex].getAttribute('data-harga')) || 0;
            const durasi = parseInt(inputDurasi.value) || 0;
            const total = hargaPerHari * durasi;

            rawTotal.value = total;
            viewTotal.value = "Rp " + total.toLocaleString('id-ID');
        }

        // Fungsi untuk memunculkan/menyembunyikan menu
        function toggleDropdown() {
            const menu = document.getElementById('dropdownMenu');
            menu.classList.toggle('hidden');
        }

        // Fungsi canggih agar menu otomatis menutup jika kita klik sembarang di luar kotak menu
        window.addEventListener('click', function(e) {
            const dropdown = document.getElementById('profileDropdown');
            if (!dropdown.contains(e.target)) {
                document.getElementById('dropdownMenu').classList.add('hidden');
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
            const selectMobil = document.getElementById("select_mobil");
            
            // Jika dropdown tidak kosong (berarti ada mobil yang otomatis ter-select dari URL)
            if (selectMobil.value !== "") {
                // Langsung jalankan fungsi perhitungan harganya
                hitungOtomatisTotal();
            }
        });
    </script>
</body>
</html>