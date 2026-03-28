<?php
$s = file_get_contents(__DIR__.'/_sum_november_from_paste.php');
if (preg_match("/<<<'TXT'\s*\n(.*)\nTXT;/s", $s, $m)) {
    file_put_contents(__DIR__.'/2025-11.txt', $m[1]);
    echo 'Wrote 2025-11.txt ('.strlen($m[1])." bytes)\n";
}
