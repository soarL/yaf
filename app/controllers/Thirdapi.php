
<?php

use models\Odd;
use models\OddMoney;
use models\User;
use Yaf\Registry;

class ThirdapiController extends Controller
{

    public function getLoanDetialAction()
    {
        set_time_limit(0);
        $params = $this->getAllQuery();
        $oddNumber = $params['projectId'];
        $token = $params['token'];
        if(empty($oddNumber) || empty($token)){
            die("请求数据有误");
        }
        if(!is_numeric($oddNumber)){
            die("projectId必须为数值");
        }
        $tokenNew = md5($oddNumber.'xwsd');
        if($token != $tokenNew){
            die("token验证失败");
        }
        $loanInfo = Odd::where('oddNumber',$oddNumber)->first(['oddNumber','oddTitle','userId','oddMoney','successMoney','oddYearRate','oddBorrowPeriod','oddBorrowStyle','oddType','oddRepaymentStyle'])->toArray();
        if($loanInfo){
            $reData["projectId"] = $loanInfo["oddNumber"];
            $reData["title"] = $loanInfo["oddTitle"];
            $reData["loanUrl"] = "http://www.hcjrfw.com/odd/".$loanInfo["oddNumber"];
            $reData["userName"] = md5($loanInfo["userId"]);
            $reData["amount"] = intval($loanInfo['successMoney']);
            $pro = intval($loanInfo['successMoney'])/intval($loanInfo["oddMoney"]);
            $reData["schedule"] = ceil(intval($pro)*100);
            $reData["interestRate"] = floatval($loanInfo["oddYearRate"])*100;
            $reData["deadline"] = $loanInfo["oddBorrowPeriod"];
            $reData["deadlineUnit"] = $loanInfo["oddBorrowStyle"];
            if($loanInfo["oddBorrowPeriod"] == '3' && $loanInfo["oddBorrowPeriod"] == 'month'){
                $reData["reward"] = 0.4;
            }else{
                $reData["reward"] = 0;
            }
            $reData["type"] = $loanInfo["oddType"];
            $reData["repaymentType"] = $loanInfo["oddRepaymentStyle"];
            $reData["warrantcom"] = "--";
        }else{
            die('没有查到记录!');
        }
        //投标列表
        $investList = OddMoney::where('type','invest')->where('oddNumber',$oddNumber)->get(['money','userId','remark','time'])->toArray();
        $subscribes = array();
        foreach($investList as $val){
            $investInfo["subscribeUserName"] = md5($val["userId"]);
            $investInfo["amount"] = $val["money"];
            $investInfo["validAmount"] = $val["money"];
            $investInfo["addDate"] = $val["time"];
            $investInfo["status"] = 1;
            if(strpos($val['remark'], 'AUTOMATIC')!==false) {
                $investInfo["type"] = 1;
            } else {
                $investInfo["type"] = 0;
            }
            $subscribes[] = $investInfo;
        }
        $reData["subscribes"] = $subscribes;
        echo json_encode($reData,JSON_UNESCAPED_SLASHES);
    }

    public function getLoanListAction()
    {
        set_time_limit(0);
        $params = $this->getAllQuery();
        $page = $params['page'];
        $pageSize = $params['pageSize'];
        $token = $params['token'];
        if(empty($page) || empty($pageSize) || empty($token)){
            die("请求数据有误");
        }
        if(!is_numeric($page) || !is_numeric($pageSize)){
            die("请求page和pageSize必须为数值");
        }
        $tokenNew = md5($page.$pageSize.'xwsd');
        if($token != $tokenNew){
            die("token验证失败");
        }
        $total = Odd::whereIn('progress',['run','start','end'])->count();
        //总页码
        $reData["totalPage"] = ceil(intval($total)/20);
        //当前页码
        $reData["currentPage"] = intval($page);
        //总标数
        $reData["totalCount"] = intval($total);
        $start = (intval($page)-1)*intval($pageSize);
        $oddNumberArr = Odd::whereIn('progress',['run','start','end'])->orderBy('addtime','ASC')->skip($start)->limit($pageSize)->get()->toArray();
        foreach($oddNumberArr as $val){
            $borrowList[] = "http://www.hcjrfw.com/odd/".$val["oddNumber"];
        }
        if(isset($borrowList)){
            $reData["borrowList"] = $borrowList;
        }else{
            $reData["borrowList"] = '';
        }
        echo json_encode($reData,JSON_UNESCAPED_SLASHES);
    }

    public function getTmpListAction()
    {
        set_time_limit(0);
        $params = $this->getAllPost();
        $page = 5 * $params['page'];
        $reData = Odd::whereIn('progress',['start','run','end'])->where('addtime','like','2016-09-17%')->limit(0,$page)->get()->toArray();
        $data['status'] = 'success';
        $data['data'] = $reData;
        $data['msg'] = '获取成功';
        exit(json_encode($data,JSON_UNESCAPED_UNICODE));
    }


    public function getTmpUser()
    {
        set_time_limit(0);
        $params = $this->getAllPost();
        if(empty($params['username']) || empty($params['pass'])){
            $data['status'] = 'error';
            $data['data'] = '';
            $data['msg'] = '参数错误';
            exit(json_encode($data,JSON_UNESCAPED_UNICODE));
        }
        $reData = User::where('username',$params['username'])->get()->toArray();
        if(md5($params['pass'].$reData['friendkey']) == $reData['loginpass']){
            $data['status'] = 'success';
            $data['data'] = $reData;
            $data['msg'] = '获取成功';
            exit(json_encode($data,JSON_UNESCAPED_UNICODE));
        }else{
            $data['status'] = 'error';
            $data['data'] = '';
            $data['msg'] = '密码错误';
            exit(json_encode($data,JSON_UNESCAPED_UNICODE));
        }
    }

}