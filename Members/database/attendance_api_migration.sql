-- Apply once to st_raguel to enable secure attendance phone app sign-in.
USE `st_raguel`;

CREATE TABLE IF NOT EXISTS attendance_api_tokens (
  id BIGINT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  last_used_at DATETIME DEFAULT NULL,
  revoked_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_attendance_api_token_hash (token_hash),
  KEY idx_attendance_api_user_expiry (user_id, expires_at),
  CONSTRAINT fk_attendance_api_token_user
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
