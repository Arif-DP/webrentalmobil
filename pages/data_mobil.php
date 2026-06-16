<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}
include '../config/koneksi.php';

// Proses Tambah / Edit Data Mobil
if (isset($_POST['simpan'])) {
    $plat_nomor = $_POST['plat_nomor'];
    $merk = $_POST['merk'];
    $model = $_POST['model'];
    $tahun = $_POST['tahun'];
    $harga = $_POST['harga_per_hari'];
    $status = $_POST['status'];

    $foto = $_FILES['foto']['name'];
    $tmp_foto = $_FILES['foto']['tmp_name'];
    $nama_foto_baru = "";

    if ($foto != "") {
        $nama_foto_baru = date('dmYHis') . "_" . str_replace(" ", "_", $foto);
        $path = "../assets/img/mobil/" . $nama_foto_baru;
        move_uploaded_file($tmp_foto, $path);
    }

    if ($_POST['mode'] == 'edit') {
        if ($nama_foto_baru != "") {
            $sql = "UPDATE mobil SET merk='$merk', model='$model', tahun='$tahun', harga_per_hari='$harga', status='$status', foto='$nama_foto_baru' WHERE plat_nomor='$plat_nomor'";
        } else {
            $sql = "UPDATE mobil SET merk='$merk', model='$model', tahun='$tahun', harga_per_hari='$harga', status='$status' WHERE plat_nomor='$plat_nomor'";
        }
    } else {
        $sql = "INSERT INTO mobil (plat_nomor, merk, model, tahun, harga_per_hari, status, foto) 
                VALUES ('$plat_nomor', '$merk', '$model', '$tahun', '$harga', '$status', '$nama_foto_baru')";
    }

    if ($conn->query($sql)) {
        header("Location: data_mobil.php");
        exit;
    }
}

