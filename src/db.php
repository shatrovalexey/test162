<?php
/**
* Создание подключения к СУБД
*
* @var ?\PDOConnection $dbh
*/
global $dbh;

if (isset($dbh)) return $dbh;

/**
* @var array $config - настройки подключения к СУБД
*/
$config = require_once(__DIR__ . '/../conf/db.php');
$dbh = new \PDO(... array_map(fn ($key) => $config[$key] ?? null, ['dsn', 'user', 'pass',]));

foreach ((array)($config['attrs'] ?? []) as $key => $value) $dbh->setAttribute($key, $value);

// действия после подключения
if (isset($config['after'])) $dbh->query($config['after']);

/**
* @return \PDOConnection
*/
return $dbh;