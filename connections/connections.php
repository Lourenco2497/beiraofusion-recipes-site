<?php

function new_db_connection()
{
    $env = "final";

    if ($env == "localhost") {
        $hostname = 'localhost';
        $username = "root";
        $password = "";
        $dbname = "beirao-fusion";
    } else {
        $hostname = 'labmm.clients.ua.pt';
        $username = "deca_25_BDTSS_47_web";
        $password = "mhQcwer1";
        $dbname = "deca_25_BDTSS_47";
    }


    $local_link = mysqli_connect($hostname, $username, $password, $dbname);


    if (!$local_link) {
        die("Connection failed: " . mysqli_connect_error());
    }


    mysqli_set_charset($local_link, "utf8");

    return $local_link;
}