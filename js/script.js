// Fungsi Visual Selection Box
function selectBox(element) {
    // Cari elemen induk (grid)
    const container = element.parentElement;
    // Hapus class 'selected' dari semua saudara
    const siblings = container.children;
    for (let sib of siblings) {
        sib.classList.remove('selected');
    }
    // Tambah class 'selected' ke elemen yang diklik
    element.classList.add('selected');
    
    // Trigger perhitungan ulang
    calcTotal();
}

// Fungsi Hitung Total Harga
function calcTotal() {
    // Ambil elemen input
    const qtyInput = document.getElementById('qty');
    const displayTotal = document.getElementById('total-price');
    const displayDetail = document.getElementById('detail-text');

    if (!qtyInput || !displayTotal) return;

    // Ambil Nilai dari Radio Button yang Tercentang (Checked)
    const printType = document.querySelector('input[name="print_type"]:checked');
    const paperSize = document.querySelector('input[name="paper_size"]:checked');
    const binding = document.querySelector('input[name="binding"]:checked');

    // Harga Dasar
    let pricePerSheet = (printType.value === 'Hitam Putih') ? 500 : 2000;
    let qty = parseInt(qtyInput.value) || 0; // Pastikan integer

    // Harga Jilid
    let bindingPrice = 0;
    if (binding.value === 'Spiral') bindingPrice = 10000;
    if (binding.value === 'Hard Cover') bindingPrice = 20000;

    // Total Kalkulasi
    let total = (pricePerSheet * qty) + bindingPrice;

    // Update Tampilan Harga
    displayTotal.innerText = 'Rp ' + total.toLocaleString('id-ID');

    // Update Teks Detail
    displayDetail.innerText = `${printType.value} • ${paperSize.value} • ${qty} lembar`;
}

// Jalankan saat halaman pertama dimuat
document.addEventListener("DOMContentLoaded", function() {
    calcTotal();
});

const toggleButton = document.getElementsByClassName('toggle-button')[0];
const navbarLinks = document.getElementsByClassName('navbar-links')[0];

toggleButton.addEventListener('click', () => {
    // Menambahkan atau menghapus class 'active' pada navbar-links
    navbarLinks.classList.toggle('active');
});

