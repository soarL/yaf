create table system_smslog(
	id int(11) not null primary key auto_increment,
	userId varchar(20) not null default '' comment '发送用户ID',
	phone varchar(50) not null default '' comment '发送手机号',
	content varchar(250) not null default '' comment '发送内容',
	sendTime datetime not null default '1970-01-01 08:00:00' comment '发送时间',
	result varchar(50) not null default '' comment '返回结果'
) comment '短信日志表' default charset=utf8;

create table system_news_comment(
	id int(11) not null primary key auto_increment,
	parentId int(11) not null default 0 comment '上一级评论',
	userId varchar(20) not null default '' comment '用户ID',
	newsId int(11) not null default 0 comment '文章地址',
	content text comment '评论内容',
	addTime datetime not null default '1970-01-01 08:00:00' comment '评论时间',
	addIp varchar(50) not null default '' comment '评论IP',
	status tinyint(1) not null default 0 comment '评论状态:0-不显示,1-显示'
) comment '评论表' default charset=utf8;

alter table system_userinfo add column spreadCode varchar(20) not null default '' comment '推广码';

create table system_webmail(
	id int(11) not null primary key auto_increment,
	title varchar(150) not null default '' comment '标题',
	content text comment '内容',
	addTime datetime not null default '1990-01-01 08:00:00' comment '添加时间',
	addIp varchar(50) not null default '0.0.0.0' comment '添加IP',
	status tinyint(1) not null default 0 comment '信件状态:0-无效,1-有效',
	sendUser varchar(50) not null default '' comment '发送人用户名',
	sendType tinyint(1) not null default 0 comment '发送类型:0-单发;1-群发',
	sendUserType tinyint(1) not null default 0 comment '发送用户类型:0-系统管理员,1-网站用户',
	files varchar(255) not null default '' comment '附件'
) comment '站内信' default charset=utf8;

create table system_user_webmail(
	id int(11) not null primary key auto_increment,
	webmailId int(11) not null default 0 comment '站内信ID',
	username varchar(50) not null default '' comment '用户名',
	status tinyint(1) not null default 0 comment '信件状态:0-未读,1-已读,2-删除'
) comment '用户站内信';

alter table system_webmail add column receiveUser varchar(50) not null default '' comment '接收用户';

create table system_attribute(
	id int(11) not null primary key auto_increment,
	name varchar(50) not null default '' comment '属性名',
	value text not null comment '属性值'
) comment '系统属性';

alter table system_attribute add column type varchar(50) not null default '' comment '属性类型';

create table system_banner(
	id int(11) not null primary key auto_increment,
	title varchar(50) not null default '' comment '标题',
	type_id int(4) not null default 0 comment '类型ID',
	link varchar(150) not null default '' comment '跳转地址',
	banner varchar(150) not null default '' comment 'banner图片',
	addtime datetime not null default '1970-01-01 08:00:00' comment '添加时间',
	status tinyint(1) not null default 0 comment '状态:0-不显示,1-显示'
) comment '轮播图' default charset=utf8;

create table system_banner_type(
	id int(11) not null primary key auto_increment,
	name varchar(50) not null default '' comment '名称',
	identity varchar(50) not null default '' comment '标识符'
) comment '轮播图类型' default charset=utf8;

create table system_video(
	id int(11) not null primary key auto_increment,
	title varchar(50) not null default '' comment '标题',
	area_id int(4) not null default 0 comment '地区ID',
	link varchar(150) not null default '' comment '视频地址',
	cover varchar(150) not null default '' comment '遮罩图片',
	addtime datetime not null default '1970-01-01 08:00:00' comment '添加时间',
	status tinyint(1) not null default 0 comment '状态:0-不显示,1-显示'
) comment '车库视频' default charset=utf8;

create table system_video_area(
	id int(11) not null primary key auto_increment,
	name varchar(50) not null default '' comment '名称',
	identity varchar(50) not null default '' comment '标识符'
) comment '车库视频地区' default charset=utf8;

alter table system_video_area drop column identity;
alter table system_banner_type drop column identity;

alter table system_smslog add column type varchar(50) not null default '' comment '短信类型';
alter table system_smslog add column sendCode varchar(50) not null default '' comment '短信验证码';

create table system_order(
	id int(4) not null primary key auto_increment,
	name varchar(50) not null default '' comment '姓名',
	phone varchar(50) not null default '' comment '手机号',
	province tinyint(1) not null default 0 comment '所在省份',
	city tinyint(1) not null default 0 comment '所在城市',
	need_money int(4) not null default 0 comment '借款金额',
	add_time datetime not null default '1970-01-01 08:00:00' comment '添加时间',
	status tinyint(1) not null default 0 comment '0-未处理,1-已回访'
) comment '预约借款表' default charset=utf8;

alter table system_userinfo add column thirdAccountStatus enum('0','1') not null default '0' comment '是否开通第三方托管';

create table user_bank_account(
	id int(4) not null primary key auto_increment,
	userId varchar(20) not null default '' comment '用户userId',
	bankNum varchar(30) not null default '' comment '卡号',
	bank tinyint(1) not null default 0 comment '开户行',
	bankUsername varchar(50) not null default ''  comment '开户用户姓名',
	province tinyint(1) not null default 0 comment '开户行所在省',
	city tinyint(1) not null default 0 comment '开户行所在市',
	subbranch varchar(150) not null default '' comment '支行名称',
	isDefault enum('y','n') not null default 'n' comment '是否默认',
	createAt datetime not null default '1990-01-01 08:00:00' comment '添加时间',
	updateAt datetime not null default '1990-01-01 08:00:00' comment '最后修改时间'
) comment '用户银行卡号' default charset=utf8;

create table user_withdraw(
	id int(4) not null primary key auto_increment,
	userId varchar(20) not null default '' comment '用户ID',
	outMoney double not null default '0' comment '提现金额',
	fee double not null default '0' comment '提现费用',
	tradeNo varchar(32) not null default '' comment '流水号',
	bank varchar(50) not null default '' comment '银行',
	province varchar(50) not null default '' comment '省份',
	city varchar(50) not null default '' comment '城市',
	subbranch varchar(150) not null default '' comment '支行',
	bankNum varchar(30) not null default '' comment '银行帐号',
	bankUsername varchar(50) not null default '' comment '开户人',
	addTime datetime default null comment '添加时间',
	validTime datetime default null comment '审核时间',
	status enum('0','1','2') not null default '0' comment '0-未提交;1-提交并成功;2-提交并失败',
	remark varchar(255) not null default '' comment '备注'
) comment '用户提现表' default charset=utf8;


/*create table user_recharge(
	id int(4) not null primary key auto_increment,
	userId varchar(20) not null default '' comment '用户ID',
	amount double not null default '0' comment '充值金额',
	realAmount double not null default '0' comment '实际到账金额',
	fee double not null default '0' comment '提现费用',
	tradeNo varchar(32) not null default '' comment '流水号',
	addTime datetime default null comment '添加时间',
	validTime datetime default null comment '审核时间',
	status enum('0','1','2') not null default '0' comment '0-未提交;1-提交并成功;2-提交并失败',
	remark varchar(255) not null default '' comment '备注'
) comment '用户充值表' default charset=utf8;*/

create table system_ranking(
	id int(4) not null primary key auto_increment,
	username varchar(50) not null default '' comment '用户名',
	tenderMoney double not null default '0' comment '充值金额',
	rankChange int(4) not null default 0 comment '排名变化'
) comment '排行榜' default charset=utf8;

create table user_vip(
	id int(4) not null primary key auto_increment,
	grade tinyint(1) not null default 1 comment 'vip等级',
	userId varchar(20) not null default '' comment '用户ID',
	addTime datetime default null comment '添加时间',
	endTime datetime default null comment '到期时间',
	customService int(4) not null default 0 comment '客服ID',
	status enum('0','1') not null default '0' comment '0-失效;1-正常'
) comment 'VIP用户' default charset=utf8;

