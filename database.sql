-- Create families table
CREATE TABLE IF NOT EXISTS families (
    family_id INT AUTO_INCREMENT PRIMARY KEY,
    family_name VARCHAR(100) NOT NULL,
    family_description TEXT,
    UNIQUE KEY (family_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create trees table
CREATE TABLE IF NOT EXISTS trees (
    tree_id INT AUTO_INCREMENT PRIMARY KEY,
    scientific_name VARCHAR(100) NOT NULL,
    common_names JSON,
    family_id INT NOT NULL,
    origin_distribution TEXT,
    physical_description TEXT,
    ecological_info TEXT,
    conservation_status ENUM('Least Concern', 'Vulnerable', 'Endangered') DEFAULT 'Least Concern',
    uses_economic TEXT,
    geotag_lat DECIMAL(10, 8),
    geotag_lng DECIMAL(11, 8),
    tree_code VARCHAR(20) NOT NULL,
    health_status VARCHAR(50),
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (tree_code),
    FOREIGN KEY (family_id) REFERENCES families(family_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create tree_photos table
CREATE TABLE IF NOT EXISTS tree_photos (
    photo_id INT AUTO_INCREMENT PRIMARY KEY,
    tree_id INT NOT NULL,
    photo_path VARCHAR(255) NOT NULL,
    caption VARCHAR(255),
    is_primary TINYINT(1) DEFAULT 0,
    FOREIGN KEY (tree_id) REFERENCES trees(tree_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'editor') DEFAULT 'editor',
    last_login TIMESTAMP NULL,
    UNIQUE KEY (username),
    UNIQUE KEY (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create contact_messages table (optional)
CREATE TABLE IF NOT EXISTS contact_messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample admin user (password: admin123)
INSERT INTO users (username, password_hash, email, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@botanicalgarden.ui.edu.ng', 'admin');

-- Insert sample families
INSERT INTO families (family_name, family_description) VALUES
('Fabaceae', 'The legume, pea, or bean family'),
('Moraceae', 'The mulberry family'),
('Arecaceae', 'The palm family'),
('Rutaceae', 'The rue or citrus family'),
('Malvaceae', 'The mallow family');

-- Insert sample tree
INSERT INTO trees (
    scientific_name, 
    common_names, 
    family_id, 
    origin_distribution, 
    physical_description, 
    ecological_info, 
    conservation_status, 
    uses_economic, 
    geotag_lat, 
    geotag_lng, 
    tree_code, 
    health_status, 
    remarks
) VALUES (
    'Khaya senegalensis', 
    '["African mahogany", "Dry zone mahogany"]', 
    1, 
    'Native to Africa, from Senegal east to Sudan and south to Zimbabwe', 
    'Large deciduous tree growing to 30 m tall, with a trunk up to 1 m diameter. Bark dark grey, fissured. Leaves pinnate, with 3-6 pairs of leaflets. Flowers small, white, in large panicles. Fruit a woody capsule 5-8 cm diameter, containing numerous winged seeds.', 
    'Grows in savanna woodlands and along rivers. Important food source for elephants which eat the bark.', 
    'Least Concern', 
    'Timber highly valued for furniture, boat building and construction. Bark used in traditional medicine.', 
    7.4456, 
    3.8945, 
    'UI-BG-TS-001', 
    'Healthy', 
    'Planted in 1985. One of the largest trees in the garden.'
);