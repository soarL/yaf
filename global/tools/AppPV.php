<?php
namespace tools;
use factories\RedisFactory;

class AppPV {
	const PV_KEY = 'app_pv';

	public static $list = [
        'aa'=>'讯息列表页', // InfosAction
        'ab'=>'问答内容页面', // AnswersAction
        'ac'=>'用户提问', // askAction
        'ad'=>'用户撤销债权转让', // delTransferAction 
        'ae'=>'用户设置自动投标', // autoSetAction
        'af'=>'用户信息页面', // getUserInfoAction
        'ag'=>'用户银行卡信息页面', // getBankCardsAction
        'ah'=>'充值记录', // RechargeRecordsAction
        'ai'=>'提现记录', // WithdrawRecordsAction
        'aj'=>'绑定第三方', // bindThirdAction
        'ak'=>'授权（取消授权）', // thirdAuthAction
        'al'=>'标的列表', // OddsAction
        'am'=>'自动投标信息页面', // autoInfoAction
        'an'=>'用户投资记录', // UserTendersAction
        'ao'=>'用户债权转让记录', // UserCrtrsAction
        'ap'=>'回款日历页面', // RepaymentsAction
        'aq'=>'用户VIP页面', // UserVipAction
        'ar'=>'用户债权转让', // transferAction
        'as'=>'用户资金账户页面', // UserAccountAction
        'at'=>'债权转让列表', // CrtrsAction
    ];

	public static function add($key) {
		$redis = RedisFactory::create();
		$redis->hIncrBy(self::PV_KEY, $key, 1);
	}

	public static function del() {
		$redis = RedisFactory::create();
		$redis->delete(self::PV_KEY);
	}

	public static function get($key=false) {
		$redis = RedisFactory::create();
		if($key) {
			return $redis->hGet(self::PV_KEY, $key);
		}
		return $redis->hGetAll(self::PV_KEY);
	}
}