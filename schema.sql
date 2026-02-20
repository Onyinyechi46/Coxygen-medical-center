CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  email_verified_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE email_verifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_email_verifications_user
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_email_verifications_user (user_id),
  INDEX idx_email_verifications_token (token_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE kyc_submissions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  full_name VARCHAR(255) NOT NULL,
  phone_number VARCHAR(50) NOT NULL,
  country VARCHAR(100) NOT NULL,
  business_name VARCHAR(255) NULL,
  id_document_path VARCHAR(500) NULL,
  id_document_mime VARCHAR(100) NULL,
  id_document_size INT UNSIGNED NULL,
  status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  reviewed_at DATETIME NULL,
  reviewer_notes TEXT NULL,
  CONSTRAINT fk_kyc_submissions_user
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_kyc_submissions_user (user_id),
  INDEX idx_kyc_submissions_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE user_wallets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  wallet_address VARCHAR(200) NOT NULL,
  wallet_address_hash CHAR(64) NOT NULL,
  status ENUM('pending', 'verified', 'revoked') NOT NULL DEFAULT 'pending',
  verified_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_user_wallets_user
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY uq_wallet_hash (wallet_address_hash),
  INDEX idx_user_wallets_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE invoice_transactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tx_hash VARCHAR(128) NOT NULL,
  action_type VARCHAR(50) NOT NULL,
  invoice_ref VARCHAR(100) NULL,
  actor_wallet_address VARCHAR(200) NOT NULL,
  actor_wallet_hash CHAR(64) NOT NULL,
  counterparty_wallet_address VARCHAR(200) NULL,
  counterparty_wallet_hash CHAR(64) NULL,
  amount_lovelace VARCHAR(40) NULL,
  asset_unit VARCHAR(100) NOT NULL DEFAULT 'lovelace',
  face_value_lovelace VARCHAR(40) NULL,
  repayment_lovelace VARCHAR(40) NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'submitted',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_invoice_transactions_tx_hash (tx_hash),
  INDEX idx_invoice_transactions_action (action_type),
  INDEX idx_invoice_transactions_actor_hash (actor_wallet_hash),
  INDEX idx_invoice_transactions_counter_hash (counterparty_wallet_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE medical_transactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tx_hash VARCHAR(128) NOT NULL,
  action_type ENUM('open_credit', 'repay_credit', 'liquidate_credit') NOT NULL,
  patient_wallet_address VARCHAR(200) NOT NULL,
  patient_wallet_hash CHAR(64) NOT NULL,
  provider_wallet_address VARCHAR(200) NULL,
  provider_wallet_hash CHAR(64) NULL,
  collateral_lovelace VARCHAR(40) NULL,
  bill_lovelace VARCHAR(40) NULL,
  interest_lovelace VARCHAR(40) NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'submitted',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_medical_transactions_tx_hash (tx_hash),
  INDEX idx_medical_transactions_action (action_type),
  INDEX idx_medical_transactions_patient_hash (patient_wallet_hash),
  INDEX idx_medical_transactions_provider_hash (provider_wallet_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE appointment_reminders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  appointment_at DATETIME NOT NULL,
  reminder_email_sent TINYINT(1) NOT NULL DEFAULT 0,
  reminder_sms_sent TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_appointment_reminders_user
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_appointment_reminders_due (appointment_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
