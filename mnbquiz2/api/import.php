<?php
/**
 * @
 * @Description:
 * @Copyright (C) 2011 helloweba.com,All Rights Reserved.
 * -----------------------------------------------------------------------------
 * @author: Liurenfei (lrfbeyond@163.com)
 * @Create: 2012-5-1
 * @Modify:
 */
include_once("connect.php");

$action = $_GET['action'];

if ($action == 'import') { //导入XLS
    include_once("excel/reader.php");

    error_log('exl:' . $_FILES['file']['tmp_name']);
    $tmp = $_FILES['file']['tmp_name'];
    if (empty ($tmp)) {
        echo '请选择要导入的Excel文件！';
        exit;
    }

    $save_path = "xls/";
    $file_name = $save_path . date('Ymdhis') . ".xls";
    if (copy($tmp, $file_name)) {
        $xls = new Spreadsheet_Excel_Reader();
        $xls->setOutputEncoding('utf-8');
        $xls->read($file_name);

        for ($i = 2; $i <= $xls->sheets[0]['numRows']; $i++) {
            $coupon_type = $xls->sheets[0]['cells'][$i][1];
            $count = $xls->sheets[0]['cells'][$i][2];
            $time = date('Y-m-d H:i:s', time());
            $data_values .= "('$coupon_type','$count','$time'),";
        }
        $data_values = substr($data_values, 0, -1); //去掉最后一个逗号
        $query = mysql_query("insert into mnb_coupons (coupon_type,count,created_at) values $data_values");//批量插入数据表中
        if ($query) {
            echo '导入成功！';
        } else {
            echo '导入失败！';
        }
    }
} elseif ($action == 'export') { //导出XLS
    $result = mysql_query("select * from mnb_coupons");
    $str = "coupon_type\tcount\t\n";
    $str = iconv('utf-8', 'gb2312', $str);
    while ($row = mysql_fetch_array($result)) {
        $coupon_type = iconv('utf-8', 'gb2312', $row['coupon_type']);
        $count = iconv('utf-8', 'gb2312', $row['count']);
        $str .= $coupon_type . "\t" . $count . "\t\n";
    }
    $filename = date('Ymd') . '_coupon.xls';
    exportExcel($filename, $str);
} elseif ($action == 'win_user') { //导出中奖用户XLS
    $result = mysql_query("select * from win_users ORDER by created_at");
    $str = "openid\tmobile\t姓名\t地址\t类型\t中奖时间\n";
    $str = iconv('utf-8', 'gb2312', $str);
    while ($row = mysql_fetch_array($result)) {
        $openid = iconv('utf-8', 'gb2312', $row['openid']);
        $mobile = iconv('utf-8', 'gb2312', $row['mobile']);
        $user_name = iconv('utf-8', 'gb2312', $row['user_name']);
        $address = iconv('utf-8', 'gb2312', $row['address']);
        $type = iconv('utf-8', 'gb2312', $row['type']);
        $time = iconv('utf-8', 'gb2312', $row['created_at']);
        $str .= $openid . "\t" . $mobile . "\t" . $user_name . "\t" . $address . "\t" . $type . "\t" . $time . "\t\n";
    }
    $filename = date('Ymd') . '_win_user.xls';
    exportExcel($filename, $str);
}


$usersMsg = trim($_POST['actions']);
if ($usersMsg == 'coupon_count') { //用券情况
    $user_win_array = [];
    for ($i = 0; $i < 5; $i++) {
        $type = $i + 1;
        $result = mysql_query("select COUNT(*) as count from win_users WHERE type = $type");
        while ($array = mysql_fetch_array($result)) {
            $count = iconv('utf-8', 'gb2312', $array['count']);
            $user_win_array[$i] = $count;
        }
    }
    $coupon_array = [];
    for ($i = 0; $i < 5; $i++) {
        $type = $i + 1;
        $result = mysql_query("select count from mnb_coupons WHERE coupon_type = $type");
        while ($array = mysql_fetch_array($result)) {
            $count = iconv('utf-8', 'gb2312', $array['count']);
            $coupon_array[$i] = $count;
        }
    }
    echo json_encode(array('user_win_array' => $user_win_array, 'coupon_array' => $coupon_array));
}


function exportExcel($filename, $content)
{
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/vnd.ms-execl");
    header("Content-Type: application/force-download");
    header("Content-Type: application/download");
    header("Content-Disposition: attachment; filename=" . $filename);
    header("Content-Transfer-Encoding: binary");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo $content;
}

?>
