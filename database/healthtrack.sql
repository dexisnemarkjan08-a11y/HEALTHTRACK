-- HealthTrack database. Import in phpMyAdmin (Import tab). It creates healthtrack_db itself.
CREATE DATABASE IF NOT EXISTS healthtrack_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE healthtrack_db;
DROP TABLE IF EXISTS consultations; DROP TABLE IF EXISTS appointments; DROP TABLE IF EXISTS doctor_visit_dates; DROP TABLE IF EXISTS patients; DROP TABLE IF EXISTS users;
CREATE TABLE users(id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(40) NOT NULL UNIQUE, full_name VARCHAR(100) NOT NULL, password_hash VARCHAR(255) NOT NULL, role ENUM('Health worker','Midwife','Administrator') NOT NULL);
CREATE TABLE doctor_visit_dates(visit_date DATE PRIMARY KEY, visit_time TIME NULL, created_by INT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL);
CREATE TABLE patients(id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(120) NOT NULL, sex ENUM('Female','Male') NOT NULL, birthdate DATE NOT NULL, purok VARCHAR(20) NOT NULL, contact VARCHAR(30) DEFAULT '', conditions_text VARCHAR(200) DEFAULT '', allergies VARCHAR(200) DEFAULT '', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX(name), INDEX(purok));
CREATE TABLE consultations(id INT AUTO_INCREMENT PRIMARY KEY, patient_id INT NOT NULL, visit_date DATE NOT NULL, complaint VARCHAR(200) NOT NULL, bp VARCHAR(15) DEFAULT '', temp VARCHAR(10) DEFAULT '', weight VARCHAR(15) DEFAULT '', diagnosis VARCHAR(200) DEFAULT '', treatment VARCHAR(300) DEFAULT '', recorded_by INT NULL, FOREIGN KEY(patient_id) REFERENCES patients(id) ON DELETE CASCADE, FOREIGN KEY(recorded_by) REFERENCES users(id) ON DELETE SET NULL);
CREATE TABLE appointments(id INT AUTO_INCREMENT PRIMARY KEY, patient_id INT NULL, patient_name VARCHAR(120) NOT NULL, patient_contact VARCHAR(30) NULL DEFAULT NULL, purok VARCHAR(20) NOT NULL, appt_date DATE NOT NULL, appt_time TIME NOT NULL, service VARCHAR(60) NOT NULL, status ENUM('Scheduled','Completed','No-show','Cancelled') NOT NULL DEFAULT 'Scheduled', FOREIGN KEY(patient_id) REFERENCES patients(id) ON DELETE CASCADE, INDEX(appt_date,appt_time));
INSERT INTO users(username,full_name,password_hash,role) VALUES
('admin','Barangay Administrator','$2y$12$D/hcHI5JfkSb3FQZPMbRAeUJ25vYKht.aBbSdA/.hppakVNGxgv7a','Administrator'),
('midwife','Rosa Villanueva, RM','$2y$12$1hK/vj1ygACEJG4vxpElGuCZ4wkQRXYFf6SpiTSrzZdbxPsG8zR3a','Midwife'),
('bhw','Barangay Health Worker','$2y$12$TJWqkiJzZu.sPNtyz1rvZ.A2./pbzgzyb3bkb2FTfC.nIbw1BXdqu','Health worker');
INSERT INTO patients(name,sex,birthdate,purok,contact,conditions_text,allergies) VALUES
('Maria Santos','Female','1993-03-14','Purok 2','0917 555 0142','','None known'),
('Jose Dela Cruz','Male','1958-07-02','Purok 4','0920 555 0177','Hypertension','Penicillin'),
('Angelica Reyes','Female','2025-05-21','Purok 1','0918 555 0129','','None known'),
('Ramon Bautista','Male','1975-11-30','Purok 3','','Diabetes','None known'),
('Liza Mendoza','Female','1989-01-09','Purok 5','0905 555 0163','','Sulfa drugs'),
('Noel Garcia','Male','2020-09-12','Purok 2','0927 555 0108','','None known');
INSERT INTO consultations(patient_id,visit_date,complaint,bp,temp,weight,diagnosis,treatment,recorded_by) VALUES
(1,CURDATE()-INTERVAL 20 DAY,'Prenatal visit, 20 weeks','110/70','36.6','58 kg','Normal pregnancy','Iron and folic acid',2),
(2,CURDATE()-INTERVAL 35 DAY,'Dizziness','150/95','36.8','72 kg','Hypertension, uncontrolled','Amlodipine 5 mg, recheck in 2 weeks',2),
(2,CURDATE()-INTERVAL 6 DAY,'BP follow-up','135/85','36.5','71 kg','Hypertension, improving','Continue medication',2),
(3,CURDATE()-INTERVAL 12 DAY,'Routine immunization','','36.7','9.4 kg','Well child','Pentavalent dose 3',2),
(5,CURDATE()-INTERVAL 3 DAY,'Cough for 2 weeks','120/80','37.2','55 kg','Suspected bronchitis','Referred for sputum test',2);
INSERT INTO appointments(patient_id,patient_name,purok,appt_date,appt_time,service,status) VALUES
(2,'Jose Dela Cruz','Purok 4',CURDATE(),'08:30','Blood pressure check','Completed'),
(1,'Maria Santos','Purok 2',CURDATE(),'09:30','Prenatal checkup','Scheduled'),
(3,'Angelica Reyes','Purok 1',CURDATE(),'10:00','Immunization','Scheduled'),
(6,'Noel Garcia','Purok 2',CURDATE(),'10:00','Child growth monitoring','Scheduled'),
(4,'Ramon Bautista','Purok 3',CURDATE()+INTERVAL 2 DAY,'09:00','General consultation','Scheduled'),
(5,'Liza Mendoza','Purok 5',CURDATE()+INTERVAL 3 DAY,'13:30','TB follow-up','Scheduled');
