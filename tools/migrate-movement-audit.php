<?php
$source = file_get_contents('C:/xampp/htdocs/boticav1/api/index.php');
$settings = array();
foreach (array('host', 'user', 'pass', 'dbname') as $key) {
    $pattern = '/^\$config\[\x27db\x27\]\[\x27' . $key . '\x27\]\s*=\s*[\x27"]([^\x27"]*)[\x27"]/m';
    if (!preg_match($pattern, $source, $match)) throw new Exception('Database configuration unavailable.');
    $settings[$key] = $match[1];
}
$db = new PDO('mysql:host=' . $settings['host'] . ';dbname=' . $settings['dbname'] . ';charset=utf8mb4', $settings['user'], $settings['pass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$db->exec(file_get_contents(__DIR__ . '/../server/migrations/20261010_movement_audit.sql'));
echo "Movement audit table ready.\n";
