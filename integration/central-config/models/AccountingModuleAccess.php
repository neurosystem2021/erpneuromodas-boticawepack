<?php
require_once __DIR__.'/ModuleAccess.php';
function accountingAllowedTabs($user){$tabs=['dashboard','accounts','entries','sources','reports','tax','banks','assets','config','audit'];return array_values(array_filter($tabs,function($tab)use($user){return moduleAccessAllowed($user,'/views/contabilidad.php?tab='.$tab);}));}
function accountingRequireAction($user,$action,$payload,$method){
 $tabs=accountingAllowedTabs($user);if(!$tabs)moduleAccessRequire('/views/contabilidad.php?tab=dashboard',true);
 $map=['dashboard'=>'dashboard','entries'=>'entries','entry'=>'entries','sources'=>'sources','source'=>'sources','capture'=>'sources','prepareSource'=>'sources','prepareCash'=>'sources','resolveSource'=>'sources','report'=>'reports','export'=>'reports','audit'=>'audit','reconciliation'=>'banks','statement'=>'banks','bank'=>'banks','importBank'=>'banks','match'=>'banks','asset'=>'assets','assets'=>'assets','depreciation'=>'assets','members'=>'config','periods'=>'config','configure'=>'config','member'=>'config','branch'=>'config','period'=>'config','closePeriod'=>'config','createCompany'=>'config','account'=>'accounts','importAccounts'=>'accounts','officialCatalog'=>'accounts','starterCatalog'=>'accounts','draft'=>'entries','opening'=>'entries','updateDraft'=>'entries','transition'=>'entries','reverse'=>'entries','template'=>'entries','yearClosing'=>'entries','voidTax'=>'tax','taxReconciliation'=>'tax','importExternalRegister'=>'tax'];
 $tab=$map[$action]??null;if($action==='report'&&($payload['report']??'')==='tax')$tab='tax';
 // Source reviewers may open the resulting entry without gaining access to the journal list.
 if($action==='entry'&&in_array('sources',$tabs,true))return;
 if($tab)moduleAccessRequire('/views/contabilidad.php?tab='.$tab,true);
}
