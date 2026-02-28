CREATE DATABASE IF NOT EXISTS db_gadget_nim;
USE db_gadget_nim;

CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS kategori (
    id_kategori INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS merk (
    id_merk INT AUTO_INCREMENT PRIMARY KEY,
    nama_merk VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS produk (
    id_produk INT AUTO_INCREMENT PRIMARY KEY,
    id_kategori INT NOT NULL,
    id_merk INT NOT NULL,
    nama_produk VARCHAR(255) NOT NULL,
    harga INT NOT NULL,
    stok INT NOT NULL,
    foto_produk VARCHAR(255) NOT NULL,
    FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (id_merk) REFERENCES merk(id_merk) ON UPDATE CASCADE ON DELETE RESTRICT
);

-- Remove previous data if exists to avoid duplicate running
TRUNCATE TABLE produk;
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE users;
TRUNCATE TABLE kategori;
TRUNCATE TABLE merk;
SET FOREIGN_KEY_CHECKS = 1;

-- Default data
INSERT INTO users (username, password) VALUES ('admin', 'admin123');

INSERT INTO kategori (nama_kategori) VALUES ('Smartphone'), ('Laptop'), ('Tablet'), ('Aksesoris');
INSERT INTO merk (nama_merk) VALUES ('Apple'), ('Samsung'), ('Asus'), ('Lenovo'), ('Xiaomi');
