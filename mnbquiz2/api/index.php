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

define('SOURCE_NAME', '8e2902a7b2dcb72717c39fe53f64afd9');

define('DATA_POST_URL', 'https://prcws.acxiom.com.cn/PRC/rest/customer/dataCollect');
//define('DATA_POST_URL', 'https://uta01.acxiom.com.cn/PRC/rest/customer/dataCollect');

define('MOBILE_REG', '/^1[34578]\d{9}$/');

define('TYPE_REG', '/^[1-5]$/');

define('BEHAVIOR_POST_URL', 'https://prcws.acxiom.com.cn/PRC/rest/customer/BehaviorCollect');

//define('SURVEY_POST_URL', 'https://uat01.acxiom.com.cn/PRC/rest/customer/SurveyCollect');
define('SURVEY_POST_URL', 'https://prcws.acxiom.com.cn/PRC/rest/customer/SurveyCollect');

switch ($action) {
    case 'save_quiz'://存储题目
        $result = save_quiz($openid, $config);
        break;
    case 'check_user'://抽奖
        $result = check_user($openid, $config);
        break;
    case 'create_user'://创建openid
        $result = create_user($openid, $config);
        break;
    case 'get_captcha'://获取验证码
        $result = captcha();
        break;
    case 'update_user':
        $result = update_user($openid, $config);
        break;
    default:
        break;
}

echo json_encode($result);

exit();


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
        return array('result' => 4);//用户未中奖。
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
//        $result = post_curl(DATA_POST_URL,
//            array(
//                'cellphone' => $checkExistUser['mobile'],
//                'name' => $userName,
//                'address' => $address,
//                'openId' => $openid,
//            ));
//
//        if (!is_array($result) || $result['RETURN_CODE'] != '000') {
//            error_log('send updata win user info to ankechen fail, openid:' . $openid);
//        }

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

    $userName = null;

    if ($stmt = $mysqli->prepare('SELECT mobile,user_name FROM win_users WHERE openid = ?')
    ) {
        $stmt->bind_param('s', $openid);
        $stmt->execute();
        $stmt->bind_result($myMobile, $myUserName);

        while ($stmt->fetch()) {
            $mobile = $myMobile;
            $userName = $myUserName;
        }

        $stmt->close();
    }

    $mysqli->close();

    if (mb_strlen($mobile) == 0) {
        return array('result' => 1);
    } else {
        if (mb_strlen($userName) == 0) {
            return array('result' => 2, 'mobile' => $mobile);
        }
        return array('result' => 3, 'mobile' => $mobile, 'user_name' => $userName);
    }
}


/**
 * 创建用户存储openid
 *
 *
 * @param $openid
 * @param $config
 * @return array
 */
function create_user($openid, $config)
{
    $utmSource = trim($_POST['utm_source']);

    if (mb_strlen($utmSource) == 0 || mb_strlen($utmSource) > 20) {
        return array('result' => 0);
    }

    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 120);
    }

    $mysqli->query('SET NAMES UTF8');

    $count = 0;

    $mobile = null;

    $openidCount = 0;

    if ($stmt = $mysqli->prepare('SELECT mobile,COUNT(*) FROM mnb_user WHERE openid = ? ')) {
        $stmt->bind_param('s', $openid);
        $stmt->execute();

        $stmt->bind_result($myMobile, $mycount);

        while ($stmt->fetch()) {
            $mobile = $myMobile;
            $openidCount = $mycount;
        }
    }

    $quizId = null;

    if ($stmt = $mysqli->prepare('SELECT quiz_id FROM mnb_quiz WHERE openid = ? ORDER BY created_at DESC LIMIT 1')) {
        $stmt->bind_param('s', $openid);
        $stmt->execute();

        $stmt->bind_result($myquizId);

        while ($stmt->fetch()) {
            $quizId = $myquizId;
        }
    }

    if ($openidCount > 0) {//openid已经存在
        if ($quizId < 4) {//没玩过关键题
            return array('result' => 1, 'quiz_id' => $quizId);
        } else if ($quizId == 5) {//第五题
            return array('result' => 5, 'quiz_id' => $quizId, 'quiz_answer4' => quizIdQuizAnswer($openid, $config, 4), 'quiz_answer5' => quizIdQuizAnswer($openid, $config, 5));
        } else if (is_null($mobile)) {//题目做完了没有抽奖
            return array('result' => 2, 'quiz_id' => $quizId, 'quiz_answer4' => quizIdQuizAnswer($openid, $config, 4));//进入抽奖页面
        } else {//玩过了
            $checkExistUser = check_exist_user($openid, $config);
            if ($checkExistUser['result'] == 2) {
                return array('result' => 4, 'quiz_answer4' => quizIdQuizAnswer($openid, $config, 4),);//用户未填写信息。
            }
            return array('result' => 3, 'quiz_answer4' => quizIdQuizAnswer($openid, $config, 4),);
        }
    }
    $time = date('Y-m-d H:i:s', time());

    if ($stmt = $mysqli->prepare('INSERT INTO mnb_user(openid,utm_source,created_at) VALUES(?,?,?)')
    ) {
        $stmt->bind_param('sss', $openid, $utmSource, $time);
        $stmt->execute();

        $count = $stmt->affected_rows;

        $stmt->close();
    }

    $mysqli->close();


    if ($count < 1) {
        return array('result' => 80);//创建失败。
    } else {
//        $result = post_curl(DATA_POST_URL,
//            array(
//                'openId' => $openid
//            ));
//        error_log('send campaign user info to ankechen, openid:' . $openid . ',result:' . json_encode($result));
//        if (!is_array($result) || $result['RETURN_CODE'] != '000') {
//            error_log('send create user info to ankechen fail, openid:' . $openid);
//        }
        return array('result' => 1, 'quiz_id' => 0);//创建成功。
    }
}

