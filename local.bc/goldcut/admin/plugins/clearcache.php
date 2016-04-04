<?php 

	$key = $_GET['key'];
	Cache::clear($key);
	
	echo "<p>Кеш для ключа {$key} сброшен</p>";
	
?>