<?php
use models\Odd;
use models\OddInfo;
use models\User;
use models\UserBid;
use models\Interest;
use Illuminate\Database\Capsule\Manager as DB;
use traits\handles\ITFAuthHandle;

/**
 * OddAction
 * APP标的详情页数据
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class OddAction extends Action {
    use ITFAuthHandle;
    
    public function execute() {
    	$params = $this->getAllQuery();
        $this->authenticate($params, ['oddNumber'=>'标的号']);

        $oddNumber = $params['oddNumber'];
        if(strpos($oddNumber, 'XFJR')===0) {
          $rdata['status'] = 0;
          $rdata['msg'] = 'APP暂不支持个人信贷标显示，请使用网页查看！';
          $this->backJson($rdata);
        }
        $userId = $this->getQuery('userId', null);

        $select = ['oddNumber', 'oddTitle', 'oddType', 'oddStyle', 'oddYearRate', 'oddReward', 'oddMoney', 'addtime', 'oddBorrowPeriod', 
        'oddBorrowStyle', 'oddRepaymentStyle', 'oddTrialTime', 'userId', 'progress', 'openTime'];
        //'oddGarageNum','oddUse',
        
        $oddInfo = OddInfo::where('oddNumber', $oddNumber)->first(['oddExteriorPhotos', 'oddLoanRemark']);
        if(!$oddInfo){
            $oddInfo = new OddInfo();
        }
        $odd = Odd::getBuilder($oddNumber, $userId)->first($select);

        $oddExteriorPhotos = $oddInfo->getImages('oddExteriorPhotos');
        
        $oddUser = User::find($odd->userId);
        $userRow1 = [];
        $userRow1['username'] = _hide_username($oddUser->username);
        $userRow1['marital'] = $oddUser->maritalstatus=='y'?'已婚':'未婚';
        $userRow1['income'] = $oddUser->income;
        $userRow1['age'] = $oddUser->getAge();
        $userRow1['sex'] = $oddUser->sex;

        $borrowMoney = Odd::where('userId', $odd->userId)->whereIn('progress', ['run','end'])->sum('oddMoney');
        $borrowSuccessCount = Odd::where('userId', $odd->userId)->whereIn('progress', ['run','end'])->count();
        $borrowCount = Odd::where('userId', $odd->userId)->whereIn('progress', ['start', 'run','end'])->count();
        $endCount = Odd::where('userId', $odd->userId)->where('progress','end')->count();
        $stayMoney = Interest::getStayMoneyByUser($odd->userId);

        $userRow2 = [];
        $userRow2['borrowMoney'] = $borrowMoney;
        $userRow2['borrowCount'] = $borrowCount;
        $userRow2['successCount'] = $borrowSuccessCount;
        $userRow2['endCount'] = $endCount;
        $userRow2['stayMoney'] = $stayMoney;

        $userRow3 = [];
        $userRow3['borrowMoney'] = $borrowMoney;
        $userRow3['stayMoney'] = $stayMoney;
        $userRow3['borrowOut'] = 0;    

        // $words = str_replace('</p>', '</p>|', _decode($oddInfo->oddLoanRemark));
        // $words = str_replace('</h1>', '</h1>|', $words);
        // $words = str_replace('</h2>', '</h2>|', $words);
        // $words = str_replace('</h3>', '</h3>|', $words);
        // $words = str_replace('</h4>', '</h4>|', $words);
        // $words = str_replace('</h5>', '</h5>|', $words);
        // $words = str_replace('</h6>', '</h6>|', $words);
        // $words = strip_tags($words);
        // $words = trim(str_replace('：', ':', $words), '|');
        // $wordList = explode('|', $words);
        // $oddLoanRemark = [];
        // foreach ($wordList as $word) {
        //   $item = explode(':', $word);
        //   $value = isset($item[1])?$item[1]:'';
        //   $key = str_replace(PHP_EOL, '', $item[0]);
        //   $value = str_replace(PHP_EOL, '', $value);
        //   $key = str_replace('&nbsp;', '', $key);
        //   $value = str_replace('&nbsp;', '', $value);
        //   if($key!='') {
        //     $oddLoanRemark[$key] = $value;
        //   }
        // }

        $ingCount = UserBid::where('oddNumber', $oddNumber)->where('status', '0')->count();

        $remain = $odd->getRemain();

        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['oddNumber'] = $odd->oddNumber;
        $rdata['data']['oddTitle'] = $odd->oddTitle;
        $rdata['data']['oddYearRate'] = $odd->oddYearRate;
        $rdata['data']['oddReward'] = $odd->oddReward;
        $rdata['data']['oddMoney'] = $odd->oddMoney;
        $rdata['data']['oddMoneyLast'] = $odd->getRemain();
        $rdata['data']['startInterest'] = '复审成功当日';
        
        //$rdata['data']['oddGarageNum'] = $odd->oddGarageNum;
        //$rdata['data']['oddUse'] = $odd->oddUse;
        $rdata['data']['oddLoanRemark'] = $oddInfo->oddLoanRemark;
        $rdata['data']['repaySource'] = $oddInfo->repaySource;
        $rdata['data']['overdueTreat'] = $oddInfo->overdueTreat;
        if($oddUser->userType==3){ 
            $rdata['data']['oddUserType'] = 'company';
            $rdata['data']['oddUserName'] = $oddUser->hidename;
            $rdata['data']['oddUserLegal'] = _hide_name($oddUser->userbank->legal);
            $rdata['data']['oddUserUSCI'] = _hide_phone($oddUser->userbank->USCI);
            $rdata['data']['oddUserLegalIdCardNo'] = _hide_cardnum($oddUser->userbank->legalIdCardNo);
            $rdata['data']['oddUserCompanyType'] = $oddUser->companyType;
            $rdata['data']['oddUserCredit'] = $oddUser->credit;
        }else{ 
            $rdata['data']['oddUserType'] = 'person';
            $rdata['data']['oddUserName'] = _hide_name($oddUser->name);
            $rdata['data']['oddUserCardnum'] = _hide_cardnum($oddUser->cardnum);
            $rdata['data']['oddUserSex'] = $oddUser->sex=='man'?'男':'女';
            $rdata['data']['oddUserCity'] = $oddUser->city;
            $rdata['data']['oddUserMaritalstatus'] = $oddUser->maritalstatus=='y'?'已婚':'未婚';
            $rdata['data']['oddUserCredit'] = $oddUser->credit;
        }
        

        $rdata['data']['idPhotos'] = $oddInfo->getImages('idPhotos');
        $rdata['data']['oddPropertyPhotos'] = $oddInfo->getImages('oddPropertyPhotos');
        $rdata['data']['oddExteriorPhotos'] = $oddInfo->getImages('oddExteriorPhotos');
        $rdata['data']['otherPhotos'] = $oddInfo->getImages('otherPhotos');

        $rdata['data']['oddExteriorPhotos'] = $oddExteriorPhotos;
        $rdata['data']['addtime'] = $odd->openTime;
        $rdata['data']['oddPeriod'] = $odd->getPeriod();
        $rdata['data']['oddRepayType'] = $odd->getRepayTypeName();
        $rdata['data']['schedule'] = $odd->getPercent($remain);
        $rdata['data']['user1'] = $userRow1;
        $rdata['data']['user2'] = $userRow2;
        $rdata['data']['user3'] = $userRow3;
        $rdata['data']['openTime'] = $odd->openTime;
        $rdata['data']['progress'] = $odd->progress=='review'?'start':$odd->progress;
        $rdata['data']['second'] = $odd->getOpenSecond();
        $rdata['data']['oddType'] = $odd->oddType;
        $rdata['data']['oddStyle'] = $odd->oddStyle;
        $rdata['data']['ingCount'] = $ingCount;
        $this->backJson($rdata);
    }
}