<?php
require_once 'db.php';
require_once 'phpqrcode/qrlib.php';

function get_families() {
    $sql = "SELECT * FROM families ORDER BY family_name";
    return query($sql);
}

function get_tree_by_id($tree_id) {
    $tree_id = escape_string($tree_id);
    $sql = "SELECT t.*, f.family_name 
            FROM trees t 
            JOIN families f ON t.family_id = f.family_id 
            WHERE t.tree_id = $tree_id";
    $result = query($sql);
    return fetch_assoc($result);
}

function get_tree_photos($tree_id) {
    $tree_id = escape_string($tree_id);
    $sql = "SELECT * FROM tree_photos WHERE tree_id = $tree_id";
    return query($sql);
}

function generate_qr_code($tree_id, $size = 10) {
    $url = BASE_URL . "/pages/tree.php?id=$tree_id";
    $filename = QR_CODES_DIR . "tree_$tree_id.png";
    QRcode::png($url, $filename, QR_ECLEVEL_L, $size);
    return $filename;
}

function get_all_trees($sort = 'tree_id', $order = 'ASC') {
    $valid_sorts = ['tree_id', 'scientific_name', 'family_id', 'tree_code'];
    $sort = in_array($sort, $valid_sorts) ? $sort : 'tree_id';
    $order = $order === 'DESC' ? 'DESC' : 'ASC';
    
    $sql = "SELECT t.*, f.family_name 
            FROM trees t 
            JOIN families f ON t.family_id = f.family_id 
            ORDER BY $sort $order";
    return query($sql);
}

function search_trees($query, $family_filter = '', $conservation_filter = '') {
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
?>