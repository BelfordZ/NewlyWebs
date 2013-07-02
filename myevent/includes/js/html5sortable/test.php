<?php

$display_layout = $_POST['display_layout'];

$chars = preg_split('/,/', $display_layout, -1, PREG_SPLIT_NO_EMPTY);

print "<pre>";
print_r($chars);
print "</pre>";

?>