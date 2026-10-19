"""Patch only the three IMCEDEN cash closing routes; never execute a close."""
from pathlib import Path
import re

ROOT = Path(__file__).resolve().parent.parent
LIVE = Path('C:/xampp/htdocs/boticav1/api/index.php')

def route(text, method, name):
    pattern = r"\$app->" + method + r"\('" + re.escape(name) + r"',.*?\n\}\);"
    matches = list(re.finditer(pattern, text, re.S))
    if len(matches) != 1:
        raise RuntimeError('Expected one route: ' + name)
    return matches[0]

def patched(text):
    m = route(text, 'post', '/cierrecaja')
    block = m.group()
    start = block.index('    $insert = "INSERT INTO Cb_CierreCaja')
    end = block.index('    return $response->withJson(array(', start)
    block = block[:start] + '''    // Close the same pending movements shown in the report, including earlier
    // dates, manual vouchers and bank references. Never delete their history.
    try {
        $this->db->beginTransaction();
        $stmt = $this->db->prepare("INSERT INTO Cb_CierreCaja
            (FechaCierre, IdTurno, UsuarioReg, Ingresos, Salidas, Ventas, Total, Descripcion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fechaCierre, $idTurno, $usuarioReg, $ingresos, $salidas, $totalVentas, $total, $descripcionCierre]);
        $idCierreCaja = $this->db->lastInsertId();

        $stmt = $this->db->prepare("UPDATE Ve_DocVenta SET IdCierre=?
            WHERE IdCierre IS NULL AND LOWER(UsuarioReg)=LOWER(?)");
        $stmt->execute([$idCierreCaja, $usuarioReg]);

        $stmt = $this->db->prepare("UPDATE Cb_CajaBanco SET IdCierre=?
            WHERE IdCierre IS NULL AND LOWER(UsuarioReg)=LOWER(?)");
        $stmt->execute([$idCierreCaja, $usuarioReg]);
        $this->db->commit();
    } catch (Throwable $error) {
        if ($this->db->inTransaction()) $this->db->rollBack();
        error_log('IMCEDEN cash closing failed: '.$error->getMessage());
        return $response->withJson(array('error' => 'No se pudo completar el cierre. Los movimientos siguen pendientes; vuelve a intentarlo.'), 500);
    }

''' + block[end:]
    text = text[:m.start()] + block + text[m.end():]
    for name in ['/cierrecaja/ingresos', '/cierrecaja/salidas']:
        m = route(text, 'get', name)
        block = m.group()
        # Bank references belong to a closing, not a calendar day. This also
        # preserves references for shifts spanning midnight in closed reports.
        a = block.index('        if ($idCierre > 0) {')
        b = block.index('\n    } else {', a)
        block = block[:a] + block[b:]
        text = text[:m.start()] + block + text[m.end():]
    return text

if __name__ == '__main__':
    original = LIVE.read_text(encoding='utf-8-sig')
    candidate = patched(original)
    target = ROOT/'integration/imceden/cash-close/index.php'
    target.parent.mkdir(parents=True, exist_ok=True)
    target.write_text(candidate, encoding='utf-8')
    # Keep the staged IMCEDEN backend consistent without replacing other routes.
    staged = ROOT/'integration/imceden/backend/api/index.php'
    text = staged.read_text(encoding='utf-8-sig')
    for method, name in [('post','/cierrecaja'),('get','/cierrecaja/ingresos'),('get','/cierrecaja/salidas')]:
        old = route(text,method,name)
        new = route(candidate,method,name).group()
        text = text[:old.start()]+new+text[old.end():]
    staged.write_text(text, encoding='utf-8')
    print('Prepared three IMCEDEN cash closing routes. No database writes.')
