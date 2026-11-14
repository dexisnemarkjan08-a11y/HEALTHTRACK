<?php require 'includes/config.php';
if(me()){header('Location: index.php');exit;}
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 chk();
 $u=q('SELECT * FROM users WHERE username=?',[trim($_POST['username']??'')])->fetch();
 if($u&&password_verify($_POST['password']??'',$u['password_hash'])){session_regenerate_id(true);$_SESSION['u']=['id'=>$u['id'],'full_name'=>$u['full_name'],'role'=>$u['role']];header('Location: index.php');exit;}
 $err='Wrong username or password.';}
head('Sign in','','login-page');?>
<section class="card pad login"><img class="login-image" src="database/Barangay%20Health%20Center%20Exterior.png" alt="Barangay health center building"><h1>Sign in</h1><?php if($err):?><p class="err" role="alert"><?=e($err)?></p><?php endif?>
<form method="post" class="fg"><input type="hidden" name="t" value="<?=csrf()?>"><label>Username<input name="username" required autofocus></label><label>Password<input type="password" name="password" required></label><div class="w"><button class="pri">Sign in</button></div></form></section>
<?php foot();
