<?php
require_once dirname(__DIR__) . '/config/database.php';

function getSQLResultSet($sql) {

    $mysqli = appMysqliConnection();
 return $mysqli->query($sql);
}
