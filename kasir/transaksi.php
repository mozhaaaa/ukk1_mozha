<?php

session_start();
include "../koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['level'] != "kasir") {
    header("Location: ../login.php");
    exit;
}

/* =========================
   SIMPAN TRANSAKSI
========================= */

if (isset($_POST['simpan'])) {

    $nama_pelanggan = $_POST['nama_pelanggan'];
    $metode = $_POST['metode_pembayaran'];
    $status = "selesai";

    $produk_ids = $_POST['produk_id'];
    $jumlah_produk = $_POST['jumlah_produk'];

    /* =========================
       CEK PRODUK
    ========================= */

    $ada_produk = false;

    foreach ($produk_ids as $produk_id) {
        if (!empty($produk_id)) {
            $ada_produk = true;
            break;
        }
    }

    if (!$ada_produk) {
        header("Location: transaksi.php?pesan=produk");
        exit;
    }

    /* =========================
       CARI PELANGGAN
    ========================= */

    $cek_pelanggan = mysqli_query(
        $koneksi,
        "SELECT * FROM pelanggan
         WHERE nama_pelanggan='$nama_pelanggan'
         LIMIT 1"
    );

    if (mysqli_num_rows($cek_pelanggan) > 0) {

        $pelanggan_data = mysqli_fetch_assoc($cek_pelanggan);
        $pelanggan_id = $pelanggan_data['pelanggan_id'];

    } else {

        if (!mysqli_query(
            $koneksi,
            "INSERT INTO pelanggan
            (nama_pelanggan)
            VALUES
            ('$nama_pelanggan')"
        )) {
            die(
                "Pelanggan gagal disimpan: "
                . mysqli_error($koneksi)
            );
        }

        $pelanggan_id = mysqli_insert_id($koneksi);
    }

    /* =========================
       CEK STOK DAN HITUNG TOTAL
    ========================= */

    $total_harga = 0;
    $detail_produk = [];

    foreach ($produk_ids as $index => $produk_id) {

        if (empty($produk_id)) {
            continue;
        }

        $jumlah = $jumlah_produk[$index];

        if ($jumlah <= 0) {
            header("Location: transaksi.php?pesan=jumlah");
            exit;
        }

        $produk = mysqli_query(
            $koneksi,
            "SELECT * FROM produk
             WHERE produk_id='$produk_id'"
        );

        $p = mysqli_fetch_assoc($produk);

        if (!$p) {
            header("Location: transaksi.php?pesan=produk");
            exit;
        }

        if ($jumlah > $p['stok']) {
            header("Location: transaksi.php?pesan=stok");
            exit;
        }

        $subtotal = $p['harga'] * $jumlah;
        $total_harga += $subtotal;

        $detail_produk[] = [
            'produk_id' => $p['produk_id'],
            'jumlah' => $jumlah,
            'harga' => $p['harga'],
            'subtotal' => $subtotal
        ];
    }

    /* =========================
       CEK PEMBAYARAN CASH
    ========================= */

    $uang_bayar = 0;
    $kembalian = 0;

    if ($metode == "cash") {

        $uang_bayar = isset($_POST['uang_bayar'])
            ? $_POST['uang_bayar']
            : 0;

        if ($uang_bayar < $total_harga) {
            header("Location: transaksi.php?pesan=kurang");
            exit;
        }

        $kembalian = $uang_bayar - $total_harga;
    }

    /* =========================
       SIMPAN PENJUALAN
    ========================= */

    if (!mysqli_query(
        $koneksi,
        "INSERT INTO penjualan
        (tanggal_penjualan, total_harga, pelanggan_id)
        VALUES
        (NOW(), '$total_harga', '$pelanggan_id')"
    )) {
        die(
            "Penjualan gagal disimpan: "
            . mysqli_error($koneksi)
        );
    }

    $penjualan_id = mysqli_insert_id($koneksi);

    /* =========================
       SIMPAN DETAIL PENJUALAN
    ========================= */

    foreach ($detail_produk as $item) {

        $produk_id = $item['produk_id'];
        $jumlah = $item['jumlah'];
        $harga = $item['harga'];
        $subtotal = $item['subtotal'];

        if (!mysqli_query(
            $koneksi,
            "INSERT INTO detail_penjualan
            (penjualan_id, produk_id, jumlah_produk, harga, subtotal)
            VALUES
            ('$penjualan_id',
             '$produk_id',
             '$jumlah',
             '$harga',
             '$subtotal')"
        )) {
            die(
                "Detail penjualan gagal disimpan: "
                . mysqli_error($koneksi)
            );
        }

        /* =========================
           UPDATE STOK
        ========================= */

        if (!mysqli_query(
            $koneksi,
            "UPDATE produk
             SET stok = stok - $jumlah
             WHERE produk_id='$produk_id'"
        )) {
            die(
                "Stok gagal diperbarui: "
                . mysqli_error($koneksi)
            );
        }
    }

    /* =========================
       SIMPAN TRANSAKSI PEMBAYARAN
    ========================= */

    if (!mysqli_query(
        $koneksi,
        "INSERT INTO transaksi
        (tanggal_transaksi, metode_pembayaran, status_transaksi, penjualan_id, uang_bayar, kembalian)
        VALUES
        (NOW(), '$metode', '$status', '$penjualan_id', '$uang_bayar', '$kembalian')"
    )) {
        die(
            "Transaksi gagal disimpan: "
            . mysqli_error($koneksi)
        );
    }

    /* =========================
       SELESAI
    ========================= */

    header("Location: transaksi.php?berhasil=1");
    exit;
}

