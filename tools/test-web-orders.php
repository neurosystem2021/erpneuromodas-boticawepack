<?php
session_id('weborderscheck'.bin2hex(random_bytes(6)));session_start();$_SESSION['user']='internal-verification';$_SERVER['REQUEST_METHOD']='GET';
ob_start();include 'C:/xampp/htdocs/boticav1/api/pedidos-web.php';$json=ob_get_clean();$data=json_decode($json,true);session_destroy();
if(!is_array($data)||!isset($data['pendientes'],$data['pedidos'])){echo $json;exit(1);}echo 'Bandeja OK: '.count($data['pedidos']).' pedidos, '.$data['pendientes'].' pendientes';
