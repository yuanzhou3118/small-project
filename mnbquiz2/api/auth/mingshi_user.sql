DROP TABLE IF EXISTS `mingshi_user`;
CREATE TABLE `mingshi_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `openid` varchar(100) DEFAULT NULL,
  `subscribe_time` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `unionid` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mingshi_user_openid_uindex` (`openid`)
) ENGINE=InnoDB AUTO_INCREMENT=384629 DEFAULT CHARSET=utf8
