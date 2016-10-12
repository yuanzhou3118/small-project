<?php

header('content-type:application/json;charset=utf-8');

session_start();

$action = trim($_POST['action']);

if (mb_strlen($action) == 0) {
    echo json_encode(array('result' => 0));

    exit();
}

$result = array('result' => 0);

switch ($action) {
    case 'check_auth':
        $result = check_auth();
        break;
    case 'add_user':
        $result = add_user();
        break;
    default:
        break;
}

echo json_encode($result);

exit();

/**
 * 验证用户是否网页授权。
 *
 * @return array
 */
function check_auth()
{
    $openid = trim($_SESSION['openid']);

    if (mb_strlen($openid) == 0) {
        $returnUrl = trim($_SERVER['HTTP_REFERER']);

        if (mb_strlen($returnUrl) == 0) {
            return array('result' => 0);
        }

        $_SESSION['return_url'] = $returnUrl;

        $config = require_once 'config.php';

        $authUrl = $config['wechat_auth_url'];

        $authUrl = str_replace('{0}', urlencode('http://' . $_SERVER['HTTP_HOST'] . '/cuba/api/auth.php'), $authUrl);

        $authUrl = str_replace('{1}', $config['wfpuser'], $authUrl);

        $authUrl = str_replace('{2}', hash('sha512', $config['wfpuser'] . $config['wfppwd']), $authUrl);

        $authUrl = str_replace('{3}', time(), $authUrl);

        return array('result' => 2, 'auth_url' => $authUrl);
    }

    return array('result' => 1);
}

/**
 * 提交用户数据到安客臣。
 *
 * @return array
 */
function add_user()
{
    $userFrom = trim($_POST['user_from']);

    if (!preg_match('/^(1|2)$/', $userFrom)) {
        return array('result' => 0);
    }

    $userFrom = intval($userFrom);

    $openid = trim($_SESSION['openid']);

    if (mb_strlen($openid) == 0) {
        return array('result' => 0);
    }

    $city = trim($_SESSION['city']);

    //提交数据到安客臣。
    $result = post_curl('https://prcws.acxiom.com.cn/PRC/rest/customer/dataCollect',
        get_source_name($userFrom),
        array(
            'city' => $city,
            'openId' => $openid
        ));

    if (!is_array($result) || $result['RETURN_CODE'] != '000') {
        error_log('send user info to ankechen fail, openid:' . $openid);
    }

    return array('result' => 1);
}

/**
 * 获取安客臣的source_name。
 *
 * @param $userFrom
 * @return string
 */
function get_source_name($userFrom)
{
    switch ($userFrom) {
        case 1:
            return '6990fdfd2a55a0fc74d3a12e6da90c29';//Havana Club Official WeChat
        case 2:
            return '9cd3cfcfff3180c28d0a7bf86f63779c';//Havana Club KOL WeChat
        default:
            return '';
    }
}

/**
 * 执行post请求。
 *
 * @param $url
 * @param $sourceName
 * @param array $data
 * @return mixed
 */
function post_curl($url, $sourceName, array $data)
{
    $ts = date('Y-m-d H:i:s.B');

    $sign = md5(md5("PRC" . $ts) . $ts);

    $source_name = $sourceName;

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
