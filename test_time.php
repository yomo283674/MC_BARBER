<?php
$hora_apertura = '09:00';
$hora_cierre = '22:00';
$ts_actual = strtotime($hora_apertura);
$ts_cierre = strtotime($hora_cierre);

echo "Apertura: $ts_actual, Cierre: $ts_cierre\n";
echo "ts_actual < ts_cierre: " . ($ts_actual < $ts_cierre ? "TRUE" : "FALSE") . "\n";
