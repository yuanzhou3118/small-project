<?php

header('content-type:application/json;charset=utf-8');

session_start();

$action = trim($_POST['action']);

if (mb_strlen($action) == 0) {
    echo json_encode(array('result' => 0));

    exit();
}

$openid = trim($_POST['openid']);

if (mb_strlen($openid) == 0 || mb_strlen($openid) > 100) {
    echo json_encode(array('result' => 0));

    exit();
}

$result = array('result' => 0);

$config = require_once 'config.php';

define('MOBILE_URL', 'https://prcws.acxiom.com.cn/PRC/rest/customer/sendSMS');

define('SOURCE_NAME', 'aa63fe273992cea82f8391bf2080b902');

define('DATA_POST_URL', 'https://prcws.acxiom.com.cn/PRC/rest/customer/dataCollect');

define('MOBILE_REG', '/^1[34578]\d{9}$/');

define('USER_REG', '/^[1-4]$/');

define('LOTTERY_RATE', 99);

switch ($action) {
    case 'add_user':
        $result = add_user($openid, $config);
        break;
    case 'get_captcha':
        $result = captcha();
        break;
    case 'update_user':
        $result = update_user($openid, $config);
        break;
    case 'add_campaign':
        $result = add_campaign($openid, $config);
        break;
    default:
        break;
}

echo json_encode($result);

exit();

/**
 * 验证用户是否参加过活动。
 *
 * @param $openid
 * @param $config
 * @return array
 */
function add_user($openid, $config)
{
    $userOccasion = trim($_POST['user_occasion']);

    if (!preg_match(USER_REG, $userOccasion)) {
        return array('result' => 0);
    }

    $userOccasion = intval($userOccasion);

    $userFrequency = trim($_POST['user_frequency']);

    if (!preg_match(USER_REG, $userFrequency)) {
        return array('result' => 0);
    }

    $userFrequency = intval($userFrequency);

    $mobile = trim($_POST['mobile']);

    if (!preg_match(MOBILE_REG, $mobile)) {
        return array('result' => 0);
    }

    $captcha = trim($_POST['captcha']);

    if (mb_strlen($captcha) == 0) {
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

    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $userMobile = null;

    $userName = null;

    if ($stmt = $mysqli->prepare('SELECT mobile, user_name' . ' FROM win_users WHERE openid = ?')) {
        $stmt->bind_param('s', $openid);
        $stmt->execute();
        $stmt->bind_result($myUserMobile, $myUserName);

        while ($stmt->fetch()) {
            $userMobile = $myUserMobile;

            $userName = $myUserName;
        }

        $stmt->close();
    }

    $mysqli->close();

    if (mb_strlen($userMobile) > 0) {
        if (mb_strlen($userName) > 0) {
            return array('result' => 2);//已经中过了。
        } else {
            return array('result' => 5);//中过未填姓名。
        }
    } else {
        return lottery($openid, $mobile, $config, $userOccasion, $userFrequency);//1:中奖；3：未中奖。
    }
}

/**
 * 处理用户参加活动数据。
 *
 * @param $openid
 * @param $config
 * @return array
 */
function add_campaign($openid, $config)
{
    $userOccasion = trim($_POST['user_occasion']);

    if (!preg_match(USER_REG, $userOccasion)) {
        return array('result' => 0);
    }

    $userOccasion = intval($userOccasion);

    $userFrequency = trim($_POST['user_frequency']);

    if (!preg_match(USER_REG, $userFrequency)) {
        return array('result' => 0);
    }

    $userFrequency = intval($userFrequency);

    $saveResult = save_campaign($openid, $config, $userOccasion, $userFrequency);

    if($saveResult['result'] == 1){
        $result = post_curl(DATA_POST_URL,
            array(
                'preferredDrinkingOccasion' => get_user_occasion($userOccasion),
                'preferredDrinkingFreq' => get_user_frequency($userFrequency),
                'openId' => $openid,
            ));

        if (!is_array($result) || $result['RETURN_CODE'] != '000') {
            error_log('send campaign user info to ankechen fail, openid:' . $openid);
        }
    }

    return array('result' => $saveResult['result']);
}

/**
 * 保存用户参加活动数据。
 *
 * @param $openid
 * @param $config
 * @param $userOccasion
 * @param $userFrequency
 * @return array
 */
function save_campaign($openid, $config, $userOccasion, $userFrequency)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $time = date('Y-m-d H:i:s', time());

    $count = 0;

    if ($stmt = $mysqli->prepare('INSERT INTO campaign_users(openid, created_at, user_occasion' .
        ', user_frequency) VALUES(?, ?, ?, ?)')
    ) {
        $stmt->bind_param('ssii', $openid, $time, $userOccasion, $userFrequency);
        $stmt->execute();

        $count = $stmt->affected_rows;

        $stmt->close();
    }

    $mysqli->close();

    if ($count < 1) {
        return array('result' => 0);//保存失败。
    } else {
        return array('result' => 1);//保存成功。
    }
}

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
        array('cellphone' => $mobile, 'smsContent' => '验证码为：' . $captcha . '。如非本人操作，请勿予理会。'));

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
 * @param $config
 * @return array
 */
