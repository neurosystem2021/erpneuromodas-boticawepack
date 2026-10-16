from pathlib import Path
p=Path('integration/receipt-finish/imprimir/index.php');s=p.read_text(encoding='utf-8');s=s.replace('SELECT mp.MetodoPago,pd.Importe,pd.NroTarjeta',"SELECT CASE pd.IdMetodoPago WHEN 1 THEN 'Efectivo' WHEN 4 THEN 'Yape / Plin' WHEN 2 THEN 'Tarjeta' WHEN 3 THEN 'Transferencia' ELSE mp.MetodoPago END MetodoPago,pd.Importe,pd.NroTarjeta");p.write_text(s,encoding='utf-8')