/* =========================
   AMBIL DATA PRODUK
========================= */

$produk = mysqli_query(
    $koneksi,
    "SELECT * FROM produk
     WHERE stok > 0
     ORDER BY produk_id DESC"
);

?>

<!DOCTYPE html>
<html>

<head>
    <title>Transaksi Kasir</title>
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .produk-row {
            display: grid;
            grid-template-columns: 1fr 180px 45px;
            gap: 12px;
            align-items: end;
            margin-bottom: 12px;
        }

        .produk-row .form-group {
            margin-bottom: 0;
        }

        .btn-tambah-produk {
            margin-top: 8px;
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 10px 16px;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn-tambah-produk:hover {
            background: #e2e8f0;
        }

        .btn-hapus-produk {
            background: #fee2e2;
            color: #dc2626;
            border: none;
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 18px;
        }

        .btn-hapus-produk:hover {
            background: #fecaca;
        }

        .payment-box {
            margin-top: 25px;
            padding: 20px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
        }

        .total-payment {
            margin-top: 20px;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 20px;
            font-weight: bold;
        }

        #totalHarga {
            float: right;
        }

        .cash-box {
            display: none;
            margin-top: 20px;
        }

        .change-box {
            margin-top: 15px;
        }

        .change-result {
            font-size: 20px;
            font-weight: bold;
            padding: 12px;
            background: #dcfce7;
            color: #15803d;
            border-radius: 8px;
        }

        @media (max-width: 700px) {
            .produk-row {
                grid-template-columns: 1fr;
            }

            #totalHarga {
                float: none;
                display: block;
                margin-top: 5px;
            }
        }
    </style>
</head>

<body>

