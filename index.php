<?php
include 'koneksi.php';
// session_start();
// $hostname = "localhost";
// $username = "root";
// $password = "";
// $dbname = "rekapkp";

// $conn = new mysqli($hostname, $username, $password, $dbname);

// if ($conn->connect_error) {
//     die("Koneksi gagal: " . $conn->connect_error);
// }

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input_username = $_POST['username'];
    $input_password = $_POST['password'];

    // Cek username dan password dari tabel users
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $input_username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verifikasi password
        if (password_verify($input_password, $user['password'])) {
            // Set session login
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $user['username'];
            
            // Redirect ke halaman import
            header("Location: import.php");
            exit();
        } else {
            $error = "Password salah.";
        }
    } else {
        $error = "Username tidak ditemukan.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            /* background: linear-gradient(135deg, #74ebd5 0%, #9face6 100%); */
            background: linear-gradient(135deg, #e9ecef 0%, #f8f9fa 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background: #fff;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            width: 350px;
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }

        .login-container h1 {
            margin-bottom: 30px;
            color: #007bff;
            font-size: 24px;
            font-weight: bold;
        }

        .login-container input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
            outline: none;
            transition: border 0.3s ease;
        }

        .login-container input:focus {
            border-color: #007bff;
        }

        .login-container button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .login-container button:hover {
            background-color: #0056b3;
            transform: scale(1.02);
        }

        .login-container .error-message {
            color: red;
            margin: 10px 0;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 600px) {
            .login-container {
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Login</h1>
        <?php if ($error): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST" action="import.php">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