create table user_vip_log(
	id int(4) not null primary key auto_increment,
	userId varchar(20) not null default '' comment '用户ID',
	addTime datetime default null comment '添加时间',
	applyTime int(11) not null default 0 comment '申请时长',
	applyMoney int(4) not null default 0 comment '支付金额',
	customService int(4) not null default 0 comment '客服ID',
	status enum('-1','0','1') not null default '0' comment '0-申请中;-1-审核失败;1-审核成功'
) comment 'VIP用户' default charset=utf8;

create table rybzb_users_gift(
	id int(4) not null primary key auto_increment,
	userId varchar(20) not null default '' comment '用户ID',
	type varchar(20) not null default '' comment '类型',
	gift tinyint(1) not null default 0 comment '礼品',
	giftName varchar(50) not null default '' comment '礼品名称',
	addTime datetime default null comment '添加时间'
) comment '抽奖表' default charset=utf8;

create table user_bid(
	id int(4) not null primary key auto_increment,
	tradeNo varchar(32) not null default '' comment '流水号',
	oddNumber varchar(20) not null default '' comment '标号',
	userId varchar(20) not null default '' comment '用户ID',
	bidMoney double not null default '0' comment '投标金额',
	status enum('0','1','2') not null default '0' comment '0-未返回;1-返回并成功;2-返回并失败',
	remark varchar(255) not null default '' comment '备注'
) comment '投标记录表' default charset=utf8;
alter table user_bid drop column transData;

alter table user_bid add column addTime datetime default null comment '添加时间';
alter table user_bid add column validTime datetime default null comment '返回结果时间';

create table system_emaillog(
	id int(11) not null primary key auto_increment,
	userId varchar(20) not null default '' comment '发送用户ID',
	email varchar(50) not null default '' comment '发送邮箱',
	sendCode varchar(50) not null default '' comment '验证码',
	sendTime datetime not null default '1970-01-01 08:00:00' comment '发送时间',
	sendType varchar(20) not null default '' comment '邮件类型'
) comment '邮件日志表' default charset=utf8;

alter table system_userinfo add column thirdAccountAuth enum('0','1') not null default '0' comment '0-未授权;1-已授权';
alter table user_bid add column batchNo varchar(32) not null default '' comment '批次号';

create table system_branch(
	id int(11) not null primary key auto_increment,
	name varchar(50) not null default '' comment '名称',
	email varchar(50) not null default '' comment '邮箱',
	phone varchar(20) not null default '' comment '电话',
	identity varchar(50) not null default '' comment '标识符',
	leader varchar(20) not null default '' comment '负责人',
	images varchar(255) not null default '' comment '公司照片',
	addTime datetime not null default '1970-01-01 08:00:00' comment '添加时间'
) comment '分公司表' default charset=utf8;

alter table user_withdraw add column xml text default null comment '发送的xml数据';
alter table user_withdraw add column result varchar(10) not null default '' comment '返回结果';
alter table user_moneyrecharge add column result varchar(10) not null default '' comment '返回结果';

create table user_claim(
	id int(4) not null primary key auto_increment,
	tradeNo varchar(32) not null default '' comment '流水号',
	batchNo varchar(32) not null default '' comment '批次号',
	claimId varchar(20) not null default '' comment '债权转让ID',
	userId varchar(20) not null default '' comment '用户ID',
	status enum('0','1','2') not null default '0' comment '0-未返回;1-返回并成功;2-返回并失败',
	remark varchar(255) not null default '' comment '备注',
	addTime datetime default null comment '添加时间',
	validTime datetime default null comment '返回结果时间',
	xml text default null comment '发送的xml数据',
	result varchar(10) not null default '' comment '返回结果'
) comment '债权购买记录表' default charset=utf8;

alter table system_emaillog add column status enum('0','1') not null default '0' comment '0-未验证;1-已验证';

alter table system_smslog add column checkTime tinyint(1) not null default 0 comment '验证次数';

alter table system_userinfo	add column loginErrTime tinyint(1) not null default 0 comment '登录错误次数';

alter table system_userinfo	add column lockTo datetime default null comment '锁定至（时间）';

create table user_login_log(
	id int(4) not null primary key auto_increment,
	userId varchar(20) not null default '' comment '用户ID',
	loginTime datetime default null comment '添加时间',
	loginIp varchar(20) not null default '' comment '登录IP'
) comment '用户登录日志' default charset=utf8;

alter table user_moneyrecharge add column payType varchar(20) not null default '' comment '支付方式';

create table user_question(
	id int(11) not null primary key auto_increment,
	username varchar(50) not null default '' comment '用户名',
	title varchar(255) not null default '' comment '标题',
	content text default null comment '内容',
	hitCount int(4) not null default 0 comment '点击量',
	answerCount int(4) not null default 0 comment '回答数',
	status enum('0','1','2') not null default '0' comment '是否生效',
	isHot enum('0','1') not null default '0' comment '是否热门',
	lastAnswerUser varchar(50) not null default '' comment '最后回答用户',
	lastAnswerTime datetime default null comment '最后回答时间',
	sort int(11) not null default 0 comment '排序',
	addTime datetime default null comment '添加时间'
) comment '用户问题表' default charset=utf8;

create table user_question_tab(
	id int(4) not null primary key auto_increment,
	content varchar(30) not null default '' comment '标题内容',
	color varchar(20) not null default '' comment '标签颜色',
	questionId int(11) not null default 0 comment '问题ID',
	addTime datetime default null comment '添加时间'
) comment '用户问题标签表' default charset=utf8;

create table user_question_answer(
	id int(11) not null primary key auto_increment,
	username varchar(50) not null default '' comment '用户名',
	questionId int(11) not null default 0 comment '问题ID',
	parentId int(11) not null default 0 comment '上级ID',
	usefulCount int(4) not null default 0 comment '有用数',
	replyCount int(4) not null default 0 comment '回复数',
	content text default null comment '回复内容',
	answerTime datetime default null comment '回答时间'
) comment '用户问题回答表' default charset=utf8;

create table user_sign_log(
	id int(11) not null primary key auto_increment,
	username varchar(50) not null default '' comment '用户名',
	continuousDay int(4) not null default 0 comment '连续天数',
	addTime datetime default null comment '签到时间'
) comment '用户签到表' default charset=utf8;

create table user_question_answer_useful(
	id int(11) not null primary key auto_increment,
	answerId int(11) not null default 0 comment '问题ID',
	username varchar(50) not null default '' comment '用户名',
	addTime datetime default null comment '添加时间'
) comment '用户问题答案有用表' default charset=utf8;

create table user_llagree(
	id int(11) not null primary key auto_increment,
	noAgree varchar(30) not null default '' comment '签约号',
	userId varchar(20) not null default '' comment '用户ID',
	acctName varchar(50) not null default '' comment '姓名',
	idNo varchar(20) not null default '' comment '身份号',
	lastUseTime datetime default null comment '最后使用时间',
	addTime datetime default null comment '添加时间'
) comment '连连支付签约卡' default charset=utf8;

alter table user_bank_account add column noAgree varchar(30) not null default '' comment '连连支付签约号';
alter table user_bank_account add column status enum('0','1') not null default '1' comment '银行卡状态';
alter table user_moneyrecharge add column bankCard varchar(30) not null default '' comment '银行卡号';
alter table user_llagree add column bankCard varchar(30) not null default '' comment '银行卡号';
alter table user_llagree add column bankCode varchar(15) not null default '' comment '银行编号';

