<?php

header('content-type:application/json;charset=utf-8');

session_start();

$action = trim($_POST['action']);

if (mb_strlen($action) == 0) {
    echo json_encode(array('result' => 0));

    exit();
}

$openid = trim($_POST['openid']);

if (mb_strlen($openid) > 100) {
    echo json_encode(array('result' => 0));

    exit();
}

$result = array('result' => 0);

$config = require_once 'config.php';

define('MOBILE_URL', 'https://prcws.acxiom.com.cn/PRC/rest/customer/sendSMS');

define('SOURCE_NAME', '89ba1238cd1744ad56e84ad813e3b4c6');

define('DATA_POST_URL', 'https://prcws.acxiom.com.cn/PRC/rest/customer/dataCollect');

define('MOBILE_REG', '/^1[34578]\d{9}$/');

switch ($action) {
    case 'get_captcha':
        $result = captcha();
        break;
    case 'add_user':
        $result = add_user($openid, $config);
        break;
    default:
        break;
}

echo json_encode($result);

exit();

/**
 * 获取短信验证码。
 *
 * @return array
 */
function captcha()
{
    $mobile = trim($_POST['mobile']);

    if (!preg_match(MOBILE_REG, $mobile)) {
        return array('result' => 0);
    }

    $captcha = mt_rand(1000, 9999);

    $_SESSION['captcha'] = $captcha;

    $result = post_curl(MOBILE_URL,
        array('cellphone' => $mobile, 'smsContent' => '超趴入口通关验证，您的验证码为：' . $captcha));

    if (!is_array($result) || $result['RETURN_CODE'] != '000') {
        error_log('send captcha to ankechen fail, mobile:' . $mobile);

        return array('result' => 3);
    }

    return array('result' => 1);
}

/**
 * 提交用户数据。
 *
 * @param $openid
 * @param array $config
 * @return array
 */
function add_user($openid, $config)
{
    $captcha = trim($_POST['captcha']);

    if (mb_strlen($captcha) == 0) {
        return array('result' => 0);
    }

    $userName = trim($_POST['user_name']);

    if (mb_strlen($userName) == 0 || mb_strlen($userName) > 20) {
        return array('result' => 0);
    }

    $mobile = trim($_POST['mobile']);

    if (!preg_match(MOBILE_REG, $mobile)) {
        return array('result' => 0);
    }

    $myCaptcha = trim($_SESSION['captcha']);

    if (mb_strlen($myCaptcha) == 0) {
        return array('result' => 0);
    }

    if ($captcha != $myCaptcha) {
        return array('result' => 4);//验证码不对。
    }

    unset($_SESSION['captcha']);

    $checkExistUser = check_exist_user($mobile, $config);

    if ($checkExistUser['result'] < 5) {
        return $checkExistUser;
    }

//    $time = time();

//    $authCode = 'TEST01';
//
//    if ($time > strtotime('2017-07-11'))
//        $authCode = 'TEST02';

    $codeList = array('TEST03', 'TEST04', 'TEST05');

    $authCode = $codeList[mt_rand(0, 2)];

    $checkExistStock = check_exist_stock($authCode, $config);

    switch ($checkExistStock) {
        case 0:
            return array('result' => 0);
        case 1:
            return win_user($mobile, $openid, $userName, $authCode, $config);//有库存。
        case 2:
            return fail_user($mobile, $openid, $userName, $config);//无库存。
        default:
            return array('result' => 0);
    }
}

/**
 * 添加中奖用户。
 *
 * @param $mobile
 * @param $openid
 * @param $userName
 * @param $authCode
 * @param $config
 * @return array
 */
function win_user($mobile, $openid, $userName, $authCode, $config)
{
    if (mt_rand(1, 100) < 71) {
        return fail_user($mobile, $openid, $userName, $config);
    }

    $addStatus = add_win_code($authCode, $config);

    if ($addStatus == 0) {
        return array('result' => 0);
    }

    if ($addStatus == 1) {
        return fail_user($mobile, $openid, $userName, $config);
    }

    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $time = date('Y-m-d H:i:s', time());

    $count = 0;

    if ($stmt = $mysqli->prepare('INSERT INTO o2o_campaign_users(openid, mobile, user_name, created_at' .
        ', auth_code) VALUES(?, ?, ?, ?, ?)')
    ) {
        $stmt->bind_param('sssss', $openid, $mobile, $userName, $time, $authCode);
        $stmt->execute();

        $count = $stmt->affected_rows;

        $stmt->close();
    }

    $mysqli->close();

    if ($count == 1) {
        //发送数据到安客臣。
        $result = post_curl(DATA_POST_URL,
            array('cellphone' => $mobile,
                'username' => $userName,
                'openId' => $openid
            ));

        if (!is_array($result) || $result['RETURN_CODE'] != '000') {
            error_log('send win user info to ankechen fail, mobile:' . $mobile);
        }

        return array('result' => 1, 'auth_code' => $authCode);
    } else {
        return array('result' => 0);
    }
}

