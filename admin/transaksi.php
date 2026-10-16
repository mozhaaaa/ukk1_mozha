<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['level'] != "admin") {
    header("Location: ../login.php");
    exit;
}

$data = mysqli_query($koneksi, "
    SELECT transaksi.*, penjualan.total_harga
    FROM transaksi
    LEFT JOIN penjualan
    ON transaksi.penjualan_id = penjualan.penjualan_id
    ORDER BY transaksi.transaksi_id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Transaksi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
        <a href="penjualan.php">Penjualan</a>
        <a href="transaksi.php">Transaksi</a>
        <a href="laporan.php">Laporan</a>
        <a href="user.php">User</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>

    <div class="content">

        <div class="header">
            <h1>Data Transaksi</h1>
            <p>Kelola dan lihat transaksi pembayaran</p>
        </div>

        <div class="box">

            <h2>Daftar Transaksi</h2>

            <div class="table-wrapper">

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tanggal</th>
                            <th>Metode Pembayaran</th>
                            <th>Status</th>
                            <th>Penjualan</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($d = mysqli_fetch_assoc($data)) { ?>

                        <tr>
                            <td><?php echo $d['transaksi_id']; ?></td>

                            <td><?php echo $d['tanggal_transaksi']; ?></td>

                            <td><?php echo $d['metode_pembayaran']; ?></td>

                            <td>
                                <?php if ($d['status_transaksi'] == "selesai") { ?>
                                    <span class="badge badge-selesai">
                                        Selesai
                                    </span>
                                <?php } else { ?>
                                    <span class="badge badge-pending">
                                        <?php echo $d['status_transaksi']; ?>
                                    </span>
                                <?php } ?>
                            </td>

                            <td>
                                #<?php echo $d['penjualan_id']; ?>
                            </td>

                            <td>
                                Rp <?php echo number_format(
                                    $d['total_harga'],
                                    0,
                                    ',',
                                    '.'
                                ); ?>
                            </td>
                        </tr>

                    <?php } ?>
                    </tbody>
                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>