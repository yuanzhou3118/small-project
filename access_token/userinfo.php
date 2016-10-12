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
    $access_token = trim(decrypt($r->APP_TOKEN));
    $openid = 'orSc_uOLQAuszq7Nr9y9fOlp3UR0';
    $unionId = unionId($access_token,$openid);

//    $context = stream_context_create(array('http' => array('user_agent' => $_SERVER['HTTP_USER_AGENT'])));
    echo $unionId;
} else {
    return FALSE;
}



function unionId($access_token,$openid){
    $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $access_token.'&openid='.$openid;

    $result = https_post($url);
//    var_dump($result);
    $jsoninfo = json_decode($result, true);

    return $jsoninfo['unionid'];
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