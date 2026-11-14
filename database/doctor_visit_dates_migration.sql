CREATE TABLE IF NOT EXISTS doctor_visit_dates(
  visit_date DATE PRIMARY KEY,
  visit_time TIME NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
);
