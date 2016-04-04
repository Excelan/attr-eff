<?php

require dirname(__FILE__).'/../boot.php';

Log::info(json_encode($_POST), 'buffer');

echo '{"status":"ok"}';

?>