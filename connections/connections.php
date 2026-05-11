<?php

function new_db_connection(): mysqli
{
    $url = getenv('MYSQL_URL') ?: getenv('DATABASE_URL') ?: null;
    if ($url) {
        $p        = parse_url($url);
        $hostname = $p['host'];
        $username = $p['user']  ?? 'root';
        $password = $p['pass']  ?? '';
        $dbname   = ltrim($p['path'] ?? '/beirao_fusion', '/') ?: 'beirao_fusion';
        $port     = (int)($p['port'] ?? 3306);
    } else {
        $hostname = getenv('MYSQLHOST')     ?: getenv('MYSQL_HOST')     ?: '127.0.0.1';
        $username = getenv('MYSQLUSER')     ?: getenv('MYSQL_USER')     ?: 'root';
        $password = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '';
        $dbname   = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'beirao_fusion';
        $port     = (int)(getenv('MYSQLPORT') ?: getenv('MYSQL_PORT')   ?: 3306);
    }

    // Connect without selecting a database so "Unknown database" during Railway
    // MySQL initialisation does not prevent the TCP handshake from succeeding.
    $link = mysqli_connect($hostname, $username, $password, '', $port);
    mysqli_set_charset($link, 'utf8');

    // select_db() throws mysqli_sql_exception if the DB isn't ready yet,
    // which lets the retry loop in seed.php catch and retry cleanly.
    $link->select_db($dbname);

    return $link;
}
