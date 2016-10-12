/*
Navicat MySQL Data Transfer

Source Server         : 111
Source Server Version : 50621
Source Host           : localhost:3306
Source Database       : invitation

Target Server Type    : MYSQL
Target Server Version : 50621
File Encoding         : 65001

Date: 2016-07-05 11:18:22
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `o2o_campaign_users`
-- ----------------------------
DROP TABLE IF EXISTS `o2o_campaign_users`;
CREATE TABLE `o2o_campaign_users` (
  `mobile` varchar(20) NOT NULL,
  `openid` varchar(100) NOT NULL,
  `user_name` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `auth_code` varchar(32) NOT NULL,
  PRIMARY KEY (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of o2o_campaign_users
-- ----------------------------
