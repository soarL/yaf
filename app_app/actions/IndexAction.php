<?php
use tools\WebSign;
use models\Banner;
use models\News;
use models\Odd;
use models\Invest;
use models\Attribute;
use traits\handles\ITFAuthHandle;

/**
 * IndexAction
 * APP首页数据
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class IndexAction extends Action {
    use ITFAuthHandle;

    public function execute() {
    	$params = $this->getAllQuery();
    	// $this->authenticate($params);
        $userId = $this->getQuery('userId', null);

        $rdata = [];

        $newsColumns = ['id','news_title'];
        $notices = News::where('news_type', 'notice')->orderBy('news_order', 'asc')->orderBy('news_time', 'desc')->limit(4)->get($newsColumns);
        $announce = News::where('news_type', 'announce')->orderBy('news_order', 'asc')->orderBy('news_time', 'desc')->first($newsColumns);
        if($announce)$notices[] = $announce;
        
    	$banners = Banner::where('type_id', 5)->where('status', '1')->orderBy('banner_order', 'desc')->get(['id','title','link','banner']);

        $select = ['oddNumber', 'oddTitle', 'oddYearRate', 'oddReward', 'oddMoney', 'addtime', 'oddBorrowPeriod',
            'oddBorrowStyle', 'progress', 'openTime', 'oddStyle', 'investType', 'oddRepaymentStyle'];

        $builder = Odd::getListBuilder($userId)->where('oddStyle', 'newhand');

        // 暂时不显示个人信贷标
        $builder->where('oddType', '<>', 'xiaojin');

        $builder = Odd::sortList($builder);
        $newHandOdds = $builder->limit(3)->get($select);

        $newHandOddList = [];
        foreach ($newHandOdds as $newHandOdd) {
            $row = [];
            $row['oddNumber'] = $newHandOdd->oddNumber;
            $row['oddTitle'] = $newHandOdd->oddTitle;
            $row['oddMoney'] = $newHandOdd->oddMoney;
            $row['oddYearRate'] = $newHandOdd->oddYearRate + $newHandOdd->oddReward;
            // $row['oddReward'] = $newHandOdd->oddReward;
            $row['oddPeriod'] = $newHandOdd->getPeriod();
            $row['schedule'] = $newHandOdd->getPercent();
            $row['addtime'] = $newHandOdd->addtime;
            $row['progress'] = $newHandOdd->progress=='review'?'start':$newHandOdd->progress;
            $row['second'] = $newHandOdd->getOpenSecond();
            $row['openTime'] = $newHandOdd->openTime;
            $row['oddStyle'] = $newHandOdd->oddStyle;
            $row['investType'] = $newHandOdd->investType;

            $row['period'] = $newHandOdd->oddBorrowPeriod;
            $row['periodType'] = $newHandOdd->oddBorrowStyle;
            $row['repayType'] = $newHandOdd->oddRepaymentStyle;
            $newHandOddList[] = $row;
        }

        $builder = Odd::getListBuilder();

        // 暂时不显示个人信贷标
        $builder->where('oddType', '<>', 'xiaojin');

        $oddList = Odd::sortList($builder)->limit(5)->get($select);
        $odds = [];
        foreach ($oddList as $odd) {
            $row = [];
            $row['oddNumber'] = $odd->oddNumber;
            $row['oddTitle'] = $odd->oddTitle;
            $row['oddMoney'] = $odd->oddMoney;
            $row['oddYearRate'] = $odd->oddYearRate;
            $row['oddReward'] = $odd->oddReward;
            $row['oddPeriod'] = $odd->getPeriod();
            $row['schedule'] = $odd->getPercent();
            $row['addtime'] = $odd->addtime;
            $row['progress'] = $odd->progress=='review'?'start':$odd->progress;
            $row['second'] = $odd->getOpenSecond();
            $row['openTime'] = $odd->openTime;
            $row['oddStyle'] = $odd->oddStyle;
            $row['investType'] = $odd->investType;

            $row['period'] = $odd->oddBorrowPeriod;
            $row['periodType'] = $odd->oddBorrowStyle;
            $row['repayType'] = $odd->oddRepaymentStyle;
            $odds[] = $row;
        }

        $allVolume = intval(Odd::getTotalVolume());
        $allInterest = Invest::whereIn('status', [1, 3, 4])->sum('interest');
        $allInterest = intval($allInterest);
        $todayLast = Odd::where('progress', 'start')->sum('oddMoney');

        $oldData = Attribute::getByIdentity('old_data');
        $oldData = json_decode($oldData, true);

        $rdata['status'] = 1;
        $rdata['msg'] = '获取成功！';
        $rdata['data']['odds'] = $odds;
        $rdata['data']['notices'] = $notices;
        $rdata['data']['banners'] = $banners;
        $rdata['data']['newHandOdds'] = $newHandOddList;
        $rdata['data']['allVolume'] = $oldData['totalVolume'] + $allVolume;
        $rdata['data']['allInterest'] = $oldData['allInterest'] + $allInterest;
        $rdata['data']['todayLast'] = $todayLast;
        $this->backJson($rdata);
    }
}