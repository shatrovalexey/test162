<?php
/**
* Рассылка
*/

/**
* @var \PDOConnection $dbh - подключение к СУБД
*/
$dbh = require_once('src/db.php');

/**
* @var \Closure $sender - приспособление для отправки сообщений
*/
list(, $sender) = require_once('src/sender.php');

/**
* Можно было бы сразу и, кажется, просто выбрать все записи за счёт умножения мощностей сущностей общей сущности для выборки.
* Но, почему-то, не стал. Подумал, что если 10K * 10K * N, который N может быть тоже 100K...
*/

// не стоит забывать, что для каждого `messages` есть `users_messages`.`id_users`=0
$dbh->query("
-- сколько-чего есть
SET
    @`v_count_users` := (SELECT count(*) AS `result` FROM `users` AS `u1`)
    , @`v_count_messages` := (SELECT count(*) AS `result` FROM `messages` AS `m1`);

-- пользователи, которым необходимо что-то дослать
DROP TEMPORARY TABLE IF EXISTS `t_users`;

CREATE TEMPORARY TABLE IF NOT EXISTS `t_users`(
    PRIMARY KEY(`id`)
) COMMENT = 'пользователи, которым что-то недоотправлено' AS
SELECT
    `u1`.`id`
FROM
    `users` AS `u1`

        LEFT OUTER JOIN `users_messages` AS `um1`
            ON (`u1`.`id` = `um1`.`id_users`)
GROUP BY
    1
HAVING
    (count(DISTINCT `um1`.`id_messages`) < @`v_count_messages`);

-- сообщения, которые необходимо кому-то дослать
DROP TEMPORARY TABLE IF EXISTS `t_messages`;

CREATE TEMPORARY TABLE IF NOT EXISTS `t_messages`(
    PRIMARY KEY(`id`)
) COMMENT = 'сообщения, которые недоотправленны' AS
SELECT
    `m1`.`id`
FROM
    `messages` AS `m1`

        LEFT OUTER JOIN `users_messages` AS `um1`
            ON (`m1`.`id` = `um1`.`id_messages`)
WHERE
    (`um1`.`id_messages` IS null)
GROUP BY
    1
HAVING
    (count(DISTINCT `um1`.`id_users`) < @`v_count_users`);
");

/**
* @var \PDOStatement $sth_sel_users - `users`.`id`, для которых актуальная рассылка
*/
$sth_sel_users = $dbh->prepare('
SELECT
    `u1`.`id`
FROM
    `t_users` AS `tu1`;
');

/**
* @var \PDOStatement $sth_sel_messages - `messages`.*, для которых актуальная рассылка для данного `id_users`
*/
$sth_sel_messages = $dbh->prepare('
SELECT
    `tm1`.`id`
FROM
    `t_messages` AS `tm1`

        LEFT OUTER JOIN `users_messages` AS `um1`
            ON (`um1`.`id_messages` = `tm1`.`id`)
                AND (`um1`.`id_users` = :id_users)
WHERE
    (`um1`.`id_users` IS null)
LIMIT 1;
');

// просмотр всех `users`.`id`, для которых актуальна рассылка
$sth_sel_users->execute();
while ($id_users = $sth_sel_users->fetchColumn()) {
    $pid_children = [];

    // просмотр всех `messages`.*, для которых актуальна рассылка для данного `users`.`id`
    $sth_sel_messages->execute([':id_users' => $id_users,]);
    while ($id_messages = $sth_sel_messages->fetchColumn()) {
        /**
        * @var int $pid - PID или результат форка
        */
        $pid = pcntl_fork();

        // форк не вышел
        if ($pid == -1) continue;

        // форк получился
        if ($pid) {
            $pid_children[] = $pid;

            continue;
        }

        // что происходит внутри потомка процесса
        // если отправка не прошла
        exit($sender([':id_users' => $id_users, ':id_messages' => $id_messages,]) ? 0 : -1);
    }
    $sth_sel_messages->closeCursor();

    /**
    * жду завершения всех собранных потомков
    */
    while ($pid_children) foreach ($pid_children as &$pid)
        if (pcntl_waitpid($pid, $status, \WNOHANG))
            unset($pid);
}
$sth_sel_users->closeCursor();