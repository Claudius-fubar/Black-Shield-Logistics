-- SQL pentru tabele necesare funcționalităților noi

-- Tabel pentru mesaje de contact
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel pentru comenzi de transport
CREATE TABLE IF NOT EXISTS transport_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pickup_location VARCHAR(255) NOT NULL,
    delivery_location VARCHAR(255) NOT NULL,
    cargo_type VARCHAR(100) NOT NULL,
    cargo_weight DECIMAL(10,2) NOT NULL,
    security_level VARCHAR(50) NOT NULL,
    pickup_date DATE NOT NULL,
    special_requirements TEXT,
    status ENUM('pending', 'approved', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    estimated_price DECIMAL(10,2),
    assigned_vehicle VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_pickup_date (pickup_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel pentru statistici și raportări
CREATE TABLE IF NOT EXISTS order_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    period_month VARCHAR(7) NOT NULL,  -- Format: YYYY-MM
    total_orders INT DEFAULT 0,
    completed_orders INT DEFAULT 0,
    cancelled_orders INT DEFAULT 0,
    total_revenue DECIMAL(12,2) DEFAULT 0,
    avg_delivery_time DECIMAL(5,2),  -- în ore
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_period (period_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
