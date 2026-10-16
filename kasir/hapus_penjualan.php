<?php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['level'] != "kasir") {
    header("Location: ../login.php");
    exit;
}

include "../koneksi.php";


/* =========================
   CEK ID PENJUALAN
========================= */

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$penjualan_id = $_GET['id'];


/* =========================
   AMBIL DETAIL PENJUALAN
========================= */

$detail = mysqli_query(
    $koneksi,
    "SELECT * FROM detail_penjualan
     WHERE penjualan_id='$penjualan_id'"
);

if (!$detail) {
    die(
        "Gagal mengambil detail penjualan: "
        . mysqli_error($koneksi)
    );
}


/* =========================
   KEMBALIKAN STOK PRODUK
========================= */

while ($d = mysqli_fetch_assoc($detail)) {

    $produk_id = $d['produk_id'];
    $jumlah = $d['jumlah_produk'];

    if (!mysqli_query(
        $koneksi,
        "UPDATE produk
         SET stok = stok + $jumlah
         WHERE produk_id='$produk_id'"
    )) {

        die(
            "Gagal mengembalikan stok: "
            . mysqli_error($koneksi)
        );

    }
}


/* =========================
   HAPUS DETAIL PENJUALAN
========================= */

if (!mysqli_query(
    $koneksi,
    "DELETE FROM detail_penjualan
     WHERE penjualan_id='$penjualan_id'"
)) {

    die(
        "Gagal menghapus detail penjualan: "
        . mysqli_error($koneksi)
    );

}


/* =========================
   HAPUS TRANSAKSI
========================= */

if (!mysqli_query(
    $koneksi,
    "DELETE FROM transaksi
     WHERE penjualan_id='$penjualan_id'"
)) {

    die(
        "Gagal menghapus transaksi: "
        . mysqli_error($koneksi)
    );

}


/* =========================
   HAPUS PENJUALAN
========================= */

if (!mysqli_query(
    $koneksi,
    "DELETE FROM penjualan
     WHERE penjualan_id='$penjualan_id'"
)) {

    die(
        "Gagal menghapus penjualan: "
        . mysqli_error($koneksi)
    );

}


/* =========================
   KEMBALI KE DASHBOARD
========================= */

header("Location: index.php");

exit;

?>