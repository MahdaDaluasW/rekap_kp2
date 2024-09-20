<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Kenaikan Pangkat</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            /* background: linear-gradient(135deg, #74ebd5 0%, #9face6 100%); */
            background: linear-gradient(135deg, #e9ecef 0%, #f8f9fa 100%);
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        h2 {
            color: #007bff;
            text-align: center;
        }

        .container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 500px;
            width: 100%;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        input[type="file"] {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            background: #f9f9f9;
            font-size: 16px;
        }

        input[type="submit"] {
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            color: white;
            padding: 10px 15px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .button-link {
            display: inline-block;
            background-color: #118bff;
            color: #fff;
            padding: 6px 10px; /* Mengurangi padding untuk tombol kecil */
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px; /* Mengurangi ukuran font */
            transition: background-color 0.3s ease;
            margin-bottom: 20px;
            padding: 10px 15px;
        }

        .button-link:hover {
            background-color: #0056b3;
        }

    </style>
</head>
<body>
    <div class="container">
        <h2>Rekapitulasi Kenaikan Pangkat</h2>
        <a href="display.php" class="button-link">Lihat Data</a>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            Pilih file Excel: 
            <input type="file" name="excelFile" accept=".xls,.xlsx" required>
            <input type="submit" value="Unggah">
        </form>
    </div>
</body>
</html>
