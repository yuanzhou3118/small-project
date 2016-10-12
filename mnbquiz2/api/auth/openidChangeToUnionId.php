<?php
include("config.php");
$config = require_once '../config.php';

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

//    $openidList = getOpenidUnionid($config);
//
//    var_dump($openidList);
//
//    foreach ($openidList['data'] as $item) {//替换openid
//        $result = match($item['openid'], $config);//搜索对应的unionid
////    var_dump($item['openid']);
//        //如果数据重复保留不更新,不过不重复就替换openid为unionid
//        $result = updateOpenid($result, $item['openid'], $config);
//
//        $repeatcount = 0;
//        if($result == 5){
//            $repeatcount++;
//        }
//
//        $failCount = 0;
//        if($result == 0){
//            $failCount++;
//        }
//    }
//    echo '失败的总数是：'.$failCount.'<br>';
//    error_log('失败的总数是：'.$failCount.'<br>');
//    echo '重复的总数是：'.$repeatcount.'<br>';
//    error_log('重复的总数是：'.$repeatcount.'<br>');


    $data = getOpenid($config);
    var_dump($data);

    foreach ($data['data'] as $item) {//替换openid
        $unionId = getUnionId($access_token,$item['openid'],$config);
        echo $unionId['result'].'<br>';
        if($unionId['result'] == 0){
            error_log('openidChangeToUnionId.php update unionid fail,openid:'.$unionId['openid']);
            echo 'openidChangeToUnionId.php update unionid fail,openid:'.$unionId['openid'].'<br>';
        }
    }

} else {
    return FALSE;
}

//获取马爹利的openid，（unionid列表）
function getOpenid($config)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $result = 0;

    if ($stmt = $mysqli->prepare('SELECT openid FROM mnb_user WHERE openid LIKE "o6M%"')
    ) {
        $stmt->execute();
        $stmt->bind_result($myOpenid);

        $result = array();

        while ($stmt->fetch()) {
            $arr = array(
                'openid' => $myOpenid,
            );
            array_push($result, $arr);
        }

        $stmt->close();
    }

    $mysqli->close();

    return array('data' => $result);
}

function getUnionId($access_token,$openid,$config){
    $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $access_token.'&openid='.$openid;

    $result = https_post($url);

    $jsoninfo = json_decode($result, true);

    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $newOpenid = $jsoninfo['unionid'];
    $oldOpenid = $openid;

    $count1 = 0;
    $count2 = 0;
    $count3 = 0;

    if ($stmt = $mysqli->prepare('UPDATE mnb_user SET openid = ? WHERE openid = ?')
    ) {
        $stmt->bind_param('ss', $newOpenid, $oldOpenid);
        $stmt->execute();

        $count1 = $stmt->affected_rows;

        $stmt->close();
    }

    if ($stmt = $mysqli->prepare('UPDATE win_users SET openid = ? WHERE openid = ?')
    ) {
        $stmt->bind_param('ss', $newOpenid, $oldOpenid);
        $stmt->execute();

        $count2 = $stmt->affected_rows;

        $stmt->close();
    }

    if ($stmt = $mysqli->prepare('UPDATE mnb_quiz SET openid = ? WHERE openid = ?')
    ) {
        $stmt->bind_param('ss', $newOpenid, $oldOpenid);
        $stmt->execute();

        $count3 = $stmt->affected_rows;

        $stmt->close();
    }

    $mysqli->close();

    if ($count1 < 1 || $count2 < 1 || $count3 < 1) {
        return array('result' => 0,'openid'=>$openid);//领取失败。
    } else {
        return array('result' => 1,'openid'=>$openid);//领取成功。
    }
}

//获取名士订阅号的openid，（名仕openid列表）
function getOpenidUnionid($config)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $result = 0;

    if ($stmt = $mysqli->prepare('SELECT openid FROM mnb_user WHERE openid LIKE "o6%"')
    ) {
        $stmt->execute();
        $stmt->bind_result($myOpenid);

        $result = array();

        while ($stmt->fetch()) {
            $arr = array(
                'openid' => $myOpenid,
            );
            array_push($result, $arr);
        }

        $stmt->close();
    }

    $mysqli->close();

    return array('data' => $result);
}

//对每个数据进行匹配
function match($openid, $config)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return 2;
    }

    $mysqli->query('SET NAMES UTF8');

    $unionid = null;

    if ($stmt = $mysqli->prepare('SELECT unionid FROM mingshi_user WHERE openid = ?')
    ) {
        $stmt->bind_param('s', $openid);
        $stmt->execute();
        $stmt->bind_result($myUnionid);

        while ($stmt->fetch()) {
            $unionid = $myUnionid;
        }

        $stmt->close();
    }

    $mysqli->close();

    return $unionid;
}

function updateOpenid($newOpenid, $oldOpenid, $config)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $status = null;
    $openid = null;

    if ($stmt = $mysqli->prepare('SELECT openid,status FROM mnb_user WHERE openid = ?')
    ) {
        $stmt->bind_param('s', $newOpenid);//unionid(ou)
        $stmt->execute();
        $stmt->bind_result($myOpenid,$myStatus);

        while ($stmt->fetch()) {
            $openid = $myOpenid;
            $status = $myStatus;
        }

        $stmt->close();
    }

    if(is_null($openid)){//重复数据
        return updateUnionid($newOpenid, $oldOpenid, $config);
    }else{
        return 5;
    }
}

function updateUnionid($newOpenid, $oldOpenid,$config){
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $count1 = 0;
    $count2 = 0;
    $count3 = 0;

    if ($stmt = $mysqli->prepare('UPDATE mnb_user SET openid = ? WHERE openid = ?')
    ) {
        $stmt->bind_param('ss', $newOpenid, $oldOpenid);
        $stmt->execute();

        $count1 = $stmt->affected_rows;

        $stmt->close();
    }

    if ($stmt = $mysqli->prepare('UPDATE win_users SET openid = ? WHERE openid = ?')
    ) {
        $stmt->bind_param('ss', $newOpenid, $oldOpenid);
        $stmt->execute();

        $count2 = $stmt->affected_rows;

        $stmt->close();
    }

    if ($stmt = $mysqli->prepare('UPDATE mnb_quiz SET openid = ? WHERE openid = ?')
    ) {
        $stmt->bind_param('ss', $newOpenid, $oldOpenid);
        $stmt->execute();

        $count3 = $stmt->affected_rows;

        $stmt->close();
    }

    $mysqli->close();

    if ($count1 < 1 || $count2 < 1 || $count3 < 1) {
        return 0;//领取失败。
    } else {
        return 1;//领取成功。
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

