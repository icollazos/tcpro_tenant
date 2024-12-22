<?php
include('api/configurador.php');
$result = yaml_parse_file('config.yaml');
$P=1;
p($P,$result);
die();
