<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!is_logged_in()) {
    header('Location: ../../login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$tree_id = intval($_GET['id']);
$tree = get_tree_by_id($tree_id);
$photos = get_tree_photos($tree_id);

if (!$tree) {
    header('Location: index.php');
    exit;
}

$page_title = 'Edit Tree';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../includes/quick_actions.php';

// Debugging setup
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify upload directory exists
        if (!file_exists(TREE_PHOTOS_DIR)) {
            if (!mkdir(TREE_PHOTOS_DIR, 0755, true)) {
                throw new Exception("Failed to create photos directory");
            }
        }

        // Process main form data
        $data = [
            // Your existing data processing
        ];

        // Update tree data
        $updates = [];
        foreach ($data as $key => $value) {
            $updates[] = "$key = '$value'";
        }
        $sql = "UPDATE trees SET " . implode(', ', $updates) . " WHERE tree_id = $tree_id";
        
        if (!query($sql)) {
            throw new Exception("Database error: " . error());
        }

        // Process photo uploads
        if (!empty($_FILES['photos']['name'][0])) {
            $uploaded_files = [];
            
            foreach ($_FILES['photos']['tmp_name'] as $index => $tmp_name) {
                if ($_FILES['photos']['error'][$index] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['photos']['name'][$index], PATHINFO_EXTENSION));
                    $filename = "tree_{$tree_id}_" . uniqid() . '.' . $ext;
                    $target_path = TREE_PHOTOS_DIR . $filename;

                    if (!move_uploaded_file($tmp_name, $target_path)) {
                        throw new Exception("Failed to move uploaded file");
                    }

                    $uploaded_files[] = [
                        'path' => $filename,
                        'caption' => $_POST['photo_captions'][$index] ?? ''
                    ];
                }
            }

            // Save to database
            foreach ($uploaded_files as $file) {
                $sql = "INSERT INTO tree_photos (tree_id, photo_path, caption) 
                        VALUES ($tree_id, '" . escape_string($file['path']) . "', '" . escape_string($file['caption']) . "')";
                if (!query($sql)) {
                    throw new Exception("Failed to save photo to database");
                }
            }
        }

        // Process photo deletions
        if (!empty($_POST['delete_photos'])) {
            foreach ($_POST['delete_photos'] as $photo_id) {
                $photo_id = intval($photo_id);
                $photo = fetch_assoc(query("SELECT photo_path FROM tree_photos WHERE photo_id = $photo_id"));
                
                if ($photo) {
                    $file_path = TREE_PHOTOS_DIR . $photo['photo_path'];
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                    query("DELETE FROM tree_photos WHERE photo_id = $photo_id");
                }
            }
        }

        // Set primary photo
        if (!empty($_POST['primary_photo'])) {
            $primary_id = intval($_POST['primary_photo']);
            query("UPDATE tree_photos SET is_primary = 0 WHERE tree_id = $tree_id");
            query("UPDATE tree_photos SET is_primary = 1 WHERE photo_id = $primary_id AND tree_id = $tree_id");
        }

        // Explicitly complete the transaction before redirect
        $_SESSION['message'] = "Tree updated successfully!";
        $_SESSION['message_type'] = 'success';
        
        // Ensure no output before header
        if (!headers_sent()) {
            header("Location: index.php");
            exit;
        } else {
            echo '<script>window.location.href="index.php";</script>';
            exit;
        }

    } catch (Exception $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
        error_log("Tree Edit Error: " . $e->getMessage());
    }
}
?>

<!-- Rest of your HTML form remains the same -->