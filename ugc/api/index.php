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

define('TOPIC_ID_REG', '/^[1-3]-[1-3]$/');

define('LOTTERY_RATE', 80);

switch ($action) {
    case 'create_user'://step-2创建用户
        $result = create_user($openid, $config);
        break;
    case 'add_user':
        $result = add_user($openid, $config);
        break;
    case 'get_captcha':
        $result = captcha();
        break;
    case 'add_topic'://step-4存topic
        $result = add_topic($openid, $config);
        break;
    case 'user_score'://step-5返回分数
        $result = user_score($openid, $config);
        break;
    case 'score_list'://step-7排行榜
        $result = score_list($config);
        break;
    case 'mobile_status'://查看中奖情况
        $result = mobile_status($openid, $config);
        break;
    case 'save_score'://保存分数
        $result = save_score($openid, $config);
        break;
    default:
        break;
}

echo json_encode($result);

exit();

/**
 * 判断是否抽过奖
 * 1：未抽过奖
 * 2：抽过奖
 *
 * @param $openid
 * @param $config
 * @return array
 */
function mobile_status($openid, $config)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $userMobile = null;

    $status = null;

    if ($stmt = $mysqli->prepare('SELECT mobile FROM ugc_users WHERE openid = ?')) {
        $stmt->bind_param('s', $openid);
        $stmt->execute();
        $stmt->bind_result($myUserMobile);

        while ($stmt->fetch()) {
            $userMobile = $myUserMobile;
        }
        $stmt->close();
    }

    $mysqli->close();

    if (mb_strlen($userMobile) > 0) {
        return array('result' => 2);
    }
    return array('result' => 1);//未抽过奖

}

/**
 * 保存分数
 *
 * @param $openid
 * @param $config
 * @return array
 */
function save_score($openid, $config)
{
    $score = intval(trim($_POST['score']));

    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $count = 0;

    if ($stmt = $mysqli->prepare('UPDATE ugc_users SET score = ? WHERE openid = ?')
    ) {
        $stmt->bind_param('is', $score, $openid);
        $stmt->execute();

        $count = $stmt->affected_rows;

        $stmt->close();
    }

    $mysqli->close();

    if ($count < 1) {
        return array('result' => 0);//失败。
    } else {
        return array('result' => 1);//成功。
    }
}

/**
 * 返回用户的分数
 *
 * @param $openid
 * @param $config
 * @return array
 */
function user_score($openid, $config)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $score = 0;

    $topicId = null;

    if ($stmt = $mysqli->prepare('SELECT topic_id FROM ugc_topics WHERE openid = ? ORDER BY created_at DESC LIMIT 1')
    ) {
        $stmt->bind_param('s', $openid);
        $stmt->execute();
        $stmt->bind_result($myTopicId);

        while ($stmt->fetch()) {
            $topicId = $myTopicId;
        }

        $stmt->close();
    }
    $headUrl = null;
    $nickname = null;
    if ($stmt = $mysqli->prepare('SELECT score,head_url,nickname FROM ugc_users WHERE openid =?')
    ) {
        $stmt->bind_param('s', $openid);
        $stmt->execute();
        $stmt->bind_result($myScore, $myHeadUrl, $myNickname);

        while ($stmt->fetch()) {
            $score = $myScore;
            $headUrl = $myHeadUrl;
            $nickname = $myNickname;
        }

        $stmt->close();
    }

    $mysqli->close();

    return array('result' => 1, 'score' => $score, 'topic_id' => $topicId, 'head_url' => $headUrl, 'nickname' => $nickname);
}

/**
 * 返回排行榜前三名数据
 *
 * @param $config
 * @return array
 */
function score_list($config)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $nickname = null;

    $headUrl = null;

    $result = 0;

    if ($stmt = $mysqli->prepare('SELECT nickname,head_url,score FROM ugc_users ORDER BY score DESC LIMIT 5')
    ) {
        $stmt->execute();
        $stmt->bind_result($myNickname, $myHeadUrl, $myScore);

        $result = array();

        while ($stmt->fetch()) {
            $arr = array(
                'nickname' => $myNickname,
                'head_url' => $myHeadUrl,
                'score' => $myScore,
            );
            array_push($result, $arr);
        }

        $stmt->close();
    }

    $mysqli->close();

    return array('data' => $result);
}

/**
 * 验证用户是否参加过活动。
 *
 * @param $openid
 * @param $config
 * @return array
 */
