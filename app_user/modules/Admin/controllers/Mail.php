<?php

namespace App\Http\Controllers\mail;

use DB;
use App\Http\Controllers\Controller;

class MailController extends Controller {

    var $key = 0;

    function slist() {
		$re = DB::table('system_webmail as t1')->orderBy('t1.id','desc');
        if (!empty($_GET['startTime'])) {
            $re = $re->where('t1.addTime','>=',$_GET['startTime'] . " 00:00:01");
        }
        if (!empty($_GET['endTime'])) {
            $re = $re->where('t1.addTime','<=',$_GET['endTime'] . " 23:59:59");
        }
        if (!empty($_GET['status'])) {
            $re = $re->where('t1.status','=',$_GET['status']);
        }
        if (!empty($_GET['sendUser'])) {
            $re = $re->where('t1.sendUser','=',$_GET['sendUser']);
        }
        if (!empty($_GET['title'])) {
            $re = $re->where('t1.title','=',$_GET['title']);
        }
        if (!empty($_GET['sendType'])) {
            $re = $re->where('t1.sendType','=',$_GET['sendType']);
        }
		$fields = $re->select('t1.id','t1.title','t1.status','t1.addTime','t1.addIp','t1.sendUser','t1.receiveUser','t1.sendType','t1.sendUserType')->paginate(15);
        $re = $this->pagin($fields);
        return view('mail.list')->with('list',$fields)->with('show_pages',$re);
    }

    function editWebmail() {
        if (is_numeric($_GET['id']) AND ! empty($_GET['id'])) {
            $id = $_GET['id'];
            $row = DB::table('system_webmail')->where('id',$id)->first();
            return view('mail.edit')->with('webmail',$row);
        } else {
            return back()->with('mes','参数错误');
        }
    }

    function doeditWebmail() {
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        $sendType = isset($_POST['sendType']) ? intval($_POST['sendType']) : 0;
        $receiveUser = isset($_POST['receiveUser']) ? trim($_POST['receiveUser']) : '';

        if ($title == '') {
            return back()->with('mes','标题不能为空');
        }
        if ($content == '') {
            return back()->with('mes','内容不能为空');
        }

        if ($sendType == 0) {
            if ($receiveUser == '') {
                return back()->with('mes','接收用户不能为空');
            }
        } else {
            $receiveUser = '';
        }
        $data = array();
        $data['title'] = $title;
        $data['content'] = $content;
        $data['receiveUser'] = $receiveUser;
        $data['sendUserType'] = 0;
        $data['status'] = 1;
        $data['sendType'] = $sendType;
        if (is_numeric($_POST['id']) AND ! empty($_POST['id'])) {
            $re = DB::table('system_webmail')->where('id',$_POST['id'])->update($data);
            if ($re)
                return back()->with('mes','修改成功');
            else
                return back()->with('mes','修改失败');
        }else {
            return back()->with('mes','参数错误');
        }
    }

    function add() {
        return view('mail.add');
    }

    function doadd() {
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        $sendType = isset($_POST['sendType']) ? intval($_POST['sendType']) : 0;
        $receiveUser = isset($_POST['receiveUser']) ? trim($_POST['receiveUser']) : '';

        if ($title == '') {
            return back()->with('mes','标题不能为空');
        }
        if ($content == '') {
            return back()->with('mes','内容不能为空');
        }

        if ($sendType == 0) {
            if ($receiveUser == '') {
                return back()->with('mes','接收用户不能为空');
            }
        } else {
            $receiveUser = '';
        }
        $data = array();
        $data['title'] = $title;
        $data['content'] = $content;
        $data['receiveUser'] = $receiveUser;
        $data['sendUser'] = $_SESSION['admin']['user'];
        $data['sendUserType'] = 0;
        $data['status'] = 1;
        $ipArray = getClientIp();
        $data['addIp'] = $ipArray['REMOTE_ADDR'][0];
        $data['sendType'] = $sendType;
        $data['addTime'] = date('Y-m-d H:i:s', time());

        $re = DB::table('system_webmail')->insert($data);
        if ($re)
            return back()->with('mes','添加成功');
        else
            return back()->with('mes','添加失败');
    }

    function deleteWebmail() {
        if (is_numeric($_GET['id'])) {
            $db = DB::table('system_webmail')->where('id',$_GET['id'])->delete();
                DB::table('system_user_webmail')->where('webmailId',$_GET['id'])->delete();
            if ($db) {
                $ajax['status'] = 'success';
                $ajax['msg'] = '删除成功';
                $ajax['data'] = '';
            } else {
                $ajax['status'] = 'error';
                $ajax['msg'] = '删除失败';
                $ajax['data'] = '';
            }
        } else {
            $ajax['status'] = 'error';
            $ajax['msg'] = '参数错误';
            $ajax['data'] = '';
        }
          $this->arrayToJson($ajax);
    }

}

?>