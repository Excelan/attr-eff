<?php

$m = new Message();
$m->urn = 'urn:manufacturer';
$m->action = 'load';
$ds = $m->deliver();

println($ds);

$filepath = (BASE_DIR.'/importexport/csv/'.'manufacturer.csv');
//println($filepath);
$fp = fopen($filepath, 'w');
foreach ($ds as $d)
{
    $d->toCSV($fp);
//    println($d);
}
fclose($fp);



$m = new Message();
$m->urn = 'urn:product';
$m->action = 'load';
$ds = $m->deliver();

println($ds);

$filepath = (BASE_DIR.'/importexport/csv/'.'product.csv'); // realpath
//println($filepath);
$fp = fopen($filepath, 'w');
foreach ($ds as $d)
{
    $d->toCSV($fp);
  //  println($d);
}
fclose($fp);


print "<h2><a href='/importexport/csv/manufacturer.csv'>Скачать производителей</a></h2>";

print "<h2><a href='/importexport/csv/product.csv'>Скачать товары</a></h2>";

?>