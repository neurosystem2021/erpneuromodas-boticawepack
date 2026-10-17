<?php
if(PHP_SAPI!=='cli')exit;$f=json_decode(file_get_contents(__DIR__.'/.tmp-module-access-fixture.json'),true);foreach(['super','personal'] as $key){session_id($f[$key]);session_start();$_SESSION['expire']=time()+300;session_write_close();}
