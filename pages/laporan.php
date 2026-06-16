<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}
include '../config/koneksi.php';

// Menangkap parameter filter dari URL
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Menyusun kondisi WHERE untuk query dinamis
$where_clauses = [];
$where_clauses_main = []; // Khusus untuk query utama yang pakai JOIN (karena butuh prefix t.)

if (!empty($filter_status)) {
    $where_clauses[] = "status_transaksi = '$filter_status'";
    $where_clauses_main[] = "t.status_transaksi = '$filter_status'";
}
if (!empty($start_date) && !empty($end_date)) {
    $where_clauses[] = "tanggal_sewa BETWEEN '$start_date' AND '$end_date'";
    $where_clauses_main[] = "t.tanggal_sewa BETWEEN '$start_date' AND '$end_date'";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = " WHERE " . implode(" AND ", $where_clauses);
}

$where_sql_main = "";
if (count($where_clauses_main) > 0) {
    $where_sql_main = " WHERE " . implode(" AND ", $where_clauses_main);
}

// Menghitung total transaksi berdasarkan filter
$query_total_transaksi = $conn->query("SELECT COUNT(*) as total FROM transaksi" . $where_sql);
$total_transaksi = $query_total_transaksi->fetch_assoc()['total'];

// Menghitung total pendapatan berdasarkan filter
$query_total_pendapatan = $conn->query("SELECT SUM(total_harga) as total FROM transaksi" . $where_sql);
$total_pendapatan = $query_total_pendapatan->fetch_assoc()['total'] ?? 0;

// Query utama untuk tabel laporan
$sql_laporan = "SELECT t.*, p.nama AS nama_pelanggan, m.merk, m.model, m.harga_per_hari 
                FROM transaksi t 
                JOIN pelanggan p ON t.pelanggan_id = p.id 
                JOIN mobil m ON t.plat_nomor = m.plat_nomor" 
                . $where_sql_main . " ORDER BY t.id DESC";

$tabel_laporan = $conn->query($sql_laporan);
?>

