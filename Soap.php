<?php


$s = new SoapClient( 'http://localhost/mantis/api/soap/mantisconnect.php?wsdl' );
$u = 'administrator';
$p = 'root';
$id = $s->mc_login($u,$p)->account_data->id;
$proj= 1;

echo $id = $categoryList = $s->mc_project_add_category($u,$p, $proj, 'cat') . "\n";
$categoryList = $s->mc_project_get_categories($u,$p, $proj);
var_dump($categoryList);
echo $id = $s->mc_project_delete_category($u,$p, $proj, 'cat'). "\n";
