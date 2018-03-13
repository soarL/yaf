<?php
use helpers\StringHelper;
use Yaf\Registry;

use models\News;
use models\User;

use models\Borrow;
use models\OddMoney;
use models\Banner;
use models\Odd;
use models\Interest;
use models\OddClaims;
use models\Invest;
use models\Question;
use models\Crtr;
use models\Attribute;
use models\Ranking;
use models\RankingWeek;
use models\RankingMonth;
use models\RankingDay;
use models\ExpectOdd;
use models\Link;
use models\Filiale;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * IndexController
 * 首页
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class IndexController extends Controller {
    public $menu = 'index';

    /**
     * 首页
     * @return mixed
     */
    public function indexAction() {
        $pdList = News::getList('preordelay', 7);
        $news = News::getList('news', 4);
        $eduList = News::getList('edu', 7);
        $notices = News::getList('notice', 7);
        
        $registerCount = User::count();
        $registerCount += Attribute::getByIdentity('addUserNum');
        
        // $builder = Crtr::getListBuilder();
        // $builder = Crtr::sortList($builder);
        // $crtrs = $builder->limit(8)->get();
        
        $bannerList = Banner::getIndexBanners();

        //$ad = Banner::getIndexAd();

        $user = $this->getUser();
        $userId = $user?$user->userId:null;

        $stayMoney = round(Interest::getStayMoney() + 0);//15316741.90,2
        $yestodayVolume = round(Odd::getYestodayVolume(),2);
        
        $totalVolume = round(Odd::getTotalVolume(),2);//41887930.00
        //$safeDay = round((time()-strtotime('2015-01-09 00:00:00'))/(24*60*60),2);

        $columns = [
            'oddNumber', 
            'oddTitle', 
            'oddReward', 
            'investType', 
            'progress', 
            'addtime', 
            'oddMoney', 
            'oddYearRate',
            'oddType',
            'oddStyle',
            'oddBorrowPeriod',
            'oddBorrowStyle',
            'openTime',
            'lookstatus',
            'userId',
        ];

        /** 新手标 **/
        $builder = Odd::getListBuilder($userId)->where('oddStyle', 'newhand');
        $builder = Odd::sortList($builder);
        $newHandOdds = $builder->limit(2)->get($columns);

        /** 热门推荐 **/
        // $builder = Odd::getListBuilder($userId)->where('oddStyle', 'normal');
        // $builder = Odd::sortList($builder);
        // $hotOdds = $builder->limit(2)->get($columns);

        /** 房抵贷 **/
        $builder = Odd::getListBuilder($userId)->where('oddType', 'house-mor')->where('oddStyle', 'normal');
        $builder = Odd::sortList($builder);
        $houseOdds = $builder->limit(5)->get($columns);
        $houseCount = Odd::getCanBidNum($userId, ['oddType'=>'house-mor', 'oddStyle'=>'normal']);

        /** 车险贷 **/
        $builder = Odd::getListBuilder($userId)->where('oddType', 'auto-ins')->where('oddStyle', 'normal');
        $builder = Odd::sortList($builder);
        $insOdds = $builder->limit(5)->get($columns);
        $insCount = Odd::getCanBidNum($userId, ['oddType'=>'auto-ins', 'oddStyle'=>'normal']);

        /** 债权转让 **/
        $builder = Crtr::getListBuilder();
        $builder = Crtr::sortList($builder);
        $crtrs = $builder->limit(5)->get();

        $crtrCount = Crtr::getCanBuyNum();
        
        $links = Link::where('link_status', 1)->where('link_type', 'link')->orderBy('link_sort', 'desc')->get();

        // $rankingList = Ranking::limit(5)->get();
        // $rankingDayList = RankingDay::limit(5)->get();
        // $rankingWeekList = RankingWeek::limit(5)->get();
        // $rankingMonthList = RankingMonth::limit(5)->get();

        //$lastEODay = ExpectOdd::orderBy('day', 'desc')->limit(1)->value('day');
        
        // $builder = ExpectOdd::where('day', $lastEODay);
        // if($lastEODay==date('Y-m-d')) {
        //     $bigerEOtime = (date('G') * 60 + intval(date('i')))/60.00;
        //     $builder = $builder->whereRaw('(time>=? or time>24)', [$bigerEOtime]);
        // }
        // $expectOdds = $builder->orderBy('time', 'asc')->limit(6)->get();

        $allInterest = Invest::whereIn('status', [1, 3, 4])->sum('interest');
        $allInterest = round($allInterest + 847986.60, 2);

        //$filiales = Filiale::where('status', 1)->whereRaw('type in (?, ?)', ['contact', 'both'])->get();

        //$oldData = Attribute::getByIdentity('old_data');
        //$oldData = json_decode($oldData, true);

        $row = Odd::where('openTime', 'like', date('Y-m-d').'%')->whereIn('progress', ['start', 'review', 'run', 'end'])->first([DB::raw('sum(oddMoney) dayOpenMoney'), DB::raw('count(1) dayOpenCount')]);
        $dayOpenMoney = floatval($row->dayOpenMoney/10000);
        $dayOpenCount = $row->dayOpenCount;

        $expect = News::with(['expectOdd'=>function($q){$q->groupBy('type')->select(DB::raw('sum(money) tmoney'),'type','day','news_id');}])->where(['news_type'=>'announce'])->orderBy('news_time','desc')->first();

        $this->display('index',[
            'news'=>$news, 
            'pdList'=>$pdList, 
            'eduList'=>$eduList, 
            'bannerList'=>$bannerList, 
            'notices'=>$notices, 
            // 'rankingList'=>$rankingList,
            // 'rankingDayList'=>$rankingDayList,
            // 'rankingWeekList'=>$rankingWeekList,
            // 'rankingMonthList'=>$rankingMonthList,
            'stayMoney'=>$stayMoney,
            'totalVolume'=>$totalVolume,
            'yestodayVolume'=>$yestodayVolume,
            'crtrs'=>$crtrs,
            //'ad'=>$ad,
            'registerCount'=>$registerCount,
            //'safeDay'=>$safeDay,
            'expect'=>$expect,

            'newHandOdds'=>$newHandOdds,
            //'hotOdds'=>$hotOdds,
            'insOdds'=>$insOdds,
            'houseOdds'=>$houseOdds,
            
            'houseCount'=>$houseCount,
            'insCount'=>$insCount,
            'crtrCount'=>$crtrCount,

            'links'=>$links,
            'allInterest'=>$allInterest,

            //'filiales'=>$filiales,
            'dayOpenMoney'=>$dayOpenMoney, 
            'dayOpenCount'=>$dayOpenCount, 
        ]);
    }
}
