HEALTHTRACK: Unified Web-Based Patient Record and Appointment System for Barangay Health Operations
(PHP + MySQL, runs on XAMPP)

SETUP
1. Copy the "healthtrack" folder to C:\xampp\htdocs\
2. Open XAMPP Control Panel and start Apache and MySQL.
3. Open http://localhost/phpmyadmin, click the Import tab, choose database/healthtrack.sql, then click Import.
   (The file creates the database healthtrack_db and all tables with sample data.)
4. Open http://localhost/healthtrack/
   If you already have a HealthTrack database with the previous doctor visit calendar, import
   database/doctor_visit_time_migration.sql once in phpMyAdmin. If you have not
   created the calendar table yet, import database/doctor_visit_dates_migration.sql.
   To allow appointment bookings for names not yet in patient records, also import
   database/appointment_patient_name_migration.sql once in phpMyAdmin.
   To add the Purok 1–13 selector to an existing database, import
   database/appointment_purok_migration.sql once in phpMyAdmin.
   To save a contact number with each appointment, import
   database/appointment_contact_migration.sql once in phpMyAdmin.

LOGIN ACCOUNTS (change these passwords before real use)
  admin     / admin123     Administrator: everything, including deleting patients
  midwife   / midwife123   Midwife: manage patients and appointments, consultation notes
  bhw       / bhw123       Health worker: view basic patient information, register patients, book appointments

ACCESS: Only midwives and administrators can edit existing patient records, change
appointment statuses, or view and add consultation notes. Health workers can
view basic patient information, register patients, and book appointments.
Only administrators can delete patients.

IF YOUR MYSQL HAS A PASSWORD: edit includes/config.php (DB_USER, DB_PASS).

MODULES: Today (daily queue and statistics), Patients (records and consultations),
Appointments (book using a typed patient name and Purok 1–13, with a 2-patients-per-slot limit and
status tracking), and the
shared Doctor visit calendar. Administrators and midwives can mark a date and enter
the doctor's exact visit time in 24-hour format.
Typed appointment names are saved with the booking and do not create a patient record.

INTERFACE: Use the theme button in the top bar to switch between light and dark mode. Your choice
is saved in this browser; first visit follows the device color preference. Booking from a patient
record pre-fills and links the appointment. A typed name is linked only when it uniquely matches
one patient by name and purok; unmatched bookings remain available without creating a patient.
