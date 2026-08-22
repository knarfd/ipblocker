<?php

$timestamp=date("Y-m-d H:i:s");

shell_exec('git checkout --orphan clean-main');
shell_exec('git add .');
shell_exec('git commit -m "'. escapeshellcmd($timestamp) . ' Updated"');
shell_exec('git branch -D main');
shell_exec('git branch -m main');
shell_exec('git push -f origin main');

?>
