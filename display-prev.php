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

// Query untuk total data verifikasi berdasarkan filter
$totalDataQuery = "SELECT COUNT(*) AS total_data FROM kenaikan_pangkat WHERE 1=1";
if ($filterVerifikator) {
    $totalDataQuery .= " AND verifikator_nama = '" . $conn->real_escape_string($filterVerifikator) . "'";
}
if ($filterInstansi) {
    $totalDataQuery .= " AND instansi_kerja = '" . $conn->real_escape_string($filterInstansi) . "'";
}
if ($filterStatusUsulan) {
    $totalDataQuery .= " AND status_usulan = '" . $conn->real_escape_string($filterStatusUsulan) . "'";
}
if ($filterTahun) {
    $totalDataQuery .= " AND YEAR(tgl_ttd_pertek) = '" . $conn->real_escape_string($filterTahun) . "'";
}
$totalDataResult = $conn->query($totalDataQuery);
$totalData = $totalDataResult->fetch_assoc()['total_data'];

// Query untuk total verifikasi per instansi (semua verifikator)
$instansiTotalQuery = "SELECT instansi_kerja, COUNT(*) AS total_verifikasi FROM kenaikan_pangkat WHERE 1=1";
if ($filterStatusUsulan) {
    $instansiTotalQuery .= " AND status_usulan = '" . $conn->real_escape_string($filterStatusUsulan) . "'";
}
if ($filterTahun) {
    $instansiTotalQuery .= " AND YEAR(tgl_ttd_pertek) = '" . $conn->real_escape_string($filterTahun) . "'";
}
$instansiTotalQuery .= " GROUP BY instansi_kerja ORDER BY total_verifikasi DESC";
$instansiTotalResult = $conn->query($instansiTotalQuery);