/**
 * 返回第四题的答案
 *
 * @param $openid
 * @param $config
 * @return array
 */
function quizIdQuizAnswer($openid, $config, $quiz_id)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $quizAnswer = null;

    if ($stmt = $mysqli->prepare('SELECT quiz_answer FROM mnb_quiz WHERE openid = ? AND quiz_id = ?')) {
        $stmt->bind_param('si', $openid, $quiz_id);
        $stmt->execute();

        $stmt->bind_result($myQuizAnswer);

        while ($stmt->fetch()) {
            $quizAnswer = $myQuizAnswer;
        }

        $stmt->close();
    }

    $mysqli->close();

    return $quizAnswer;//第四题答案
}

/**
 * 存储题目答案
 *
 * @param $openid
 * @param $config
 * @return array
 */
function save_quiz($openid, $config)
{
    $quizId = intval(trim($_POST['quiz_id']));

    if ($quizId < 1) {
        return array('result' => 0);
    }

    $questionID = 80 + $quizId;

    $quizAnswer = trim($_POST['quiz_answer']);

    if (mb_strlen($quizAnswer) == 0) {
        return array('result' => 0);
    }

    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $time = date('Y-m-d H:i:s', time());

    $count = 0;

    if ($stmt = $mysqli->prepare('INSERT INTO mnb_quiz (openid, created_at, quiz_id,quiz_answer) VALUES(?, ?, ?, ?)')
    ) {
        $stmt->bind_param('ssis', $openid, $time, $quizId, $quizAnswer);
        $stmt->execute();

        $count = $stmt->affected_rows;

        $stmt->close();
    }

    $id = 0;

    if ($stmt = $mysqli->prepare('SELECT id FROM mnb_quiz WHERE openid = ? AND quiz_id = ?')
    ) {
        $stmt->bind_param('si', $openid, $quizId);
        $stmt->execute();
        $stmt->bind_result($myId);

        while ($stmt->fetch()) {
            $id = $myId;
        }

        $stmt->close();
    }

    $mysqli->close();

//    if (mb_strlen($id) < 4) {
//        switch (mb_strlen($id)) {
//            case 1:
//                $id = '000' . $id;
//                break;
//            case 2:
//                $id = '00' . $id;
//                break;
//            case 3:
//                $id = '0' . $id;
//                break;
//            default:
//                break;
//
//        }
//    }
//
//    $unixTime = date('Y-m-d H:i:s.B', time());
//
//    $serialTime = date('YmdHi', time());
//
    if ($count < 1) {
        return array('result' => 0);//保存失败。
    } else {
//        $runtime = microtime(true);
//        //发送数据到安客臣。
//        $result = post_curl(SURVEY_POST_URL,
//            array(
//                'credential_type' => 'openId',
//                'credentialID' => $openid,
//                'timestamp' => $unixTime,
//                'questionID' => $questionID,
//                'answerID' => $quizAnswer,
//                'openAnswer' => '',
//                'serialNumber' => $serialTime . $id,
//                'surveyCode' => 'ques_Martell Noblige_001',
//            ));
//
//        error_log('time span for acxiom user coupon api,openid:' . $openid .
//            ',time:' . round(microtime(true) - $runtime, 4) . 's');
//
//        error_log('send quiz info to ankechen, openid:' . $openid . ',result:' . json_encode($result));
//
//        if (!is_array($result) || $result['RETURN_CODE'] != '000') {
//            error_log('send win user info to ankechen fail, openid:' . $openid);
//        }

        return array('result' => 1);//更新成功。
    }
}

