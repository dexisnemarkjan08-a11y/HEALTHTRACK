<?php require 'includes/config.php';need();
$s=trim($_GET['q']??'');$pu=$_GET['pu']??'';
$rows=q('SELECT p.*,(SELECT MAX(visit_date) FROM consultations c WHERE c.patient_id=p.id) last_visit FROM patients p WHERE (?="" OR p.purok=?) AND (?="" OR p.name LIKE ? OR CONCAT("P-",LPAD(p.id,4,"0")) LIKE ?) ORDER BY p.name',[$pu,$pu,$s,"%$s%","%$s%"])->fetchAll();
head('Patients','patients.php');?>
<div class="bar"><h1>Patient records</h1><a class="btn pri" href="patient.php?new=1">Register patient</a></div>
<form class="tools" method="get"><input name="q" placeholder="Search by name or ID" aria-label="Search patients" value="<?=e($s)?>"><select name="pu" aria-label="Purok"><option value="">All puroks</option><?php foreach(PUROKS as $u):?><option<?=$u===$pu?' selected':''?>><?=$u?></option><?php endforeach?></select><button>Search</button></form>
<div class="card"><?php if($rows):?><table><thead><tr><th>Patient<th>Age and sex<th>Purok<th>Contact<th>Last visit</thead><tbody><?php foreach($rows as $p):?>
<tr><td><a href="patient.php?id=<?=$p['id']?>"><b><?=e($p['name'])?></b></a><small><?=pid($p['id'])?></small><td><?=age($p['birthdate'])?> · <?=$p['sex']?><td><?=$p['purok']?><td><?=e($p['contact'])?:'None on file'?><td><?=$p['last_visit']?fd($p['last_visit']):'No visits yet'?>
<?php endforeach?></tbody></table><?php else:?><p class="empty">No patients match. Clear the search or register a new patient.</p><?php endif?></div>
<?php foot();
