<?php 
if (!defined('ADMIN_AREA')) die("Direct call");
?>

<h2>Импорт Excel XML</h2>

<form action="/goldcut/admin/?plugin=import" method="post" enctype="multipart/form-data">

	Какие сущности создать из файла: <input name="urn" type="text" value="urn-film" /> 
	<input name="xmlfile" type="file" /> 
	<input type="submit" value="Импортировать" />

</form>

<?php

/**
<Cell><Data ss:Type="Number">11</Data></Cell>
<Cell><Data ss:Type="String">ЕВРОТУР</Data></Cell>
*/		 

	if (!count($_FILES)) exit();

	/*
	printH('$_POST params:');
	println($_POST);
	printH('$_FILES raw:');
	println($_FILES);
	*/
	
	$suffix = 'xmlfile';
	
	$error = (int) $_FILES[$suffix]['error'];
	$type = $_FILES[$suffix]['type'];
	$size = $_FILES[$suffix]['size'];
	$name = basename($_FILES[$suffix]['name']);
	$tmpfile = $_FILES[$suffix]['tmp_name'];
	
	// 4 == $error если в форме был input file, а файл не выбран. те это "soft error" 
	if (4 == $error) continue;
	
	if ($error > 0)
	{
		$errorText = "Error {$error} in upload"; 
		Log::error($errorText, 'myerrornamespace');
		throw new Exception($errorText);
	}
	
	if (is_uploaded_file($tmpfile))
	{
		// $all = array();
		$dom = DOMDocument::load($tmpfile);
		$rows = $dom->getElementsByTagName( 'Row' );
		$first_row = true;
		foreach ($rows as $row)
		{
			if (!$first_row)
			{
				$index = 1;
				$cells = $row->getElementsByTagName('Cell');
				$row = array();
				foreach($cells as $cell)
				{ 
					$ind = $cell->getAttribute('Index');
					if ($ind != null) $index = $ind;
					$row[] = $cell->nodeValue; // $row[$index]
					$index += 1;
				}
				$all[] = $row;
			}
			$first_row = false;
		}
	}
	else
	{
		$errorText = "Forget upload. (Security)"; 
		Log::error($errorText, 'myerrornamespace');
		throw new Exception($errorText);
	}
	
	

	foreach ($all as $row)
	{
		try
		{
			if (mb_strlen($row[1]) > 4)
			{
				$m = new Message();
				$m->action = 'create';
				$m->urn = $_POST['urn'];
				$m->title = $row[1];
				$m->origin = $row[2];
				$m->year = $row[3];
				$m->anons = $row[4];
				$m->duration = $row[5];
				$result = $m->deliver();
				//println($m);
				//println($result);
			}
		}
		catch (Exception $e) 
		{
			println($e->getMessage());
		}
	}
	
	
?>