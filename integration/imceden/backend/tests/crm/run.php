<?php

$tests = array(
    __DIR__ . '/CrmDomainTest.php',
    __DIR__ . '/CrmStaticSafetyTest.php',
    __DIR__ . '/CrmMessagingTest.php',
    __DIR__ . '/CrmCustomerImportTest.php',
);

foreach ($tests as $test) require $test;
echo "CRM TESTS OK\n";
