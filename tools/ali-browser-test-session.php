<?php
// Only ephemeral sessions for local automated preflight. Never export a real user's session.
if(($argv[1]??'')==='create'){
 session_id('aliBrowserFixture'.bin2hex(random_bytes(12)));session_start();$_SESSION['user']='superadmin';$_SESSION['expire']=time()+180;$id=session_id();session_write_close();echo json_encode(['id'=>$id]);
}elseif(($argv[1]??'')==='destroy'&&preg_match('/^aliBrowserFixture[a-f0-9]{24}$/D',$argv[2]??'')){
 session_id($argv[2]);session_start();$_SESSION=[];session_destroy();echo 'destroyed';
}else{throw new Exception('Invalid test session request');}
