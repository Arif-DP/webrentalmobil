<?php
// ====== 1. TEMPELKAN KODE SCRIPT BARU ANDA DI SINI ======
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}
include '../config/koneksi.php';

$q_mobil = $conn->query("SELECT COUNT(*) as total FROM mobil");
$total_mobil = $q_mobil->fetch_assoc()['total'] ?? 0;

$q_pelanggan = $conn->query("SELECT COUNT(*) as total FROM pelanggan");
$total_pelanggan = $q_pelanggan->fetch_assoc()['total'] ?? 0;

$q_disewa = $conn->query("SELECT COUNT(*) as total FROM mobil WHERE status = 'Disewa'");
$total_disewa = $q_disewa->fetch_assoc()['total'] ?? 0;

$q_pendapatan = $conn->query("SELECT SUM(total_harga) as total FROM transaksi");
$total_pendapatan = $q_pendapatan->fetch_assoc()['total'] ?? 0;


// =========================================================================
// SCRIPT TAMBAHAN: DATA GRAFIK & AKTIVITAS SECARA AMAN (ANTI-ERROR SESUAI ERD)
// =========================================================================
$pendapatan_bulanan = array_fill(1, 12, 0);
$kolom_tanggal = 'id'; 
$recent_transaksi = [];
$pengembalian_hari_ini = [];

// Periksa kolom tabel transaksi terlebih dahulu agar tidak memicu error SQL jika nama kolom berbeda
$check_cols = $conn->query("SHOW COLUMNS FROM transaksi");
if($check_cols) {
    $cols = [];
    while($c = $check_cols->fetch_assoc()) { $cols[] = $c['Field']; }
    
    // Cari kolom tanggal yang tersedia
    if(in_array('tgl_transaksi', $cols)) $kolom_tanggal = 'tgl_transaksi';
    elseif(in_array('tgl_pinjam', $cols)) $kolom_tanggal = 'tgl_pinjam';
    elseif(in_array('tanggal', $cols)) $kolom_tanggal = 'tanggal';
    elseif(in_array('tanggal_sewa', $cols)) $kolom_tanggal = 'tanggal_sewa'; // Tambahan otomatis membaca kolom ERD Anda
    elseif(in_array('created_at', $cols)) $kolom_tanggal = 'created_at';

    // 1. Ambil data pendapatan per bulan untuk Line Chart
    if($kolom_tanggal !== 'id') {
        $q_chart = $conn->query("SELECT MONTH($kolom_tanggal) as bulan, SUM(total_harga) as total FROM transaksi WHERE YEAR($kolom_tanggal) = YEAR(CURDATE()) GROUP BY MONTH($kolom_tanggal)");
        if ($q_chart) {
            while($row = $q_chart->fetch_assoc()) {
                if(!empty($row['bulan'])) {
                    $pendapatan_bulanan[(int)$row['bulan']] = (int)$row['total'];
                }
            }
        }
    }

    // 2. Ambil data Transaksi Terbaru (Limit 5) - Query Disesuaikan Berdasarkan ERD Anda
    $q_recent = $conn->query("SELECT * FROM transaksi ORDER BY id DESC LIMIT 5");
    if($q_recent) {
        while($row = $q_recent->fetch_assoc()) {
            // Relasi aman ke Pelanggan (Menggunakan 'pelanggan_id' sesuai ERD)
            if(isset($row['pelanggan_id'])) {
                $qp = $conn->query("SELECT nama FROM pelanggan WHERE id = " . intval($row['pelanggan_id']));
                if($qp && $p_res = $qp->fetch_assoc()) $row['nama_pelanggan'] = $p_res['nama'];
            }
            // Relasi aman ke Mobil (Menggunakan 'plat_nomor' sesuai ERD)
            if(isset($row['plat_nomor'])) {
                $qm = $conn->query("SELECT merk, model FROM mobil WHERE plat_nomor = '" . $conn->real_escape_string($row['plat_nomor']) . "'");
                if($qm && $m_res = $qm->fetch_assoc()) {
                    $row['nama_mobil'] = $m_res['merk'] . ' ' . $m_res['model'];
                }
            }
            $recent_transaksi[] = $row;
        }
    }

    // 3. Ambil data Jatuh Tempo/Pengembalian Hari Ini (Sesuai Struktur Database Asli)
    
    // Logika: (Tanggal Sewa + Durasi Hari) <= Hari Ini, DAN statusnya masih 'Berjalan'
    $query_kritis = "SELECT * FROM transaksi 
                     WHERE DATE_ADD(tanggal_sewa, INTERVAL durasi_hari DAY) <= CURDATE() 
                     AND status_transaksi = 'Berjalan' 
                     ORDER BY DATE_ADD(tanggal_sewa, INTERVAL durasi_hari DAY) ASC";
    
    $q_kembali = $conn->query($query_kritis);
    
    if($q_kembali) {
        while($row = $q_kembali->fetch_assoc()) {
            $nama_p = 'Pelanggan'; $telp_p = '-';
            
            // Ambil data pelanggan
            if(isset($row['pelanggan_id'])) {
                $qp = $conn->query("SELECT nama, telepon FROM pelanggan WHERE id = " . intval($row['pelanggan_id']));
                if($qp && $p_res = $qp->fetch_assoc()) {
                    $nama_p = $p_res['nama'];
                    $telp_p = $p_res['telepon'] ?? '-';
                }
            }
            
            // Ambil data mobil
            $nama_m = 'Mobil';
            if(isset($row['plat_nomor'])) {
                $qm = $conn->query("SELECT merk, model FROM mobil WHERE plat_nomor = '" . $conn->real_escape_string($row['plat_nomor']) . "'");
                if($qm && $m_res = $qm->fetch_assoc()) {
                    $nama_m = $m_res['merk'] . ' ' . $m_res['model'];
                }
            }
            
            $pengembalian_hari_ini[] = [
                'nama_mobil' => $nama_m,
                'nama_pelanggan' => $nama_p,
                'telepon' => $telp_p
            ];
        }
    }
}

