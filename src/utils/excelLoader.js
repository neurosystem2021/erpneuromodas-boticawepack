let loading;
export default function loadExcel() {
  if (window.ExcelJS) return Promise.resolve(window.ExcelJS);
  if (!loading) loading = new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.src = new URL('static/vendor/exceljs-4.4.0.min.js', document.baseURI).href;
    script.onload = () => {
      if (window.ExcelJS) resolve(window.ExcelJS);
      else { loading = null; script.remove(); reject(new Error('No se pudo iniciar el lector Excel.')); }
    };
    script.onerror = () => { loading = null; script.remove(); reject(new Error('No se pudo cargar el lector Excel. Intenta nuevamente.')); };
    document.head.appendChild(script);
  });
  return loading;
}
