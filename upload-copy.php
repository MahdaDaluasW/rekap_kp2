<?php
// Koneksi ke database
$hostname = "localhost";
$username = "root";
$password = "";
$dbname = "rekapkp";

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

        // Ambil header dari file Excel (baris ketiga, melewati dua baris pertama)
        $header = $sheetData[2];

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

        // Siapkan query untuk membuat tabel secara dinamis (jika tabel belum ada)
        $createTableQuery = "CREATE TABLE IF NOT EXISTS kenaikan_pangkat (id INT AUTO_INCREMENT PRIMARY KEY, ";
        foreach ($header as $field) {
            $createTableQuery .= "$field VARCHAR(255), ";
        }
        $createTableQuery = rtrim($createTableQuery, ', ') . ')';

        if ($conn->query($createTableQuery) === TRUE) {
            // Memasukkan data dari Excel ke database
            $dataInserted = true; // Flag untuk melacak status
            for ($i = 3; $i < count($sheetData); $i++) {  // Mulai dari baris ketiga
                $row = $sheetData[$i];
                $insertQuery = "INSERT INTO kenaikan_pangkat (";

                foreach ($header as $field) {
                    $insertQuery .= "$field, ";
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