// Query untuk total verifikasi per instansi berdasarkan filter verifikator
$instansiVerifikatorQuery = "SELECT instansi_kerja, COUNT(*) AS total_verifikasi_per_verifikator FROM kenaikan_pangkat WHERE 1=1";
if ($filterVerifikator) {
    $instansiVerifikatorQuery .= " AND verifikator_nama = '" . $conn->real_escape_string($filterVerifikator) . "'";
}
if ($filterStatusUsulan) {
    $instansiVerifikatorQuery .= " AND status_usulan = '" . $conn->real_escape_string($filterStatusUsulan) . "'";
}
if ($filterTahun) {
    $instansiVerifikatorQuery .= " AND YEAR(tgl_ttd_pertek) = '" . $conn->real_escape_string($filterTahun) . "'";
}
$instansiVerifikatorQuery .= " GROUP BY instansi_kerja ORDER BY total_verifikasi_per_verifikator DESC";
$instansiVerifikatorResult = $conn->query($instansiVerifikatorQuery);

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
// Tangani permintaan AJAX untuk detail instansi
if (isset($_GET['action']) && $_GET['action'] == 'getInstansiDetails') {
    $instansi_kerja = isset($_GET['instansi_kerja']) ? $_GET['instansi_kerja'] : '';
    $verifikator_nama = isset($_GET['verifikator_nama']) ? $_GET['verifikator_nama'] : '';

    // Query untuk mendapatkan nama, NIP, dan alasan tolak dokumen berdasarkan instansi dan verifikator
    $detailsQuery = "SELECT nama, nip, alasan_tolak FROM kenaikan_pangkat WHERE 1=1";
    
    if ($instansi_kerja) {
        $detailsQuery .= " AND instansi_kerja = '" . $conn->real_escape_string($instansi_kerja) . "'";
    }
    
    if ($verifikator_nama) {
        $detailsQuery .= " AND verifikator_nama = '" . $conn->real_escape_string($verifikator_nama) . "'";
    }
    
    $detailsQuery .= " AND status_usulan = 'Perbaikan Dokumen'";
    
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
            border-radius: 5px;
        }
        .total-data {
            font-size: 18px;
            margin: 20px 0;
        }
        .instansi-summary, .instansi-details {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .instansi-summary h3, .instansi-details h3 {
            margin-top: 0;
        }
        .instansi-link {
            color: #4CAF50;
            text-decoration: none;
        }
        .instansi-link:hover {
            text-decoration: underline;
        }
        .instansi-details ul {
            list-style-type: none;
            padding: 0;
        }
        .instansi-details li {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .instansi-details li:last-child {
            border-bottom: none;
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

    <h1>Data Verifikasi Kenaikan Pangkat</h1>
    <a href="index.php" class="button-link">Home</a>
    <?php echo $statusMessage; ?>

    <div class="filter-form">
        <form method="POST" action="">
            <label for="verifikator">Nama Verifikator:</label>
            <select name="verifikator" id="verifikator">
                <option value="">Semua</option>
                <?php while ($row = $verifikatorResult->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($row['verifikator_nama']); ?>" <?php echo $filterVerifikator == $row['verifikator_nama'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['verifikator_nama']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="instansi">Instansi Kerja:</label>
            <select name="instansi" id="instansi">
                <option value="">Semua</option>
                <?php while ($row = $instansiResult->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($row['instansi_kerja']); ?>" <?php echo $filterInstansi == $row['instansi_kerja'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['instansi_kerja']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="status_usulan">Status Usulan:</label>
            <select name="status_usulan" id="status_usulan">
                <option value="">Semua</option>
                <?php while ($row = $statusUsulanResult->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($row['status_usulan']); ?>" <?php echo $filterStatusUsulan == $row['status_usulan'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['status_usulan']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="tahun">Tahun:</label>
            <select name="tahun" id="tahun">
                <option value="">Semua</option>
                <?php while ($row = $tahunResult->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($row['tahun']); ?>" <?php echo $filterTahun == $row['tahun'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['tahun']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <input type="submit" value="Filter">
        </form>
    </div>

    <div class="total-data">
        <p>Total Data Verifikasi: <span><?php echo $totalData; ?></span></p>
    </div>

    <div class="instansi-summary">
        <h3>Detail Total Verifikasi Per Instansi:</h3>
        <p><?php echo $filterDescription; ?></p>
        <table>
            <tr>
                <th>Instansi Kerja</th>
                <th>Total Usulan</th>
                <th>Jumlah Diverifikasi</th>
                <th>Persentase Pengerjaan</th>
            </tr>
            <?php
            if ($instansiTotalResult->num_rows > 0) {
                $instansiTotalData = [];
                while ($row = $instansiTotalResult->fetch_assoc()) {
                    $instansiTotalData[$row['instansi_kerja']] = $row['total_verifikasi'];
                }
                
                while ($row = $instansiVerifikatorResult->fetch_assoc()) {
                    $instansiVerifikatorData[$row['instansi_kerja']] = $row['total_verifikasi_per_verifikator'];
                }
                
                foreach ($instansiTotalData as $instansi => $totalVerifikasi) {
                    $totalVerifikasiVerifikator = isset($instansiVerifikatorData[$instansi]) ? $instansiVerifikatorData[$instansi] : 0;
                    $persentase = $totalVerifikasi > 0 ? ($totalVerifikasiVerifikator / $totalVerifikasi) * 100 : 0;
                    
                    echo "<tr>";
                    echo "<td><a href='#' class='instansi-link' data-instansi='" . htmlspecialchars($instansi) . "'>" . htmlspecialchars($instansi) . "</a></td>";
                    echo "<td>" . htmlspecialchars($totalVerifikasi) . "</td>";
                    echo "<td>" . htmlspecialchars($totalVerifikasiVerifikator) . "</td>";
                    echo "<td>" . number_format($persentase, 2) . "%</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>Tidak ada data yang ditemukan</td></tr>";
            }
            ?>
        </table>
    </div>

    <!-- Perbaikan tampilkan detail BTS dalam tabel -->
    <div class="instansi-details" id="instansi-details" style="display:none;">
        <h3>Detail Data Perbaikan:</h3>
        <table id="details-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>NIP</th>
                    <th>Alasan Tolak</th>
                </tr>
            </thead>
            <tbody id="details-list">
            </tbody>
        </table>
    </div>


    <script>
       document.addEventListener('DOMContentLoaded', function () {
        const instansiLinks = document.querySelectorAll('.instansi-link');
        const detailsTable = document.getElementById('details-table');
        const detailsList = document.getElementById('details-list');
        const detailsDiv = document.getElementById('instansi-details');

        instansiLinks.forEach(link => {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                const instansi = this.getAttribute('data-instansi');
                const verifikator = document.getElementById('verifikator').value; // Mengambil nilai dari dropdown verifikator

                // Clear previous details
                detailsList.innerHTML = '';

                // Fetch new details
                fetch('?action=getInstansiDetails&instansi_kerja=' + encodeURIComponent(instansi) + '&verifikator_nama=' + encodeURIComponent(verifikator))
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            data.forEach(item => {
                                const row = document.createElement('tr');

                                const nameCell = document.createElement('td');
                                nameCell.textContent = item.nama;
                                row.appendChild(nameCell);

                                const nipCell = document.createElement('td');
                                nipCell.textContent = item.nip;
                                row.appendChild(nipCell);

                                const reasonCell = document.createElement('td');
                                reasonCell.textContent = item.alasan_tolak;
                                row.appendChild(reasonCell);

                                detailsList.appendChild(row);
                            });
                            detailsTable.style.display = 'table';
                            detailsDiv.style.display = 'block';
                        } else {
                            const row = document.createElement('tr');
                            const cell = document.createElement('td');
                            cell.colSpan = 3;
                            cell.textContent = 'Tidak ada data yang ditemukan';
                            row.appendChild(cell);
                            detailsList.appendChild(row);
                            detailsTable.style.display = 'table';
                            detailsDiv.style.display = 'block';
                        }
                    });
            });
        });
    });
    </script>

</body>
</html>
