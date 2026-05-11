<?php

function new_db_connection()
{
    // Railway automatically sets MYSQLHOST, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE, MYSQLPORT
    // when a MySQL plugin is attached to the project.
    $hostname = getenv('MYSQLHOST')     ?: 'localhost';
    $username = getenv('MYSQLUSER')     ?: 'root';
    $password = getenv('MYSQLPASSWORD') ?: '';
    $dbname   = getenv('MYSQLDATABASE') ?: 'beirao_fusion';
    $port     = (int)(getenv('MYSQLPORT') ?: 3306);

    $local_link = mysqli_connect($hostname, $username, $password, $dbname, $port);

    if (!$local_link) {
        die("Connection failed: " . mysqli_connect_error());
    }

    mysqli_set_charset($local_link, "utf8");

    return $local_link;
}