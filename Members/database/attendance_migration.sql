-- Apply once to an existing st_raguel database before using the attendance module.
-- Adds attendance roles, member QR identifiers, and attendance workflow tables.
-- Import the current st_raguel.sql first, then execute this file once in phpMyAdmin.
-- Create Attendance Controller and Attendance Holder accounts from Super Admin > Users.

USE `st_raguel`;

ALTER TABLE users
  MODIFY role ENUM(
    'Admin',
    'Editor',
    'Super Admin',
    'Members Manager',
    'Attendance Controller',
    'Attendance Holder',
    'Members'
  ) DEFAULT 'Admin';

ALTER TABLE members
  ADD COLUMN membership_status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  ADD COLUMN qr_token CHAR(32) NULL;

UPDATE members
  SET qr_token = LOWER(SUBSTRING(SHA2(CONCAT(UUID(), RAND(), id), 256), 1, 32))
  WHERE qr_token IS NULL;

ALTER TABLE members
  MODIFY qr_token CHAR(32) NOT NULL,
  ADD UNIQUE KEY uq_members_qr_token (qr_token),
  ADD KEY idx_membership_status (membership_status);

CREATE TABLE attendance_sessions (
  id INT NOT NULL AUTO_INCREMENT,
  title VARCHAR(180) NOT NULL,
  event_id INT DEFAULT NULL,
  session_start DATETIME NOT NULL,
  checkin_opens_at DATETIME NOT NULL,
  checkin_closes_at DATETIME NOT NULL,
  location VARCHAR(255) NOT NULL,
  status ENUM('open', 'submitted', 'approved', 'cancelled') NOT NULL DEFAULT 'open',
  holder_user_id INT NOT NULL,
  created_by INT NOT NULL,
  submitted_at DATETIME DEFAULT NULL,
  approved_by INT DEFAULT NULL,
  approved_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_attendance_session_start (session_start),
  KEY idx_attendance_session_status (status),
  KEY idx_attendance_holder_status (holder_user_id, status),
  CONSTRAINT chk_attendance_checkin_window
    CHECK (checkin_closes_at > checkin_opens_at),
  CONSTRAINT fk_attendance_session_event
    FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE SET NULL,
  CONSTRAINT fk_attendance_session_holder
    FOREIGN KEY (holder_user_id) REFERENCES users (id),
  CONSTRAINT fk_attendance_session_creator
    FOREIGN KEY (created_by) REFERENCES users (id),
  CONSTRAINT fk_attendance_session_approver
    FOREIGN KEY (approved_by) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE attendance_session_roster (
  session_id INT NOT NULL,
  member_id INT NOT NULL,
  PRIMARY KEY (session_id, member_id),
  KEY idx_attendance_roster_member (member_id),
  CONSTRAINT fk_attendance_roster_session
    FOREIGN KEY (session_id) REFERENCES attendance_sessions (id) ON DELETE CASCADE,
  CONSTRAINT fk_attendance_roster_member
    FOREIGN KEY (member_id) REFERENCES members (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE attendance_records (
  id INT NOT NULL AUTO_INCREMENT,
  session_id INT NOT NULL,
  member_id INT NOT NULL,
  status ENUM('present', 'excused', 'absent') NOT NULL,
  method ENUM('manual', 'qr', 'correction') NOT NULL,
  recorded_by INT NOT NULL,
  recorded_at DATETIME DEFAULT NULL,
  reason VARCHAR(500) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_attendance_session_member (session_id, member_id),
  KEY idx_attendance_records_member (member_id),
  KEY idx_attendance_records_user (recorded_by),
  CONSTRAINT fk_attendance_record_roster
    FOREIGN KEY (session_id, member_id)
    REFERENCES attendance_session_roster (session_id, member_id) ON DELETE CASCADE,
  CONSTRAINT fk_attendance_record_user
    FOREIGN KEY (recorded_by) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE attendance_audit_log (
  id BIGINT NOT NULL AUTO_INCREMENT,
  session_id INT DEFAULT NULL,
  member_id INT DEFAULT NULL,
  actor_user_id INT DEFAULT NULL,
  action VARCHAR(40) NOT NULL,
  before_value TEXT DEFAULT NULL,
  after_value TEXT DEFAULT NULL,
  reason VARCHAR(500) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_attendance_audit_session (session_id, created_at),
  KEY idx_attendance_audit_member (member_id, created_at),
  CONSTRAINT fk_attendance_audit_session
    FOREIGN KEY (session_id) REFERENCES attendance_sessions (id) ON DELETE SET NULL,
  CONSTRAINT fk_attendance_audit_member
    FOREIGN KEY (member_id) REFERENCES members (id) ON DELETE SET NULL,
  CONSTRAINT fk_attendance_audit_actor
    FOREIGN KEY (actor_user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
