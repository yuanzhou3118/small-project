<?php
/**
 * Created by PhpStorm.
 * User: sopzhou
 * Date: 2016/8/31
 * Time: 12:12
 */

header("content-type:text/html;charset=utf-8");


$userId=$_POST["account"];
$password=$_POST["pwd"];
if($userId=="admin" && $password == 'Qaz123*()'){
//    header('Location: http://www.baidu.com/');
    echo '<script>alert("提交成功！");location.href="dashboard.html"</script>';
}else{
    echo "账户或密码错误";
}

