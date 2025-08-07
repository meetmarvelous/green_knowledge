<?php
require_once 'db.php';
require_once 'phpqrcode/qrlib.php';

function get_families()
{
    $sql = "SELECT * FROM families ORDER BY family_name";
    return query($sql);
}

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

function get_tree_photos($tree_id)
{
    $tree_id = escape_string($tree_id);
    $sql = "SELECT * FROM tree_photos WHERE tree_id = $tree_id";
    return query($sql);
}
function get_all_trees($sort = 'tree_id', $order = 'ASC')
{
    $valid_sorts = ['tree_id', 'scientific_name', 'family_id', 'tree_code'];
    $sort = in_array($sort, $valid_sorts) ? $sort : 'tree_id';
    $order = $order === 'DESC' ? 'DESC' : 'ASC';

    $sql = "SELECT t.*, f.family_name 
            FROM trees t 
            JOIN families f ON t.family_id = f.family_id 
            ORDER BY $sort $order";
    return query($sql);
}

function search_trees($query, $family_filter = '', $conservation_filter = '')
{
    $query = escape_string($query);
    $sql = "SELECT t.*, f.family_name 
            FROM trees t 
            JOIN families f ON t.family_id = f.family_id 
            WHERE (scientific_name LIKE '%$query%' 
                   OR common_names LIKE '%$query%' 
                   OR family_name LIKE '%$query%' 
                   OR tree_code LIKE '%$query%')";

    if (!empty($family_filter)) {
        $family_filter = escape_string($family_filter);
        $sql .= " AND t.family_id = '$family_filter'";
    }

    if (!empty($conservation_filter)) {
        $conservation_filter = escape_string($conservation_filter);
        $sql .= " AND conservation_status = '$conservation_filter'";
    }

    $sql .= " ORDER BY scientific_name ASC";
    return query($sql);
}


function generate_qr_code($tree_id, $size = 10)
{
    // Only generate if in admin context
    if (!defined('ADMIN_CONTEXT')) {
        return false;
    }

    $url = BASE_URL . "pages/tree.php?id=$tree_id";
    $qr_file = QR_CODES_DIR . "tree_$tree_id.png";

    // Generate QR code
    require_once 'phpqrcode/qrlib.php';
    QRcode::png($url, $qr_file, QR_ECLEVEL_L, $size);

    return $qr_file;
}

function generate_qr_with_record($tree_id)
{
    // Verify tree exists
    $tree = get_tree_by_id($tree_id);
    if (!$tree) {
        throw new Exception("Tree not found");
    }

    // Generate unique filename
    $qr_filename = "tree_{$tree_id}_" . time() . ".png";
    $qr_path = QR_CODES_DIR . $qr_filename;

    // Generate QR code
    require_once 'phpqrcode/qrlib.php';
    $url = BASE_URL . "/pages/tree.php?id=$tree_id";
    QRcode::png($url, $qr_path, QR_ECLEVEL_L, 10);

    // Database record
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $qr_path_db = escape_string($qr_path);

    // Deactivate old QR codes for this tree
    mysqli_query($conn, "UPDATE qr_codes SET is_active = FALSE WHERE tree_id = $tree_id");

    // Insert new record
    $insert_sql = "INSERT INTO qr_codes (tree_id, qr_path) VALUES ($tree_id, '$qr_path_db')";
    if (!mysqli_query($conn, $insert_sql)) {
        throw new Exception("Database error: " . mysqli_error($conn));
    }

    // Update tree record
    $update_sql = "UPDATE trees SET qr_code_path = '$qr_path_db' WHERE tree_id = $tree_id";
    mysqli_query($conn, $update_sql);

    return [
        'success' => true,
        'path' => $qr_path,
        'url' => str_replace(QR_CODES_DIR, BASE_URL . '/' . QR_CODES_DIR, $qr_path)
    ];
}
