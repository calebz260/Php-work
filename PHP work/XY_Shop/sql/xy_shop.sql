-- ============================================================
-- XY_Shop Database Schema — Full + Security Tables
-- ============================================================

CREATE DATABASE IF NOT EXISTS XY_Shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE XY_Shop;

-- ------------------------------------------------------------
-- Table: Shopkeeper
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Shopkeeper (
    ShopkeeperId INT AUTO_INCREMENT PRIMARY KEY,
    UserName     VARCHAR(100) NOT NULL UNIQUE,
    Password     VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: Product
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Product (
    ProductCode INT AUTO_INCREMENT PRIMARY KEY,
    ProductName VARCHAR(200) NOT NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: ProductIn
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ProductIn (
    Id          INT AUTO_INCREMENT PRIMARY KEY,
    ProductCode INT NOT NULL,
    DateTime    DATETIME NOT NULL,
    Quantity    INT NOT NULL,
    UnitPrice   DECIMAL(10,2) NOT NULL,
    TotalPrice  DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (ProductCode) REFERENCES Product(ProductCode) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: ProductOut
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ProductOut (
    Id          INT AUTO_INCREMENT PRIMARY KEY,
    ProductCode INT NOT NULL,
    DateTime    DATETIME NOT NULL,
    Quantity    INT NOT NULL,
    UnitPrice   DECIMAL(10,2) NOT NULL,
    TotalPrice  DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (ProductCode) REFERENCES Product(ProductCode) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: login_attempts
-- Tracks failed login attempts per IP for rate limiting.
-- Records are cleaned up automatically after the lockout window.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS login_attempts (
    Id          INT AUTO_INCREMENT PRIMARY KEY,
    IpAddress   VARCHAR(45)  NOT NULL,
    AttemptTime DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_time (IpAddress, AttemptTime)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: audit_log
-- Immutable record of every significant action in the system.
-- Answers: WHO did WHAT, WHEN, and from WHERE.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS audit_log (
    Id          INT AUTO_INCREMENT PRIMARY KEY,
    UserId      INT          NULL,                      -- NULL for unauthenticated events
    Action      VARCHAR(100) NOT NULL,                  -- e.g. LOGIN_SUCCESS, ADD_PRODUCT
    Details     TEXT         NOT NULL,                  -- Human-readable description
    IpAddress   VARCHAR(45)  NOT NULL DEFAULT '',
    UserAgent   VARCHAR(500) NOT NULL DEFAULT '',
    CreatedAt   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user   (UserId),
    INDEX idx_action (Action),
    INDEX idx_time   (CreatedAt)
) ENGINE=InnoDB;
