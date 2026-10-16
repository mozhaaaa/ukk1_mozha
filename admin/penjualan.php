<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['level'] != "admin") {
    header("Location: ../login.php");
    exit;
}

$data = mysqli_query($koneksi, "
    SELECT
        penjualan.penjualan_id,
        penjualan.tanggal_penjualan,
        penjualan.total_harga,
        pelanggan.nama_pelanggan,
        GROUP_CONCAT(
            CONCAT(
                produk.nama_produk,
                ' x',
                detail_penjualan.jumlah_produk
            )
            SEPARATOR '||'
        ) AS detail_pembelian
    FROM penjualan
    INNER JOIN pelanggan
        ON penjualan.pelanggan_id = pelanggan.pelanggan_id
    LEFT JOIN detail_penjualan
        ON penjualan.penjualan_id = detail_penjualan.penjualan_id
    LEFT JOIN produk
        ON detail_penjualan.produk_id = produk.produk_id
    GROUP BY
        penjualan.penjualan_id,
        penjualan.tanggal_penjualan,
        penjualan.total_harga,
        pelanggan.nama_pelanggan
    ORDER BY penjualan.penjualan_id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Penjualan</title>
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
            <h1>Data Penjualan</h1>
            <p>Daftar seluruh data penjualan</p>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Detail Pembelian</th>
                        <th>Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($d = mysqli_fetch_assoc($data)) { ?>
                    <tr>
                        <td>#<?php echo $d['penjualan_id']; ?></td>
                        <td><?php echo $d['tanggal_penjualan']; ?></td>
                        <td>
                            <?php
                            echo $d['nama_pelanggan']
                                ? htmlspecialchars($d['nama_pelanggan'])
                                : "Umum";
                            ?>
                        </td>
                        <td>
                            <?php
                            if ($d['detail_pembelian']) {
                                $detail = explode('||', $d['detail_pembelian']);
                                foreach ($detail as $item) {
                                    echo '<div>' . htmlspecialchars($item) . '</div>';
                                }
                            } else {
                                echo "-";
                            }
                            ?>
                        </td>
                        <td>
                            Rp <?php echo number_format($d['total_harga'], 0, ',', '.'); ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>