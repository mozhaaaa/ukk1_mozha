<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['level'] != "kasir") {
    header("Location: ../login.php");
    exit;
}

$data = mysqli_query($koneksi, "
    SELECT
        penjualan.penjualan_id,
        penjualan.tanggal_penjualan,
        penjualan.total_harga,
        pelanggan.nama_pelanggan,
        transaksi.tanggal_transaksi,
        transaksi.metode_pembayaran,
        transaksi.status_transaksi,
        SUM(detail_penjualan.jumlah_produk) AS jumlah_produk,
        GROUP_CONCAT(
            CONCAT(
                produk.nama_produk,
                ' (',
                detail_penjualan.jumlah_produk,
                'x)'
            )
            SEPARATOR '||'
        ) AS produk_dibeli
    FROM penjualan

    LEFT JOIN pelanggan
        ON penjualan.pelanggan_id = pelanggan.pelanggan_id

    LEFT JOIN transaksi
        ON penjualan.penjualan_id = transaksi.penjualan_id

    LEFT JOIN detail_penjualan
        ON penjualan.penjualan_id = detail_penjualan.penjualan_id

    LEFT JOIN produk
        ON detail_penjualan.produk_id = produk.produk_id

    GROUP BY
        penjualan.penjualan_id,
        penjualan.tanggal_penjualan,
        penjualan.total_harga,
        pelanggan.nama_pelanggan,
        transaksi.tanggal_transaksi,
        transaksi.metode_pembayaran,
        transaksi.status_transaksi

    ORDER BY penjualan.penjualan_id DESC
");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Riwayat Penjualan</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .produk-list {
            line-height: 1.8;
        }

        .produk-item {
            padding: 2px 0;
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

        <a href="../logout.php" class="logout">
            Logout
        </a>

    </div>

    <div class="content">

        <div class="header">

            <h1>Riwayat Penjualan</h1>

            <p>
                Daftar penjualan dan pembayaran
            </p>

        </div>

        <div class="box">

            <h2>Riwayat Transaksi</h2>

            <div class="table-wrapper">

                <table>

                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Produk Dibeli</th>
                        <th>Jumlah Produk</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Status</th>
                    </tr>

                    <?php while ($d = mysqli_fetch_assoc($data)) { ?>

                    <tr>

                        <td>
                            #<?php echo $d['penjualan_id']; ?>
                        </td>

                        <td>
                            <?php echo $d['tanggal_penjualan']; ?>
                        </td>

                        <td>
                            <?php
                            echo $d['nama_pelanggan']
                                ? $d['nama_pelanggan']
                                : "Umum";
                            ?>
                        </td>

                        <td>
                            <div class="produk-list">

                                <?php
                                if ($d['produk_dibeli']) {

                                    $produk = explode(
                                        '||',
                                        $d['produk_dibeli']
                                    );

                                    foreach ($produk as $item) {
                                        echo '<div class="produk-item">• '
                                            . $item
                                            . '</div>';
                                    }

                                } else {
                                    echo "-";
                                }
                                ?>

                            </div>
                        </td>

                        <td>
                            <?php echo $d['jumlah_produk']; ?>
                        </td>

                        <td>
                            Rp <?php
                            echo number_format(
                                $d['total_harga'],
                                0,
                                ',',
                                '.'
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo $d['metode_pembayaran']
                                ? $d['metode_pembayaran']
                                : "-";
                            ?>
                        </td>

                        <td>

                            <?php if ($d['status_transaksi']) { ?>

                                <?php if ($d['status_transaksi'] == "selesai") { ?>

                                    <span class="badge badge-selesai">
                                        Selesai
                                    </span>

                                <?php } else { ?>

                                    <span class="badge badge-pending">
                                        <?php echo $d['status_transaksi']; ?>
                                    </span>

                                <?php } ?>

                            <?php } else { ?>

                                <span class="badge badge-pending">
                                    Belum Dibayar
                                </span>

                            <?php } ?>

                        </td>

                    </tr>

                    <?php } ?>

                </table>

            </div>

        </div>

    </div>

</div>

</body>

</html>