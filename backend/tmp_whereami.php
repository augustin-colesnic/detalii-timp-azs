<?php
// tmp_whereami.php - upload temporarily to your web-accessible backend/ folder
// then open in browser and delete the file afterwards.
header('Content-Type: text/plain; charset=utf-8');
echo "__DIR__: " . __DIR__ . "\n";
echo "__FILE__: " . __FILE__ . "\n";
// Optional useful info, uncomment if you need it (remove phpinfo() afterwards)
// phpinfo();
