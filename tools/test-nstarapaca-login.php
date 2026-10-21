<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';
require __DIR__.'/../server/production-login/auth.php';
function loginCheck($ok,$message){if(!$ok)throw new RuntimeException($message);}
$db=appPdoConnection();$schema='nst_login_test_'.bin2hex(random_bytes(6));
$db->exec('CREATE DATABASE `'.$schema.'`');
try{
    $db->exec('USE `'.$schema.'`');
    $db->exec("CREATE TABLE Seg_Usuario(Usuario VARCHAR(100),Password VARCHAR(64),IdUsuarioPerfil INT,IdAlmacen INT,Anulado TINYINT);
CREATE TABLE Seg_UsuarioPerfil(IdUsuarioPerfil INT,UsuarioPerfil VARCHAR(80));
CREATE TABLE Seg_UsuarioModulo(IdUsuarioModulo INT,UsuarioModulo VARCHAR(100));
CREATE TABLE Seg_UsuarioModulo_has_UsuarioPerfil(IdUsuarioPerfil INT,IdUsuarioModulo INT,Lectura TINYINT,Escritura TINYINT);
INSERT INTO Seg_Usuario VALUES('fixture',SHA2('fixture-password',256),1,3,0),('programmer',SHA2('fixture-password',256),2,1,0),('disabled',SHA2('fixture-password',256),1,1,1);
INSERT INTO Seg_UsuarioPerfil VALUES(1,'ADMIN'),(2,'PROGRAMADOR');
INSERT INTO Seg_UsuarioModulo VALUES(1,'Venta.php');
INSERT INTO Seg_UsuarioModulo_has_UsuarioPerfil VALUES(1,1,1,0)");
    $connect=function()use($db){return $db;};$_SESSION=array();
    list($status,$result)=nstLoginResponse('POST',array('nameUser'=>'fixture','namePass'=>'fixture-password'),$connect,1000);
    loginCheck($status===200&&$result['isLogin']&&$_SESSION['user']==='fixture'&&$_SESSION['idAlmacen']==3&&$_SESSION['permisos']['Venta.php']['Escritura']==0,'Valid login must preserve user, warehouse and permissions.');
    $_SESSION=array();list($status,$result)=nstLoginResponse('POST',array('nameUser'=>'programmer','namePass'=>'fixture-password'),$connect,1000);
    loginCheck($status===200&&$result['redirect']==='/views/diario.php'&&$_SESSION['idPerfil']===2,'Programmer role and redirect.');
    foreach(array('disabled',"fixture' OR 1=1 -- ") as $user){$_SESSION=array();list($status,$result)=nstLoginResponse('POST',array('nameUser'=>$user,'namePass'=>'fixture-password'),$connect,1000);loginCheck($status===401&&!isset($_SESSION['user']),'Disabled/injected account accepted.');}
    $_SESSION=array();for($n=0;$n<5;$n++)nstLoginResponse('POST',array('nameUser'=>'fixture','namePass'=>'wrong'),$connect,1000);
    list($status)=nstLoginResponse('POST',array('nameUser'=>'fixture','namePass'=>'fixture-password'),function(){throw new LogicException('Lockout must not access DB.');},1100);loginCheck($status===429,'Attempt lockout.');
    $_SESSION=array();list($status,$result)=nstLoginResponse('POST',array('nameUser'=>'fixture','namePass'=>'fixture-password'),function(){throw new RuntimeException('PRIVATE_FIXTURE_DETAIL');});
    loginCheck($status===503&&strpos(json_encode($result),'PRIVATE_FIXTURE_DETAIL')===false,'DB error must remain safe JSON.');
    list($status)=nstLoginResponse('GET',array(),function(){throw new LogicException('GET must not access DB.');});loginCheck($status===405,'GET must not query credentials.');
    echo "PASS login, exact table case, permissions, programmer redirect, disabled users, injection rejection, lockout and safe database errors. Isolated fixture only.\n";
}finally{$db->exec('DROP DATABASE `'.$schema.'`');}
