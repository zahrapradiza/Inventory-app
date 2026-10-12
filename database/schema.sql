CREATE DATABASE IF NOT EXISTS inventory_db;
USE inventory_db;

-- 1. Tabel Pengguna (Untuk Fitur Login & Role)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabel Kategori Produk (One-to-Many dengan products)
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE
);

-- 3. Tabel Supplier
CREATE TABLE suppliers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    contact_info VARCHAR(255)
);

-- 4. Tabel Produk Utama
-- Menerapkan relasi ke categories (category_id)
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    sku VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    minimum_stock INT NOT NULL DEFAULT 5,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT
);

-- 5. Junction Table: Many-to-Many (Produk <-> Supplier)
CREATE TABLE product_suppliers (
    product_id INT NOT NULL,
    supplier_id INT NOT NULL,
    supplier_price DECIMAL(12,2),
    PRIMARY KEY (product_id, supplier_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE
);

-- 6. Tabel Histori / Pergerakan Stok (Barang Masuk/Keluar)
CREATE TABLE stock_movements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    type ENUM('IN', 'OUT') NOT NULL,
    quantity INT NOT NULL,
    movement_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
);

-- Insert Data Awal (Dummy) untuk Testing Login
-- Password untuk kedua akun ini adalah: "password123"
INSERT INTO users (username, password_hash, role) VALUES 
('admin_tes', '$2y$10$w8.3f6/1.hO1cM19pP5/mO2P2hG/gA.C1nO0.G/eR9.T9j3mQpT9W', 'admin'),
('staff_tes', '$2y$10$w8.3f6/1.hO1cM19pP5/mO2P2hG/gA.C1nO0.G/eR9.T9j3mQpT9W', 'staff');

INSERT INTO categories (name) VALUES ('Makanan'), ('Minuman'), ('Alat Tulis');