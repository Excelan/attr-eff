<?php
	extract($this->context);

	print "<h1>{$read->title}</h1>";
	
	print "
	<div class=read>
		{$attach}
		{$read->text}
	</div>";
?>