/**
 * 添加中奖code。
 *
 * @param $authCode
 * @param $config
 * @return int
 */
function add_win_code($authCode, $config)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return 0;
    }

    $mysqli->query('SET NAMES UTF8');

    $count = 0;

    if ($stmt = $mysqli->prepare('INSERT INTO o2o_win_codes(auth_code) VALUES(?)')
    ) {
        $stmt->bind_param('s', $authCode);
        $stmt->execute();

        $count = $stmt->affected_rows;

        $stmt->close();
    }

    $mysqli->close();

    return $count > 0 ? 2 : 1;
}

/**
 * 添加未中奖数据。
 *
 * @param $mobile
 * @param $openid
 * @param $userName
 * @param $config
 * @return array
 */
function fail_user($mobile, $openid, $userName, $config)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $time = date('Y-m-d H:i:s', time());

    $count = 0;

    $authCode = '';

    if ($stmt = $mysqli->prepare('INSERT INTO o2o_campaign_users(openid, mobile, user_name, created_at' .
        ', auth_code) VALUES(?, ?, ?, ?, ?)')
    ) {
        $stmt->bind_param('sssss', $openid, $mobile, $userName, $time, $authCode);
        $stmt->execute();

        $count = $stmt->affected_rows;

        $stmt->close();
    }

    $mysqli->close();

    if ($count == 1) {
        //发送数据到安客臣。
        $result = post_curl(DATA_POST_URL,
            array('cellphone' => $mobile,
                'username' => $userName,
                'openId' => $openid
            ));

        if (!is_array($result) || $result['RETURN_CODE'] != '000') {
            error_log('send not win user info to ankechen fail, mobile:' . $mobile);
        }

        return array('result' => 6);
    } else {
        return array('result' => 1);
    }
}

/**
 * 验证城市是否有库存。
 *
 * @param $authCode
 * @param $config
 * @return int
 */
function check_exist_stock($authCode, $config)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return 0;
    }

    $mysqli->query('SET NAMES UTF8');

    $count = 0;

    if ($stmt = $mysqli->prepare('SELECT count(*) as total_count' . ' FROM o2o_win_codes WHERE auth_code = ?')) {
        $stmt->bind_param('s', $authCode);
        $stmt->execute();
        $stmt->bind_result($totalCount);

        while ($stmt->fetch()) {
            $count = $totalCount;
        }

        $stmt->close();
    }

    $mysqli->close();

    return $count > 0 ? 2 : 1;
}

/**
 * 验证用户是否参加过。
 *
 * @param $mobile
 * @param $config
 * @return array
 */
function check_exist_user($mobile, $config)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $getMobile = null;

    $authCode = null;

    if ($stmt = $mysqli->prepare('SELECT mobile, auth_code' . ' FROM o2o_campaign_users WHERE mobile = ?')) {
        $stmt->bind_param('s', $mobile);
        $stmt->execute();
        $stmt->bind_result($myMobile, $myAuthCode);

        while ($stmt->fetch()) {
            $getMobile = $myMobile;

            $authCode = $myAuthCode;
        }

        $stmt->close();
    }

    $mysqli->close();

    if (mb_strlen($getMobile) == 0) {
        return array('result' => 5);//没参加过。
    } else {
        if (mb_strlen($authCode) == 0) {
            return array('result' => 2);//未中奖。
        } else {
            return array('result' => 3);//已中奖。
        }
    }
}

/**
 * 执行post请求。
 *
 * @param $url
 * @param array $data
 * @return mixed
 */
function post_curl($url, array $data)
{
    $ts = date('Y-m-d H:i:s.B');

    $sign = md5(md5("PRC" . $ts) . $ts);

    $source_name = SOURCE_NAME;

    $data = array_merge($data, compact('ts', 'sign', 'source_name'));

    $jsonData = json_encode($data);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($jsonData))
    );

    $response = curl_exec($ch);

    curl_close($ch);

    return json_decode($response, true, JSON_UNESCAPED_UNICODE);
}
