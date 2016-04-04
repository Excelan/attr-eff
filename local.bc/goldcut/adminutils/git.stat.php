<?php

if (ADMIN_AREA !== true) {
    require dirname(__FILE__) . '/../boot.php';
}

// https://gist.github.com/hofmannsven/6814451

print '<pre>';
print "<b>LAST DAY CHANGES</b>\n\n";
print '<span style="color: green">';
print shell_run_command(BASE_DIR, 'git diff --numstat "@{1 day ago}"');
print '</span>';
print "\n\n<b>HISTORY</b>\n\n\n";
print shell_run_command(BASE_DIR, 'git log --stat --summary --no-merges');
print '</pre>';
?>