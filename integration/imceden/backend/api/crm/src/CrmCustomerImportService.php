<?php

final class CrmCustomerImportService
{
    const MAX_ROWS = 2000;
    const MAX_BYTES = 5242880;

    public function readUploadedFile(array $file)
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Seleccione un archivo válido.');
        }
        if ((int) ($file['size'] ?? 0) > self::MAX_BYTES) {
            throw new InvalidArgumentException('El archivo no debe superar 5 MB.');
        }
        $name = (string) ($file['name'] ?? '');
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($extension, array('xlsx', 'csv'), true)) {
            throw new InvalidArgumentException('Use un archivo Excel .xlsx o CSV.');
        }
        $matrix = $extension === 'xlsx'
            ? $this->readXlsx((string) $file['tmp_name'])
            : $this->readCsv((string) $file['tmp_name']);
        return $this->mapRows($matrix);
    }

    private function readCsv($path)
    {
        $handle = fopen($path, 'rb');
        if (!$handle) throw new InvalidArgumentException('No se pudo leer el archivo CSV.');
        $rows = array();
        $first = fgets($handle);
        rewind($handle);
        $separator = substr_count((string) $first, ';') > substr_count((string) $first, ',') ? ';' : ',';
        while (($row = fgetcsv($handle, 0, $separator)) !== false && count($rows) <= self::MAX_ROWS) {
            $rows[] = $row;
        }
        fclose($handle);
        return $rows;
    }

    private function readXlsx($path)
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) throw new InvalidArgumentException('El archivo Excel está dañado.');
        $sheetStat = $zip->statName('xl/worksheets/sheet1.xml');
        $sharedStat = $zip->statName('xl/sharedStrings.xml');
        if (!$sheetStat || (int) $sheetStat['size'] > 20971520
            || ($sharedStat && (int) $sharedStat['size'] > 10485760)) {
            $zip->close();
            throw new InvalidArgumentException('El contenido del Excel es demasiado grande.');
        }
        $shared = array();
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $xml = simplexml_load_string($sharedXml);
            foreach ($xml->si as $item) {
                $text = '';
                if (isset($item->t)) $text = (string) $item->t;
                else foreach ($item->r as $run) $text .= (string) $run->t;
                $shared[] = $text;
            }
        }
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if ($sheetXml === false) throw new InvalidArgumentException('El Excel no contiene una primera hoja.');
        $sheet = simplexml_load_string($sheetXml);
        $rows = array();
        foreach ($sheet->sheetData->row as $xmlRow) {
            $row = array();
            foreach ($xmlRow->c as $cell) {
                $reference = (string) $cell['r'];
                preg_match('/^[A-Z]+/', $reference, $match);
                $column = $this->columnIndex($match[0] ?? 'A');
                $type = (string) $cell['t'];
                if ($type === 'inlineStr') $value = (string) $cell->is->t;
                else {
                    $value = (string) $cell->v;
                    if ($type === 's') $value = $shared[(int) $value] ?? '';
                }
                $row[$column] = $value;
            }
            if ($row) {
                ksort($row);
                $rows[] = $row;
            }
            if (count($rows) > self::MAX_ROWS) break;
        }
        return $rows;
    }

    private function columnIndex($letters)
    {
        $index = 0;
        foreach (str_split($letters) as $letter) $index = $index * 26 + ord($letter) - 64;
        return $index - 1;
    }

    private function mapRows(array $matrix)
    {
        if (count($matrix) < 2) throw new InvalidArgumentException('El archivo debe tener encabezados y clientes.');
        $aliases = array(
            'name' => array('nombre', 'nombres', 'cliente', 'razon social', 'razón social'),
            'phone' => array('telefono', 'teléfono', 'celular', 'whatsapp', 'numero', 'número'),
            'document' => array('documento', 'dni', 'ruc', 'document number'),
            'email' => array('correo', 'email', 'correo electronico', 'correo electrónico'),
        );
        $columns = array();
        foreach ($matrix[0] as $index => $heading) {
            $normalized = $this->normalizeHeading($heading);
            foreach ($aliases as $field => $names) {
                if (in_array($normalized, array_map(array($this, 'normalizeHeading'), $names), true)) $columns[$field] = $index;
            }
        }
        if (!isset($columns['name']) || !isset($columns['phone'])) {
            throw new InvalidArgumentException('El Excel necesita las columnas Nombre y Teléfono.');
        }
        $rows = array();
        $rejected = 0;
        foreach (array_slice($matrix, 1, self::MAX_ROWS) as $row) {
            $name = trim((string) ($row[$columns['name']] ?? ''));
            $phone = preg_replace('/\D+/', '', (string) ($row[$columns['phone']] ?? ''));
            if (strlen($phone) > 9) $phone = substr($phone, -9);
            if ($name === '' || strlen($phone) < 8) {
                $rejected++;
                continue;
            }
            $email = isset($columns['email']) ? strtolower(trim((string) ($row[$columns['email']] ?? ''))) : '';
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $email = '';
            $rows[$phone] = array(
                'display_name' => mb_substr($name, 0, 180),
                'phone' => $phone,
                'document_number' => isset($columns['document'])
                    ? mb_substr(trim((string) ($row[$columns['document']] ?? '')), 0, 32) : '',
                'email' => mb_substr($email, 0, 190),
            );
        }
        return array('rows' => array_values($rows), 'rejected' => $rejected);
    }

    public function normalizeHeading($value)
    {
        $value = mb_strtolower(trim((string) $value), 'UTF-8');
        return strtr($value, array('á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u'));
    }
}
