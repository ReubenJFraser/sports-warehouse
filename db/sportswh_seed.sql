-- sportswh_seed.sql
-- Minimal schema + one row to confirm database initialization

CREATE TABLE IF NOT EXISTS sample_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO sample_products (name, price)
VALUES ('Test Product', 19.99);

