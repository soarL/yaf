<?php
use Yaf\Registry;
use helpers\StringHelper;
use helpers\NetworkHelper;
use factories\RedisFactory;

class RedisController extends Controller {
    public function indexAction() {
        $redis = RedisFactory::create();
        $list = [
            'aa'=>'讯息列表页', 
            'ab'=>'问答内容页面', 
            'ac'=>'用户提问', 
            'ad'=>'用户撤销债权转让', 
            'ae'=>'用户设置自动投标信息', 
            'af'=>'用户债权转让信息页面', 
            'ag'=>'用户银行卡信息页面',
            'ah'=>'充值记录',
            'ai'=>'提现记录',
            'aj'=>'绑定第三方',
            'ak'=>'授权（取消授权）',
            'al'=>'标的列表',
            'am'=>'自动投标信息页面',
            'an'=>'用户投资记录',
            'ao'=>'用户债权转让记录',
            'ap'=>'回款日历页面',
            'aq'=>'用户VIP页面',
            'ar'=>'用户债权转让',
            'as'=>'用户资金账户页面',
            'at'=>'用户信息页面',
        ];
        
        $redis->hIncrBy('app_pv', 'ab', 1);
        die();
        $redis->set('foo', '廖金灵');
        $value = $redis->get('foo');
        var_dump($value);

        $redis->del('foo');

        $value = $redis->get('foo');

        var_dump($value);

        $data = ['var1'=>'hhhh', 'var2'=>233, 'var3'=>'sss'];
        $redis->mset($data);

        var_dump($redis->keys('*1*'));
        var_dump($redis->keys('*'));

        echo $redis->randomkey();

        $redis->flushdb();

        var_dump($redis-> randomkey());

        $redis->set('name', '廖金灵');
        echo $redis->ttl('name');
    }

    public function listAction() {

    }
}
