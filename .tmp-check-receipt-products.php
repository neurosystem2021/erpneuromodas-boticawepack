<?php
require 'C:/xampp/htdocs/boticav1/imprimir/serviceReceiptProducts.php';
$rows=serviceReceiptProducts(4670);foreach($rows as $row)echo json_encode($row),PHP_EOL;
