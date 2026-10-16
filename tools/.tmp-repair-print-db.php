<?php
$root='C:/xampp/htdocs/boticav1';
$db=new PDO('mysql:host=localhost;dbname=nst;charset=utf8mb4','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
if (!$db->query('SELECT COUNT(*) FROM Ve_DocVenta')->fetchColumn()) throw new RuntimeException('No hay ventas en la base local');
$file=$root.'/config/database.local.php';
copy($file,$root.'/config/database.local.before-print-'.date('Ymd-His').'.php');
$config=require $file;
$config=array_merge($config,['host'=>'localhost','user'=>'root','pass'=>'','dbname'=>'nst','port'=>'3306','charset'=>'utf8mb4']);
file_put_contents($file,"<?php\nreturn ".var_export($config,true).";\n");
require $root.'/config/database.php';
appPdoConnection()->query('SELECT 1');
echo "PASS configuracion local conectada a la misma base que las ventas.\n";
