SET @empresa_id = (SELECT IDEMPRESA FROM Gen_Empresa ORDER BY IDEMPRESA LIMIT 1);

UPDATE Gen_Empresa
SET DESCRIPCION = 'VENTA DE BATERIAS, AROS Y LLANTAS AL POR MAYOR Y MENOR'
WHERE IDEMPRESA = @empresa_id;

UPDATE Gen_EmpresaConfig
SET MetaValue = 'VENTA DE BATERIAS, AROS Y LLANTAS AL POR MAYOR Y MENOR'
WHERE IdEmpresa = @empresa_id AND MetaKey = 'actividad_comercial';

INSERT INTO Gen_EmpresaConfig (IdEmpresa, MetaKey, MetaValue)
SELECT @empresa_id, 'actividad_comercial', 'VENTA DE BATERIAS, AROS Y LLANTAS AL POR MAYOR Y MENOR'
WHERE NOT EXISTS (
    SELECT 1
    FROM Gen_EmpresaConfig
    WHERE IdEmpresa = @empresa_id AND MetaKey = 'actividad_comercial'
);
