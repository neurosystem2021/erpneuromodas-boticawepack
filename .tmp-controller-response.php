<?php
foreach(array('EmpresaConfigGuardar','PlanesMenuGuardar') as $name){$ch=curl_init('http://localhost/controllers/'.$name.'.php');curl_setopt_array($ch,array(CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>10));$body=curl_exec($ch);echo curl_getinfo($ch,CURLINFO_HTTP_CODE).' '.substr(strip_tags($body),0,400)."\n";curl_close($ch);}
