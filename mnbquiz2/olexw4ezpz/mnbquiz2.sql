/*
Navicat MySQL Data Transfer

Source Server         : 111
Source Server Version : 50621
Source Host           : localhost:3306
Source Database       : ugc

Target Server Type    : MYSQL
Target Server Version : 50621
File Encoding         : 65001

Date: 2016-06-30 14:41:14
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `fail_users`
-- ----------------------------
DROP TABLE IF EXISTS `mnb_quiz`;
CREATE TABLE `mnb_quiz` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `openid` varchar(50) NOT NULL,
  `quiz_id` int(11) unsigned NOT NULL,
  `quiz_answer` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of fail_users
-- ----------------------------

-- ----------------------------
-- Table structure for `win_users`
-- ----------------------------
DROP TABLE IF EXISTS `mnb_user`;
CREATE TABLE `mnb_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `openid` varchar(50) NOT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `status` int(11) unsigned NOT NULL DEFAULT '0',
  `type` int(11) unsigned DEFAULT '0',
  `utm_source` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mmb_user_openid_uindex` (`openid`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of win_users
-- ----------------------------
DROP TABLE IF EXISTS `win_users`;
CREATE TABLE `win_users` (
  `openid` varchar(100) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NULL DEFAULT NULL,
  `type` int(11) DEFAULT '0',
  PRIMARY KEY (`openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `mnb_coupons`;
CREATE TABLE `mnb_coupons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `coupon_type` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `update_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `count` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8
