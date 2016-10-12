<?php
header('content-type:text/html;charset=utf-8');
$config = require_once '../config.php';

define('SOURCE_NAME', '8e2902a7b2dcb72717c39fe53f64afd9');

define('DATA_POST_URL', 'https://prcws.acxiom.com.cn/PRC/rest/customer/dataCollect');
//define('DATA_POST_URL', 'https://uta01.acxiom.com.cn/PRC/rest/customer/dataCollect');

define('BEHAVIOR_POST_URL', 'https://prcws.acxiom.com.cn/PRC/rest/customer/BehaviorCollect');

//define('SURVEY_POST_URL', 'https://uat01.acxiom.com.cn/PRC/rest/customer/SurveyCollect');
define('SURVEY_POST_URL', 'https://prcws.acxiom.com.cn/PRC/rest/customer/SurveyCollect');

$data = getOpenidUnionid($config);
var_dump($data);

foreach ($data['data'] as $item) {//替换openid
    $result = match($item['openid'], $config);
//    var_dump($item['openid']);
    $result = updateOpenid($result, $item['openid'], $config);

    $failCount = 0;
    if($result == 0){
        $failCount++;
    }
}
echo '失败的总数是：'.$failCount;

$result = getUser($config);

echo $result;


//更新openid
function updateOpenid($newOpenid, $oldOpenid, $config)
{

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
        return array('result' => 0);//领取失败。
    } else {
        return array('result' => 1);//领取成功。
    }
}

//获取名士订阅号的openid，（unionid列表）
function getOpenidUnionid($config)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return array('result' => 0);
    }

    $mysqli->query('SET NAMES UTF8');

    $result = 0;

    if ($stmt = $mysqli->prepare('SELECT openid FROM mnb_user WHERE openid LIKE "oUDu5%"')
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
function match($unionId, $config)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return 2;
    }

    $mysqli->query('SET NAMES UTF8');

    $openid = null;

    if ($stmt = $mysqli->prepare('SELECT openid FROM mingshi_user WHERE unionid = ?')
    ) {
        $stmt->bind_param('s', $unionId);
        $stmt->execute();
        $stmt->bind_result($myOpenid);

        while ($stmt->fetch()) {
            $openid = $myOpenid;
        }

        $stmt->close();
    }

    $mysqli->close();

    return $openid;
}

//获取数据发送安客诚
function getUser($config)
{
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    if (mysqli_connect_errno()) {
        error_log('Failed to connect to MySQL,error:' . mysqli_connect_error());

        return 2;
    }

    $mysqli->query('SET NAMES UTF8');

    $user = array();

    $time = date('Y-m-d 00:00:00');

    if ($stmt = $mysqli->prepare('SELECT openid,type,created_at,status FROM mnb_user WHERE created_at > ?')
    ) {
        $stmt->bind_param('s', $time);
        $stmt->execute();
        $stmt->bind_result($myOpenid, $type, $created_at, $status);

        while ($stmt->fetch()) {
            $arr = array(
                'openid' => $myOpenid,
                'type' => $type,
                'mobile' => $type,
                'created_at' => $created_at,
                'status' => $status
            );
            array_push($user, $arr);
        }

        $stmt->close();
    }

    $quiz = array();

    if ($stmt = $mysqli->prepare('SELECT id,openid,quiz_id,quiz_answer,created_at FROM mnb_quiz WHERE created_at > ?')
    ) {
        $stmt->bind_param('s', $time);
        $stmt->execute();
        $stmt->bind_result($id, $myOpenid, $quizId, $quizAnswer, $created_at);

        while ($stmt->fetch()) {
            $arr = array(
                'id' => $id,
                'openid' => $myOpenid,
                'quiz_id' => $quizId,
                'quiz_answer' => $quizAnswer,
                'created_at' => $created_at,
            );
            array_push($quiz, $arr);
        }

        $stmt->close();
    }

    $win_user = array();

    if ($stmt = $mysqli->prepare('SELECT openid,user_name,address,mobile,created_at FROM win_users WHERE created_at > ?')
    ) {
        $stmt->bind_param('s', $time);
        $stmt->execute();
        $stmt->bind_result($myOpenid, $userName, $address, $mobile, $created_at);

        while ($stmt->fetch()) {
            $arr = array(
                'openid' => $myOpenid,
                'username' => $userName,
                'address' => $address,
                'mobile' => $mobile,
                'created_at' => $created_at,
            );
            array_push($win_user, $arr);
        }

        $stmt->close();
    }

    $mysqli->close();

    return sendAnkechen($user, $quiz, $win_user);
}


