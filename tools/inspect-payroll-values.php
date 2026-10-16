<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();$p=$db->query('SELECT * FROM Rhu_PlanillaPeriodo WHERE IdPlanilla=1')->fetch(PDO::FETCH_ASSOC);echo json_encode($p,JSON_UNESCAPED_UNICODE);
