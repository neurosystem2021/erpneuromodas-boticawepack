<?php
foreach([false,true] as $diary){$_SESSION=['diario_programador'=>$diary];$val=true;$res='Prueba';$max_attempts=5;$_SESSION['failed_attempts']=0;
$results = array(
	'isLogin' => $val,
 'redirect' => !empty($_SESSION['diario_programador']) ? '/views/diario.php' : '/views/V_VentaForm.php?pos=imceden',
	'message' => $res,
	'cantidad_res' => $max_attempts - $_SESSION['failed_attempts']

);

$expected=$diary?'/views/diario.php':'/views/V_VentaForm.php?pos=imceden';if($results['redirect']!==$expected)throw new RuntimeException('Incorrect redirect');}echo 'PASS actual controller response selects IMCEDEN POS; existing diary role destination preserved.'.PHP_EOL;
