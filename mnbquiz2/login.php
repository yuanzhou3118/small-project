<?php
      /*
      *  前三个是需要配置的，根据自己需求填写正确的
      */
      session_start();
      $oauthUrl = "http://mnbq1pro.gypserver.com/wxAPI/?a=5spzSmcI0h";  //管理授权里面的授权网址  (*替换成自己的)
      $key = 'gyp2016'; // MD5 key值  授权里面对应的key值 (*替换成自己的)
      $rurl = urlencode('http://lftapl.dangdaimingshi.com/mnbquiz2/login.php');  //*回调地址，一般写当前地址
      $unixtime = strtotime("now"); //当前时间
      $token=$key.$unixtime;
      $token = md5($token); //生成MD5验证
	  $scope = 'snsapi_userinfo'; //微信授权 类型 弹出授权页面，可通过openid 拿到用户信息
	//			$scope = 'snsapi_base'; //微信授权 类型 不弹出授权页面，直接跳转，只能获取用户openid

	if(isset($_GET['rwurl'])){
		$_SESSION['rwurl']=$_GET['rwurl'];
	}

			if(isset($_GET['openid']) && isset($_GET['access_token'])){
					// 根据openid和access_token获取微信用户信息
					$get_user_url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $_GET['access_token'] . '&openid=' . $_GET['openid'] . '&lang=zh_CN';
       	$userInfoTemp = file_get_contents($get_user_url, false);
					$user["openid"] = $_GET['openid'];  //获取到的openid
					$user["token"] = $_GET['access_token'];  //获取到的 access_token
					$user["info"] = json_decode($userInfoTemp,true);  //获取用户信息
					$_SESSION['user']=$user;  //存用户信息到session,避免重复登录
					//echo json_encode(($_SESSION['user']))."<br>";  //打印用户信息
					//echo $_SESSION['rwurl']."<br>";
					//echo $user["info"]["openid"]."<br>";

					$rwurl=$_SESSION['rwurl'];
				  if (strstr($rwurl, "?")) {
				  		$rwurl2=$rwurl."&openid=".$user["info"]["openid"]."&unionid=".$user["info"]["unionid"]."&nickname=".$user["info"]["nickname"]."&headimgurl=".$user["info"]["headimgurl"]."&headimgurl=".$user["info"]["headimgurl"];

				  }else{
				  	$rwurl2=$rwurl."?openid=".$user["info"]["openid"]."&unionid=".$user["info"]["unionid"]."&nickname=".$user["info"]["nickname"]."&headimgurl=".$user["info"]["headimgurl"]."&headimgurl=".$user["info"]["headimgurl"];
				  }
				  header('Location: ' . $rwurl2, true, 301);

			}else{
					//通过重定向到我们的接口来获取微信openid和access_token
					$url=$oauthUrl."&mark=getwxoauth&key=$key&rurl=$rurl&scope=$scope&unixtime=".$unixtime."&token=".$token;
					header('Location: ' . $url, true, 301);
			}




	?>
