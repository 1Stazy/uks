
DROP DATABASE IF EXISTS uks_db;
CREATE DATABASE uks_db;
USE uks_db;

CREATE TABLE contracts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contract_id VARCHAR(20) NOT NULL UNIQUE,
    type VARCHAR(50) NOT NULL, -- 'car', 'clothes', 'electronics'
    date DATE NOT NULL,
    seller_name VARCHAR(255) NOT NULL,
    street VARCHAR(255) NOT NULL,
    city VARCHAR(255) NOT NULL,
    postal_code VARCHAR(10) NOT NULL,
    email VARCHAR(100) NOT NULL,
    products TEXT NOT NULL, -- JSON z detalami (VIN, Przebieg lub Rozmiar)
    total_amount DECIMAL(10, 2) NOT NULL,
    signature_seller MEDIUMTEXT NOT NULL,
    signature_admin MEDIUMTEXT DEFAULT NULL, -- Podpis admina (ścieżka lub base64)
    account_number VARCHAR(255),
    phone_number VARCHAR(20),
    payment_method VARCHAR(50),
    status VARCHAR(20) DEFAULT 'pending', -- pending, accepted, rejected
    rejection_reason TEXT DEFAULT NULL,
    is_warranty BOOLEAN DEFAULT 0, -- Rękojmia
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL
);

-- Login: Admin, Hasło: Admin (hash dla "Admin")
INSERT INTO admins (username, password_hash) VALUES ('Admin', '$2y$10$tM.y.t.y.t.y.t.y.t.y.u...HASH_PLACEHOLDER...e.g.USE_PHP_PASSWORD_HASH');
-- Uwaga: W kodzie PHP zrobimy proste porównanie stringów dla uproszczenia,
-- ale w bazie trzymamy strukturę.
