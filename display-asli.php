<?php
// Koneksi ke database
$hostname = "localhost";
$username = "root";
$password = "";
$dbname = "rekap_kp";

$conn = new mysqli($hostname, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil status dari query parameter
$status = isset($_GET['status']) ? $_GET['status'] : '';

if ($status === 'berhasil') {
    $statusMessage = "<h3 class='status-success'>Proses Upload Berhasil!</h3>";
} elseif ($status === 'gagal') {
    $statusMessage = "<h3 class='status-error'>Proses Upload Gagal!</h3>";
} else {
    $statusMessage = "<h3>Status tidak dikenal</h3>";
}

// Ambil nama verifikator, instansi kerja, dan tahun untuk dropdown
$verifikatorQuery = "SELECT DISTINCT verifikator_nama FROM kenaikan_pangkat ORDER BY verifikator_nama";
$verifikatorResult = $conn->query($verifikatorQuery);

$instansiQuery = "SELECT DISTINCT instansi_kerja FROM kenaikan_pangkat ORDER BY instansi_kerja";
$instansiResult = $conn->query($instansiQuery);

$statusUsulanQuery = "SELECT DISTINCT status_usulan FROM kenaikan_pangkat ORDER BY status_usulan";
$statusUsulanResult = $conn->query($statusUsulanQuery);

$tahunQuery = "SELECT DISTINCT YEAR(tgl_ttd_pertek) AS tahun FROM kenaikan_pangkat ORDER BY tahun";
$tahunResult = $conn->query($tahunQuery);

// Filter data
$filterVerifikator = isset($_POST['verifikator']) ? $_POST['verifikator'] : '';
$filterInstansi = isset($_POST['instansi']) ? $_POST['instansi'] : '';
$filterStatusUsulan = isset($_POST['status_usulan']) ? $_POST['status_usulan'] : '';
$filterTahun = isset($_POST['tahun']) ? $_POST['tahun'] : '';

$filterQuery = "SELECT verifikator_nama, instansi_kerja, status_usulan, tgl_ttd_pertek FROM kenaikan_pangkat WHERE 1=1";
if ($filterVerifikator) {
    $filterQuery .= " AND verifikator_nama = '" . $conn->real_escape_string($filterVerifikator) . "'";
}
if ($filterInstansi) {
    $filterQuery .= " AND instansi_kerja = '" . $conn->real_escape_string($filterInstansi) . "'";
}
if ($filterStatusUsulan) {
    $filterQuery .= " AND status_usulan = '" . $conn->real_escape_string($filterStatusUsulan) . "'";
}
if ($filterTahun) {
    $filterQuery .= " AND YEAR(tgl_ttd_pertek) = '" . $conn->real_escape_string($filterTahun) . "'";
}
$result = $conn->query($filterQuery);

$totalData = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data yang Diunggah</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }
        h1, h2, h3 {
            color: #333;
        }
        .status-success {
            color: #4CAF50;
            background-color: #e8f5e9;
            padding: 10px;
            border: 1px solid #4CAF50;
            border-radius: 5px;
            display: inline-block;
        }
        .status-error {
            color: #F44336;
            background-color: #ffebee;
            padding: 10px;
            border: 1px solid #F44336;
            border-radius: 5px;
            display: inline-block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #fff;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .filter-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .filter-form label {
            margin-right: 10px;
        }
        .filter-form select {
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 3px;
            margin-right: 15px;
        }
        .total-data {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #e0f7fa;
            border: 1px solid #00acc1;
            border-radius: 5px;
        }
        .total-data span {
            color: #00796b;
        }
        .button-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #00796b;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .button-link:hover {
            background-color: #004d40;
        }
    </style>
</head>
<body>
    <h1 align="center">Rekapitulasi Verifikasi Kenaikan Pangkat</h1>
    <a href="index.php" class="button-link">Home</a>
    <?php echo $statusMessage; ?>

    <div class="filter-form">
        <form action="" method="post">
            <label for="verifikator">Nama Verifikator:</label>
            <select name="verifikator" id="verifikator">
                <option value="">Semua</option>
                <?php while ($row = $verifikatorResult->fetch_assoc()): ?>
                    <option value="<?php echo $row['verifikator_nama']; ?>" <?php echo ($row['verifikator_nama'] === $filterVerifikator) ? 'selected' : ''; ?>>
                        <?php echo $row['verifikator_nama']; ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="instansi">Instansi Kerja:</label>
            <select name="instansi" id="instansi">
                <option value="">Semua</option>
                <?php while ($row = $instansiResult->fetch_assoc()): ?>
                    <option value="<?php echo $row['instansi_kerja']; ?>" <?php echo ($row['instansi_kerja'] === $filterInstansi) ? 'selected' : ''; ?>>
                        <?php echo $row['instansi_kerja']; ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="status_usulan">Status Usulan:</label>
            <select name="status_usulan" id="status_usulan">
                <option value="">Semua</option>
                <?php while ($row = $statusUsulanResult->fetch_assoc()): ?>
                    <option value="<?php echo $row['status_usulan']; ?>" <?php echo ($row['status_usulan'] === $filterStatusUsulan) ? 'selected' : ''; ?>>
                        <?php echo $row['status_usulan']; ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="tahun">Tahun:</label>
            <select name="tahun" id="tahun">
                <option value="">Semua</option>
                <?php while ($row = $tahunResult->fetch_assoc()): ?>
                    <option value="<?php echo $row['tahun']; ?>" <?php echo ($row['tahun'] === $filterTahun) ? 'selected' : ''; ?>>
                        <?php echo $row['tahun']; ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <input type="submit" value="Filter">
        </form>
    </div>

    <div class="total-data">
        Total Data Verifikasi: <span><?php echo $totalData; ?></span>
    </div>

    <table>
        <tr>
            <th>Nama Verifikator</th>
            <th>Instansi Kerja</th>
            <th>Status Usulan</th>
            <th>Tahun TTD Pertek</th>
        </tr>
        
        <?php
        if ($result->num_rows > 0) {
            // Menampilkan data di tabel
            while ($row = $result->fetch_assoc()) {
                $tahun = date('Y', strtotime($row['tgl_ttd_pertek']));
                echo "<tr>";
                echo "<td>" . $row['verifikator_nama'] . "</td>";
                echo "<td>" . $row['instansi_kerja'] . "</td>";
                echo "<td>" . $row['status_usulan'] . "</td>";
                echo "<td>" . $tahun . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='3'>Tidak ada data yang ditemukan</td></tr>";
        }
        ?>
    </table>

    
</body>
</html>

<?php
$conn->close();
?>
