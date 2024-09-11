<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Upload</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        h1 {
            color: #007bff;
            margin-bottom: 20px;
        }

        .status-message {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .status-message.success {
            color: #28a745;
        }

        .status-message.error {
            color: #dc3545;
        }

        .link {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .link:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Status Upload</h1>
        <?php
        // Ambil status dari query parameter
        $status = isset($_GET['status']) ? $_GET['status'] : 'gagal';

        if ($status === 'berhasil') {
            echo '<p class="status-message success">Data berhasil diunggah dan disimpan ke database.</p>';
        } else {
            echo '<p class="status-message error">Terjadi kesalahan saat mengunggah data. Silakan coba lagi.</p>';
        }
        ?>
        <a href="index.php" class="link">Kembali ke Unggah File</a>
    </div>

</body>
</html>
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

require 'vendor/autoload.php'; // Menggunakan PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

// Status awal
$status = "gagal";

if (isset($_FILES['excelFile']) && $_FILES['excelFile']['error'] == UPLOAD_ERR_OK) {
    $file = $_FILES['excelFile']['tmp_name'];

    try {
        // Memuat file Excel menggunakan PhpSpreadsheet
        $spreadsheet = IOFactory::load($file);
        $sheetData = $spreadsheet->getActiveSheet()->toArray();

        // Ambil header dari file Excel (baris pertama)
        $header = $sheetData[0];

        // Tangani duplikasi kolom dengan cara menambahkan angka di akhir nama kolom yang duplikat
        $fieldCount = [];
        foreach ($header as $index => $field) {
            if (empty(trim($field))) {
                // Jika nama kolom kosong, beri nama default
                $field = "kolom_" . ($index + 1);
            }

            // Jika kolom sudah ada, tambahkan angka untuk menghindari duplikasi
            if (isset($fieldCount[$field])) {
                $fieldCount[$field]++;
                $header[$index] = $field . "_" . $fieldCount[$field];
            } else {
                $fieldCount[$field] = 0;
                $header[$index] = $field;
            }
        }

        // Siapkan query untuk membuat tabel secara dinamis
        $createTableQuery = "CREATE TABLE IF NOT EXISTS kenaikan_pangkat (id INT AUTO_INCREMENT PRIMARY KEY, ";
        foreach ($header as $field) {
            $createTableQuery .= "`$field` VARCHAR(255), ";
        }
        $createTableQuery = rtrim($createTableQuery, ', ') . ')';

        if ($conn->query($createTableQuery) === TRUE) {
            // Memasukkan data dari Excel ke database
            $dataInserted = true; // Flag untuk melacak status
            for ($i = 1; $i < count($sheetData); $i++) {
                $row = $sheetData[$i];
                $insertQuery = "INSERT INTO kenaikan_pangkat (";

                foreach ($header as $field) {
                    $insertQuery .= "`$field`, ";
                }

                $insertQuery = rtrim($insertQuery, ', ') . ') VALUES (';

                foreach ($row as $value) {
                    $insertQuery .= "'" . $conn->real_escape_string($value) . "', ";
                }

                $insertQuery = rtrim($insertQuery, ', ') . ')';

                if ($conn->query($insertQuery) !== TRUE) {
                    $dataInserted = false;
                    break;
                }
            }

            if ($dataInserted) {
                $status = "berhasil";
            } else {
                $status = "gagal";
            }
        }

    } catch (Exception $e) {
        $status = "gagal";
    }

    // Redirect ke halaman display.php dengan status sebagai query parameter
    header("Location: display.php?status=$status");
    exit();
} else {
    // Redirect ke halaman display.php dengan status gagal jika tidak ada file yang diunggah
    header("Location: display.php?status=gagal");
    exit();
}

$conn->close();
?>
