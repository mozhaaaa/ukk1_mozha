<?php

session_start();
include "../koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['level'] != "kasir") {
    header("Location: ../login.php");
    exit;
}


/* =========================
   TAMBAH PRODUK KE KERANJANG
========================= */

if (isset($_POST['tambah_produk'])) {

    $nama_pelanggan = $_POST['nama_pelanggan'];
    $produk_id = $_POST['produk_id'];
    $jumlah = $_POST['jumlah_produk'];

    if ($jumlah <= 0) {
        header("Location: penjualan.php?pesan=jumlah");
        exit;
    }

    $produk = mysqli_query($koneksi, "
        SELECT * FROM produk
        WHERE produk_id='$produk_id'
    ");

    $p = mysqli_fetch_assoc($produk);

    if (!$p) {
        header("Location: penjualan.php?pesan=produk");
        exit;
    }

    if ($jumlah > $p['stok']) {
        header("Location: penjualan.php?pesan=stok");
        exit;
    }

    /*
     * Menyimpan nama pelanggan sementara
     */
    $_SESSION['nama_pelanggan_penjualan'] = $nama_pelanggan;

    /*
     * Membuat keranjang jika belum ada
     */
    if (!isset($_SESSION['keranjang_penjualan'])) {
        $_SESSION['keranjang_penjualan'] = [];
    }

    /*
     * Jika produk sudah ada di keranjang,
     * jumlahnya ditambahkan.
     */
    if (isset($_SESSION['keranjang_penjualan'][$produk_id])) {

        $jumlah_baru =
            $_SESSION['keranjang_penjualan'][$produk_id]['jumlah']
            + $jumlah;

        if ($jumlah_baru > $p['stok']) {
            header("Location: penjualan.php?pesan=stok");
            exit;
        }

        $_SESSION['keranjang_penjualan'][$produk_id]['jumlah'] = $jumlah_baru;

        $_SESSION['keranjang_penjualan'][$produk_id]['subtotal'] =
            $p['harga'] * $jumlah_baru;

    } else {

        $_SESSION['keranjang_penjualan'][$produk_id] = [
            'produk_id' => $p['produk_id'],
            'nama_produk' => $p['nama_produk'],
            'harga' => $p['harga'],
            'jumlah' => $jumlah,
            'subtotal' => $p['harga'] * $jumlah
        ];
    }

    header("Location: penjualan.php?ditambahkan=1");
    exit;
}


/* =========================
   HAPUS PRODUK DARI KERANJANG
========================= */

if (isset($_GET['hapus'])) {

    $hapus_id = $_GET['hapus'];

    if (isset($_SESSION['keranjang_penjualan'][$hapus_id])) {
        unset($_SESSION['keranjang_penjualan'][$hapus_id]);
    }

    header("Location: penjualan.php");
    exit;
}


/* =========================
   SIMPAN PENJUALAN
========================= */

if (isset($_POST['simpan'])) {

    if (
        !isset($_SESSION['keranjang_penjualan']) ||
        count($_SESSION['keranjang_penjualan']) == 0
    ) {
        header("Location: penjualan.php?pesan=keranjang");
        exit;
    }

    $nama_pelanggan = $_SESSION['nama_pelanggan_penjualan'];

    /*
     * Cari pelanggan berdasarkan nama
     */
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
            (nama_pelanggan, alamat, no_telepon)
            VALUES
            ('$nama_pelanggan','','')"
        )) {
            die("Pelanggan gagal disimpan: " . mysqli_error($koneksi));
        }

        $pelanggan_id = mysqli_insert_id($koneksi);
    }


    /* =========================
       HITUNG TOTAL
    ========================= */

    $total_harga = 0;

    foreach ($_SESSION['keranjang_penjualan'] as $item) {

        $total_harga += $item['subtotal'];
    }


    /* =========================
       SIMPAN DATA PENJUALAN
    ========================= */

    if (!mysqli_query(
        $koneksi,
        "INSERT INTO penjualan
        (tanggal_penjualan, total_harga, pelanggan_id)
        VALUES
        (NOW(), '$total_harga', '$pelanggan_id')"
    )) {
        die("Penjualan gagal disimpan: " . mysqli_error($koneksi));
    }

    $penjualan_id = mysqli_insert_id($koneksi);


    /* =========================
       SIMPAN DETAIL PENJUALAN
    ========================= */

    foreach ($_SESSION['keranjang_penjualan'] as $item) {

        $produk_id = $item['produk_id'];
        $jumlah = $item['jumlah'];
        $harga = $item['harga'];
        $subtotal = $item['subtotal'];

        /*
         * Simpan detail
         */
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

        /*
         * Kurangi stok produk
         */
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
       KOSONGKAN KERANJANG
    ========================= */

    unset($_SESSION['keranjang_penjualan']);
    unset($_SESSION['nama_pelanggan_penjualan']);


    header("Location: penjualan.php?berhasil=1");
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


/* =========================
   DATA KERANJANG
========================= */

$keranjang = [];

if (isset($_SESSION['keranjang_penjualan'])) {
    $keranjang = $_SESSION['keranjang_penjualan'];
}

$total_keranjang = 0;

foreach ($keranjang as $item) {
    $total_keranjang += $item['subtotal'];
}

$nama_pelanggan_sementara = "";

if (isset($_SESSION['nama_pelanggan_penjualan'])) {
    $nama_pelanggan_sementara =
        $_SESSION['nama_pelanggan_penjualan'];
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Penjualan Kasir</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

<div class="wrapper">

    <div class="sidebar">

        <div class="logo">

            <h2>Toko Sembako</h2>

            <p>Kasir</p>

        </div>

        <a href="index.php">Dashboard</a>

        <a href="penjualan.php">Penjualan</a>

        <a href="transaksi.php">Transaksi</a>

        <a href="riwayat.php">Riwayat</a>

        <a
            href="../logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>


    <div class="content">

        <div class="header">

            <h1>Penjualan</h1>

            <p>
                Kelola dan buat transaksi penjualan.
            </p>

        </div>


        <?php if (isset($_GET['berhasil'])) { ?>

            <div
                class="alert"
                style="background:#dcfce7;color:#15803d;"
            >

                Penjualan berhasil disimpan.

            </div>

        <?php } ?>


        <?php if (isset($_GET['ditambahkan'])) { ?>

            <div
                class="alert"
                style="background:#dcfce7;color:#15803d;"
            >

                Produk berhasil ditambahkan ke keranjang.

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

                } elseif ($_GET['pesan'] == "keranjang") {

                    echo "Silakan tambahkan produk terlebih dahulu.";

                }

                ?>

            </div>

        <?php } ?>


        <div class="box">

            <h2>Buat Penjualan</h2>


            <form method="POST">

                <div class="form-grid">


                    <div class="form-group">

                        <label>Nama Pelanggan</label>

                        <input
                            type="text"
                            name="nama_pelanggan"
                            placeholder="Masukkan nama pelanggan"
                            value="<?php
                            echo htmlspecialchars(
                                $nama_pelanggan_sementara
                            );
                            ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>Produk</label>

                        <select
                            name="produk_id"
                            required
                        >

                            <option value="">
                                Pilih Produk
                            </option>

                            <?php while (
                                $p = mysqli_fetch_assoc($produk)
                            ) { ?>

                                <option
                                    value="<?php
                                    echo $p['produk_id'];
                                    ?>"
                                >

                                    <?php
                                    echo $p['nama_produk'];
                                    ?>

                                    - Rp

                                    <?php
                                    echo number_format(
                                        $p['harga'],
                                        0,
                                        ',',
                                        '.'
                                    );
                                    ?>

                                </option>

                            <?php } ?>

                        </select>

                    </div>


                    <div class="form-group">

                        <label>Jumlah Produk</label>

                        <input
                            type="number"
                            name="jumlah_produk"
                            min="1"
                            placeholder="Masukkan jumlah produk"
                            required
                        >

                    </div>

                </div>


                <button
                    type="submit"
                    name="tambah_produk"
                    class="btn btn-primary"
                >
                    Tambah Produk
                </button>

            </form>

        </div>


        <?php if (count($keranjang) > 0) { ?>

        <div class="box">

            <h2>Daftar Produk Pembelian</h2>


            <div class="table-wrapper">

                <table>

                    <tr>

                        <th>No</th>

                        <th>Produk</th>

                        <th>Harga</th>

                        <th>Jumlah</th>

                        <th>Subtotal</th>

                        <th>Aksi</th>

                    </tr>


                    <?php

                    $no = 1;

                    foreach ($keranjang as $item) {

                    ?>

                    <tr>

                        <td>
                            <?php echo $no++; ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $item['nama_produk']
                            );
                            ?>
                        </td>

                        <td>
                            Rp
                            <?php
                            echo number_format(
                                $item['harga'],
                                0,
                                ',',
                                '.'
                            );
                            ?>
                        </td>

                        <td>
                            <?php echo $item['jumlah']; ?>
                        </td>

                        <td>
                            Rp
                            <?php
                            echo number_format(
                                $item['subtotal'],
                                0,
                                ',',
                                '.'
                            );
                            ?>
                        </td>

                        <td>

                            <a
                                href="penjualan.php?hapus=<?php
                                echo $item['produk_id'];
                                ?>"
                                class="btn"
                                onclick="return confirm(
                                    'Hapus produk ini dari pembelian?'
                                );"
                            >
                                Hapus
                            </a>

                        </td>

                    </tr>

                    <?php } ?>


                    <tr>

                        <th colspan="4">
                            TOTAL
                        </th>

                        <th colspan="2">

                            Rp
                            <?php
                            echo number_format(
                                $total_keranjang,
                                0,
                                ',',
                                '.'
                            );
                            ?>

                        </th>

                    </tr>

                </table>

            </div>


            <form method="POST">

                <button
                    type="submit"
                    name="simpan"
                    class="btn btn-primary"
                >
                    Simpan Penjualan
                </button>

            </form>

        </div>

        <?php } ?>


    </div>

</div>

</body>

</html>