<?php
require __DIR__.'/../integration/ali-pos-security.php';
function phase2Assert($condition,$name){if(!$condition)throw new Exception($name);}
class Phase2Statement{private $row;function __construct($row){$this->row=$row;}function execute($args){}function fetch($mode){return $this->row;}}
class Phase2Database{private $user;private $permission;function __construct($user,$permission=[]){$this->user=$user;$this->permission=$permission;}function prepare($sql){return new Phase2Statement(strpos($sql,'FROM Seg_Usuario WHERE')!==false?$this->user:$this->permission);}}
$admin=aliPosCapabilities(new Phase2Database(['Usuario'=>'superadmin','IdUsuarioPerfil'=>1,'IdAlmacen'=>1]),'superadmin');
$reader=aliPosCapabilities(new Phase2Database(['Usuario'=>'reader','IdUsuarioPerfil'=>3,'IdAlmacen'=>2],['can_read'=>1,'can_write'=>0]),'reader');
$writer=aliPosCapabilities(new Phase2Database(['Usuario'=>'writer','IdUsuarioPerfil'=>3,'IdAlmacen'=>2],['can_read'=>1,'can_write'=>1]),'writer');
$missing=aliPosCapabilities(new Phase2Database(false),'missing');
phase2Assert($admin['read']&&$admin['write']&&$admin['admin'],'admin capabilities');
phase2Assert(!$missing['read'],'unknown user denied');
foreach([['search',2,$reader,200],['add',2,$reader,403],['method',2,$writer,200],['search',1,$writer,403],['search',1,$admin,200],['pay',2,$admin,200],['confirm',2,$admin,200],['arbitrary_sql',2,$admin,422]] as $case){[$action,$warehouse,$caps,$expected]=$case;phase2Assert(aliValidatePosAction(['action'=>$action,'warehouse'=>$warehouse],$caps)[0]===$expected,$action.' permission');}
phase2Assert(aliValidatePosAction(['action'=>'search','warehouse'=>1,'admin'=>true],$reader)[0]===422,'extra role parameter rejected');
phase2Assert(aliValidatePosAction(['action'=>'search','warehouse'=>'1'],$admin)[0]===422,'wrong type rejected');
echo 'PASS real permission adapter contracts, read/write/warehouse boundaries, closed actions and checkout action permissions. No database writes.'.PHP_EOL;
