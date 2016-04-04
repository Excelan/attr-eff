<?php

if (ADMIN_AREA !== true) {
    require dirname(__FILE__) . '/../boot.php';
}

print '<pre>';

print shell_run_command(BASE_DIR, 'git commit -am "'.time().'"');

print shell_run_command(BASE_DIR, 'git pull');

print shell_run_command(BASE_DIR, 'git push');

print 'SUBMODULE UPDATE';
print shell_run_command(BASE_DIR, 'git submodule update');
print '</pre>';
?>