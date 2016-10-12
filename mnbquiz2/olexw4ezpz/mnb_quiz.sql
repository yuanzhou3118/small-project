DROP TABLE IF EXISTS `new_mnb_quiz`;
CREATE TABLE `new_mnb_quiz` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `openid` varchar(50) NOT NULL,
  `quiz_id` int(11) unsigned NOT NULL,
  `quiz_answer` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `new_win_users`;
CREATE TABLE `new_win_users` (
  `openid` varchar(100) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NULL DEFAULT NULL,
  `type` int(11) DEFAULT '0',
  PRIMARY KEY (`openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;