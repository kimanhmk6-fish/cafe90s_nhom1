-- =========================================================
-- GÓC CÀ PHÊ 90S - KHỞI TẠO CƠ SỞ DỮ LIỆU
-- 12 bảng theo ERD (schema). Dữ liệu mẫu nằm ở seed.sql
-- Database: cafe90snhom1
-- =========================================================

CREATE DATABASE IF NOT EXISTS cafe90snhom1
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE cafe90snhom1;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_details;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS carts;
DROP TABLE IF EXISTS product_sizes;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS sizes;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS user_profiles;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. USERS: tài khoản đăng nhập
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    remember_token VARCHAR(100) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. USER_PROFILES: thông tin chi tiết của tài khoản
CREATE TABLE user_profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    full_name VARCHAR(100) NULL,
    phone VARCHAR(20) NULL,
    address TEXT NULL,
    avatar VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_profiles_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. PASSWORD_RESETS: mã/token đặt lại mật khẩu
-- Theo ERD, bảng liên kết bằng email logic, không tạo khóa ngoại.
CREATE TABLE password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_password_resets_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. CATEGORIES: danh mục sản phẩm
CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. SIZES: kích cỡ đồ uống
CREATE TABLE sizes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(10) NOT NULL UNIQUE,
    description VARCHAR(50) NULL,
    sort_order TINYINT UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. PRODUCTS: sản phẩm
CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(170) NOT NULL UNIQUE,
    description TEXT NULL,
    image VARCHAR(255) NULL,
    base_price DECIMAL(12,0) NOT NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_products_base_price CHECK (base_price >= 0),
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_products_category_id (category_id),
    INDEX idx_products_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. PRODUCT_SIZES: giá theo kích cỡ; bánh không cần dòng trong bảng này
CREATE TABLE product_sizes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    size_id INT UNSIGNED NOT NULL,
    price DECIMAL(12,0) NOT NULL,
    UNIQUE KEY uq_product_size (product_id, size_id),
    CONSTRAINT chk_product_sizes_price CHECK (price >= 0),
    CONSTRAINT fk_product_sizes_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_product_sizes_size
        FOREIGN KEY (size_id) REFERENCES sizes(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. CARTS: mỗi tài khoản có tối đa một giỏ hàng
CREATE TABLE carts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_carts_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. CART_ITEMS: sản phẩm trong giỏ hàng
CREATE TABLE cart_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    size_id INT UNSIGNED NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    UNIQUE KEY uq_cart_product_size (cart_id, product_id, size_id),
    CONSTRAINT chk_cart_items_quantity CHECK (quantity >= 1),
    CONSTRAINT fk_cart_items_cart
        FOREIGN KEY (cart_id) REFERENCES carts(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_cart_items_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_cart_items_size
        FOREIGN KEY (size_id) REFERENCES sizes(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_cart_items_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. ORDERS: đơn hàng
CREATE TABLE orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    order_code VARCHAR(30) NOT NULL UNIQUE,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    shipping_address TEXT NOT NULL,
    note TEXT NULL,
    subtotal DECIMAL(12,0) NOT NULL DEFAULT 0,
    shipping_fee DECIMAL(12,0) NOT NULL DEFAULT 15000,
    total_amount DECIMAL(12,0) NOT NULL,
    status ENUM('pending', 'confirmed', 'preparing', 'shipping', 'completed', 'cancelled')
        NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_orders_subtotal CHECK (subtotal >= 0),
    CONSTRAINT chk_orders_shipping_fee CHECK (shipping_fee >= 0),
    CONSTRAINT chk_orders_total CHECK (total_amount >= 0),
    CONSTRAINT fk_orders_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_orders_user_id (user_id),
    INDEX idx_orders_status (status),
    INDEX idx_orders_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. ORDER_DETAILS: chi tiết đơn hàng, lưu tên/giá tại thời điểm mua
CREATE TABLE order_details (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    size_id INT UNSIGNED NULL,
    product_name VARCHAR(150) NOT NULL,
    size_name VARCHAR(10) NULL,
    price DECIMAL(12,0) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    total DECIMAL(12,0) NOT NULL,
    CONSTRAINT chk_order_details_price CHECK (price >= 0),
    CONSTRAINT chk_order_details_quantity CHECK (quantity >= 1),
    CONSTRAINT chk_order_details_total CHECK (total >= 0),
    CONSTRAINT fk_order_details_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_order_details_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_order_details_size
        FOREIGN KEY (size_id) REFERENCES sizes(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_order_details_order_id (order_id),
    INDEX idx_order_details_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. PAYMENTS: mỗi đơn hàng có tối đa một bản ghi thanh toán
CREATE TABLE payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL UNIQUE,
    payment_method VARCHAR(50) NOT NULL DEFAULT 'bank_transfer',
    amount DECIMAL(12,0) NOT NULL,
    status ENUM('pending', 'paid', 'failed') NOT NULL DEFAULT 'pending',
    transaction_code VARCHAR(100) NULL,
    paid_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_payments_amount CHECK (amount >= 0),
    CONSTRAINT fk_payments_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
