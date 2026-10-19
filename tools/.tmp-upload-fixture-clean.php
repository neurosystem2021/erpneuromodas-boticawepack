<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$data=json_decode(file_get_contents(__DIR__.'/.tmp-upload-fixture.json'),true);if(!preg_match('/^quote_files_test_[a-f0-9]{10}$/D',$data['schema']))throw new RuntimeException('Unexpected test database');appPdoConnection()->exec('DROP DATABASE `'.$data['schema'].'`');echo 'Isolated upload test database removed';