// Proses Hapus Data Mobil
if (isset($_GET['hapus'])) {
    $plat_nomor = $_GET['hapus'];
    $cek_foto = $conn->query("SELECT foto FROM mobil WHERE plat_nomor='$plat_nomor'")->fetch_assoc();
    if (!empty($cek_foto['foto'])) {
        $file_path = "../assets/img/mobil/" . $cek_foto['foto'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    $conn->query("DELETE FROM mobil WHERE plat_nomor='$plat_nomor'");
    header("Location: data_mobil.php");
    exit;
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $plat_edit = $_GET['edit'];
    $result_edit = $conn->query("SELECT * FROM mobil WHERE plat_nomor='$plat_edit'");
    $edit_data = $result_edit->fetch_assoc();
}

$tabel_mobil = $conn->query("SELECT * FROM mobil");
?>

<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f8fafc]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mobil</title>
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

            <a href="data_mobil.php" class="relative flex items-center gap-3 px-3 py-2 bg-gradient-to-r from-slate-800/60 to-slate-800/20 text-white rounded-lg text-sm font-medium transition-all group whitespace-nowrap">
                <span class="absolute left-0 bottom-0 md:bottom-2 md:top-2 md:left-0 w-full md:w-1 h-0.5 md:h-auto bg-orange-500 rounded-t md:rounded-r"></span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-orange-500">
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
                <span>Console</span><span>/</span><span class="text-slate-900 font-semibold">Kelola Data Mobil</span>
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
                    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Manajemen Data Armada</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Tambah baru, edit spesifikasi, atau hapus database inventaris mobil.</p>
                </div>

                <div class="bg-white p-6 rounded-xl border border-slate-200/70 shadow-[0_2px_8px_rgba(0,0,0,0.01)]">
                    <h3 class="text-sm font-bold mb-4 text-slate-800 uppercase tracking-wide flex items-center gap-2">
                        <span class="w-1.5 h-3 bg-orange-500 rounded-sm"></span>
                        <?= $edit_data ? 'Update Spesifikasi Mobil' : 'Pendaftaran Mobil Baru'; ?>
                    </h3>
                    
                    <form action="data_mobil.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <input type="hidden" name="mode" value="<?= $edit_data ? 'edit' : 'tambah'; ?>">
                        
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Plat Nomor</label>
                            <input type="text" name="plat_nomor" placeholder="Contoh: N 1234 AB" required class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-slate-400 transition" value="<?= $edit_data['plat_nomor'] ?? ''; ?>" <?= $edit_data ? 'readonly class="bg-slate-50 text-slate-400 cursor-not-allowed border-slate-200"' : ''; ?>>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Merk Mobil</label>
                            <input type="text" name="merk" placeholder="Contoh: Toyota" required class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-slate-400 transition" value="<?= $edit_data['merk'] ?? ''; ?>">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Model / Tipe</label>
                            <input type="text" name="model" placeholder="Contoh: Avanza" required class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-slate-400 transition" value="<?= $edit_data['model'] ?? ''; ?>">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Tahun Kendaraan</label>
                            <input type="number" name="tahun" placeholder="Contoh: 2022" required class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-slate-400 transition" value="<?= $edit_data['tahun'] ?? ''; ?>">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Tarif Sewa (Per Hari)</label>
                            <input type="number" name="harga_per_hari" placeholder="Contoh: 350000" required class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-slate-400 transition" value="<?= $edit_data['harga_per_hari'] ?? ''; ?>">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Status Unit</label>
                            <select name="status" class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-lg bg-white focus:outline-none focus:border-slate-400 transition">
                                <option value="Tersedia" <?= (isset($edit_data['status']) && $edit_data['status'] == 'Tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                                <option value="Disewa" <?= (isset($edit_data['status']) && $edit_data['status'] == 'Disewa') ? 'selected' : ''; ?>>Disewa</option>
                                <option value="Maintenance" <?= (isset($edit_data['status']) && $edit_data['status'] == 'Maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                            </select>
                        </div>
                        
                        <div class="md:col-span-3">
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Media Foto Armada</label>
                            
                            <div id="dropzone" class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-xl hover:border-orange-500 hover:bg-orange-50/30 transition-all cursor-pointer relative group" onclick="document.getElementById('foto').click()">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-slate-400 group-hover:text-orange-500 transition-colors" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    
                                    <div class="flex text-sm text-slate-600 justify-center">
                                        <label for="foto" class="relative cursor-pointer rounded-md font-medium text-orange-600 hover:text-orange-500 focus-within:outline-none bg-transparent">
                                            <span>Upload file gambar</span>
                                            <input id="foto" name="foto" type="file" class="sr-only" accept="image/*" onchange="previewImage(event)">
                                        </label>
                                        <p class="pl-1">atau drag and drop</p>
                                    </div>
                                    <p class="text-[10px] text-slate-500 mt-1">*Kosongkan jika berkas foto tidak ingin diubah/ditambahkan.</p>
                                    <p class="text-xs text-slate-500">PNG, JPG, JPEG up to 2MB</p>
                                </div>
                            </div>

                            <div id="image-preview" class="hidden mt-4">
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Preview Gambar:</p>
                                <img id="preview-img" src="" alt="Preview" class="max-h-48 rounded-lg border border-slate-200 shadow-sm object-cover">
                            </div>
                        </div>
                        <div class="md:col-span-3 flex justify-end space-x-2 pt-4 border-t border-slate-100 mt-2">
                            <?php if ($edit_data): ?>
                                <a href="data_mobil.php" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 text-xs font-semibold transition">Batal</a>
                            <?php endif; ?>
                            <button type="submit" name="simpan" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 text-xs font-semibold shadow-sm transition">
                                <?= $edit_data ? 'Simpan Perubahan' : 'Daftarkan Mobil'; ?>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-xl border border-slate-200/70 shadow-[0_2px_8px_rgba(0,0,0,0.01)] overflow-x-auto scrollbar-none">
                    <table class="w-full text-left border-collapse min-w-[600px]">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider w-24">Foto</th>
                                <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Plat Nomor</th>
                                <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Identitas Unit</th>
                                <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tarif Operasional</th>
                                <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kondisi</th>
                                <th class="p-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <?php if ($tabel_mobil->num_rows > 0): ?>
                                <?php while($row = $tabel_mobil->fetch_assoc()): ?>
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="p-4">
                                            <?php if (!empty($row['foto'])): ?>
                                                <img src="../assets/img/mobil/<?= $row['foto']; ?>" alt="Mobil" class="w-16 h-11 object-cover rounded-md border border-slate-200 shadow-sm">
                                            <?php else: ?>
                                                <div class="w-16 h-11 bg-slate-50 rounded-md border border-slate-100 flex items-center justify-center text-[9px] text-slate-400 font-medium">No Image</div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4 font-mono font-bold text-slate-800 tracking-wide"><?= $row['plat_nomor']; ?></td>
                                        <td class="p-4">
                                            <div class="font-bold text-slate-900"><?= $row['merk']; ?></div>
                                            <div class="text-[11px] text-slate-400 font-medium"><?= $row['model']; ?> &middot; Prod <?= $row['tahun']; ?></div>
                                        </td>
                                        <td class="p-4 font-bold text-slate-900">Rp <?= number_format($row['harga_per_hari'], 0, ',', '.'); ?><span class="text-[10px] text-slate-400 font-normal">/hr</span></td>
                                        <td class="p-4">
                                            <?php 
                                            $badge = "bg-emerald-500/10 text-emerald-600 border-emerald-500/20";
                                            if ($row['status'] == 'Disewa') $badge = "bg-orange-500/10 text-orange-600 border-orange-500/20";
                                            if ($row['status'] == 'Maintenance') $badge = "bg-rose-500/10 text-rose-600 border-rose-500/20";
                                            ?>
                                            <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold border <?= $badge; ?>"><?= $row['status']; ?></span>
                                        </td>
                                        <td class="p-4 text-center space-x-3 text-xs font-semibold">
                                            <a href="data_mobil.php?edit=<?= $row['plat_nomor']; ?>" class="text-slate-600 hover:text-slate-900 transition">Edit</a>
                                            <a href="data_mobil.php?hapus=<?= $row['plat_nomor']; ?>" onclick="return confirm('Hapus permanen unit ini?')" class="text-rose-500 hover:text-rose-700 transition">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-slate-400 italic text-xs">Belum ada baris data mobil di dalam inventaris.</td>
                                </tr>
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

        // Script untuk Preview Gambar + Validasi
        function previewImage(event) {
            const input = event.target;
            const previewContainer = document.getElementById('image-preview');
            const previewImg = document.getElementById('preview-img');

            if (input.files && input.files[0]) {
                const file = input.files[0];

                // 1. Validasi Tipe File (hanya PNG, JPG, JPEG)
                const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Gagal! Tipe file tidak didukung. Harap upload gambar berformat PNG, JPG, atau JPEG.');
                    input.value = ''; // Reset file yang dipilih
                    previewContainer.classList.add('hidden');
                    return; // Hentikan proses
                }

                // 2. Validasi Ukuran File (Maksimal 2MB)
                // 2MB = 2 * 1024 * 1024 bytes = 2097152 bytes
                const maxSize = 2 * 1024 * 1024; 
                if (file.size > maxSize) {
                    alert('Gagal! Ukuran file terlalu besar. Maksimal ukuran file adalah 2MB.');
                    input.value = ''; // Reset file yang dipilih
                    previewContainer.classList.add('hidden');
                    return; // Hentikan proses
                }

                // Jika lolos kedua validasi di atas, tampilkan preview gambar
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
                
            } else {
                previewContainer.classList.add('hidden');
                previewImg.src = "";
            }
        }

        // Fungsi Mesin Drag & Drop Asli
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('foto');

        // Saat gambar diseret masuk ke dalam kotak
        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault(); // Mencegah browser membuka gambar di tab baru
            dropzone.classList.add('border-orange-500', 'bg-orange-50'); // Beri efek nyala
        });

        // Saat gambar diseret keluar kotak sebelum dilepas
        dropzone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropzone.classList.remove('border-orange-500', 'bg-orange-50'); // Hilangkan efek
        });

        // Saat gambar resmi dijatuhkan (di-drop) ke dalam kotak
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('border-orange-500', 'bg-orange-50');

            // Tangkap file yang dijatuhkan
            if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                // Pindahkan file tersebut ke dalam input file tersembunyi kita
                fileInput.files = e.dataTransfer.files;
                
                // Panggil fungsi validasi dan preview yang sudah kita buat tadi
                previewImage({ target: fileInput });
            }
        });
    </script>
</body>
</html>