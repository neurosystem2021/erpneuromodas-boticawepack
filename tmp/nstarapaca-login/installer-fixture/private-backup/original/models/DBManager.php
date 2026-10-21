<?php
header('Content-Type: text/html;charset=utf-8');
function getSQLResultSet($sql) {
 $mysqli = new mysqli('localhost','fixture','','fixture');
 return $mysqli->query($sql);
}
