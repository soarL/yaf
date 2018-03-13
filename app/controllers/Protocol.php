<?php
use models\OddMoney;
use models\Odd;
use models\Protocol;
use models\OddClaims;
use exceptions\HttpException;
use traits\protocols\UserProtocol;

/**
 * ProtocolController
 * 主要用户显示用户合同，下载合同(PDF格式)
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class ProtocolController extends Controller {
    use UserProtocol;

    const PROTOCOL_KEY = 'xwsd_protocol_key';

	public $menu = 'protocol';
	public $submenu = 'protocol';
    
    public $oddCols = ['oddNumber', 'oddYearRate', 'oddRehearTime', 'userId', 'oddBorrowStyle', 'oddBorrowPeriod', 'oddType', 'oddRepaymentStyle'];
    public $tenderUserCols = ['userId', 'username', 'name', 'cardnum'];
    public $borrowUserCols = ['userId', 'username', 'name', 'cardnum', 'email', 'city', 'phone'];
    public $investsCols = ['id', 'oddMoneyId', 'zongEr', 'benJin', 'interest', 'endtime'];

    /**
     * 查看合同
     * @param  integer $pronum 合同编号
     * @return  mixed
     */
    public function showAction($pronum=0) {
        $this->submenu = 'show';
        $tender = false;
        
        $user = $this->getUser();

        $oddMoney = OddMoney::with(['user'=>function($query) {
            $query->select($this->tenderUserCols);
        }])->with(['invests'=>function($query) {
            $query->select($this->investsCols);
        }])->where('userId', $user->userId)->where('id', $pronum)->first();

        $tpl = '';
        $data = [];
        if($user&&$oddMoney) {
            $data['params'] = $oddMoney->getProtocolInfo();
            if($oddMoney->type=='credit') {
                $tpl = 'crtr';
            } else if($oddMoney->type=='invest') {
                if($oddMoney->odd->oddType=='danbao') {
                    $tpl = 'lease';
                } else if($oddMoney->odd->oddType=='xiaojin') {
                    $tpl = 'crd';
                } else {
                    $tpl = 'loan';
                }
            }
        } else {
            throw new HttpException(500);
        }
        $this->display($tpl, $data);
    }

    /**
     * 查看借款合同
     * @param  string $num 标的号
     * @return  mixed
     */
    public function loanAction($num='') {
        $link = '';
        $tender = false;
        $odd = Odd::where('oddNumber', $num)->first(['userId', 'oddType', 'oddBorrowStyle', 'oddBorrowPeriod']);
        if(!$odd) {
            throw new HttpException(500);
        }
        $params = [];
        $header = ['还款日期', '每期还款金额'];
        $data = [['&nbsp;', '&nbsp;']];
        $style = [
            'rowWidth' => [50, 50]
        ];
        $params['repayInfo'] = ['header'=>$header, 'data'=>$data, 'style'=>$style];
        if($odd->oddType=='danbao') {
            $header = ['受让方用户名', '姓名', '身份证号', '投标金额', '受让期限', '年利率', '受让开始日', '受让截止日', '投标本息'];
            $data = [['&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;']];
            $style = [
                'rowWidth' => [50, 50, '', 40, 50, '', 80, 80, '']
            ];
            $params['borrowInfo'] = ['header'=>$header, 'data'=>$data, 'style'=>$style];
            $this->display('lease', ['params'=>$params]);
        } else if($odd->oddType=='xiaojin') {
            $this->display('crd', ['params'=>$params]);
        } else {
            $header = ['出借人用户名', '出借人姓名', '身份证号', '投标金额', '借款期限', '年利率', '借款开始日', '借款截止日', '投标本息'];
            $data = [['&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;']];
            $style = [
                'rowWidth' => [50, 50, '', 40, 50, '', 80, 80, '']
            ];
            $params['borrowInfo'] = ['header'=>$header, 'data'=>$data, 'style'=>$style];
            $this->display('loan', ['params'=>$params]);
        }
    }

    /**
     * 生成汇诚普惠用户协议(PDF)
     * @param  string  $output 输出类型
     * @return mixed
     */
    public function generateFourAction($output='F') {
        return $this->generateUser($output);
    }
}