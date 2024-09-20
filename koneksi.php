<?php
// Konfigurasi database
$hostname = 'localhost'; // Nama host
$username = 'root';      // Username MySQL
$password = '';      // Password MySQL (biarkan kosong jika tidak ada)
$database = 'rekapkp'; // Ganti dengan nama database Anda

// Membuat koneksi
$conn = new mysqli($hostname, $username, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
} else {
    // echo "Koneksi berhasil"; // Uncomment jika ingin mengecek apakah koneksi berhasil
}
?>