function sendAnkechen(array $data, array $quiz, array $win_user)
{

    foreach ($data as $item) {//mnb_user信息
        $arrayContent = null;
        $prizeName = null;
        if ($item['status'] == 1) {
            $prizeName = '25元微店优惠券';
        }
        else if ($item['status'] == 2) {
            switch ($item['type']) {
                case 1:
                    $prizeName = '马爹利名士双杯礼盒';
                    break;
                case 2:
                    $prizeName = '点烟器酒伴套餐';
                    break;
                case 3:
                    $prizeName = '手机壳';
                    break;
                case 4:
                    $prizeName = '50元微店优惠券';
                    break;
                case 5:
                    $prizeName = 'NB酒壶礼盒';
                    break;
            }
        }
        $arrayContent = array(
            'prize_type' => $item['type'],
            'prize_name' => $prizeName,
        );
        $result = post_curl(BEHAVIOR_POST_URL,
            array(
                'credential_type' => 'openId',
                'credentialID' => $item['openid'],
                'timestamp' => $item['created_at'] . '.000',
                'behavior_code' => 'bhv_LD_002',
                'behavior_content' => $arrayContent
            ), $item['created_at']);

        error_log('send user to ankechen, openid:' . $item['openid'] . ',result:' . json_encode($result));

        if (!is_array($result) || $result['RETURN_CODE'] != '000') {
            error_log('send not win user info to ankechen fail, openid:' . $item['openid']);
        }

        $dataPost = post_curl(DATA_POST_URL,
            array(
                'openId' => $item['openid'],
            ), $item['created_at']);
        error_log('send campaign user info to ankechen, openid:' . $item['openid'] . ',result:' . json_encode($dataPost));
        if (!is_array($result) || $result['RETURN_CODE'] != '000') {
            error_log('send create user info to ankechen fail, openid:' . $item['openid']);
        }

    }

    echo 'mnb_user发送完毕<br>';



    $runtime = microtime(true);

    foreach ($quiz as $item) {
        $id = $item['id'];
        if (mb_strlen($id) < 4) {
            switch (mb_strlen($id)) {
                case 1:
                    $id = '000' . $id;
                    break;
                case 2:
                    $id = '00' . $id;
                    break;
                case 3:
                    $id = '0' . $id;
                    break;
                default:
                    break;

            }
        }

        $serialTime = date('YmdHi', time());

//        $runtime = microtime(true);

        $questionID = 80 + $item['quiz_id'];
        //发送数据到安客臣。
        $result = post_curl(SURVEY_POST_URL,
            array(
                'credential_type' => 'openId',
                'credentialID' => $item['openid'],
                'timestamp' => $item['created_at'] . '.000',
                'questionID' => $questionID,
                'answerID' => $item['quiz_answer'],
                'openAnswer' => '',
                'serialNumber' => $serialTime . $id,
                'surveyCode' => 'ques_Martell Noblige_001',
            ), $item['created_at']);


        error_log('send quiz info to ankechen, openid:' . $item['openid'] . ',result:' . json_encode($result));

        if (!is_array($result) || $result['RETURN_CODE'] != '000') {
            error_log('send quiz info to ankechen fail, openid:' . $item['openid']);
        }
    }

    error_log('time span for acxiom quiz info api,time:' . round(microtime(true) - $runtime, 4) . 's');

    echo 'quiz部分发送完毕<br>';

    foreach ($win_user as $item) {
        $result = post_curl(DATA_POST_URL,
            array(
                'cellphone' => $item['mobile'],
                'name' => $item['username'],
                'address' => $item['address'],
                'openId' => $item['openid'],
            ), $item['created_at']);

        error_log('send win user info to ankechen, openid:' . $item['openid'] . ',result:' . json_encode($result));

        if (!is_array($result) || $result['RETURN_CODE'] != '000') {
            error_log('send win user info to ankechen fail, openid:' . $item['openid'] . ',result:' . json_encode($result));
        }
    }
    echo 'win_user部分发送完毕<br>';

    return 1;
}

/**
 * 执行post请求。
 *
 * @param $url
 * @param array $data
 * @param $created_at
 * @return mixed
 */
function post_curl($url, array $data, $created_at)
{
    $ts = $created_at . '.000';

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

