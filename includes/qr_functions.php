<?php
function generate_qr_with_record($tree_id) {
    try {
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
            'url' => str_replace(QR_CODES_DIR, BASE_URL.'/'.QR_CODES_DIR, $qr_path)
        ];
        
    } catch (Exception $e) {
        error_log("QR Generation Error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}