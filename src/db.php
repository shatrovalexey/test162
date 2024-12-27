<?php
global $dbh;

if (isset($dbh)) return $dbh;

$config = require_once(__DIR__ . '/../conf/db.php');
$dbh = new \PDO(... array_map(fn ($key) => $config[$key], ['dsn', 'user', 'pass',]));

if (isset($config['attrs'])) foreach ((array)$config['attrs'] as $key => $value) $dbh->setAttribute($key, $value);
if (isset($config['after'])) $dbh->query($config['after']);

return $dbh;