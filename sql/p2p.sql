/*
Navicat MySQL Data Transfer

Source Server         : 新测试
Source Server Version : 50173
Source Host           : 192.168.188.229:3306
Source Database       : p2p

Target Server Type    : MYSQL
Target Server Version : 50173
File Encoding         : 65001

Date: 2015-06-13 18:37:52
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for p2p_comment
-- ----------------------------
DROP TABLE IF EXISTS `p2p_comment`;
CREATE TABLE `p2p_comment` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment` varchar(255) DEFAULT NULL COMMENT '评论内容',
  `msg_id` int(20) DEFAULT NULL COMMENT '商品ID',
  `weixin` varchar(100) DEFAULT NULL,
  `add_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='客户评论';

-- ----------------------------
-- Records of p2p_comment
-- ----------------------------

-- ----------------------------
-- Table structure for p2p_log
-- ----------------------------
DROP TABLE IF EXISTS `p2p_log`;
CREATE TABLE `p2p_log` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `value` text,
  `time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of p2p_log
-- ----------------------------
INSERT INTO `p2p_log` VALUES ('1', '开始: <xml><ToUserName><![CDATA[gh_e9cf364a02cb]]></ToUserName>\n<FromUserName><![CDATA[o_HnUt7douuToB7k_sO6xMr7oi5Y]]></FromUserName>\n<CreateTime>1418701382</CreateTime>\n<MsgType><![CDATA[event]]></MsgType>\n<Event><![CDATA[unsubscribe]]></Event>\n<EventKey><![CDATA[]]></EventKey>\n</xml>', '2014-12-16 11:43:03');
INSERT INTO `p2p_log` VALUES ('2', '结束: <xml><ToUserName><![CDATA[o_HnUt7douuToB7k_sO6xMr7oi5Y]]></ToUserName><FromUserName><![CDATA[gh_e9cf364a02cb]]></FromUserName><CreateTime>1418701383</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[取消关注]]></Content><MsgId>14187013836481</MsgId></xml>', '2014-12-16 11:43:03');
INSERT INTO `p2p_log` VALUES ('3', '开始: <xml><ToUserName><![CDATA[gh_e9cf364a02cb]]></ToUserName>\n<FromUserName><![CDATA[o_HnUt7CiUQczh8C_f1tYbsPpSmo]]></FromUserName>\n<CreateTime>1418701439</CreateTime>\n<MsgType><![CDATA[text]]></MsgType>\n<Content><![CDATA[？]]></Content>\n<MsgId>6093276283494979440</MsgId>\n</xml>', '2014-12-16 11:44:00');
INSERT INTO `p2p_log` VALUES ('4', '结束: <xml><ToUserName><![CDATA[o_HnUt7CiUQczh8C_f1tYbsPpSmo]]></ToUserName><FromUserName><![CDATA[gh_e9cf364a02cb]]></FromUserName><CreateTime>1418701440</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[业务电话：18894141663\n网址：www.hcjrfw.com]]></Content><MsgId>14187014402145</MsgId></xml>', '2014-12-16 11:44:00');
INSERT INTO `p2p_log` VALUES ('5', '开始: <xml><ToUserName><![CDATA[gh_e9cf364a02cb]]></ToUserName>\n<FromUserName><![CDATA[o_HnUt7CiUQczh8C_f1tYbsPpSmo]]></FromUserName>\n<CreateTime>1418701446</CreateTime>\n<MsgType><![CDATA[event]]></MsgType>\n<Event><![CDATA[unsubscribe]]></Event>\n<EventKey><![CDATA[]]></EventKey>\n</xml>', '2014-12-16 11:44:07');
INSERT INTO `p2p_log` VALUES ('6', '结束: <xml><ToUserName><![CDATA[o_HnUt7CiUQczh8C_f1tYbsPpSmo]]></ToUserName><FromUserName><![CDATA[gh_e9cf364a02cb]]></FromUserName><CreateTime>1418701447</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[取消关注]]></Content><MsgId>14187014473482</MsgId></xml>', '2014-12-16 11:44:07');
INSERT INTO `p2p_log` VALUES ('7', '开始: <xml><ToUserName><![CDATA[gh_e9cf364a02cb]]></ToUserName>\n<FromUserName><![CDATA[o_HnUt7CiUQczh8C_f1tYbsPpSmo]]></FromUserName>\n<CreateTime>1418701471</CreateTime>\n<MsgType><![CDATA[event]]></MsgType>\n<Event><![CDATA[subscribe]]></Event>\n<EventKey><![CDATA[]]></EventKey>\n</xml>', '2014-12-16 11:44:32');
INSERT INTO `p2p_log` VALUES ('8', '结束: <xml><ToUserName><![CDATA[o_HnUt7CiUQczh8C_f1tYbsPpSmo]]></ToUserName><FromUserName><![CDATA[gh_e9cf364a02cb]]></FromUserName><CreateTime>1418701472</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[金袋子\n你身边的车贷专家！\n电话：18894141663\n网址：www.hcjrfw.com\n如需帮助请回复“?”]]></Content><MsgId>14187014723897</MsgId></xml>', '2014-12-16 11:44:32');
INSERT INTO `p2p_log` VALUES ('9', '开始: <xml><ToUserName><![CDATA[gh_e9cf364a02cb]]></ToUserName>\n<FromUserName><![CDATA[o_HnUt7CiUQczh8C_f1tYbsPpSmo]]></FromUserName>\n<CreateTime>1418704193</CreateTime>\n<MsgType><![CDATA[text]]></MsgType>\n<Content><![CDATA[我要吃饭]]></Content>\n<MsgId>6093288111834913660</MsgId>\n</xml>', '2014-12-16 12:29:54');
INSERT INTO `p2p_log` VALUES ('10', '结束: <xml><ToUserName><![CDATA[o_HnUt7CiUQczh8C_f1tYbsPpSmo]]></ToUserName><FromUserName><![CDATA[gh_e9cf364a02cb]]></FromUserName><CreateTime>1418704194</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[]]></Content><MsgId>14187041946583</MsgId></xml>', '2014-12-16 12:29:54');
INSERT INTO `p2p_log` VALUES ('11', '开始: <xml><ToUserName><![CDATA[gh_e9cf364a02cb]]></ToUserName>\n<FromUserName><![CDATA[o_HnUt7CiUQczh8C_f1tYbsPpSmo]]></FromUserName>\n<CreateTime>1418704211</CreateTime>\n<MsgType><![CDATA[text]]></MsgType>\n<Content><![CDATA[？]]></Content>\n<MsgId>6093288189144324997</MsgId>\n</xml>', '2014-12-16 12:30:12');
INSERT INTO `p2p_log` VALUES ('12', '结束: <xml><ToUserName><![CDATA[o_HnUt7CiUQczh8C_f1tYbsPpSmo]]></ToUserName><FromUserName><![CDATA[gh_e9cf364a02cb]]></FromUserName><CreateTime>1418704212</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[业务电话：18894141663\n网址：www.hcjrfw.com]]></Content><MsgId>14187042125372</MsgId></xml>', '2014-12-16 12:30:12');
INSERT INTO `p2p_log` VALUES ('13', '开始: <xml><ToUserName><![CDATA[gh_e9cf364a02cb]]></ToUserName>\n<FromUserName><![CDATA[o_HnUt7CiUQczh8C_f1tYbsPpSmo]]></FromUserName>\n<CreateTime>1418704223</CreateTime>\n<MsgType><![CDATA[text]]></MsgType>\n<Content><![CDATA[help]]></Content>\n<MsgId>6093288240683932556</MsgId>\n</xml>', '2014-12-16 12:30:24');
INSERT INTO `p2p_log` VALUES ('14', '结束: <xml><ToUserName><![CDATA[o_HnUt7CiUQczh8C_f1tYbsPpSmo]]></ToUserName><FromUserName><![CDATA[gh_e9cf364a02cb]]></FromUserName><CreateTime>1418704224</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[金袋子\n你身边的车贷专家！\n电话：18894141663\n网址：www.hcjrfw.com\n如需帮助请回复“?”]]></Content><MsgId>14187042242335</MsgId></xml>', '2014-12-16 12:30:24');
INSERT INTO `p2p_log` VALUES ('15', '开始: <xml><ToUserName><![CDATA[gh_e9cf364a02cb]]></ToUserName>\n<FromUserName><![CDATA[o_HnUt7CiUQczh8C_f1tYbsPpSmo]]></FromUserName>\n<CreateTime>1418704253</CreateTime>\n<MsgType><![CDATA[text]]></MsgType>\n<Content><![CDATA[tq:福州]]></Content>\n<MsgId>6093288369532951445</MsgId>\n</xml>', '2014-12-16 12:30:54');
INSERT INTO `p2p_log` VALUES ('16', '结束: <xml><ToUserName><![CDATA[o_HnUt7CiUQczh8C_f1tYbsPpSmo]]></ToUserName><FromUserName><![CDATA[gh_e9cf364a02cb]]></FromUserName><CreateTime>1418704254</CreateTime><MsgType><![CDATA[news]]></MsgType><ArticleCount>4</ArticleCount><Articles><item><Title><![CDATA[周二 12月16日 (实时：15℃)\n多云,微风,15 ~ 4℃]]></Title><PicUrl><![CDATA[http://weiexpress.sinaapp.com/images/weather.jpg]]></PicUrl><Url><![CDATA[]]></Url></item><item><Title><![CDATA[周三\n多云,微风,11 ~ 5℃]]></Title><PicUrl><![CDATA[http://api.map.baidu.com/images/weather/day/duoyun.png]]></PicUrl><Url><![CDATA[]]></Url></item><item><Title><![CDATA[周四\n阴,微风,11 ~ 6℃]]></Title><PicUrl><![CDATA[http://api.map.baidu.com/images/weather/day/yin.png]]></PicUrl><Url><![CDATA[]]></Url></item><item><Title><![CDATA[周五\n阴,微风,14 ~ 10℃]]></Title><PicUrl><![CDATA[http://api.map.baidu.com/images/weather/day/yin.png]]></PicUrl><Url><![CDATA[]]></Url></item></Articles></xml>', '2014-12-16 12:30:54');

-- ----------------------------
-- Table structure for p2p_user
-- ----------------------------
DROP TABLE IF EXISTS `p2p_user`;
CREATE TABLE `p2p_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `weixin` varchar(100) DEFAULT NULL,
  `name` varchar(10) DEFAULT NULL,
  `qq` varchar(12) DEFAULT NULL,
  `adder` varchar(400) DEFAULT NULL,
  `localtionX` varchar(20) DEFAULT NULL,
  `localtionY` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `shengri` date DEFAULT NULL,
  `readImgId` varchar(20) DEFAULT NULL,
  `sex` enum('female','male') DEFAULT 'male',
  `jifen` varchar(20) DEFAULT '0' COMMENT '积分',
  `vip` varchar(10) DEFAULT NULL COMMENT '会员卡',
  `pass` varchar(20) DEFAULT NULL COMMENT '密码',
  `area` varchar(20) DEFAULT NULL COMMENT '省份城市',
  `fakeid` varchar(40) DEFAULT NULL COMMENT '微信客户ID',
  `time` date DEFAULT NULL,
  `status` enum('1','0') DEFAULT '1',
  `locationNum` int(10) DEFAULT '3' COMMENT 'gps定位授权数',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of p2p_user
-- ----------------------------
INSERT INTO `p2p_user` VALUES ('1', 'o_HnUt7CiUQczh8C_f1tYbsPpSmo', '辉', null, null, null, null, null, null, '0', 'male', '0', null, null, null, '1143123239', '2014-12-16', '1', '3');
INSERT INTO `p2p_user` VALUES ('2', 'o_HnUt2zJ42G8nczgA9Ir89tg6NM', '陈代泾', null, null, null, null, null, null, '0', 'male', '0', null, null, null, '456995115', '2014-12-16', '1', '3');
INSERT INTO `p2p_user` VALUES ('3', 'o_HnUt7douuToB7k_sO6xMr7oi5Y', '刘晨辉', '8171455', '东方路31号', null, null, '18894141663', '1982-08-15', '0', 'male', '0', null, '31415926', '江西省-吉安市-万安县', '160011160', '2014-12-16', '1', '3');
INSERT INTO `p2p_user` VALUES ('4', 'o_HnUtw5Kpmp_sCRa3WOWvW5tThs', '', null, null, null, null, null, null, '0', 'male', '0', null, null, null, '52230765', '2014-12-17', '1', '3');

-- ----------------------------
-- Table structure for p2p_usermsg
-- ----------------------------
DROP TABLE IF EXISTS `p2p_usermsg`;
CREATE TABLE `p2p_usermsg` (
  `int` int(20) NOT NULL AUTO_INCREMENT,
  `weixin` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `fakeid` varchar(40) DEFAULT NULL,
  `msg` varchar(200) DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  PRIMARY KEY (`int`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of p2p_usermsg
-- ----------------------------
INSERT INTO `p2p_usermsg` VALUES ('1', 'o_HnUt7CiUQczh8C_f1tYbsPpSmo', 'web', '1143123239', '测试是否能发送信息', '2014-12-16 14:45:13');
INSERT INTO `p2p_usermsg` VALUES ('2', 'o_HnUt7douuToB7k_sO6xMr7oi5Y', 'web', '160011160', '你在干啥', '2014-12-16 22:22:39');
INSERT INTO `p2p_usermsg` VALUES ('3', 'o_HnUt7CiUQczh8C_f1tYbsPpSmo', 'web', '1143123239', '能看到信息吗', '2014-12-16 22:25:07');
INSERT INTO `p2p_usermsg` VALUES ('4', 'o_HnUt2zJ42G8nczgA9Ir89tg6NM', 'web', '456995115', '阿代，能看到信息吗？', '2014-12-16 22:25:50');
INSERT INTO `p2p_usermsg` VALUES ('5', 'o_HnUt7CiUQczh8C_f1tYbsPpSmo', 'web', '1143123239', '终于可以了。、不容易啊！', '2014-12-19 17:47:24');

-- ----------------------------
-- Table structure for stock_bargain
-- ----------------------------
DROP TABLE IF EXISTS `stock_bargain`;
CREATE TABLE `stock_bargain` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `userId` int(20) DEFAULT NULL,
  `stockName` varchar(20) DEFAULT NULL COMMENT '股票名称',
  `stockType` varchar(10) DEFAULT NULL COMMENT '市场类型',
  `stockNum` varchar(20) DEFAULT NULL COMMENT '证券代码',
  `direction` enum('buy','sale') DEFAULT 'buy' COMMENT 'buy:买进,sale:卖出',
  `trustMoney` float DEFAULT NULL COMMENT '买入价格',
  `trustNum` int(11) DEFAULT NULL COMMENT '买入数量',
  `truthMoney` float DEFAULT NULL COMMENT '成交价格',
  `truthNum` int(11) DEFAULT NULL COMMENT '成交数量',
  `status` enum('1','0','-1') DEFAULT '0',
  `addtime` datetime DEFAULT NULL,
  `handletime` datetime DEFAULT NULL COMMENT '处理时间',
  `operator` varchar(20) DEFAULT NULL COMMENT '操作人',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of stock_bargain
-- ----------------------------
INSERT INTO `stock_bargain` VALUES ('2', '1000000001', '美利纸业', null, 'sz000815', 'buy', '16', '6000', '15.67', '6000', '1', '2015-04-12 15:46:17', '2015-04-12 20:00:27', 'system');
INSERT INTO `stock_bargain` VALUES ('3', '1000000001', '深圳能源', null, 'sz000027', 'buy', '16', '100', '15.03', '100', '1', '2015-04-12 15:50:08', '2015-04-13 12:00:36', 'system');
INSERT INTO `stock_bargain` VALUES ('4', '1000000001', '*ST美利', null, 'sz000815', 'buy', '18.97', '236', null, null, '0', '2015-04-25 18:13:44', null, null);

-- ----------------------------
-- Table structure for stock_peizi
-- ----------------------------
DROP TABLE IF EXISTS `stock_peizi`;
CREATE TABLE `stock_peizi` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `money` double DEFAULT NULL COMMENT '配置金额',
  `beisu` int(4) DEFAULT NULL COMMENT '倍数',
  `insure` double DEFAULT NULL COMMENT '保证金',
  `available` double DEFAULT NULL COMMENT '操盘资金',
  `alarm` double DEFAULT NULL COMMENT '预警线',
  `close` double DEFAULT NULL COMMENT '平仓线',
  `deadline` int(10) DEFAULT NULL COMMENT '期限',
  `adminfee` float DEFAULT NULL COMMENT '管理费',
  `status` enum('1','0','-1') DEFAULT '0' COMMENT '状态',
  `userId` varchar(20) DEFAULT NULL COMMENT '用户ID',
  `addtime` datetime DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of stock_peizi
-- ----------------------------
INSERT INTO `stock_peizi` VALUES ('1', '100000', '4', '25000', '125000', '112000', '108000', '16', '100', '1', '1000000001', '2015-05-16 20:24:23');

-- ----------------------------
-- Table structure for stock_range
-- ----------------------------
DROP TABLE IF EXISTS `stock_range`;
CREATE TABLE `stock_range` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) DEFAULT NULL COMMENT '股票名称',
  `money` float DEFAULT NULL COMMENT '当前价格',
  `fudu` float DEFAULT NULL COMMENT '幅度',
  `type` enum('up','down') DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of stock_range
-- ----------------------------
INSERT INTO `stock_range` VALUES ('1', '中国联通', '5.34', '10.1', 'up');
INSERT INTO `stock_range` VALUES ('2', '永泰能源', '4.91', '10.09', 'up');
INSERT INTO `stock_range` VALUES ('3', '太化股份', '6.9', '10.05', 'up');
INSERT INTO `stock_range` VALUES ('4', '小商品城', '15.36', '10.03', 'up');
INSERT INTO `stock_range` VALUES ('5', '长电科技', '15.15', '10.02', 'up');
INSERT INTO `stock_range` VALUES ('6', '海润光伏', '7.72', '-6.88', 'down');
INSERT INTO `stock_range` VALUES ('7', '西部黄金', '15.86', '-4.92', 'down');
INSERT INTO `stock_range` VALUES ('8', '青山纸业', '4.28', '-3.39', 'down');
INSERT INTO `stock_range` VALUES ('9', '再升科技', '34.06', '-2.38', 'down');
INSERT INTO `stock_range` VALUES ('10', '华贸物流', '16.59', '-2.24', 'down');

-- ----------------------------
-- Table structure for system_admin
-- ----------------------------
DROP TABLE IF EXISTS `system_admin`;
CREATE TABLE `system_admin` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `user` varchar(20) DEFAULT NULL,
  `pass` varchar(32) DEFAULT NULL,
  `key` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_admin
-- ----------------------------
INSERT INTO `system_admin` VALUES ('1', 'admin', '660661f4a631442308488b69dbff0751', 'a12q');

-- ----------------------------
-- Table structure for system_answer
-- ----------------------------
DROP TABLE IF EXISTS `system_answer`;
CREATE TABLE `system_answer` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `message_answer` text,
  `message_time` datetime DEFAULT NULL,
  `message_user` varchar(20) DEFAULT NULL,
  `message_id` int(20) NOT NULL COMMENT 'model_message的id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='user_message 回答列表';

-- ----------------------------
-- Records of system_answer
-- ----------------------------
INSERT INTO `system_answer` VALUES ('1', '具体详情，请联系18296629575,刘先生', '2013-08-27 15:47:03', '管理员', '1');
INSERT INTO `system_answer` VALUES ('2', '好的请联系15679631084', '2014-01-13 11:25:58', 'admin@xinfeiyou.com', '3');
INSERT INTO `system_answer` VALUES ('3', 'OK没问题的13246', '2014-12-23 00:21:57', 'admin', '2');
INSERT INTO `system_answer` VALUES ('4', '不错哦！', '2015-03-17 09:02:10', 'admin', '6');

-- ----------------------------
-- Table structure for system_attribute
-- ----------------------------
DROP TABLE IF EXISTS `system_attribute`;
CREATE TABLE `system_attribute` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '属性名',
  `value` text NOT NULL COMMENT '属性值',
  `type` varchar(50) NOT NULL DEFAULT '' COMMENT '属性类型',
  `identity` varchar(50) NOT NULL DEFAULT '' COMMENT '唯一标识，不可重复',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='系统属性';

-- ----------------------------
-- Records of system_attribute
-- ----------------------------
INSERT INTO `system_attribute` VALUES ('1', '关键词', 'hcjrfw.com,汇诚普惠,互联网金融,福建金融,福建贷款,福建网贷,福建P2P,福建投资,福建理财,福建借款,福建车贷,福建拆借,福建急用钱,福建汽车抵押,福建二手车,福建汽车,福建p2b,福建p2c,福建小额贷款,福建网络贷款,福建按揭车,福建全款车,福建网贷,福建P2P,福州网贷,厦门网贷,三明网贷,泉州网贷,漳州网贷,龙岩网贷', 'string', 'keywords');
INSERT INTO `system_attribute` VALUES ('3', '描述', '汇诚普惠,福建首家第三方资金托管的网贷平台,五百万风险拨备金,年化收益19.8%,专注车辆抵押贷款,致力于开创小微金融新时代！我们的主营业务:互联网金融,福建金融,福建贷款,福建网贷,福建P2P,福建投资,福建理财,福建借款,福建车贷,福建拆借,福建急用钱,福建汽车抵押,福建二手车,福建汽车,福建p2b,福建p2c,福建小额贷款,福建网络贷款,福建按揭车,福建全款车,福建网贷,福建P2P,福州网贷,厦门网贷,三明网贷,泉州网贷,漳州网贷,龙岩网贷', 'string', 'description');
INSERT INTO `system_attribute` VALUES ('4', '标题', '汇诚普惠 - hcjrfw.com - 开创小微金融新时代 - 福建网贷', 'string', 'title');

-- ----------------------------
-- Table structure for system_bank
-- ----------------------------
DROP TABLE IF EXISTS `system_bank`;
CREATE TABLE `system_bank` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `bankname` varchar(20) DEFAULT NULL,
  `bankbin` varchar(20) DEFAULT NULL,
  `banklen` float DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=461 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_bank
-- ----------------------------
INSERT INTO `system_bank` VALUES ('1', '工商银行', '402791', '16');
INSERT INTO `system_bank` VALUES ('2', '工商银行', '427028', '16');
INSERT INTO `system_bank` VALUES ('3', '工商银行', '427038', '16');
INSERT INTO `system_bank` VALUES ('4', '工商银行', '548259', '16');
INSERT INTO `system_bank` VALUES ('5', '工商银行', '620200', '18');
INSERT INTO `system_bank` VALUES ('6', '工商银行', '620302', '18');
INSERT INTO `system_bank` VALUES ('7', '工商银行', '620402', '18');
INSERT INTO `system_bank` VALUES ('8', '工商银行', '620403', '18');
INSERT INTO `system_bank` VALUES ('9', '工商银行', '620404', '18');
INSERT INTO `system_bank` VALUES ('10', '工商银行', '620405', '18');
INSERT INTO `system_bank` VALUES ('11', '工商银行', '620406', '18');
INSERT INTO `system_bank` VALUES ('12', '工商银行', '620407', '18');
INSERT INTO `system_bank` VALUES ('13', '工商银行', '620408', '18');
INSERT INTO `system_bank` VALUES ('14', '工商银行', '620409', '18');
INSERT INTO `system_bank` VALUES ('15', '工商银行', '620410', '18');
INSERT INTO `system_bank` VALUES ('16', '工商银行', '620411', '18');
INSERT INTO `system_bank` VALUES ('17', '工商银行', '620412', '18');
INSERT INTO `system_bank` VALUES ('18', '工商银行', '620502', '18');
INSERT INTO `system_bank` VALUES ('19', '工商银行', '620503', '18');
INSERT INTO `system_bank` VALUES ('20', '工商银行', '620512', '18');
INSERT INTO `system_bank` VALUES ('21', '工商银行', '620602', '18');
INSERT INTO `system_bank` VALUES ('22', '工商银行', '620604', '18');
INSERT INTO `system_bank` VALUES ('23', '工商银行', '620607', '18');
INSERT INTO `system_bank` VALUES ('24', '工商银行', '620609', '18');
INSERT INTO `system_bank` VALUES ('25', '工商银行', '620611', '18');
INSERT INTO `system_bank` VALUES ('26', '工商银行', '620612', '18');
INSERT INTO `system_bank` VALUES ('27', '工商银行', '620704', '18');
INSERT INTO `system_bank` VALUES ('28', '工商银行', '620706', '18');
INSERT INTO `system_bank` VALUES ('29', '工商银行', '620707', '18');
INSERT INTO `system_bank` VALUES ('30', '工商银行', '620708', '18');
INSERT INTO `system_bank` VALUES ('31', '工商银行', '620709', '18');
INSERT INTO `system_bank` VALUES ('32', '工商银行', '620710', '18');
INSERT INTO `system_bank` VALUES ('33', '工商银行', '620711', '18');
INSERT INTO `system_bank` VALUES ('34', '工商银行', '620712', '18');
INSERT INTO `system_bank` VALUES ('35', '工商银行', '620713', '18');
INSERT INTO `system_bank` VALUES ('36', '工商银行', '620714', '18');
INSERT INTO `system_bank` VALUES ('37', '工商银行', '620802', '18');
INSERT INTO `system_bank` VALUES ('38', '工商银行', '620902', '18');
INSERT INTO `system_bank` VALUES ('39', '工商银行', '620904', '18');
INSERT INTO `system_bank` VALUES ('40', '工商银行', '620905', '18');
INSERT INTO `system_bank` VALUES ('41', '工商银行', '621001', '18');
INSERT INTO `system_bank` VALUES ('42', '工商银行', '621102', '18');
INSERT INTO `system_bank` VALUES ('43', '工商银行', '621103', '18');
INSERT INTO `system_bank` VALUES ('44', '工商银行', '621105', '18');
INSERT INTO `system_bank` VALUES ('45', '工商银行', '621106', '18');
INSERT INTO `system_bank` VALUES ('46', '工商银行', '621107', '18');
INSERT INTO `system_bank` VALUES ('47', '工商银行', '621202', '18');
INSERT INTO `system_bank` VALUES ('48', '工商银行', '621203', '18');
INSERT INTO `system_bank` VALUES ('49', '工商银行', '621204', '18');
INSERT INTO `system_bank` VALUES ('50', '工商银行', '621205', '18');
INSERT INTO `system_bank` VALUES ('51', '工商银行', '621206', '18');
INSERT INTO `system_bank` VALUES ('52', '工商银行', '621207', '18');
INSERT INTO `system_bank` VALUES ('53', '工商银行', '621208', '18');
INSERT INTO `system_bank` VALUES ('54', '工商银行', '621209', '18');
INSERT INTO `system_bank` VALUES ('55', '工商银行', '621210', '18');
INSERT INTO `system_bank` VALUES ('56', '工商银行', '621211', '18');
INSERT INTO `system_bank` VALUES ('57', '工商银行', '621302', '18');
INSERT INTO `system_bank` VALUES ('58', '工商银行', '621303', '18');
INSERT INTO `system_bank` VALUES ('59', '工商银行', '621304', '18');
INSERT INTO `system_bank` VALUES ('60', '工商银行', '621305', '18');
INSERT INTO `system_bank` VALUES ('61', '工商银行', '621306', '18');
INSERT INTO `system_bank` VALUES ('62', '工商银行', '621307', '18');
INSERT INTO `system_bank` VALUES ('63', '工商银行', '621309', '18');
INSERT INTO `system_bank` VALUES ('64', '工商银行', '621311', '18');
INSERT INTO `system_bank` VALUES ('65', '工商银行', '621313', '18');
INSERT INTO `system_bank` VALUES ('66', '工商银行', '621315', '18');
INSERT INTO `system_bank` VALUES ('67', '工商银行', '621317', '18');
INSERT INTO `system_bank` VALUES ('68', '工商银行', '621402', '18');
INSERT INTO `system_bank` VALUES ('69', '工商银行', '621404', '18');
INSERT INTO `system_bank` VALUES ('70', '工商银行', '621405', '18');
INSERT INTO `system_bank` VALUES ('71', '工商银行', '621406', '18');
INSERT INTO `system_bank` VALUES ('72', '工商银行', '621407', '18');
INSERT INTO `system_bank` VALUES ('73', '工商银行', '621408', '18');
INSERT INTO `system_bank` VALUES ('74', '工商银行', '621409', '18');
INSERT INTO `system_bank` VALUES ('75', '工商银行', '621410', '18');
INSERT INTO `system_bank` VALUES ('76', '工商银行', '621502', '18');
INSERT INTO `system_bank` VALUES ('77', '工商银行', '621511', '18');
INSERT INTO `system_bank` VALUES ('78', '工商银行', '621602', '18');
INSERT INTO `system_bank` VALUES ('79', '工商银行', '621603', '18');
INSERT INTO `system_bank` VALUES ('80', '工商银行', '621604', '18');
INSERT INTO `system_bank` VALUES ('81', '工商银行', '621605', '18');
INSERT INTO `system_bank` VALUES ('82', '工商银行', '621606', '18');
INSERT INTO `system_bank` VALUES ('83', '工商银行', '621607', '18');
INSERT INTO `system_bank` VALUES ('84', '工商银行', '621608', '18');
INSERT INTO `system_bank` VALUES ('85', '工商银行', '621609', '18');
INSERT INTO `system_bank` VALUES ('86', '工商银行', '621610', '18');
INSERT INTO `system_bank` VALUES ('87', '工商银行', '621611', '18');
INSERT INTO `system_bank` VALUES ('88', '工商银行', '621612', '18');
INSERT INTO `system_bank` VALUES ('89', '工商银行', '621613', '18');
INSERT INTO `system_bank` VALUES ('90', '工商银行', '621614', '18');
INSERT INTO `system_bank` VALUES ('91', '工商银行', '621615', '18');
INSERT INTO `system_bank` VALUES ('92', '工商银行', '621616', '18');
INSERT INTO `system_bank` VALUES ('93', '工商银行', '621617', '18');
INSERT INTO `system_bank` VALUES ('94', '工商银行', '621804', '18');
INSERT INTO `system_bank` VALUES ('95', '工商银行', '621807', '18');
INSERT INTO `system_bank` VALUES ('96', '工商银行', '621813', '18');
INSERT INTO `system_bank` VALUES ('97', '工商银行', '621814', '18');
INSERT INTO `system_bank` VALUES ('98', '工商银行', '621817', '18');
INSERT INTO `system_bank` VALUES ('99', '工商银行', '621901', '18');
INSERT INTO `system_bank` VALUES ('100', '工商银行', '621903', '18');
INSERT INTO `system_bank` VALUES ('101', '工商银行', '621904', '18');
INSERT INTO `system_bank` VALUES ('102', '工商银行', '621905', '18');
INSERT INTO `system_bank` VALUES ('103', '工商银行', '621906', '18');
INSERT INTO `system_bank` VALUES ('104', '工商银行', '621907', '18');
INSERT INTO `system_bank` VALUES ('105', '工商银行', '621908', '18');
INSERT INTO `system_bank` VALUES ('106', '工商银行', '621909', '18');
INSERT INTO `system_bank` VALUES ('107', '工商银行', '621910', '18');
INSERT INTO `system_bank` VALUES ('108', '工商银行', '621911', '18');
INSERT INTO `system_bank` VALUES ('109', '工商银行', '621912', '18');
INSERT INTO `system_bank` VALUES ('110', '工商银行', '621913', '18');
INSERT INTO `system_bank` VALUES ('111', '工商银行', '621914', '18');
INSERT INTO `system_bank` VALUES ('112', '工商银行', '621915', '18');
INSERT INTO `system_bank` VALUES ('113', '工商银行', '622002', '18');
INSERT INTO `system_bank` VALUES ('114', '工商银行', '622003', '18');
INSERT INTO `system_bank` VALUES ('115', '工商银行', '622004', '18');
INSERT INTO `system_bank` VALUES ('116', '工商银行', '622005', '18');
INSERT INTO `system_bank` VALUES ('117', '工商银行', '622006', '18');
INSERT INTO `system_bank` VALUES ('118', '工商银行', '622007', '18');
INSERT INTO `system_bank` VALUES ('119', '工商银行', '622008', '18');
INSERT INTO `system_bank` VALUES ('120', '工商银行', '622010', '18');
INSERT INTO `system_bank` VALUES ('121', '工商银行', '622011', '18');
INSERT INTO `system_bank` VALUES ('122', '工商银行', '622012', '18');
INSERT INTO `system_bank` VALUES ('123', '工商银行', '622013', '18');
INSERT INTO `system_bank` VALUES ('124', '工商银行', '622015', '18');
INSERT INTO `system_bank` VALUES ('125', '工商银行', '622016', '18');
INSERT INTO `system_bank` VALUES ('126', '工商银行', '622017', '18');
INSERT INTO `system_bank` VALUES ('127', '工商银行', '622018', '18');
INSERT INTO `system_bank` VALUES ('128', '工商银行', '622019', '18');
INSERT INTO `system_bank` VALUES ('129', '工商银行', '622020', '18');
INSERT INTO `system_bank` VALUES ('130', '工商银行', '622102', '18');
INSERT INTO `system_bank` VALUES ('131', '工商银行', '622103', '18');
INSERT INTO `system_bank` VALUES ('132', '工商银行', '622104', '18');
INSERT INTO `system_bank` VALUES ('133', '工商银行', '622105', '18');
INSERT INTO `system_bank` VALUES ('134', '工商银行', '622110', '18');
INSERT INTO `system_bank` VALUES ('135', '工商银行', '622111', '18');
INSERT INTO `system_bank` VALUES ('136', '工商银行', '622114', '18');
INSERT INTO `system_bank` VALUES ('137', '工商银行', '622302', '18');
INSERT INTO `system_bank` VALUES ('138', '工商银行', '622303', '18');
INSERT INTO `system_bank` VALUES ('139', '工商银行', '622304', '18');
INSERT INTO `system_bank` VALUES ('140', '工商银行', '622305', '18');
INSERT INTO `system_bank` VALUES ('141', '工商银行', '622306', '18');
INSERT INTO `system_bank` VALUES ('142', '工商银行', '622307', '18');
INSERT INTO `system_bank` VALUES ('143', '工商银行', '622308', '18');
INSERT INTO `system_bank` VALUES ('144', '工商银行', '622309', '18');
INSERT INTO `system_bank` VALUES ('145', '工商银行', '622313', '18');
INSERT INTO `system_bank` VALUES ('146', '工商银行', '622314', '18');
INSERT INTO `system_bank` VALUES ('147', '工商银行', '622315', '18');
INSERT INTO `system_bank` VALUES ('148', '工商银行', '622317', '18');
INSERT INTO `system_bank` VALUES ('149', '工商银行', '622402', '18');
INSERT INTO `system_bank` VALUES ('150', '工商银行', '622403', '18');
INSERT INTO `system_bank` VALUES ('151', '工商银行', '622404', '18');
INSERT INTO `system_bank` VALUES ('152', '工商银行', '622502', '18');
INSERT INTO `system_bank` VALUES ('153', '工商银行', '622504', '18');
INSERT INTO `system_bank` VALUES ('154', '工商银行', '622505', '18');
INSERT INTO `system_bank` VALUES ('155', '工商银行', '622509', '18');
INSERT INTO `system_bank` VALUES ('156', '工商银行', '622510', '18');
INSERT INTO `system_bank` VALUES ('157', '工商银行', '622513', '18');
INSERT INTO `system_bank` VALUES ('158', '工商银行', '622517', '18');
INSERT INTO `system_bank` VALUES ('159', '工商银行', '622604', '18');
INSERT INTO `system_bank` VALUES ('160', '工商银行', '622605', '18');
INSERT INTO `system_bank` VALUES ('161', '工商银行', '622606', '18');
INSERT INTO `system_bank` VALUES ('162', '工商银行', '622703', '18');
INSERT INTO `system_bank` VALUES ('163', '工商银行', '622706', '18');
INSERT INTO `system_bank` VALUES ('164', '工商银行', '622715', '18');
INSERT INTO `system_bank` VALUES ('165', '工商银行', '622806', '18');
INSERT INTO `system_bank` VALUES ('166', '工商银行', '622902', '18');
INSERT INTO `system_bank` VALUES ('167', '工商银行', '622903', '18');
INSERT INTO `system_bank` VALUES ('168', '工商银行', '622904', '18');
INSERT INTO `system_bank` VALUES ('169', '工商银行', '623002', '18');
INSERT INTO `system_bank` VALUES ('170', '工商银行', '623006', '18');
INSERT INTO `system_bank` VALUES ('171', '工商银行', '623008', '18');
INSERT INTO `system_bank` VALUES ('172', '工商银行', '623011', '18');
INSERT INTO `system_bank` VALUES ('173', '工商银行', '623012', '18');
INSERT INTO `system_bank` VALUES ('174', '工商银行', '623014', '18');
INSERT INTO `system_bank` VALUES ('175', '工商银行', '623015', '18');
INSERT INTO `system_bank` VALUES ('176', '工商银行', '623100', '18');
INSERT INTO `system_bank` VALUES ('177', '工商银行', '623202', '18');
INSERT INTO `system_bank` VALUES ('178', '工商银行', '623301', '18');
INSERT INTO `system_bank` VALUES ('179', '工商银行', '623400', '18');
INSERT INTO `system_bank` VALUES ('180', '工商银行', '623500', '18');
INSERT INTO `system_bank` VALUES ('181', '工商银行', '623602', '18');
INSERT INTO `system_bank` VALUES ('182', '工商银行', '623700', '18');
INSERT INTO `system_bank` VALUES ('183', '工商银行', '623803', '18');
INSERT INTO `system_bank` VALUES ('184', '工商银行', '623901', '18');
INSERT INTO `system_bank` VALUES ('185', '工商银行', '624000', '18');
INSERT INTO `system_bank` VALUES ('186', '工商银行', '624100', '18');
INSERT INTO `system_bank` VALUES ('187', '工商银行', '624200', '18');
INSERT INTO `system_bank` VALUES ('188', '工商银行', '624301', '18');
INSERT INTO `system_bank` VALUES ('189', '工商银行', '624402', '18');
INSERT INTO `system_bank` VALUES ('190', '工商银行', '620058', '19');
INSERT INTO `system_bank` VALUES ('191', '工商银行', '620516', '19');
INSERT INTO `system_bank` VALUES ('192', '工商银行', '621225', '19');
INSERT INTO `system_bank` VALUES ('193', '工商银行', '621226', '19');
INSERT INTO `system_bank` VALUES ('194', '工商银行', '621227', '19');
INSERT INTO `system_bank` VALUES ('195', '工商银行', '621281', '19');
INSERT INTO `system_bank` VALUES ('196', '工商银行', '621288', '19');
INSERT INTO `system_bank` VALUES ('197', '工商银行', '621721', '19');
INSERT INTO `system_bank` VALUES ('198', '工商银行', '621722', '19');
INSERT INTO `system_bank` VALUES ('199', '工商银行', '621723', '19');
INSERT INTO `system_bank` VALUES ('200', '工商银行', '622200', '19');
INSERT INTO `system_bank` VALUES ('201', '工商银行', '622202', '19');
INSERT INTO `system_bank` VALUES ('202', '工商银行', '622203', '19');
INSERT INTO `system_bank` VALUES ('203', '工商银行', '622208', '19');
INSERT INTO `system_bank` VALUES ('204', '工商银行', '900000', '19');
INSERT INTO `system_bank` VALUES ('205', '工商银行', '900010', '19');
INSERT INTO `system_bank` VALUES ('206', '工商银行', '9558', '19');
INSERT INTO `system_bank` VALUES ('207', '工商银行', '620086', '19');
INSERT INTO `system_bank` VALUES ('208', '工商银行', '621558', '19');
INSERT INTO `system_bank` VALUES ('209', '工商银行', '621559', '19');
INSERT INTO `system_bank` VALUES ('210', '工商银行', '621618', '19');
INSERT INTO `system_bank` VALUES ('211', '工商银行', '621670', '19');
INSERT INTO `system_bank` VALUES ('212', '工商银行', '623062', '19');
INSERT INTO `system_bank` VALUES ('213', '建设银行', '421349', '16');
INSERT INTO `system_bank` VALUES ('214', '建设银行', '434061', '16');
INSERT INTO `system_bank` VALUES ('215', '建设银行', '434062', '16');
INSERT INTO `system_bank` VALUES ('216', '建设银行', '524094', '16');
INSERT INTO `system_bank` VALUES ('217', '建设银行', '526410', '16');
INSERT INTO `system_bank` VALUES ('218', '建设银行', '552245', '16');
INSERT INTO `system_bank` VALUES ('219', '建设银行', '621080', '16');
INSERT INTO `system_bank` VALUES ('220', '建设银行', '621082', '16');
INSERT INTO `system_bank` VALUES ('221', '建设银行', '621466', '16');
INSERT INTO `system_bank` VALUES ('222', '建设银行', '621488', '16');
INSERT INTO `system_bank` VALUES ('223', '建设银行', '621499', '16');
INSERT INTO `system_bank` VALUES ('224', '建设银行', '622966', '16');
INSERT INTO `system_bank` VALUES ('225', '建设银行', '622988', '16');
INSERT INTO `system_bank` VALUES ('226', '建设银行', '436742', '19');
INSERT INTO `system_bank` VALUES ('227', '建设银行', '589970', '19');
INSERT INTO `system_bank` VALUES ('228', '建设银行', '620060', '19');
INSERT INTO `system_bank` VALUES ('229', '建设银行', '621081', '19');
INSERT INTO `system_bank` VALUES ('230', '建设银行', '621284', '19');
INSERT INTO `system_bank` VALUES ('231', '建设银行', '621467', '19');
INSERT INTO `system_bank` VALUES ('232', '建设银行', '621598', '19');
INSERT INTO `system_bank` VALUES ('233', '建设银行', '621621', '19');
INSERT INTO `system_bank` VALUES ('234', '建设银行', '621700', '19');
INSERT INTO `system_bank` VALUES ('235', '建设银行', '622280', '19');
INSERT INTO `system_bank` VALUES ('236', '建设银行', '622700', '19');
INSERT INTO `system_bank` VALUES ('239', '建设银行', '623211', '19');
INSERT INTO `system_bank` VALUES ('240', '农业银行', '103', '19');
INSERT INTO `system_bank` VALUES ('241', '农业银行', '620059', '19');
INSERT INTO `system_bank` VALUES ('242', '农业银行', '621282', '19');
INSERT INTO `system_bank` VALUES ('243', '农业银行', '621336', '19');
INSERT INTO `system_bank` VALUES ('244', '农业银行', '621619', '19');
INSERT INTO `system_bank` VALUES ('245', '农业银行', '621671', '19');
INSERT INTO `system_bank` VALUES ('246', '农业银行', '622821', '19');
INSERT INTO `system_bank` VALUES ('247', '农业银行', '622822', '19');
INSERT INTO `system_bank` VALUES ('248', '农业银行', '622823', '19');
INSERT INTO `system_bank` VALUES ('249', '农业银行', '622824', '19');
INSERT INTO `system_bank` VALUES ('250', '农业银行', '622825', '19');
INSERT INTO `system_bank` VALUES ('251', '农业银行', '622826', '19');
INSERT INTO `system_bank` VALUES ('252', '农业银行', '622827', '19');
INSERT INTO `system_bank` VALUES ('253', '农业银行', '622828', '19');
INSERT INTO `system_bank` VALUES ('254', '农业银行', '622840', '19');
INSERT INTO `system_bank` VALUES ('255', '农业银行', '622841', '19');
INSERT INTO `system_bank` VALUES ('256', '农业银行', '622843', '19');
INSERT INTO `system_bank` VALUES ('257', '农业银行', '622844', '19');
INSERT INTO `system_bank` VALUES ('258', '农业银行', '622845', '19');
INSERT INTO `system_bank` VALUES ('259', '农业银行', '622846', '19');
INSERT INTO `system_bank` VALUES ('260', '农业银行', '622847', '19');
INSERT INTO `system_bank` VALUES ('261', '农业银行', '622848', '19');
INSERT INTO `system_bank` VALUES ('262', '农业银行', '622849', '19');
INSERT INTO `system_bank` VALUES ('263', '农业银行', '623018', '19');
INSERT INTO `system_bank` VALUES ('264', '农业银行', '623206', '19');
INSERT INTO `system_bank` VALUES ('265', '农业银行', '95595', '19');
INSERT INTO `system_bank` VALUES ('266', '农业银行', '95596', '19');
INSERT INTO `system_bank` VALUES ('267', '农业银行', '95597', '19');
INSERT INTO `system_bank` VALUES ('268', '农业银行', '95598', '19');
INSERT INTO `system_bank` VALUES ('269', '农业银行', '95599', '19');
INSERT INTO `system_bank` VALUES ('270', '平安银行', '621626', '19');
INSERT INTO `system_bank` VALUES ('271', '平安银行', '623058', '19');
INSERT INTO `system_bank` VALUES ('272', '平安银行', '602907', '16');
INSERT INTO `system_bank` VALUES ('273', '平安银行', '622298', '16');
INSERT INTO `system_bank` VALUES ('274', '平安银行', '622986', '16');
INSERT INTO `system_bank` VALUES ('275', '平安银行', '622989', '16');
INSERT INTO `system_bank` VALUES ('276', '平安银行', '627066', '16');
INSERT INTO `system_bank` VALUES ('277', '平安银行', '627067', '16');
INSERT INTO `system_bank` VALUES ('278', '平安银行', '627068', '16');
INSERT INTO `system_bank` VALUES ('279', '平安银行', '627069', '16');
INSERT INTO `system_bank` VALUES ('280', '深圳发展银行', '412962', '16');
INSERT INTO `system_bank` VALUES ('281', '深圳发展银行', '412963', '16');
INSERT INTO `system_bank` VALUES ('282', '深圳发展银行', '415752', '16');
INSERT INTO `system_bank` VALUES ('283', '深圳发展银行', '415753', '16');
INSERT INTO `system_bank` VALUES ('284', '深圳发展银行', '622535', '16');
INSERT INTO `system_bank` VALUES ('285', '深圳发展银行', '622536', '16');
INSERT INTO `system_bank` VALUES ('286', '深圳发展银行', '622538', '16');
INSERT INTO `system_bank` VALUES ('287', '深圳发展银行', '622539', '16');
INSERT INTO `system_bank` VALUES ('288', '深圳发展银行', '622983', '16');
INSERT INTO `system_bank` VALUES ('289', '深圳发展银行', '998800', '16');
INSERT INTO `system_bank` VALUES ('290', '招商银行', '690755', '15');
INSERT INTO `system_bank` VALUES ('291', '招商银行', '402658', '16');
INSERT INTO `system_bank` VALUES ('292', '招商银行', '410062', '16');
INSERT INTO `system_bank` VALUES ('293', '招商银行', '468203', '16');
INSERT INTO `system_bank` VALUES ('294', '招商银行', '512425', '16');
INSERT INTO `system_bank` VALUES ('295', '招商银行', '524011', '16');
INSERT INTO `system_bank` VALUES ('296', '招商银行', '621286', '16');
INSERT INTO `system_bank` VALUES ('297', '招商银行', '622580', '16');
INSERT INTO `system_bank` VALUES ('298', '招商银行', '622588', '16');
INSERT INTO `system_bank` VALUES ('299', '招商银行', '622598', '16');
INSERT INTO `system_bank` VALUES ('300', '招商银行', '622609', '16');
INSERT INTO `system_bank` VALUES ('301', '招商银行', '95555', '16');
INSERT INTO `system_bank` VALUES ('302', '招商银行', '690755', '18');
INSERT INTO `system_bank` VALUES ('303', '中信银行', '433670', '16');
INSERT INTO `system_bank` VALUES ('304', '中信银行', '433671', '16');
INSERT INTO `system_bank` VALUES ('305', '中信银行', '433680', '16');
INSERT INTO `system_bank` VALUES ('306', '中信银行', '442729', '16');
INSERT INTO `system_bank` VALUES ('307', '中信银行', '442730', '16');
INSERT INTO `system_bank` VALUES ('308', '中信银行', '620082', '16');
INSERT INTO `system_bank` VALUES ('309', '中信银行', '621767', '16');
INSERT INTO `system_bank` VALUES ('310', '中信银行', '621768', '16');
INSERT INTO `system_bank` VALUES ('311', '中信银行', '621770', '16');
INSERT INTO `system_bank` VALUES ('312', '中信银行', '621771', '16');
INSERT INTO `system_bank` VALUES ('313', '中信银行', '621772', '16');
INSERT INTO `system_bank` VALUES ('314', '中信银行', '621773', '16');
INSERT INTO `system_bank` VALUES ('315', '中信银行', '622690', '16');
INSERT INTO `system_bank` VALUES ('316', '中信银行', '622691', '16');
INSERT INTO `system_bank` VALUES ('317', '中信银行', '622692', '16');
INSERT INTO `system_bank` VALUES ('318', '中信银行', '622696', '16');
INSERT INTO `system_bank` VALUES ('319', '中信银行', '622698', '16');
INSERT INTO `system_bank` VALUES ('320', '中信银行', '622998', '16');
INSERT INTO `system_bank` VALUES ('321', '中信银行', '622999', '16');
INSERT INTO `system_bank` VALUES ('322', '中信银行', '968807', '16');
INSERT INTO `system_bank` VALUES ('323', '中信银行', '968808', '16');
INSERT INTO `system_bank` VALUES ('324', '中信银行', '968809', '16');
INSERT INTO `system_bank` VALUES ('325', '光大银行', '303', '16');
INSERT INTO `system_bank` VALUES ('326', '光大银行', '620085', '16');
INSERT INTO `system_bank` VALUES ('327', '光大银行', '620518', '16');
INSERT INTO `system_bank` VALUES ('328', '光大银行', '621489', '16');
INSERT INTO `system_bank` VALUES ('329', '光大银行', '621492', '16');
INSERT INTO `system_bank` VALUES ('330', '光大银行', '622660', '16');
INSERT INTO `system_bank` VALUES ('331', '光大银行', '622661', '16');
INSERT INTO `system_bank` VALUES ('332', '光大银行', '622662', '16');
INSERT INTO `system_bank` VALUES ('333', '光大银行', '622663', '16');
INSERT INTO `system_bank` VALUES ('334', '光大银行', '622664', '16');
INSERT INTO `system_bank` VALUES ('335', '光大银行', '622665', '16');
INSERT INTO `system_bank` VALUES ('336', '光大银行', '622666', '16');
INSERT INTO `system_bank` VALUES ('337', '光大银行', '622667', '16');
INSERT INTO `system_bank` VALUES ('338', '光大银行', '622668', '16');
INSERT INTO `system_bank` VALUES ('339', '光大银行', '622669', '16');
INSERT INTO `system_bank` VALUES ('340', '光大银行', '622670', '16');
INSERT INTO `system_bank` VALUES ('341', '光大银行', '622671', '16');
INSERT INTO `system_bank` VALUES ('342', '光大银行', '622672', '16');
INSERT INTO `system_bank` VALUES ('343', '光大银行', '622673', '16');
INSERT INTO `system_bank` VALUES ('344', '光大银行', '622674', '16');
INSERT INTO `system_bank` VALUES ('345', '光大银行', '90030', '16');
INSERT INTO `system_bank` VALUES ('346', '光大银行', '620535', '19');
INSERT INTO `system_bank` VALUES ('347', '浦发银行', '622516', '16');
INSERT INTO `system_bank` VALUES ('348', '浦发银行', '622517', '16');
INSERT INTO `system_bank` VALUES ('349', '浦发银行', '622518', '16');
INSERT INTO `system_bank` VALUES ('350', '浦发银行', '622521', '16');
INSERT INTO `system_bank` VALUES ('351', '浦发银行', '622522', '16');
INSERT INTO `system_bank` VALUES ('352', '浦发银行', '622523', '16');
INSERT INTO `system_bank` VALUES ('353', '浦发银行', '84301', '16');
INSERT INTO `system_bank` VALUES ('354', '浦发银行', '84336', '16');
INSERT INTO `system_bank` VALUES ('355', '浦发银行', '84373', '16');
INSERT INTO `system_bank` VALUES ('356', '浦发银行', '84385', '16');
INSERT INTO `system_bank` VALUES ('357', '浦发银行', '84390', '16');
INSERT INTO `system_bank` VALUES ('358', '浦发银行', '87000', '16');
INSERT INTO `system_bank` VALUES ('359', '浦发银行', '87010', '16');
INSERT INTO `system_bank` VALUES ('360', '浦发银行', '87030', '16');
INSERT INTO `system_bank` VALUES ('361', '浦发银行', '87040', '16');
INSERT INTO `system_bank` VALUES ('362', '浦发银行', '84380', '16');
INSERT INTO `system_bank` VALUES ('363', '浦发银行', '984301', '16');
INSERT INTO `system_bank` VALUES ('364', '浦发银行', '984303', '16');
INSERT INTO `system_bank` VALUES ('365', '浦发银行', '84361', '16');
INSERT INTO `system_bank` VALUES ('366', '浦发银行', '87050', '16');
INSERT INTO `system_bank` VALUES ('367', '浦发银行', '621352', '16');
INSERT INTO `system_bank` VALUES ('368', '浦发银行', '621793', '16');
INSERT INTO `system_bank` VALUES ('369', '浦发银行', '621795', '16');
INSERT INTO `system_bank` VALUES ('370', '浦发银行', '621796', '16');
INSERT INTO `system_bank` VALUES ('371', '浦发银行', '621351', '16');
INSERT INTO `system_bank` VALUES ('372', '浦发银行', '621390', '16');
INSERT INTO `system_bank` VALUES ('373', '浦发银行', '621792', '16');
INSERT INTO `system_bank` VALUES ('374', '浦发银行', '621791', '16');
INSERT INTO `system_bank` VALUES ('375', '浦发银行', '84342', '16');
INSERT INTO `system_bank` VALUES ('376', '民生银行', '415599', '16');
INSERT INTO `system_bank` VALUES ('377', '民生银行', '421393', '16');
INSERT INTO `system_bank` VALUES ('378', '民生银行', '421865', '16');
INSERT INTO `system_bank` VALUES ('379', '民生银行', '427570', '16');
INSERT INTO `system_bank` VALUES ('380', '民生银行', '427571', '16');
INSERT INTO `system_bank` VALUES ('381', '民生银行', '472067', '16');
INSERT INTO `system_bank` VALUES ('382', '民生银行', '472068', '16');
INSERT INTO `system_bank` VALUES ('383', '民生银行', '622615', '16');
INSERT INTO `system_bank` VALUES ('384', '民生银行', '622616', '16');
INSERT INTO `system_bank` VALUES ('385', '民生银行', '622617', '16');
INSERT INTO `system_bank` VALUES ('386', '民生银行', '622618', '16');
INSERT INTO `system_bank` VALUES ('387', '民生银行', '622619', '16');
INSERT INTO `system_bank` VALUES ('388', '民生银行', '622620', '16');
INSERT INTO `system_bank` VALUES ('389', '民生银行', '622622', '16');
INSERT INTO `system_bank` VALUES ('390', '交通银行', '601428', '17');
INSERT INTO `system_bank` VALUES ('391', '交通银行', '405512', '17');
INSERT INTO `system_bank` VALUES ('392', '交通银行', '622258', '17');
INSERT INTO `system_bank` VALUES ('393', '交通银行', '622259', '17');
INSERT INTO `system_bank` VALUES ('394', '交通银行', '622260', '19');
INSERT INTO `system_bank` VALUES ('395', '交通银行', '622261', '19');
INSERT INTO `system_bank` VALUES ('396', '交通银行', '622262', '19');
INSERT INTO `system_bank` VALUES ('397', '交通银行', '621056', '19');
INSERT INTO `system_bank` VALUES ('398', '交通银行', '621335', '19');
INSERT INTO `system_bank` VALUES ('399', '邮政储蓄银行', '621096', '19');
INSERT INTO `system_bank` VALUES ('400', '邮政储蓄银行', '621098', '19');
INSERT INTO `system_bank` VALUES ('401', '邮政储蓄银行', '622150', '19');
INSERT INTO `system_bank` VALUES ('402', '邮政储蓄银行', '622151', '19');
INSERT INTO `system_bank` VALUES ('403', '邮政储蓄银行', '622181', '19');
INSERT INTO `system_bank` VALUES ('404', '邮政储蓄银行', '622188', '19');
INSERT INTO `system_bank` VALUES ('405', '邮政储蓄银行', '955100', '19');
INSERT INTO `system_bank` VALUES ('406', '邮政储蓄银行', '621095', '19');
INSERT INTO `system_bank` VALUES ('407', '邮政储蓄银行', '620062', '19');
INSERT INTO `system_bank` VALUES ('408', '邮政储蓄银行', '621285', '19');
INSERT INTO `system_bank` VALUES ('409', '邮政储蓄银行', '621798', '19');
INSERT INTO `system_bank` VALUES ('410', '邮政储蓄银行', '621799', '19');
INSERT INTO `system_bank` VALUES ('411', '邮政储蓄银行', '621797', '19');
INSERT INTO `system_bank` VALUES ('412', '邮政储蓄银行', '620529', '19');
INSERT INTO `system_bank` VALUES ('413', '邮政储蓄银行', '622199', '19');
INSERT INTO `system_bank` VALUES ('414', '邮政储蓄银行', '62215049', '19');
INSERT INTO `system_bank` VALUES ('415', '邮政储蓄银行', '62215050', '19');
INSERT INTO `system_bank` VALUES ('416', '邮政储蓄银行', '62215051', '19');
INSERT INTO `system_bank` VALUES ('417', '邮政储蓄银行', '62218850', '19');
INSERT INTO `system_bank` VALUES ('418', '邮政储蓄银行', '62218851', '19');
INSERT INTO `system_bank` VALUES ('419', '邮政储蓄银行', '62218849', '19');
INSERT INTO `system_bank` VALUES ('420', '邮政储蓄银行', '621622', '19');
INSERT INTO `system_bank` VALUES ('421', '邮政储蓄银行', '621599', '19');
INSERT INTO `system_bank` VALUES ('422', '邮政储蓄银行', '623219', '19');
INSERT INTO `system_bank` VALUES ('423', '邮政储蓄银行', '621674', '19');
INSERT INTO `system_bank` VALUES ('424', '邮政储蓄银行', '623218', '19');
INSERT INTO `system_bank` VALUES ('425', '中国银行', '621660', '19');
INSERT INTO `system_bank` VALUES ('426', '中国银行', '621661', '19');
INSERT INTO `system_bank` VALUES ('427', '中国银行', '621662', '19');
INSERT INTO `system_bank` VALUES ('428', '中国银行', '621663', '19');
INSERT INTO `system_bank` VALUES ('429', '中国银行', '621665', '19');
INSERT INTO `system_bank` VALUES ('430', '中国银行', '621667', '19');
INSERT INTO `system_bank` VALUES ('431', '中国银行', '621668', '19');
INSERT INTO `system_bank` VALUES ('432', '中国银行', '621669', '19');
INSERT INTO `system_bank` VALUES ('433', '中国银行', '621666', '19');
INSERT INTO `system_bank` VALUES ('434', '中国银行', '456351', '19');
INSERT INTO `system_bank` VALUES ('435', '中国银行', '601382', '19');
INSERT INTO `system_bank` VALUES ('436', '中国银行', '621256', '19');
INSERT INTO `system_bank` VALUES ('437', '中国银行', '621212', '19');
INSERT INTO `system_bank` VALUES ('438', '中国银行', '621283', '19');
INSERT INTO `system_bank` VALUES ('439', '中国银行', '620061', '19');
INSERT INTO `system_bank` VALUES ('440', '中国银行', '621725', '19');
INSERT INTO `system_bank` VALUES ('441', '中国银行', '621330', '19');
INSERT INTO `system_bank` VALUES ('442', '中国银行', '621331', '19');
INSERT INTO `system_bank` VALUES ('443', '中国银行', '621332', '19');
INSERT INTO `system_bank` VALUES ('444', '中国银行', '621333', '19');
INSERT INTO `system_bank` VALUES ('445', '中国银行', '621297', '19');
INSERT INTO `system_bank` VALUES ('446', '中国银行', '621568', '19');
INSERT INTO `system_bank` VALUES ('447', '中国银行', '621569', '19');
INSERT INTO `system_bank` VALUES ('448', '中国银行', '621672', '19');
INSERT INTO `system_bank` VALUES ('449', '中国银行', '623208', '19');
INSERT INTO `system_bank` VALUES ('450', '中国银行', '621620', '19');
INSERT INTO `system_bank` VALUES ('451', '中国银行', '621756', '19');
INSERT INTO `system_bank` VALUES ('452', '中国银行', '621757', '19');
INSERT INTO `system_bank` VALUES ('453', '中国银行', '621758', '19');
INSERT INTO `system_bank` VALUES ('454', '中国银行', '621759', '19');
INSERT INTO `system_bank` VALUES ('455', '中国银行', '621785', '19');
INSERT INTO `system_bank` VALUES ('456', '中国银行', '621786', '19');
INSERT INTO `system_bank` VALUES ('457', '中国银行', '621787', '19');
INSERT INTO `system_bank` VALUES ('458', '中国银行', '621788', '19');
INSERT INTO `system_bank` VALUES ('459', '中国银行', '621789', '19');
INSERT INTO `system_bank` VALUES ('460', '中国银行', '621790', '19');

-- ----------------------------
-- Table structure for system_banner
-- ----------------------------
DROP TABLE IF EXISTS `system_banner`;
CREATE TABLE `system_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `type_id` int(4) NOT NULL DEFAULT '0' COMMENT '类型ID',
  `link` varchar(150) NOT NULL DEFAULT '' COMMENT '跳转地址',
  `banner` varchar(150) NOT NULL DEFAULT '' COMMENT 'banner图片',
  `addtime` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '添加时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态:0-不显示,1-显示',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='轮播图';

-- ----------------------------
-- Records of system_banner
-- ----------------------------
INSERT INTO `system_banner` VALUES ('1', '轮播图1', '1', 'http://www.hcjrfw.com', '143193214176294.jpg', '2015-05-18 14:55:41', '1');

-- ----------------------------
-- Table structure for system_banner_type
-- ----------------------------
DROP TABLE IF EXISTS `system_banner_type`;
CREATE TABLE `system_banner_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='轮播图类型';

-- ----------------------------
-- Records of system_banner_type
-- ----------------------------
INSERT INTO `system_banner_type` VALUES ('1', '首页轮播图');

-- ----------------------------
-- Table structure for system_imitatelog
-- ----------------------------
DROP TABLE IF EXISTS `system_imitatelog`;
CREATE TABLE `system_imitatelog` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `userId` int(20) DEFAULT NULL COMMENT '用户ID',
  `imiMoney` float DEFAULT NULL COMMENT '配资金额',
  `imiMultiple` int(11) DEFAULT '1' COMMENT '风险倍数',
  `imiBail` float DEFAULT NULL COMMENT '保证金',
  `imiStockMoney` float DEFAULT NULL COMMENT '操盘资金',
  `imiBodeMoney` float DEFAULT NULL COMMENT '预警线',
  `imiOutMoney` float DEFAULT NULL COMMENT '平仓线',
  `imiApplyDay` int(11) DEFAULT NULL COMMENT '使用期限',
  `imiManageMoney` float DEFAULT NULL COMMENT '管理费，按天来算',
  `addtime` datetime DEFAULT NULL COMMENT '添加时间',
  `status` enum('1','0','-1') DEFAULT '0' COMMENT '1通过，0待审，-1拒绝',
  `operator` varchar(20) DEFAULT NULL COMMENT '操作人',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_imitatelog
-- ----------------------------

-- ----------------------------
-- Table structure for system_link
-- ----------------------------
DROP TABLE IF EXISTS `system_link`;
CREATE TABLE `system_link` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `link_url` varchar(200) DEFAULT NULL,
  `link_name` varchar(200) DEFAULT NULL,
  `link_user` varchar(20) DEFAULT NULL,
  `link_time` datetime DEFAULT NULL,
  `link_status` enum('1','0') DEFAULT '1',
  `link_type` enum('link','work') DEFAULT 'link' COMMENT '链接，合作商',
  `link_logo` varchar(50) NOT NULL DEFAULT '' COMMENT '网站logo',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COMMENT='友情链接';

-- ----------------------------
-- Records of system_link
-- ----------------------------
INSERT INTO `system_link` VALUES ('1', 'http://www.jindaizhi.com', '金袋子', 'admin', '2014-12-22 22:18:14', '1', 'link', '');
INSERT INTO `system_link` VALUES ('2', 'http://www.hcjrfw.com', '汇诚普惠', 'admin', '2014-12-22 22:20:45', '1', 'link', '');
INSERT INTO `system_link` VALUES ('3', 'http://www.renrendai.com', '人人贷', 'admin', '2014-12-22 23:54:27', '1', 'link', '');
INSERT INTO `system_link` VALUES ('4', 'http://www.cmbc.com.cn', '民生银行', 'admin', '2014-12-22 23:55:22', '1', 'work', '');
INSERT INTO `system_link` VALUES ('5', 'http://www.ccb.com', '建设银行', 'admin', '2014-12-22 23:56:21', '1', 'work', '');
INSERT INTO `system_link` VALUES ('6', 'http://www.abchina.com', '农业银行', 'admin', '2014-12-22 23:56:53', '1', 'work', '');
INSERT INTO `system_link` VALUES ('19', 'aaaaaa', 'sdfs', 'admin', '2015-05-18 14:17:05', '1', 'link', '');
INSERT INTO `system_link` VALUES ('18', '斯蒂芬', '111正在', 'admin', '2015-05-15 17:31:08', '1', 'link', '143168226881394.png');

-- ----------------------------
-- Table structure for system_message
-- ----------------------------
DROP TABLE IF EXISTS `system_message`;
CREATE TABLE `system_message` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(20) DEFAULT NULL COMMENT '用户名',
  `user_tel` varchar(20) DEFAULT NULL COMMENT '电话',
  `user_mail` varchar(100) DEFAULT NULL COMMENT '邮件',
  `user_contact` varchar(100) DEFAULT NULL COMMENT '留言内容',
  `user_message` text COMMENT '留言主题',
  `user_time` datetime DEFAULT NULL COMMENT '创建时间',
  `user_ip` varchar(15) DEFAULT NULL COMMENT 'ip地址',
  `user_status` enum('0','1') DEFAULT '0' COMMENT '有效无效',
  `pro_number` varchar(20) DEFAULT NULL COMMENT '订单单号',
  `type` enum('tousu','jianyi') DEFAULT 'jianyi' COMMENT '类型',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='用户提问';

-- ----------------------------
-- Records of system_message
-- ----------------------------
INSERT INTO `system_message` VALUES ('1', '刘先生', '13245546608', '', '', '有业务方面的问题咨询下。', '2013-08-27 15:46:13', '120.206.160.73', '1', null, 'jianyi');
INSERT INTO `system_message` VALUES ('2', '刘先生', '18270067079', '', '', '我要购买100个螺丝模具', '2013-08-28 19:48:35', '120.206.160.73', '1', null, 'jianyi');
INSERT INTO `system_message` VALUES ('3', '刘先生', '18296629576', '', '', '我要订购牙板。', '2013-08-28 19:50:19', '192.168.10.188', '1', null, 'jianyi');
INSERT INTO `system_message` VALUES ('6', '陈女生', '18894141663', 'xing654@163.com', '哈切', '啊我发违法jaw配额分jaw配额分jaw', '2014-12-29 20:12:22', '202.104.151.84', '1', null, 'jianyi');

-- ----------------------------
-- Table structure for system_news
-- ----------------------------
DROP TABLE IF EXISTS `system_news`;
CREATE TABLE `system_news` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `news_title` varchar(200) DEFAULT NULL,
  `news_abstract` varchar(200) DEFAULT NULL,
  `news_body` text,
  `news_time` datetime DEFAULT NULL,
  `news_num` int(20) DEFAULT '0',
  `news_user` varchar(40) DEFAULT NULL,
  `news_url` varchar(200) DEFAULT NULL COMMENT '抓取的URL地址',
  `news_type` varchar(20) DEFAULT NULL COMMENT '新闻类别',
  `news_keywords` varchar(200) DEFAULT NULL COMMENT '关键字',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='新闻';

-- ----------------------------
-- Records of system_news
-- ----------------------------
INSERT INTO `system_news` VALUES ('1', '金袋子网贷系统正式发售', '金袋子网贷系统正式发售', '&lt;div&gt;福建汇诚普惠金融信息服务有限公司成立于2014年，注册资金5000万元，办公地址位于台江金融街--升龙汇金中心 &lt;/div&gt;&lt;div&gt; &amp;nbsp; &amp;nbsp;我司是一家专业从事汽车抵（质）押贷款等金融服务的P2P网络平台。早在数年前公司即专注于汽车抵（质）押贷款等服务，并凭借其强大的实力，深厚的背景，专业的管理和安全可靠、互利共赢的理念，成为福建本地抵押贷款行业里的一颗耀眼明珠。随着互联网P2P行业的兴起，公司投入巨资，强势进军互联网，立志打造成为最专业的P2P汽车抵（质）押贷款服务平台。&lt;/div&gt;&lt;div&gt; &amp;nbsp; &amp;nbsp;实力见证发展，合作保障安全。公司成立之初，为扩大平台影响力，公司专门拨出500万元人民币的风险拨备金，交由招商银行托管。公司与第三方支付平台--上海汇潮信息技术有限公司（简称上海汇潮支付）结成长期战略合作伙伴关系，投资者在平台交易的全部资金都由汇潮支付进行第三方支付并进行全额托管，做到资金与平台充分隔离。同时，亚太排名第一的大成律师事务所提供全程安全顾问，公司还组建了一支由专家组成的专业风控团队，对平台的每一个项目都进行严格的风险评估，从而全方位的为投资者的资金安全保驾护航。 &lt;/div&gt;&lt;div&gt; &amp;nbsp; 管理创造效益，服务成就未来。公司拥有国内一流的专业运营管理团队，成员均为银行、互联网金融、P2P、投资等领域的专家人士，拥有超强的技术力量和丰富的运作经验。强大的实力和雄厚的背景使得公司有足够的信心和能力来满足客户的投资、理财要求。投资者在这里可以享受到轻松理财、快速盈利、提存便捷、安全无忧的五星级投资理财专业服务。 &lt;/div&gt;&lt;div&gt; &amp;nbsp;未来，公司将继续以风险风控为核心，以透明化、规范化运营为宗旨，以安全可靠、互利共赢为理念，力求为广大投资者搭建一个规范、透明、优质、诚信、的互联网金融服务平台，营造一种安全、高效、快捷、灵活的投资氛围和理财环境，以回报广大客户的支持和厚爱。&lt;/div&gt;', '2014-12-22 21:46:43', '0', 'admin', null, 'officenews', '金袋子,网贷,p2p,网络贷款,p2p软件');
INSERT INTO `system_news` VALUES ('2', '三三四四', '阿斯蒂芬撒撒反对', '&lt;p&gt;斯蒂芬添加新闻&lt;/p&gt;', '2015-05-05 11:29:41', '0', 'admin', null, 'officenews', '');
INSERT INTO `system_news` VALUES ('3', '闪亮的方式', '是个打工噶阿斯蒂芬撒算的', '&lt;p&gt;斯蒂芬斯蒂芬多数发达&lt;/p&gt;', '2015-05-18 09:39:11', '0', 'admin', null, 'announce', '');
INSERT INTO `system_news` VALUES ('4', '关于赣州4S店签约质押车辆发标公告', '关于赣州4S店签约质押车辆发标公告', '&lt;p&gt;尊敬的汇诚普惠客户：&lt;/p&gt;&lt;p&gt;&lt;br /&gt;&lt;/p&gt;&lt;span id=&quot;__dyeditor_bookmark_start_0__&quot;&gt;&lt;/span&gt;&lt;p&gt;2015年5月26日汇诚普惠与赣州市XX贸易有限公司（XX4S店）签订车辆质押合同，车辆型号分别为：力帆320、力帆530、力帆720、帝豪EC7、英伦SC720、共计56台，借款金额200万元，计划于2015年5月28日发标，具体发标时间待定，敬请关注！&lt;span id=&quot;__dyeditor_bookmark_start_0__&quot;&gt;&lt;/span&gt;&lt;span id=&quot;__dyeditor_bookmark_end_1__&quot;&gt;&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;br /&gt;&lt;/p&gt;&lt;p&gt;福建汇诚普惠金融信息服务有限公司&lt;/p&gt;&lt;p&gt;客服部&lt;/p&gt;&lt;p&gt;2015年5月27日&lt;/p&gt;', '2015-06-02 20:56:48', '0', 'admin', null, 'officenews', '赣州,汇诚普惠,抵押车贷款');

-- ----------------------------
-- Table structure for system_news_comment
-- ----------------------------
DROP TABLE IF EXISTS `system_news_comment`;
CREATE TABLE `system_news_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentId` int(11) NOT NULL DEFAULT '0' COMMENT '上一级评论',
  `userId` varchar(20) NOT NULL DEFAULT '' COMMENT '用户ID',
  `newsId` int(11) NOT NULL DEFAULT '0' COMMENT '文章地址',
  `content` text COMMENT '评论内容',
  `addTime` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '评论时间',
  `addIp` varchar(50) NOT NULL DEFAULT '' COMMENT '评论IP',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '评论状态:0-不显示,1-显示',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='评论表';

-- ----------------------------
-- Records of system_news_comment
-- ----------------------------
INSERT INTO `system_news_comment` VALUES ('1', '0', '1000000002', '1', 'sdfsdfssdfssdfkjlsdlfjsdlfjlsdjlfjlsdjflsdljflsdfjlsd', '1970-01-01 08:00:00', '127.0.0.1', '1');

-- ----------------------------
-- Table structure for system_notice
-- ----------------------------
DROP TABLE IF EXISTS `system_notice`;
CREATE TABLE `system_notice` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `body` text,
  `title` varchar(20) DEFAULT NULL COMMENT '类型表中key',
  `user` varchar(20) DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='帮助信息列表';

-- ----------------------------
-- Records of system_notice
-- ----------------------------
INSERT INTO `system_notice` VALUES ('1', '&lt;p&gt;新公司成立2周年新公司成立2周年新公司成立2周年新公司成立2周年新公司成立2周年新公司成立2周年新公司成立2周年&lt;/p&gt;', '新公司成立2周年', 'admin', '2014-12-23 05:50:57');

-- ----------------------------
-- Table structure for system_office
-- ----------------------------
DROP TABLE IF EXISTS `system_office`;
CREATE TABLE `system_office` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `office_name` varchar(20) DEFAULT NULL,
  `office_adderss` varchar(200) DEFAULT NULL,
  `office_tel` varchar(20) DEFAULT NULL,
  `office_mobile` varchar(20) DEFAULT NULL,
  `office_email` varchar(40) DEFAULT NULL,
  `office_web` varchar(40) DEFAULT NULL,
  `office_icp` varchar(40) DEFAULT NULL COMMENT '备案号',
  `office_qq` varchar(20) DEFAULT NULL,
  `office_qq1` varchar(20) DEFAULT NULL,
  `office_time` varchar(20) DEFAULT NULL,
  `office_sina` varchar(20) DEFAULT NULL COMMENT '新浪微博',
  `office_2wei` varchar(255) DEFAULT NULL COMMENT '二位码地址',
  `user` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='网站公司信息';

-- ----------------------------
-- Records of system_office
-- ----------------------------
INSERT INTO `system_office` VALUES ('1', '小微时代', '福州市台江区鳌江路万达广场5A写字楼5楼', '0796-2067989', '400-886-887', 'admin@xinfeiyou.com', 'www.hcjrfw.com', '闽ICP备14018647号', '81457554', '81457555', '09:00~18:30', '小微时代', 'http://sta.quchaogu.com/amd/common/img/wx.png', 'admin');

-- ----------------------------
-- Table structure for system_order
-- ----------------------------
DROP TABLE IF EXISTS `system_order`;
CREATE TABLE `system_order` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '姓名',
  `phone` varchar(50) NOT NULL DEFAULT '' COMMENT '手机号',
  `province` tinyint(1) NOT NULL DEFAULT '0' COMMENT '所在省份',
  `city` tinyint(1) NOT NULL DEFAULT '0' COMMENT '所在城市',
  `need_money` int(4) NOT NULL DEFAULT '0' COMMENT '借款金额',
  `add_time` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '添加时间',
  `status` enum('0','1') NOT NULL DEFAULT '0' COMMENT '0-未处理,1-已回访',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='预约借款表';

-- ----------------------------
-- Records of system_order
-- ----------------------------
INSERT INTO `system_order` VALUES ('1', '关键词', '18760419185', '3', '2', '100', '2015-06-02 13:46:28', '0');
INSERT INTO `system_order` VALUES ('2', '廖金灵', '18760419185', '7', '5', '100000', '2015-06-02 13:48:31', '0');

-- ----------------------------
-- Table structure for system_power
-- ----------------------------
DROP TABLE IF EXISTS `system_power`;
CREATE TABLE `system_power` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `powerGroup` varchar(20) DEFAULT NULL COMMENT '组名',
  `powerStr` text COMMENT '权限字符串',
  `addtime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_power
-- ----------------------------
INSERT INTO `system_power` VALUES ('1', '管理员', 'news_addNews,news_setNewsType,news_listNews,banner_list,banner_add,banner_typelist,banner_typeadd,video_areaAdd,video_areaList,video_add,video_list,newsComment_listNewsComment,loan_oddTrial,loan_oddRehear,loan_listSearch,loan_listOutflow,loan_oddSend,invest_listClaims,invest_listSearch,invest_userMonerySend,invest_sendClaims,invest_queue,loan_listOverdue,loan_listOverdueUser,workLog_moneyLog,workLog_queueLog,workLog_systemLog,workLog_workLog,user_listApprove,user_listUser,user_listMessage,user_listAutoLoan,user_listQueueLoan,webmail_webmailList,webmail_addWebmail,spread_users,workFlowRun_workDestroy,workFlowRun_workCommit,workFlowRun_workControl,workFlowRun_workQuery,workFlowRun_waitHandle,workFlowRun_index,workFlowSet_tableSort,workFlowSet_tableSet,workFlowSet_index,workFlowSet_flowSort,orgSet_unitSet,orgSet_userGroup,orgSet_userPriv,orgSet_user,orgSet_dept,expand_setEmail,expand_qqLogin,info_addInfo,info_addNotice,info_addInfo,info_addWork,info_addLink,info_addInfo,info_addInfo,info_addInfo,info_powerSet,sys_setSysType,sys_setSysTypeUrl,index_logout,index_deleteCache,sys_loadSysType,info_addContact,attribute_list', '2015-06-12 12:40:45');

-- ----------------------------
-- Table structure for system_poweruser
-- ----------------------------
DROP TABLE IF EXISTS `system_poweruser`;
CREATE TABLE `system_poweruser` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(20) DEFAULT NULL COMMENT '用户名',
  `powerId` int(20) DEFAULT NULL COMMENT '权限ID',
  `addtime` datetime DEFAULT NULL,
  `powerPriv` text COMMENT '最终权限',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_poweruser
-- ----------------------------
INSERT INTO `system_poweruser` VALUES ('1', 'admin', '1', '2015-06-12 12:40:56', 'news_addNews,news_setNewsType,news_listNews,banner_list,banner_add,banner_typelist,banner_typeadd,video_areaAdd,video_areaList,video_add,video_list,newsComment_listNewsComment,loan_oddTrial,loan_oddRehear,loan_listSearch,loan_listOutflow,loan_oddSend,invest_listClaims,invest_listSearch,invest_userMonerySend,invest_sendClaims,invest_queue,loan_listOverdue,loan_listOverdueUser,workLog_moneyLog,workLog_queueLog,workLog_systemLog,workLog_workLog,user_listApprove,user_listUser,user_listMessage,user_listAutoLoan,user_listQueueLoan,webmail_webmailList,webmail_addWebmail,spread_users,workFlowRun_workDestroy,workFlowRun_workCommit,workFlowRun_workControl,workFlowRun_workQuery,workFlowRun_waitHandle,workFlowRun_index,workFlowSet_tableSort,workFlowSet_tableSet,workFlowSet_index,workFlowSet_flowSort,orgSet_unitSet,orgSet_userGroup,orgSet_userPriv,orgSet_user,orgSet_dept,expand_setEmail,expand_qqLogin,info_addInfo,info_addNotice,info_addInfo,info_addWork,info_addLink,info_addInfo,info_addInfo,info_addInfo,info_powerSet,sys_setSysType,sys_setSysTypeUrl,index_logout,index_deleteCache,sys_loadSysType,info_addContact,attribute_list');

-- ----------------------------
-- Table structure for system_setsystype
-- ----------------------------
DROP TABLE IF EXISTS `system_setsystype`;
CREATE TABLE `system_setsystype` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `type_value` varchar(20) DEFAULT NULL,
  `type_root` int(20) DEFAULT NULL,
  `type_small` int(20) DEFAULT NULL,
  `type_rank` int(20) DEFAULT '0' COMMENT '排列位置',
  `type_url` varchar(100) DEFAULT NULL COMMENT '菜单链接',
  `type_openwind` enum('1','0') DEFAULT '0',
  `type_priv` varchar(30) DEFAULT NULL COMMENT '权限标志符',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=146 DEFAULT CHARSET=utf8 COMMENT='后台导航条信息';

-- ----------------------------
-- Records of system_setsystype
-- ----------------------------
INSERT INTO `system_setsystype` VALUES ('1', '我的办公桌', '0', '0', '1', null, '0', null);
INSERT INTO `system_setsystype` VALUES ('2', '文章管理', '0', '0', '2', null, '0', null);
INSERT INTO `system_setsystype` VALUES ('3', '借款管理', '0', '0', '4', null, '0', null);
INSERT INTO `system_setsystype` VALUES ('4', '客户管理', '0', '0', '6', null, '0', null);
INSERT INTO `system_setsystype` VALUES ('5', '系统管理', '0', '0', '10', null, '0', null);
INSERT INTO `system_setsystype` VALUES ('6', '系统管理', '5', '0', '0', '/admin.php?module=info&action=addInfo&typekey=bzzx', '1', null);
INSERT INTO `system_setsystype` VALUES ('7', '默认菜单', '5', '0', '0', '/admin.php?module=info&action=addInfo&typekey=xfbz', '1', null);
INSERT INTO `system_setsystype` VALUES ('84', '系统菜单', '5', '7', '0', '/admin.php?module=sys&action=setSysType', '0', 'sys_setSysType');
INSERT INTO `system_setsystype` VALUES ('85', '链接设置', '5', '7', '0', '/admin.php?module=sys&action=setSysTypeUrl', '0', 'sys_setSysTypeUrl');
INSERT INTO `system_setsystype` VALUES ('10', '借款管理', '3', '0', '0', '/admin.php?module=loan&action=oddTrial&status=y', '1', null);
INSERT INTO `system_setsystype` VALUES ('11', '投资管理', '3', '0', '0', '/admin.php?module=loan&action=oddRehear&status=y', '1', null);
INSERT INTO `system_setsystype` VALUES ('106', '资料管理', '4', '17', '0', '/admin.php?module=user&action=listApprove', '0', 'user_listApprove');
INSERT INTO `system_setsystype` VALUES ('105', '客户列表', '4', '17', '0', '/admin.php?module=user&action=listUser', '0', 'user_listUser');
INSERT INTO `system_setsystype` VALUES ('14', '文章管理', '2', '0', '0', '/admin.php?module=news&action=listNews', '1', null);
INSERT INTO `system_setsystype` VALUES ('17', '客户管理', '4', '0', '0', '/admin.php?module=user&action=listUser', '1', null);
INSERT INTO `system_setsystype` VALUES ('86', '退出系统', '5', '7', '0', '/admin.php?module=index&action=logout', '0', 'index_logout');
INSERT INTO `system_setsystype` VALUES ('103', '通知接口', '28', '29', '0', '/admin.php?module=expand&action=setEmail', '0', 'expand_setEmail');
INSERT INTO `system_setsystype` VALUES ('104', '联合登录', '28', '29', '0', '/admin.php?module=expand&action=qqLogin', '0', 'expand_qqLogin');
INSERT INTO `system_setsystype` VALUES ('87', '清除缓存', '5', '7', '0', '/admin.php?module=index&action=deleteCache', '0', 'index_deleteCache');
INSERT INTO `system_setsystype` VALUES ('108', '推广管理', '0', '0', '7', null, '0', null);
INSERT INTO `system_setsystype` VALUES ('24', '常用工具', '1', '0', '0', '/admin.php?module=user&action=listMessage', '1', null);
INSERT INTO `system_setsystype` VALUES ('31', '报表管理', '0', '0', '5', null, '0', null);
INSERT INTO `system_setsystype` VALUES ('88', '更新导航', '5', '7', '0', '/admin.php?module=sys&action=loadSysType', '0', 'sys_loadSysType');
INSERT INTO `system_setsystype` VALUES ('89', '公司信息', '5', '7', '0', '/admin.php?module=info&action=addContact', '0', 'info_addContact');
INSERT INTO `system_setsystype` VALUES ('28', '扩展设置', '0', '0', '9', null, '0', null);
INSERT INTO `system_setsystype` VALUES ('29', '扩展设置', '28', '0', '0', '/admin.php?module=expand&action=setEmail', '1', null);
INSERT INTO `system_setsystype` VALUES ('101', '债权转让', '3', '11', '0', '/admin.php?module=invest&action=listClaims', '0', 'invest_listClaims');
INSERT INTO `system_setsystype` VALUES ('102', '投资查询', '3', '11', '0', '/admin.php?module=invest&action=listSearch', '0', 'invest_listSearch');
INSERT INTO `system_setsystype` VALUES ('70', '表单分类', '55', '57', '0', '/admin.php?module=workFlowSet&action=tableSort', '0', 'workFlowSet_tableSort');
INSERT INTO `system_setsystype` VALUES ('69', '工作销毁', '55', '58', '0', '/admin.php?module=workFlowRun&action=workDestroy', '0', 'workFlowRun_workDestroy');
INSERT INTO `system_setsystype` VALUES ('68', '工作委托', '55', '58', '0', '/admin.php?module=workFlowRun&action=workCommit', '0', 'workFlowRun_workCommit');
INSERT INTO `system_setsystype` VALUES ('67', '工作监控', '55', '58', '0', '/admin.php?module=workFlowRun&action=workControl', '0', 'workFlowRun_workControl');
INSERT INTO `system_setsystype` VALUES ('66', '工作查询', '55', '58', '0', '/admin.php?module=workFlowRun&action=workQuery', '0', 'workFlowRun_workQuery');
INSERT INTO `system_setsystype` VALUES ('65', '待办工作', '55', '58', '0', '/admin.php?module=workFlowRun&action=waitHandle', '0', 'workFlowRun_waitHandle');
INSERT INTO `system_setsystype` VALUES ('64', '新建工作', '55', '58', '0', '/admin.php?module=workFlowRun&action=index', '0', 'workFlowRun_index');
INSERT INTO `system_setsystype` VALUES ('63', '单位设置', '55', '56', '0', '/admin.php?module=orgSet&action=unitSet', '0', 'orgSet_unitSet');
INSERT INTO `system_setsystype` VALUES ('62', '公共自定义组', '55', '56', '0', '/admin.php?module=orgSet&action=userGroup', '0', 'orgSet_userGroup');
INSERT INTO `system_setsystype` VALUES ('61', '角色权限', '55', '56', '0', '/admin.php?module=orgSet&action=userPriv', '0', 'orgSet_userPriv');
INSERT INTO `system_setsystype` VALUES ('60', '用户管理', '55', '56', '0', '/admin.php?module=orgSet&action=user', '0', 'orgSet_user');
INSERT INTO `system_setsystype` VALUES ('59', '部门管理', '55', '56', '0', '/admin.php?module=orgSet&action=dept', '0', 'orgSet_dept');
INSERT INTO `system_setsystype` VALUES ('58', '工作办理', '55', '0', '0', null, '0', null);
INSERT INTO `system_setsystype` VALUES ('57', '工作流设置', '55', '0', '0', null, '0', null);
INSERT INTO `system_setsystype` VALUES ('56', '组织机构设置', '55', '0', '0', null, '0', null);
INSERT INTO `system_setsystype` VALUES ('55', '工作流管理', '0', '0', '8', null, '0', null);
INSERT INTO `system_setsystype` VALUES ('71', '设计表单', '55', '57', '0', '/admin.php?module=workFlowSet&action=tableSet', '0', 'workFlowSet_tableSet');
INSERT INTO `system_setsystype` VALUES ('72', '设计流程', '55', '57', '0', '/admin.php?module=workFlowSet&action=index', '0', 'workFlowSet_index');
INSERT INTO `system_setsystype` VALUES ('73', '流程分类管理', '55', '57', '0', '/admin.php?module=workFlowSet&action=flowSort', '0', 'workFlowSet_flowSort');
INSERT INTO `system_setsystype` VALUES ('74', '客户留言', '1', '24', '0', '/admin.php?module=user&action=listMessage', '0', 'user_listMessage');
INSERT INTO `system_setsystype` VALUES ('75', '帮助中心', '5', '6', '0', '/admin.php?module=info&action=addInfo&typekey=bzzx', '0', 'info_addInfo');
INSERT INTO `system_setsystype` VALUES ('77', '公司公告', '5', '6', '0', '/admin.php?module=info&action=addNotice', '0', 'info_addNotice');
INSERT INTO `system_setsystype` VALUES ('78', '关于我们', '5', '6', '0', '/admin.php?module=info&action=addInfo&typekey=gywm', '0', 'info_addInfo');
INSERT INTO `system_setsystype` VALUES ('79', '合作商', '5', '6', '0', '/admin.php?module=info&action=addWork', '0', 'info_addWork');
INSERT INTO `system_setsystype` VALUES ('80', '友情链接', '5', '6', '0', '/admin.php?module=info&action=addLink', '0', 'info_addLink');
INSERT INTO `system_setsystype` VALUES ('81', '招贤纳士', '5', '6', '0', '/admin.php?module=info&action=addInfo&typekey=zxns', '0', 'info_addInfo');
INSERT INTO `system_setsystype` VALUES ('82', '联系我们', '5', '6', '0', '/admin.php?module=info&action=addInfo&typekey=lxwm', '0', 'info_addInfo');
INSERT INTO `system_setsystype` VALUES ('83', '消费保障', '5', '6', '0', '/admin.php?module=info&action=addInfo&typekey=xfbz', '0', 'info_addInfo');
INSERT INTO `system_setsystype` VALUES ('91', '文章添加', '2', '14', '0', '/admin.php?module=news&action=addNews', '0', 'news_addNews');
INSERT INTO `system_setsystype` VALUES ('90', '文章类别', '2', '14', '0', '/admin.php?module=news&action=setNewsType', '0', 'news_setNewsType');
INSERT INTO `system_setsystype` VALUES ('92', '文章列表', '2', '14', '0', '/admin.php?module=news&action=listNews', '0', 'news_listNews');
INSERT INTO `system_setsystype` VALUES ('94', '初审借款', '3', '10', '0', '/admin.php?module=loan&action=oddTrial&status=y', '0', 'loan_oddTrial');
INSERT INTO `system_setsystype` VALUES ('95', '复审借款', '3', '10', '0', '/admin.php?module=loan&action=oddRehear&status=y', '0', 'loan_oddRehear');
INSERT INTO `system_setsystype` VALUES ('96', '借款查询', '3', '10', '0', '/admin.php?module=loan&action=listSearch', '0', 'loan_listSearch');
INSERT INTO `system_setsystype` VALUES ('97', '流标查询', '3', '10', '0', '/admin.php?module=loan&action=listOutflow', '0', 'loan_listOutflow');
INSERT INTO `system_setsystype` VALUES ('115', '自动投标', '4', '113', '0', '/admin.php?module=user&action=listAutoLoan', '0', 'user_listAutoLoan');
INSERT INTO `system_setsystype` VALUES ('113', '设置管理', '4', '0', '0', null, '0', null);
INSERT INTO `system_setsystype` VALUES ('114', '排队查询', '4', '113', '0', '/admin.php?module=user&action=listQueueLoan', '0', 'user_listQueueLoan');
INSERT INTO `system_setsystype` VALUES ('100', '代发借款', '3', '10', '0', '/admin.php?module=loan&action=oddSend', '0', 'loan_oddSend');
INSERT INTO `system_setsystype` VALUES ('107', '客户留言', '4', '17', '0', '/admin.php?module=user&action=listMessage', '0', 'user_listMessage');
INSERT INTO `system_setsystype` VALUES ('109', '手工投标', '3', '11', '0', '/admin.php?module=invest&action=userMonerySend', '0', 'invest_userMonerySend');
INSERT INTO `system_setsystype` VALUES ('110', '还款管理', '3', '0', '0', null, '0', null);
INSERT INTO `system_setsystype` VALUES ('111', '逾期查询', '3', '110', '0', '/admin.php?module=loan&action=listOverdue', '0', 'loan_listOverdue');
INSERT INTO `system_setsystype` VALUES ('112', '逾期会员', '3', '110', '0', '/admin.php?module=loan&action=listOverdueUser', '0', 'loan_listOverdueUser');
INSERT INTO `system_setsystype` VALUES ('116', '评论管理', '0', '0', '3', null, '0', null);
INSERT INTO `system_setsystype` VALUES ('117', '评论管理', '116', '0', '0', null, '0', null);
INSERT INTO `system_setsystype` VALUES ('118', '评论列表', '116', '117', '0', '/admin.php?module=newsComment&action=listNewsComment', '0', 'newsComment_listNewsComment');
INSERT INTO `system_setsystype` VALUES ('119', '权限设置', '5', '6', '0', '/admin.php?module=info&action=powerSet', '0', 'info_powerSet');
INSERT INTO `system_setsystype` VALUES ('120', '推广管理', '108', '0', '0', null, '0', null);
INSERT INTO `system_setsystype` VALUES ('121', '推广用户列表', '108', '120', '0', '/admin.php?module=spread&action=users', '0', 'spread_users');
INSERT INTO `system_setsystype` VALUES ('122', '手工转让', '3', '11', '0', '/admin.php?module=invest&action=sendClaims', '0', 'invest_sendClaims');
INSERT INTO `system_setsystype` VALUES ('124', '站内信', '4', '0', '0', null, '0', null);
INSERT INTO `system_setsystype` VALUES ('125', '站内信列表', '4', '124', '0', '/admin.php?module=webmail&action=webmailList', '0', 'webmail_webmailList');
INSERT INTO `system_setsystype` VALUES ('126', '发送站内信', '4', '124', '0', '/admin.php?module=webmail&action=addWebmail', '0', 'webmail_addWebmail');
INSERT INTO `system_setsystype` VALUES ('127', '轮播图管理', '2', '0', '0', null, '0', null);
INSERT INTO `system_setsystype` VALUES ('128', '轮播图列表', '2', '127', '0', '/admin.php?module=banner&action=list', '0', 'banner_list');
INSERT INTO `system_setsystype` VALUES ('129', '轮播图添加', '2', '127', '0', '/admin.php?module=banner&action=add', '0', 'banner_add');
INSERT INTO `system_setsystype` VALUES ('130', '轮播图类别', '2', '127', '0', '/admin.php?module=banner&action=typelist', '0', 'banner_typelist');
INSERT INTO `system_setsystype` VALUES ('131', '轮播图类别添加', '2', '127', '0', '/admin.php?module=banner&action=typeadd', '0', 'banner_typeadd');
INSERT INTO `system_setsystype` VALUES ('132', '车库视频管理', '2', '0', '0', null, '0', null);
INSERT INTO `system_setsystype` VALUES ('133', '添加地区', '2', '132', '0', '/admin.php?module=video&action=areaAdd', '0', 'video_areaAdd');
INSERT INTO `system_setsystype` VALUES ('134', '地区列表', '2', '132', '0', '/admin.php?module=video&action=areaList', '0', 'video_areaList');
INSERT INTO `system_setsystype` VALUES ('135', '添加视频', '2', '132', '0', '/admin.php?module=video&action=add', '0', 'video_add');
INSERT INTO `system_setsystype` VALUES ('136', '视频列表', '2', '132', '0', '/admin.php?module=video&action=list', '0', 'video_list');
INSERT INTO `system_setsystype` VALUES ('137', '系统变量', '5', '7', '0', '/admin.php?module=attribute&action=list', '0', 'attribute_list');
INSERT INTO `system_setsystype` VALUES ('138', '日志管理', '3', '0', '0', null, '0', null);
INSERT INTO `system_setsystype` VALUES ('139', '资金日志', '3', '138', '0', '/admin.php?module=workLog&action=moneyLog', '0', 'workLog_moneyLog');
INSERT INTO `system_setsystype` VALUES ('140', '队列日志', '3', '138', '0', '/admin.php?module=workLog&action=queueLog', '0', 'workLog_queueLog');
INSERT INTO `system_setsystype` VALUES ('141', '系统日志', '3', '138', '0', '/admin.php?module=workLog&action=systemLog', '0', 'workLog_systemLog');
INSERT INTO `system_setsystype` VALUES ('142', '任务日志', '3', '138', '0', '/admin.php?module=workLog&action=workLog', '0', 'workLog_workLog');
INSERT INTO `system_setsystype` VALUES ('143', '队列情况', '3', '11', '0', '/admin.php?module=invest&action=queue', '0', 'invest_queue');

-- ----------------------------
-- Table structure for system_settype
-- ----------------------------
DROP TABLE IF EXISTS `system_settype`;
CREATE TABLE `system_settype` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `type_value` varchar(20) DEFAULT NULL,
  `type_root` int(20) DEFAULT NULL,
  `type_small` int(20) DEFAULT NULL,
  `type_rank` int(20) DEFAULT '0' COMMENT '排列位置',
  `type_url` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COMMENT='商品分类';

-- ----------------------------
-- Records of system_settype
-- ----------------------------
INSERT INTO `system_settype` VALUES ('1', '925纯银饰品', '0', '0', '0', null);
INSERT INTO `system_settype` VALUES ('2', '水晶饰品', '0', '0', '0', null);
INSERT INTO `system_settype` VALUES ('3', '925纯银耳环', '1', '0', '0', null);
INSERT INTO `system_settype` VALUES ('4', '925纯银项链', '1', '0', '0', null);
INSERT INTO `system_settype` VALUES ('5', '925纯银耳钉', '1', '0', '0', null);
INSERT INTO `system_settype` VALUES ('6', '925纯银戒指', '1', '0', '0', null);
INSERT INTO `system_settype` VALUES ('7', '925纯银手链', '1', '0', '0', null);
INSERT INTO `system_settype` VALUES ('8', '水晶项链', '2', '0', '0', null);
INSERT INTO `system_settype` VALUES ('9', '水晶手链', '2', '0', '0', null);
INSERT INTO `system_settype` VALUES ('10', '水晶耳饰', '2', '0', '0', null);
INSERT INTO `system_settype` VALUES ('11', '水晶戒指', '2', '0', '0', null);
INSERT INTO `system_settype` VALUES ('12', '水晶胸针', '2', '0', '0', null);
INSERT INTO `system_settype` VALUES ('13', '水晶头饰', '2', '0', '0', null);
INSERT INTO `system_settype` VALUES ('14', '水晶套装', '2', '0', '0', null);
INSERT INTO `system_settype` VALUES ('15', '水晶袖扣', '2', '0', '0', null);

-- ----------------------------
-- Table structure for system_smslog
-- ----------------------------
DROP TABLE IF EXISTS `system_smslog`;
CREATE TABLE `system_smslog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(20) NOT NULL DEFAULT '' COMMENT '发送用户ID',
  `phone` varchar(50) NOT NULL DEFAULT '' COMMENT '发送手机号',
  `content` varchar(250) NOT NULL DEFAULT '' COMMENT '发送内容',
  `sendTime` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '发送时间',
  `result` varchar(50) NOT NULL DEFAULT '' COMMENT '返回结果',
  `type` varchar(50) NOT NULL DEFAULT '' COMMENT '短信类型',
  `sendCode` varchar(50) NOT NULL DEFAULT '' COMMENT '短信验证码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COMMENT='短信日志表';

-- ----------------------------
-- Records of system_smslog
-- ----------------------------
INSERT INTO `system_smslog` VALUES ('1', '1000000003', '18760419185', '随便发送【汇诚普惠】', '2015-04-29 13:13:58', '821053724', '', '');
INSERT INTO `system_smslog` VALUES ('2', '1000000003', '18760419185', '随便发送【汇诚普惠】', '2015-04-29 13:29:50', '573919599', '', '');
INSERT INTO `system_smslog` VALUES ('3', '1000000003', '18760419185', '随便发送【汇诚普惠】', '2015-04-29 13:36:10', '714420672', '', '');
INSERT INTO `system_smslog` VALUES ('4', '1000000003', '18760419185', '随便发送【汇诚普惠】', '2015-04-29 13:46:34', '384110999', '', '');
INSERT INTO `system_smslog` VALUES ('5', '1000000003', '18760419185', '随便发送【汇诚普惠】', '2015-04-29 13:54:52', '835539903', '', '');
INSERT INTO `system_smslog` VALUES ('6', '1000000003', '18760419185', '【汇诚普惠】', '2015-04-30 10:09:35', '16430762', '', '');
INSERT INTO `system_smslog` VALUES ('7', '', '18760419185', '您正在执行注册操作，验证码是082983。【汇诚普惠】', '2015-05-28 16:06:11', '888209123', '', '');
INSERT INTO `system_smslog` VALUES ('8', '', '18760419185', '您正在执行注册操作，验证码是033154。【汇诚普惠】', '2015-05-28 16:07:49', '727230806', '', '');
INSERT INTO `system_smslog` VALUES ('9', '', '18760419185', '您正在执行注册操作，验证码是979126。【汇诚普惠】', '2015-05-28 16:21:22', '63568757', '', '');
INSERT INTO `system_smslog` VALUES ('10', '', '18760419185', '您正在执行注册操作，验证码是020670。【汇诚普惠】', '2015-05-28 16:29:42', '931713534', '', '');
INSERT INTO `system_smslog` VALUES ('11', '', '18760419185', '您正在执行注册操作，验证码是584119。【汇诚普惠】', '2015-05-28 17:25:45', '899550979', '', '');
INSERT INTO `system_smslog` VALUES ('12', '', '18760419185', '您正在执行注册操作，验证码是562105。【汇诚普惠】', '2015-05-28 17:28:14', '550647231', '', '');
INSERT INTO `system_smslog` VALUES ('13', '', '18760419185', '您正在执行注册操作，验证码是016367。【汇诚普惠】', '2015-05-28 18:19:57', '882348190', '', '016367');
INSERT INTO `system_smslog` VALUES ('14', '', '18760419185', '您正在执行注册操作，验证码是624126。【汇诚普惠】', '2015-05-28 18:24:59', '930190392', 'register', '624126');
INSERT INTO `system_smslog` VALUES ('15', '', '18760419185', '您正在执行注册操作，验证码是655506。【汇诚普惠】', '2015-05-28 18:31:11', '892228972', 'register', '655506');
INSERT INTO `system_smslog` VALUES ('16', '', '18760419185', '您正在执行注册操作，验证码是413406。【汇诚普惠】', '2015-05-29 09:33:40', '808751567', 'register', '413406');
INSERT INTO `system_smslog` VALUES ('17', '', '18760419185', '您正在执行注册操作，验证码是456784。【汇诚普惠】', '2015-05-29 09:37:56', '474557064', 'register', '456784');
INSERT INTO `system_smslog` VALUES ('18', '', '18760419185', '您正在执行注册操作，验证码是997406。【汇诚普惠】', '2015-05-29 10:27:34', '872563099', 'register', '997406');
INSERT INTO `system_smslog` VALUES ('19', '', '18760419185', '您正在执行注册操作，验证码是497546。【汇诚普惠】', '2015-05-29 11:15:42', '3063218', 'register', '497546');
INSERT INTO `system_smslog` VALUES ('20', '', '13799998027', '您正在执行注册操作，验证码是243108。【汇诚普惠】', '2015-05-29 11:55:57', '248970091', 'register', '243108');

-- ----------------------------
-- Table structure for system_type
-- ----------------------------
DROP TABLE IF EXISTS `system_type`;
CREATE TABLE `system_type` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `type_name` varchar(20) DEFAULT NULL,
  `type_key` varchar(20) DEFAULT NULL,
  `type_ck` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='网站介绍其他分类';

-- ----------------------------
-- Records of system_type
-- ----------------------------
INSERT INTO `system_type` VALUES ('1', '帮助中心', 'bzzx', 'web');
INSERT INTO `system_type` VALUES ('2', '消费保障', 'xfbz', 'web');
INSERT INTO `system_type` VALUES ('3', '联系我们', 'lxwm', 'web');
INSERT INTO `system_type` VALUES ('4', '招贤纳士', 'zxns', 'web');
INSERT INTO `system_type` VALUES ('5', '关于我们', 'gywm', 'web');
INSERT INTO `system_type` VALUES ('6', '公司公告', 'gsgg', 'web');
INSERT INTO `system_type` VALUES ('7', '公司新闻', 'officenews', 'news');
INSERT INTO `system_type` VALUES ('8', '行业新闻', 'hangyenew', 'news');
INSERT INTO `system_type` VALUES ('11', '网站公告', 'notice', 'news');
INSERT INTO `system_type` VALUES ('10', '投标预告', 'announce', 'news');

-- ----------------------------
-- Table structure for system_user_webmail
-- ----------------------------
DROP TABLE IF EXISTS `system_user_webmail`;
CREATE TABLE `system_user_webmail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `webmailId` int(11) NOT NULL DEFAULT '0' COMMENT '站内信ID',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '信件状态:0-未读,1-已读,2-删除',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COMMENT='用户站内信';

-- ----------------------------
-- Records of system_user_webmail
-- ----------------------------
INSERT INTO `system_user_webmail` VALUES ('18', '3', '18760419185', '0');
INSERT INTO `system_user_webmail` VALUES ('19', '4', '18760419185', '0');
INSERT INTO `system_user_webmail` VALUES ('20', '6', '18760419185', '0');
INSERT INTO `system_user_webmail` VALUES ('21', '7', '18760419185', '0');
INSERT INTO `system_user_webmail` VALUES ('22', '5', '18760419185', '0');
INSERT INTO `system_user_webmail` VALUES ('23', '9', '18760419185', '0');
INSERT INTO `system_user_webmail` VALUES ('24', '5', 'liuch', '0');

-- ----------------------------
-- Table structure for system_userinfo
-- ----------------------------
DROP TABLE IF EXISTS `system_userinfo`;
CREATE TABLE `system_userinfo` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `userId` varchar(20) DEFAULT NULL COMMENT '用户id暂时未用',
  `username` varchar(20) DEFAULT NULL COMMENT '用户',
  `userimg` varchar(400) DEFAULT NULL COMMENT '用户头像',
  `phone` varchar(12) DEFAULT NULL COMMENT '手机号码',
  `phonestatus` enum('y','n') DEFAULT 'n' COMMENT '手机认证状态',
  `loginpass` varchar(32) DEFAULT NULL COMMENT '登录密码',
  `paypass` varchar(32) DEFAULT NULL COMMENT '支付密码',
  `friendkey` varchar(20) DEFAULT NULL COMMENT '密码加密Key',
  `name` varchar(10) DEFAULT NULL COMMENT '真实姓名',
  `cardnum` varchar(20) DEFAULT NULL COMMENT '身份证号',
  `cardimgz` varchar(100) DEFAULT NULL COMMENT '身份证正面',
  `cardimgf` varchar(100) DEFAULT NULL COMMENT '身份证反面',
  `cardstatus` enum('y','n') DEFAULT 'n' COMMENT '身份证是否认证',
  `email` varchar(40) DEFAULT NULL COMMENT '邮箱',
  `emailstatus` enum('y','n') DEFAULT 'n' COMMENT '邮箱是否认证',
  `sex` enum('man','women') DEFAULT 'man',
  `ethnic` varchar(10) DEFAULT NULL COMMENT '种族',
  `birth` date DEFAULT NULL COMMENT '生日',
  `city` varchar(20) DEFAULT NULL COMMENT '城市',
  `adder` varchar(100) DEFAULT NULL COMMENT '地址',
  `maritalstatus` enum('y','n') DEFAULT 'n' COMMENT '是否结婚',
  `child` enum('y','n') DEFAULT 'n' COMMENT '有无子女',
  `educational` varchar(20) DEFAULT NULL COMMENT '学历',
  `income` varchar(20) DEFAULT NULL COMMENT '收入',
  `social` enum('y','n') DEFAULT 'n' COMMENT '有无社保',
  `socialnum` varchar(40) DEFAULT NULL COMMENT '社保号',
  `housing` varchar(20) DEFAULT NULL COMMENT '住房条件',
  `buycar` enum('y','n') DEFAULT 'n' COMMENT '是否购车',
  `overdue` enum('y','n') DEFAULT 'n' COMMENT '逾期记录',
  `fundMoney` double NOT NULL DEFAULT '0' COMMENT '投资金额',
  `investMoney` double NOT NULL DEFAULT '0' COMMENT '已投资金额',
  `frozenMoney` double DEFAULT '0' COMMENT '冻结金额',
  `vip` varchar(20) DEFAULT NULL COMMENT 'VIP等级',
  `addtime` datetime DEFAULT NULL COMMENT '添加时间',
  `userlinkstatus` enum('n','y') DEFAULT 'n' COMMENT '是否填写紧急联系人',
  `userofficestatus` enum('n','y') DEFAULT 'n' COMMENT '是否填写单位信息',
  `userhousestatus` enum('n','y') DEFAULT 'n' COMMENT '是否填写房产信息',
  `creditMonery` double DEFAULT '0' COMMENT '信用额度',
  `mortgageMonery` double DEFAULT '0' COMMENT '抵押额度',
  `guaranteeMonery` double DEFAULT '0' COMMENT '担保额度',
  `status` enum('1','0','-1') DEFAULT '1' COMMENT '1正常，0锁定，-1失效',
  `codeInput` char(6) DEFAULT NULL COMMENT '手机验证码',
  `imiUser` varchar(20) DEFAULT NULL COMMENT '模拟账户',
  `imiMoney` double DEFAULT '100000' COMMENT '模拟资金',
  `imiFreezeMoney` double DEFAULT '0' COMMENT '冻结资金',
  `spreadCode` varchar(20) NOT NULL DEFAULT '' COMMENT '推广码',
  `thirdAccountStatus` enum('0','1') NOT NULL DEFAULT '0' COMMENT '是否开通第三方托管',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_userinfo
-- ----------------------------
INSERT INTO `system_userinfo` VALUES ('1', '1508000', 'admin', '\\public\\flash\\data\\1000000001_big.jpg', '18894141666', 'y', '510c6dd641117bcaadb40627cec8ddd5', '314d84442451813e9721d99a9c77c283', '825kg', '商家', '362428198208150074', null, null, 'y', 'admin@xinfeiyou.com', 'y', 'man', '汉族', '1982-08-15', '福建省-福州市-台江区', '光明南路升龙汇金2306', 'n', 'n', '本科', '60000', 'n', null, '租房', 'n', 'n', '49460', '0', '0', '1', '2014-12-23 06:49:06', 'y', 'y', 'y', '100000', '100000', '100000', '1', '', '', '100000', '0', '', '0');
INSERT INTO `system_userinfo` VALUES ('2', '1000000001', 'xing654', '\\public\\flash\\data\\1000000001_big.jpg', '18894141663', 'y', '510c6dd641117bcaadb40627cec8ddd5', '314d84442451813e9721d99a9c77c283', '845kg', '刘祖一', '362428198208150074', null, null, 'y', 'admin@xinfeiyou.com', 'y', 'man', '汉族', '1982-08-15', '福建省-福州市-台江区', '光明南路升龙汇金2306', 'n', 'n', '本科', '60000', 'n', null, '租房', 'n', 'n', '0', '0', '0', '1', '2014-12-23 06:49:06', 'y', 'y', 'y', '100000', '0', '100000', '1', '4097', 'imi_xing654', '100000', '0', '', '0');
INSERT INTO `system_userinfo` VALUES ('3', '1000000002', 'liuch', null, '15679631084', 'y', 'aae78ea9f58357935342578ac7c3b0d7', 'aae78ea9f58357935342578ac7c3b0d7', '23284', '刘晨辉', '362428198208150077', null, null, 'y', 'xing654@163.com', 'y', 'man', '汉族', null, '江西省-吉安市-万安县', '东风路31号', 'n', 'n', '本科', '1600', 'n', null, null, 'n', 'n', '49800', '200', '0', '1', '2015-01-07 22:00:52', 'n', 'y', 'n', '0', '0', '0', '1', null, null, '100000', '0', '4f365909db63d481', '0');
INSERT INTO `system_userinfo` VALUES ('4', '1000000003', 'xiaoym', null, '15270067079', 'y', 'ca91d64b05c18cb17ebaaaddbe1083ff', 'ca91d64b05c18cb17ebaaaddbe1083ff', '123kg', '刘祖军', '362428198710164623', null, null, 'y', null, 'n', 'man', null, null, null, null, 'n', 'n', null, null, 'n', null, null, 'n', 'n', '50270', '10000', '0', '1', '2015-01-07 22:00:55', 'y', 'y', 'n', '0', '0', '0', '1', null, null, '100000', '0', '', '0');
INSERT INTO `system_userinfo` VALUES ('5', '1000000013', '18760419185', null, '18760419185', 'y', '3bd90886eae755d1f2c9f673ecbe7dc1', '3bd90886eae755d1f2c9f673ecbe7dc1', '05oc7', '廖金灵', '350824199001105476', null, null, 'y', null, 'n', 'man', null, '1990-01-10', null, null, 'n', 'n', null, null, 'n', null, null, 'n', 'n', '50470', '9800', '0', '1', '2015-05-27 10:44:00', 'n', 'n', 'n', '0', '0', '0', '1', null, null, '100000', '0', '4ebbda28c6c67945', '0');

-- ----------------------------
-- Table structure for system_video
-- ----------------------------
DROP TABLE IF EXISTS `system_video`;
CREATE TABLE `system_video` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `area_id` int(4) NOT NULL DEFAULT '0' COMMENT '地区ID',
  `link` varchar(150) NOT NULL DEFAULT '' COMMENT '视频地址',
  `cover` varchar(150) NOT NULL DEFAULT '' COMMENT '遮罩图片',
  `addtime` datetime NOT NULL DEFAULT '1970-01-01 08:00:00' COMMENT '添加时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态:0-不显示,1-显示',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='车库视频';

-- ----------------------------
-- Records of system_video
-- ----------------------------
INSERT INTO `system_video` VALUES ('1', 'ssss', '1', 'http://player.youku.com/player.php/Type/Folder/Fid/25764144/Ob/1/sid/XOTY1NjI1MDY0/v.swf', '143193690333486.jpg', '2015-05-18 16:15:03', '1');

-- ----------------------------
-- Table structure for system_video_area
-- ----------------------------
DROP TABLE IF EXISTS `system_video_area`;
CREATE TABLE `system_video_area` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='车库视频地区';

-- ----------------------------
-- Records of system_video_area
-- ----------------------------
INSERT INTO `system_video_area` VALUES ('1', '福州车库');
INSERT INTO `system_video_area` VALUES ('2', '宁德车库');
INSERT INTO `system_video_area` VALUES ('3', '厦门车库');

-- ----------------------------
-- Table structure for system_webad
-- ----------------------------
DROP TABLE IF EXISTS `system_webad`;
CREATE TABLE `system_webad` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `adimg` varchar(300) DEFAULT NULL COMMENT '图片地址',
  `adurl` varchar(100) DEFAULT NULL COMMENT '链接地址',
  `addtime` datetime DEFAULT NULL COMMENT '添加时间',
  `status` enum('0','1','-1') DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_webad
-- ----------------------------

-- ----------------------------
-- Table structure for system_webinfo
-- ----------------------------
DROP TABLE IF EXISTS `system_webinfo`;
CREATE TABLE `system_webinfo` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `body` longtext,
  `type_key` varchar(20) DEFAULT NULL COMMENT '类型表中key',
  `user` varchar(20) DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='帮助信息列表';

-- ----------------------------
-- Records of system_webinfo
-- ----------------------------
INSERT INTO `system_webinfo` VALUES ('1', '&lt;p&gt;帮助中心&lt;br /&gt;&lt;/p&gt;', 'bzzx', 'admin', '2014-01-12 19:38:48');
INSERT INTO `system_webinfo` VALUES ('2', '&lt;p&gt;消费保障&lt;/p&gt;', 'xfbz', 'admin', '2014-01-12 19:39:07');
INSERT INTO `system_webinfo` VALUES ('3', '&lt;p&gt;联系我们&lt;br /&gt;&lt;/p&gt;', 'lxwm', 'admin', '2014-01-12 19:39:19');
INSERT INTO `system_webinfo` VALUES ('4', '&lt;p&gt;招贤纳士&lt;/p&gt;', 'zxns', 'admin', '2014-01-12 19:39:31');
INSERT INTO `system_webinfo` VALUES ('5', '&lt;div&gt;福建汇诚普惠金融信息服务有限公司成立于2014年，注册资金5000万元，办公地址位于台江金融街--升龙汇金中心 &lt;/div&gt;&lt;div&gt; &amp;nbsp; &amp;nbsp;我司是一家专业从事汽车抵（质）押贷款等金融服务的P2P网络平台。早在数年前公司即专注于汽车抵（质）押贷款等服务，并凭借其强大的实力，深厚的背景，专业的管理和安全可靠、互利共赢的理念，成为福建本地抵押贷款行业里的一颗耀眼明珠。随着互联网P2P行业的兴起，公司投入巨资，强势进军互联网，立志打造成为最专业的P2P汽车抵（质）押贷款服务平台。&lt;/div&gt;&lt;div&gt; &amp;nbsp; &amp;nbsp;实力见证发展，合作保障安全。公司成立之初，为扩大平台影响力，公司专门拨出500万元人民币的风险拨备金，交由招商银行托管。公司与第三方支付平台--上海汇潮信息技术有限公司（简称上海汇潮支付）结成长期战略合作伙伴关系，投资者在平台交易的全部资金都由汇潮支付进行第三方支付并进行全额托管，做到资金与平台充分隔离。同时，亚太排名第一的大成律师事务所提供全程安全顾问，公司还组建了一支由专家组成的专业风控团队，对平台的每一个项目都进行严格的风险评估，从而全方位的为投资者的资金安全保驾护航。 &lt;/div&gt;&lt;div&gt; &amp;nbsp; 管理创造效益，服务成就未来。公司拥有国内一流的专业运营管理团队，成员均为银行、互联网金融、P2P、投资等领域的专家人士，拥有超强的技术力量和丰富的运作经验。强大的实力和雄厚的背景使得公司有足够的信心和能力来满足客户的投资、理财要求。投资者在这里可以享受到轻松理财、快速盈利、提存便捷、安全无忧的五星级投资理财专业服务。 &lt;/div&gt;&lt;div&gt; &amp;nbsp;未来，公司将继续以风险风控为核心，以透明化、规范化运营为宗旨，以安全可靠、互利共赢为理念，力求为广大投资者搭建一个规范、透明、优质、诚信、的互联网金融服务平台，营造一种安全、高效、快捷、灵活的投资氛围和理财环境，以回报广大客户的支持和厚爱。&lt;/div&gt;', 'gywm', 'admin', '2014-12-22 22:10:16');
INSERT INTO `system_webinfo` VALUES ('6', '&lt;p&gt;公司公告&lt;/p&gt;', 'gsgg', 'admin', '2014-12-23 00:41:16');

-- ----------------------------
-- Table structure for system_webmail
-- ----------------------------
DROP TABLE IF EXISTS `system_webmail`;
CREATE TABLE `system_webmail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text COMMENT '内容',
  `addTime` datetime NOT NULL DEFAULT '1990-01-01 08:00:00' COMMENT '添加时间',
  `addIp` varchar(50) NOT NULL DEFAULT '0.0.0.0' COMMENT '添加IP',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '信件状态:0-无效,1-有效',
  `sendUser` varchar(50) NOT NULL DEFAULT '' COMMENT '发送人用户名',
  `sendType` tinyint(1) NOT NULL DEFAULT '0' COMMENT '发送类型:0-单发;1-群发',
  `sendUserType` tinyint(1) NOT NULL DEFAULT '0' COMMENT '发送用户类型:0-系统管理员,1-网站用户',
  `files` varchar(255) NOT NULL DEFAULT '' COMMENT '附件',
  `receiveUser` varchar(50) NOT NULL DEFAULT '' COMMENT '接收用户',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='站内信';

-- ----------------------------
-- Records of system_webmail
-- ----------------------------
INSERT INTO `system_webmail` VALUES ('8', 'lllll', '&lt;p&gt;ssdfssdfasdfsdafsdafasd&lt;br /&gt;&lt;/p&gt;', '2015-06-03 17:20:34', '127.0.0.1', '1', 'admin', '0', '0', '', 'xing654');
INSERT INTO `system_webmail` VALUES ('3', '测试发送1', '&lt;p&gt;&amp;lt;p&amp;gt;算的添加站内信死死死死死死死死死&amp;lt;/p&amp;gt;&lt;/p&gt;', '2015-05-04 16:32:52', '0.0.0.0', '1', 'admin', '0', '0', '', '18760419185');
INSERT INTO `system_webmail` VALUES ('4', '测试发送', '&lt;p&gt;三三四四添加站内信&lt;/p&gt;', '2015-05-04 16:36:33', '0.0.0.0', '1', 'admin', '0', '0', '', '18760419185');
INSERT INTO `system_webmail` VALUES ('5', '测试发送搜索', '&lt;p&gt;添加站内信斯蒂芬森范德萨发送到&lt;/p&gt;', '2015-05-04 16:41:34', '', '1', 'admin', '1', '0', '', '');
INSERT INTO `system_webmail` VALUES ('6', '测试发送', '&lt;p&gt;斯蒂芬添加站内信&lt;/p&gt;', '2015-05-04 16:44:58', '', '1', 'admin', '0', '0', '', '18760419185');
INSERT INTO `system_webmail` VALUES ('7', '测试发送', '&lt;p&gt;算的添加站内信&lt;/p&gt;', '2015-05-04 16:50:07', '127.0.0.1', '1', 'admin', '0', '0', '', '18760419185');
INSERT INTO `system_webmail` VALUES ('9', '最新', '&lt;p&gt;试点城市地方撒法&lt;/p&gt;', '2015-06-03 17:21:36', '127.0.0.1', '1', 'admin', '0', '0', '', '18760419185');

-- ----------------------------
-- Table structure for user_bank_account
-- ----------------------------
DROP TABLE IF EXISTS `user_bank_account`;
CREATE TABLE `user_bank_account` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `userId` varchar(20) NOT NULL DEFAULT '' COMMENT '用户userId',
  `bankNum` varchar(30) NOT NULL DEFAULT '' COMMENT '卡号',
  `bank` tinyint(1) NOT NULL DEFAULT '0' COMMENT '开户行',
  `bankUsername` varchar(50) NOT NULL DEFAULT '' COMMENT '开户用户姓名',
  `province` tinyint(1) NOT NULL DEFAULT '0' COMMENT '开户行所在省',
  `city` tinyint(1) NOT NULL DEFAULT '0' COMMENT '开户行所在市',
  `subbranch` varchar(150) NOT NULL DEFAULT '' COMMENT '支行名称',
  `isDefault` enum('y','n') NOT NULL DEFAULT 'n' COMMENT '是否默认',
  `createAt` datetime NOT NULL DEFAULT '1990-01-01 08:00:00' COMMENT '添加时间',
  `updateAt` datetime NOT NULL DEFAULT '1990-01-01 08:00:00' COMMENT '最后修改时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='用户银行卡号';

-- ----------------------------
-- Records of user_bank_account
-- ----------------------------
INSERT INTO `user_bank_account` VALUES ('1', '1000000013', '1234698745543555', '3', '廖金灵', '2', '1', '福州支行', 'n', '2015-06-11 17:49:28', '2015-06-13 14:54:17');
INSERT INTO `user_bank_account` VALUES ('2', '1000000013', '1324654565132125', '8', '廖金灵', '3', '3', '租房后斯蒂芬森', 'y', '2015-06-11 18:45:24', '2015-06-13 17:37:05');

-- ----------------------------
-- Table structure for user_creditlog
-- ----------------------------
DROP TABLE IF EXISTS `user_creditlog`;
CREATE TABLE `user_creditlog` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `serialNumber` varchar(20) DEFAULT NULL COMMENT '流水号',
  `mkey` enum('user','work') DEFAULT NULL COMMENT '用户，流程',
  `type` varchar(20) DEFAULT NULL COMMENT '操作类型',
  `mode` enum('in','out') DEFAULT 'in' COMMENT 'in:进，out:出',
  `mvalue` float DEFAULT NULL COMMENT '充值金额',
  `remark` varchar(200) DEFAULT NULL COMMENT '金额操作备注',
  `userId` varchar(20) DEFAULT NULL COMMENT '用户ID',
  `time` datetime DEFAULT NULL COMMENT '操作时间',
  `operator` varchar(20) DEFAULT NULL COMMENT '操作人',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user_creditlog
-- ----------------------------

-- ----------------------------
-- Table structure for user_friend
-- ----------------------------
DROP TABLE IF EXISTS `user_friend`;
CREATE TABLE `user_friend` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `friend` varchar(20) DEFAULT NULL COMMENT '好友',
  `userId` varchar(20) DEFAULT NULL COMMENT '所有者',
  `time` datetime DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user_friend
-- ----------------------------
INSERT INTO `user_friend` VALUES ('2', 'xiaoym', '1000000001', '2015-01-07 22:46:19');

-- ----------------------------
-- Table structure for user_house
-- ----------------------------
DROP TABLE IF EXISTS `user_house`;
CREATE TABLE `user_house` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `userId` varchar(20) DEFAULT NULL COMMENT '用户ID',
  `houseradder` varchar(100) DEFAULT NULL COMMENT '房产地址',
  `houserarea` float DEFAULT NULL COMMENT '建筑面积',
  `houseryear` varchar(20) DEFAULT NULL COMMENT '建筑年份',
  `houserpay` enum('y','n') DEFAULT 'n',
  `housername1` varchar(20) DEFAULT NULL,
  `housername2` varchar(20) DEFAULT NULL,
  `houserage` int(20) DEFAULT NULL COMMENT '贷款年限',
  `housermonth` float DEFAULT NULL COMMENT '月供',
  `houserbalance` float DEFAULT NULL COMMENT '贷款余额',
  `houserbank` varchar(20) DEFAULT NULL COMMENT '按揭银行',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid` (`userId`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user_house
-- ----------------------------
INSERT INTO `user_house` VALUES ('1', '1000000001', '江西省吉安市井冈山凤凰路19号', '300', '2009', 'y', '刘晨辉', '齐天大圣', '30', '1000', '200000', '中国工商银行');
INSERT INTO `user_house` VALUES ('2', '1000000002', '江西省吉安市万安县凤凰路193号', '720', '2010', 'y', '郭季季', null, null, null, null, '');

-- ----------------------------
-- Table structure for user_link
-- ----------------------------
DROP TABLE IF EXISTS `user_link`;
CREATE TABLE `user_link` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `firstLinkName` varchar(20) DEFAULT NULL COMMENT '第一联系人',
  `firstRelation` enum('house','friend','business') DEFAULT NULL COMMENT '关系',
  `firstPhone` varchar(20) DEFAULT NULL COMMENT '手机号码',
  `firstOther` varchar(20) DEFAULT NULL COMMENT '其他联系',
  `secondLinkName` varchar(20) DEFAULT NULL,
  `secondRelation` enum('house','friend','business') DEFAULT NULL,
  `secondPhone` varchar(20) DEFAULT NULL,
  `secondOther` varchar(20) DEFAULT NULL,
  `thirdLinkName` varchar(20) DEFAULT NULL,
  `thirdRelation` enum('house','friend','business') DEFAULT NULL,
  `thirdPhone` varchar(20) DEFAULT NULL,
  `thirdOther` varchar(20) DEFAULT NULL,
  `userId` varchar(20) DEFAULT NULL COMMENT '用户ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid` (`userId`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user_link
-- ----------------------------
INSERT INTO `user_link` VALUES ('1', '刘晨红1', 'house', '11111111111111', 'QQ：1111111222', '阿尼', 'house', '22222222222222', 'email:xing665@122.co', '哈士奇', 'business', '33333333333333', 'EE：傲威法', '1000000001');
INSERT INTO `user_link` VALUES ('2', '刘晨红', 'house', '18810010008', null, '郭季季', 'house', '13245445451', null, '温余平', 'house', '18945645689', null, '1000000002');

-- ----------------------------
-- Table structure for user_moneylog
-- ----------------------------
DROP TABLE IF EXISTS `user_moneylog`;
CREATE TABLE `user_moneylog` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `serialNumber` varchar(20) DEFAULT NULL COMMENT '流水号',
  `oddNumber` varchar(20) DEFAULT NULL,
  `mkey` enum('user','work') DEFAULT NULL COMMENT '用户，流程',
  `type` varchar(20) DEFAULT NULL COMMENT '操作类型',
  `mode` enum('in','out') DEFAULT 'in' COMMENT 'in:进，out:出',
  `mvalue` float DEFAULT NULL COMMENT '充值金额',
  `remark` varchar(200) DEFAULT NULL COMMENT '金额操作备注',
  `userId` varchar(20) DEFAULT NULL COMMENT '用户ID',
  `time` datetime DEFAULT NULL COMMENT '操作时间',
  `operator` varchar(20) DEFAULT NULL COMMENT '操作人',
  `status` enum('0','1','-1') DEFAULT '0' COMMENT '0:未操作，1:成功，-1:失败',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user_moneylog
-- ----------------------------
INSERT INTO `user_moneylog` VALUES ('1', '20150606000000000001', '20150610000000000001', 'work', 'odd', 'in', '20000', '1000000001{GET}{PRINCIPAL}20000', '1000000001', '2015-06-06 16:39:16', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('2', '20150606000000000002', '20150610000000000001', 'work', 'odd', 'out', '140', '{ODD}20150610000000000001,1000000001{LESS}{SERVICE}{MONEY}140->{OPERATE}{SUCCESS}', '1000000001', '2015-06-06 16:39:16', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('3', '20150606000000000003', '20150610000000000001', 'work', 'odd', 'in', '140', '{ODD}20150610000000000001,1000000001{LESS}{SERVICE}{MONEY}140->{OPERATE}{SUCCESS}', '1508000', '2015-06-06 16:39:16', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('4', '20150606000000000004', '20150610000000000001', 'work', 'odd', 'out', '10000', '{ODD}20150610000000000001{UNFREEZE}{SUCCESS}1000000002', '1000000002', '2015-06-06 16:39:16', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('5', '20150606000000000005', '20150610000000000001', 'work', 'odd', 'out', '10000', '{ODD}20150610000000000001{UNFREEZE}{SUCCESS}1000000003', '1000000003', '2015-06-06 16:39:16', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('6', '20150606000000000006', '20150610000000000001', 'work', 'odd', 'out', '9800', '{CLAIMS}{BUY}{OPERATE}{SUCCESS}', '1000000013', '2015-06-06 16:40:06', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('7', '20150606000000000007', '20150610000000000001', 'work', 'odd', 'in', '9800', '{CLAIMS}{BUY}{OPERATE}{SUCCESS}', '1000000002', '2015-06-06 16:40:06', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('8', '20150606000000000008', '20150610000000000001', 'work', 'odd', 'in', '100', '{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{HUANKUAN}->1000000013{SUCCESS}', '1000000013', '2015-06-06 16:40:27', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('9', '20150606000000000009', '20150610000000000001', 'work', 'odd', 'out', '100', '{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{LESS}1508000{SUCCESS}', '1508000', '2015-06-06 16:40:27', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('10', '20150606000000000010', '20150610000000000001', 'work', 'odd', 'out', '10', '{SYSTEM}:{ODD}20150610000000000001{INTEREST}{SERVICE}{MONEY}10,->1000000013{LESS}{SUCCESS}', '1000000013', '2015-06-06 16:40:27', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('11', '20150606000000000011', '20150610000000000001', 'work', 'odd', 'in', '10', '{SYSTEM}:{ODD}20150610000000000001,{COMPANY}{GET}1000000013{INTEREST}{SERVICE}{MONEY}10，{OPERATE}{SUCCESS}', '1508000', '2015-06-06 16:40:27', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('12', '20150606000000000012', '20150610000000000001', 'work', 'odd', 'in', '100', '{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{HUANKUAN}->1000000003{SUCCESS}', '1000000003', '2015-06-06 16:40:27', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('13', '20150606000000000013', '20150610000000000001', 'work', 'odd', 'out', '100', '{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{LESS}1508000{SUCCESS}', '1508000', '2015-06-06 16:40:27', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('14', '20150606000000000014', '20150610000000000001', 'work', 'odd', 'out', '10', '{SYSTEM}:{ODD}20150610000000000001{INTEREST}{SERVICE}{MONEY}10,->1000000003{LESS}{SUCCESS}', '1000000003', '2015-06-06 16:40:27', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('15', '20150606000000000015', '20150610000000000001', 'work', 'odd', 'in', '10', '{SYSTEM}:{ODD}20150610000000000001,{COMPANY}{GET}1000000003{INTEREST}{SERVICE}{MONEY}10，{OPERATE}{SUCCESS}', '1508000', '2015-06-06 16:40:27', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('16', '20150606000000000016', '20150610000000000001', 'work', 'odd', 'in', '100', '{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{HUANKUAN}->1000000013{SUCCESS}', '1000000013', '2015-06-06 16:40:53', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('17', '20150606000000000017', '20150610000000000001', 'work', 'odd', 'out', '100', '{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{LESS}1508000{SUCCESS}', '1508000', '2015-06-06 16:40:53', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('18', '20150606000000000018', '20150610000000000001', 'work', 'odd', 'out', '10', '{SYSTEM}:{ODD}20150610000000000001{INTEREST}{SERVICE}{MONEY}10,->1000000013{LESS}{SUCCESS}', '1000000013', '2015-06-06 16:40:53', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('19', '20150606000000000019', '20150610000000000001', 'work', 'odd', 'in', '10', '{SYSTEM}:{ODD}20150610000000000001,{COMPANY}{GET}1000000013{INTEREST}{SERVICE}{MONEY}10，{OPERATE}{SUCCESS}', '1508000', '2015-06-06 16:40:53', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('20', '20150606000000000020', '20150610000000000001', 'work', 'odd', 'in', '100', '{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{HUANKUAN}->1000000003{SUCCESS}', '1000000003', '2015-06-06 16:40:53', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('21', '20150606000000000021', '20150610000000000001', 'work', 'odd', 'out', '100', '{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{LESS}1508000{SUCCESS}', '1508000', '2015-06-06 16:40:53', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('22', '20150606000000000022', '20150610000000000001', 'work', 'odd', 'out', '10', '{SYSTEM}:{ODD}20150610000000000001{INTEREST}{SERVICE}{MONEY}10,->1000000003{LESS}{SUCCESS}', '1000000003', '2015-06-06 16:40:53', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('23', '20150606000000000023', '20150610000000000001', 'work', 'odd', 'in', '10', '{SYSTEM}:{ODD}20150610000000000001,{COMPANY}{GET}1000000003{INTEREST}{SERVICE}{MONEY}10，{OPERATE}{SUCCESS}', '1508000', '2015-06-06 16:40:53', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('24', '20150606000000000024', '20150610000000000001', 'work', 'odd', 'in', '10000', '{SYSTEM}:{ODD}20150610000000000001{PRINCIPAL}10000，', '1000000013', '2015-06-06 16:41:02', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('25', '20150606000000000025', '20150610000000000001', 'work', 'odd', 'out', '10000', '{SYSTEM}:{ODD}20150610000000000001{PRINCIPAL}10000，', '1508000', '2015-06-06 16:41:02', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('26', '20150606000000000026', '20150610000000000001', 'work', 'odd', 'in', '100', '{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{HUANKUAN}->1000000013{SUCCESS}', '1000000013', '2015-06-06 16:41:02', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('27', '20150606000000000027', '20150610000000000001', 'work', 'odd', 'out', '100', '{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{LESS}1508000{SUCCESS}', '1508000', '2015-06-06 16:41:02', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('28', '20150606000000000028', '20150610000000000001', 'work', 'odd', 'out', '10', '{SYSTEM}:{ODD}20150610000000000001{INTEREST}{SERVICE}{MONEY}10,->1000000013{LESS}{SUCCESS}', '1000000013', '2015-06-06 16:41:02', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('29', '20150606000000000029', '20150610000000000001', 'work', 'odd', 'in', '10', '{SYSTEM}:{ODD}20150610000000000001,{COMPANY}{GET}1000000013{INTEREST}{SERVICE}{MONEY}10，{OPERATE}{SUCCESS}', '1508000', '2015-06-06 16:41:02', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('30', '20150606000000000030', '20150610000000000001', 'work', 'odd', 'in', '10000', '{SYSTEM}:{ODD}20150610000000000001{PRINCIPAL}10000，', '1000000003', '2015-06-06 16:41:02', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('31', '20150606000000000031', '20150610000000000001', 'work', 'odd', 'out', '10000', '{SYSTEM}:{ODD}20150610000000000001{PRINCIPAL}10000，', '1508000', '2015-06-06 16:41:02', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('32', '20150606000000000032', '20150610000000000001', 'work', 'odd', 'in', '100', '{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{HUANKUAN}->1000000003{SUCCESS}', '1000000003', '2015-06-06 16:41:02', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('33', '20150606000000000033', '20150610000000000001', 'work', 'odd', 'out', '100', '{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{LESS}1508000{SUCCESS}', '1508000', '2015-06-06 16:41:02', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('34', '20150606000000000034', '20150610000000000001', 'work', 'odd', 'out', '10', '{SYSTEM}:{ODD}20150610000000000001{INTEREST}{SERVICE}{MONEY}10,->1000000003{LESS}{SUCCESS}', '1000000003', '2015-06-06 16:41:02', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('35', '20150606000000000035', '20150610000000000001', 'work', 'odd', 'in', '10', '{SYSTEM}:{ODD}20150610000000000001,{COMPANY}{GET}1000000003{INTEREST}{SERVICE}{MONEY}10，{OPERATE}{SUCCESS}', '1508000', '2015-06-06 16:41:02', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('36', '20150606000000000036', '', 'user', 'addmoney', 'out', '19860', '{HOUTAI}{LESS}:1000000001->19860->{OPERATE}{SUCCESS}', '1000000001', '2015-06-06 19:45:16', 'sysadmin', '0');
INSERT INTO `user_moneylog` VALUES ('37', '20150606000000000037', '', 'user', 'addmoney', 'in', '19860', '{HOUTAI}{LESS}:1000000001->19860->{OPERATE}{SUCCESS}', '1508000', '2015-06-06 19:45:16', 'sysadmin', '0');

-- ----------------------------
-- Table structure for user_moneyrecharge
-- ----------------------------
DROP TABLE IF EXISTS `user_moneyrecharge`;
CREATE TABLE `user_moneyrecharge` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '客户充值列表',
  `serialNumber` varchar(40) DEFAULT NULL COMMENT '流水号',
  `userId` varchar(20) DEFAULT NULL COMMENT '用户ID',
  `mode` enum('in','out') DEFAULT 'in' COMMENT 'in:进，out:出',
  `money` double DEFAULT NULL,
  `status` enum('0','1','-1') DEFAULT '0' COMMENT '0：未审核，1审核，-1：失效',
  `time` datetime DEFAULT NULL,
  `operator` varchar(20) DEFAULT NULL COMMENT '操作人',
  `remark` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user_moneyrecharge
-- ----------------------------
INSERT INTO `user_moneyrecharge` VALUES ('1', null, '1000000001', 'out', '19860', '1', '2015-06-10 19:45:03', 'admin', 'OL');

-- ----------------------------
-- Table structure for user_office
-- ----------------------------
DROP TABLE IF EXISTS `user_office`;
CREATE TABLE `user_office` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `userId` varchar(20) DEFAULT NULL COMMENT '用户ID',
  `officename` varchar(100) DEFAULT NULL COMMENT '单位名称',
  `officephone` varchar(20) DEFAULT NULL COMMENT '单位电话',
  `officecity` varchar(20) DEFAULT NULL COMMENT '单位城市',
  `officeadder` varchar(100) DEFAULT NULL COMMENT '单位地址',
  `officeyear` enum('1','2','3','4','5') DEFAULT '1' COMMENT '工作年限',
  `officeproof` varchar(20) DEFAULT NULL COMMENT '证明人',
  `officeprooftel` varchar(20) DEFAULT NULL COMMENT '证明人电话',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user_office
-- ----------------------------
INSERT INTO `user_office` VALUES ('1', '1000000002', '福建汇诚普惠金融服务有限公司', '4008839993', '福建省-福州市-台江区', '光明南路1号升龙汇金中心1106', '1', '郑文晶', '13905926529');
INSERT INTO `user_office` VALUES ('2', '1000000001', '福建汇诚普惠金融服务有限公司', '400888999', '福建省-福州市-台江区', '光明北路188号1', '5', '张巍', '15270006888');

-- ----------------------------
-- Table structure for user_queue
-- ----------------------------
DROP TABLE IF EXISTS `user_queue`;
CREATE TABLE `user_queue` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `userId` varchar(20) NOT NULL DEFAULT '',
  `location` float DEFAULT NULL COMMENT '下限',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user` (`userId`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of user_queue
-- ----------------------------
INSERT INTO `user_queue` VALUES ('1', '1000000001', '1');
INSERT INTO `user_queue` VALUES ('2', '1000000002', '2');
INSERT INTO `user_queue` VALUES ('3', '1000000003', '3');

-- ----------------------------
-- Table structure for user_queuelog
-- ----------------------------
DROP TABLE IF EXISTS `user_queuelog`;
CREATE TABLE `user_queuelog` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `oddNumber` varchar(20) DEFAULT NULL COMMENT '借款标ID',
  `sqlstr` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user_queuelog
-- ----------------------------
INSERT INTO `user_queuelog` VALUES ('1', '20150610000000000001', 'DELETE FROM user_queue WHERE userId = \"1000000002\"');
INSERT INTO `user_queuelog` VALUES ('2', '20150610000000000001', 'INSERT INTO user_queue(userId,location)VALUES(\"1000000002\",\"4\")');
INSERT INTO `user_queuelog` VALUES ('3', '20150610000000000001', 'DELETE FROM user_queue WHERE userId = \"1000000003\"');
INSERT INTO `user_queuelog` VALUES ('4', '20150610000000000001', 'INSERT INTO user_queue(userId,location)VALUES(\"1000000003\",\"5\")');
INSERT INTO `user_queuelog` VALUES ('5', '20150610000000000001', 'INSERT INTO user_queue (userId,location) VALUES (\"1000000001\",\"1\")');
INSERT INTO `user_queuelog` VALUES ('6', '20150610000000000001', 'INSERT INTO user_queue (userId,location) VALUES (\"1000000002\",\"2\")');
INSERT INTO `user_queuelog` VALUES ('7', '20150610000000000001', 'INSERT INTO user_queue (userId,location) VALUES (\"1000000003\",\"3\")');

-- ----------------------------
-- Table structure for work_cmdlog
-- ----------------------------
DROP TABLE IF EXISTS `work_cmdlog`;
CREATE TABLE `work_cmdlog` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `workId` varchar(40) DEFAULT NULL COMMENT '工作ID',
  `oddtype` varchar(20) DEFAULT NULL COMMENT '提交事务类型',
  `operator` varchar(20) DEFAULT NULL COMMENT '操作人',
  `cmdxml` text COMMENT 'xml代码',
  `addtime` datetime DEFAULT NULL COMMENT '开始时间',
  `endtime` datetime DEFAULT NULL COMMENT '结束时间',
  `status` enum('0','1','-1') DEFAULT '0' COMMENT '0待运行，1运行成功，-1运行失败',
  `remark` varchar(200) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of work_cmdlog
-- ----------------------------
INSERT INTO `work_cmdlog` VALUES ('1', '20150606000000000001', 'runOddTrial', 'admin', '<?xml version=\"1.0\"?><root><accountNumber>100000123</accountNumber><cmd>runOddTrial</cmd><oddNumber>20150610000000000001</oddNumber><status>y</status><userId>admin</userId><adviceURL>http://www.p2p.com/adviceurl.php</adviceURL><secureCode>07ecadf58337c59454dad5f270d344d9</secureCode></root>', '2015-06-06 16:39:04', '2015-06-06 16:39:04', '1', '{\"status\":\"success\",\"msg\":\"{TRIAL}{SUCCESS}\",\"data\":\"20150606000000000001\"}');
INSERT INTO `work_cmdlog` VALUES ('2', '20150606000000000002', 'autoLoan', 'admin', '<?xml version=\"1.0\"?><root><accountNumber>100000123</accountNumber><cmd>autoLoan</cmd><oddNumber>20150610000000000001</oddNumber><userId>admin</userId><adviceURL>http://www.p2p.com/adviceurl.php</adviceURL><secureCode>9497a2ffb9b9e240b7551870928a71d8</secureCode></root>', '2015-06-06 16:39:09', '2015-06-06 16:39:09', '1', '{\"status\":\"success\",\"msg\":\"{AUTOMATIC}{SUCCESS}\",\"data\":\"20150606000000000002\"}');
INSERT INTO `work_cmdlog` VALUES ('3', '20150606000000000003', 'runOddRehear', 'admin', '<?xml version=\"1.0\"?><root><accountNumber>100000123</accountNumber><cmd>runOddRehear</cmd><oddNumber>20150610000000000001</oddNumber><type>month</type><oddLoanServiceFees>0.007</oddLoanServiceFees><status>y</status><userId>admin</userId><adviceURL>http://www.p2p.com/adviceurl.php</adviceURL><secureCode>d066766b93780ac4e6f8f202b3c86d24</secureCode></root>', '2015-06-06 16:39:16', '2015-06-06 16:39:16', '1', '{\"status\":\"success\",\"msg\":\"{OPERATE}{SUCCESS}\",\"data\":\"20150606000000000003\"}');
INSERT INTO `work_cmdlog` VALUES ('4', '20150606000000000004', 'publishClaimsOdd', '1000000002', '<?xml version=\"1.0\"?><root><accountNumber>100000123</accountNumber><cmd>publishClaimsOdd</cmd><userId>1000000002</userId><oddmoneyId>2</oddmoneyId><oddNumber>20150610000000000001</oddNumber><claimsMoney>9800</claimsMoney><adviceURL>http://www.p2p.com/adviceurl.php</adviceURL><secureCode>e0e4bbce8a7a3fc760a56d749625e7b0</secureCode></root>', '2015-06-06 16:39:54', '2015-06-06 16:39:54', '1', '{\"status\":\"success\",\"msg\":\"{CLAIMS}{TRANSFER}{OPERATE}{SUCCESS}\",\"data\":\"20150606000000000004\"}');
INSERT INTO `work_cmdlog` VALUES ('5', '20150606000000000005', 'buyClaimsOdd', '1000000013', '<?xml version=\"1.0\"?><root><accountNumber>100000123</accountNumber><cmd>buyClaimsOdd</cmd><userId>1000000013</userId><oddmoneyId>2</oddmoneyId><claimsId>1</claimsId><oddNumber>20150610000000000001</oddNumber><adviceURL>http://www.p2p.com/adviceurl.php</adviceURL><secureCode>edc8d54fa1858ebcbfdf5fde2d7a17d4</secureCode></root>', '2015-06-06 16:40:06', '2015-06-06 16:40:06', '1', '{\"status\":\"success\",\"msg\":\"{CLAIMS}{BUY}{OPERATE}{SUCCESS}\",\"data\":\"20150606000000000005\"}');
INSERT INTO `work_cmdlog` VALUES ('6', '20150606000000000006', 'regular', 'admin', '<?xml version=\"1.0\"?><root><cmd>regular</cmd><accountNumber>100000123</accountNumber><oddNumber>20150610000000000001</oddNumber><type>month</type><qishu>1</qishu><zongqishu>3</zongqishu><benjin>0</benjin><lixi>1</lixi><userId>admin</userId><adviceURL>http://www.p2p.com/adviceurl.php</adviceURL><secureCode>7b3cb00f034a4e6658ed47a561de95fc</secureCode></root>', '2015-06-06 16:40:27', '2015-06-06 16:40:27', '1', '{\"status\":\"success\",\"msg\":\"{SYSTEM}:{ODD}20150610000000000001{HUANKUAN}{SUCCESS}\",\"data\":\"20150606000000000006\"}');
INSERT INTO `work_cmdlog` VALUES ('7', '20150606000000000007', 'regular', 'admin', '<?xml version=\"1.0\"?><root><cmd>regular</cmd><accountNumber>100000123</accountNumber><oddNumber>20150610000000000001</oddNumber><type>month</type><qishu>2</qishu><zongqishu>3</zongqishu><benjin>0</benjin><lixi>1</lixi><userId>admin</userId><adviceURL>http://www.p2p.com/adviceurl.php</adviceURL><secureCode>8b2e5c53ffcc49f138d3d70655d10d66</secureCode></root>', '2015-06-06 16:40:53', '2015-06-06 16:40:53', '1', '{\"status\":\"success\",\"msg\":\"{SYSTEM}:{ODD}20150610000000000001{HUANKUAN}{SUCCESS}\",\"data\":\"20150606000000000007\"}');
INSERT INTO `work_cmdlog` VALUES ('8', '20150606000000000008', 'regular', 'admin', '<?xml version=\"1.0\"?><root><cmd>regular</cmd><accountNumber>100000123</accountNumber><oddNumber>20150610000000000001</oddNumber><type>month</type><qishu>3</qishu><zongqishu>3</zongqishu><benjin>1</benjin><lixi>1</lixi><userId>admin</userId><adviceURL>http://www.p2p.com/adviceurl.php</adviceURL><secureCode>bf7880a781a17609270f7564535b107e</secureCode></root>', '2015-06-06 16:41:02', '2015-06-06 16:41:02', '1', '{\"status\":\"success\",\"msg\":\"{SYSTEM}:{ODD}20150610000000000001{HUANKUAN}{SUCCESS}\",\"data\":\"20150606000000000008\"}');
INSERT INTO `work_cmdlog` VALUES ('9', '20150606000000000009', 'lessMoney', '1000000001', '<?xml version=\"1.0\"?><root><accountNumber>100000123</accountNumber><cmd>lessMoney</cmd><id>1</id><type>ok</type><userId>1000000001</userId><adviceURL>http://www.p2p.com/adviceurl.php</adviceURL><secureCode>9c5d740a74d98d056158c330a646ce0b</secureCode></root>', '2015-06-06 19:45:16', '2015-06-06 19:45:16', '1', '{\"status\":\"success\",\"msg\":\"{HOUTAI}{LESS}:1000000001->19860->{OPERATE}{SUCCESS}\",\"data\":\"20150606000000000009\"}');
INSERT INTO `work_cmdlog` VALUES ('10', '20150609000000000001', 'runOddTrial', 'admin', '<?xml version=\"1.0\"?><root><accountNumber>100000123</accountNumber><cmd>runOddTrial</cmd><oddNumber>20150613000000000001</oddNumber><status>y</status><userId>admin</userId><adviceURL>http://www.p2p.com/adviceurl.php</adviceURL><secureCode>01a72e067222f1c70129708c652dee6f</secureCode></root>', '2015-06-09 17:34:33', '2015-06-09 17:34:33', '1', '{\"status\":\"success\",\"msg\":\"{TRIAL}{SUCCESS}\",\"data\":\"20150609000000000001\"}');

-- ----------------------------
-- Table structure for work_info
-- ----------------------------
DROP TABLE IF EXISTS `work_info`;
CREATE TABLE `work_info` (
  `oddkey` varchar(20) NOT NULL DEFAULT '',
  `oddvalue` float DEFAULT NULL,
  `oddremark` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`oddkey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of work_info
-- ----------------------------
INSERT INTO `work_info` VALUES ('spreadMoney', '0.005', '推广费比率');
INSERT INTO `work_info` VALUES ('serialNumber', '20', '资金流水号长度');
INSERT INTO `work_info` VALUES ('oddNumber', '20', '借款单号长度');
INSERT INTO `work_info` VALUES ('interestServer', '0.1', '利息服务费');

-- ----------------------------
-- Table structure for work_loanprocess
-- ----------------------------
DROP TABLE IF EXISTS `work_loanprocess`;
CREATE TABLE `work_loanprocess` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '借款流程暂时未用',
  `oddNumber` varchar(20) DEFAULT NULL COMMENT '标单编号',
  `dedutType` enum('freeze','dedut') DEFAULT NULL COMMENT '扣款类型',
  `processNum` int(20) DEFAULT NULL COMMENT '流程序号',
  `dedutStyle` varchar(20) DEFAULT NULL COMMENT '扣费类型',
  `operator` varchar(20) DEFAULT NULL COMMENT '操作',
  `userId` varchar(20) DEFAULT NULL COMMENT '操作人',
  `time` datetime DEFAULT NULL COMMENT '操作时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of work_loanprocess
-- ----------------------------

-- ----------------------------
-- Table structure for work_odd
-- ----------------------------
DROP TABLE IF EXISTS `work_odd`;
CREATE TABLE `work_odd` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `oddNumber` varchar(20) NOT NULL COMMENT '借款单编号,唯一',
  `oddType` enum('diya','xingyong') NOT NULL COMMENT '标的类型',
  `oddTitle` varchar(100) NOT NULL COMMENT '借标标题',
  `oddUse` varchar(100) DEFAULT NULL COMMENT '借款用途',
  `oddYearRate` float NOT NULL COMMENT '年化率',
  `oddMoney` double NOT NULL DEFAULT '0' COMMENT '借款金额',
  `oddMoneyLast` double DEFAULT '0' COMMENT '金额剩余量',
  `startMoney` float DEFAULT NULL COMMENT '起投金额',
  `endMoney` float DEFAULT NULL COMMENT '结束金额',
  `oddMultiple` int(10) DEFAULT '1' COMMENT '杠杆倍数',
  `oddBorrowStyle` enum('month','day','sec') NOT NULL COMMENT '秒标是用来做活动的',
  `oddRepaymentStyle` enum('monthpay','matchpay') DEFAULT 'monthpay' COMMENT '按月付息，等额本息',
  `oddBorrowPeriod` int(20) NOT NULL COMMENT '借款期限',
  `oddBorrowValidTime` int(20) NOT NULL COMMENT '筹标期限，单位天',
  `oddExteriorPhotos` text COMMENT '外观图片json',
  `oddPropertyPhotos` text COMMENT '产权图片json',
  `otherPhotos` text COMMENT '借款手续json',
  `oddLoanRemark` text COMMENT '借款描述',
  `oddLoanServiceFees` float DEFAULT NULL COMMENT '借款服务费7%',
  `oddLoanFine` float DEFAULT NULL COMMENT '提前还款罚息',
  `oddTrial` enum('y','n','e') DEFAULT 'n' COMMENT '初审,e失效',
  `oddTrialTime` datetime DEFAULT NULL COMMENT '初审时间',
  `oddTrialRemark` text COMMENT '初审备注',
  `oddRehear` enum('y','n','e') DEFAULT 'n' COMMENT '复审,e失效',
  `oddRehearTime` datetime DEFAULT NULL COMMENT '复审时间',
  `oddRehearRemark` text COMMENT '复审备注',
  `addtime` datetime DEFAULT NULL COMMENT '添加时间',
  `userId` varchar(20) DEFAULT NULL COMMENT '用户名',
  `progress` enum('start','run','end','fail','prep','outflow') DEFAULT 'prep' COMMENT '流程运行标示,outflow流标',
  `operator` varchar(20) DEFAULT NULL COMMENT '代发标人',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of work_odd
-- ----------------------------
INSERT INTO `work_odd` VALUES ('1', '20150610000000000001', 'diya', '奔驰s400质押标', '装修家', '0.12', '20000', '0', '50', '0', '1', 'month', 'monthpay', '3', '5', '', '', '', '&lt;p&gt;添加借款描述&lt;/p&gt;', '0.007', null, 'y', '2015-06-06 16:39:04', '初审成功', 'y', '2015-06-06 16:39:16', '复审成功', '2015-06-10 16:39:16', '1000000001', 'run', 'admin');
INSERT INTO `work_odd` VALUES ('2', '20150613000000000001', 'diya', '宝马730质押标', '开店', '0.12', '20000', '20000', '50', '0', '1', 'month', 'monthpay', '2', '5', '', '', '', '&lt;p&gt;添加借款描述&lt;/p&gt;', null, null, 'y', '2015-06-09 17:34:33', '初审成功', 'n', null, null, '2015-06-13 15:17:10', '1000000001', 'start', 'admin');

-- ----------------------------
-- Table structure for work_oddautomatic
-- ----------------------------
DROP TABLE IF EXISTS `work_oddautomatic`;
CREATE TABLE `work_oddautomatic` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `autostatus` enum('0','1') DEFAULT '0' COMMENT '是否开启自动投标',
  `investTimeStyle` enum('all','limit') DEFAULT 'all' COMMENT '投资期限',
  `investTimeStart` int(5) DEFAULT NULL COMMENT '最少周期',
  `investTimeEnd` int(5) DEFAULT NULL COMMENT '最大周期',
  `investDay` enum('0','1') DEFAULT NULL COMMENT '是否投天标',
  `investMoneyUper` float DEFAULT NULL COMMENT '金额上限',
  `investMoneyLower` float DEFAULT NULL COMMENT '下限',
  `investRateStyle` enum('all','limit') DEFAULT NULL COMMENT '利率是否限定',
  `investRateStart` float DEFAULT NULL COMMENT '开始年利率',
  `investRateEnd` float DEFAULT NULL COMMENT '结束年利率',
  `investType` enum('all','diya','xingyong') DEFAULT 'all' COMMENT '抵押标，信用标',
  `investEgisMoney` float DEFAULT NULL COMMENT '用户保护金',
  `investEgisMoneyStatus` enum('0','1') DEFAULT '0' COMMENT '账户保护金是否启动',
  `addtime` datetime DEFAULT NULL COMMENT '更新时间',
  `userId` varchar(20) DEFAULT NULL COMMENT '用户',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of work_oddautomatic
-- ----------------------------
INSERT INTO `work_oddautomatic` VALUES ('1', '1', 'limit', '1', '3', '1', '10000', '17000', 'limit', '10', '19', 'all', '1000', '1', '2015-04-01 14:33:58', '1000000001');
INSERT INTO `work_oddautomatic` VALUES ('2', '1', 'limit', '1', '3', '1', '0', '10000', 'all', '0', '0', 'all', '100', '1', '2015-04-10 14:34:03', '1000000003');
INSERT INTO `work_oddautomatic` VALUES ('3', '1', 'all', '0', '0', '1', '0', '10000', 'all', '0', '0', 'all', '1000', '1', '2015-04-24 14:34:06', '1000000002');

-- ----------------------------
-- Table structure for work_oddclaims
-- ----------------------------
DROP TABLE IF EXISTS `work_oddclaims`;
CREATE TABLE `work_oddclaims` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `oddmoneyId` int(20) DEFAULT NULL,
  `oddNumber` varchar(20) DEFAULT NULL COMMENT '借款单编号',
  `userId` varchar(20) DEFAULT NULL COMMENT '用户ID',
  `idFrom` int(20) DEFAULT NULL COMMENT '出售用户oddclaims.Id',
  `userIdFrom` varchar(20) DEFAULT NULL COMMENT '出售的用户ID',
  `addtime` datetime DEFAULT NULL COMMENT '购买时间',
  `claimsMoney` double DEFAULT NULL COMMENT '转让价格',
  `status` enum('0','1','-1') DEFAULT '0' COMMENT '0:未被购买，1：已经成功,-1:失效',
  `type` enum('in','out') DEFAULT NULL COMMENT 'in 买进，out 卖出',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of work_oddclaims
-- ----------------------------
INSERT INTO `work_oddclaims` VALUES ('1', '2', '20150610000000000001', '1000000002', null, null, '2015-06-06 16:39:54', '9800', '-1', 'out');
INSERT INTO `work_oddclaims` VALUES ('2', '2', '20150610000000000001', '1000000013', '1', '1000000002', '2015-06-06 16:40:06', '9800', '1', 'in');

-- ----------------------------
-- Table structure for work_oddinterest
-- ----------------------------
DROP TABLE IF EXISTS `work_oddinterest`;
CREATE TABLE `work_oddinterest` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `oddNumber` varchar(20) NOT NULL DEFAULT '' COMMENT '借款单编号',
  `qishu` int(20) DEFAULT NULL COMMENT '期数',
  `benJin` double NOT NULL DEFAULT '0' COMMENT '本金',
  `interest` float NOT NULL DEFAULT '0' COMMENT '利息',
  `zongEr` double NOT NULL DEFAULT '0' COMMENT '总额',
  `yuEr` double NOT NULL DEFAULT '0' COMMENT '余额',
  `realMonery` double NOT NULL DEFAULT '0' COMMENT '实还金额',
  `userId` varchar(20) DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  `endtime` datetime DEFAULT NULL COMMENT '结束时间',
  `operatetime` datetime DEFAULT NULL COMMENT '还款时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of work_oddinterest
-- ----------------------------
INSERT INTO `work_oddinterest` VALUES ('1', '20150610000000000001', '1', '0', '200', '200', '20000', '200', '1000000001', '2015-06-06 16:39:16', '2015-07-06 16:39:16', '2015-06-06 16:40:27');
INSERT INTO `work_oddinterest` VALUES ('2', '20150610000000000001', '2', '0', '200', '200', '20000', '200', '1000000001', '2015-07-06 16:39:16', '2015-08-05 16:39:16', '2015-06-06 16:40:53');
INSERT INTO `work_oddinterest` VALUES ('3', '20150610000000000001', '3', '20000', '200', '20200', '0', '20200', '1000000001', '2015-08-05 16:39:16', '2015-09-04 16:39:16', '2015-06-06 16:41:02');

-- ----------------------------
-- Table structure for work_oddinterest_invest
-- ----------------------------
DROP TABLE IF EXISTS `work_oddinterest_invest`;
CREATE TABLE `work_oddinterest_invest` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `oddNumber` varchar(20) NOT NULL DEFAULT '' COMMENT '借款单编号',
  `qishu` int(20) DEFAULT NULL COMMENT '期数',
  `benJin` double NOT NULL DEFAULT '0' COMMENT '本金',
  `interest` float NOT NULL DEFAULT '0' COMMENT '利息',
  `zongEr` double NOT NULL DEFAULT '0' COMMENT '总额',
  `yuEr` double NOT NULL DEFAULT '0' COMMENT '余额',
  `realMonery` double NOT NULL DEFAULT '0' COMMENT '实还金额',
  `oddMoneyId` int(20) DEFAULT NULL COMMENT 'oddmoney的ID',
  `addtime` datetime DEFAULT NULL,
  `endtime` datetime DEFAULT NULL COMMENT '结束时间',
  `operatetime` datetime DEFAULT NULL COMMENT '还款时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of work_oddinterest_invest
-- ----------------------------
INSERT INTO `work_oddinterest_invest` VALUES ('1', '20150610000000000001', '1', '0', '100', '100', '10000', '100', '2', '2015-06-06 16:39:16', '2015-07-06 16:39:16', '2015-06-06 16:40:27');
INSERT INTO `work_oddinterest_invest` VALUES ('2', '20150610000000000001', '2', '0', '100', '100', '10000', '100', '2', '2015-07-06 16:39:16', '2015-08-05 16:39:16', '2015-06-06 16:40:53');
INSERT INTO `work_oddinterest_invest` VALUES ('3', '20150610000000000001', '3', '10000', '100', '10100', '0', '10100', '2', '2015-08-05 16:39:16', '2015-09-04 16:39:16', '2015-06-06 16:41:02');
INSERT INTO `work_oddinterest_invest` VALUES ('4', '20150610000000000001', '1', '0', '100', '100', '10000', '100', '3', '2015-06-06 16:39:16', '2015-07-06 16:39:16', '2015-06-06 16:40:27');
INSERT INTO `work_oddinterest_invest` VALUES ('5', '20150610000000000001', '2', '0', '100', '100', '10000', '100', '3', '2015-07-06 16:39:16', '2015-08-05 16:39:16', '2015-06-06 16:40:53');
INSERT INTO `work_oddinterest_invest` VALUES ('6', '20150610000000000001', '3', '10000', '100', '10100', '0', '10100', '3', '2015-08-05 16:39:16', '2015-09-04 16:39:16', '2015-06-06 16:41:02');

-- ----------------------------
-- Table structure for work_oddlog
-- ----------------------------
DROP TABLE IF EXISTS `work_oddlog`;
CREATE TABLE `work_oddlog` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `oddNumber` varchar(20) DEFAULT NULL COMMENT '借款单号',
  `user` varchar(20) DEFAULT NULL COMMENT '用户名称',
  `remark` varchar(400) DEFAULT NULL COMMENT '说明',
  `type` enum('oddTrial','oddRehear','oddAuto','oddClaims','oddOther','oddRepayment','oddInvest') DEFAULT NULL COMMENT 'oddAuto自动投标',
  `sqllog` text COMMENT '操作的数据库语句',
  `time` datetime DEFAULT NULL COMMENT '操作时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of work_oddlog
-- ----------------------------
INSERT INTO `work_oddlog` VALUES ('1', '20150610000000000001', 'sysadmin', '{SYSTEM}:{ODD}20150610000000000001{TRIAL}{END}-->{OPERATE}{SUCCESS}', 'oddTrial', 'UPDATE work_odd SET progress = \"start\",oddTrial = \"y\",oddTrialTime = \"2015-06-06 16:39:04\",oddTrialRemark = \"初审成功\" WHERE oddNumber = \"20150610000000000001\"', '2015-06-06 16:39:04');
INSERT INTO `work_oddlog` VALUES ('2', '20150610000000000001', 'sysadmin', '{SYSTEM}:{USER}:1000000001{JIEKUAN}:20000-->{OPERATE}{SUCCESS}', 'oddTrial', 'INSERT INTO work_oddmoney(oddNumber,type,money,userId,remark,time,status) VALUES (\"20150610000000000001\",\"loan\",\"20000\",\"1000000001\",\"{USER}:1000000001{JIEKUAN}:20000\",\"2015-06-06 16:39:04\",\"0\")', '2015-06-06 16:39:04');
INSERT INTO `work_oddlog` VALUES ('3', '20150610000000000001', 'sysadmin', '{SYSTEM}:{LOAN}{ODD}:20150610000000000001{INVEST}{SUCCESS},{MONEY}:10000', 'oddInvest', 'UPDATE work_odd SET oddMoneyLast = (oddMoneyLast - 10000) WHERE oddNumber = \"20150610000000000001\"', '2015-06-06 16:39:09');
INSERT INTO `work_oddlog` VALUES ('4', '20150610000000000001', 'sysadmin', '{SYSTEM}:{FREEZE}{USER}:1000000002{MONEY}-->{SUCCESS}', 'oddInvest', 'UPDATE system_userinfo SET frozenMoney = (frozenMoney + 10000) , fundMoney = (fundMoney - 10000) WHERE userId = \"1000000002\"', '2015-06-06 16:39:09');
INSERT INTO `work_oddlog` VALUES ('5', '20150610000000000001', 'sysadmin', '{SYSTEM}:{AUTOMATIC}：1000000002{INVEST}10000-->{OPERATE}{SUCCESS}', 'oddInvest', 'INSERT INTO work_oddmoney(oddNumber,type,money,userId,remark,time,status) VALUES (\"20150610000000000001\",\"invest\",\"10000\",\"1000000002\",\"{AUTOMATIC}：1000000002{INVEST}10000\",\"2015-06-06 16:39:09\",\"0\")', '2015-06-06 16:39:09');
INSERT INTO `work_oddlog` VALUES ('6', '20150610000000000001', 'sysadmin', '{SYSTEM}:{LOAN}{ODD}:20150610000000000001{INVEST}{SUCCESS},{MONEY}:10000', 'oddInvest', 'UPDATE work_odd SET oddMoneyLast = (oddMoneyLast - 10000) WHERE oddNumber = \"20150610000000000001\"', '2015-06-06 16:39:09');
INSERT INTO `work_oddlog` VALUES ('7', '20150610000000000001', 'sysadmin', '{SYSTEM}:{FREEZE}{USER}:1000000003{MONEY}-->{SUCCESS}', 'oddInvest', 'UPDATE system_userinfo SET frozenMoney = (frozenMoney + 10000) , fundMoney = (fundMoney - 10000) WHERE userId = \"1000000003\"', '2015-06-06 16:39:09');
INSERT INTO `work_oddlog` VALUES ('8', '20150610000000000001', 'sysadmin', '{SYSTEM}:{AUTOMATIC}：1000000003{INVEST}10000-->{OPERATE}{SUCCESS}', 'oddInvest', 'INSERT INTO work_oddmoney(oddNumber,type,money,userId,remark,time,status) VALUES (\"20150610000000000001\",\"invest\",\"10000\",\"1000000003\",\"{AUTOMATIC}：1000000003{INVEST}10000\",\"2015-06-06 16:39:09\",\"0\")', '2015-06-06 16:39:09');
INSERT INTO `work_oddlog` VALUES ('9', '20150610000000000001', 'sysadmin', '{SYSTEM}:{ODD}20150610000000000001{LOCKED}{LOAN},{INVEST}{MONEY}->{OPERATE}{SUCCESS}', 'oddRehear', 'UPDATE work_oddmoney SET status = \"1\" WHERE status = \"0\" AND oddNumber = \"20150610000000000001\"', '2015-06-06 16:39:16');
INSERT INTO `work_oddlog` VALUES ('10', '20150610000000000001', 'sysadmin', '{SYSTEM}:{ODD}20150610000000000001{RELOAD}', 'oddRehear', 'UPDATE work_odd SET oddRehearRemark = \"复审成功\",progress = \"run\",oddLoanServiceFees = \"0.007\",oddRehear = \"y\",oddRehearTime = \"2015-06-06 16:39:16\" WHERE oddNumber = \"20150610000000000001\"', '2015-06-06 16:39:16');
INSERT INTO `work_oddlog` VALUES ('11', '20150610000000000001', 'sysadmin', '{SYSTEM}:1000000001{GET}{PRINCIPAL}20000', 'oddRehear', 'UPDATE system_userinfo SET fundMoney = (fundMoney +20000)  WHERE userId = \"1000000001\" ', '2015-06-06 16:39:16');
INSERT INTO `work_oddlog` VALUES ('12', '20150610000000000001', 'sysadmin', '{SYSTEM}:1000000001{GET}{PRINCIPAL}20000', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000001\",\"work\",\"odd\",\"in\",\"20000\",\"1000000001{GET}{PRINCIPAL}20000\",\"1000000001\",\"2015-06-06 16:39:16\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:39:16');
INSERT INTO `work_oddlog` VALUES ('13', '20150610000000000001', 'sysadmin', '{SYSTEM}:{ODD}20150610000000000001,1000000001{LESS}{SERVICE}{MONEY}140->{OPERATE}{SUCCESS}', 'oddRehear', 'UPDATE system_userinfo SET fundMoney = (fundMoney + 140)  WHERE userId = \"1508000\" ', '2015-06-06 16:39:16');
INSERT INTO `work_oddlog` VALUES ('14', '20150610000000000001', 'sysadmin', '{SYSTEM}:{ODD}20150610000000000001,1000000001{LESS}{SERVICE}{MONEY}140->{OPERATE}{SUCCESS}', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000002\",\"work\",\"odd\",\"out\",\"140\",\"{ODD}20150610000000000001,1000000001{LESS}{SERVICE}{MONEY}140->{OPERATE}{SUCCESS}\",\"1000000001\",\"2015-06-06 16:39:16\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:39:16');
INSERT INTO `work_oddlog` VALUES ('15', '20150610000000000001', 'sysadmin', '{SYSTEM}:{ODD}20150610000000000001,1000000001{LESS}{SERVICE}{MONEY}140->{OPERATE}{SUCCESS}', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000003\",\"work\",\"odd\",\"in\",\"140\",\"{ODD}20150610000000000001,1000000001{LESS}{SERVICE}{MONEY}140->{OPERATE}{SUCCESS}\",\"1508000\",\"2015-06-06 16:39:16\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:39:16');
INSERT INTO `work_oddlog` VALUES ('16', '20150610000000000001', 'sysadmin', '{SYSTEM}:{ODD}20150610000000000001{APREAD}{MONEY}->{OPERATE}{END}', 'oddRehear', '', '2015-06-06 16:39:16');
INSERT INTO `work_oddlog` VALUES ('17', '20150610000000000001', 'sysadmin', '{SYSTEM}:{ODD}20150610000000000001{MORTGAGE}{LESS}20000->1000000001->{OPERATE}{SUCCESS}', 'oddRehear', 'UPDATE system_userinfo SET mortgageMonery = (mortgageMonery - 20000) WHERE userId = \"1000000001\"', '2015-06-06 16:39:16');
INSERT INTO `work_oddlog` VALUES ('18', '20150610000000000001', 'sysadmin', '{SYSTEM}:客户利息收益列表{QISHU}1-->{OPERATE}{SUCCESS}', 'oddRehear', 'INSERT INTO work_oddinterest_invest(oddNumber,qishu,benJin,interest,zongEr,yuEr,realMonery,oddMoneyId,addtime,endtime)VALUES(\"20150610000000000001\",\"1\",\"0\",\"100\",\"100\",\"10000\",\"0\",\"2\",\"2015-06-06 16:39:16\",\"2015-07-06 16:39:16\")', '2015-06-06 16:39:16');
INSERT INTO `work_oddlog` VALUES ('19', '20150610000000000001', 'sysadmin', '{SYSTEM}:客户利息收益列表{QISHU}2-->{OPERATE}{SUCCESS}', 'oddRehear', 'INSERT INTO work_oddinterest_invest(oddNumber,qishu,benJin,interest,zongEr,yuEr,realMonery,oddMoneyId,addtime,endtime)VALUES(\"20150610000000000001\",\"2\",\"0\",\"100\",\"100\",\"10000\",\"0\",\"2\",\"2015-07-06 16:39:16\",\"2015-08-05 16:39:16\")', '2015-06-06 16:39:16');
INSERT INTO `work_oddlog` VALUES ('20', '20150610000000000001', 'sysadmin', '{SYSTEM}:客户利息收益列表{QISHU}3-->{OPERATE}{SUCCESS}', 'oddRehear', 'INSERT INTO work_oddinterest_invest(oddNumber,qishu,benJin,interest,zongEr,yuEr,realMonery,oddMoneyId,addtime,endtime)VALUES(\"20150610000000000001\",\"3\",\"10000\",\"100\",\"10100\",\"0\",\"0\",\"2\",\"2015-08-05 16:39:16\",\"2015-09-04 16:39:16\")', '2015-06-06 16:39:16');
INSERT INTO `work_oddlog` VALUES ('21', '20150610000000000001', 'sysadmin', '{SYSTEM}:客户利息收益列表{QISHU}1-->{OPERATE}{SUCCESS}', 'oddRehear', 'INSERT INTO work_oddinterest_invest(oddNumber,qishu,benJin,interest,zongEr,yuEr,realMonery,oddMoneyId,addtime,endtime)VALUES(\"20150610000000000001\",\"1\",\"0\",\"100\",\"100\",\"10000\",\"0\",\"3\",\"2015-06-06 16:39:16\",\"2015-07-06 16:39:16\")', '2015-06-06 16:39:16');
INSERT INTO `work_oddlog` VALUES ('22', '20150610000000000001', 'sysadmin', '{SYSTEM}:客户利息收益列表{QISHU}2-->{OPERATE}{SUCCESS}', 'oddRehear', 'INSERT INTO work_oddinterest_invest(oddNumber,qishu,benJin,interest,zongEr,yuEr,realMonery,oddMoneyId,addtime,endtime)VALUES(\"20150610000000000001\",\"2\",\"0\",\"100\",\"100\",\"10000\",\"0\",\"3\",\"2015-07-06 16:39:16\",\"2015-08-05 16:39:16\")', '2015-06-06 16:39:16');
INSERT INTO `work_oddlog` VALUES ('23', '20150610000000000001', 'sysadmin', '{SYSTEM}:客户利息收益列表{QISHU}3-->{OPERATE}{SUCCESS}', 'oddRehear', 'INSERT INTO work_oddinterest_invest(oddNumber,qishu,benJin,interest,zongEr,yuEr,realMonery,oddMoneyId,addtime,endtime)VALUES(\"20150610000000000001\",\"3\",\"10000\",\"100\",\"10100\",\"0\",\"0\",\"3\",\"2015-08-05 16:39:16\",\"2015-09-04 16:39:16\")', '2015-06-06 16:39:16');
INSERT INTO `work_oddlog` VALUES ('24', '20150610000000000001', 'sysadmin', '{SYSTEM}:{HUANKUANLIST}:{QISHU}1-->{OPERATE}{SUCCESS}', 'oddRehear', 'INSERT INTO work_oddinterest(oddNumber,qishu,benJin,interest,zongEr,yuEr,realMonery,userId,addtime,endtime)VALUES(\"20150610000000000001\",\"1\",\"0\",\"200\",\"200\",\"20000\",\"0\",\"1000000001\",\"2015-06-06 16:39:16\" , \"2015-07-06 16:39:16\")', '2015-06-06 16:39:16');
INSERT INTO `work_oddlog` VALUES ('25', '20150610000000000001', 'sysadmin', '{SYSTEM}:{HUANKUANLIST}:{QISHU}2-->{OPERATE}{SUCCESS}', 'oddRehear', 'INSERT INTO work_oddinterest(oddNumber,qishu,benJin,interest,zongEr,yuEr,realMonery,userId,addtime,endtime)VALUES(\"20150610000000000001\",\"2\",\"0\",\"200\",\"200\",\"20000\",\"0\",\"1000000001\",\"2015-07-06 16:39:16\" , \"2015-08-05 16:39:16\")', '2015-06-06 16:39:16');
INSERT INTO `work_oddlog` VALUES ('26', '20150610000000000001', 'sysadmin', '{SYSTEM}:{HUANKUANLIST}:{QISHU}3-->{OPERATE}{SUCCESS}', 'oddRehear', 'INSERT INTO work_oddinterest(oddNumber,qishu,benJin,interest,zongEr,yuEr,realMonery,userId,addtime,endtime)VALUES(\"20150610000000000001\",\"3\",\"20000\",\"200\",\"20200\",\"0\",\"0\",\"1000000001\",\"2015-08-05 16:39:16\" , \"2015-09-04 16:39:16\")', '2015-06-06 16:39:16');
INSERT INTO `work_oddlog` VALUES ('27', '20150610000000000001', 'sysadmin', '{SYSTEM}:{ODD}20150610000000000001{UNFREEZE}{SUCCESS}1000000002', 'oddRehear', 'UPDATE system_userinfo SET frozenMoney = (frozenMoney - 10000), investMoney = (investMoney + 10000) WHERE userId = \"1000000002\"', '2015-06-06 16:39:16');
INSERT INTO `work_oddlog` VALUES ('28', '20150610000000000001', 'sysadmin', '{SYSTEM}:{ODD}20150610000000000001{UNFREEZE}{SUCCESS}1000000002', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000004\",\"work\",\"odd\",\"out\",\"10000\",\"{ODD}20150610000000000001{UNFREEZE}{SUCCESS}1000000002\",\"1000000002\",\"2015-06-06 16:39:16\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:39:16');
INSERT INTO `work_oddlog` VALUES ('29', '20150610000000000001', 'sysadmin', '{SYSTEM}:{ODD}20150610000000000001{UNFREEZE}{SUCCESS}1000000003', 'oddRehear', 'UPDATE system_userinfo SET frozenMoney = (frozenMoney - 10000), investMoney = (investMoney + 10000) WHERE userId = \"1000000003\"', '2015-06-06 16:39:16');
INSERT INTO `work_oddlog` VALUES ('30', '20150610000000000001', 'sysadmin', '{SYSTEM}:{ODD}20150610000000000001{UNFREEZE}{SUCCESS}1000000003', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000005\",\"work\",\"odd\",\"out\",\"10000\",\"{ODD}20150610000000000001{UNFREEZE}{SUCCESS}1000000003\",\"1000000003\",\"2015-06-06 16:39:16\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:39:16');
INSERT INTO `work_oddlog` VALUES ('31', '20150610000000000001', 'sysadmin', '{SYSTEM}:{ODD}20150610000000000001{REHEAR}{SUCCESS}', 'oddRehear', 'UPDATE work_odd SET oddRehearRemark = \"复审成功\",progress = \"run\",oddLoanServiceFees = \"0.007\",oddRehear = \"y\",oddRehearTime = \"2015-06-06 16:39:16\" WHERE oddNumber = \"20150610000000000001\"', '2015-06-06 16:39:16');
INSERT INTO `work_oddlog` VALUES ('32', '20150610000000000001', 'sysadmin', '{SYSTEM}:{CLAIMS}{TRANSFER}{OPERATE}{SUCCESS}', 'oddClaims', 'INSERT INTO work_oddclaims(oddmoneyId,oddNumber,userId,claimsMoney,type,addtime) VALUES (\"2\", \"20150610000000000001\", \"1000000002\", \"9800\",\"out\",\"2015-06-06 16:39:54\")', '2015-06-06 16:39:54');
INSERT INTO `work_oddlog` VALUES ('33', '20150610000000000001', 'sysadmin', '{SYSTEM}:{CLAIMS}{BUY}{SUCCESS}', 'oddClaims', 'UPDATE work_oddmoney SET userId = \"1000000013\",ckclaims = \"1\" WHERE id = \"2\"', '2015-06-06 16:40:06');
INSERT INTO `work_oddlog` VALUES ('34', '20150610000000000001', 'sysadmin', '{SYSTEM}:{ADD}{CLAIMS}{LOG}', 'oddClaims', 'INSERT INTO work_oddclaims(oddmoneyId,oddNumber,userId,addtime,claimsMoney,status,type,idFrom,userIdFrom) VALUES (\"2\", \"20150610000000000001\", \"1000000013\",\"2015-06-06 16:40:06\", \"9800\",\"1\",\"in\", \"1\", \"1000000002\")', '2015-06-06 16:40:06');
INSERT INTO `work_oddlog` VALUES ('35', '20150610000000000001', 'sysadmin', '{SYSTEM}:{CLAIMS}{BUY}{OPERATE}{SUCCESS}', 'oddClaims', 'UPDATE system_userinfo SET fundMoney = (fundMoney + 9800), investMoney = (investMoney - 9800)  WHERE userId = \"1000000002\" ', '2015-06-06 16:40:06');
INSERT INTO `work_oddlog` VALUES ('36', '20150610000000000001', 'sysadmin', '{SYSTEM}:{CLAIMS}{BUY}{OPERATE}{SUCCESS}', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000006\",\"work\",\"odd\",\"out\",\"9800\",\"{CLAIMS}{BUY}{OPERATE}{SUCCESS}\",\"1000000013\",\"2015-06-06 16:40:06\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:40:06');
INSERT INTO `work_oddlog` VALUES ('37', '20150610000000000001', 'sysadmin', '{SYSTEM}:{CLAIMS}{BUY}{OPERATE}{SUCCESS}', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000007\",\"work\",\"odd\",\"in\",\"9800\",\"{CLAIMS}{BUY}{OPERATE}{SUCCESS}\",\"1000000002\",\"2015-06-06 16:40:06\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:40:06');
INSERT INTO `work_oddlog` VALUES ('38', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{LESS}{LOANUSER}{HUANKUAN}{MONEY}', 'oddRepayment', 'UPDATE work_oddinterest SET operatetime = \"2015-06-06 16:40:27\", realMonery = \"200\" WHERE oddNumber = \"20150610000000000001\" AND qishu = \"1\"', '2015-06-06 16:40:27');
INSERT INTO `work_oddlog` VALUES ('39', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{HUANKUAN}->1000000013{SUCCESS}', 'oddRepayment', 'UPDATE system_userinfo SET fundMoney = (fundMoney + 100) WHERE userId = \"1000000013\"', '2015-06-06 16:40:27');
INSERT INTO `work_oddlog` VALUES ('40', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{HUANKUAN}->1000000013{SUCCESS}', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000008\",\"work\",\"odd\",\"in\",\"100\",\"{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{HUANKUAN}->1000000013{SUCCESS}\",\"1000000013\",\"2015-06-06 16:40:27\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:40:27');
INSERT INTO `work_oddlog` VALUES ('41', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{LESS}1508000{SUCCESS}', 'oddRepayment', 'UPDATE system_userinfo SET fundMoney = (fundMoney - 100 ) WHERE userId = \"1508000\"', '2015-06-06 16:40:27');
INSERT INTO `work_oddlog` VALUES ('42', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{LESS}1508000{SUCCESS}', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000009\",\"work\",\"odd\",\"out\",\"100\",\"{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{LESS}1508000{SUCCESS}\",\"1508000\",\"2015-06-06 16:40:27\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:40:27');
INSERT INTO `work_oddlog` VALUES ('43', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001,{RELOAD}{INVEST}{SHOUYI}-->{SUCCESS}', 'oddRepayment', 'UPDATE work_oddinterest_invest SET operatetime = \"2015-06-06 16:40:27\", realMonery = \"100\" WHERE oddNumber = \"20150610000000000001\" AND qishu = \"1\" AND oddMoneyId = \"2\"', '2015-06-06 16:40:27');
INSERT INTO `work_oddlog` VALUES ('44', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}{SERVICE}{MONEY}10,->1000000013{LESS}{SUCCESS}', 'oddRepayment', 'UPDATE system_userinfo SET fundMoney = (fundMoney - 10) WHERE userId = \"1000000013\"', '2015-06-06 16:40:27');
INSERT INTO `work_oddlog` VALUES ('45', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}{SERVICE}{MONEY}10,->1000000013{LESS}{SUCCESS}', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000010\",\"work\",\"odd\",\"out\",\"10\",\"{SYSTEM}:{ODD}20150610000000000001{INTEREST}{SERVICE}{MONEY}10,->1000000013{LESS}{SUCCESS}\",\"1000000013\",\"2015-06-06 16:40:27\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:40:27');
INSERT INTO `work_oddlog` VALUES ('46', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001,{COMPANY}{GET}1000000013{INTEREST}{SERVICE}{MONEY}10，{OP', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000011\",\"work\",\"odd\",\"in\",\"10\",\"{SYSTEM}:{ODD}20150610000000000001,{COMPANY}{GET}1000000013{INTEREST}{SERVICE}{MONEY}10，{OPERATE}{SUCCESS}\",\"1508000\",\"2015-06-06 16:40:27\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:40:27');
INSERT INTO `work_oddlog` VALUES ('47', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{HUANKUAN}->1000000003{SUCCESS}', 'oddRepayment', 'UPDATE system_userinfo SET fundMoney = (fundMoney + 100) WHERE userId = \"1000000003\"', '2015-06-06 16:40:27');
INSERT INTO `work_oddlog` VALUES ('48', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{HUANKUAN}->1000000003{SUCCESS}', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000012\",\"work\",\"odd\",\"in\",\"100\",\"{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{HUANKUAN}->1000000003{SUCCESS}\",\"1000000003\",\"2015-06-06 16:40:27\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:40:27');
INSERT INTO `work_oddlog` VALUES ('49', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{LESS}1508000{SUCCESS}', 'oddRepayment', 'UPDATE system_userinfo SET fundMoney = (fundMoney - 100 ) WHERE userId = \"1508000\"', '2015-06-06 16:40:27');
INSERT INTO `work_oddlog` VALUES ('50', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{LESS}1508000{SUCCESS}', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000013\",\"work\",\"odd\",\"out\",\"100\",\"{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{LESS}1508000{SUCCESS}\",\"1508000\",\"2015-06-06 16:40:27\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:40:27');
INSERT INTO `work_oddlog` VALUES ('51', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001,{RELOAD}{INVEST}{SHOUYI}-->{SUCCESS}', 'oddRepayment', 'UPDATE work_oddinterest_invest SET operatetime = \"2015-06-06 16:40:27\", realMonery = \"100\" WHERE oddNumber = \"20150610000000000001\" AND qishu = \"1\" AND oddMoneyId = \"3\"', '2015-06-06 16:40:27');
INSERT INTO `work_oddlog` VALUES ('52', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}{SERVICE}{MONEY}10,->1000000003{LESS}{SUCCESS}', 'oddRepayment', 'UPDATE system_userinfo SET fundMoney = (fundMoney - 10) WHERE userId = \"1000000003\"', '2015-06-06 16:40:27');
INSERT INTO `work_oddlog` VALUES ('53', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}{SERVICE}{MONEY}10,->1000000003{LESS}{SUCCESS}', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000014\",\"work\",\"odd\",\"out\",\"10\",\"{SYSTEM}:{ODD}20150610000000000001{INTEREST}{SERVICE}{MONEY}10,->1000000003{LESS}{SUCCESS}\",\"1000000003\",\"2015-06-06 16:40:27\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:40:27');
INSERT INTO `work_oddlog` VALUES ('54', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001,{COMPANY}{GET}1000000003{INTEREST}{SERVICE}{MONEY}10，{OP', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000015\",\"work\",\"odd\",\"in\",\"10\",\"{SYSTEM}:{ODD}20150610000000000001,{COMPANY}{GET}1000000003{INTEREST}{SERVICE}{MONEY}10，{OPERATE}{SUCCESS}\",\"1508000\",\"2015-06-06 16:40:27\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:40:27');
INSERT INTO `work_oddlog` VALUES ('55', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{LESS}{LOANUSER}{HUANKUAN}{MONEY}', 'oddRepayment', 'UPDATE work_oddinterest SET operatetime = \"2015-06-06 16:40:53\", realMonery = \"200\" WHERE oddNumber = \"20150610000000000001\" AND qishu = \"2\"', '2015-06-06 16:40:53');
INSERT INTO `work_oddlog` VALUES ('56', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{HUANKUAN}->1000000013{SUCCESS}', 'oddRepayment', 'UPDATE system_userinfo SET fundMoney = (fundMoney + 100) WHERE userId = \"1000000013\"', '2015-06-06 16:40:53');
INSERT INTO `work_oddlog` VALUES ('57', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{HUANKUAN}->1000000013{SUCCESS}', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000016\",\"work\",\"odd\",\"in\",\"100\",\"{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{HUANKUAN}->1000000013{SUCCESS}\",\"1000000013\",\"2015-06-06 16:40:53\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:40:53');
INSERT INTO `work_oddlog` VALUES ('58', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{LESS}1508000{SUCCESS}', 'oddRepayment', 'UPDATE system_userinfo SET fundMoney = (fundMoney - 100 ) WHERE userId = \"1508000\"', '2015-06-06 16:40:53');
INSERT INTO `work_oddlog` VALUES ('59', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{LESS}1508000{SUCCESS}', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000017\",\"work\",\"odd\",\"out\",\"100\",\"{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{LESS}1508000{SUCCESS}\",\"1508000\",\"2015-06-06 16:40:53\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:40:53');
INSERT INTO `work_oddlog` VALUES ('60', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001,{RELOAD}{INVEST}{SHOUYI}-->{SUCCESS}', 'oddRepayment', 'UPDATE work_oddinterest_invest SET operatetime = \"2015-06-06 16:40:53\", realMonery = \"100\" WHERE oddNumber = \"20150610000000000001\" AND qishu = \"2\" AND oddMoneyId = \"2\"', '2015-06-06 16:40:53');
INSERT INTO `work_oddlog` VALUES ('61', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}{SERVICE}{MONEY}10,->1000000013{LESS}{SUCCESS}', 'oddRepayment', 'UPDATE system_userinfo SET fundMoney = (fundMoney - 10) WHERE userId = \"1000000013\"', '2015-06-06 16:40:53');
INSERT INTO `work_oddlog` VALUES ('62', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}{SERVICE}{MONEY}10,->1000000013{LESS}{SUCCESS}', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000018\",\"work\",\"odd\",\"out\",\"10\",\"{SYSTEM}:{ODD}20150610000000000001{INTEREST}{SERVICE}{MONEY}10,->1000000013{LESS}{SUCCESS}\",\"1000000013\",\"2015-06-06 16:40:53\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:40:53');
INSERT INTO `work_oddlog` VALUES ('63', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001,{COMPANY}{GET}1000000013{INTEREST}{SERVICE}{MONEY}10，{OP', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000019\",\"work\",\"odd\",\"in\",\"10\",\"{SYSTEM}:{ODD}20150610000000000001,{COMPANY}{GET}1000000013{INTEREST}{SERVICE}{MONEY}10，{OPERATE}{SUCCESS}\",\"1508000\",\"2015-06-06 16:40:53\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:40:53');
INSERT INTO `work_oddlog` VALUES ('64', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{HUANKUAN}->1000000003{SUCCESS}', 'oddRepayment', 'UPDATE system_userinfo SET fundMoney = (fundMoney + 100) WHERE userId = \"1000000003\"', '2015-06-06 16:40:53');
INSERT INTO `work_oddlog` VALUES ('65', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{HUANKUAN}->1000000003{SUCCESS}', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000020\",\"work\",\"odd\",\"in\",\"100\",\"{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{HUANKUAN}->1000000003{SUCCESS}\",\"1000000003\",\"2015-06-06 16:40:53\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:40:53');
INSERT INTO `work_oddlog` VALUES ('66', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{LESS}1508000{SUCCESS}', 'oddRepayment', 'UPDATE system_userinfo SET fundMoney = (fundMoney - 100 ) WHERE userId = \"1508000\"', '2015-06-06 16:40:53');
INSERT INTO `work_oddlog` VALUES ('67', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{LESS}1508000{SUCCESS}', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000021\",\"work\",\"odd\",\"out\",\"100\",\"{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{LESS}1508000{SUCCESS}\",\"1508000\",\"2015-06-06 16:40:53\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:40:53');
INSERT INTO `work_oddlog` VALUES ('68', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001,{RELOAD}{INVEST}{SHOUYI}-->{SUCCESS}', 'oddRepayment', 'UPDATE work_oddinterest_invest SET operatetime = \"2015-06-06 16:40:53\", realMonery = \"100\" WHERE oddNumber = \"20150610000000000001\" AND qishu = \"2\" AND oddMoneyId = \"3\"', '2015-06-06 16:40:53');
INSERT INTO `work_oddlog` VALUES ('69', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}{SERVICE}{MONEY}10,->1000000003{LESS}{SUCCESS}', 'oddRepayment', 'UPDATE system_userinfo SET fundMoney = (fundMoney - 10) WHERE userId = \"1000000003\"', '2015-06-06 16:40:53');
INSERT INTO `work_oddlog` VALUES ('70', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}{SERVICE}{MONEY}10,->1000000003{LESS}{SUCCESS}', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000022\",\"work\",\"odd\",\"out\",\"10\",\"{SYSTEM}:{ODD}20150610000000000001{INTEREST}{SERVICE}{MONEY}10,->1000000003{LESS}{SUCCESS}\",\"1000000003\",\"2015-06-06 16:40:53\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:40:53');
INSERT INTO `work_oddlog` VALUES ('71', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001,{COMPANY}{GET}1000000003{INTEREST}{SERVICE}{MONEY}10，{OP', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000023\",\"work\",\"odd\",\"in\",\"10\",\"{SYSTEM}:{ODD}20150610000000000001,{COMPANY}{GET}1000000003{INTEREST}{SERVICE}{MONEY}10，{OPERATE}{SUCCESS}\",\"1508000\",\"2015-06-06 16:40:53\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:40:53');
INSERT INTO `work_oddlog` VALUES ('72', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{LESS}{LOANUSER}{HUANKUAN}{MONEY}', 'oddRepayment', 'UPDATE work_oddinterest SET operatetime = \"2015-06-06 16:41:02\", realMonery = \"20200\" WHERE oddNumber = \"20150610000000000001\" AND qishu = \"3\"', '2015-06-06 16:41:02');
INSERT INTO `work_oddlog` VALUES ('73', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{PRINCIPAL}10000，{HUANKUAN}->1000000013{SUCCESS}', 'oddRepayment', 'UPDATE system_userinfo SET fundMoney = (fundMoney + 10000) WHERE userId = \"1000000013\"', '2015-06-06 16:41:02');
INSERT INTO `work_oddlog` VALUES ('74', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{PRINCIPAL}10000，', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000024\",\"work\",\"odd\",\"in\",\"10000\",\"{SYSTEM}:{ODD}20150610000000000001{PRINCIPAL}10000，\",\"1000000013\",\"2015-06-06 16:41:02\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:41:02');
INSERT INTO `work_oddlog` VALUES ('75', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{PRINCIPAL}10000，{LESS}1508000{SUCCESS}', 'oddRepayment', 'UPDATE system_userinfo SET fundMoney = (fundMoney - 10000 ) WHERE userId = \"1508000\"', '2015-06-06 16:41:02');
INSERT INTO `work_oddlog` VALUES ('76', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{PRINCIPAL}10000，', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000025\",\"work\",\"odd\",\"out\",\"10000\",\"{SYSTEM}:{ODD}20150610000000000001{PRINCIPAL}10000，\",\"1508000\",\"2015-06-06 16:41:02\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:41:02');
INSERT INTO `work_oddlog` VALUES ('77', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{HUANKUAN}->1000000013{SUCCESS}', 'oddRepayment', 'UPDATE system_userinfo SET fundMoney = (fundMoney + 100) WHERE userId = \"1000000013\"', '2015-06-06 16:41:02');
INSERT INTO `work_oddlog` VALUES ('78', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{HUANKUAN}->1000000013{SUCCESS}', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000026\",\"work\",\"odd\",\"in\",\"100\",\"{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{HUANKUAN}->1000000013{SUCCESS}\",\"1000000013\",\"2015-06-06 16:41:02\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:41:02');
INSERT INTO `work_oddlog` VALUES ('79', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{LESS}1508000{SUCCESS}', 'oddRepayment', 'UPDATE system_userinfo SET fundMoney = (fundMoney - 100 ) WHERE userId = \"1508000\"', '2015-06-06 16:41:02');
INSERT INTO `work_oddlog` VALUES ('80', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{LESS}1508000{SUCCESS}', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000027\",\"work\",\"odd\",\"out\",\"100\",\"{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{LESS}1508000{SUCCESS}\",\"1508000\",\"2015-06-06 16:41:02\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:41:02');
INSERT INTO `work_oddlog` VALUES ('81', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001,{RELOAD}{INVEST}{SHOUYI}-->{SUCCESS}', 'oddRepayment', 'UPDATE work_oddinterest_invest SET operatetime = \"2015-06-06 16:41:02\", realMonery = \"10100\" WHERE oddNumber = \"20150610000000000001\" AND qishu = \"3\" AND oddMoneyId = \"2\"', '2015-06-06 16:41:02');
INSERT INTO `work_oddlog` VALUES ('82', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}{SERVICE}{MONEY}10,->1000000013{LESS}{SUCCESS}', 'oddRepayment', 'UPDATE system_userinfo SET fundMoney = (fundMoney - 10) WHERE userId = \"1000000013\"', '2015-06-06 16:41:02');
INSERT INTO `work_oddlog` VALUES ('83', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}{SERVICE}{MONEY}10,->1000000013{LESS}{SUCCESS}', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000028\",\"work\",\"odd\",\"out\",\"10\",\"{SYSTEM}:{ODD}20150610000000000001{INTEREST}{SERVICE}{MONEY}10,->1000000013{LESS}{SUCCESS}\",\"1000000013\",\"2015-06-06 16:41:02\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:41:02');
INSERT INTO `work_oddlog` VALUES ('84', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001,{COMPANY}{GET}1000000013{INTEREST}{SERVICE}{MONEY}10，{OP', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000029\",\"work\",\"odd\",\"in\",\"10\",\"{SYSTEM}:{ODD}20150610000000000001,{COMPANY}{GET}1000000013{INTEREST}{SERVICE}{MONEY}10，{OPERATE}{SUCCESS}\",\"1508000\",\"2015-06-06 16:41:02\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:41:02');
INSERT INTO `work_oddlog` VALUES ('85', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{PRINCIPAL}10000，{HUANKUAN}->1000000003{SUCCESS}', 'oddRepayment', 'UPDATE system_userinfo SET fundMoney = (fundMoney + 10000) WHERE userId = \"1000000003\"', '2015-06-06 16:41:02');
INSERT INTO `work_oddlog` VALUES ('86', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{PRINCIPAL}10000，', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000030\",\"work\",\"odd\",\"in\",\"10000\",\"{SYSTEM}:{ODD}20150610000000000001{PRINCIPAL}10000，\",\"1000000003\",\"2015-06-06 16:41:02\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:41:02');
INSERT INTO `work_oddlog` VALUES ('87', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{PRINCIPAL}10000，{LESS}1508000{SUCCESS}', 'oddRepayment', 'UPDATE system_userinfo SET fundMoney = (fundMoney - 10000 ) WHERE userId = \"1508000\"', '2015-06-06 16:41:02');
INSERT INTO `work_oddlog` VALUES ('88', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{PRINCIPAL}10000，', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000031\",\"work\",\"odd\",\"out\",\"10000\",\"{SYSTEM}:{ODD}20150610000000000001{PRINCIPAL}10000，\",\"1508000\",\"2015-06-06 16:41:02\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:41:02');
INSERT INTO `work_oddlog` VALUES ('89', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{HUANKUAN}->1000000003{SUCCESS}', 'oddRepayment', 'UPDATE system_userinfo SET fundMoney = (fundMoney + 100) WHERE userId = \"1000000003\"', '2015-06-06 16:41:02');
INSERT INTO `work_oddlog` VALUES ('90', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{HUANKUAN}->1000000003{SUCCESS}', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000032\",\"work\",\"odd\",\"in\",\"100\",\"{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{HUANKUAN}->1000000003{SUCCESS}\",\"1000000003\",\"2015-06-06 16:41:02\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:41:02');
INSERT INTO `work_oddlog` VALUES ('91', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{LESS}1508000{SUCCESS}', 'oddRepayment', 'UPDATE system_userinfo SET fundMoney = (fundMoney - 100 ) WHERE userId = \"1508000\"', '2015-06-06 16:41:02');
INSERT INTO `work_oddlog` VALUES ('92', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{LESS}1508000{SUCCESS}', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000033\",\"work\",\"odd\",\"out\",\"100\",\"{SYSTEM}:{ODD}20150610000000000001{INTEREST}100,{LESS}1508000{SUCCESS}\",\"1508000\",\"2015-06-06 16:41:02\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:41:02');
INSERT INTO `work_oddlog` VALUES ('93', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001,{RELOAD}{INVEST}{SHOUYI}-->{SUCCESS}', 'oddRepayment', 'UPDATE work_oddinterest_invest SET operatetime = \"2015-06-06 16:41:02\", realMonery = \"10100\" WHERE oddNumber = \"20150610000000000001\" AND qishu = \"3\" AND oddMoneyId = \"3\"', '2015-06-06 16:41:02');
INSERT INTO `work_oddlog` VALUES ('94', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}{SERVICE}{MONEY}10,->1000000003{LESS}{SUCCESS}', 'oddRepayment', 'UPDATE system_userinfo SET fundMoney = (fundMoney - 10) WHERE userId = \"1000000003\"', '2015-06-06 16:41:02');
INSERT INTO `work_oddlog` VALUES ('95', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001{INTEREST}{SERVICE}{MONEY}10,->1000000003{LESS}{SUCCESS}', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000034\",\"work\",\"odd\",\"out\",\"10\",\"{SYSTEM}:{ODD}20150610000000000001{INTEREST}{SERVICE}{MONEY}10,->1000000003{LESS}{SUCCESS}\",\"1000000003\",\"2015-06-06 16:41:02\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:41:02');
INSERT INTO `work_oddlog` VALUES ('96', '20150610000000000001', 'sysadmin', '{SYSTEM}:{SYSTEM}:{ODD}20150610000000000001,{COMPANY}{GET}1000000003{INTEREST}{SERVICE}{MONEY}10，{OP', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000035\",\"work\",\"odd\",\"in\",\"10\",\"{SYSTEM}:{ODD}20150610000000000001,{COMPANY}{GET}1000000003{INTEREST}{SERVICE}{MONEY}10，{OPERATE}{SUCCESS}\",\"1508000\",\"2015-06-06 16:41:02\",\"sysadmin\",\"20150610000000000001\")', '2015-06-06 16:41:02');
INSERT INTO `work_oddlog` VALUES ('97', '', 'sysadmin', '{SYSTEM}:{HOUTAI}{LESS}:1000000001->19860->{OPERATE}{SUCCESS}', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000036\",\"user\",\"addmoney\",\"out\",\"19860\",\"{HOUTAI}{LESS}:1000000001->19860->{OPERATE}{SUCCESS}\",\"1000000001\",\"2015-06-06 19:45:16\",\"sysadmin\",\"\")', '2015-06-06 19:45:16');
INSERT INTO `work_oddlog` VALUES ('98', '', 'sysadmin', '{SYSTEM}:{HOUTAI}{LESS}:1000000001->19860->{OPERATE}{SUCCESS}', 'oddAuto', 'INSERT INTO user_moneylog (serialNumber,mkey,type,mode,mvalue,remark,userId,time,operator,oddNumber)  VALUES (\"20150606000000000037\",\"user\",\"addmoney\",\"in\",\"19860\",\"{HOUTAI}{LESS}:1000000001->19860->{OPERATE}{SUCCESS}\",\"1508000\",\"2015-06-06 19:45:16\",\"sysadmin\",\"\")', '2015-06-06 19:45:16');
INSERT INTO `work_oddlog` VALUES ('99', '20150613000000000001', 'sysadmin', '{SYSTEM}:{ODD}20150613000000000001{TRIAL}{END}-->{OPERATE}{SUCCESS}', 'oddTrial', 'UPDATE work_odd SET progress = \"start\",oddTrial = \"y\",oddTrialTime = \"2015-06-09 17:34:33\",oddTrialRemark = \"初审成功\" WHERE oddNumber = \"20150613000000000001\"', '2015-06-09 17:34:33');
INSERT INTO `work_oddlog` VALUES ('100', '20150613000000000001', 'sysadmin', '{SYSTEM}:{USER}:1000000001{JIEKUAN}:20000-->{OPERATE}{SUCCESS}', 'oddTrial', 'INSERT INTO work_oddmoney(oddNumber,type,money,userId,remark,time,status) VALUES (\"20150613000000000001\",\"loan\",\"20000\",\"1000000001\",\"{USER}:1000000001{JIEKUAN}:20000\",\"2015-06-09 17:34:33\",\"0\")', '2015-06-09 17:34:33');

-- ----------------------------
-- Table structure for work_oddlogerror
-- ----------------------------
DROP TABLE IF EXISTS `work_oddlogerror`;
CREATE TABLE `work_oddlogerror` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `oddNumber` varchar(20) DEFAULT NULL COMMENT '借款单号',
  `user` varchar(20) DEFAULT NULL COMMENT '用户名称',
  `remark` varchar(100) DEFAULT NULL COMMENT '说明',
  `type` enum('oddTrial','oddRehear','oddAuto','oddClaims','oddOther','oddRepayment','oddInvest') DEFAULT NULL COMMENT 'oddAuto自动投标',
  `sqllog` text COMMENT '操作的数据库语句',
  `time` datetime DEFAULT NULL COMMENT '操作时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of work_oddlogerror
-- ----------------------------

-- ----------------------------
-- Table structure for work_oddmoney
-- ----------------------------
DROP TABLE IF EXISTS `work_oddmoney`;
CREATE TABLE `work_oddmoney` (
  `id` int(40) unsigned NOT NULL AUTO_INCREMENT,
  `oddNumber` varchar(20) DEFAULT NULL COMMENT '借款单号',
  `type` enum('loan','invest') DEFAULT 'loan' COMMENT '投资类型',
  `money` double DEFAULT NULL COMMENT '金额',
  `userId` varchar(20) DEFAULT NULL COMMENT '用户',
  `remark` varchar(100) DEFAULT NULL COMMENT '备注',
  `time` datetime DEFAULT NULL COMMENT '操作时间',
  `status` enum('0','1','-1','2') DEFAULT '0' COMMENT '0冻结,1锁死,-1失效,2债权转让出去',
  `ckclaims` enum('0','1') DEFAULT '0' COMMENT '是否债权转让',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of work_oddmoney
-- ----------------------------
INSERT INTO `work_oddmoney` VALUES ('1', '20150610000000000001', 'loan', '20000', '1000000001', '{USER}:1000000001{JIEKUAN}:20000', '2015-06-06 16:39:04', '1', '0');
INSERT INTO `work_oddmoney` VALUES ('2', '20150610000000000001', 'invest', '10000', '1000000013', '{AUTOMATIC}：1000000002{INVEST}10000', '2015-06-06 16:39:09', '1', '1');
INSERT INTO `work_oddmoney` VALUES ('3', '20150610000000000001', 'invest', '10000', '1000000003', '{AUTOMATIC}：1000000003{INVEST}10000', '2015-06-06 16:39:09', '1', '0');
INSERT INTO `work_oddmoney` VALUES ('4', '20150613000000000001', 'loan', '20000', '1000000001', '{USER}:1000000001{JIEKUAN}:20000', '2015-06-09 17:34:33', '0', '0');

-- ----------------------------
-- Table structure for work_tradelog
-- ----------------------------
DROP TABLE IF EXISTS `work_tradelog`;
CREATE TABLE `work_tradelog` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `serialNumber` varchar(20) DEFAULT NULL COMMENT '流水号',
  `oddNumber` varchar(20) DEFAULT NULL COMMENT '标编号',
  `type` enum('in','out','frozen') DEFAULT NULL COMMENT '资金类型',
  `fromId` varchar(20) DEFAULT NULL COMMENT '出钱用户',
  `toId` varchar(20) DEFAULT NULL COMMENT '收钱用户',
  `money` double DEFAULT NULL COMMENT '操作金额',
  `addtime` datetime DEFAULT NULL,
  `status` enum('1','0','-1') DEFAULT NULL COMMENT '状态1:成功 0:申请中  -1:失败',
  `remark` text COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of work_tradelog
-- ----------------------------
