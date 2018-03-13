<?php

namespace App\Http\Controllers\prize;

use DB;
use App\Http\Controllers\Controller;

class PrizeController extends Controller {
    /*
     * 列出未审核兑奖
     */

    function showAuditPrize() {
        $this->publicCheckLogin();
		$re = DB::table('act_user_prize')->leftjoin('act_prize','act_user_prize.prizeId','=','act_prize.id')->where('status','=',0)
				->orderBy('act_user_prize.addtime','desc');
        $where = "`status` = '0'";
        if (!empty($_GET['startTime']) AND ! empty($_GET['endTime'])) {
            $re = $re->where('act_user_prize.addtime','>=',$_GET['startTime'] . " 00:00:01")->where('act_user_prize.addtime','<=',$_GET['endTime'] . " 23:59:59");
        }
        
        if(!empty($_GET["value"])){
            $re = $re->where($_GET['key'],'LIKE',"%" . $_GET['value'] . "%");
        }
		$fields = $re->select('act_user_prize.id','act_user_prize.userId','act_user_prize.status','act_user_prize.addtime','act_prize.prizeName','act_prize.prizeCash');
        $re = $this->perpage($re, $fields, 10);
        $list = array();
		$re = $this->o2s($re);
		foreach($re as $row){
            //查看用户名
			$userInfo = DB::table('system_userinfo')->select('username','name','phone')->where('userId','=',$row["userId"])->first();
			$userInfo = (array)$userInfo;
            $row["userName"] = isset($userInfo["username"]) ? $userInfo["username"] : "";
            $row["userTrueName"] = isset($userInfo["name"]) ? $userInfo["name"] : "";
            $row["phone"] = isset($userInfo["phone"]) ? $userInfo["phone"] : "";
            $list[] = $row;
        }
        $this->Tmpl['list'] = $list;
        $this->display();
    }

    /*
     * 改变兑换状态
     */

