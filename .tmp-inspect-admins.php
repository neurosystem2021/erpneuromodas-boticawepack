<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=nst;charset=utf8', 'root', '');
echo json_encode($db->query('SELECT Usuario, IdUsuarioPerfil FROM Seg_Usuario WHERE IdUsuarioPerfil=1 AND COALESCE(Anulado,0)=0')->fetchAll(PDO::FETCH_ASSOC));
