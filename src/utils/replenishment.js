/* Pure inventory calculations; rotation-period balances are never current stock. */
function finite(value) {
  if (value === null || value === undefined || value === '') return null;
  const number = Number(value);
  return Number.isFinite(number) ? number : null;
}
function periodDays(from, to) {
  const valid = value => {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) return false;
    const date = new Date(value + 'T00:00:00Z');
    return Number.isFinite(date.getTime()) && date.toISOString().slice(0, 10) === value;
  };
  if (!valid(from) || !valid(to)) throw new Error('Selecciona un periodo válido.');
  const days = (Date.parse(to + 'T00:00:00Z') - Date.parse(from + 'T00:00:00Z')) / 86400000 + 1;
  if (days <= 0) throw new Error('La fecha inicial no puede ser posterior a la final.');
  return days;
}
function rankedProducts(products) {
  return products.filter(product => Number(product.ControlaStock) === 1 && Number(product.Anulado || 0) === 0 && Number(product.CantidadVentas) > 0)
    .slice().sort((a, b) => Number(b.CantidadVentas) - Number(a.CantidadVentas) || Number(b.TotalVentas || 0) - Number(a.TotalVentas || 0) || Number(a.IdProducto) - Number(b.IdProducto));
}
function minimumProducts(products) {
  return products.filter(product => Number(product.ControlaStock) === 1 && Number(product.Anulado || 0) === 0 && Number(product.ControlaStockMinimo) === 1 && Number(product.StockMinimo) > 0);
}
function calculate(product, stock, days, coverageDays, rank) {
  const sales = Math.max(0, finite(product.CantidadVentas) || 0);
  const daily = sales / days;
  const configured = Number(product.ControlaStockMinimo) === 1 && Number(product.StockMinimo) > 0;
  const minimum = Math.max(0, finite(product.StockMinimo) || 0);
  const demand = Math.ceil(daily * coverageDays);
  const target = configured ? minimum + Math.max(1, demand) : demand;
  const current = finite(stock);
  let state = 'normal';
  if (current === null) state = 'unknown';
  else if (current <= 0) state = 'urgent';
  else if (configured && current <= minimum) state = 'minimum';
  else if (current < target) state = 'reorder';
  else if (!configured) state = 'configure';
  return Object.assign({}, product, { rank, current, daily, configured, minimum, target, state,
    coverage: current === null || daily === 0 ? null : Math.max(0, current) / daily,
    suggested: current === null ? null : Math.max(0, Math.ceil((target - current) * 10000) / 10000)
  });
}
export { finite, periodDays, rankedProducts, minimumProducts, calculate };
