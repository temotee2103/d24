-- D24项目数据库表结构

-- 用户表
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nickname VARCHAR(100),
    phone VARCHAR(20),
    notes TEXT,
    role ENUM('super_admin', 'admin', 'agent') NOT NULL,
    status ENUM('active', 'suspended') DEFAULT 'active',
    commission_rate DECIMAL(5,2) NOT NULL DEFAULT 0,
    can_create_subagent TINYINT(1) DEFAULT 0,
    credit_limit DECIMAL(12,2) DEFAULT 0,
    used_credit DECIMAL(12,2) DEFAULT 0,
    balance DECIMAL(12,2) DEFAULT 0,
    parent_id INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 插入默认超级管理员
INSERT INTO users (username, password, nickname, role, commission_rate, can_create_subagent, status)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '超级管理员', 'super_admin', 100, 1, 'active');
-- 注意：密码是'password'的哈希值，实际使用时请修改

-- 订单表
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    total_amount DECIMAL(12,2) NOT NULL,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- 交易记录表
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT NULL,
    type ENUM('deposit', 'withdraw', 'commission', 'winning') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    balance_before DECIMAL(12,2) NOT NULL,
    balance_after DECIMAL(12,2) NOT NULL,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 奖金规则表
CREATE TABLE prize_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prize_type ENUM('头奖', '二将', '三将', '特别奖', '安慰奖') NOT NULL,
    size_type VARCHAR(10) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    UNIQUE KEY (prize_type, size_type)
) ENGINE=InnoDB;

-- 插入默认奖金规则
INSERT INTO prize_rules (prize_type, size_type, amount) VALUES
('头奖', '大', 2500),
('头奖', '小', 3500),
('头奖', '4A', 6000),
('二将', '大', 1000),
('二将', '小', 2000),
('二将', '4B', 6000),
('三将', '大', 500),
('三将', '小', 1000),
('三将', '4C', 6000),
('特别奖', '大', 200),
('特别奖', '小', 0),
('安慰奖', '大', 60),
('安慰奖', '小', 0); 