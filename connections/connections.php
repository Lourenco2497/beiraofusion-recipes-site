<?php

function new_db_connection()
{
    // Railway automatically sets MYSQLHOST, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE, MYSQLPORT
    // when a MySQL plugin is attached to the project.
    $hostname = getenv('MYSQLHOST')     ?: ($_SERVER['MYSQLHOST']     ?? '127.0.0.1');
    $username = getenv('MYSQLUSER')     ?: ($_SERVER['MYSQLUSER']     ?? 'root');
    $password = getenv('MYSQLPASSWORD') ?: ($_SERVER['MYSQLPASSWORD'] ?? '');
    $dbname   = getenv('MYSQLDATABASE') ?: ($_SERVER['MYSQLDATABASE'] ?? 'beirao_fusion');
    $port     = (int)(getenv('MYSQLPORT') ?: ($_SERVER['MYSQLPORT']  ?? 3306));

    $local_link = mysqli_connect($hostname, $username, $password, $dbname, $port);

    if (!$local_link) {
        die("Connection failed: " . mysqli_connect_error());
    }

    mysqli_set_charset($local_link, "utf8");

    return $local_link;
}