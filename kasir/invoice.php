<?php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['level'] != "kasir") {
    header("Location: ../login.php");
    exit;
}

include "../koneksi.php";

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$penjualan_id = $_GET['id'];


/* =========================
   DATA PENJUALAN
========================= */

$penjualan = mysqli_query(
    $koneksi,
    "
    SELECT
        penjualan.*,
        pelanggan.nama_pelanggan
    FROM penjualan
    LEFT JOIN pelanggan
    ON penjualan.pelanggan_id = pelanggan.pelanggan_id
    WHERE penjualan.penjualan_id='$penjualan_id'
    "
);

$data_penjualan = mysqli_fetch_assoc($penjualan);

if (!$data_penjualan) {
    echo "Data penjualan tidak ditemukan.";
    exit;
}


/* =========================
   DETAIL PRODUK
========================= */

$detail = mysqli_query(
    $koneksi,
    "
    SELECT
        detail_penjualan.*,
        produk.nama_produk
    FROM detail_penjualan
    LEFT JOIN produk
    ON detail_penjualan.produk_id = produk.produk_id
    WHERE detail_penjualan.penjualan_id='$penjualan_id'
    "
);


/* =========================
   DATA TRANSAKSI
========================= */

$transaksi = mysqli_query(
    $koneksi,
    "
    SELECT *
    FROM transaksi
    WHERE penjualan_id='$penjualan_id'
    LIMIT 1
    "
);

$data_transaksi = mysqli_fetch_assoc($transaksi);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Nota Pembelian</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 30px;
        }

        .nota {
            width: 500px;
            margin: auto;
            background: white;
            padding: 25px;
            box-sizing: border-box;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .nota-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .nota-header h2 {
            margin: 0 0 5px 0;
        }

        .nota-header p {
            margin: 0;
            font-size: 14px;
            color: #666;
        }

        .garis {
            border-top: 1px dashed #999;
            margin: 15px 0;
        }

        .info {
            font-size: 14px;
            line-height: 1.7;
        }

        .info div {
            display: flex;
        }

        .info .label {
            width: 130px;
            font-weight: bold;
        }

        .produk {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 14px;
        }

        .produk th {
            text-align: left;
            border-bottom: 1px solid #333;
            padding: 8px 4px;
        }

        .produk td {
            padding: 8px 4px;
            border-bottom: 1px solid #eee;
        }

        .text-right {
            text-align: right;
        }

        .total {
            display: flex;
            justify-content: space-between;
            font-size: 17px;
            font-weight: bold;
            margin-top: 15px;
        }

        .pembayaran {
            margin-top: 10px;
            font-size: 14px;
        }

        .pembayaran div {
            display: flex;
            justify-content: space-between;
            line-height: 1.8;
        }

        .pembayaran .kembali {
            font-weight: bold;
        }

        .ketentuan {
            text-align: center;
            margin-top: 15px;
            font-size: 12px;
            line-height: 1.5;
            color: #666;
        }

        .terima-kasih {
            text-align: center;
            margin-top: 25px;
            font-size: 13px;
            color: #555;
        }

        .tombol {
            text-align: center;
            margin-top: 25px;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            margin: 3px;
            border-radius: 5px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-cetak {
            background: #2563eb;
            color: white;
        }

        .btn-kembali {
            background: #6b7280;
            color: white;
        }

        @media print {

            body {
                background: white;
                padding: 0;
            }

            .nota {
                width: 100%;
                box-shadow: none;
            }

            .tombol {
                display: none;
            }

        }

    </style>

</head>

<body>


<div class="nota">


    <!-- HEADER NOTA -->

    <div class="nota-header">

        <h2>TOKO SEMBAKO</h2>

        <p>NOTA PEMBELIAN</p>

    </div>


    <div class="garis"></div>


    <!-- INFORMASI PEMBELIAN -->

    <div class="info">

        <div>
            <span class="label">ID Penjualan</span>
            :
            #<?php echo $data_penjualan['penjualan_id']; ?>
        </div>

        <div>
            <span class="label">Tanggal</span>
            :
            <?php echo $data_penjualan['tanggal_penjualan']; ?>
        </div>

        <div>
            <span class="label">Pelanggan</span>
            :
            <?php echo htmlspecialchars(
                $data_penjualan['nama_pelanggan']
            ); ?>
        </div>

        <?php if ($data_transaksi) { ?>

            <div>
                <span class="label">Pembayaran</span>
                :
                <?php echo htmlspecialchars(
                    $data_transaksi['metode_pembayaran']
                ); ?>
            </div>

        <?php } ?>

    </div>


    <div class="garis"></div>


    <!-- DAFTAR PRODUK -->

    <table class="produk">

        <tr>

            <th>Produk</th>

            <th>Harga</th>

            <th>Qty</th>

            <th class="text-right">Subtotal</th>

        </tr>


        <?php while ($d = mysqli_fetch_assoc($detail)) { ?>

        <tr>

            <td>
                <?php
                echo htmlspecialchars(
                    $d['nama_produk']
                );
                ?>
            </td>

            <td>
                Rp <?php
                echo number_format(
                    $d['harga'],
                    0,
                    ',',
                    '.'
                );
                ?>
            </td>

            <td>
                <?php echo $d['jumlah_produk']; ?>
            </td>

            <td class="text-right">
                Rp <?php
                echo number_format(
                    $d['subtotal'],
                    0,
                    ',',
                    '.'
                );
                ?>
            </td>

        </tr>

        <?php } ?>

    </table>


    <div class="garis"></div>


    <!-- TOTAL -->

    <div class="total">

        <span>Total</span>

        <span>
            Rp <?php
            echo number_format(
                $data_penjualan['total_harga'],
                0,
                ',',
                '.'
            );
            ?>
        </span>

    </div>


    <!-- PEMBAYARAN -->

    <?php if ($data_transaksi && $data_transaksi['metode_pembayaran'] == "cash") { ?>

    <div class="pembayaran">

        <div>
            <span>Uang Dibayar</span>
            <span>
                Rp <?php
                echo number_format(
                    $data_transaksi['uang_bayar'],
                    0,
                    ',',
                    '.'
                );
                ?>
            </span>
        </div>

        <div class="kembali">
            <span>Kembali</span>
            <span>
                Rp <?php
                echo number_format(
                    $data_transaksi['kembalian'],
                    0,
                    ',',
                    '.'
                );
                ?>
            </span>
        </div>

    </div>

    <?php } ?>


    <!-- KETENTUAN PEMBELIAN -->

    <div class="ketentuan">

        Barang yang sudah dibeli tidak dapat dikembalikan atau ditukar,
        kecuali terdapat kesalahan dari pihak toko.

    </div>


    <div class="terima-kasih">

        Terima kasih telah berbelanja.

    </div>


    <!-- TOMBOL -->

    <div class="tombol">

        <button
            onclick="window.print()"
            class="btn btn-cetak"
        >
            Cetak Nota
        </button>

        <a
            href="index.php"
            class="btn btn-kembali"
        >
            Kembali
        </a>

    </div>


</div>


</body>

</html>