<div class="wrapper">

    <div class="sidebar">
        <div class="logo">
            <h2>Toko Sembako</h2>
            <p>Kasir</p>
        </div>

        <a href="index.php">Dashboard</a>
        <a href="transaksi.php">Transaksi</a>
        <a href="riwayat.php">Riwayat</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>

    <div class="content">

        <div class="header">
            <h1>Transaksi</h1>
            <p>Kelola penjualan dan pembayaran</p>
        </div>

        <?php if (isset($_GET['berhasil'])) { ?>
            <div class="alert" style="background:#dcfce7;color:#15803d;">
                Transaksi berhasil disimpan.
            </div>
        <?php } ?>

        <?php if (isset($_GET['pesan'])) { ?>
            <div class="alert">
                <?php
                if ($_GET['pesan'] == "produk") {
                    echo "Produk tidak ditemukan.";
                } elseif ($_GET['pesan'] == "jumlah") {
                    echo "Jumlah produk harus lebih dari 0.";
                } elseif ($_GET['pesan'] == "stok") {
                    echo "Jumlah pembelian melebihi stok yang tersedia.";
                } elseif ($_GET['pesan'] == "kurang") {
                    echo "Uang pembayaran kurang dari total harga.";
                }
                ?>
            </div>
        <?php } ?>

        <div class="box">
            <h2>Buat Transaksi</h2>

            <form method="POST" id="formTransaksi">

                <div class="form-group">
                    <label>Nama Pelanggan</label>
                    <input type="text" name="nama_pelanggan" placeholder="Masukkan nama pelanggan" required>
                </div>

                <br>

                <label>Produk Pembelian</label>

                <div id="produkContainer">
                    <div class="produk-row">

                        <div class="form-group">
                            <select name="produk_id[]" required>
                                <option value="">Pilih Produk</option>

                                <?php
                                mysqli_data_seek($produk, 0);
                                while ($p = mysqli_fetch_assoc($produk)) {
                                ?>

                                    <option value="<?php echo $p['produk_id']; ?>">
                                        <?php echo htmlspecialchars($p['nama_produk']); ?>
                                    </option>

                                <?php } ?>

                            </select>
                        </div>

                        <div class="form-group">
                            <input type="number" name="jumlah_produk[]" min="1" placeholder="Jumlah" required>
                        </div>

                        <button type="button" class="btn-hapus-produk" onclick="hapusProduk(this)">×</button>

                    </div>
                </div>

                <button type="button" class="btn-tambah-produk" onclick="tambahProduk()">＋ Tambah Produk</button>

                <div class="total-payment">
                    <span>Total Harga</span>
                    <span id="totalHarga">Rp 0</span>
                    <div style="clear:both;"></div>
                </div>

                <div class="payment-box">

                    <h3>Pembayaran</h3>

                    <br>

                    <div class="form-grid">

                        <div class="form-group">
                            <label>Metode Pembayaran</label>

                            <select name="metode_pembayaran" id="metode_pembayaran" required>
                                <option value="">Pilih Pembayaran</option>
                                <option value="cash">Cash</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Status Transaksi</label>

                            <select name="status_transaksi" required>
                                <option value="selesai">Selesai</option>
                            </select>
                        </div>

                    </div>

                    <!-- =========================
                         CASH
                    ========================= -->

                    <div class="cash-box" id="cashBox">

                        <div class="form-group">
                            <label>Uang Dibayar</label>

                            <input
                                type="number"
                                name="uang_bayar"
                                id="uang_bayar"
                                min="0"
                                placeholder="Masukkan uang dibayar"
                            >
                        </div>

                        <div class="change-box">

                            <label>Kembalian</label>

                            <div class="change-result" id="kembalian">
                                Rp 0
                            </div>

                        </div>

                    </div>

                    <br>

                    <button type="submit" name="simpan" class="btn btn-primary">
                        Simpan Transaksi
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script>

/* =========================
   DATA HARGA PRODUK
========================= */

const hargaProduk = {

    <?php

    mysqli_data_seek($produk, 0);

    $dataHarga = [];

    while ($p = mysqli_fetch_assoc($produk)) {

        $dataHarga[] =
            "'" . $p['produk_id'] . "': " . $p['harga'];

    }

    echo implode(",", $dataHarga);

    ?>

};


/* =========================
   TAMBAH BARIS PRODUK
========================= */

function tambahProduk() {

    const container =
        document.getElementById('produkContainer');

    const baris =
        document.createElement('div');

    baris.className = 'produk-row';

    baris.innerHTML = `

        <div class="form-group">

            <select name="produk_id[]" required>

                <option value="">
                    Pilih Produk
                </option>

                <?php

                mysqli_data_seek($produk, 0);

                while ($p = mysqli_fetch_assoc($produk)) {

                ?>

                    <option value="<?php echo $p['produk_id']; ?>">
                        <?php echo htmlspecialchars($p['nama_produk']); ?>
                    </option>

                <?php } ?>

            </select>

        </div>

        <div class="form-group">

            <input
                type="number"
                name="jumlah_produk[]"
                min="1"
                placeholder="Jumlah"
                required
            >

        </div>

        <button
            type="button"
            class="btn-hapus-produk"
            onclick="hapusProduk(this)"
        >
            ×
        </button>

    `;

    container.appendChild(baris);
}


/* =========================
   HAPUS BARIS PRODUK
========================= */

