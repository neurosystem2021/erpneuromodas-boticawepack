from pathlib import Path
p=Path('integration/order-supplier/api/index.php');s=Path('C:/xampp/htdocs/boticav1/api/index.php').read_text(encoding='utf-8')
s=s.replace("    $stream=fopen('php://temp','r+');\n    (new \\PhpOffice\\PhpSpreadsheet\\Writer\\Xlsx($book))->save($stream);\n    rewind($stream);\n    $response->getBody()->write(stream_get_contents($stream));fclose($stream);", "    $temporary=tempnam(sys_get_temp_dir(),'nst-order-');\n    try {\n        (new \\PhpOffice\\PhpSpreadsheet\\Writer\\Xlsx($book))->save($temporary);\n        $response->getBody()->write(file_get_contents($temporary));\n    } finally { if(is_file($temporary))unlink($temporary); }")
s=s.replace('        $resp = curl_exec($curl);\n        curl_close($curl);\n\n        // Ruta del archivo generado', "        curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,5);\n        curl_setopt($curl,CURLOPT_TIMEOUT,90);\n        $resp = curl_exec($curl);\n        $status=curl_getinfo($curl,CURLINFO_HTTP_CODE);\n        curl_close($curl);\n        if($resp===false || $status!==200)throw new RuntimeException('No se pudo generar el PDF. Intente nuevamente.');\n\n        // Ruta del archivo generado")
p.write_text(s,encoding='utf-8')
p=Path('src/views/reportes/Ordenes.vue');s=p.read_text(encoding='utf-8');a=s.index('    async generarPdf(');b=s.index('    actualizarDet(',a)
s=s[:a]+'''    async generarPdf(IdOrdenCompra) {
      const preview = window.open("about:blank", "_blank");
      if (preview) preview.document.body.textContent = "Generando PDF...";
      try {
        const res = await this.axios(`${this.API}reporte/ordencomprapdf`, {params: {id: IdOrdenCompra}});
        if (typeof res.data !== "string" || !res.data) throw new Error("Respuesta inválida");
        let url = res.data.replace(/\\\\/g, "");
        if (!/^https?:\\/\\//i.test(url)) url = window.location.protocol + "//" + url;
        url += (url.includes("?") ? "&" : "?") + "v=" + Date.now();
        if (preview) preview.location.replace(url);
        else window.location.assign(url);
      } catch (error) {
        if (preview) preview.close();
        this.$toasted.show("No se pudo generar el PDF. Intenta nuevamente.");
      }
    },
'''+s[b:];p.write_text(s,encoding='utf-8')
