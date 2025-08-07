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

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    error_log("EDIT TREE POST DATA: " . print_r($_POST, true));
    error_log("FILES DATA: " . print_r($_FILES, true));

    $required_fields = ['scientific_name', 'family_id', 'tree_code'];
    foreach ($required_fields as $field) {
      if (empty($_POST[$field])) {
        throw new Exception("Error: $field is required");
      }
    }

    $data = [
      'scientific_name' => escape_string($_POST['scientific_name']),
      'common_names' => escape_string($_POST['common_names'] ?? ''),
      'family_id' => intval($_POST['family_id']),
      'origin_distribution' => escape_string($_POST['origin_distribution'] ?? ''),
      'physical_description' => escape_string($_POST['physical_description'] ?? ''),
      'ecological_info' => escape_string($_POST['ecological_info'] ?? ''),
      'conservation_status' => escape_string($_POST['conservation_status'] ?? 'Least Concern'),
      'uses_economic' => escape_string($_POST['uses_economic'] ?? ''),
      'geotag_lat' => floatval($_POST['geotag_lat'] ?? 0),
      'geotag_lng' => floatval($_POST['geotag_lng'] ?? 0),
      'tree_code' => escape_string($_POST['tree_code']),
      'health_status' => escape_string($_POST['health_status'] ?? ''),
      'remarks' => escape_string($_POST['remarks'] ?? '')
    ];

    $updates = [];
    foreach ($data as $key => $value) {
      $updates[] = "$key = '$value'";
    }
    $sql = "UPDATE trees SET " . implode(', ', $updates) . " WHERE tree_id = $tree_id";

    error_log("UPDATE SQL: $sql");
    if (!query($sql)) {
      throw new Exception("Database error: " . error());
    }

    if (!empty($_FILES['photos']['name'][0])) {
      error_log("NEW PHOTOS UPLOAD ATTEMPTED");

      if (!file_exists(TREE_PHOTOS_DIR)) {
        error_log("CREATING PHOTO DIRECTORY: " . TREE_PHOTOS_DIR);
        if (!mkdir(TREE_PHOTOS_DIR, 0755, true)) {
          throw new Exception("Failed to create photos directory");
        }
      }

      $photo_count = count($_FILES['photos']['name']);
      error_log("NEW PHOTO COUNT: $photo_count");

      for ($i = 0; $i < $photo_count; $i++) {
        if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_OK) {
          error_log("PROCESSING NEW PHOTO $i");

          $check = getimagesize($_FILES['photos']['tmp_name'][$i]);
          if ($check === false) {
            throw new Exception("File is not an image");
          }

          $ext = strtolower(pathinfo($_FILES['photos']['name'][$i], PATHINFO_EXTENSION));
          $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
          if (!in_array($ext, $allowed_exts)) {
            throw new Exception("Only JPG, PNG, and GIF files are allowed");
          }

          $filename = "tree_{$tree_id}_" . uniqid() . '.' . $ext;
          $target_path = TREE_PHOTOS_DIR . $filename;
          error_log("TARGET PATH: $target_path");

          if (!move_uploaded_file($_FILES['photos']['tmp_name'][$i], $target_path)) {
            throw new Exception("Failed to move uploaded file");
          }

          $caption = escape_string($_POST['photo_captions'][$i] ?? '');
          $is_primary = 0;
          $photo_sql = "INSERT INTO tree_photos (tree_id, photo_path, caption, is_primary) 
                                 VALUES ($tree_id, '$filename', '$caption', $is_primary)";

          error_log("PHOTO SQL: $photo_sql");
          if (!query($photo_sql)) {
            throw new Exception("Failed to save photo to database: " . error());
          }

          error_log("NEW PHOTO $i UPLOADED SUCCESSFULLY");
        } else {
          error_log("NEW PHOTO $i UPLOAD ERROR: " . $_FILES['photos']['error'][$i]);
        }
      }
    }

    if (!empty($_POST['primary_photo'])) {
      $primary_id = intval($_POST['primary_photo']);
      error_log("SETTING PRIMARY PHOTO: $primary_id");

      query("UPDATE tree_photos SET is_primary = 0 WHERE tree_id = $tree_id");
      query("UPDATE tree_photos SET is_primary = 1 WHERE photo_id = $primary_id AND tree_id = $tree_id");
    }

    if (!empty($_POST['delete_photos'])) {
      foreach ($_POST['delete_photos'] as $photo_id) {
        $photo_id = intval($photo_id);
        error_log("DELETING PHOTO ID: $photo_id");

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

    $_SESSION['message'] = "Tree updated successfully!";
    $_SESSION['message_type'] = 'success';
    error_log("TREE UPDATED SUCCESSFULLY");
    header("Location: index.php");
    exit;
  } catch (Exception $e) {
    error_log("EDIT TREE ERROR: " . $e->getMessage());
    $_SESSION['message'] = $e->getMessage();
    $_SESSION['message_type'] = 'danger';
  }
}
