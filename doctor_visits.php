<?php require 'includes/config.php';
need();
$canEdit = in_array(me()['role'], ['Midwife', 'Administrator'], true);
$month = $_GET['month'] ?? date('Y-m');
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
    http_response_code(400);
    exit('Invalid calendar month.');
}
$monthDate = DateTimeImmutable::createFromFormat('!Y-m-d', $month . '-01');
if (!$monthDate || $monthDate->format('Y-m') !== $month) {
    http_response_code(400);
    exit('Invalid calendar month.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    chk();
    need(['Midwife', 'Administrator']);
    $action = $_POST['action'] ?? '';
    $visitDate = $_POST['visit_date'] ?? '';
    $postedMonth = $_POST['month'] ?? '';
    if (!in_array($action, ['save', 'remove'], true) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $visitDate) || !checkdate((int)substr($visitDate, 5, 2), (int)substr($visitDate, 8, 2), (int)substr($visitDate, 0, 4)) || $postedMonth !== substr($visitDate, 0, 7)) {
        http_response_code(400);
        exit('Invalid doctor visit date.');
    }
    if ($action === 'save') {
        $visitTime = $_POST['visit_time'] ?? '';
        if (!is_string($visitTime) || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $visitTime)) {
            http_response_code(400);
            exit('Choose a valid doctor visit time.');
        }
        q('INSERT INTO doctor_visit_dates(visit_date,visit_time,created_by) VALUES(?,?,?) ON DUPLICATE KEY UPDATE visit_time=VALUES(visit_time),created_by=VALUES(created_by)', [$visitDate, $visitTime . ':00', me()['id']]);
    } else q('DELETE FROM doctor_visit_dates WHERE visit_date=?', [$visitDate]);
    flash($action === 'save' ? 'Doctor visit time saved.' : 'Doctor visit date unmarked.');
    header('Location: doctor_visits.php?month=' . urlencode($postedMonth));
    exit;
}
$monthEnd = $monthDate->modify('+1 month');
$markedRows = q('SELECT visit_date,visit_time FROM doctor_visit_dates WHERE visit_date>=? AND visit_date<? ORDER BY visit_date', [$monthDate->format('Y-m-d'), $monthEnd->format('Y-m-d')])->fetchAll();
$marked = [];
foreach ($markedRows as $row) $marked[$row['visit_date']] = $row['visit_time'];
$days = (int)$monthDate->format('t');
$offset = (int)$monthDate->format('N') - 1;
$previous = $monthDate->modify('-1 month')->format('Y-m');
$next = $monthDate->modify('+1 month')->format('Y-m');
$today = date('Y-m-d');
head('Doctor visit calendar', 'doctor_visits.php'); ?>
<div class="bar">
    <div>
        <h1>Doctor visit calendar</h1><small>Shared schedule for doctor visits</small>
    </div><a class="btn" href="index.php">Back to dashboard</a>
</div>
<section class="card pad doctor-calendar">
    <div class="doctor-calendar-head"><a class="btn" href="doctor_visits.php?month=<?= $previous ?>" aria-label="Previous month">&larr;</a>
        <h2><?= e($monthDate->format('F Y')) ?></h2><a class="btn" href="doctor_visits.php?month=<?= $next ?>" aria-label="Next month">&rarr;</a>
    </div>
    <div class="doctor-calendar-grid" role="grid" aria-label="Doctor visit dates for <?= e($monthDate->format('F Y')) ?>">
        <?php foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $weekday): ?><div class="doctor-calendar-weekday" role="columnheader"><?= $weekday ?></div><?php endforeach ?>
        <?php for ($i = 0; $i < $offset; $i++): ?><div class="doctor-calendar-empty" role="gridcell"></div><?php endfor ?>
        <?php for ($day = 1; $day <= $days; $day++): $date = $monthDate->format('Y-m-') . sprintf('%02d', $day);
            $isVisit = array_key_exists($date, $marked);
            $visitTime = $marked[$date] ?? '';
            $timeValue = $visitTime ? date('H:i', strtotime($visitTime)) : '';
            $classes = 'doctor-calendar-day' . ($isVisit ? ' is-visit' : '') . ($date === $today ? ' is-today' : ''); ?>
            <div class="doctor-calendar-cell" role="gridcell">
                <?php if ($canEdit): ?><form method="post" class="doctor-calendar-form<?= $isVisit ? ' is-marked' : '' ?>"><input type="hidden" name="t" value="<?= csrf() ?>"><input type="hidden" name="month" value="<?= $month ?>"><input type="hidden" name="visit_date" value="<?= $date ?>"><span class="doctor-calendar-date"><?= $day ?></span><input class="doctor-calendar-time" type="time" name="visit_time" value="<?= e($timeValue) ?>" required aria-label="Doctor visit time on <?= $monthDate->format('F') . ' ' . $day ?>"><button class="doctor-calendar-save" type="submit" name="action" value="save"><?= $isVisit ? 'Save' : 'Set time' ?></button><?php if ($isVisit): ?><span class="doctor-calendar-current"><?= e(date('H:i', strtotime($visitTime))) ?></span><button class="doctor-calendar-remove" type="submit" name="action" value="remove" formnovalidate aria-label="Remove doctor visit on <?= $monthDate->format('F') . ' ' . $day ?>">Remove</button><?php endif ?></form>
                <?php else: ?><div class="<?= $classes ?>" aria-label="<?= e($monthDate->format('F') . ' ' . $day . ($isVisit ? ', doctor visit ' . ($visitTime ? date('H:i', strtotime($visitTime)) : 'time not set') : '')) ?>"><span><?= $day ?></span><?php if ($isVisit): ?><small><?= $visitTime ? date('H:i', strtotime($visitTime)) : 'Time not set' ?></small><?php endif ?></div><?php endif ?>
            </div>
        <?php endfor ?>
    </div>
    <div class="doctor-calendar-legend"><span><i class="doctor-calendar-swatch"></i> Doctor visit date and time</span><span>Outlined date is today</span></div>
    <p class="note"><?= $canEdit ? 'Enter a time in 24-hour format, then select Set time or Save. Use Remove to clear a date.' : 'Doctor visit dates and times are managed by an Administrator or Midwife.' ?></p>
</section>
<?php foot();
