<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['level'] != "admin") {
    header("Location: ../login.php");
    exit;
}

/* =====================================================
   HAPUS PELANGGAN BERDASARKAN ID
   SEKALIGUS HAPUS DATA PENJUALAN & TRANSAKSI TERKAIT
   (OPTIMASI: Langsung tanpa loop)
===================================================== */

if (isset($_GET['hapus_pelanggan'])) {

    $pelanggan_id = mysqli_real_escape_string(
        $koneksi,
        $_GET['hapus_pelanggan']
    );

    /*
     * Mulai transaksi database
     */
    mysqli_begin_transaction($koneksi);

    try {

        /*
         * 1. Hapus transaksi terkait pelanggan ini
         */
        if (!mysqli_query(
            $koneksi,
            "DELETE FROM transaksi 
             WHERE penjualan_id IN (
                 SELECT penjualan_id FROM penjualan WHERE pelanggan_id='$pelanggan_id'
             )"
        )) {
            throw new Exception("Gagal menghapus transaksi: " . mysqli_error($koneksi));
        }

        /*
         * 2. Hapus detail penjualan terkait pelanggan ini
         */
        if (!mysqli_query(
            $koneksi,
            "DELETE FROM detail_penjualan 
             WHERE penjualan_id IN (
                 SELECT penjualan_id FROM penjualan WHERE pelanggan_id='$pelanggan_id'
             )"
        )) {
            throw new Exception("Gagal menghapus detail penjualan: " . mysqli_error($koneksi));
        }

        /*
         * 3. Hapus semua penjualan milik pelanggan ini
         */
        if (!mysqli_query(
            $koneksi,
            "DELETE FROM penjualan 
             WHERE pelanggan_id='$pelanggan_id'"
        )) {
            throw new Exception("Gagal menghapus penjualan: " . mysqli_error($koneksi));
        }

        /*
         * 4. Terakhir hapus pelanggan
         */
        if (!mysqli_query(
            $koneksi,
            "DELETE FROM pelanggan 
             WHERE pelanggan_id='$pelanggan_id'"
        )) {
            throw new Exception("Gagal menghapus pelanggan: " . mysqli_error($koneksi));
        }

        /*
         * Jika semua berhasil, simpan perubahan
         */
        mysqli_commit($koneksi);

    } catch (Exception $e) {

        /*
         * Jika ada yang gagal, batalkan semua proses penghapusan
         */
        mysqli_rollback($koneksi);

        die($e->getMessage());
    }

    header("Location: index.php?hapus=berhasil");
    exit;
}

$jml_pelanggan = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pelanggan")
)['total'];

$jml_produk = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM produk")
)['total'];

$jml_transaksi = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM transaksi")
)['total'];

// TOTAL PENDAPATAN HARI INI
$pendapatan_hari_ini = mysqli_fetch_assoc(
    mysqli_query($koneksi, "
        SELECT COALESCE(SUM(total_harga), 0) AS total
        FROM penjualan
        WHERE DATE(tanggal_penjualan) = CURDATE()
    ")
)['total'];

// TOTAL PENDAPATAN SEMUA
$pendapatan_semua = mysqli_fetch_assoc(
    mysqli_query($koneksi, "
        SELECT COALESCE(SUM(total_harga), 0) AS total
        FROM penjualan
    ")
)['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        /* =====================================================
           KOTAK DASHBOARD AGAR 5 KOTAK MUAT SATU BARIS
        ===================================================== */
        .card-container {
            grid-template-columns: repeat(5, 1fr) !important;
            gap: 12px !important;
        }

        .card-container .card {
            padding: 16px !important;
            min-width: 0 !important;
        }

        .card-container .card h3 {
            font-size: 13px !important;
            margin-bottom: 6px !important;
            white-space: nowrap;
        }

        .card-container .card .angka {
            font-size: 19px !important;
            white-space: nowrap;
        }

        .notifikasi-berhasil {
            background: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 14px;
            font-weight: 600;
        }

        /* =====================================================
           RESPONSIVE
        ===================================================== */
        @media (max-width: 1100px) {
            .card-container {
                grid-template-columns: repeat(3, 1fr) !important;
            }
        }

        @media (max-width: 700px) {
            .card-container {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }

        @media (max-width: 500px) {
            .card-container {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</head>

<body>

<div class="wrapper">

    <div class="sidebar">

        <div class="logo">
            <h2>Toko Sembako</h2>
            <p>Admin</p>
        </div>

        <a href="index.php">Dashboard</a>
        <a href="pelanggan.php">Pelanggan</a>
        <a href="produk.php">Produk</a>
        <a href="laporan.php">Laporan</a>
        <a href="user.php">User</a>

        <a href="../logout.php" class="logout">Logout</a>

    </div>

    <div class="content">

        <div class="header">
            <h1>Dashboard Admin</h1>
            <p>Selamat datang, <?php echo $_SESSION['username']; ?></p>
        </div>

        <?php if (isset($_GET['hapus']) && $_GET['hapus'] == "berhasil") { ?>
            <div class="notifikasi-berhasil">
                ✓ Admin berhasil menghapus.
            </div>
        <?php } ?>

        <div class="card-container">

            <div class="card">
                <h3>Total Pelanggan</h3>
                <div class="angka"><?php echo $jml_pelanggan; ?></div>
            </div>

            <div class="card">
                <h3>Total Produk</h3>
                <div class="angka"><?php echo $jml_produk; ?></div>
            </div>

            <div class="card">
                <h3>Total Transaksi</h3>
                <div class="angka"><?php echo $jml_transaksi; ?></div>
            </div>

            <div class="card">
                <h3>Pendapatan Hari Ini</h3>
                <div class="angka">
                    Rp <?php echo number_format($pendapatan_hari_ini, 0, ',', '.'); ?>
                </div>
            </div>

            <div class="card">
                <h3>Total Pendapatan</h3>
                <div class="angka">
                    Rp <?php echo number_format($pendapatan_semua, 0, ',', '.'); ?>
                </div>
            </div>

        </div>

    </div>

</div>

</body>
</html>