<?php

session_start();

$returnUrl = trim($_SESSION['return_url']);

if (mb_strlen($returnUrl) == 0) {
    $returnUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/cuba/';
}

unset($_SESSION['return_url']);

$openid = trim($_GET['openid']);

if (mb_strlen($openid) == 0) {
    go_header($returnUrl);

    exit();
}

$token = trim($_GET['token']);

if (mb_strlen($token) == 0) {
    go_header($returnUrl);

    exit();
}

$unixtime = trim($_GET['unixtime']);

if (mb_strlen($unixtime) == 0) {
    go_header($returnUrl);

    exit();
}

$config = require_once 'config.php';

if (strcasecmp($token, md5($openid . hash('sha512', $config['wfpuser'] . $config['wfppwd']) . $unixtime)) != 0) {
    go_header($returnUrl);

    exit();
}

$_SESSION['city'] = trim($_GET['city']);

$_SESSION['openid'] = $openid;

go_header($returnUrl);

exit();

function go_header($url)
{
    echo '<script type="text/javascript">window.location.href="' . $url . '";</script>';
}
