<?php require 'includes/config.php';need();
$id=(int)($_GET['id']??$_GET['edit']??0);
$canC=in_array(me()['role'],['Midwife','Administrator']);
if(isset($_GET['edit'])&&!canManagePatients()){http_response_code(403);exit('Your role is not allowed to edit patient records.');}
if($_SERVER['REQUEST_METHOD']==='POST'){chk();$a=$_POST['action']??'';$v=fn($k)=>trim($_POST[$k]??'');
 if($a==='save'){
  if($id)need(['Midwife','Administrator']);
  $f=[$v('name'),in_array($v('sex'),['Female','Male'])?$v('sex'):'Female',$v('birthdate'),in_array($v('purok'),PUROKS)?$v('purok'):PUROKS[0],$v('contact'),$v('conditions'),$v('allergies')];
  if($f[0]===''||$f[2]===''){flash('Name and date of birth are required.');header('Location: '.$_SERVER['REQUEST_URI']);exit;}
  if($id)q('UPDATE patients SET name=?,sex=?,birthdate=?,purok=?,contact=?,conditions_text=?,allergies=? WHERE id=?',[...$f,$id]);
  else{q('INSERT INTO patients(name,sex,birthdate,purok,contact,conditions_text,allergies) VALUES(?,?,?,?,?,?,?)',$f);$id=(int)db()->lastInsertId();}
  flash('Patient saved.');header("Location: patient.php?id=$id");exit;}
 if($a==='consult'){need(['Midwife','Administrator']);
  if($id&&$v('complaint')!==''){q('INSERT INTO consultations(patient_id,visit_date,complaint,bp,temp,weight,diagnosis,treatment,recorded_by) VALUES(?,?,?,?,?,?,?,?,?)',[$id,$v('visit_date')?:date('Y-m-d'),$v('complaint'),$v('bp'),$v('temp'),$v('weight'),$v('diagnosis'),$v('treatment'),me()['id']]);flash('Consultation saved.');}
  header("Location: patient.php?id=$id");exit;}
 if($a==='delete'){need(['Administrator']);q('DELETE FROM patients WHERE id=?',[$id]);flash('Patient deleted.');header('Location: patients.php');exit;}}
$p=null;
if($id){$p=q('SELECT * FROM patients WHERE id=?',[$id])->fetch();if(!$p){http_response_code(404);head('Not found','patients.php');echo '<p class="empty">Patient not found. <a href="patients.php">Back to patients</a></p>';foot();exit;}}
head($p?$p['name']:'Register patient','patients.php');
$g=fn($k)=>e($p[$k]??'');
if(isset($_GET['new'])||isset($_GET['edit'])):?>
<h1><?=$p?'Edit patient':'Register patient'?></h1>
<form method="post" class="fg card pad"><input type="hidden" name="t" value="<?=csrf()?>"><input type="hidden" name="action" value="save">
<label>Full name<input name="name" value="<?=$g('name')?>" required></label>
<label>Sex<select name="sex"><?php foreach(['Female','Male'] as $o):?><option<?=($p['sex']??'')===$o?' selected':''?>><?=$o?></option><?php endforeach?></select></label>
<label>Date of birth<input type="date" name="birthdate" value="<?=$g('birthdate')?>" max="<?=date('Y-m-d')?>" required></label>
<label>Purok<select name="purok"><?php foreach(PUROKS as $o):?><option<?=($p['purok']??'')===$o?' selected':''?>><?=$o?></option><?php endforeach?></select></label>
<label>Contact number<input name="contact" value="<?=$g('contact')?>" inputmode="tel"></label>
<label>Known conditions<input name="conditions" value="<?=$g('conditions_text')?>"></label>
<label>Allergies<input name="allergies" value="<?=$g('allergies')?>"></label>
<div class="w row"><a class="btn" href="<?=$p?'patient.php?id='.$id:'patients.php'?>">Cancel</a><button class="pri">Save patient</button></div></form>
<?php else:
$cs=$canC?q('SELECT c.*,u.full_name by_name FROM consultations c LEFT JOIN users u ON u.id=c.recorded_by WHERE patient_id=? ORDER BY visit_date DESC,c.id DESC',[$id])->fetchAll():[];
$nx=q("SELECT * FROM appointments WHERE patient_id=? AND status='Scheduled' AND appt_date>=CURDATE() ORDER BY appt_date,appt_time LIMIT 1",[$id])->fetch();?>
<div class="bar"><div><h1><?=e($p['name'])?></h1><small><?=pid($id)?> · <?=age($p['birthdate'])?> · <?=$p['sex']?></small></div>
<div class="row"><a class="btn" href="appointments.php?patient=<?=$id?>#book">Book appointment</a><?php if(canManagePatients()):?><a class="btn" href="patient.php?edit=<?=$id?>">Edit details</a><?php endif?>
<?php if(me()['role']==='Administrator'):?><form method="post" onsubmit="return confirm('Delete this patient with all consultations and appointments?')"><input type="hidden" name="t" value="<?=csrf()?>"><input type="hidden" name="action" value="delete"><button class="dg">Delete patient</button></form><?php endif?></div></div>
<dl class="card pad"><dt>Purok<dd><?=$p['purok']?><dt>Born<dd><?=fd($p['birthdate'])?><dt>Contact<dd><?=e($p['contact'])?:'None on file'?><?php if($canC):?><dt>Known conditions<dd><?=e($p['conditions_text'])?:'None recorded'?><dt>Allergies<dd><?=e($p['allergies'])?:'None recorded'?><?php endif?><dt>Next appointment<dd><?=$nx?fd($nx['appt_date']).' at '.substr($nx['appt_time'],0,5).', '.e($nx['service']):'None scheduled'?></dl>
<?php if($canC):?><h2>Consultations</h2><form method="post" class="fg card pad"><input type="hidden" name="t" value="<?=csrf()?>"><input type="hidden" name="action" value="consult">
<label>Chief complaint<input name="complaint" required></label><label>Date<input type="date" name="visit_date" value="<?=date('Y-m-d')?>" required></label><label>Blood pressure<input name="bp" placeholder="120/80"></label><label>Temperature (°C)<input name="temp"></label><label>Weight<input name="weight" placeholder="58 kg"></label><label>Diagnosis<input name="diagnosis"></label><label>Treatment or advice<input name="treatment"></label><div class="w"><button class="pri">Save consultation</button></div></form>
<?php endif?>
<?php if($canC): foreach($cs as $c):?><article class="cn"><b><?=fd($c['visit_date'])?>: <?=e($c['complaint'])?></b><small>BP <?=e($c['bp'])?:'not taken'?> · Temp <?=e($c['temp'])?:'not taken'?> · Weight <?=e($c['weight'])?:'not taken'?><?=$c['by_name']?' · Recorded by '.e($c['by_name']):''?></small><p>Diagnosis: <?=e($c['diagnosis'])?:'none'?>. Treatment: <?=e($c['treatment'])?:'none'?>.</p></article><?php endforeach;if(!$cs)echo '<p class="empty">No consultations recorded yet.</p>';endif;
endif;foot();
