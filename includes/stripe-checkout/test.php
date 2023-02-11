<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__  . '/PayPal-PHP-SDK/autoload.php';

include_once 'config.php';

$url = "https://ipinfo.io/209.126.83.215/json?token=11ee714a45e924";
$ip_info_json = file_get_contents($url);
$ip_info = json_decode($ip_info_json);

echo $ip_info->country;

print_r($ip_info);
