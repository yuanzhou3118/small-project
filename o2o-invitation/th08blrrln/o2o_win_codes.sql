/*
Navicat MySQL Data Transfer

Source Server         : 111
Source Server Version : 50621
Source Host           : localhost:3306
Source Database       : invitation

Target Server Type    : MYSQL
Target Server Version : 50621
File Encoding         : 65001

Date: 2016-07-05 11:18:31
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `o2o_win_codes`
-- ----------------------------
DROP TABLE IF EXISTS `o2o_win_codes`;
CREATE TABLE `o2o_win_codes` (
  `auth_code` varchar(10) NOT NULL,
  PRIMARY KEY (`auth_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of o2o_win_codes
-- ----------------------------
