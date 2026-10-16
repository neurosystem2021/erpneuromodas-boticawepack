<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
foreach($db->query('SELECT pg.IdPago,pg.Tipo,pg.Importe,pg.IdCuenta,c.Cuenta,pg.Fecha,pg.UsuarioReg,pg.Anulado,cb.IdCajaBanco,cb.IdTipoCajaBanco,t.Tipo TipoOperacion,cb.EsDelVendedor,cb.IdCierre,cb.Anulado CajaAnulado FROM Rhu_PlanillaPago pg JOIN Cb_CajaBanco cb ON cb.IdCajaBanco=pg.IdCajaBanco JOIN Cb_Cuenta c ON c.IdCuenta=cb.IdCuenta JOIN Cb_TipoCajaBanco t ON t.IdTipoCajaBanco=cb.IdTipoCajaBanco')->fetchAll(PDO::FETCH_ASSOC) as $r)echo json_encode($r,JSON_UNESCAPED_UNICODE).PHP_EOL;