    function showOperationPrize() {
        $this->publicCheckLogin();
        $db = $this->loadDB();
        $prizeId = $_GET["prizeId"];
        $status = $_GET["status"];
        $pageNow = isset($_GET["pageNow"]) ? $_GET["pageNow"] : "1";
        $type = isset($_GET["type"]) ? $_GET["type"] : "audit";
        $url = "/admin.php?module=Prize&action=ListPrize&p=" . $pageNow;
        if (!is_numeric($prizeId) || !in_array($status, array('-1', '0', '1', '2'))) {
            $msg["status"] = "error";
            $msg["msg"] = "传入参数有误1";
            if ($type == "list") {
                //跳到列表页面
                goBack($msg["msg"], $url);
            } else {
                $this->arrayToJson($msg);
            }
        }
        switch ($status) {
            case '-1':
                //查看交易金额
                $sql_str = "SELECT act_prize.prizeCash,act_user_prize.userId FROM act_user_prize LEFT JOIN act_prize ON act_user_prize.prizeId = act_prize.id WHERE act_user_prize.id = " . $prizeId;
                $prizeInfo = $db->getRow($sql_str);
                if (empty($prizeInfo["prizeCash"]) || !is_numeric($prizeInfo["userId"])) {
                    $msg["status"] = "error";
                    $msg["msg"] = "兑换金额不正确，或用户id不正确";
                    if ($type == "list") {
                        //跳到列表页面
                        goBack($msg["msg"], $url);
                    } else {
                        $this->arrayToJson($msg);
                    }
                }
                //获取冻结金额
                $sql_str = "SELECT imiFreezeMoney FROM system_userinfo WHERE userId = " . $prizeInfo["userId"];
                $imiFreezeMoney = $db->getOne($sql_str);
                $prizeCash = $prizeInfo["prizeCash"];
                if ($imiFreezeMoney < $prizeCash) {
                    $msg["status"] = "error";
                    $msg["msg"] = "冻结金额小于兑换金额，请联系管理员";
                    if ($type == "list") {
                        //跳到列表页面
                        goBack($msg["msg"], $url);
                    } else {
                        $this->arrayToJson($msg);
                    }
                }
                //解冻
                //设置状态
                $db->execute("start transaction");
                $db->execute("SET autocommit=0");
                $sql1 = "UPDATE system_userinfo SET imiMoney = imiMoney + {$prizeCash},imiFreezeMoney = imiFreezeMoney - {$prizeCash}" .
                        " WHERE userId = " . $prizeInfo["userId"];
                $sql2 = "UPDATE act_user_prize SET `status` = '-1' WHERE id = " . $prizeId;
                $res1 = $db->execute($sql1);
                $res2 = $db->execute($sql2);
                if ($res1 && $res2) {
                    $db->execute("commit");
                    $db->execute("SET autocommit=1");
                    $msg["status"] = "success";
                    $msg["msg"] = "设置成功";
                } else {
                    $db->execute("rollback");
                    $db->execute("SET autocommit=1");
                    $msg["status"] = "error";
                    $msg["msg"] = "设置失败";
                }
                if ($type == "list") {
                    //跳到列表页面
                    goBack($msg["msg"], $url);
                } else {
                    $this->arrayToJson($msg);
                }
                break;
            case '1':                                 //审核成功
                $sql_str = "UPDATE act_user_prize SET `status` = '1' WHERE id = " . $prizeId;
                if ($db->execute($sql_str)) {
                    $msg["status"] = "success";
                    $msg["msg"] = "设置成功";
                } else {
                    $msg["status"] = "error";
                    $msg["msg"] = "设置失败";
                }
                if ($type == "list") {
                    //跳到列表页面
                    goBack($msg["msg"], $url);
                } else {
                    $this->arrayToJson($msg);
                }
                break;
            case '2':                                //已发货
                $sql_str = "SELECT act_prize.prizeCash,act_user_prize.userId FROM act_user_prize LEFT JOIN act_prize ON act_user_prize.prizeId = act_prize.id WHERE act_user_prize.id = " . $prizeId;
                $prizeInfo = $db->getRow($sql_str);
                if (empty($prizeInfo["prizeCash"]) || !is_numeric($prizeInfo["userId"])) {
                    $msg["status"] = "error";
                    $msg["msg"] = "兑换金额不正确，或用户id不正确";
                    if ($type == "list") {
                        //跳到列表页面
                        goBack($msg["msg"], $url);
                    } else {
                        $this->arrayToJson($msg);
                    }
                }
                //获取冻结金额
                $sql_str = "SELECT imiFreezeMoney FROM system_userinfo WHERE userId = " . $prizeInfo["userId"];
                $imiFreezeMoney = $db->getOne($sql_str);
                $prizeCash = $prizeInfo["prizeCash"];
                if ($imiFreezeMoney < $prizeCash) {
                    $msg["status"] = "error";
                    $msg["msg"] = "冻结金额小于兑换金额，请联系管理员";
                    if ($type == "list") {
                        //跳到列表页面
                        goBack($msg["msg"], $url);
                    } else {
                        $this->arrayToJson($msg);
                    }
                }
                //解冻
                //设置状态
                $db->execute("start transaction");
                $db->execute("SET autocommit=0");
                $sql1 = "UPDATE system_userinfo SET imiFreezeMoney = imiFreezeMoney - {$prizeCash}" .
                        " WHERE userId = " . $prizeInfo["userId"];
                $sql2 = "UPDATE act_user_prize SET `status` = '2' WHERE id = " . $prizeId;
                $res1 = $db->execute($sql1);
                $res2 = $db->execute($sql2);
                if ($res1 && $res2) {
                    $db->execute("commit");
                    $db->execute("SET autocommit=1");
                    $msg["status"] = "success";
                    $msg["msg"] = "设置成功";
                } else {
                    $db->execute("rollback");
                    $db->execute("SET autocommit=1");
                    $msg["status"] = "error";
                    $msg["msg"] = "设置失败";
                }
                if ($type == "list") {
                    //跳到列表页面
                    goBack($msg["msg"], $url);
                } else {
                    $this->arrayToJson($msg);
                }
                break;
            default :
                $msg["status"] = "error";
                $msg["msg"] = "请传入正确状态参数";
                if ($type == "list") {
                    //跳到列表页面
                    goBack("请传入正确状态参数", $url);
                } else {
                    $this->arrayToJson($msg);
                }
                break;
        }
    }

