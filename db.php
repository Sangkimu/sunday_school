<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function db_connect(): mysqli
{
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'sunday_school';

    $db = new mysqli($host, $username, $password, $database);
    $db->set_charset('utf8mb4');

    return $db;
}

function db_param_types(array $params): string
{
    return implode('', array_map(static fn ($value) => is_int($value) ? 'i' : (is_float($value) ? 'd' : 's'), $params));
}

function db_bind_params(mysqli_stmt $stmt, array $params): void
{
    if (count($params) === 0) {
        return;
    }

    $types = db_param_types($params);
    $refs = [];

    foreach ($params as $key => $value) {
        $refs[$key] = &$params[$key];
    }

    array_unshift($refs, $types);
    call_user_func_array([$stmt, 'bind_param'], $refs);
}

function db_prepare_and_execute(mysqli $db, string $sql, array $params = []): mysqli_stmt
{
    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        throw new RuntimeException($db->error);
    }

    if (!empty($params)) {
        db_bind_params($stmt, $params);
    }

    $stmt->execute();
    return $stmt;
}

function db_fetch_all(mysqli $db, string $sql, array $params = []): array
{
    $stmt = db_prepare_and_execute($db, $sql, $params);
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

function db_fetch_one(mysqli $db, string $sql, array $params = []): array|false
{
    $stmt = db_prepare_and_execute($db, $sql, $params);
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row === null ? false : $row;
}

function db_scalar(mysqli $db, string $sql, array $params = []): mixed
{
    $row = db_fetch_one($db, $sql, $params);

    if ($row === false) {
        return null;
    }

    return array_shift($row);
}