create table user_bank_unbind(
	id int(4) not null primary key auto_increment,
	bankId int(4) not null default 0 comment '银行卡ID',
	userId varchar(20) not null default '' comment '用户ID',
	status enum('-1','0','1','2') not null default '0' comment '0-未处理;1-处理中;2-已解绑;-1-解绑失败',
	addTime datetime default null comment '添加时间'
) comment '解除绑定申请' default charset=utf8;

alter table user_question add column type enum('normal', 'ceo') not null default 'normal' comment '问题类型';
alter table user_question_answer add column status enum('0','1','2') not null default '0' comment '是否生效';

create table auth_role(
	id int(4) not null primary key auto_increment,
	name varchar(50) not null unique default '名称',
	display_name varchar(50) not null default '显示名称',
	description varchar(255) not null default '描述',
	created_at datetime default null comment '添加时间',
	updated_at datetime default null comment '修改时间'
) comment '用户角色' default charset=utf8;

create table auth_permission(
	id int(4) not null primary key auto_increment,
	name varchar(50) not null unique default '名称',
	display_name varchar(50) not null default '显示名称',
	description varchar(255) not null default '描述',
	created_at datetime default null comment '添加时间',
	updated_at datetime default null comment '修改时间'
) comment '用户权限' default charset=utf8;

create table auth_role_permission(
	role_id int(4) not null comment '角色ID',
	permission_id int(4) not null comment '权限ID',
	primary key (`role_id`,`permission_id`)
) comment '用户权限' default charset=utf8;

create table auth_user_role(
	userId varchar(20) not null comment '用户ID',
	role_id int(4) not null comment '角色ID',
	primary key (`userId`,`role_id`)
) comment '用户权限' default charset=utf8;

create table act_prize(
	id int(4) not null primary key auto_increment,
	prizeName varchar(50) not null default '' comment '奖品名称',
	prizeCash int(4) not null default 0 comment '奖品所需现金卷'
) comment '活动奖品表' default charset=utf8;

create table act_user_prize(
	id int(4) not null primary key auto_increment,
	userId varchar(20) not null default 0 comment '用户ID',
	prizeId int(4) not null default 0 comment '奖品ID',
	status tinyint(1) not null default 0 comment '0:审核中;1:审核成功;2:已发货;-1:审核失败',
	remark varchar(255) not null default '' comment '备注',
	addtime datetime default null comment '添加时间',
	validtime datetime default null comment '审核时间'
) comment '活动用户兑奖记录' default charset=utf8;

create table act_user_address(
	id int(4) not null primary key auto_increment,
	userId varchar(20) not null default '' comment '用户ID',
	name varchar(50) not null default '' comment '收货人',
	address varchar(100) not null default '' comment '地址',
	addressDetail varchar(100) not null default '' comment '详细地址',
	phone varchar(20) not null default '' comment '手机号',
	zipcode varchar(10) not null default '' comment '邮编',
	addtime datetime default null comment '添加时间'
) comment '活动用户收货地址' default charset=utf8;

