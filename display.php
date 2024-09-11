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

// Ambil nama verifikator, instansi kerja, status usulan, dan tahun untuk dropdown
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

// Query untuk total verifikasi per instansi
$instansiTotalQuery = "SELECT instansi_kerja, COUNT(*) AS total_verifikasi FROM kenaikan_pangkat WHERE 1=1";
if ($filterVerifikator) {
    $instansiTotalQuery .= " AND verifikator_nama = '" . $conn->real_escape_string($filterVerifikator) . "'";
}
if ($filterStatusUsulan) {
    $instansiTotalQuery .= " AND status_usulan = '" . $conn->real_escape_string($filterStatusUsulan) . "'";
}
if ($filterTahun) {
    $instansiTotalQuery .= " AND YEAR(tgl_ttd_pertek) = '" . $conn->real_escape_string($filterTahun) . "'";
}
$instansiTotalQuery .= " GROUP BY instansi_kerja ORDER BY total_verifikasi DESC";
$instansiTotalResult = $conn->query($instansiTotalQuery);

// Keterangan filter yang diterapkan
$filterDescription = "Filter aktif: ";
$filterDescription .= $filterVerifikator ? "Nama Verifikator: " . htmlspecialchars($filterVerifikator) . "; " : "";
$filterDescription .= $filterInstansi ? "Instansi Kerja: " . htmlspecialchars($filterInstansi) . "; " : "";
$filterDescription .= $filterStatusUsulan ? "Status Usulan: " . htmlspecialchars($filterStatusUsulan) . "; " : "";
$filterDescription .= $filterTahun ? "Tahun: " . htmlspecialchars($filterTahun) . ";" : "";

if (empty($filterDescription)) {
    $filterDescription = "Tidak ada filter aktif.";
}

// Tangani permintaan AJAX untuk detail instansi
if (isset($_GET['action']) && $_GET['action'] == 'getInstansiDetails') {
    $instansi_kerja = isset($_GET['instansi_kerja']) ? $_GET['instansi_kerja'] : '';

    // Query untuk mendapatkan nama dan NIP yang tidak memenuhi syarat berdasarkan instansi
    $detailsQuery = "SELECT nama, nip FROM kenaikan_pangkat WHERE instansi_kerja = '" . $conn->real_escape_string($instansi_kerja) . "' AND status_usulan = 'Tidak Memenuhi Syarat'";
    $detailsResult = $conn->query($detailsQuery);

    $details = [];
    while ($row = $detailsResult->fetch_assoc()) {
        $details[] = $row;
    }

    echo json_encode($details);
    exit;
}

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
        .instansi-summary {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .instansi-summary h3 {
            margin-top: 0;
        }
        .instansi-summary table {
            margin-top: 10px;
        }
        .instansi-details {
            display: none;
            margin-top: 20px;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .instansi-details h3 {
            margin-top: 0;
        }
        .instansi-details ul {
            list-style-type: none;
            padding: 0;
        }
        .instansi-details li {
            padding: 5px 0;
            border-bottom: 1px solid #ddd;
        }
        .instansi-details li:last-child {
            border-bottom: none;
        }
        .instansi-link {
            color: #00796b;
            text-decoration: none;
        }
        .instansi-link:hover {
            text-decoration: underline;
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

    <!-- Detail total verifikasi per instansi -->
    <div class="instansi-summary">
        <h3>Detail Total Verifikasi Per Instansi:</h3>
        <p><?php echo $filterDescription; ?></p>
        <table>
            <tr>
                <th>Instansi Kerja</th>
                <th>Total Verifikasi</th>
            </tr>
            <?php
            if ($instansiTotalResult->num_rows > 0) {
                while ($row = $instansiTotalResult->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td><a href='#' class='instansi-link' data-instansi='" . htmlspecialchars($row['instansi_kerja']) . "'>" . htmlspecialchars($row['instansi_kerja']) . "</a></td>";
                    echo "<td>" . htmlspecialchars($row['total_verifikasi']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='2'>Tidak ada data yang ditemukan</td></tr>";
            }
            ?>
        </table>
    </div>

    <!-- Detail tidak memenuhi syarat -->
    <div id="instansi-details" class="instansi-details">
        <h3>Detail Tidak Memenuhi Syarat:</h3>
        <ul id="details-list"></ul>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const instansiLinks = document.querySelectorAll('.instansi-link');
            const detailsList = document.getElementById('details-list');
            const detailsDiv = document.getElementById('instansi-details');

            instansiLinks.forEach(link => {
                link.addEventListener('click', function (event) {
                    event.preventDefault();
                    const instansi = this.getAttribute('data-instansi');
                    
                    // Clear previous details
                    detailsList.innerHTML = '';

                    // Fetch new details
                    fetch('?action=getInstansiDetails&instansi_kerja=' + encodeURIComponent(instansi))
                        .then(response => response.json())
                        .then(data => {
                            if (data.length > 0) {
                                data.forEach(item => {
                                    const listItem = document.createElement('li');
                                    listItem.textContent = item.nama + ' (NIP: ' + item.nip + ')';
                                    detailsList.appendChild(listItem);
                                });
                                detailsDiv.style.display = 'block';
                            } else {
                                const listItem = document.createElement('li');
                                listItem.textContent = 'Tidak ada data yang ditemukan';
                                detailsList.appendChild(listItem);
                                detailsDiv.style.display = 'block';
                            }
                        });
                });
            });
        });
    </script>
</body>
</html>
