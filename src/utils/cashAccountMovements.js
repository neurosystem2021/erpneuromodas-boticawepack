export function isMainCashMovement(item) {
  if (item.IdCuenta !== undefined && item.IdCuenta !== null) return Number(item.IdCuenta) === 1;
  return Number(item.EsTarjeta) === 0;
}
export function partitionCashMovements(items) {
  const rows = Array.isArray(items) ? items : [];
  return {cash:rows.filter(isMainCashMovement),bank:rows.filter(item => !isMainCashMovement(item))};
}
export function sumMovementAmounts(items) {
  return (items || []).reduce((sum,item) => sum + (Number(item.Anulado) ? 0 : Number(item.Importe) || 0),0);
}
export function groupBankMovements(items) {
  const groups = new Map();
  (items || []).forEach(item => {
    const key = String(item.IdCuenta || item.Cuenta);
    if (!groups.has(key)) groups.set(key,{key,account:item.Cuenta || 'Cuenta bancaria',rows:[],total:0});
    groups.get(key).rows.push(item);
  });
  return Array.from(groups.values()).map(group => Object.assign(group,{total:sumMovementAmounts(group.rows)}));
}
