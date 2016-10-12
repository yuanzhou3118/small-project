/*
Navicat MySQL Data Transfer

Source Server         : 111
Source Server Version : 50621
Source Host           : localhost:3306
Source Database       : quiz_630

Target Server Type    : MYSQL
Target Server Version : 50621
File Encoding         : 65001

Date: 2016-06-30 14:41:14
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `fail_users`
-- ----------------------------
DROP TABLE IF EXISTS `fail_users`;
CREATE TABLE `fail_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mobile` varchar(20) NOT NULL,
  `openid` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_occasion` int(10) unsigned NOT NULL,
  `user_frequency` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of fail_users
-- ----------------------------

-- ----------------------------
-- Table structure for `win_users`
-- ----------------------------
DROP TABLE IF EXISTS `win_users`;
CREATE TABLE `win_users` (
  `openid` varchar(100) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_occasion` int(10) unsigned NOT NULL,
  `user_frequency` int(10) unsigned NOT NULL,
  PRIMARY KEY (`openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of win_users
-- ----------------------------
