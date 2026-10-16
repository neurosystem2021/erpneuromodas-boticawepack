const http = require('http');
const WebSocket = require('ws');

const getJson = url => new Promise((resolve, reject) => {
  http.get(url, response => {
    let body = '';
    response.on('data', chunk => { body += chunk; });
    response.on('end', () => {
      try { resolve(JSON.parse(body)); } catch (error) { reject(error); }
    });
  }).on('error', reject);
});

const wait = ms => new Promise(resolve => setTimeout(resolve, ms));

(async () => {
  let targets = [];
  for (let attempt = 0; attempt < 20; attempt += 1) {
    try {
      targets = await getJson('http://127.0.0.1:9227/json');
      if (targets.length) break;
    } catch (_) {}
    await wait(500);
  }
  const target = targets.find(item => item.type === 'page');
  if (!target) throw new Error('No se encontró una página de Chrome');
  const socket = new WebSocket(target.webSocketDebuggerUrl);
  let sequence = 0;
  const pending = new Map();
  socket.on('message', raw => {
    const message = JSON.parse(raw.toString());
    if (message.id && pending.has(message.id)) {
      const { resolve, reject } = pending.get(message.id);
      pending.delete(message.id);
      message.error ? reject(message.error) : resolve(message.result);
    }
  });
  await new Promise((resolve, reject) => {
    socket.once('open', resolve);
    socket.once('error', reject);
  });
  const call = (method, params = {}) => new Promise((resolve, reject) => {
    sequence += 1;
    pending.set(sequence, { resolve, reject });
    socket.send(JSON.stringify({ id: sequence, method, params }));
  });
  await call('Runtime.enable');
  await wait(5000);
  const result = await call('Runtime.evaluate', {
    expression: `(async () => {
      const input = document.querySelector('#txtAddProduct');
      if (!input) return { url: location.href, error: 'No se encontró #txtAddProduct', title: document.title };
      input.focus();
      input.value = 'camara';
      input.dispatchEvent(new Event('input', { bubbles: true }));
      input.dispatchEvent(new KeyboardEvent('keyup', { bubbles: true, key: 'a' }));
      await new Promise(resolve => setTimeout(resolve, 3500));
      const list = document.querySelector('.pos-product-name-search .autocomplete-list ul');
      const outer = document.querySelector('.pos-product-name-search .autocomplete.autocomplete-list');
      const shell = document.querySelector('.pos-shell.card');
      if (!list) return { url: location.href, error: 'No apareció la lista', value: input.value };
      const listStyle = getComputedStyle(list);
      const outerStyle = outer ? getComputedStyle(outer) : null;
      const shellStyle = shell ? getComputedStyle(shell) : null;
      const first = list.querySelector('.pos-product-option');
      return {
        url: location.href,
        rows: list.querySelectorAll('li').length,
        listHeight: Math.round(list.getBoundingClientRect().height),
        listScrollHeight: list.scrollHeight,
        listMaxHeight: listStyle.maxHeight,
        outerOverflow: outerStyle && outerStyle.overflow,
        shellOverflow: shellStyle && shellStyle.overflow,
        firstText: first && first.innerText.trim(),
        hasStockCard: !!list.querySelector('.pos-product-option__stock'),
        hasPriceCard: !!list.querySelector('.pos-product-option__price'),
        imageRight: first && Math.round(first.querySelector('img').getBoundingClientRect().right) === Math.round(first.getBoundingClientRect().right - 5)
      };
    })()`,
    awaitPromise: true,
    returnByValue: true
  });
  process.stdout.write(JSON.stringify(result.result.value, null, 2));
  socket.close();
})().catch(error => {
  process.stderr.write(error.stack || String(error));
  process.exitCode = 1;
});
