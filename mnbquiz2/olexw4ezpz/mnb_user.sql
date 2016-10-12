DROP TABLE IF EXISTS `new_mnb_user`;
CREATE TABLE `new_mnb_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(50) NOT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `type` int(11) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `utm_source` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mmb_user_openid_uindex` (`openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
