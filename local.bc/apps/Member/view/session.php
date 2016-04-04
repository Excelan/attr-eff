<div class="FL rama rama2 FH">
	
<?php

if ($this->role == "USER")
{
	echo '<h3>Logout (role=USER)</h3>';
}
else
{
	echo '<p class="TM1 BM1"><a href="/member/login" class="blue-pill">Login</a> ($this->role != "USER")</p>';
	echo '<h3>FORCE Logout</h3>';
}

echo '
<form action="/member/logout" method=post>
<input type="submit" value="Logout" class="blue-pill">
</form>';

?>

</div>