<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';require 'integration/payroll/api/payroll.php';$db=appPdoConnection();$q=$db->query("SELECT p.FechaIngreso FROM Rhu_PlanillaPersonal p JOIN Rhu_Empleado e ON e.IdEmpleado=p.IdEmpleado WHERE e.DniRuc='73604150'");$date=$q->fetchColumn();echo json_encode(['storedHireDate'=>$date,'cycle'=>nstPayrollCycle('2026-10',$date)]),"\n";
