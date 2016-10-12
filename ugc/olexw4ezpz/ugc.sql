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
DROP TABLE IF EXISTS `ugc_topics`;
CREATE TABLE `ugc_topics` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `openid` varchar(100) NOT NULL,
  `topic_id` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of fail_users
-- ----------------------------

-- ----------------------------
-- Table structure for `win_users`
-- ----------------------------
DROP TABLE IF EXISTS `ugc_users`;
CREATE TABLE `ugc_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `openid` varchar(100) NOT NULL,
  `head_url` varchar(150) NOT NULL,
  `nickname` varchar(50) NOT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `score` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_openid_uindex` (`openid`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8

-- ----------------------------
-- Records of win_users
-- ----------------------------
