<?php
/**
* настройки подключения к СУБД
*
* @return array
*/
return [
    'dsn' => 'mysql:dbname=test162'
    , 'user' => 'root'
    , 'pass' => 'f2ox9erm'
    , 'after' => '
SET
    NAMES utf8
    , GLOBAL local_infile = true
;
    '
    , 'attrs' => [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    ],
];