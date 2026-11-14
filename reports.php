<?php require 'includes/config.php';need();
$type=$_GET['type']??'';
if(!in_array($type,['today','upcoming','consultations'],true)){http_response_code(404);head('Report not found');echo '<p class="empty">Report not found. <a href="index.php">Back to dashboard</a></p>';foot();exit;}
if($type==='consultations')need(['Midwife','Administrator']);
$start=date('Y-m-01');
$next=date('Y-m-01',strtotime('+1 month'));
if($type==='today'){
 $rows=q("SELECT a.*,COALESCE(NULLIF(a.patient_name,''),p.name) name,COALESCE(NULLIF(a.purok,''),p.purok,'') purok,COALESCE(NULLIF(a.patient_contact,''),p.contact,'') contact FROM appointments a LEFT JOIN patients p ON p.id=a.patient_id WHERE a.status='Scheduled' AND a.appt_date=CURDATE() ORDER BY a.appt_time")->fetchAll();
 head('Still waiting today','index.php');
}elseif($type==='upcoming'){
 $rows=q("SELECT a.*,COALESCE(NULLIF(a.patient_name,''),p.name) name,COALESCE(NULLIF(a.purok,''),p.purok,'') purok,COALESCE(NULLIF(a.patient_contact,''),p.contact,'') contact FROM appointments a LEFT JOIN patients p ON p.id=a.patient_id WHERE a.status='Scheduled' AND a.appt_date>CURDATE() ORDER BY a.appt_date,a.appt_time")->fetchAll();
 head('Upcoming scheduled','index.php');
}else{
 $rows=q('SELECT c.*,p.name,p.purok,u.full_name by_name FROM consultations c JOIN patients p ON p.id=c.patient_id LEFT JOIN users u ON u.id=c.recorded_by WHERE c.visit_date>=? AND c.visit_date<? ORDER BY c.visit_date DESC,c.id DESC',[$start,$next])->fetchAll();
 head('Consultations this month','index.php');
}?>
<div class="bar"><div><h1><?=$type==='today'?'Still waiting today':($type==='upcoming'?'Upcoming scheduled appointments':'Consultations this month')?></h1><small><?=$type==='today'?'Today’s appointments with Scheduled status':($type==='upcoming'?'Future appointments with Scheduled status':'Records for '.date('F Y'))?></small></div><a class="btn" href="index.php">Back to dashboard</a></div>
<?php if($type==='today'||$type==='upcoming'):?>
<section class="card"><ul class="al"><?php foreach($rows as $r)arow($r,'reports.php?type='.$type,true);if(!$rows)echo '<li class="empty">'.($type==='today'?'No patients are waiting today.':'No upcoming scheduled appointments.').'</li>';?></ul></section>
<?php else:?>
<div class="card"><?php if($rows):?><table><thead><tr><th>Patient</th><th>Date</th><th>Chief complaint</th><th>Diagnosis</th><th>Recorded by</th></tr></thead><tbody><?php foreach($rows as $r):?>
<tr><td><a href="patient.php?id=<?=$r['patient_id']?>"><b><?=e($r['name'])?></b></a><small><?=pid($r['patient_id'])?> · <?=e($r['purok'])?></small></td><td><?=fd($r['visit_date'])?></td><td><?=e($r['complaint'])?></td><td><?=e($r['diagnosis'])?:'—'?></td><td><?=e($r['by_name'])?:'—'?></td></tr>
<?php endforeach?></tbody></table><?php else:?><p class="empty">No consultations recorded this month.</p><?php endif?></div>
<?php endif;foot();
