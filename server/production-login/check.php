<?php
if(PHP_SAPI!=='cli')exit;
$o=getopt('',array('root:'));$root=realpath($o['root']??'');
if(!$root)throw new RuntimeException('Use --root=/ruta/ERP');
try{
    require $root.'/config/database.php';require __DIR__.'/auth.php';
    $config=appDatabaseConfig();$db=appPdoConnection();
    $out=array('configured_database'=>$config['dbname'],'connected_database'=>$db->query('SELECT DATABASE()')->fetchColumn(),'identity_tables'=>array());
    foreach(array('Seg_Usuario','Seg_UsuarioPerfil','Seg_UsuarioModulo','Seg_UsuarioModulo_has_UsuarioPerfil') as $expected){$name=nstLoginTable($db,$expected);$out['identity_tables'][$expected]=$name;}
    $mysqli=appMysqliConnection();$result=$mysqli->query('SELECT DATABASE()');$out['mysqli_database']=$result->fetch_row()[0];$mysqli->close();
    echo json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
}catch(Throwable $e){fwrite(STDERR,'Connection/table check failed. Error code: '.$e->getCode().PHP_EOL);exit(1);}
