<?php
require 'includes/config.php';
need();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    chk();
    $action = $_POST['action'] ?? '';
    if ($action === 'status') {
        need(['Midwife', 'Administrator']);
        $allowedStatuses = ['Completed', 'No-show', 'Cancelled'];
        if (in_array($_POST['status'] ?? '', $allowedStatuses, true)) {
            q('UPDATE appointments SET status=? WHERE id=?', [$_POST['status'], (int) ($_POST['id'] ?? 0)]);
        }
        $back = $_POST['back'] ?? '';
        flash('Appointment updated.');
        header('Location: ' . (strpos($back, 'index.php') === 0 || strpos($back, 'appointments.php') === 0 ? $back : 'appointments.php'));
        exit;
    }

    if ($action === 'book') {
        $patientName = is_string($_POST['patient_name'] ?? null) ? trim($_POST['patient_name']) : '';
        $patientContact = is_string($_POST['patient_contact'] ?? null) ? trim($_POST['patient_contact']) : '';
        $purok = is_string($_POST['purok'] ?? null) ? $_POST['purok'] : '';
        $date = is_string($_POST['appt_date'] ?? null) ? $_POST['appt_date'] : '';
        $time = is_string($_POST['appt_time'] ?? null) ? $_POST['appt_time'] : '';
        $service = is_string($_POST['service'] ?? null) ? $_POST['service'] : '';
        $linkedId = filter_var($_POST['patient_id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
        $message = '';

        $contactDigits = preg_replace('/\D/', '', $patientContact);
        $invalidContact = $patientContact !== ''
            && (!preg_match('/^[0-9+().\s-]{7,30}$/', $patientContact) || strlen($contactDigits) < 7 || strlen($contactDigits) > 15);
        if ($invalidContact) {
            $message = 'Enter a valid contact number with 7–15 digits, or leave it blank.';
        } elseif ($patientName === '' || mb_strlen($patientName, 'UTF-8') > 120 || !in_array($purok, PUROKS, true)
            || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
            || !checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4))
            || $date < date('Y-m-d') || !in_array($time, times(), true) || !in_array($service, SERVICES, true)) {
            $message = 'Enter a patient name and select a purok, then check the date, time and service.';
        } else {
            if ($linkedId > 0) {
                $linkedPatient = q('SELECT id, name, purok FROM patients WHERE id=?', [$linkedId])->fetch();
                if ($linkedPatient) {
                    // Trust the selected record as the source of truth for linked bookings.
                    $patientName = $linkedPatient['name'];
                    $purok = $linkedPatient['purok'];
                } else {
                    $linkedId = 0;
                }
            }

            // Typed names remain supported. Link only when name + purok identifies one record.
            if (!$linkedId) {
                $matches = q('SELECT id FROM patients WHERE LOWER(name)=LOWER(?) AND purok=? LIMIT 2', [$patientName, $purok])->fetchAll();
                if (count($matches) === 1) {
                    $linkedId = (int) $matches[0]['id'];
                }
            }

            $sameTime = q("SELECT patient_id, patient_name, purok FROM appointments WHERE appt_date=? AND appt_time=? AND status<>'Cancelled'", [$date, $time])->fetchAll();
            $duplicate = false;
            foreach ($sameTime as $booking) {
                $sameRecord = $linkedId > 0 && (int) ($booking['patient_id'] ?? 0) === $linkedId;
                $sameTypedPatient = mb_strtolower(trim($booking['patient_name']), 'UTF-8') === mb_strtolower($patientName, 'UTF-8')
                    && $booking['purok'] === $purok;
                if ($sameRecord || $sameTypedPatient) {
                    $duplicate = true;
                    break;
                }
            }

            if ($duplicate) {
                $message = 'This patient already has an appointment at that time.';
            } elseif (count($sameTime) >= 2) {
                $message = 'That time already has 2 patients booked. Choose another time.';
            } else {
                q('INSERT INTO appointments(patient_id,patient_name,patient_contact,purok,appt_date,appt_time,service) VALUES(?,?,?,?,?,?,?)', [$linkedId ?: null, $patientName, $patientContact, $purok, $date, $time, $service]);
                $message = $linkedId ? 'Appointment booked and linked to the patient record.' : 'Appointment booked.';
            }
        }
        flash($message);
        header('Location: appointments.php?d=' . urlencode($date));
        exit;
    }
}

