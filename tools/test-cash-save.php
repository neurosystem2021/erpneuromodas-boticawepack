<?php
ob_start();
$sessionDir=__DIR__."/../.tmp-test-sessions";if(!is_dir($sessionDir))mkdir($sessionDir);ini_set("session.save_path",$sessionDir);session_start();
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
$db->exec('CREATE TEMPORARY TABLE Cb_TipoCajaBanco(IdTipoCajaBanco INT PRIMARY KEY,Tipo INT) ENGINE=InnoDB');$db->exec('INSERT INTO Cb_TipoCajaBanco VALUES(1,1),(2,0),(3,2)');
$db->exec('CREATE TEMPORARY TABLE Cb_Cuenta(IdCuenta INT PRIMARY KEY) ENGINE=InnoDB');$db->exec('INSERT INTO Cb_Cuenta VALUES(1)');
$db->exec('CREATE TEMPORARY TABLE Cb_CajaBanco(IdCajaBanco INT AUTO_INCREMENT PRIMARY KEY,IdTipoCajaBanco INT,IdCuenta INT,FechaDoc DATETIME,Concepto VARCHAR(255),Importe DOUBLE,IdProveedor INT,IdCliente INT,UsuarioReg VARCHAR(255)) ENGINE=InnoDB');
$db->exec('CREATE TEMPORARY TABLE Cb_CajaBancoDet(IdCajaBanco INT,IdDocDet INT,Importe DOUBLE,Hash VARCHAR(255),Tipo VARCHAR(45),UNIQUE KEY(IdCajaBanco,IdDocDet)) ENGINE=InnoDB');
$db->exec('CREATE TEMPORARY TABLE Cb_LetraCaja(IdLetraCaja INT PRIMARY KEY,Cancelado INT,IdCajaBanco INT) ENGINE=InnoDB');$db->exec('INSERT INTO Cb_LetraCaja VALUES(1,0,NULL)');
class CashRequest{private $data;function __construct($data){$this->data=$data;}function getParam($key){return $this->data[$key]??null;}}
class CashResponse{function withJson($data,$status=200){return array('data'=>$data,'status'=>$status);}}
class CashContainer{public $db;}
$container=new CashContainer();$container->db=$db;$save=require __DIR__.'/../integration/cash-save/api/cash-movement-save.php';$save=$save->bindTo($container);$_SESSION=array('user'=>'superadmin');
$data=array('IdTipoCajaBanco'=>1,'IdCuenta'=>1,'FechaDoc'=>'2026-10-15','Concepto'=>"Pago cámaras del proveedor O'Connor",'Importe'=>1000,'IdProveedor'=>1,'AplicadoDocVenta'=>array(),'User'=>'spoofed-user');
$r=$save(new CashRequest($data),new CashResponse());if($r['status']!==422||(int)$db->query('SELECT COUNT(*) FROM Cb_CajaBanco')->fetchColumn()!==0)throw new Exception('Payment without debt accepted');
$data['AplicadoDocVenta']=array(array('Id'=>1,'IdLetraCaja'=>1,'Aplicado'=>1000));
$r=$save(new CashRequest($data),new CashResponse());if($r['status']!==200)throw new Exception('Applied supplier payment failed');$row=$db->query('SELECT * FROM Cb_CajaBanco')->fetch(PDO::FETCH_ASSOC);if((float)$row['Importe']!==1000.0||$row['Concepto']!==$data['Concepto']||$row['UsuarioReg']!=='superadmin')throw new Exception('Incorrect movement');
$invalid=$data;$invalid['Importe']=0;$r=$save(new CashRequest($invalid),new CashResponse());if($r['status']!==422)throw new Exception('Zero amount accepted');
$invalid=$data;$invalid['Importe']=2000;$r=$save(new CashRequest($invalid),new CashResponse());if($r['status']!==422)throw new Exception('Mismatched amount accepted');
$generic=$data;$generic['IdTipoCajaBanco']=3;$generic['AplicadoDocVenta']=array();$r=$save(new CashRequest($generic),new CashResponse());if($r['status']!==200)throw new Exception('General cash movement blocked');
$before=(int)$db->query('SELECT COUNT(*) FROM Cb_CajaBanco')->fetchColumn();$data['AplicadoDocVenta']=array(array('Id'=>2,'Aplicado'=>500),array('Id'=>2,'Aplicado'=>500));$r=$save(new CashRequest($data),new CashResponse());if($r['status']!==500||(int)$db->query('SELECT COUNT(*) FROM Cb_CajaBanco')->fetchColumn()!==$before)throw new Exception('Rollback failed');
$_SESSION=array();session_destroy();echo "PASS payments require debt; valid applied payments and general movements saved; session user, totals and rollback verified. Temporary tables only.\n";ob_end_flush();
