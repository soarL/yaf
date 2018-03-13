<?php
/**
 * WebError
 * 服务器错误信息
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
namespace tools;

class WebError {

	/*未登录*/
	const UNLOGIN = -99;

	/*权限不足*/
    const NOPERM = -98;

    /*访问方式错误*/
    const ERRORACCESS = -97;

    /*服务器维护*/
    const MAINT = -96;

}