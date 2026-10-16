<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['level'] != "admin") {
    header("Location: ../login.php");
    exit;
}

include "../koneksi.php";

/* =====================================================
   DATA PELANGGAN YANG MEMBELI
===================================================== */

$data_pelanggan_membeli = mysqli_query(
    $koneksi,
    "
    SELECT
        pelanggan.pelanggan_id,
        pelanggan.nama_pelanggan,
        penjualan.penjualan_id,
        penjualan.tanggal_penjualan,
        penjualan.total_harga,
        GROUP_CONCAT(
            CONCAT(
                produk.nama_produk,
                ' x',
                detail_penjualan.jumlah_produk
            )
            SEPARATOR '||'
        ) AS detail_pembelian,
        transaksi.metode_pembayaran,
        transaksi.status_transaksi
    FROM pelanggan

    INNER JOIN penjualan
        ON pelanggan.pelanggan_id = penjualan.pelanggan_id

    LEFT JOIN detail_penjualan
        ON penjualan.penjualan_id = detail_penjualan.penjualan_id

    LEFT JOIN produk
        ON detail_penjualan.produk_id = produk.produk_id

    LEFT JOIN transaksi
        ON penjualan.penjualan_id = transaksi.penjualan_id

    GROUP BY
        pelanggan.pelanggan_id,
        pelanggan.nama_pelanggan,
        penjualan.penjualan_id,
        penjualan.tanggal_penjualan,
        penjualan.total_harga,
        transaksi.metode_pembayaran,
        transaksi.status_transaksi

    ORDER BY penjualan.penjualan_id DESC
    "
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Pelanggan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* =====================================================
           DATA PELANGGAN YANG MEMBELI
        ===================================================== */

        .data-pembelian-box {
            background: white;
            padding: 15px;
            margin-top: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .data-pembelian-box h2 {
            margin-bottom: 4px;
            font-size: 18px;
        }

        .data-pembelian-box p {
            color: #777;
            margin-bottom: 12px;
            font-size: 13px;
        }

        .data-pembelian-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .data-pembelian-wrapper table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
            font-size: 12px;
        }

        .data-pembelian-wrapper th,
        .data-pembelian-wrapper td {
            padding: 7px 8px;
            border-bottom: 1px solid #eee;
            text-align: left;
            vertical-align: middle;
        }

        .data-pembelian-wrapper th {
            background: #f5f7fa;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }

        .data-pembelian-wrapper td {
            line-height: 1.4;
        }

        .detail-pembelian {
            line-height: 1.4;
        }

        .detail-pembelian div {
            margin-bottom: 2px;
        }

        .status-pembelian {
            display: inline-block;
            padding: 4px 7px;
            border-radius: 4px;
            background: #e5e7eb;
            color: #374151;
            font-size: 10px;
            font-weight: 600;
            white-space: nowrap;
        }

        .btn-hapus-pelanggan {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 9px;
            border-radius: 5px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            font-size: 11px;
            border: none;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn-hapus-pelanggan:hover {
            background: #c82333;
        }

        /* Lebar kolom agar lebih rapi */
        .data-pembelian-wrapper th:nth-child(1),
        .data-pembelian-wrapper td:nth-child(1) {
            width: 70px;
        }

        .data-pembelian-wrapper th:nth-child(2),
        .data-pembelian-wrapper td:nth-child(2) {
            width: 120px;
        }

        .data-pembelian-wrapper th:nth-child(3),
        .data-pembelian-wrapper td:nth-child(3) {
            width: 80px;
        }

        .data-pembelian-wrapper th:nth-child(4),
        .data-pembelian-wrapper td:nth-child(4) {
            width: 130px;
        }

        .data-pembelian-wrapper th:nth-child(5),
        .data-pembelian-wrapper td:nth-child(5) {
            width: 180px;
        }

        .data-pembelian-wrapper th:nth-child(6),
        .data-pembelian-wrapper td:nth-child(6) {
            width: 110px;
        }

        .data-pembelian-wrapper th:nth-child(7),
        .data-pembelian-wrapper td:nth-child(7) {
            width: 110px;
        }

        .data-pembelian-wrapper th:nth-child(8),
        .data-pembelian-wrapper td:nth-child(8) {
            width: 80px;
        }

        .data-pembelian-wrapper th:nth-child(9),
        .data-pembelian-wrapper td:nth-child(9) {
            width: 65px;
            text-align: center;
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
            <h1>Data Pelanggan</h1>
            <p>Daftar pelanggan beserta data pembelian yang dilakukan</p>
        </div>

        <div class="data-pembelian-box">
            <h2>Data Pelanggan yang Membeli</h2>
            <p>Daftar pelanggan beserta data pembelian yang dilakukan.</p>

            <div class="data-pembelian-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID Pelanggan</th>
                            <th>Nama Pelanggan</th>
                            <th>ID Penjualan</th>
                            <th>Tanggal</th>
                            <th>Produk yang Dibeli</th>
                            <th>Total Harga</th>
                            <th>Metode Pembayaran</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($d = mysqli_fetch_assoc($data_pelanggan_membeli)) { ?>
                        <tr>
                            <td><?php echo $d['pelanggan_id']; ?></td>
                            <td><?php echo htmlspecialchars($d['nama_pelanggan']); ?></td>
                            <td>#<?php echo $d['penjualan_id']; ?></td>
                            <td><?php echo $d['tanggal_penjualan']; ?></td>
                            <td>
                                <div class="detail-pembelian">
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
                                </div>
                            </td>
                            <td>Rp <?php echo number_format($d['total_harga'], 0, ',', '.'); ?></td>
                            <td>
                                <?php
                                echo $d['metode_pembayaran']
                                    ? htmlspecialchars($d['metode_pembayaran'])
                                    : "-";
                                ?>
                            </td>
                            <td>
                                <span class="status-pembelian">
                                    <?php
                                    echo $d['status_transaksi']
                                        ? htmlspecialchars($d['status_transaksi'])
                                        : "-";
                                    ?>
                                </span>
                            </td>
                            <td>
                                <a href="index.php?hapus_pelanggan=<?php echo $d['pelanggan_id']; ?>"
                                   class="btn-hapus-pelanggan"
                                   onclick="return confirm('Apakah kamu yakin ingin menghapus pelanggan ID <?php echo $d['pelanggan_id']; ?> beserta seluruh data pembeliannya?');">
                                    Hapus
                                </a>
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