/**
 * 验证用户是否参加过活动。
 * 如果没有进行抽奖
 *
 * @param $openid
 * @param $config
 * @return array
 */
function check_user($openid, $config)
{
    $type = intval(trim($_POST['type']));

    if (!preg_match(TYPE_REG, $type)) {
        return array('result' => 0);
    }

    $mobile = trim($_POST['mobile']);

    if (!preg_match(MOBILE_REG, $mobile)) {
        return array('result' => 0);
    }

    $captcha = trim($_POST['captcha']);

    if (mb_strlen($captcha) == 0) {
        return array('result' => 0);
    }

    $myCaptcha = trim($_SESSION['captcha']);

//    $myCaptcha = '1111';

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

    $status = null;

    if ($stmt = $mysqli->prepare('SELECT mobile,status FROM mnb_user WHERE openid = ?')) {
        $stmt->bind_param('s', $openid);
        $stmt->execute();
        $stmt->bind_result($myUserMobile, $myUserStatus);

        while ($stmt->fetch()) {
            $userMobile = $myUserMobile;

            $status = $myUserStatus;
        }
        $stmt->close();
    }

    $mysqli->close();

    if (mb_strlen($userMobile) > 0) {//写过手机号码了

        if ($status == 1) {
            return array('result' => 3);//未中奖
        } else {
            return array('result' => 2);//已经中过了。
        }
    } else {
        return lottery($openid, $mobile, $config, $type);//1:中奖；3：未中奖。
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
 * 抽奖。
 *
 * @param $openid
 * @param $mobile
 * @param $config
 * @return array
 */
function lottery($openid, $mobile, $config, $type)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $couponCount = 0;

    if ($stmt = $mysqli->prepare('SELECT count FROM mnb_coupons WHERE coupon_type = ?')//某一种类的未使用奖品数量
    ) {
        $stmt->bind_param('i', $type);
        $stmt->execute();
        $stmt->bind_result($myCount);

        while ($stmt->fetch()) {

            $couponCount = $myCount;
        }

        $stmt->close();
    }

    if (is_null($couponCount)) {
        $couponCount = 0;
    }

    $winCount = 0;

    if ($stmt = $mysqli->prepare('SELECT COUNT(*) AS win_user FROM win_users WHERE type = ?')//某一种类的中奖人数
    ) {
        $stmt->bind_param('i', $type);
        $stmt->execute();
        $stmt->bind_result($myWinCount);

        while ($stmt->fetch()) {
            $winCount = $myWinCount;
        }

        $stmt->close();
    }

    $mysqli->close();

    if ($couponCount > $winCount && mt_rand(1, 100) > 100) {
        return win_user($openid, $mobile, $config, $type);//中奖。
    } else {
        return fail_user($openid, $mobile, $config, $type);//未中奖。
    }
}

/**
 * 保存中奖用户。
 *
 * @param $openid
 * @param $mobile
 * @param $config
 * @param $type
 * @return array
 */
function win_user($openid, $mobile, $config, $type)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $count = 0;

    $myStatus = 2;

    if ($stmt = $mysqli->prepare('UPDATE mnb_user SET mobile = ?,status = ?,type = ? WHERE openid = ?')
    ) {
        $stmt->bind_param('siis', $mobile, $myStatus, $type, $openid);
        $stmt->execute();

        $count = $stmt->affected_rows;

        $stmt->close();
    }

    $winCount = 0;

    $time = date('Y-m-d H:i:s', time());

    if ($stmt = $mysqli->prepare('INSERT INTO win_users(openid,mobile,created_at,type) VALUES(?, ?, ?, ?)')
    ) {
        $stmt->bind_param('sssi', $openid, $mobile, $time, $type);
        $stmt->execute();

        $winCount = $stmt->affected_rows;

        $stmt->close();
    }

    $mysqli->close();

//    $prizeName = null;
//
//    switch ($type) {
//        case 1:
//            $prizeName = '马爹利名士双杯礼盒';
//            break;
//        case 2:
//            $prizeName = '点烟器酒伴套餐';
//            break;
//        case 3:
//            $prizeName = '手机壳';
//            break;
//        case 4:
//            $prizeName = '50元微店优惠券';
//            break;
//        case 5:
//            $prizeName = 'NB酒壶礼盒';
//            break;
//    }

    if (($count < 1) || ($winCount < 1)) {
        return array('result' => 0);//领取失败。
    } else {
//        发送数据到安客臣。
//        $timestamp = date('Y-m-d H:i:s.B');
//        $arrayContent = array(
//            'prize_type' => $type,
//            'prize_name' => $prizeName
//        );
//        $result = post_curl(BEHAVIOR_POST_URL,
//            array(
//                'credential_type' => 'openId',
//                'credentialID' => $openid,
//                'timestamp' => $timestamp,
//                'behavior_code' => 'bhv_LD_002',
//                'behavior_content' => $arrayContent
//            ));
//
//        if (!is_array($result) || $result['RETURN_CODE'] != '000') {
//            error_log('send win user info to ankechen fail, openid:' . $openid);
//        }

        return array('result' => 1);//领取成功。
    }
}