function update_user($openid, $config)
{
    $userName = trim($_POST['user_name']);

    if (mb_strlen($userName) == 0 || mb_strlen($userName) > 100) {
        return array('result' => 0);
    }

    $address = trim($_POST['address']);

    if (mb_strlen($address) == 0 || mb_strlen($address) > 200) {
        return array('result' => 0);
    }

    $checkExistUser = check_exist_user($openid, $config);

    if ($checkExistUser['result'] < 2) {
        return array('result' => 3);//用户未中奖。
    }

    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $time = date('Y-m-d H:i:s', time());

    $count = 0;

    if ($stmt = $mysqli->prepare('UPDATE win_users SET user_name = ?, address = ?, updated_at = ? WHERE openid = ?')
    ) {
        $stmt->bind_param('ssss', $userName, $address, $time, $openid);
        $stmt->execute();

        $count = $stmt->affected_rows;

        $stmt->close();
    }

    $mysqli->close();

    if ($count < 1) {
        return array('result' => 2);//更新失败。
    } else {
        //发送数据到安客臣。
        $result = post_curl(DATA_POST_URL,
            array('cellphone' => $checkExistUser['mobile'],
                'username' => $userName,
                'address' => $address,
                'preferredDrinkingOccasion' => get_user_occasion($checkExistUser['user_occasion']),
                'preferredDrinkingFreq' => get_user_frequency($checkExistUser['user_frequency']),
                'openId' => $openid,
            ));

        if (!is_array($result) || $result['RETURN_CODE'] != '000') {
            error_log('send win user info to ankechen fail, openid:' . $openid);
        }

        return array('result' => 1);//更新成功。
    }
}

/**
 * 验证用户是否中奖过。
 *
 * @param $openid
 * @param $config
 * @return array
 */
function check_exist_user($openid, $config)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $mobile = null;

    $userOccasion = 0;

    $userFrequency = 0;

    if ($stmt = $mysqli->prepare('SELECT mobile, user_occasion, user_frequency'
        . ' FROM win_users WHERE openid = ?')
    ) {
        $stmt->bind_param('s', $openid);
        $stmt->execute();
        $stmt->bind_result($myMobile, $myUserOccasion, $myUserFrequency);

        while ($stmt->fetch()) {
            $mobile = $myMobile;

            $userOccasion = $myUserOccasion;

            $userFrequency = $myUserFrequency;
        }

        $stmt->close();
    }

    $mysqli->close();

    if (mb_strlen($mobile) == 0) {
        return array('result' => 1);
    } else {
        $result = array();

        $result['result'] = 2;
        $result['mobile'] = $mobile;
        $result['user_occasion'] = $userOccasion;
        $result['user_frequency'] = $userFrequency;

        return $result;
    }
}

