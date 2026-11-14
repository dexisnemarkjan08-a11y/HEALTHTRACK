<?php require 'includes/config.php';need();
$rows=q("SELECT a.*,COALESCE(NULLIF(a.patient_name,''),p.name) name,COALESCE(NULLIF(a.purok,''),p.purok,'') purok,COALESCE(NULLIF(a.patient_contact,''),p.contact,'') contact FROM appointments a LEFT JOIN patients p ON p.id=a.patient_id WHERE a.appt_date=CURDATE() ORDER BY a.appt_time")->fetchAll();
$wait=count(array_filter($rows,fn($r)=>$r['status']==='Scheduled'));
$pc=q('SELECT purok,COUNT(*) n FROM patients GROUP BY purok')->fetchAll(PDO::FETCH_KEY_PAIR);
$mx=max(array_merge([1],array_values($pc)));
$canViewConsultations=in_array(me()['role'],['Midwife','Administrator'],true);
$st=[
 [q('SELECT COUNT(*) FROM patients')->fetchColumn(),'Registered patients',null],
 [$wait,'Still waiting today','reports.php?type=today'],
 [q("SELECT COUNT(*) FROM appointments WHERE status='Scheduled' AND appt_date>CURDATE()")->fetchColumn(),'Upcoming scheduled','reports.php?type=upcoming'],
 [q("SELECT COUNT(*) FROM consultations WHERE DATE_FORMAT(visit_date,'%Y-%m')=DATE_FORMAT(CURDATE(),'%Y-%m')")->fetchColumn(),'Consultations this month',$canViewConsultations?'reports.php?type=consultations':null]
];
head('Today','index.php','dashboard-page');?>
<div class="bar dashboard-heading"><div><h1>Dashboard</h1><small><?=date('l, F j, Y')?></small></div><a class="btn pri" href="appointments.php#book">Book appointment</a></div>
<div class="grid dashboard-layout"><section class="card dashboard-queue"><div class="dashboard-section-head"><div><h2>Today’s appointments</h2><small>Scheduled visits and current queue</small></div><span class="dashboard-count"><?=count($rows)?> total</span></div><ul class="al"><?php foreach($rows as $r)arow($r,'index.php');if(!$rows)echo '<li class="empty">No appointments today. Book one with the button above.</li>';?></ul></section>
<aside class="dashboard-aside"><div class="stats"><?php foreach($st as $s):?><?php if($s[2]):?><a class="stat" href="<?=$s[2]?>"><b><?=$s[0]?></b><?=$s[1]?></a><?php else:?><div class="stat"<?=$s[1]==='Consultations this month'?' title="Consultation details are available to midwives and administrators."':''?>><b><?=$s[0]?></b><?=$s[1]?></div><?php endif?><?php endforeach?></div>
<section class="card pad dashboard-purok"><div class="dashboard-section-head"><div><h2>Patients by purok</h2><small>Registered patients in each area</small></div><span class="dashboard-count">13 puroks</span></div><div class="dashboard-purok-list"><?php foreach(PUROKS as $u):$n=$pc[$u]??0;?><div class="pb"><span><?=$u?></span><s><i style="width:<?=$n/$mx*100?>%"></i></s><em><?=$n?></em></div><?php endforeach?></div></section></aside></div>
<?php foot();
