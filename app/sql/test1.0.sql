create table student(
	id int(4) not null primary key auto_increment,
	name varchar(50) not null default '' comment '姓名',
	teacher_id int(4) not null default 0 comment '老师ID',
	age tinyint(1) not null default 0 comment '年龄',
	sex tinyint(1) not null default 0 comment '性别'
) default charset=utf8 comment '学生表';

create table teacher(
	id int(4) not null primary key auto_increment,
	name varchar(50) not null default '' comment '姓名',
	course_id int(4) not null default 0 comment '老师ID',
	age tinyint(1) not null default 0 comment '年龄',
	sex tinyint(1) not null default 0 comment '性别'
) default charset=utf8 comment '老师表';

create table course(
	id int(4) not null primary key auto_increment,
	name varchar(50) not null default '' comment '课程名',
	period float(2,1) not null default 0 comment '课时'
) default charset=utf8 comment '课程表';
