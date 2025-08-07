-- Add to your existing trees table
ALTER TABLE trees ADD COLUMN qr_code_path VARCHAR(255) AFTER remarks;

-- Create qr_codes table
CREATE TABLE IF NOT EXISTS qr_codes (
    qr_id INT AUTO_INCREMENT PRIMARY KEY,
    tree_id INT NOT NULL,
    qr_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (tree_id) REFERENCES trees(tree_id) ON DELETE CASCADE,
    INDEX (tree_id, is_active)
);