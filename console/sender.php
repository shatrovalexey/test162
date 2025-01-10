<?php
error_reporting(\E_ALL);
set_time_limit(0);
ob_implicit_flush();

// Обработка запросов на отправку сообщений и регистрацию в БД

$dbh = require_once('src/db.php');
list($sender,) = require_once('src/sender.php');

// Объявление констант

foreach (['ADDR' => '127.0.0.1', 'PORT' => 10000, 'POOL' => 1000, 'BUFFER' => 100,] as $key => $value)
    define($key, $value);

/**
* Завершение с выводом ошибок
*
* @param ?\resource $sock - сокет
*/
function done(?\resource $sock = null): void
{
    show_error($sock);
    socket_close($sock);

    exit(-1);
}

/**
* Вывод ошибки сокета
*
* @param ?\resource $sock - сокет
*/
function show_error(?\resource $sock = null): void
{
    error_log(socket_strerror(socket_last_error(... ($sock ? [$sock,] : []))));
}

// Стоит учесть что для `id_users` и для `id_messages` существуют FK и есть UNIQUE INDEX.
// То есть, при ошибке `execute` вернёт "ложь".
$sth_ins = $dbh->prepare('
INSERT IGNORE INTO
    `users_messages`
SET
    `id_users` := :id_users
    `id_messages` := :id_messages;
');

// Получение содержимого сообщения
$sth_sel = $dbh->prepare('
SELECT
    `u1`.`fullname`
    , `m1`.`title`
    , `m1`.`body`
FROM
    `messages` AS `m1`
    , `users` AS `u1`
WHERE
    (`m1`.`id` = :id_messages)
    AND (`u1`.`id` = :id_users)
LIMIT 1;
');

// создание и включение сокета
if (($sock = socket_create(\AF_INET, \SOCK_STREAM, \SOL_TCP)) === false) done($sock);
if (socket_bind($sock, ADDR, PORT) === false) done($sock);
if (socket_listen($sock, POOL) === false) done($sock);

// обрабатывает новые подключения
do {
    if (($msgsock = socket_accept($sock)) === false) {
        show_error($sock);

        continue;
    }

    do {
        // чтение только строки, максимум 71 символ. Потому что ожидается кодировка ASCII и size(UNSIGNED BIGINT) = 20
        if (($query = socket_read($msgsock, BUFFER, \PHP_NORMAL_READ)) === false) {
            show_error($msgsock);

            continue;
        }

        // проверка содержмого $query
        if (
            !($query = json_decode($query))
            || !array_key_exists('id_users', $query)
            || !array_key_exists('id_messages', $query)

            // попытка записи об обработке сообщения
            || !$sth_ins->execute([':id_users' => $query['id_users'], ':id_messages' => $query['id_messages'],])
        ) break;

        $sth_ins->closeCursor();

        // запуск отправки сообщения
        $sth_sel->execute([':id_users' => $query['id_users'], ':id_messages' => $id_messages,]);
        $sender(... $sth_sel->fetch(\PDO::FETCH_NUM));
        $sth_sel->closeCursor();

        break;
    } while (true);

    // закрытие подключения клиента
    socket_close($msgsock);
} while (true);

// закрытие подключения сервера
socket_close($sock);

$dbh->disconnect();