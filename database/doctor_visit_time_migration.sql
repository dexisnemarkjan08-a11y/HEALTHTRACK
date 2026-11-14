-- Run once if doctor_visit_dates currently has a visit_period column.
-- Existing visit dates are kept; exact times must be entered in the calendar.
ALTER TABLE doctor_visit_dates
  DROP COLUMN visit_period,
  ADD COLUMN visit_time TIME NULL AFTER visit_date;