function hapusProduk(button) {

    const container =
        document.getElementById('produkContainer');

    const semuaBaris =
        container.querySelectorAll('.produk-row');

    if (semuaBaris.length > 1) {

        button.parentElement.remove();

        hitungTotal();

        if (metodePembayaran.value === 'cash') {
            hitungKembalian();
        }

    } else {

        alert('Minimal harus ada satu produk.');

    }
}


/* =========================
   FORMAT RUPIAH
========================= */

function formatRupiah(angka) {

    return 'Rp ' +
        angka.toLocaleString('id-ID');

}


/* =========================
   HITUNG TOTAL
========================= */

function hitungTotal() {

    let total = 0;

    const produkSelect =
        document.querySelectorAll(
            'select[name="produk_id[]"]'
        );

    const jumlahInput =
        document.querySelectorAll(
            'input[name="jumlah_produk[]"]'
        );

    produkSelect.forEach(function(select, index) {

        const produkId =
            select.value;

        const jumlah =
            parseInt(jumlahInput[index].value) || 0;

        if (
            produkId &&
            jumlah > 0 &&
            hargaProduk[produkId]
        ) {

            total +=
                hargaProduk[produkId] * jumlah;

        }

    });

    document.getElementById('totalHarga').innerHTML =
        formatRupiah(total);

    return total;
}


/* =========================
   HITUNG KEMBALIAN
========================= */

function hitungKembalian() {

    const total =
        hitungTotal();

    const bayar =
        parseInt(uangBayar.value) || 0;

    const hasil =
        bayar - total;

    if (hasil >= 0) {

        kembalian.innerHTML =
            formatRupiah(hasil);

        kembalian.style.background =
            '#dcfce7';

        kembalian.style.color =
            '#15803d';

    } else {

        kembalian.innerHTML =
            'Uang kurang ' +
            formatRupiah(Math.abs(hasil));

        kembalian.style.background =
            '#fee2e2';

        kembalian.style.color =
            '#dc2626';

    }
}


/* =========================
   ELEMENT PEMBAYARAN
========================= */

const metodePembayaran =
    document.getElementById('metode_pembayaran');

const cashBox =
    document.getElementById('cashBox');

const uangBayar =
    document.getElementById('uang_bayar');

const kembalian =
    document.getElementById('kembalian');


/* =========================
   EVENT METODE PEMBAYARAN
========================= */

metodePembayaran.addEventListener(
    'change',
    function() {

        cashBox.style.display =
            'none';

        uangBayar.required =
            false;

        if (this.value === 'cash') {

            cashBox.style.display =
                'block';

            uangBayar.required =
                true;

            hitungKembalian();

        }

    }
);


/* =========================
   EVENT JUMLAH PRODUK
========================= */

document.addEventListener(
    'input',
    function(event) {

        if (
            event.target.name ===
            'jumlah_produk[]'
        ) {

            hitungTotal();

            if (
                metodePembayaran.value ===
                'cash'
            ) {

                hitungKembalian();

            }

        }

    }
);


/* =========================
   EVENT PILIH PRODUK
========================= */

document.addEventListener(
    'change',
    function(event) {

        if (
            event.target.name ===
            'produk_id[]'
        ) {

            hitungTotal();

            if (
                metodePembayaran.value ===
                'cash'
            ) {

                hitungKembalian();

            }

        }

    }
);


/* =========================
   EVENT UANG DIBAYAR
========================= */

uangBayar.addEventListener(
    'input',
    function() {

        hitungKembalian();

    }
);


/* =========================
   CEK SEBELUM SIMPAN
========================= */

document
    .getElementById('formTransaksi')
    .addEventListener(
        'submit',
        function(event) {

            const total =
                hitungTotal();

            if (total <= 0) {

                event.preventDefault();

                alert(
                    'Silakan pilih produk dan masukkan jumlah produk terlebih dahulu.'
                );

                return;
            }

            if (
                metodePembayaran.value ===
                'cash'
            ) {

                const bayar =
                    parseInt(
                        uangBayar.value
                    ) || 0;

                if (bayar < total) {

                    event.preventDefault();

                    alert(
                        'Uang yang dibayar kurang dari total harga.'
                    );

                    uangBayar.focus();

                }

            }

        }
    );


/* =========================
   TAMPILKAN TOTAL SAAT HALAMAN DIBUKA
========================= */

hitungTotal();

</script>

</body>

</html>