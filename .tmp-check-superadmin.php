<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=nst;charset=utf8', 'root', '');
$q=$db->prepare('SELECT Usuario, IdUsuarioPerfil, COALESCE(Anulado,0) AS Anulado FROM Seg_Usuario WHERE LOWER(Usuario)=?');$q->execute(['superadmin']);echo json_encode($q->fetchAll(PDO::FETCH_ASSOC));
