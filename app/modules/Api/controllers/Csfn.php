<?php
use models\Odd;
use models\User;
use models\UserOffice;
use helpers\NetworkHelper;
use helpers\StringHelper;
use Yaf\Registry;
use tools\WebSign;
use tools\Log;
use plugins\ancun\ACTool;
use factories\RedisFactory;

/**
 * CsfnController
 * 消费金融接口
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class CsfnController extends Controller {

	public function authenticate($params, $expects=array()) {
		if(!WebSign::check($params, $expects)) {
	        $rdata['status'] = 0;
	        $rdata['msg'] = WebSign::getMsg();
	        $this->backJson($rdata);
	    }
	}

	public function addAction() {
		$params = $this->getAllPost();
        
        Log::write('请求', $params, 'csfn', 'INFO');
        
        $expects = [
            'sn'=>'借款单编号',
            'cardnum'=>'身份证号',
            'realname'=>'真实姓名',
            'title'=>'借款标题',
            'use'=>'借款用途',
            'money'=>'借款金额',
            'period'=>'期数',
            'idImages'=>'身份证图片',
            'controlList'=>'风控认证材料',
            'controlContent'=>'风控内容',
            'controlImages'=>'风控图片',
            'operator'=>'操作用户（管理员ID）',
            'isRenew'=>'是否续借',
            'industry'=>'行业',
            'salary'=>'月薪',
            'depart'=>'部门',
            'nature'=>'性质',
            'marriage'=>'婚姻状况',
            'address'=>'户籍地',
            'addressLocal'=>'居住地',
            'educational'=>'学历',
            'companyAddress'=>'公司地址',
            'position'=>'职务'
        ];

		$this->authenticate($params, $expects);
		$isRenew = $params['isRenew'];
		$cardnum = $params['cardnum'];
        $oddNumber = $params['sn'];
        
        $marriage = $params['marriage']=='1'?'y':'n';

		$user = User::where('cardnum', $cardnum)->first();
		$rdata = [];
		if(!$isRenew && $user) {
			$rdata['status'] = 0;
	        $rdata['msg'] = '该身份证已经存在，不允许发布线上借款！';
	        $this->backJson($rdata);
		}
		if($isRenew && !$user) {
			$rdata['status'] = 0;
	        $rdata['msg'] = '该身份证不存在，不允许续借！';
	        $this->backJson($rdata);
		}

        $count = Odd::where('oddNumber', $oddNumber)->count();
        if($count>0) {
            $rdata['status'] = 0;
            $rdata['msg'] = '借款编号已存在！';
            $this->backJson($rdata);
        }
		
		$period =  $params['period'];
		$yearRate = 0;
		if(isset(Odd::$csfnRates[$period])) {
			$yearRate = Odd::$csfnRates[$period];
		} else {
			$rdata['status'] = 0;
	        $rdata['msg'] = '期限错误！';
	        $this->backJson($rdata);
		}

		if(!$isRenew) {
			$birth = StringHelper::getBirthdayByCardnum($cardnum);
			$sex = StringHelper::getSexByCardnum($cardnum);
			$data = [];
            
            $phone = 0;
            $redis = RedisFactory::create();
            if($redis->exists('crd_phone_max')) { 
                $phone = $redis->incr('crd_phone_max');
            } else {
                $phone = 17986800000;
                $redis->set('crd_phone_max', $phone);
            }
            
			$loginpass = StringHelper::generateRandomString(6);

            $data['username'] = $phone;
            $data['loginpass'] = $loginpass;
            $data['phone'] = $phone;

            $data['cardnum'] = $cardnum;
            $data['name'] = $params['realname'];
            $data['cardstatus'] = 'y';
            $data['phonestatus'] = 'y';
            $data['sex'] = $sex;
            $data['birth'] = $birth;

            $data['addressLocal'] = $params['addressLocal'];
            $data['city'] = $params['address'];
            $data['educational'] = $params['educational'];
            $data['maritalstatus'] = $marriage;

            $data['certificationTime'] = date('Y-m-d H:i:s');
            $data['addtime'] = date('Y-m-d H:i:s');

            $data['birth'] = $birth;
            $data['userType'] = 2;
            
            $user = User::addOne($data);
            if(!$user) {
            	$rdata['status'] = 0;
		        $rdata['msg'] = '添加用户失败！';
		        $this->backJson($rdata);
            } else {
            	$acTool = new ACTool($user, 'user');
    			$acTool->send();
            }
		}
        $sexName = $sex=='man'?'先生':'女士';
        $cityNameItem = explode(',', $params['addressLocal']);
        $cityName = isset($cityNameItem[1])?$cityNameItem[1]:'';
        $title = $cityName . _substr($params['realname'], 0, 1) . $sexName . '的借款';

		$data = [];
        $data['userId'] = $user->userId;
        $data['operator'] = $params['operator'];;
        $data['addtime'] = date("Y-m-d H:i:s");
        $data['oddMoney'] = $params['money'];
        // $data['openTime'] = '';
        // $data['oddNumber'] = Odd::generateNumber();
        $data['oddNumber'] = $oddNumber;
        $data['startMoney'] = 50;
        $data['endMoney'] = 0;
        $data['imageUploadStatus'] = 'y';
        $data['oddYearRate'] = $yearRate;
        $data['oddBorrowPeriod'] = $period;
        $data['oddBorrowStyle'] = 'week';
        $data['oddType'] = 'xiaojin';
        $data['oddRepaymentStyle'] = 'matchpay';
        $data['oddTitle'] = $title;
        //$data['oddUse'] = $params['use'];
        $data['oddBorrowValidTime'] = 5;
        $data['progress'] = 'initial';
        $data['oddExteriorPhotos'] = $params['idImages'];
        $data['oddLoanControlList'] = $params['controlList'];
        $data['oddLoanControl'] = $params['controlContent'];
        $data['otherPhotos'] = $params['controlImages'];

        $office = new UserOffice();
        $office->userId = $user->userId;
        $office->officename = $params['company'];
        $office->officecity = $params['companyAddress'];
        $office->industry = $params['industry'];
        $office->salary = $params['salary'];
        $office->depart = $params['depart'];
        $office->nature = $params['nature'];
        $office->position = $params['position'];
        $office->save();

        if(Odd::insert($data)) {
        	$rdata['status'] = 1;
	        $rdata['msg'] = '发布线上借款成功！';
	        
	        $rdata['data']['number'] = $oddNumber;
	        $rdata['data']['paypass'] = $paypass;
	        $rdata['data']['loginpass'] = $loginpass;
	        $rdata['data']['username'] = $phone;

	        $this->backJson($rdata);
       	} else {
       		$rdata['status'] = 0;
	        $rdata['msg'] = '发布线上借款失败！';
	        $this->backJson($rdata);
       	}
	}

    public function thirdStatusAction() {
		$params = $this->getAllPost();
        $expects = [
            'username'=>'用户名'
        ];

		$this->authenticate($params, $expects);
		$user = User::where('username', $params['username'])->first(['userId', 'thirdAccountStatus', 'thirdAccountAuth']);
		$rdata = [];
		if(!$user) {
			$rdata['status'] = 0;
	        $rdata['msg'] = '用户不存在！';
	        $this->backJson($rdata);
		}

        $rdata['status'] = 1;
        $rdata['msg'] = '查询成功！';
        $rdata['data']['accountStatus'] = $user->thirdAccountStatus;
        $rdata['data']['authStatus'] = $user->thirdAccountAuth;
        $this->backJson($rdata);
	}
}

