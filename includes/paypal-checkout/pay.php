<?php
$redirect_url = $_GET['redirect_url'];

$redirect_url = urldecode($redirect_url);

header('Location: ' . $redirect_url);
