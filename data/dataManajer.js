const DB = {
    save: (key, data) => localStorage.setItem(key, JSON.stringify(data)),
    get: (key) => JSON.parse(localStorage.getItem(key)) || []
};

if (!localStorage.getItem('mobil')) DB.save('mobil', []);
if (!localStorage.getItem('pelanggan')) DB.save('pelanggan', []);
if (!localStorage.getItem('transaksi')) DB.save('transaksi', []);