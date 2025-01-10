<?php
foreach (['SERVICE' => 'www', 'PROTO' => 'tcp', 'ADDR' => '127.0.0.1', 'PORT' => 10000, 'POOL' => 1000, 'BUFFER' => 100,] as $key => $value)
    define($key, $value);

foreach (['B_PORT' => getservbyname(SERVICE, PROTO), 'B_ADDR' => gethostbyname(ADDR),] as $key => $value)
    define($key, $value);

return [
    /**
    * Заглушка отправки сообщений
    *
    * @param string $fullname - имя
    * @param string $title - заголовок
    * @param string $body - тело сообщения
    *
    * @return bool - результат
    */
    fn (string $fullname, string $title, string $body): bool => true

    /**
    * Отправка заявки на отправку и регистрацию сообщения
    *
    * @param int $id_users
    * @param int $id_messages
    */
    , fn (int $id_users, int $id_messages): bool => {
        if (
            ($socket = socket_create(\AF_INET, \SOCK_STREAM, \SOL_TCP)) === false
            || socket_connect($socket, B_ADDR, B_PORT) === false
        ) return false;

        $data = json_encode([':id_users' => $id_users, ':id_messages' => $id_messages,]);
        $result = !!socket_write($socket, $data, strlen($data));

        socket_close($socket);

        return $result;
    },
];