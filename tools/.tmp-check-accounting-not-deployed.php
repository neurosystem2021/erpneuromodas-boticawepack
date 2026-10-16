<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$count=appPdoConnection()->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name LIKE 'co_%'")->fetchColumn();echo 'New accounting tables in live ERP: '.$count.PHP_EOL;
