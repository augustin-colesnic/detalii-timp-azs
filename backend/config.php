<?php
// config.php - KEEP THIS SECRET. Place outside the web root if possible.
// Copy this file to your server and change the admin_token to a long random string.
// this is needed for admin timp-tulcea to set default server message (verset de memorat)
return [
    // Path to SQLite DB file. By default it will be in backend/data/default_message.sqlite
    'db_path' => __DIR__ . '/data/default_message.sqlite',
    // Long random string used to authenticate admin requests. Change this.
    'admin_token' => 'zFMGZPU8h2odKFBLotFGP3LHHzrcErT1CA0BAPEqgDTGbg2VuHZa1SV4MQtXSSui',
];