    /*
     * 兑奖列表
     */

    function slist() {
		$re = DB::table('act_user_prize')->leftjoin('act_prize','act_user_prize.prizeId','=','act_prize.id')
                ->leftjoin('system_userinfo as s','s.userId','=','act_user_prize.userId')
				->orderBy('act_user_prize.addtime','desc');
        if (!empty($_GET['startTime']) AND ! empty($_GET['endTime'])) {
            $re = $re->where('act_user_prize.addtime','>=',$_GET['startTime'] . " 00:00:01")->where('act_user_prize.addtime','<=',$_GET['endTime'] . " 23:59:59");
        }
        if(!empty($_GET["value"])){
            $re = $re->where($_GET['key'],'like',"%" . $_GET['value'] . "%");
        }
        $orderStatus = isset($_GET["orderStatus"]) ? $_GET["orderStatus"] : "";
        if(in_array($orderStatus, array("-1","0","1","2","3"))){
            $re = $re->where('act_user_prize.status','=',$orderStatus);
        }
        
        $cash = isset($_GET["cash"]) ? $_GET["cash"] : "";
        switch ($cash){
            case "yes":
                $re = $re->where('act_prize.isMoney','>','0');
                break;
            case "no":
                $re = $re->where('act_prize.isMoney','=','0');
                break; 
        }
        
		$fields = $re->select('act_user_prize.id','act_user_prize.userId','act_user_prize.status','act_user_prize.addtime','act_prize.prizeName','act_prize.prizeCash','act_prize.isMoney','s.username','s.name','s.phone')->paginate(15);
        $p = isset($_GET["p"]) ? $_GET["p"] : 1;
        $re = $this->pagin($fields);
        $list = array();
		foreach($fields as $row){
            //查看用户名
            $row = (array)$row;
            $row["userName"] = isset($row["username"]) ? $row["username"] : "";
            $row["userTrueName"] = isset($row["name"]) ? $row["name"] : "";
            $row["phone"] = isset($row["phone"]) ? $row["phone"] : "";
            $list[] = $row;
        }
        return  view('Prize.list')->with('list',$list)->with('show_pages',$re)->with('p',$p);
    }

    /*
     * 收货地址
     */
    function address(){
		$re = DB::table('act_user_address AS address')->leftjoin('system_userinfo AS info','address.userId','=','info.userId')
				->orderBy('address.addtime','desc');

        if (!empty($_GET['startTime']) AND ! empty($_GET['endTime'])) {
            $re = $re->where('act_user_prize.addtime','>=',$_GET['startTime'] . " 00:00:01")->where('act_user_prize.addtime','<=',$_GET['endTime'] . " 23:59:59");
        }
        if(!empty($_GET["value"])){
            $re = $re->where($_GET['key'],"LIKE","%" . $_GET['value'] . "%");
        }
		$fields = $re->select('address.*','info.username','info.name AS trueName')->paginate(15);
        $re = $this->pagin($fields);
        return view('Prize.address')->with('list',$fields)->with('show_pages',$re);
    }
    
    /*
     * 获取用户的收货地址
     */
    function getUserAddress(){
        $userId = $_GET["userId"];
        if(!is_numeric($userId)){
            $msg['status'] = 'error';
            $msg["msg"] = "用户id有误";
            $this->arrayToJson($msg);
        }
        $userAddress = DB::table('act_user_address')->where('userId',$userId)->first();
        if(empty($userAddress)){
            $msg['status'] = 'error';
            $msg["msg"] = "该用户还没设置地址";
            $this->arrayToJson($msg);
        }
        $msg["status"] = "success";
        $msg["data"] = $userAddress;
        $this->arrayToJson($msg);  
    }
}

?>