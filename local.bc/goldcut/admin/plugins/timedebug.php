<?php 
if (!defined('ADMIN_AREA')) die("Direct call");
?>

<script type="text/javascript" src="/lib/js/jstz/jstz.min.js"></script>

<table cellspacing=0 cellpadding=4 border=1>
<tr>
	<th>&nbsp;</th>
	<th>Сервер</th>
	<th>Клиент</th>
</tr>
<tr>
	<td>Код шкалы временной зоны</td>
	<td><?php echo date('e'); ?></td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>Признак летнего времени</td>
	<td><?php echo date('I'); ?></td>
	<td>
		<script>
		var timezone = jstz.determine_timezone();
		document.writeln( timezone.dst() );
		</script>
	</td>
</tr>
<tr>
	<td>Разница с временем по Гринвичу</td>
	<td><?php echo date('O'); ?></td>
	<td>
		<!-- (getTimezoneOffset() / as Text / timezone.offset())<br> -->
		<script>
		var today = new Date();
		document.writeln( today.getTimezoneOffset() );
		document.writeln('/');
		var clientGMTOffset = -(new Date().getTimezoneOffset() / 60);
		if (clientGMTOffset > 0) clientGMTOffsetTEXT = 'GMT +' + clientGMTOffset;
		else clientGMTOffsetTEXT = 'GMT ' + clientGMTOffset;
		document.writeln( clientGMTOffsetTEXT );
		document.writeln('/');
		document.writeln( timezone.offset() );
	</script>
	</td>
</tr>
<tr>
	<td>UNIX Timestamp</td>
	<td><?php echo time(); ?></td>
	<td>
		<script>
			var clientts = Math.round(new Date().getTime() / 1000);
			document.writeln(clientts);
		</script>
	</td>	
</tr>
<tr>
	<td>GMT/UTC время</td>
	<td><?php echo gmdate("Y-m-d\TH:i:s\Z") ?></td>
	<td>
		<script>
			var clientts = Math.round(new Date().getTime() / 1000);
			document.writeln(today.toUTCString());
		</script>
	</td>
</tr>
<tr>
	<td>Локальное время</td>
	<td><?php echo date('r'); ?></td>
	<td>
		<script>
			var clientts = Math.round(new Date().getTime() / 1000);
			document.writeln(today.toSystemLocaleString());
		</script>
	</td>
</tr>
<tr>
	<td>Аббревиатура временной зоны</td>
	<td><?php echo date('T'); ?></td>
	<td>&nbsp;</td>
</tr>
<table>

<p>TODO Add world clock for target timezones (conf defined)</p>