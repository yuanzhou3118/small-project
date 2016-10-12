<?php
include("config.php");
function decrypt($str)
{
    global $decodekey;
    $strBin = hex2bin($str);
    $td = mcrypt_module_open('des', '', 'ecb', '');
    mcrypt_generic_init($td, $decodekey, '12345678');
    $strRes = mdecrypt_generic($td, $strBin);
    return $strRes;
}

global $appID;
$r = file_get_contents('http://prapi.pernod-ricard-china.com:8080/wxWeb/apptoken?app_id=' . $appID);
$r = json_decode($r);

if ($r->ERRORMSG === '0' && isset($r->APP_TOKEN)) {
    #二维码参数
    $qrcode = '{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "ctripTianxunqunar4page2"}}}';

    $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . decrypt($r->APP_TOKEN);

    $result = https_post($url, $qrcode);
    $jsoninfo = json_decode($result, true);
    $ticket = $jsoninfo['ticket'];

    echo $ticket;

    $codeurl = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $ticket;

    $img = file_get_contents($codeurl);
    file_put_contents('ctripTianxunqunar4page2.jpg', $img);
    $context = stream_context_create(array('http' => array('user_agent' => $_SERVER['HTTP_USER_AGENT'])));
    return true;
} else {
    return FALSE;
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