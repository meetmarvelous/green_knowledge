<?php
require_once 'db.php';
require_once 'config.php';

/**
 * Get all plant families
 */
function get_families()
{
  $sql = "SELECT * FROM families ORDER BY family_name";
  return query($sql);
}

/**
 * Get tree by ID with family info
 */
function get_tree_by_id($tree_id)
{
  $tree_id = escape_string($tree_id);
  $sql = "SELECT t.*, f.family_name 
            FROM trees t 
            JOIN families f ON t.family_id = f.family_id 
            WHERE t.tree_id = $tree_id";
  $result = query($sql);
  return fetch_assoc($result);
}

/**
 * Get all photos for a tree
 */
function get_tree_photos($tree_id)
{
  $tree_id = escape_string($tree_id);
  $sql = "SELECT * FROM tree_photos WHERE tree_id = $tree_id ORDER BY is_primary DESC";
  return query($sql);
}

/**
 * Get all trees (with optional sorting)
 */
function get_all_trees($sort = 'scientific_name', $order = 'ASC')
{
  $valid_sorts = ['tree_id', 'scientific_name', 'family_id', 'tree_code', 'created_at'];
  $sort = in_array($sort, $valid_sorts) ? $sort : 'scientific_name';
  $order = $order === 'DESC' ? 'DESC' : 'ASC';

  $sql = "SELECT t.*, f.family_name 
            FROM trees t 
            JOIN families f ON t.family_id = f.family_id 
            ORDER BY $sort $order";
  return query($sql);
}

/**
 * Search trees with filters
 */
function search_trees($query, $family_filter = '', $conservation_filter = '')
{
  $query = escape_string($query);
  $sql = "SELECT t.*, f.family_name 
            FROM trees t 
            JOIN families f ON t.family_id = f.family_id 
            WHERE (t.scientific_name LIKE '%$query%' 
                   OR t.common_names LIKE '%$query%' 
                   OR f.family_name LIKE '%$query%' 
                   OR t.tree_code LIKE '%$query%')";

  if (!empty($family_filter)) {
    $family_filter = escape_string($family_filter);
    $sql .= " AND t.family_id = '$family_filter'";
  }

  if (!empty($conservation_filter)) {
    $conservation_filter = escape_string($conservation_filter);
    $sql .= " AND t.conservation_status = '$conservation_filter'";
  }

  $sql .= " ORDER BY t.scientific_name ASC";
  return query($sql);
}

/**
 * Generate QR code and maintain database records
 */
function generate_qr_with_record($tree_id)
{
  try {
    // Verify tree exists
    $tree = get_tree_by_id($tree_id);
    if (!$tree) {
      throw new Exception("Tree not found");
    }

    // Generate unique filename
    $qr_filename = "tree_{$tree_id}_" . time() . ".png";
    $qr_path = QR_CODES_DIR . $qr_filename;

    // Ensure directory exists
    if (!file_exists(QR_CODES_DIR)) {
      mkdir(QR_CODES_DIR, 0755, true);
    }

    // Generate QR code
    require_once 'phpqrcode/qrlib.php';
    $url = BASE_URL . "/pages/tree.php?id=$tree_id";
    QRcode::png($url, $qr_path, QR_ECLEVEL_L, 10);

    if (!file_exists($qr_path)) {
      throw new Exception("Failed to generate QR code image");
    }

    // Database operations
    $qr_path_db = escape_string($qr_path);

    // Deactivate old QR codes for this tree
    query("UPDATE qr_codes SET is_active = FALSE WHERE tree_id = $tree_id");

    // Insert new record
    query("INSERT INTO qr_codes (tree_id, qr_path) VALUES ($tree_id, '$qr_path_db')");

    // Update tree record
    query("UPDATE trees SET qr_code_path = '$qr_path_db' WHERE tree_id = $tree_id");

    return [
      'success' => true,
      'path' => $qr_path,
      'url' => str_replace(QR_CODES_DIR, BASE_URL . '/' . QR_CODES_DIR, $qr_path),
      'tree_code' => $tree['tree_code'],
      'scientific_name' => $tree['scientific_name']
    ];
  } catch (Exception $e) {
    error_log("QR Generation Error [Tree ID: $tree_id]: " . $e->getMessage());
    return [
      'success' => false,
      'message' => $e->getMessage()
    ];
  }
}

/**
 * Get active QR code for a tree
 */
function get_active_qr_code($tree_id)
{
  $tree_id = escape_string($tree_id);
  $sql = "SELECT qr_path FROM qr_codes WHERE tree_id = $tree_id AND is_active = TRUE LIMIT 1";
  $result = query($sql);
  return fetch_assoc($result);
}

/**
 * Delete tree and handle QR codes (archive instead of delete)
 */
function delete_tree($tree_id)
{
  $tree_id = escape_string($tree_id);

  // Archive QR codes (don't delete files)
  query("UPDATE qr_codes SET is_active = FALSE WHERE tree_id = $tree_id");

  // Delete tree record
  return query("DELETE FROM trees WHERE tree_id = $tree_id");
}

/**
 * Debugging function
 */
function debug_log($message, $data = null)
{
  $log = "[" . date('Y-m-d H:i:s') . "] " . $message;
  if ($data) {
    $log .= " - " . print_r($data, true);
  }
  error_log($log);
}

/**
 * Check if a tree exists
 */
function tree_exists($tree_id)
{
  $tree_id = escape_string($tree_id);
  $result = query("SELECT tree_id FROM trees WHERE tree_id = $tree_id");
  return (num_rows($result) > 0);
}
