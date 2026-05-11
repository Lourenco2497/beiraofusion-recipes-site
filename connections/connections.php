<?php

function new_db_connection()
{
    // Try MYSQL_URL first (Railway always provides this as a single connection string)
    $url = getenv('MYSQL_URL') ?: getenv('DATABASE_URL') ?: null;
    if ($url) {
        $p = parse_url($url);
        $hostname = $p['host'];
        $username = $p['user']   ?? 'root';
        $password = $p['pass']   ?? '';
        $dbname   = ltrim($p['path'] ?? '/beirao_fusion', '/');
        $port     = (int)($p['port'] ?? 3306);
    } else {
        // Fall back to individual variables (MYSQLHOST or MYSQL_HOST naming conventions)
        $hostname = getenv('MYSQLHOST')     ?: getenv('MYSQL_HOST')     ?: '127.0.0.1';
        $username = getenv('MYSQLUSER')     ?: getenv('MYSQL_USER')     ?: 'root';
        $password = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '';
        $dbname   = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'beirao_fusion';
        $port     = (int)(getenv('MYSQLPORT') ?: getenv('MYSQL_PORT')   ?: 3306);
    }

    $local_link = mysqli_connect($hostname, $username, $password, $dbname, $port);

    if (!$local_link) {
        die("Connection failed: " . mysqli_connect_error());
    }

    mysqli_set_charset($local_link, "utf8");

    return $local_link;
}