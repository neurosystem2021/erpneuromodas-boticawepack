<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';
require ($argv[1]??__DIR__.'/../server/facturacion/factura-igv.php');
function invoiceCheck($ok,$label){if(!$ok)throw new RuntimeException($label);}
$db=appPdoConnection();
$db->exec("CREATE TEMPORARY TABLE Ve_DocVentaTipoDoc(IdTipoDoc INT PRIMARY KEY,TipoDoc VARCHAR(255),VaRegVenta TINYINT,CodSunat VARCHAR(2),TipoDocSunat VARCHAR(2),TieneIgv TINYINT,LimiteItems INT,CodigoIgv VARCHAR(2),Orden INT,EsElectronico TINYINT,Anulado TINYINT) ENGINE=InnoDB;
CREATE TEMPORARY TABLE Ve_DocVentaPuntoVentaDet(IdDocVentaPuntoVenta INT,IdDocVentaTipoDoc INT,Serie VARCHAR(45),PRIMARY KEY(IdDocVentaPuntoVenta,IdDocVentaTipoDoc)) ENGINE=InnoDB;
CREATE TEMPORARY TABLE Ve_DocVenta(IdDocVenta INT PRIMARY KEY,IdDocVentaPuntoVenta INT,IdTipoDoc INT,Serie VARCHAR(45),Numero INT,Anulado INT) ENGINE=InnoDB;
INSERT INTO Ve_DocVentaTipoDoc VALUES(3,'NOTA DE VENTA',1,'12','01',0,0,'20',0,0,0),(6,'FACTURA ELEC.',1,'01','01',0,10,'20',0,1,0),(7,'BOLETA ELEC.',1,'03','01',0,8,'20',0,1,0),(11,'NOTA DE CREDITO ELEC.',1,'07','01',0,0,'20',0,1,0);
INSERT INTO Ve_DocVentaPuntoVentaDet VALUES(1,6,'F004'),(1,8,'F104'),(2,6,'F005'),(2,8,'F005');
INSERT INTO Ve_DocVenta VALUES(1,1,6,'F004',25,0),(2,2,6,'F005',41,0),(3,2,8,'F005',42,0),(4,1,8,'F104',3,0),(5,1,7,'B004',100,0);");
$before=$db->query('SELECT * FROM Ve_DocVentaTipoDoc ORDER BY IdTipoDoc')->fetchAll();$series=$db->query('SELECT * FROM Ve_DocVentaPuntoVentaDet ORDER BY IdDocVentaPuntoVenta,IdDocVentaTipoDoc')->fetchAll();$sales=$db->query('SELECT * FROM Ve_DocVenta ORDER BY IdDocVenta')->fetchAll();
$migration=file_get_contents(__DIR__.'/../server/migrations/imceden-factura-igv.sql');$db->exec($migration);$row=$db->query('SELECT * FROM Ve_DocVentaTipoDoc WHERE IdTipoDoc=8')->fetch();
invoiceCheck($row['TipoDoc']==='FACTURA/IGV ELEC.'&&(int)$row['TieneIgv']===1&&$row['CodigoIgv']==='10'&&$row['CodSunat']==='01'&&(int)$row['EsElectronico']===1&&(int)$row['Anulado']===0,'Exact NST electronic VAT invoice flags, enabled in IMCEDEN');
invoiceCheck($db->query('SELECT * FROM Ve_DocVentaTipoDoc WHERE IdTipoDoc<>8 ORDER BY IdTipoDoc')->fetchAll()===$before,'Other document types unchanged');
$db->exec($migration);invoiceCheck((int)$db->query('SELECT COUNT(*) FROM Ve_DocVentaTipoDoc WHERE IdTipoDoc=8')->fetchColumn()===1,'Idempotent activation');
invoiceCheck(imcFacturaIgvNumero($db,8,'F104')===4,'Existing dedicated F104 continues');
invoiceCheck(imcFacturaIgvNumero($db,8,'F005')===43&&imcFacturaIgvNumero($db,6,'F005')===43,'Shared invoice series continue across tax variants');
invoiceCheck(imcFacturaIgvNumero($db,6,'F004')===26&&imcFacturaIgvNumero($db,8,'F199')===1,'Other invoice series unaffected');
invoiceCheck(imcFacturaIgvNumero($db,7,'B004')===null&&imcFacturaIgvNumero($db,3,'004')===null&&imcFacturaIgvNumero($db,11,'FC04')===null,'Boleta, internal note and credit-note numbering unaffected');
invoiceCheck($db->query('SELECT * FROM Ve_DocVentaPuntoVentaDet ORDER BY IdDocVentaPuntoVenta,IdDocVentaTipoDoc')->fetchAll()===$series,'IMCEDEN series retained without NST series copy');
invoiceCheck($db->query('SELECT * FROM Ve_DocVenta ORDER BY IdDocVenta')->fetchAll()===$sales,'No existing invoices modified');
echo "PASS IMCEDEN invoice activation, NST tax flags, dedicated/shared invoice series, idempotence and existing sales/types unchanged. Connection-local temporary tables only; no real invoice or SUNAT request.\n";
