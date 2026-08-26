<?php
require 'config/database.php';
require 'models/Servicio.php';
$s = new Servicio();
var_dump($s->contarActivos());
