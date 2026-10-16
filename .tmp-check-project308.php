<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
$sql="SELECT p.IdProducto,p.Producto,p.IdPreOrden,COALESCE((SELECT SUM(d.Cantidad*d.Precio*IF(d.EsManoDeObra=1,0,1)) FROM Ve_PreOrdenDet d WHERE d.IdPreOrden=p.IdPreOrden),0) TotalServicio,COALESCE((SELECT SUM(c.Importe) FROM Cb_CajaBanco c JOIN Cb_TipoCajaBanco t ON t.IdTipoCajaBanco=c.IdTipoCajaBanco WHERE c.IdPreOrden=p.IdPreOrden AND t.Tipo=0 AND IFNULL(c.Anulado,0)=0),0) Adelanto FROM Gen_Producto p WHERE p.Producto='PROYECTO 308'";
foreach($db->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $row){$row['Saldo']=(float)$row['TotalServicio']-(float)$row['Adelanto'];echo json_encode($row),PHP_EOL;}
