// Accept a code only after three consecutive matching readings in a short window.
export function createScanConsensus(required = 3, windowMs = 1800) {
  let previous = '', count = 0, started = 0;
  return (value, now = Date.now()) => {
    const code = String(value || '').trim();
    if (!code || code.length > 128) return false;
    if (code !== previous || now - started > windowMs) { previous = code; count = 0; started = now; }
    count++;
    if (count < required) return false;
    previous = ''; count = 0;
    return code;
  };
}

export function inventoryQuantity(value) {
  if (value === '' || value === null || value === undefined) return null;
  const number = Number(value);
  return Number.isFinite(number) && number >= 0 ? Math.round(number * 1000) / 1000 : null;
}
