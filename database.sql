-- Simple MySQL schema (2NF) for E1 Personal Record
-- Focus: required Personal Data fields + Home Address stored separately

CREATE DATABASE IF NOT EXISTS e1_personal_record_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE e1_personal_record_db;

-- Lookup table so Civil Status values are not repeated as free text
CREATE TABLE IF NOT EXISTS civil_statuses (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  code VARCHAR(30) NOT NULL,
  name VARCHAR(60) NOT NULL,
  requires_other TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_civil_status_code (code)
) ENGINE=InnoDB;

INSERT INTO civil_statuses (code, name, requires_other) VALUES
  ('single', 'Single', 0),
  ('married', 'Married', 0),
  ('widowed', 'Widowed', 0),
  ('legally_separated', 'Legally Separated', 0),
  ('others', 'Others', 1)
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  requires_other = VALUES(requires_other);

-- Main person record (required fields live here)
CREATE TABLE IF NOT EXISTS persons (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

  last_name VARCHAR(80) NOT NULL,
  first_name VARCHAR(80) NOT NULL,
  middle_name VARCHAR(80) NOT NULL,

  date_of_birth DATE NOT NULL,
  sex ENUM('male','female') NOT NULL,

  civil_status_id INT UNSIGNED NOT NULL,
  civil_status_other VARCHAR(120) NULL,

  nationality VARCHAR(80) NOT NULL,
  place_of_birth VARCHAR(150) NOT NULL,

  mobile_number VARCHAR(30) NOT NULL,
  email VARCHAR(120) NOT NULL,

  religion VARCHAR(80) NULL,
  telephone_number VARCHAR(30) NULL,

  father_last_name VARCHAR(80) NULL,
  father_first_name VARCHAR(80) NULL,
  father_middle_name VARCHAR(80) NULL,

  mother_last_name VARCHAR(80) NULL,
  mother_first_name VARCHAR(80) NULL,
  mother_middle_name VARCHAR(80) NULL,

  -- Checkbox: "The same with Home Address"
  same_as_home_address TINYINT(1) NOT NULL DEFAULT 0,

  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_civil_status_id (civil_status_id),
  CONSTRAINT fk_persons_civil_status
    FOREIGN KEY (civil_status_id) REFERENCES civil_statuses(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Home Address saved individually (separate table)
-- 1 person = 0/1 home address record (PK is also FK)
CREATE TABLE IF NOT EXISTS person_home_addresses (
  person_id BIGINT UNSIGNED NOT NULL,
  address_line VARCHAR(200) NOT NULL,
  zip_code VARCHAR(20) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (person_id),
  CONSTRAINT fk_home_address_person
    FOREIGN KEY (person_id) REFERENCES persons(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- Part 2: Dependents/Beneficiaries
-- One table for spouse, children, and other beneficiaries.
CREATE TABLE IF NOT EXISTS person_dependents (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  person_id BIGINT UNSIGNED NOT NULL,
  dependent_type ENUM('spouse','child','other') NOT NULL,
  last_name VARCHAR(80) NOT NULL,
  first_name VARCHAR(80) NOT NULL,
  middle_name VARCHAR(80) NULL,
  suffix VARCHAR(20) NULL,
  date_of_birth DATE NULL,
  relationship VARCHAR(60) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_person_dependents_person_id (person_id),
  CONSTRAINT fk_dependents_person
    FOREIGN KEY (person_id) REFERENCES persons(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- Part 3: Self-Employed (SE)
CREATE TABLE IF NOT EXISTS person_self_employment (
  person_id BIGINT UNSIGNED NOT NULL,
  profession_business VARCHAR(150) NULL,
  year_started VARCHAR(10) NULL,
  monthly_earnings VARCHAR(30) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (person_id),
  CONSTRAINT fk_se_person
    FOREIGN KEY (person_id) REFERENCES persons(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- Part 3: Overseas Filipino Worker (OFW)
CREATE TABLE IF NOT EXISTS person_ofw (
  person_id BIGINT UNSIGNED NOT NULL,
  foreign_address VARCHAR(200) NULL,
  monthly_earnings VARCHAR(30) NULL,
  flexi_fund ENUM('yes','no') NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (person_id),
  CONSTRAINT fk_ofw_person
    FOREIGN KEY (person_id) REFERENCES persons(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- Part 3: Non-Working Spouse (NWS)
-- Note: file upload is stored as a file path string.
CREATE TABLE IF NOT EXISTS person_nws (
  person_id BIGINT UNSIGNED NOT NULL,
  working_spouse_ss_no VARCHAR(30) NULL,
  working_spouse_monthly_income VARCHAR(30) NULL,
  working_spouse_signature_file_path VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (person_id),
  CONSTRAINT fk_nws_person
    FOREIGN KEY (person_id) REFERENCES persons(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- Part 4: Certification
-- Note: signature file upload is stored as a file path string.
CREATE TABLE IF NOT EXISTS person_certifications (
  person_id BIGINT UNSIGNED NOT NULL,
  printed_name VARCHAR(150) NULL,
  signature_text VARCHAR(150) NULL,
  signature_file_path VARCHAR(255) NULL,
  cert_date DATE NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (person_id),
  CONSTRAINT fk_cert_person
    FOREIGN KEY (person_id) REFERENCES persons(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- Part 5: To be filled out by SSS
-- Note: all signature uploads are stored as file path strings.
CREATE TABLE IF NOT EXISTS person_sss_processing (
  person_id BIGINT UNSIGNED NOT NULL,
  business_code VARCHAR(50) NULL,
  working_spouse_msc VARCHAR(50) NULL,
  monthly_contribution VARCHAR(50) NULL,
  approved_msc VARCHAR(50) NULL,
  start_of_payment VARCHAR(50) NULL,
  flexi_status ENUM('approved','disapproved') NULL,

  received_by_signature_path VARCHAR(255) NULL,
  received_by_datetime DATETIME NULL,

  processed_by_signature_path VARCHAR(255) NULL,
  processed_by_datetime DATETIME NULL,

  reviewed_by_signature_path VARCHAR(255) NULL,
  reviewed_by_datetime DATETIME NULL,

  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (person_id),
  CONSTRAINT fk_sss_processing_person
    FOREIGN KEY (person_id) REFERENCES persons(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB;