insert into act_prize(`id`,`prizeName`, `prizeCash`) values(1, '118元现金红包', '30000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(2, '198元现金红包', '50000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(3, '398元现金红包', '100000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(4, '888元现金红包', '190000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(5, '1388元现金红包', '290000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(6, '2388元现金红包', '480000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(7, '3888元现金红包', '770000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(8, '4888元现金红包', '960000');

insert into act_prize(`id`,`prizeName`, `prizeCash`) values(9, '费列罗榛果威化巧克力', '10000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(10, '三只松鼠夏威夷果', '10000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(11, '金士顿16G U盘', '10000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(12, '毕加索（pimio）PS-903瑞典花王钢笔', '30000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(13, '小熊（Bear）JSQ-A30Q1加湿器', '30000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(14, '瑞士军刀双肩电脑背包', '30000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(15, '360安全路由器 P1', '30000');

insert into act_prize(`id`,`prizeName`, `prizeCash`) values(16, '飞利浦（PHILIPS）YQ306/16 水洗设计电动剃须刀', '50000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(17, '海尔（Haier） 家用车载空气净化器', '50000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(18, '膳魔师（THERMOS）300ml不锈钢轻量保温杯时尚保温水', '50000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(19, 'Ferrari香水', '50000');

insert into act_prize(`id`,`prizeName`, `prizeCash`) values(20, '漫步者（EDIFIER）R1700BT 4吋2.0蓝牙音箱', '100000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(21, '九阳（Joyoung）免滤全钢多功能豆浆机', '100000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(22, '博朗（BRAUN）欧乐BS15.523.2 声波式电动牙刷', '100000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(23, '苏泊尔电压力锅高压锅CYSB50FC88Q-100球型内胆', '100000');

insert into act_prize(`id`,`prizeName`, `prizeCash`) values(24, '飞利浦HR1871/00榨汁机', '190000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(25, '红米Note 3', '190000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(26, '沃尔卡（walka）加强型 侧护儿童安全座椅', '190000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(27, '巴宝莉盒装香水', '190000');


insert into act_prize(`id`,`prizeName`, `prizeCash`) values(28, 'roscenic（浦桑尼克）Pro-COCO 智能扫地机器人', '290000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(29, '飞利浦HD9232/30 airfyer空气炸锅', '290000');

insert into act_prize(`id`,`prizeName`, `prizeCash`) values(30, '索尼（SONY） ILCE-5000L 微单单镜套机', '480000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(31, 'Beats Solo2 Wireless 头戴式贴耳蓝牙耳机', '480000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(32, '巴宝莉套装香水', '480000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(33, 'GUCCI套装香水', '480000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(34, '万宝龙钱包', '480000');

insert into act_prize(`id`,`prizeName`, `prizeCash`) values(35, '海尔（Haier）BCD-648WDPF 648升 风冷无霜对开门冰箱', '770000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(36, 'Apple Watch Sport智能手表', '770000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(37, 'Apple iPad Air 2 9.7英寸平板电脑16G', '770000');

insert into act_prize(`id`,`prizeName`, `prizeCash`) values(38, 'Apple iPhone 6s 16G', '960000');
insert into act_prize(`id`,`prizeName`, `prizeCash`) values(39, '佳能（Canon） EOS 700D 套机(18-135mm)微单', '960000');

create table system_history(
	id int(4) not null primary key auto_increment,
	name varchar(50) not null default '' comment '名称',
	content varchar(255) not null default '' comment '内容',
	happened_at date default null comment '发生时间',
	created_at datetime default null comment '添加时间'
) comment '公司历程' default charset=utf8;

alter table system_filiale_message add column photos text comment '照片';
alter table system_activity add column photos text comment '照片';
alter table system_activity add column type enum('online', 'offline') not null default 'online' comment '照片';

alter table system_filiale_message add column type enum('both', 'contact', 'branch') not null default 'contact' comment '类型';

alter table user_moneyrecharge add column payWay varchar(10) not null default '1' comment '支付方式';

alter table user_claim add column `money` double DEFAULT 0 COMMENT '实际购买金额';
alter table user_claim add column `reward` double DEFAULT 0 COMMENT '项目奖励';

create table user_spread_extract(
	id int(4) not null primary key auto_increment,
	userId varchar(20) not null default '' comment '用户ID',
	extract_money double not null default 0 comment '提取金额',
	created_at datetime default '1970-01-01 08:00:00' comment '添加时间'
) comment '推广奖励提取记录' default charset=utf8;


SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for auth_permission_role
-- ----------------------------
DROP TABLE IF EXISTS `auth_permission_role`;
CREATE TABLE `auth_permission_role` (
  `permission_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `permission_role_role_id_foreign` (`role_id`),
  CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `auth_permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `auth_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for auth_permissions
-- ----------------------------
DROP TABLE IF EXISTS `auth_permissions`;
CREATE TABLE `auth_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for auth_role_user
-- ----------------------------
DROP TABLE IF EXISTS `auth_role_user`;
CREATE TABLE `auth_role_user` (
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_user_role_id_foreign` (`role_id`),
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `auth_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for auth_roles
-- ----------------------------
DROP TABLE IF EXISTS `auth_roles`;
CREATE TABLE `auth_roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `user_protocols` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` varchar(20) not null default '' comment '用户ID',
  `oddMoneyId` int(11) not null default 0 comment '投标ID',
  `protocolName` varchar(255) not null default '',
  `type` enum('odd', 'claim') not null default 'odd',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

create table system_jobs (
	id int(11) not null primary key auto_increment,
	name varchar(255) not null default '' comment '工作名称',
	dp_id int(4) not null default 0 comment '部门ID',
	salary varchar(255) not null default '' comment '薪水',
	experience varchar(255) not null default '' comment '工作经验',
	education varchar(255) not null default '' comment '学历',
	work_time varchar(255) not null default '' comment '工作时间',
	address varchar(255) not null default '' comment '工作地点',
	duty varchar(255) not null default '' comment '职责',
	requirement varchar(255) not null default '' comment '要求',
	welfare varchar(255) not null default '' comment '福利',
	status enum('active', 'invalid') not null default 'active' comment '状态',
	`created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  	`updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) comment '招聘' default charset=utf8;

create table system_departments (
	id int(11) not null primary key auto_increment,
	name varchar(50) not null default '' comment '名称',
	identifier varchar(50) not null unique default '' comment '标识符'
) comment '部门' default charset=utf8;

alter table user_llagree add column type enum('lianlian', 'baofoo') not null default 'lianlian' comment '类型';

alter table system_userinfo add column userType enum('1','2') not null default '1' comment '用户类型';

create table system_look_votes (
	`id` int(11) not null primary key auto_increment,
	`userId` varchar(20) not null default '' comment '用户ID',
	`oddNumber` varchar(20) not null default '' comment '标号',
	`created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	`updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) comment '查标投票表' default charset=utf8;

create table system_look_odds (
	`id` int(11) not null primary key auto_increment,
	`oddNumber` varchar(20) not null default '' comment '标号',
	`num` int(11) not null default 0 comment '票数',
	`link` varchar(50) not null default '' comment '查标地址',
	`period` int(4) not null default 0 comment '期数',
	`created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	`updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) comment '查标表' default charset=utf8;

alter table work_odd add column freezeMoney double(20, 2) not null default 0 comment '正在投资中的金额';
alter table work_odd add column isUserLook enum('y', 'n') not null default 'n' comment '标的是否被查';

create table user_crtr(
	id int(4) not null primary key auto_increment,
	tradeNo varchar(32) not null default '' comment '流水号',
	crtr_id int(11) not null default 0 comment '债权号',
	userId varchar(20) not null default '' comment '用户ID',
	money double not null default '0' comment '购买',
	status enum('0','1','2','3') not null default '0' comment '0-未返回;1-返回并成功;2-返回并失败;3-超时',
	addTime datetime default null comment '添加时间',
	validTime datetime default null comment '返回结果时间',
	batchNo varchar(32) not null default '' comment '流水号',
	remark varchar(255) not null default '' comment '备注'
) engine innodb comment '债权购买记录表' default charset=utf8;

alter table work_odd add column oddStyle enum('normal', 'newhand') not null default 'normal' comment '标的类型';

create table user_log(
	id int(4) not null primary key auto_increment,
	userId varchar(20) not null default '' comment '用户ID',
	type enum('normal', 'auto') not null default 'normal' comment '类型',
	data text comment '数据',
	change_time datetime default null comment '添加时间',
	remark varchar(255) not null default '' comment '备注'
) comment '用户设置日志' default charset=utf8;

alter table user_moneyrecharge add column media varchar(10) not null default 'pc' comment '操作媒体';
alter table user_withdraw add column media varchar(10) not null default 'pc' comment '操作媒体';
alter table user_bid add column media varchar(10) not null default 'pc' comment '操作媒体';
alter table work_oddmoney add column media varchar(10) not null default 'pc' comment '操作媒体';
alter table user_crtr add column media varchar(10) not null default 'pc' comment '操作媒体';
alter table work_CreditAss add column media varchar(10) not null default 'pc' comment '操作媒体';

create table user_ancun_data(
	id int(4) not null primary key auto_increment,
	userId varchar(20) not null default '' comment '用户ID',
	tradeNo varchar(50) not null default '' comment '流水号',
	type varchar(10) not null default '' comment '类型',
	flow tinyint(1) not null default 0 comment '流程号',
	recordNo varchar(50) not null default '' comment '保全号',
	sendTime datetime default null comment '传输时间'
) comment '用户安存日志' default charset=utf8;

alter table system_userinfo add column bindThirdTime datetime default null comment '绑定第三方时间';
alter table system_userinfo add column certificationTime datetime default null comment '实名认证时间';
alter table work_CreditAss add column serviceMoney double not null default 0 comment '服务费';

create table auth_actions(
	id int(4) not null primary key auto_increment,
	name varchar(150) not null default '' comment '名称',
	identifier varchar(20) not null default '' comment '标识符',
	is_menu enum('y', 'n') not null default 'n' comment '是否是菜单',
	parent_id int(4) not null default 0 comment '上级行为',
	domain enum('www', 'user') not null default 'user' comment '所属域',
	module enum('Index', 'Admin') not null default 'Admin' comment '模块',
	link varchar(30) not null default '' comment '链接，不包含模块名',
	permissions varchar(150) not null DEFAULT '' comment '所需权限',
	description varchar(255) not null default '' comment '描述',
	icon varchar(20) not null default '' comment '菜单图标',
	rank int(4) not null default 0 comment '排序',
	created_at timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  	updated_at timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) comment '行为表' default charset=utf8;

create table system_lotteries(
	id int(4) not null primary key auto_increment,
	sn varchar(50) not null default '' comment '编码',
	status tinyint(1) not null default 0 comment '0：未被获取，1：已获取未使用，2：已使用',
	type enum('withdraw', 'interest', 'money') not null default 'interest' comment '类型',
	useful_day int(4) not null default 30 comment '有效天数',
	remark varchar(255) not null default '' comment '备注',
	money_rate double not null default 0 comment '金额或利率',
	money_lower double default null comment '面值范围-最小',
	money_uper double default null comment '面值范围-最大',
	period_lower tinyint(1) default null comment '期限范围-最小',
	period_uper tinyint(1) default null comment '期限范围-最大',
	userId varchar(20) not null default '' comment '用户ID',
	get_at datetime DEFAULT NULL comment '获取时间',
	used_at datetime DEFAULT NULL comment '使用时间',
	endtime datetime DEFAULT NULL comment '有效时间',
	created_at timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  	updated_at timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) comment '券表' default charset=utf8;

alter table user_withdraw add column lotteryId int(4) not null default 0 comment '提现券';

create table user_bespokes(
	`id` int(4) not null primary key auto_increment,
	`userId` varchar(20) not null default '' comment '用户ID',
	`money` double not null default 0 comment '金额',
	`status` tinyint(1) not null default 0 comment '0：未回访，1：回访并确认，2：不预约',
	`time` datetime DEFAULT NULL comment '约标日期',
	`remark` varchar(255) not null default '' comment '备注',
	`created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  	`updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) comment '约标表' default charset=utf8;

create table system_expect_odds(
	`id` int(4) not null primary key auto_increment,
	`title` varchar(150) not null default '' comment '标名',
	`money` double not null default 0 comment '金额',
	`period` tinyint(1) not null default '0' comment '期限',
	`type` tinyint(1) not null default '0' comment '类型',
	`yearRate` varchar(50) not null default '' comment '年化率',
	`time` tinyint(1) not null default '0' comment '时间',
	`day` date default null comment '日期',
	`news_id` int(4) not null default 0 comment '新闻ID',
	`created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  	`updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) comment '预发标表' default charset=utf8;

create table user_settings(
	`id` int(4) not null primary key auto_increment,
	`userId` varchar(20) not null default '' comment '用户ID',
	`spread_show` tinyint(1) not null default '1' comment '投资信息是否显示给推荐人(0:显示,1:显示)',
	`created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  	`updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) comment '用户设置表' default charset=utf8;

create table system_cs_staff(
	`id` int(4) not null primary key auto_increment,
	`nick_name` varchar(50) not null default '' comment '昵称',
	`name` varchar(50) not null default '' comment '姓名',
	`qq` varchar(20) not null default '' comment 'QQ号',
	`created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  	`updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) comment '客服表' default charset=utf8;

create table act_guoqing2016_lotteries(
	`id` int(4) not null primary key auto_increment,
	`userId` varchar(20) not null default '' comment '用户ID',
	`num` varchar(20) not null default '' comment '编号',
	`type` enum('A', 'B', 'C') not null default 'A' comment '类型',
	`created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  	`updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) comment '国庆活动抽奖券' default charset=utf8;

create table user_integral(
	`id` int(4) not null primary key auto_increment,
	`ref_id` int(11) not null default '0' comment '对应ID',
	`userId` varchar(20) not null default '0' comment '用户ID',
	`type` enum('repayment', 'sign') not null default 'repayment' comment '类型',
	`money` double not null default 0 comment '本金',
	`integral` int(4) not null default 0 comment '积分',
	`total` int(4) not null default 0 comment '总积分',
	`remark` varchar(255) not null default '' comment '备注',
	`created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  	`updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) comment '预发标表' default charset=utf8;

alter table user_moneyrecharge add column bankCode varchar(20) not null default '' comment '银行编码';

alter table work_odd add COLUMN cerStatus tinyint(1) not null default 0 COMMENT '证明信息状态：0位合同状态, 1位发送安存';
alter table work_CreditAss add COLUMN cerStatus tinyint(1) not null default 0 COMMENT '证明信息状态：0位合同状态, 1位发送安存';

create table system_pv(
	`id` int(4) not null primary key auto_increment,
	`name` varchar(50) not null default '0' comment '名称',
	`num` int(11) not null default '0' comment '次数',
	`created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  	`updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) comment 'pv表' default charset=utf8;


alter table user_llagree add column noAgreeThird varchar(50) not null default '' comment '第三方认证码';
alter table user_bank_account add column agreeID int(4) not null default 0 comment '认证ID';
alter table user_moneyrecharge add column thirdSerialNo varchar(50) not null default '' comment '第三方订单号';

alter table user_bespokes add column month VARCHAR(120) not null default '' COMMENT '投资期限';
alter table act_prize add column num int(4) not null default 0 comment '奖品数量';

create table act_user_packetes(
	`id` int(4) not null primary key auto_increment,
	`userId` varchar(20) not null default '' comment '用户ID',
	`money` tinyint(1) not null default '0' comment '金额',
	`status` tinyint(1) not null DEFAULT '0' comment '状态',
	`created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  	`updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) comment '用户现金红包表' default charset=utf8;

create table user_tender_money(
	`id` int(4) not null primary key auto_increment,
	`userId` varchar(20) not null default '' comment '用户ID',
	`money` int(4) not null default '0' comment '金额',
	`period` tinyint(1) not null default '0' comment '投资期限',
	`odd_type` varchar(20) not null default '' comment '标的类型',
	`money_last` int(4) not null default '0' comment '剩余金额',
	`oddMoneyId` int(11) not null default 0 comment '投资表ID',
	`created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  	`updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) comment '用户投资金额表' default charset=utf8;

alter table system_lotteries add column remark varchar(255) default '' comment '备注';

create table system_operation_logs(
	`id` int(4) not null primary key auto_increment,
	`userId` varchar(20) not null default '0' comment '用户ID',
	`content` varchar(255) not null default '' comment '操作内容',
	`action_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) comment '系统操作日志' default charset=utf8;

alter table user_tender_money engine=innodb;

alter table user_office add column industry varchar(80) not null default '' comment '行业';
alter table user_office add column position varchar(80) not null default '' comment '职位';
alter table user_office add column salary int(4) not null default 0 comment '月薪';
alter table user_office add column depart varchar(80) not null default '' comment '部门';
alter table user_office add column nature varchar(20) not null default '' comment '性质';

alter table system_userinfo add column addressLocal varchar(150) not null default '' comment '居住地';

create table user_basic(
	`id` int(4) not null primary key auto_increment,
	`userId` varchar(20) not null default '' comment '用户ID',
	`money` int(4) not null default '0' comment '金额',
	`period` tinyint(1) not null default '0' comment '投资期限',
	`odd_type` varchar(20) not null default '' comment '标的类型',
	`money_last` int(4) not null default '0' comment '剩余金额',
	`oddMoneyId` int(11) not null default 0 comment '投资表ID',
	`created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  	`updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) comment '用户投资金额表' default charset=utf8;


create table system_access_logs (
	`id` int(4) not null primary key auto_increment,
	`userId` varchar(20) not null default '' comment '用户ID',
	`url` varchar(100) not null default '' comment '访问URL',
	`refer` varchar(100) not null default '' comment '来源地址',
	`pm_id` int(4) not null default '0' comment '渠道ID',
	`ip` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '访问IP',
	`accessed_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) comment '网站访问记录' default charset=utf8;

CREATE TABLE `user_data_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` date NOT NULL COMMENT '日期，精确到天',
  `registrations` int(11) NOT NULL DEFAULT '0' COMMENT '注册人数',
  `newRechargeUserNum` int(11) NOT NULL DEFAULT '0' COMMENT '新用户（第一次充值的用户为新用户充值）充值（人数）',
  `oldRechargeUserNum` int(11) NOT NULL DEFAULT '0' COMMENT '老用户充值（人数）',
  `rechargeMoney` double NOT NULL DEFAULT '0' COMMENT '充值总额',
  `pcRechargeMoney` double NOT NULL DEFAULT '0' COMMENT 'pc充值金额',
  `appRechargeMoney` double NOT NULL DEFAULT '0' COMMENT 'app充值金额',
  `oddMoney` double NOT NULL DEFAULT '0' COMMENT '发标总额',
  `newInvestUserNum` int(11) NOT NULL DEFAULT '0' COMMENT '新用户投资(人数)',
  `oldInvestUserNum` int(11) NOT NULL DEFAULT '0' COMMENT '老用户投资(人数)',
  `investMoney` double NOT NULL DEFAULT '0' COMMENT '投资总额',
  `withdrawNum` int(11) NOT NULL DEFAULT '0' COMMENT '提现数量',
  `withdrawMoney` double NOT NULL DEFAULT '0' COMMENT '提现金额',
  `newLoanUserNum` int(11) NOT NULL DEFAULT '0' COMMENT '新用户借款(人数)',
  `oldLoanUserNum` int(11) NOT NULL DEFAULT '0' COMMENT '老用户借款(人数)',
  `loanMoney` double NOT NULL DEFAULT '0' COMMENT '借款总额',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='数据监控（运营部）（使用计划任务统计）';

ALTER TABLE `user_moneyrecharge`
ADD COLUMN `userType`  enum('2','1','0') NOT NULL DEFAULT '0' COMMENT '新老用户充值（第一次充值为新用户充值）：0：未处理，1：新用户，2：老用户' AFTER `thirdSerialNo`;

ALTER TABLE `work_oddmoney`
ADD COLUMN `userType`  enum('2','1','0') NOT NULL DEFAULT '0' COMMENT '新老用户投资/借款（第一次投资/借款为新用户投资/借款）：0：未处理，1：新用户，2：老用户' AFTER `lotteryId`;

ALTER TABLE `work_odd`
MODIFY COLUMN `oddBorrowStyle`  enum('sec','day','week','month') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '秒标是用来做活动的' AFTER `oddMultiple`;

ALTER TABLE `work_oddmoney`
MODIFY COLUMN `userType`  tinyint NOT NULL DEFAULT 0 COMMENT '新老用户投资/借款（第一次投资/借款为新用户投资/借款）：0：未处理，1：新用户，2：老用户' AFTER `lotteryId`;

UPDATE work_oddmoney SET userType = 0 WHERE userType = 3;
UPDATE work_oddmoney SET userType = 4 WHERE userType = 2;
UPDATE work_oddmoney SET userType = 2 WHERE userType = 1;
UPDATE work_oddmoney SET userType = 1 WHERE userType = 4;

ALTER TABLE `user_moneyrecharge`
MODIFY COLUMN `userType`  tinyint NOT NULL DEFAULT 0 COMMENT '新老用户充值（第一次充值为新用户充值）：0：未处理，1：新用户，2：老用户' AFTER `thirdSerialNo`;

UPDATE user_moneyrecharge SET userType = 0 WHERE userType = 3;
UPDATE user_moneyrecharge SET userType = 4 WHERE userType = 2;
UPDATE user_moneyrecharge SET userType = 2 WHERE userType = 1;
UPDATE user_moneyrecharge SET userType = 1 WHERE userType = 4;

CREATE TABLE `promotion_channel` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '渠道编号',
  `channelCode` varchar(10) NOT NULL COMMENT '渠道简称',
  `channelName` varchar(20) NOT NULL COMMENT '渠道名称',
  `enStatus` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用：1启用，2：不启用',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

alter table system_userinfo add column `channel_id` int(10) not null default 0 COMMENT '渠道ID';

CREATE TABLE `promotion_vv` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `channel_id` int(10) NOT NULL default 0 COMMENT '渠道ID',
  `pv` int(10) NOT NULL default 0 COMMENT 'PV',
  `uv` int(10) NOT NULL default 0 COMMENT 'UV',
  `date` date not null default '0000-00-00' comment '日期',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 存管 BEGIN --

alter table system_userinfo add column custody_id varchar(20) not null default '' COMMENT '存管账号';
alter table system_userinfo add column is_custody_pwd tinyint(1) not null default 0 comment '是否设置存管密码';
alter table system_userinfo add column auto_bid_auth varchar(30) not null default '' comment '自动投标签约订单号';
alter table system_userinfo add column auto_credit_auth varchar(30) not null default '' comment '自动债转签约订单号';

alter table user_bid add column result varchar(10) not null default '' COMMENT '投标结果';

alter table work_oddmoney add column authCode varchar(20) not null default '' COMMENT '存管授权码';

alter table user_bank_account add column binInfo varchar(150) not null default '' COMMENT '银行卡bin信息';

alter table user_crtr add column interest double(12, 2) not null default 0 COMMENT '利息部分';

ALTER TABLE `user_redpack` CHANGE COLUMN `giftMoney` `money`  double(12,2) NULL DEFAULT 0 AFTER `addtime`, MODIFY COLUMN `status`  tinyint(1) NULL DEFAULT 0 AFTER `money`;
alter table user_redpack add column type varchar(20) not null default '' comment '类型';
alter table user_redpack add column remark varchar(255) not null default '' comment '备注';

alter table user_moneylog add column frozen double(12, 2) not null default 0 comment '冻结金额';

alter table user_crtr add column result varchar(10) not null default '' comment '购买结果';

CREATE TABLE `work_odd_info` (
  `oddNumber` varchar(20) unique NOT NULL COMMENT '借款单编号,唯一',
  `oddExteriorPhotos` text COMMENT '外观图片json',
  `oddPropertyPhotos` text COMMENT '产权图片json',
  `bankCreditReport` text COMMENT '央行征信报告图片json',
  `otherPhotos` text COMMENT '借款手续json',
  `oddLoanRemark` text COMMENT '借款描述',
  `oddLoanControlList` varchar(400) DEFAULT NULL COMMENT '风控资料列表',
  `oddLoanControl` text COMMENT '风控说明',
  `controlPhotos` text COMMENT '风控图片',
  `validateCarPhotos` text COMMENT '验车图片',
  `contractVideoUrl` varchar(100) DEFAULT '' COMMENT '签约视频URL'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

alter table work_odd add column successMoney double(12, 2) not null default 0  comment '成功金额' AFTER `oddMoney`;
alter table work_odd add column fullTime datetime not null default '0000-00-00 00:00:00' comment '满标时间' AFTER `addtime`;
ALTER TABLE `user_moneylog`
MODIFY COLUMN `mode`  enum('in','out','freeze','unfreeze','sync') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'in' COMMENT 'in:进 out:出 freeze:冻结 unfreeze:解冻' AFTER `type`;

alter table work_odd drop column freezeMoney;
alter table work_odd drop column oddMoneyLast;
alter table user_bid drop column batchNo;

alter table user_moneylog drop column serialNumber;
alter table user_moneylog drop column oddNumber;
alter table user_moneylog drop column mkey;
alter table user_moneylog drop column status;
alter table user_moneylog drop column xml;
alter table user_moneylog drop column resultStatus;
alter table user_moneylog drop column investUserId;
alter table user_moneylog drop column operator;

CREATE TABLE `user_odd_orders` (
	`id` int(10) unsigned not null auto_increment primary key;
	`userId` varchar(20) NOT NULL COMMENT '用户ID',
	`oddNumber` varchar(20) NOT NULL COMMENT '标的号',
	`money` double(12, 2) NOT NULL DEFAULT 0  COMMENT '预约金额',
	`created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  	`updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) comment '用户约标表' ENGINE=MyISAM DEFAULT CHARSET=utf8;

alter table work_oddmoney add column remain double(12, 2) not null default 0  comment '剩余金额' AFTER `money`;

alter table work_CreditAss drop column freezeMoney;
alter table work_CreditAss drop column oddMoneyLast;
alter table work_CreditAss add column successMoney double(12, 2) not null default 0  comment '成功金额' AFTER `money`;
alter table work_CreditAss add column outtime datetime not null default '0000-00-00 00:00:00' comment '过期时间';
alter table work_CreditAss add column period tinyint(1) not null default '0' comment '出售总期数';

drop table user;

alter table system_userinfo drop column creditMonery;
alter table system_userinfo drop column mortgageMonery;
alter table system_userinfo drop column guaranteeMonery;
alter table system_userinfo drop column `userlinkstatus`;
alter table system_userinfo drop column `userofficestatus`;
alter table system_userinfo drop column `userhousestatus`;
alter table system_userinfo drop column `codeInput`;
alter table system_userinfo drop column `imiUser`;

alter table system_userinfo drop column `creditorNum`;
alter table system_userinfo drop column `loanUserId`;
alter table system_userinfo drop column `currentPrincipal`;
alter table system_userinfo drop column `currentPrincipalFrozen`;
alter table system_userinfo drop column `currentInterest`;
alter table system_userinfo drop column `currentInterestFrozen`;
alter table system_userinfo drop column `overdue`;
alter table system_userinfo drop column `cardimgz`;
alter table system_userinfo drop column `cardimgf`;

ALTER TABLE `system_userinfo`
MODIFY COLUMN `userimg`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '用户头像' AFTER `username`,
MODIFY COLUMN `qq`  varchar(12) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'QQ号码' AFTER `userimg`,
MODIFY COLUMN `phone`  varchar(12) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '手机号码' AFTER `qq`;

update system_userinfo set userimg='' where userimg is null;
update system_userinfo set qq='' where qq is null;

alter table system_userinfo drop column `oldIntegral`;

-- 删除前需要把相应字段转移到 work_odd_info
alter table work_odd drop column `oddExteriorPhotos`;
alter table work_odd drop column `oddPropertyPhotos`;
alter table work_odd drop column `bankCreditReport`;
alter table work_odd drop column `otherPhotos`;
alter table work_odd drop column `oddLoanRemark`;
alter table work_odd drop column `oddLoanControlList`;
alter table work_odd drop column `oddLoanControl`;
alter table work_odd drop column `controlPhotos`;
alter table work_odd drop column `validateCarPhotos`;
alter table work_odd drop column `contractVideoUrl`;
alter table work_odd drop column `oddTrial`;
alter table work_odd drop column `oddRehear`;
alter table work_odd drop column `imageUploadStatus`;
alter table work_odd drop column `ancun`;
alter table work_odd drop column `salesman`;
alter table work_odd drop column `salesmanRate`;
alter table work_odd drop column `oddLoanFine`;
ALTER TABLE `work_odd`
MODIFY COLUMN `oddTrialRemark`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '初审备注' AFTER `oddTrialTime`,
MODIFY COLUMN `oddRehearRemark`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '复审备注' AFTER `oddRehearTime`;

ALTER TABLE `work_odd`
MODIFY COLUMN `lookstatus`  tinyint(1) NULL DEFAULT 0 COMMENT '是否显示' ,
MODIFY COLUMN `investType`  tinyint(1) NULL DEFAULT 0 COMMENT '0自动，1手动' AFTER `lookstatus`,
MODIFY COLUMN `readstatus`  tinyint(1) NULL DEFAULT 0 COMMENT '是否预发' AFTER `investType`;

update work_odd set lookstatus=0 where lookstatus=1;
update work_odd set lookstatus=1 where lookstatus=2;

update work_odd set investType=0 where investType=1;
update work_odd set investType=1 where investType=2;

update work_odd set readstatus=0 where readstatus=1;
update work_odd set readstatus=1 where readstatus=2;

alter table work_oddautomatic drop column `investDay`;
alter table work_oddautomatic drop column `investType`;
alter table work_oddautomatic drop column `investEgisMoneyStatus`;
alter table work_oddautomatic drop column `typesJson`;
alter table work_oddautomatic add column types varchar(100) not null default '' comment '类型';

ALTER TABLE `work_oddinterest`
CHANGE COLUMN `advance` `status`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '0未还 1已还 2提前 3逾期' AFTER `operatetime`;

ALTER TABLE `work_oddinterest_invest`
ENGINE=InnoDB;

ALTER TABLE `user_integral`
ENGINE=InnoDB;

CREATE TABLE `work_custody_batch` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `batchNo` varchar(20) not null default '' comment '批次号',
  `refNum` varchar(20) not null default '' comment '标的号或债权号',
  `type` varchar(20) not null default '' COMMENT 'repayment:还款，rehear:复审,trial:初审',
  `sendTime` datetime not null default '0000-00-00 00:00:00' COMMENT '发送时间',
  `sendData` varchar(255) not null DEFAULT '' COMMENT '发送的数据',
  `checkResult` varchar(255) not null DEFAULT '' COMMENT '存管合法性结果',
  `checkTime` datetime  not null default '0000-00-00 00:00:00' COMMENT '合法性结果返回时间',
  `returnResult` varchar(255) not null DEFAULT '' COMMENT '存管处理结果',
  `returnTime` datetime not null default '0000-00-00 00:00:00' COMMENT '业务结果返回时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

alter table `work_custody_batch` add column `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0未处理 1处理成功 -1处理失败';

ALTER TABLE `work_oddinterest_invest`
CHANGE COLUMN `strUserId` `userId`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '已回款用户ID' AFTER `operatetime`;

ALTER TABLE `work_oddinterest_invest`
CHANGE COLUMN `realMonery` `realAmount`  double(20,2) NOT NULL DEFAULT 0.00 COMMENT '实还金额' AFTER `yuEr`;

ALTER TABLE `work_oddinterest`
CHANGE COLUMN `realMonery` `realAmount`  double NOT NULL DEFAULT 0 COMMENT '实还金额' AFTER `yuEr`;

alter table user_crtr add column fee double(12, 2) not null default 0 comment '转让人支付的服务费';
-- alter table system_userinfo rename users;
-- 存管 END --
alter table work_odd add column `signMoney` double(12, 2) not null default 0 COMMENT '呈签金额' AFTER oddMoney;
alter table work_odd_copy add column `signMoney` double(12, 2) not null default 0 COMMENT '呈签金额' AFTER oddMoney;

CREATE TABLE `system_traffic_hour` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `hour` int(10) not null default 0 comment '小时',
  `pm_key` varchar(20) not null default '' comment '推广码',
  `pv` int(4) not null default '0' COMMENT 'PV',
  `uv` int(4) not null default '0' COMMENT 'UV',
  `ip` int(4) not null DEFAULT '0' COMMENT 'IP',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `system_traffic_day` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `date` int(10) not null default 0 comment '日期',
  `pm_key` varchar(20) not null default '' comment '推广码',
  `pv` int(4) not null default '0' COMMENT 'PV',
  `uv` int(4) not null default '0' COMMENT 'UV',
  `ip` int(4) not null DEFAULT '0' COMMENT 'IP',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `system_traffic_month` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `month` int(10) not null default 0 comment '月份',
  `pm_key` varchar(20) not null default '' comment '推广码',
  `pv` int(4) not null default '0' COMMENT 'PV',
  `uv` int(4) not null default '0' COMMENT 'UV',
  `ip` int(4) not null DEFAULT '0' COMMENT 'IP',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `system_traffic_week` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `monday` int(10) not null default 0 comment '周一',
  `pm_key` varchar(20) not null default '' comment '推广码',
  `pv` int(4) not null default '0' COMMENT 'PV',
  `uv` int(4) not null default '0' COMMENT 'UV',
  `ip` int(4) not null DEFAULT '0' COMMENT 'IP',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

alter table system_userinfo drop column channel_id;
alter table system_userinfo add column `channelCode` varchar(10) not null default '' COMMENT '渠道KEY';

CREATE TABLE `system_config` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) not null unique comment '属性名',
  `display_name` varchar(100) not null default '' comment '属性名称',
  `type` varchar(20) not null default 'common' comment '类型',
  `value` varchar(255) not null default '' COMMENT '属性值',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

alter table work_odd add column `isCr` tinyint(1) not null default 0 COMMENT '是否代偿';

ALTER TABLE `work_oddautomatic`
MODIFY COLUMN `autostatus`  tinyint(1) NULL DEFAULT '0' COMMENT '是否开启自动投标' AFTER `id`;

UPDATE work_oddautomatic set autostatus=0 where autostatus=1;
UPDATE work_oddautomatic set autostatus=1 where autostatus=2;

alter table work_odd add column firstFigure varchar(120) not null default '' comment '首图';

# master 分支
alter table system_userinfo add column `media` varchar(10) not null default 'pc' COMMENT '操作媒体';

alter table user_bid add column `lottery_id` int(4) not null default 0 COMMENT '奖券ID';
alter table work_oddautomatic add column `lottery_id` int(4) not null default 0 COMMENT '奖券ID';
alter table system_lotteries add column `pay_status` tinyint(1) not null default 0 COMMENT '支付状态 0 不可支付 1 可支付 2 已支付';

ALTER TABLE `work_odd`
CHANGE COLUMN `oddLoanServiceFees` `serviceFee`  double(12,2) NOT NULL DEFAULT 0 COMMENT '借款服务费' AFTER `oddBorrowValidTime`;

alter table work_odd add column `gpsFee` double(12,2) not null default 0 COMMENT 'gps费用' AFTER `serviceFee`;

CREATE TABLE `work_bail_repay` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `oddNumber` varchar(20) not null default '' comment '标的号',
  `orgBatchNo` varchar(20) not null default '' comment '代偿批次号',
  `batchNo` varchar(20) not null default '' comment '偿还批次号',
  `period` int(10) not null default 0 comment '期数',
  `items` text comment '代偿债权记录',
  `status` tinyint(1) not null default 0 comment '0:未偿还 1:已偿还 -1:偿还失败',
  `addTime` datetime not null default '0000-00-00 00:00:00' COMMENT '添加时间',
  `sendTime` datetime not null default '0000-00-00 00:00:00' COMMENT '请求时间',
  `returnTime` datetime not null default '0000-00-00 00:00:00' COMMENT '返回结果时间',
  `result` varchar(10) not null default '' comment '返回结果',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `work_odd`
MODIFY COLUMN `progress`  enum('prep','published','start','review','run','end','fail') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'prep' COMMENT '流程运行标识' AFTER `userId`;

alter table work_odd add column receiptUserId varchar(20) not null default '' comment '收款人ID';
alter table work_odd add column receiptStatus tinyint(1) not null default 0 comment '受托支付状态 0不受托 1受托';

alter table work_odd add column isATBiding tinyint(1) not null default 0 comment '是否在自动投标 0是 1否';

CREATE TABLE `user_sync_log` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `tradeNo` varchar(30) not null default '' comment '订单号',
  `userId` varchar(20) not null default '' comment '用户ID',
  `tranType` varchar(20) not null default '' COMMENT '交易类型',
  `mode` enum('in','out','freeze','unfreeze') DEFAULT 'in' COMMENT 'in:进，out:出',
  `money` double(12, 2) not null DEFAULT 0 COMMENT '交易金额',
  `addTime` datetime  not null default '1970-01-01 08:00:00' COMMENT '同步时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

alter table user_moneylog add column source tinyint(1) not null default 0 comment '来源 0平台 1存管';

ALTER TABLE `user_integral`
MODIFY COLUMN `type`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '类型' AFTER `userId`;

alter table work_odd add column publishTime datetime not null default '1970-01-01 08:00:00' comment '标的发布时间' AFTER `addtime`;

CREATE TABLE `user_tran_logs` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `money` double(12, 1) NOT NULL default 0 COMMENT '转移金额',
  `addTime` datetime NOT NULL default '1970-01-01 08:00:00' COMMENT '添加时间',
  `handleTime` datetime NOT NULL default '1970-01-01 08:00:00' COMMENT '处理时间',
  `userId` varchar(20) not null default '' comment '用户ID',
  `status` tinyint(1) not null default '0' comment '状态 0 审核中 1成功 2失败', 
  `payStatus` tinyint(1) not null default '0' comment '状态 0 未处理 1已处理 2处理失败', 
  `result` varchar(20) not null default '' comment '处理结果', 
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `system_old_users` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `userId` varchar(20) not null default '' comment '用户ID',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

alter table auth_permissions add column act_id int(1) not null default 0 comment '行为ID';
alter table user_redpack add column orderId varchar(30) not null default '' comment '订单号';

alter table work_oddmoney add column cid int(4) not null default 0 comment '债权转让ID';

alter table user_bid add column lotteryId int(4) not null default 0 comment '奖券ID';

CREATE TABLE `custody_logs` (
  `id` int(10) not null auto_increment comment 'id',
  `acqcode` varchar(11) not null default '' comment '受理方标识码',
  `seqno` varchar(6) not null default '' comment '系统跟踪号',
  `cendt` varchar(10) not null default '' comment '交易传输时间',
  `cardnbr` varchar(19) not null default '' comment '主账号',
  `amount` varchar(12) not null default '' comment '交易金额',
  `crflag` varchar(1) not null default '' comment '交易金额符号',
  `msgtype` varchar(4) not null default '' comment '消息类型',
  `proccode` varchar(6) not null default '' comment '交易类型码',
  `mertype` varchar(4) not null default '' comment '商户类型',
  `term` varchar(8) not null default '' comment '受卡机终端标识码',
  `retseqno` varchar(12) not null default '' comment '检索参考号',
  `conmode` varchar(2) not null default '' comment '服务点条件码',
  `autresp` varchar(6) not null default '' comment '授权应答码',
  `forcode` varchar(11) not null default '' comment '发送方标识码',
  `clrdate` varchar(4) not null default '' comment '清算日期',
  `oldseqno` varchar(6) not null default '' comment '原始交易的系统跟踪号',
  `openbrno` varchar(6) not null default '' comment '发卡网点号',
  `tranbrno` varchar(6) not null default '' comment '交易网点',
  `ervind` varchar(1) not null default '' comment '冲正、撤销标志',
  `transtype` varchar(4) not null default '' comment '主机交易类型',
  `transdate` varchar(8) not null default '' comment '交易时间',
  primary key (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `custody_full_logs` (
  `id` int(10) not null auto_increment comment 'id',
  `bank` varchar(4) not null default '' comment '银行号',
  `cardnbr` varchar(19) not null default '' comment '电子账号',
  `amount` varchar(17) not null default '' comment '交易金额',
  `cur_num` varchar(3) not null default '' comment '货币代码',
  `crflag` varchar(1) not null default '' comment '交易金额符号',
  `valdate` varchar(8) not null default '' comment '入帐日期',
  `inpdate` varchar(8) not null default '' comment '交易日期',
  `reldate` varchar(8) not null default '' comment '自然日期',
  `inptime` varchar(8) not null default '' comment '交易时间',
  `tranno` varchar(6) not null default '' comment '交易流水号',
  `ori_tranno` varchar(6) not null default '' comment '关联交易流水号',
  `transtype` varchar(4) not null default '' comment '交易类型',
  `desline` varchar(42) not null default '' comment '交易描述',
  `curr_bal` varchar(17) not null default '' comment '交易后余额',
  `forcardnbr` varchar(19) not null default '' comment '对手交易帐号',
  `revind` varchar(1) not null default '' comment '冲正、撤销标志',
  primary key (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `user_redpack_batch` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `batchNo` varchar(20) not null default '' comment '批次号',
  `addTime` datetime not null default '1970-01-01 08:00:00' comment '添加时间',
  `items` text comment '数据项', 
  `status` tinyint(1) not null default '0' comment '状态 0 未处理 1处理成功 2处理失败', 
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- new
alter table work_odd drop column `offlineRate`;
alter table work_odd add column finishType tinyint(1) not null default 0 comment '完结类型 0 未还款  1 正常还款  2 提前还款 3 逾期还款';
alter table work_odd add column finishTime datetime not null default '1970-01-01 08:00:00' comment '完结时间';

alter table user_moneyrecharge drop column `mode`;
alter table user_moneyrecharge drop column `operator`;
alter table user_moneyrecharge drop column `source`;
alter table user_moneyrecharge drop column `payStatus`;
alter table user_moneyrecharge drop column `bankCard`;
alter table user_moneyrecharge drop column `bankCode`;
alter table user_moneyrecharge drop column `thirdSerialNo`;

alter table work_oddmoney add column bailBal double(12, 2) not null default 0 comment '代偿先还本金';

CREATE TABLE `user_degwithdraw` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `userId` varchar(20) NOT NULL DEFAULT '' COMMENT '用户ID',
  `oddNumber` varchar(20) NOT NULL DEFAULT '' COMMENT '标的号',
  `money` double(12, 2) NOT NULL DEFAULT '0' COMMENT '受托支付提现金额',
  `tradeNo` varchar(32) NOT NULL DEFAULT '' COMMENT '流水号',
  `addTime` datetime DEFAULT NULL COMMENT '添加时间',
  `validTime` datetime DEFAULT NULL COMMENT '审核时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-未处理;1-处理成功;2-处理失败;',
  `result` varchar(10) NOT NULL DEFAULT '' COMMENT '返回结果',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=18911 DEFAULT CHARSET=utf8 COMMENT='受托支付提现表';


