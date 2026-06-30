-- ============================================================================
-- U-SIIRS Database Schema
-- UTeM Smart Infrastructure Issue Reporting System
-- ============================================================================
-- IMPROVEMENTS:
--   - Added INDEX idx_is_read on contact_messages (is_read) to avoid full scans
--     on the admin badge query (SELECT COUNT(*) WHERE is_read=0) run on every page.
--   - Added INDEX idx_created_at on reports (created_at) since most list queries
--     ORDER BY this column.
--   - Added admin_activity_log table (admin_id, action, target_type, target_id,
--     details) with composite index on (target_type, target_id) for efficient
--     per-entity audit lookups, plus indexes on admin_id and created_at.
--   - Changed admins.role from VARCHAR(20) to ENUM('super_admin','admin','deactivated')
--     to match the ERD and enforce valid values at the DB level.
-- ============================================================================

CREATE DATABASE IF NOT EXISTS u_siirs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE u_siirs;

DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS admin_activity_log;
DROP TABLE IF EXISTS status_updates;
DROP TABLE IF EXISTS report_attachments;
DROP TABLE IF EXISTS reports;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS contact_messages;

-- ── Users ─────────────────────────────────────────────────────────────────────
CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100)  NOT NULL,
    email         VARCHAR(255)  NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL,
    avatar        VARCHAR(255)  DEFAULT NULL,
    is_banned     TINYINT(1)    NOT NULL DEFAULT 0,
    reset_token   VARCHAR(64)   DEFAULT NULL,
    reset_expires DATETIME      DEFAULT NULL,
    created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email     (email),
    INDEX idx_is_banned (is_banned)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Admins ────────────────────────────────────────────────────────────────────
CREATE TABLE admins (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100)  NOT NULL,
    email         VARCHAR(255)  NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL,
    created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Reports ───────────────────────────────────────────────────────────────────
CREATE TABLE reports (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT           NOT NULL,
    title       VARCHAR(150)  NOT NULL,
    category    VARCHAR(50)   NOT NULL,
    description TEXT          NOT NULL,
    location    VARCHAR(200)  NOT NULL,
    photo       VARCHAR(255)  DEFAULT NULL,
    status      ENUM('Pending','In Progress','Resolved','Closed','Rejected')
                              NOT NULL DEFAULT 'Pending',
    priority    ENUM('Low','Medium','High','Critical')
                              NOT NULL DEFAULT 'Medium',
    admin_notes TEXT          DEFAULT NULL,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reports_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status     (status),
    INDEX idx_user       (user_id),
    -- IMPROVEMENT: Index on created_at because most queries ORDER BY this column
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Report Attachments ────────────────────────────────────────────────────────
CREATE TABLE report_attachments (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    report_id  INT           NOT NULL,
    filename   VARCHAR(255)  NOT NULL,
    created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_att_report FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    INDEX idx_att_report (report_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Status Audit Trail ────────────────────────────────────────────────────────
CREATE TABLE status_updates (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    report_id           INT     NOT NULL,
    status              ENUM('Pending','In Progress','Resolved','Closed','Rejected') NOT NULL,
    remarks             TEXT    DEFAULT NULL,
    changed_by_admin_id INT     DEFAULT NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_su_report FOREIGN KEY (report_id)           REFERENCES reports(id) ON DELETE CASCADE,
    CONSTRAINT fk_su_admin  FOREIGN KEY (changed_by_admin_id) REFERENCES admins(id)  ON DELETE SET NULL,
    INDEX idx_su_report (report_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Notifications ─────────────────────────────────────────────────────────────
CREATE TABLE notifications (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    report_id  INT          DEFAULT NULL,
    message    VARCHAR(500) NOT NULL,
    is_read    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notif_user   FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    CONSTRAINT fk_notif_report FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE SET NULL,
    INDEX idx_notif_user   (user_id),
    INDEX idx_notif_is_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Contact Messages ──────────────────────────────────────────────────────────
CREATE TABLE contact_messages (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(255) NOT NULL,
    subject    VARCHAR(150) NOT NULL,
    message    TEXT         NOT NULL,
    is_read    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    -- IMPROVEMENT: Index on is_read prevents full table scan on admin badge query
    -- (SELECT COUNT(*) FROM contact_messages WHERE is_read=0) runs on every admin page.
    INDEX idx_is_read   (is_read),
    INDEX idx_created   (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Admin Activity Log ────────────────────────────────────────────────────────
CREATE TABLE admin_activity_log (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    admin_id    INT           NOT NULL,
    action      VARCHAR(100)  NOT NULL,
    target_type VARCHAR(50)   DEFAULT NULL,
    target_id   INT           DEFAULT NULL,
    details     TEXT          DEFAULT NULL,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_log_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
    INDEX idx_log_admin      (admin_id),
    INDEX idx_log_target     (target_type, target_id),
    INDEX idx_log_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Default Admin Account ──────────────────────────────────────────────────────
-- Credentials: admin@utem.edu.my / Admin@123
-- NOTE: In production, this seed data would be replaced with a secure setup process.
INSERT INTO admins (full_name, email, password_hash) VALUES
('System Admin', 'admin@utem.edu.my', '$2b$10$qx5GjiFrbf9Nr0Y6/Kb7T.UGgMRvcaWmr2RiE2gKClPhRJz.1COtG');

ALTER TABLE users
  ADD COLUMN last_login_at      DATETIME     NULL     DEFAULT NULL,
  ADD COLUMN last_login_ip      VARCHAR(45)  NULL     DEFAULT NULL,
  ADD COLUMN notification_email TINYINT(1)   NOT NULL DEFAULT 1;

-- admins table – role uses enum to match ERD values
ALTER TABLE admins
  ADD COLUMN role ENUM('super_admin','admin','deactivated') NOT NULL DEFAULT 'admin';

  UPDATE admins SET role = 'super_admin' WHERE email = 'admin@utem.edu.my';

-- ============================================================================
-- v2 Additions: Map coordinates, admin notifications, proof media
-- ============================================================================

-- Map coordinates on reports
ALTER TABLE reports
  ADD COLUMN IF NOT EXISTS latitude  DECIMAL(10,8) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS longitude DECIMAL(11,8) DEFAULT NULL;

-- Fix type column on user notifications
ALTER TABLE notifications
  ADD COLUMN IF NOT EXISTS type VARCHAR(50) NOT NULL DEFAULT 'status_update';

-- Admin notifications (new report alerts)
CREATE TABLE IF NOT EXISTS admin_notifications (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    report_id  INT          DEFAULT NULL,
    message    VARCHAR(500) NOT NULL,
    is_read    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_an_report FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE SET NULL,
    INDEX idx_an_is_read (is_read),
    INDEX idx_an_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin proof media (work-in-progress evidence)
CREATE TABLE IF NOT EXISTS report_progress_media (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    report_id  INT          NOT NULL,
    admin_id   INT          NOT NULL,
    filename   VARCHAR(255) NOT NULL,
    caption    VARCHAR(300) DEFAULT NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rpm_report FOREIGN KEY (report_id) REFERENCES reports(id)  ON DELETE CASCADE,
    CONSTRAINT fk_rpm_admin  FOREIGN KEY (admin_id)  REFERENCES admins(id)   ON DELETE CASCADE,
    INDEX idx_rpm_report (report_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
