-- Run once on an existing HealthTrack database to save a contact number with each appointment.
ALTER TABLE appointments
  ADD COLUMN patient_contact VARCHAR(30) NULL DEFAULT NULL AFTER patient_name;
