-- IMCEDEN: tipo de comprobante tomado de nstarapaca.
-- Ejecutar solamente en la base de IMCEDEN, nunca en nstarapaca.
-- Conserva las series existentes (incluida F104) y los comprobantes emitidos.
INSERT INTO Ve_DocVentaTipoDoc
    (IdTipoDoc,TipoDoc,VaRegVenta,CodSunat,TipoDocSunat,TieneIgv,LimiteItems,CodigoIgv,Orden,EsElectronico,Anulado)
SELECT 8,'FACTURA/IGV ELEC.',1,'01','01',1,0,'10',3,1,0
WHERE NOT EXISTS (SELECT 1 FROM Ve_DocVentaTipoDoc WHERE IdTipoDoc=8 OR TipoDoc='FACTURA/IGV ELEC.');

UPDATE Ve_DocVentaTipoDoc SET Anulado=0
WHERE IdTipoDoc=8 AND TipoDoc='FACTURA/IGV ELEC.' AND CodSunat='01'
  AND TieneIgv=1 AND CodigoIgv='10' AND EsElectronico=1;