// Kalkulasi sisa armada yang tersedia di garasi
$total_tersedia = max(0, $total_mobil - $total_disewa);
?>

<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f8fafc]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
            
            <a href="dashboard.php" class="relative flex items-center gap-3 px-3 py-2 bg-gradient-to-r from-slate-800/60 to-slate-800/20 text-white rounded-lg text-sm font-medium transition-all group whitespace-nowrap">
                <span class="absolute left-0 bottom-0 md:bottom-2 md:top-2 md:left-0 w-full md:w-1 h-0.5 md:h-auto bg-orange-500 rounded-t md:rounded-r"></span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-orange-500">
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
                <span>Console</span>
                <span>/</span>
                <span class="text-slate-900 font-semibold">Dashboard</span>
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
            <div class="w-full space-y-8">
                
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Metrik Ringkasan</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Analisis performa bisnis rental mobil Anda secara realtime.</p>
                    </div>
                    <span class="flex items-center gap-2 text-xs font-mono font-medium bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm text-slate-500">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-slate-400">
                            <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>
                        </svg>
                        <?= date('d M Y'); ?>
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                    
                    <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-[0_2px_8px_rgba(0,0,0,0.02)] flex items-center justify-between group hover:border-slate-300 transition-all">
                        <div class="space-y-1">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Armada</span>
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight"><?= $total_mobil; ?> <span class="text-xs font-normal text-slate-400">Unit</span></h3>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                                <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/>
                                <circle cx="7" cy="17" r="2"/>
                                <circle cx="17" cy="17" r="2"/>
                                <path d="M5 10h14"/>
                            </svg>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-[0_2px_8px_rgba(0,0,0,0.02)] flex items-center justify-between group hover:border-slate-300 transition-all">
                        <div class="space-y-1">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Pelanggan</span>
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight"><?= $total_pelanggan; ?> <span class="text-xs font-normal text-slate-400">User</span></h3>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-[0_2px_8px_rgba(0,0,0,0.02)] flex items-center justify-between group hover:border-slate-300 transition-all">
                        <div class="space-y-1">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Mobil Aktif Sewa</span>
                            <h3 class="text-2xl font-black text-orange-600 tracking-tight"><?= $total_disewa; ?> <span class="text-xs font-normal text-slate-400">Jalan</span></h3>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-orange-500/10 text-orange-600 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                                <path d="m15.5 7.5 2.3 2.3a1 1 0 0 0 1.4 0l2.1-2.1a1 1 0 0 0 0-1.4L19 4"/><path d="m21 2-9.6 9.6"/><circle cx="7.5" cy="15.5" r="5.5"/>
                            </svg>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-[0_2px_8px_rgba(0,0,0,0.02)] flex items-center justify-between group hover:border-slate-300 transition-all">
                        <div class="space-y-1">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Pendapatan</span>
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></h3>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                                <rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/>
                            </svg>
                        </div>
                    </div>

                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white p-6 rounded-xl border border-slate-200/70 shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-indigo-500"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                            Tren Pendapatan Bulanan (Tahun Ini)
                        </h3>
                        <div class="h-64 relative">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-xl border border-slate-200/70 shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-500"><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/><path d="M2 12h20"/></svg>
                            Status Operasional Armada
                        </h3>
                        <div class="h-64 relative flex items-center justify-center">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white p-6 rounded-xl border border-slate-200/70 shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-blue-500"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
                            Transaksi Terbaru
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-100 text-slate-400 font-semibold bg-slate-50/50">
                                        <th class="py-2 px-3">ID</th>
                                        <th class="py-2 px-3">Pelanggan / Armada</th>
                                        <th class="py-2 px-3">Total Tagihan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-600">
                                    <?php if(!empty($recent_transaksi)): ?>
                                        <?php foreach($recent_transaksi as $tx): ?>
                                            <tr class="hover:bg-slate-50/50 transition-colors">
                                                <td class="py-2.5 px-3 font-mono text-slate-400">#<?= $tx['id'] ?? '-'; ?></td>
                                                <td class="py-2.5 px-3">
                                                    <div class="font-bold text-slate-800">
                                                        <?= htmlspecialchars($tx['nama_pelanggan'] ?? 'Pelanggan Tidak Ditemukan'); ?>
                                                    </div>
                                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                                        <?= htmlspecialchars($tx['nama_mobil'] ?? 'Mobil Tidak Ditemukan'); ?>
                                                    </div>
                                                </td>
                                                <td class="py-2.5 px-3 font-bold text-slate-700">Rp <?= number_format($tx['total_harga'] ?? 0, 0, ',', '.'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="py-4 text-center text-slate-400">Belum ada aktivitas transaksi.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl border border-slate-200/70 shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-orange-500"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            Pengembalian Kritis Hari Ini
                        </h3>
                        <div class="bg-white rounded-xl border border-slate-200/70 shadow-[0_2px_8px_rgba(0,0,0,0.01)] overflow-x-auto scrollbar-none">
                            <table class="w-full text-left border-collapse min-w-[600px]">
                                <thead>
                                    <tr class="border-b border-slate-100 text-slate-400 font-semibold bg-slate-50/50">
                                        <th class="py-2 px-3">Armada</th>
                                        <th class="py-2 px-3">Penyewa</th>
                                        <th class="py-2 px-3">Kontak WA / HP</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-600">
                                    <?php if(!empty($pengembalian_hari_ini)): ?>
                                        <?php foreach($pengembalian_hari_ini as $p_hari): ?>
                                            <tr class="hover:bg-red-50/40 transition-colors">
                                                <td class="py-2.5 px-3 font-bold text-slate-800"><?= htmlspecialchars($p_hari['nama_mobil']); ?></td>
                                                <td class="py-2.5 px-3 text-slate-600"><?= htmlspecialchars($p_hari['nama_pelanggan']); ?></td>
                                                <td class="py-2.5 px-3 font-mono text-indigo-600 font-medium"><?= htmlspecialchars($p_hari['telepon']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="py-6 text-center text-slate-400">
                                                <div class="flex flex-col items-center justify-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-400"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                                    <span class="mt-1 text-slate-500 font-medium">Semua aman!</span>
                                                    <span class="text-[10px] text-slate-400">Tidak ada jadwal pengembalian kritis hari ini.</span>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Distribusi Pendapatan dari Backend PHP
        const dataPendapatan = <?= json_encode(array_values($pendapatan_bulanan)); ?>;
        
        // 1. Konfigurasi Line Chart (Tren Pendapatan Bulanan)
        const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctxRevenue, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: dataPendapatan,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.04)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#4f46e5',
                    pointRadius: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0, 0, 0, 0.02)' }, ticks: { font: { size: 10 } } },
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                }
            }
        });

        // 2. Konfigurasi Doughnut Chart (Status Operasional Mobil)
        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Tersedia', 'Sedang Disewa'],
                datasets: [{
                    data: [<?= $total_tersedia; ?>, <?= $total_disewa; ?>],
                    backgroundColor: ['#10b981', '#f97316'],
                    borderWidth: 3,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, padding: 15, font: { size: 11, weight: '500' } }
                    }
                },
                cutout: '72%'
            }
        });
    </script>
</body>
</html>