$dateFilter = isset($_GET['d']) ? (string) $_GET['d'] : date('Y-m-d');
$statusFilter = is_string($_GET['s'] ?? '') ? $_GET['s'] : '';
$rows = q("SELECT a.*,COALESCE(NULLIF(a.patient_name,''),p.name) name,COALESCE(NULLIF(a.purok,''),p.purok,'') purok,
        COALESCE(NULLIF(a.patient_contact,''),p.contact,'') contact
    FROM appointments a LEFT JOIN patients p ON p.id=a.patient_id
    WHERE (?='' OR a.appt_date=?) AND (?='' OR a.status=?) ORDER BY a.appt_date,a.appt_time",
    [$dateFilter, $dateFilter, $statusFilter, $statusFilter])->fetchAll();
$selectedPatientId = filter_var($_GET['patient'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
$selectedPatient = $selectedPatientId ? q('SELECT id,name,purok,contact FROM patients WHERE id=?', [$selectedPatientId])->fetch() : null;
if (!$selectedPatient) {
    $selectedPatientId = 0;
}
$patientOptions = q('SELECT name,purok FROM patients ORDER BY name')->fetchAll();

head('Appointments', 'appointments.php'); ?>
<div class="bar"><div><h1>Appointments</h1><small>Coordinate visits with patient records and the daily queue.</small></div><a class="btn pri" href="#book">Book appointment</a></div>
<form class="tools" method="get"><input type="date" name="d" value="<?= e($dateFilter) ?>" aria-label="Date"><select name="s" aria-label="Status"><option value="">All statuses</option><?php foreach (['Scheduled', 'Completed', 'No-show', 'Cancelled'] as $option): ?><option value="<?= e($option) ?>"<?= $option === $statusFilter ? ' selected' : '' ?>><?= e($option) ?></option><?php endforeach ?></select><button>Filter</button><a class="btn" href="appointments.php">Today</a><a class="btn" href="appointments.php?d=">All dates</a></form>
<div class="card"><ul class="al"><?php foreach ($rows as $row) arow($row, 'appointments.php?' . http_build_query(['d' => $dateFilter, 's' => $statusFilter]), $dateFilter === ''); if (!$rows) echo '<li class="empty">No appointments for this filter. Pick another date or book one below.</li>'; ?></ul></div>
<details id="book" class="card pad appointment-booking"<?= $selectedPatient ? ' open' : '' ?> style="margin-top:18px">
    <summary><span><b>Book appointment</b><small>Choose a patient and an available half-hour slot.</small></span><span class="summary-action">Open form</span></summary>
    <form method="post" class="fg" id="booking-form">
        <input type="hidden" name="t" value="<?= csrf() ?>"><input type="hidden" name="action" value="book">
        <input type="hidden" name="patient_id" id="patient-id" value="<?= $selectedPatient ? (int) $selectedPatient['id'] : '' ?>">
        <label>Patient name<input id="patient-name" name="patient_name" list="patient-options" value="<?= e($selectedPatient['name'] ?? '') ?>" maxlength="120" placeholder="Enter or select a patient" autocomplete="off" required><datalist id="patient-options"><?php foreach ($patientOptions as $option): ?><option value="<?= e($option['name']) ?>" label="<?= e($option['purok']) ?>"></option><?php endforeach ?></datalist></label>
        <label>Contact number (optional)<input type="tel" name="patient_contact" value="<?= e($selectedPatient['contact'] ?? '') ?>" maxlength="30" inputmode="tel" autocomplete="tel" placeholder="e.g. 0917 555 0142" aria-describedby="appointment-contact-hint"><small id="appointment-contact-hint">Enter 7–15 digits. This is saved with the appointment only.</small></label>
        <label>Purok<select id="patient-purok" name="purok" required><option value="">Select purok</option><?php foreach (PUROKS as $purok): ?><option value="<?= e($purok) ?>"<?= ($selectedPatient['purok'] ?? '') === $purok ? ' selected' : '' ?>><?= e($purok) ?></option><?php endforeach ?></select></label>
        <label>Date<input type="date" name="appt_date" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required></label>
        <fieldset class="w time-picker"><legend>Appointment time</legend><div class="time-slots"><?php foreach (times() as $time): ?><label class="time-option"><input type="radio" name="appt_time" value="<?= e($time) ?>" required><span><?= date('g:i A', strtotime($time)) ?></span></label><?php endforeach ?></div><small class="time-hint">Available every 30 minutes from 8:00 AM to 4:30 PM.</small></fieldset>
        <label>Service<select name="service" required><?php foreach (SERVICES as $service): ?><option value="<?= e($service) ?>"><?= e($service) ?></option><?php endforeach ?></select></label>
        <div class="w"><button class="pri">Book appointment</button></div>
    </form>
</details>
<?php foot();
