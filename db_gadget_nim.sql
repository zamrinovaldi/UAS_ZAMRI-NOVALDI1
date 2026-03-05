-- UAS Invetory Gadget Database Restored
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    nama_lengkap VARCHAR(100),
    role ENUM('admin', 'kasir') NOT NULL DEFAULT 'kasir',
    security_question VARCHAR(255),
    security_answer VARCHAR(255),
    credential_id TEXT
);

CREATE TABLE IF NOT EXISTS kategori (
    id_kategori INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS merk (
    id_merk INT AUTO_INCREMENT PRIMARY KEY,
    nama_merk VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS produk (
    id_produk INT AUTO_INCREMENT PRIMARY KEY,
    id_kategori INT,
    id_merk INT,
    nama_produk VARCHAR(100) NOT NULL,
    harga INT NOT NULL,
    stok INT NOT NULL,
    foto_produk VARCHAR(255),
    FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori) ON DELETE CASCADE,
    FOREIGN KEY (id_merk) REFERENCES merk(id_merk) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS penjualan (
    id_penjualan INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    total_harga DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS detail_penjualan (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_penjualan INT NOT NULL,
    id_produk INT NOT NULL,
    jumlah INT NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (id_penjualan) REFERENCES penjualan(id_penjualan) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS activity_logs (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    aksi VARCHAR(50) NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default User admin:admin
INSERT IGNORE INTO users (id_user, username, password, role) VALUES (1, 'admin', '$2y$10$Y7p75R8lBly.2n8x8G/5p.7nC.o5w1Z8j/X0k8m9o2P4Q5W6E7R8S', 'admin');

COMMIT;
