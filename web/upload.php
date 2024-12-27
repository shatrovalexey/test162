<?php
foreach ([
    ['HTTP_BAD_REQUEST', 400,]
    , ['HTTP_SERVER_ERROR', 500,]
    , ['HTTP_OK', 200,]
    , ['HTTP_CONTENT_TYPE', 'application/json',]
] as $args) define(... $args);

/**
* HTTP-ответ в формате JSON
*
* @param mixed $data - данные HTTP-ответа
* @param in $code - код HTTP-ответа
*/
function http_json_response($data, int $code = 200): void
{
    http_json_response_code($code);
    header('Content-Type: ' . HTTP_CONTENT_TYPE);

    echo json_encode(is_scalar($data) ? ['message' => $data,] : $data);
    exit;
}

// что если не существует $_FILES['data']...
if (!isset($_FILES['data']))
    http_json_response('#1. не соблюдён формат запроса', HTTP_BAD_REQUEST);

// $file_path = 'data/users.csv';
/**
* @var ?string $file_path
*/
// что если не существует $_FILES['data']['tmp_name']... или существует...
$file_path = $_FILES['data']['tmp_name']
    ?? http_json_response('#2. не соблюдён формат запроса', HTTP_BAD_REQUEST);

// что если файл не был загружен извне..
if (!is_uploaded_file($file_path))
    http_json_response('#3. не соблюдён формат запроса', HTTP_BAD_REQUEST);

/**
* @var \PDOConnection $dbh - подключение к СУБД
*/
$dbh = require_once('src/db.php');

/**
* @var ?string $path_upload - папка, куда переместить загруженный файл
*/
$path_upload = $dbh->query('SELECT @@secure_file_priv AS `path_upload`;')->fetchColumn()
    ?? http_json_response('некуда загружать', HTTP_SERVER_ERROR);

/**
* @var string $file_temp - путь, куда переместить загруженный файл
*/
$file_temp = tempnam($path_upload, pathinfo($file_path, \PATHINFO_BASENAME));

// что если не удаётся переместить загруженный файл куда-нужно...
if (!move_uploaded_file($file_path, $file_temp))
    http_json_response('не удаётся записать файл', HTTP_SERVER_ERROR);

/**
* @var \PDOStatement $sth
*/
$sth = $dbh->prepare('
LOAD DATA
    INFILE :file_temp
REPLACE INTO TABLE
    `users`
FIELDS
    TERMINATED BY :term
    ENCLOSED BY :enc
LINES
    TERMINATED BY :term2
(`id`, `fullname`);
');

try {
    // загрузка файла в таблицу
    $sth->execute([
        ':file_temp' => $file_temp
        , ':term' => ','
        , ':enc' => '"'
        , ':term2' => "\r\n",
    ]);
} catch (\Exception $exception) {
    // если не удалось
    http_json_response($exception->getMessage(), HTTP_SERVER_ERROR);
} finally {
    // в итоге, нужно удалить файл
    unlink($file_temp);
}

// что-то ответить
http_json_response($sth->row_count(), HTTP_OK);