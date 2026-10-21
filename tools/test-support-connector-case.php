<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';
require __DIR__.'/../integration/helpdesk/connector/support-connector.php';
class ConnectorCaseFixture {
    public $matches;
    function __construct($matches) { $this->matches=$matches; }
    function prepare($query) { return $this; }
    function execute($params) { return true; }
    function fetchAll($mode) { return $this->matches; }
}
foreach(array(array('GEN_EMPRESA'),array('gen_empresa'),array('Gen_Empresa')) as $names) {
    if(supportConnectorTable(new ConnectorCaseFixture($names),'Gen_Empresa')!=='`'.$names[0].'`')throw new RuntimeException('Case mapping failed.');
}
try {
    supportConnectorTable(new ConnectorCaseFixture(array('GEN_EMPRESA','gen_empresa')),'Gen_Empresa');
    throw new LogicException('Ambiguous identity table was accepted.');
} catch (RuntimeException $expected) {}
$db=appPdoConnection();
foreach(array('Gen_Empresa','Seg_Usuario') as $name){
    $table=supportConnectorTable($db,$name);
    $db->query('SELECT 1 FROM '.$table.' LIMIT 1')->fetchColumn();
}
echo "PASS exact identity-table case, ambiguous-table rejection and read-only lookup against installed ERP. No ticket or ERP writes.\n";
