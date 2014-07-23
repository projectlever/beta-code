<?php
// test
$id = trim(strip_tags($_POST["id"]));
$files = explode("\n",shell_exec("git diff-tree --no-commit-id --name-only -r " . $id));
echo json_encode($files);
?>