function add_user($openid, $config)
{
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

    $status = null;

    if ($stmt = $mysqli->prepare('SELECT mobile,status FROM ugc_users WHERE openid = ?')) {
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

    if (mb_strlen($userMobile) > 0) {
        if ($status == 1) {
            return array('result' => 3);//未中奖
        } else {
            return array('result' => 2);//已经中过了。
        }
    } else {
        return lottery($openid, $mobile, $config);//1:中奖；3：未中奖。
    }
}

/**
 * 创建用户信息--STEP 2
 *
 * @param $openid
 * @param $config
 * @return array
 */
function create_user($openid, $config)
{
    $headUrl = trim($_POST['head_url']);

    if (mb_strlen($headUrl) == 0 || mb_strlen($headUrl) > 150) {
        return array('result' => 0);
    }

    $nickname = trim($_POST['nickname']);

    if (mb_strlen($nickname) == 0 || mb_strlen($nickname) > 50) {
        return array('result' => 0);
    }

    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $count = 0;

    $openidCount = 0;

    if ($stmt = $mysqli->prepare('SELECT COUNT(*) FROM ugc_users WHERE openid = ?')) {
        $stmt->bind_param('s', $openid);
        $stmt->execute();

        $stmt->bind_result($myCount);

        while ($stmt->fetch()) {
            $openidCount = $myCount;
        }
    }
    $selectScore['score'] = 0;

    if ($openidCount) {//openid已经存在
        $selectScore = select_score($openid, $config);
        return array('result' => 1, 'score' => $selectScore['score']);//创建成功。
    }

    if ($stmt = $mysqli->prepare('INSERT INTO ugc_users(openid, head_url, nickname) VALUES(?, ?, ?)')
    ) {
        $stmt->bind_param('sss', $openid, $headUrl, $nickname);
        $stmt->execute();

        $count = $stmt->affected_rows;

        $stmt->close();
    }

    $mysqli->close();


    if ($count < 1) {
        return array('result' => 0);//创建失败。
    } else {
        $result = post_curl(DATA_POST_URL,
            array(
                'openId' => $openid,
                'nickname' => $nickname,
            ));

        if (!is_array($result) || $result['RETURN_CODE'] != '000') {
            error_log('send campaign user info to ankechen fail, openid:' . $openid);
        }

        return array('result' => 1, 'score' => $selectScore['score']);//创建成功。
    }
}

function select_score($openid, $config)
{

    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $score = 0;

    if ($stmt = $mysqli->prepare('SELECT score FROM ugc_users WHERE openid = ?')) {
        $stmt->bind_param('s', $openid);
        $stmt->execute();
        $stmt->bind_result($myScore);

        while ($stmt->fetch()) {
            $score = $myScore;
        }
    }

    $mysqli->close();

    return array('score' => $score);//保存成功。

}

/**
 * 处理用户参加活动数据。Step-4
 *
 * @param $openid
 * @param $config
 * @return array
 */
function add_topic($openid, $config)
{
    $topicId = trim($_POST['topic_id']);

    if (!preg_match(TOPIC_ID_REG, $topicId)) {
        return array('result' => 0);
    }

    $saveResult = save_campaign($openid, $topicId, $config);

//    $saveScore = update_score($openid, $score, $config);//保存分数

    return array('result' => $saveResult['result']);
}

/**
 * 更新分数
 *
 * @param $openid
 * @param $score
 * @param $config
 * @return array
 */
function update_score($openid, $score, $config)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $count = 0;

    if ($stmt = $mysqli->prepare('UPDATE ugc_users SET score = ? WHERE openid = ?')
    ) {
        $stmt->bind_param('is', $score, $openid);
        $stmt->execute();

        $count = $stmt->affected_rows;

        $stmt->close();
    }

    $mysqli->close();

    if ($count < 1) {
        return array('result' => 0);//失败。
    } else {
        return array('result' => 1);//成功。
    }
}


/**
 * 保存用户参加活动数据。
 *
 * @param $openid
 * @param $topicId
 * @param $config
 * @return array
 */
function save_campaign($openid, $topicId, $config)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $time = date('Y-m-d H:i:s', time());

    $count = 0;

    if ($stmt = $mysqli->prepare('INSERT INTO ugc_topics(openid, created_at, topic_id) VALUES(?, ?, ?)')
    ) {
        $stmt->bind_param('sss', $openid, $time, $topicId);
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
 * 抽奖。
 *
 * @param $openid
 * @param $mobile
 * @param $config
 * @return array
 */
function lottery($openid, $mobile, $config)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $count = 0;

    if ($stmt = $mysqli->prepare('SELECT COUNT(*) AS ' . 'total_count FROM ugc_users')
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
        return win_user($openid, $mobile, $config);//中奖。
    } else {
        return fail_user($openid, $mobile, $config);//未中奖。
    }
}

/**
 * 保存中奖用户。
 *
 * @param $openid
 * @param $mobile
 * @param $config
 * @return array
 */
function win_user($openid, $mobile, $config)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $count = 0;

    $myStatus = 2;

    if ($stmt = $mysqli->prepare('UPDATE ugc_users SET mobile = ?,status = ? WHERE openid = ?')
    ) {
        $stmt->bind_param('sis', $mobile, $myStatus, $openid);
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
 * @return array
 */
function fail_user($openid, $mobile, $config)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $count = 0;

    $myStatus = 1;

    if ($stmt = $mysqli->prepare('UPDATE ugc_users SET mobile = ?,status = ? WHERE openid = ?')
    ) {
        $stmt->bind_param('sis', $mobile, $myStatus, $openid);
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
            array(
                'cellphone' => $mobile,
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
