<?php
$multiAgentLinks=['/views/ag_operacion.php'=>['/views/Ag_Operacion.php','Operaciones'],'/views/ag_reimpresion.php'=>['/views/Ag_Reimpresion.php','Reimpresión de recibos'],'/views/ag_cierrecaja.php'=>['/views/Ag_CierreCaja.php','Cierre de caja'],'/views/ag_configuracion.php'=>['/views/Ag_Configuracion.php','Configuración']];
$multiAgentVisible=!in_array('multi agente',$headerGruposDeshabilitados,true)&&count(array_diff(array_keys($multiAgentLinks),$headerMenuDeshabilitados))>0;
if($multiAgentVisible){
 echo '<li class="dropdown" id="multiagente-menu"><a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-users" aria-hidden="true"></i> Multi Agente <span class="caret"></span></a><ul class="dropdown-menu">';
 foreach($multiAgentLinks as $link)echo '<li><a href="'.$link[0].'">'.htmlspecialchars($link[1],ENT_QUOTES,'UTF-8').'</a></li>';
 echo '</ul></li>';
}
