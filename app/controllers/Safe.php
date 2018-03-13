<?php
use models\Odd;
use models\OddInfo;

/**
 * SafeController
 * 对应主栏目【安全保障】下的一系列页面
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class SafeController extends Controller {
	public $menu = 'safe';
	public $submenu = 'safe';

	/**
     * 安全保障主页
     * @return  mixed
     */
	public function indexAction() {
		$this->display('index');
	}

	/**
     * 第三方托管--汇潮简介
     * @return  mixed
     */
	public function payOneAction() {
		$this->submenu = 'pay';
		$this->display('payOne');
	}

	/**
     * 第三方托管--托管流程
     * @return  mixed
     */
	public function payTwoAction() {
		$this->submenu = 'pay';
		$this->display('payTwo');
	}

	/**
     * 第三方托管--资金安全
     * @return  mixed
     */
	public function payThreeAction() {
		$this->submenu = 'pay';
		$this->display('payThree');
	}

	/**
     * 法律保障
     * @return  mixed
     */
	public function lawAction() {
		$this->submenu = 'law';
		$this->display('law');
	}

	/**
     * 法律保障--资金安全保障措施公示公告 
     * @return  mixed
     */
	public function contractOneAction() {
		$this->submenu = 'law';
		$this->display('contractOne');
	}

	/**
     * 法律保障--借款合同 
     * @return  mixed
     */
	public function contractTwoAction() {
		$this->submenu = 'law';
		$this->display('contractTwo');
	}

	/**
     * 法律保障--债权转让协议 
     * @return  mixed
     */
	public function contractThreeAction() {
		$this->submenu = 'law';
		$this->display('contractThree');
	}

	/**
     * 法律保障--债权转让协议（受让人第三人） 
     * @return  mixed
     */
	public function contractFourAction() {
		$this->submenu = 'law';
		$this->display('contractFour');
	}

	/**
     * 法律保障--居间服务电子协议 
     * @return  mixed
     */
	public function contractFiveAction() {
		$this->submenu = 'law';
		$this->display('contractFive');
	}

	/**
     * 注册页面--网站服务协议 
     * @return  mixed
     */
	public function contractSixAction() {
		$this->submenu = 'law';
		$this->display('contractSix');
	}

	/**
     * 风险拔备金
     * @return  mixed
     */
	public function hazardAction() {
		$this->submenu = 'hazard';
		$this->display('hazard');
	}

	/**
     * 证信查询
     * @return  mixed
     */
	public function creditAction($oddNumber) {
		$this->submenu = 'credit';
		$odd = OddInfo::where('oddNumber', $oddNumber)->first(['bankCreditReport']);
		$creditImg = '';
		if($odd->bankCreditReport!=null&&$odd->bankCreditReport!='') {
			$imgsList = $odd->getImages('bankCreditReport');
			$creditImg = $imgsList[0]['normal'];
		}
		$this->display('credit',['creditImg'=>$creditImg]);
	}

	/**
	 * 风险提示
	 */
	public function riskAction() {
		$this->submenu = 'risk';
		$this->display('risk');
	}

	/**
	 * 安存提示
	 */
	public function ancunAction() {
		$this->submenu = 'ancun';
		$this->display('ancun');
	}

    /**
     * 存管开户协议
     */
    public function custodyAction() {
        $this->submenu = 'custody';
        $this->display('custody');
    }
}