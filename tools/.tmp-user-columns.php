<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$d=appPdoConnection();echo json_encode($d->query('SHOW COLUMNS FROM Seg_Usuario')->fetchAll(PDO::FETCH_COLUMN));
