<?php
include("config.php");
$config = require_once 'config-user.php';

function decrypt($str)
{
//    global $decodekey;
    $strBin = hex2bin($str);
    $td = mcrypt_module_open('des', '', 'ecb', '');
    mcrypt_generic_init($td, 'hgGUN78Gsf', '12345678');
    $strRes = mdecrypt_generic($td, $strBin);
    return $strRes;
}

global $appID;
$r = file_get_contents('http://prapi.pernod-ricard-china.com:8080/wxWeb/apptoken?app_id=wxdfca2c2bde12de7f');
$r = json_decode($r);

if ($r->ERRORMSG === '0' && isset($r->APP_TOKEN)) {
    $access_token = trim(decrypt($r->APP_TOKEN));

//    $response = getUserlist($access_token, $config);
//
//    $total = intval(trim($response['total']));
//
//    $time = intval(ceil($total / 10000));
//
//    echo $total.'<br>';
//
//    for ($i = 1; $i <= $time-1; $i++) {
//        $response = getUserlist($access_token, $config, $response['next_openid']);
        $response = getUserlist($access_token, $config, 'o6M7ojmCs-FhxuSMsn3HW9ualDsI');
//        echo $i.'<br>';
//    }
    return TRUE;
} else {
    return FALSE;
}

//获取用户列表

function getUserlist($access_token, $config, $next_openid = null)
{
    $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=' . $access_token . '&next_openid=' . $next_openid;

    $result = https_post($url);

    $jsoninfo = json_decode($result, true);

    //存入数据库
    foreach ($jsoninfo['data']['openid'] as $item) {
        $save = saveOpenid($item, $config);
    }
    echo count($jsoninfo['data']['openid']);

    var_dump($jsoninfo['next_openid']);

    return $jsoninfo;
}

//存入数据库
function saveOpenid($openid, $config)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return 2;
    }

    $mysqli->query('SET NAMES UTF8');

    $count = 0;

    if ($stmt = $mysqli->prepare('INSERT INTO unionid(openid) VALUES(?)')
    ) {
        $stmt->bind_param('s', $openid);
        $stmt->execute();

        $count = $stmt->affected_rows;

        $stmt->close();
    }

    $mysqli->close();

    if ($count < 1) {
        return 0;//保存失败。
    } else {
        return 1;//保存成功。
    }
}

function https_post($url, $data = null)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    if (!empty($data)) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}

?>