/**
 * 抽奖。
 *
 * @param $openid
 * @param $mobile
 * @param $config
 * @param int $userOccasion
 * @param int $userFrequency
 * @return array
 */
function lottery($openid, $mobile, $config, $userOccasion, $userFrequency)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $count = 0;

    if ($stmt = $mysqli->prepare('SELECT COUNT(*) AS ' . 'total_count FROM win_users')
    ) {
        $stmt->execute();
        $stmt->bind_result($myCount);

        while ($stmt->fetch()) {
            $count = $myCount;
        }

        $stmt->close();
    }

    $mysqli->close();

    if ($config['stock'] > $count && mt_rand(1, 100) > LOTTERY_RATE) {
        return win_user($openid, $mobile, $config, $userOccasion, $userFrequency);//中奖。
    } else {
        return fail_user($openid, $mobile, $config, $userOccasion, $userFrequency);//未中奖。
    }
}

/**
 * 保存中奖用户。
 *
 * @param $openid
 * @param $mobile
 * @param $config
 * @param int $userOccasion
 * @param int $userFrequency
 * @return array
 */
function win_user($openid, $mobile, $config, $userOccasion, $userFrequency)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $time = date('Y-m-d H:i:s', time());

    $count = 0;

    if ($stmt = $mysqli->prepare('INSERT INTO win_users(openid, mobile, created_at, user_occasion' .
        ', user_frequency) VALUES(?, ?, ?, ?, ?)')
    ) {
        $stmt->bind_param('sssii', $openid, $mobile, $time, $userOccasion, $userFrequency);
        $stmt->execute();

        $count = $stmt->affected_rows;

        $stmt->close();
    }

    $mysqli->close();

    if ($count < 1) {
        return array('result' => 0);//领取失败。
    } else {
        return array('result' => 1);//领取成功。
    }
}

/**
 * 保存未中奖用户。
 *
 * @param $openid
 * @param $mobile
 * @param $config
 * @param int $userOccasion
 * @param int $userFrequency
 * @return array
 */
function fail_user($openid, $mobile, $config, $userOccasion, $userFrequency)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $time = date('Y-m-d H:i:s', time());

    $count = 0;

    if ($stmt = $mysqli->prepare('INSERT INTO fail_users(openid, mobile, created_at, user_occasion' .
        ', user_frequency) VALUES(?, ?, ?, ?, ?)')
    ) {
        $stmt->bind_param('sssii', $openid, $mobile, $time, $userOccasion, $userFrequency);
        $stmt->execute();

        $count = $stmt->affected_rows;

        $stmt->close();
    }

    $mysqli->close();

    if ($count < 1) {
        return array('result' => 0);//未中奖保存失败。
    } else {
        //发送数据到安客臣。
        $result = post_curl(DATA_POST_URL,
            array('cellphone' => $mobile,
                'preferredDrinkingOccasion'=> get_user_occasion($userOccasion),
                'preferredDrinkingFreq'=> get_user_frequency($userFrequency),
                'openId' => $openid
            ));

        if (!is_array($result) || $result['RETURN_CODE'] != '000') {
            error_log('send not win user info to ankechen fail, openid:' . $openid);
        }

        return array('result' => 3);//未中奖保存成功。
    }
}

/**
 * 获取给安客臣的场景值。
 *
 * @param int $userOccasion
 * @return string
 */
function get_user_occasion($userOccasion)
{
    switch ($userOccasion) {
        case 1:
            return '21008';
        case 2:
            return '21012';
        case 3:
            return '21005';
        case 4:
            return '21013';
        default:
            return '';
    }
}

/**
 * 获取给安客臣的频率值。
 *
 * @param int $userFrequency
 * @return string
 */
function get_user_frequency($userFrequency)
{
    switch ($userFrequency) {
        case 1:
            return '12';
        case 2:
            return '14';
        case 3:
            return '15';
        case 4:
            return '21';
        default:
            return '';
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
