<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use traits\BatchInsert;

/**
 * MoneyLog|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class MoneyLog extends Model {
    use BatchInsert;
    
	protected $table = 'user_moneylog';

	public $timestamps = false;

    public static $words = [
        '{SYSTEM}' => '系统',
        '{LOAN}' => '借款',
        '{ODD}' => '单号',
        '{INVEST}' => '投资',
        '{SUCCESS}' => '成功',
        '{FAIL}' => '失败',
        '{MONEY}' => '金额',
        '{AUTOMATIC}' => '自动投标',
        '{SHOUDONG}' => '手动投标',
        '{OPERATE}' => '操作',
        '{FREEZE}' => '冻结',
        '{UNFREEZE}' => '解冻',
        '{USER}' => '用户',
        '{DANGER}' => '非法',
        '{NULLDATA}' => '空数据',
        '{DATA}' => '数据',
        '{WRITELOG}' => '写日志',
        '{REPEAT}' => '重复',
        '{CLAIMS}' => '债权',
        '{TRANSFER}' => '转让',
        '{WORK}' => '工作流',
        '{NO}' => '未',
        '{HUANQI}' => '进入还款期',
        '{BUY}' => '购买',
        '{ADD}' => '添加',
        '{LESS}' => '扣除',
        '{LOG}' => '日志',
        '{BUGUO}' => '不够',
        '{YIBEI}' => '已被',
        '{PARAMETER}' => '参数有误',
        '{RELOAD}' => '更新状态',
        '{REHEAR}' => '复审',
        '{LOCKED}' => '锁死',
        '{GET}' => '获取',
        '{PRINCIPAL}' => '本金',
        '{SERVICE}' => '服务',
        '{APREAD}' => '推广',
        '{END}' => '结束',
        '{MORTGAGE}' => '抵押额',
        '{CREDIT}' => '信用额',
        '{GUARANTEE}' => '担保额',
        '{QISHU}' => '期数',
        '{HUANKUANLIST}' => '还款列表',
        '{LOANUSER}' => '借款者',
        '{HUANKUAN}' => '还款',
        '{INTEREST}' => '利息',
        '{SHOUYI}' => '收益表',
        '{COMPANY}' => '商家',
        '{TRIAL}' => '初审',
        '{DENY}' => '拒绝',
        '{JIEKUAN}' => '借款',
        '{HOUTAI}' => '后台',
        '{CHONGZHI}' => '充值',
        '{BALANCE}' => '余额',
        '{SHENGYU}' => '剩余',
    ];

    public static $types = [
        'nor-curprofit' => '活期收益',
        'nor-tfrecharge' => '转账充值',
        'nor-withdraw' => '提现',
        'nor-recharge' => '充值',

        'nor-tender' => '投资',
        'nor-loan' => '借款',
        'nor-loandel' => '融资扣款',
        'nor-crtr' => '购买债权',
        'nor-transfer' => '转让债权',
        'nor-recvpayment' => '回款',
        'nor-repayment' => '还款',
        'nor-bailrepay' => '还垫付款',
        'nor-sync' => '资金同步',
        'nor-cancel-tender' => '投资撤销',
        'nor-degwithdraw' => '受托支付提现',
        'nor-degincome' => '受托支付入账',
        'nor-delayfee' => '逾期罚息',

        'fee-recharge' => '充值手续费',
        'fee-interest' => '利息服务费',
        'fee-crtr' => '债转服务费',
        'fee-withdraw' => '提现手续费',
        'fee-loan' => '借款手续费',
        'rpk-spread' => '推荐奖励',
        'rpk-newuser' => '新手红包',
        'rpk-normal' => '红包',
        'rpk-tran' => '资金迁移',
        'rpk-cancel' => '红包撤销',
        'rpk-interest' => '加息券加息',
        'rpk-reward' => '项目加息',
        'rpk-investmoney' => '抵扣红包',
    ];

    public function translate() {
        if(strtotime($this->time)<strtotime('2016-10-27 14:19:00')) {
            return $this->oldTranslate();
        }
        $linkMarks = [
            '@oddNumber' => WEB_MAIN . '/odd/',
            '@crtrNumber' => WEB_MAIN . '/crtr/view/num/',
        ];

        $remark = $this->remark;
        foreach ($linkMarks as $mark => $link) {
            $num = preg_match_all('/(?<='.$mark.'{)\d+(?=})/', $remark, $matches);
            if($num>0) {
                foreach ($matches[0] as $value) {
                    $search = $mark.'{'.$value.'}';
                    $replace = '<a target="_blank" href="'.$link.$value.'">['.$value.']</a>';
                    $remark = str_replace($search, $replace, $remark);
                }
            }
        }
        return $remark;
    }

    public function oldTranslate() {
        $remark = '';
        if($this->type=='interestService') {
            if($this->mode=='out') {
                if(strrpos($this->remark, '{LESS}{SERVICE}{MONEY}')!==false) {
                    $remark = '扣除借款服务费'.$this->mvalue.'元';
                } else if(strrpos($this->remark, '{SYSTEM}:{ODD}')!==false) {
                    $remark = '扣除利息服务费'.$this->mvalue.'元';
                }
            } else {
                if(strrpos($this->remark, '{LESS}{SERVICE}{MONEY}')!==false) {
                    $remark = '收取用户借款服务费'.$this->mvalue.'元';
                } else if(strrpos($this->remark, '{SYSTEM}:{ODD}')!==false) {
                    $pos1 = strpos($this->remark, '{GET}') + 5;
                    $pos2 = strpos($this->remark, '{INTEREST}');
                    $userId = substr($this->remark, $pos1, $pos2-$pos1);
                    $remark = '收取用户['.$userId.']利息服务费'.$this->mvalue.'元';
                }
            }
        } else if($this->type=='interest') {
            if($this->mode=='in') {
                $remark = '获得标的[<a href="' .WEB_MAIN. \Url::to('/odd/view', ['num'=>$this->oddNumber]) . '" target="_blank">'
                    . $this->odd->oddTitle . '</a>]还款'.$this->mvalue.'元（利息部分）';
            } else {
                $remark = '支出用户投资利息'.$this->mvalue.'元';
            }
        } else if($this->type=='recharge') {
            $remark = '在线充值'.$this->mvalue.'元';
        } else if($this->type=='withdraw') {
            $remark = '提取现金'.$this->mvalue.'元';
        } else if($this->type=='odd') {
            if($this->mode=='out') {
                $remark = '购买债权支出'.$this->mvalue.'元';
            } else {
                $remark = '出售债权收入'.$this->mvalue.'元';
            }
        } else if($this->type=='addmoney') {
            if($this->mode=='out') {
                if(strpos($this->remark, '红包')===false) {
                    $remark = '后台扣除金额'.$this->mvalue.'元';
                } else {
                    $remark = '送出'.$this->mvalue.'元红包';
                }
            } else {
                if(strpos($this->remark, '红包')===false) {
                    $remark = '后台划入金额'.$this->mvalue.'元红包';
                } else {
                    $remark = '获取'.$this->mvalue.'元红包';
                }
            }
        } else if($this->type=='reward') {
            if($this->mode=='out') {
                $remark = '支付用户投标[<a href="' .WEB_MAIN. \Url::to('/odd/view', ['num'=>$this->oddNumber]) 
                . '" target="_blank">' . $this->odd->oddTitle . '</a>]奖励'.$this->mvalue.'元';
            } else {
                $remark = '用户投标[<a href="' .WEB_MAIN. \Url::to('/odd/view', ['num'=>$this->oddNumber]) . '" target="_blank">' 
                    . $this->odd->oddTitle . '</a>]获取奖励'.$this->mvalue.'元';
            }
        } else if($this->type=='spread') {
            if($this->mode=='out') {
                $pos1 = strpos($this->remark, '->') + 2;
                $pos2 = strpos($this->remark, '->{OPERATE}');
                $userId = substr($this->remark, $pos1, $pos2-$pos1);
                $remark = '支出推广奖励'.$this->mvalue.'元给用户'.$userId;
            } else {
                $remark = '获得推广奖励'.$this->mvalue.'元（好友[<span>' . $this->investUser->username . '</span>]投资[<a href="' 
                    .WEB_MAIN. \Url::to('/odd/view', ['num'=>$this->oddNumber]) . '" target="_blank">' 
                    . $this->odd->oddTitle . '</a>]）';
            }
        } else if($this->type=='capital') {
            if($this->mode=='in') {
                if(strrpos($this->remark, '{GET}{PRINCIPAL}')!==false) {
                    $remark = '借入金额'.$this->mvalue.'元';
                } else if(strrpos($this->remark, '{SYSTEM}:{ODD}')!==false) {
                    $remark = '获得标的[<a href="' .WEB_MAIN. \Url::to('/odd/view', ['num'=>$this->oddNumber]) . '" target="_blank">'
                        . $this->odd->oddTitle . '</a>]还款'.$this->mvalue.'元（本金部分）';
                }
            } else {
                if(strrpos($this->remark, '{UNFREEZE}{SUCCESS}')!==false) {
                    $remark = '投标[<a href="' .WEB_MAIN. \Url::to('/odd/view', ['num'=>$this->oddNumber]) . '" target="_blank">' 
                        . $this->odd->oddTitle . '</a>]成功，支出'.$this->mvalue.'元';
                } else if(strrpos($this->remark, '{SYSTEM}:{ODD}')!==false) {
                    $remark = '返还用户投资本金'.$this->mvalue.'元';
                }
            }
        } else if($this->type=='virtual') {
            if($this->mode=='in') {
                if(strrpos($this->remark, '{GET}{PRINCIPAL}')!==false) {
                    $remark = '借入虚拟金额'.$this->mvalue.'元';
                } else if(strrpos($this->remark, '{SYSTEM}:{ODD}')!==false) {
                    $remark = '获得标的[<a href="' .WEB_MAIN. \Url::to('/odd/view', ['num'=>$this->oddNumber]) . '" target="_blank">'
                        . $this->odd->oddTitle . '</a>]还款'.$this->mvalue.'元（虚拟金部分）';
                }
            } else {
                if(strrpos($this->remark, '{UNFREEZE}{SUCCESS}')!==false) {
                    $remark = '投标[<a href="' .WEB_MAIN. \Url::to('/odd/view', ['num'=>$this->oddNumber]) . '" target="_blank">' 
                        . $this->odd->oddTitle . '</a>]成功，支出虚拟金'.$this->mvalue.'元';
                } else if(strrpos($this->remark, '{SYSTEM}:{ODD}')!==false) {
                    $remark = '返还用户投资虚拟金'.$this->mvalue.'元';
                }
            }
        } else if($this->type=='crtr') {
            $remark = $this->remark;
        } else {
            $remark = str_replace('-->{SUCCESS}', '', $this->remark);
            $remark = str_replace('{INTEREST}{SERVICE}{MONEY}', '利息', $remark);
            $remark = str_replace('{INTEREST}', '利息', $remark);
        }
        return $remark;
    }
    
    public function user() {
        return $this->belongsTo('models\User', 'userId');
    }

    /**
     * 添加用户资金日志
     * @param array $data 信息
     * @param Model $user 用户
     * @return boolean 是否添加成功
     */
    public static function addOne($data, $user) {
        $log = new self();
        $log->type = $data['type'];
        $log->mode = $data['mode'];
        $log->mvalue = $data['mvalue'];
        $log->remark = $data['remark'];
        $log->userId = $user->userId;
        $log->remain = $user->fundMoney;
        $log->time = date('Y-m-d H:i:s');
        return $log->save();
    }

    /**
     * 添加多个用户资金日志
     * @param [type] $data [description]
     * @param [type] $time [description]
     */
    public static function addAll($data, $time) {
        foreach ($data as $key => &$value) {
            $value['time'] = $time;
        }
        return self::insert($data);
    }


    /**
     * 记录日志
     * @param  string $userId   用户ID
     * @param  string $type   类型
     * @param  string $mode   模式[in out freeze unfreeze]
     * @param  double $value  变动金额
     * @param  string $remark 备注
     * @return boolean        是否添加成功
     */
    public static function log($userId, $type, $mode, $value, $remark) {
        $user = User::where('userId', $userId)->first(['userId', 'fundMoney', 'frozenMoney']);
        $data = [];
        $data['userId'] = $user->userId;
        $data['type'] = $type;
        $data['mode'] = $mode;
        $data['mvalue'] = $value;
        $data['remark'] = $remark;
        $data['remain'] = $user->fundMoney;
        $data['frozen'] = $user->frozenMoney;
        $data['time'] = date('Y-m-d H:i:s');
        return self::insert($data);
    }

    public function getRewardFrom() {
    	return '好友投标【<a class="link-style" target="_blank" href="'
    		.WEB_MAIN.'/odd/' . $this->oddNumber . '">' . $this->odd->oddTitle . '</a>】。';
    }

    public function getTypeName() {
        return self::$types[$this->type];
    }

    public function getModeName() {
        if($this->mode=='in') {
            return '收入';
        } else if($this->mode=='out') {
            return '支出';
        } else if($this->mode=='freeze') {
            return '冻结';
        } else if($this->mode=='unfreeze') {
            return '解冻';
        } else if($this->mode=='sync') {
            return '同步';
        } else if($this->mode=='cancel') {
            return '撤销';
        }
        return '';
    }
}