<?php
/** Acceso administrativo: contraseña desde variable APP_ADMIN_PASSWORD_HASH. */
require_once __DIR__.'/includes/comun.php';
require_once __DIR__.'/includes/autenticacion.php';
iniciar();
if (hashAdministrador()==='' && puedeConfigurarLocal()) { header('Location: configurar.php'); exit; }
if(!empty($_SESSION['autorizado'])){header('Location: index.php');exit;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{verificarCsrf();$hash=hashAdministrador();
 if(!$hash) throw new RuntimeException('Acceso sin configurar. En Render agrega APP_ADMIN_PASSWORD en Environment; en XAMPP abre configurar.php desde localhost.');
 if(password_verify((string)($_POST['password']??''),$hash)){
 session_regenerate_id(true);$_SESSION['autorizado']=true;header('Location: index.php');exit;
 }$error='Contraseña incorrecta.';
 }catch(Throwable $e){$error=$e->getMessage();}
}
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Acceso | Cafetería Express</title><link rel="stylesheet" href="assets/css/estilos.css"></head><body><main style="max-width:420px;margin:10vh auto;padding:22px"><section class="panel"><h1>☕ Cafetería Express</h1><h2>Acceso administrativo</h2><?php if($error):?><p role="alert"><?=e($error)?></p><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf())?>"><label class="field">Contraseña<input type="password" name="password" autocomplete="current-password" required></label><p><button class="button" type="submit">Ingresar</button></p></form></section></main></body></html>
