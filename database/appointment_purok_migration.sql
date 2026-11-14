-- Run once on an existing HealthTrack database to add appointment purok selection.
ALTER TABLE appointments
  ADD COLUMN purok VARCHAR(20) NULL AFTER patient_name;

UPDATE appointments a
LEFT JOIN patients p ON p.id=a.patient_id
SET a.purok=COALESCE(p.purok,'Purok 1')
WHERE a.purok IS NULL;

ALTER TABLE appointments
  MODIFY purok VARCHAR(20) NOT NULL;