<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f8fafc]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi</title>
    <script src="../assets/js/tailwind.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            .print-area {
                width: 100% !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
                background-color: white !important;
            }
            body { background-color: white !important; }
        }
        
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

            <a href="transaksi_rental.php" class="flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-slate-100 hover:bg-slate-800/30 rounded-lg text-sm font-medium transition-all group whitespace-nowrap">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-slate-500 group-hover:text-slate-300 transition-colors">
                    <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/>
                </svg>
                <span>Transaksi Rental</span>
            </a>

            <a href="laporan.php" class="relative flex items-center gap-3 px-3 py-2 bg-gradient-to-r from-slate-800/60 to-slate-800/20 text-white rounded-lg text-sm font-medium transition-all group whitespace-nowrap">
                <span class="absolute left-0 bottom-0 md:bottom-2 md:top-2 md:left-0 w-full md:w-1 h-0.5 md:h-auto bg-orange-500 rounded-t md:rounded-r"></span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-orange-500">
                    <path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/>
                </svg>
                <span>Laporan</span>
            </a>

        </nav>
        </div>
    </aside>

    <div class="flex-1 flex flex-col overflow-y-auto print-area">
        
        <header class="bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 md:px-8 py-3 flex justify-between items-center z-10">
            <div class="flex items-center gap-2 text-xs font-medium text-slate-400"><span>Console</span><span>/</span><span class="text-slate-900 font-semibold">Laporan Keuangan</span></div>
            <button onclick="window.print()" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all shadow-sm hover:shadow active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect width="12" height="8" x="6" y="14"></rect>
                </svg>
                Cetak Laporan PDF
            </button>
        </header>

        <main class="p-4 md:p-8 w-full space-y-6 print-area bg-[#F8FAFC]">
            
            <div class="hidden print:block text-center border-b-2 border-slate-800 pb-4 mb-6">
                <h1 class="text-3xl font-black uppercase tracking-widest text-slate-900">Arif Car Rental</h1>
                <p class="text-sm text-slate-600 mt-1 font-medium">Laporan Rekapitulasi Operasional & Keuangan Unit</p>
                <p class="text-[10px] text-slate-400 mt-1">Dicetak pada: <?= date('d F Y, H:i'); ?> WIB</p>
                <?php if(!empty($start_date) && !empty($end_date)): ?>
                    <p class="text-[10px] font-bold text-slate-700 mt-1">Periode: <?= date('d M Y', strtotime($start_date)) ?> s/d <?= date('d M Y', strtotime($end_date)) ?></p>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
                <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-[0_2px_8px_rgba(0,0,0,0.01)] flex items-center justify-between transition-transform hover:-translate-y-1 duration-300">
                    <div class="space-y-1">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Volume Transaksi</span>
                        <h3 class="text-3xl font-black text-slate-900 tracking-tight"><?= $total_transaksi; ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                            <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                            <polyline points="16 7 22 7 22 13"></polyline>
                        </svg>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-xl border border-slate-200/70 shadow-[0_2px_8px_rgba(0,0,0,0.01)] flex items-center justify-between transition-transform hover:-translate-y-1 duration-300">
                    <div class="space-y-1">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Akumulasi Pendapatan</span>
                        <h3 class="text-2xl font-black text-emerald-600 tracking-tight">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                            <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                            <line x1="2" x2="22" y1="10" y2="10"></line>
                        </svg>
                    </div>
                </div>
            </div>

            <form method="GET" action="laporan.php" class="bg-white p-3 rounded-xl border border-slate-200/70 shadow-[0_2px_8px_rgba(0,0,0,0.01)] flex flex-wrap items-center gap-4 no-print">
                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider pl-2">Periode:</span>
                    <input type="date" name="start_date" value="<?= $start_date ?>" class="text-sm border border-slate-200 rounded-lg px-3 py-1.5 outline-none focus:border-orange-500 text-slate-600">
                    <span class="text-slate-400 text-xs font-bold">s/d</span>
                    <input type="date" name="end_date" value="<?= $end_date ?>" class="text-sm border border-slate-200 rounded-lg px-3 py-1.5 outline-none focus:border-orange-500 text-slate-600">
                </div>
                
                <div class="flex items-center gap-2 border-l border-slate-200 pl-4">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status:</span>
                    <select name="status" class="text-sm border border-slate-200 rounded-lg px-3 py-1.5 outline-none focus:border-orange-500 text-slate-600 bg-white">
                        <option value="">Semua Status</option>
                        <option value="Berjalan" <?= $filter_status == 'Berjalan' ? 'selected' : '' ?>>Berjalan</option>
                        <option value="Selesai" <?= $filter_status == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                    </select>
                </div>

                <div class="flex gap-2 ml-auto">
                    <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-1.5 rounded-lg text-sm font-bold transition-all shadow-sm">Filter</button>
                    <a href="laporan.php" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-1.5 rounded-lg text-sm font-bold transition-all">Reset</a>
                </div>
            </form>

            <div class="bg-white rounded-xl border border-slate-200/70 shadow-[0_2px_8px_rgba(0,0,0,0.01)] overflow-x-auto scrollbar-none">
                <table class="w-full text-left border-collapse min-w-[600px]">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider w-12 text-center">No</th>
                            <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tgl Faktur</th>
                            <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Identitas Pelanggan</th>
                            <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Data Armada</th>
                            <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Durasi</th>
                            <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status Faktur</th>
                            <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider text-right">Nilai Transaksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php if ($tabel_laporan->num_rows > 0): ?>
                            <?php $no = 1; while($row = $tabel_laporan->fetch_assoc()): ?>
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="p-4 text-center text-slate-400 font-medium"><?= $no++; ?></td>
                                    <td class="p-4 text-slate-600 font-medium"><?= date('d M Y', strtotime($row['tanggal_sewa'])); ?></td>
                                    <td class="p-4 font-bold text-slate-900"><?= $row['nama_pelanggan']; ?></td>
                                    <td class="p-4">
                                        <div class="font-medium text-slate-800"><?= $row['merk']; ?></div>
                                        <div class="text-[10px] font-mono text-slate-400 font-bold tracking-wider"><?= $row['plat_nomor']; ?></div>
                                    </td>
                                    <td class="p-4 font-medium text-slate-600"><?= $row['durasi_hari']; ?> Hari</td>
                                    <td class="p-4">
                                        <?php if ($row['status_transaksi'] == 'Berjalan'): ?>
                                            <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold border bg-orange-500/10 text-orange-600 border-orange-500/20">Berjalan</span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold border bg-emerald-500/10 text-emerald-600 border-emerald-500/20">Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 font-black text-slate-800 tracking-tight text-right">Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-400 italic text-xs">Rekap data tidak ditemukan untuk rentang tanggal atau status ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="hidden print:grid grid-cols-2 pt-16 text-center text-sm text-slate-800">
                <div></div>
                <div class="space-y-16">
                    <p>Mengetahui/Mengesahkan,<br><span class="font-bold">Direktur Operasional</span></p>
                    <p class="font-bold underline text-slate-900">Arif Car Rental Corp.</p>
                </div>
            </div>

        </main>
    </div>
</body>
</html>