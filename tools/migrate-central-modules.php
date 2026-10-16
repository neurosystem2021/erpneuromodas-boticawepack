<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
$columns=array_column($db->query('SHOW COLUMNS FROM Gen_Empresa')->fetchAll(PDO::FETCH_ASSOC),'Field');
foreach(array('CELULAR','EMAIL') as $column)if(!in_array($column,$columns,true))$db->exec('ALTER TABLE Gen_Empresa ADD COLUMN '.$column.' VARCHAR(255) NULL');
$id=(int)$db->query('SELECT IDEMPRESA FROM Gen_Empresa ORDER BY IDEMPRESA LIMIT 1')->fetchColumn();
$source=file_get_contents('C:/xampp/htdocs/boticav1/api/index.php');$seeds=array('sunat_entorno'=>'FE_PRODUCCION');
foreach(array('USUARIO_SOL_EMPRESA'=>'usuario_sol','PASS_SOL_EMPRESA'=>'clave_sol') as $constant=>$key)if(preg_match("/define\\('".$constant."',\\s*'([^']*)'\\);/",$source,$m))$seeds[$key]=$m[1];
$db->beginTransaction();try{$q=$db->prepare('SELECT COUNT(*) FROM Gen_EmpresaConfig WHERE IdEmpresa=? AND MetaKey=?');$insert=$db->prepare('INSERT INTO Gen_EmpresaConfig(IdEmpresa,MetaKey,MetaValue) VALUES(?,?,?)');foreach($seeds as $key=>$value){$q->execute(array($id,$key));if(!$q->fetchColumn())$insert->execute(array($id,$key,$value));}$db->commit();}catch(Throwable $e){$db->rollBack();throw $e;}
echo "Central configuration schema ready; existing company and credentials preserved.\n";