/**
 * 保存未中奖用户。
 *
 * @param $openid
 * @param $mobile
 * @param $config
 * @param $type
 * @return array
 */
function fail_user($openid, $mobile, $config, $type)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $count = 0;

    $myStatus = 1;

    if ($stmt = $mysqli->prepare('UPDATE mnb_user SET mobile = ?,status = ?,type = ?  WHERE openid = ?')
    ) {
        $stmt->bind_param('siis', $mobile, $myStatus, $type, $openid);
        $stmt->execute();

        $count = $stmt->affected_rows;

        $stmt->close();
    }

    $mysqli->close();

    if ($count < 1) {
        return array('result' => 0);//未中奖保存失败。
    } else {
//        发送数据到安客臣。
//        $timestamp = date('Y-m-d H:i:s.B');
//        $arrayContent = array(
//            'prize_type' => $type,
//            'prize_name' => '25元微店优惠券',
//        );
//        $result = post_curl(BEHAVIOR_POST_URL,
//            array(
//                'credential_type' => 'openId',
//                'credentialID' => $openid,
//                'timestamp' => $timestamp,
//                'behavior_code' => 'bhv_LD_002',
//                'behavior_content' => $arrayContent
//            ));
//
//        error_log('send not win user to ankechen, openid:' . $openid . ',result:' . json_encode($result));
//
//        if (!is_array($result) || $result['RETURN_CODE'] != '000') {
//            error_log('send not win user info to ankechen fail, openid:' . $openid);
//        }

        return array('result' => 3);//未中奖保存成功。
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

    $data = array_merge(compact('ts', 'sign', 'source_name'), $data);

    error_log('send post_curl to ankechen, data:' . json_encode($data));

    $jsonData = json_encode($data);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');
    curl_setopt($ch, CURLOPT_SSLVERSION, 6);
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


//$.ajax({
//type: 'POST',
//url: 'api/index.php',
//data: {action: 'create_user', openid: 'test_openid'},
//dataType: 'json',
//success: function(data){
//    console.log(data);
//}
//});


//$.ajax({
//type: 'POST',
//url: 'api/index.php',
//data: {action: 'save_quiz', openid: 'test_openid',quiz_id:'7',quiz_answer:'2-3-4'},
//dataType: 'json',
//success: function(data){
//    console.log(data);
//}
//});

//$.ajax({
//type: 'POST',
//url: 'api/index.php',
//data: {action: 'update_user', openid: 'test_openid',user_name:'dsfs',address:'sfdsfsdf'},
//dataType: 'json',
//success: function(data){
//    console.log(data);
//}
//});
