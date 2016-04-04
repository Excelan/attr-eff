<?php 
	if ($q = (int) $_GET['question'])
	{
		echo "<div id='help'>";
			include (BASE_DIR."/dashboard/help/{$q}.html");
			echo "<div style='float: right; margin-top: 20px; margin-bottom: 20px; margin-right: 40px;'><a href=/goldcut/admin/>Назад</a></div>";
		echo "</div>";
	}
?>