ALTER TABLE Gen_Producto
    ADD COLUMN IF NOT EXISTS FechaOfertaInicio DATE NULL AFTER PrecioApp,
    ADD COLUMN IF NOT EXISTS FechaOfertaFin DATE NULL AFTER FechaOfertaInicio;

CREATE INDEX IF NOT EXISTS idx_producto_oferta_pedi2
    ON Gen_Producto (IdOfertaCategoria, PosOferta, FechaOfertaInicio, FechaOfertaFin);
