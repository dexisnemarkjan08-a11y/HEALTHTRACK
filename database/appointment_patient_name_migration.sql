-- Run once on an existing HealthTrack database to allow bookings for names
-- that are not yet in the patient records.
ALTER TABLE appointments
  ADD COLUMN patient_name VARCHAR(120) NULL AFTER patient_id;

UPDATE appointments a
JOIN patients p ON p.id=a.patient_id
SET a.patient_name=p.name
WHERE a.patient_name IS NULL;

ALTER TABLE appointments
  MODIFY patient_id INT NULL,
  MODIFY patient_name VARCHAR(120) NOT NULL;
