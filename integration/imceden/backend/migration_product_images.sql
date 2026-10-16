-- Galería de hasta tres imágenes por producto.
-- Compatible con la columna Imagen existente.
ALTER TABLE Gen_Producto
  ADD COLUMN IF NOT EXISTS Imagen2 VARCHAR(255) NULL AFTER Imagen,
  ADD COLUMN IF NOT EXISTS Imagen3 VARCHAR(255) NULL AFTER Imagen2;
