<?php
use Yaf\Registry;
use helpers\StringHelper;
use helpers\NetworkHelper;
use tools\WebSign;

class TestappController extends Controller {
    
  public $menu = 'testapp';
  public $baseUrl = 'https://app.hcjrfw.com/api';

  /**
   * 首页
   */
  public function indexAction() {
    $params = [];
    $params['userId'] = '1000011869';
    $this->api('/index', $params);
  }

  /**
   * 标的列表
   */
  public function oddsAction() {
    $params = [];
    $params['type'] = 'all';
    $params['page'] = '1';
    $params['pageSize'] = '10';
    $params['period'] = 'all';
    $params['userId'] = '1000011869';
    $this->api('/odds', $params);
  }

  /**
   * 标的详情页
   */
  public function oddAction() {
    $params = [];
    $params['oddNumber'] = '20170605000003';
    $params['userId'] = '1000011869';
    $this->api('/odd', $params);
  }

  /**
   * 标的风控材料
   */
  public function oddRMAction() {
    $params = [];
    $params['oddNumber'] = '20170605000003';
    $params['userId'] = '1000011869';
    $this->api('/oddRM', $params);
  }

  /**
   * 债权列表
   */
  public function crtrsAction() {
    $params = [];
    $params['page'] = '1';
    $params['pageSize'] = '10';
    $this->api('/crtrs', $params);
  }

  /**
   * 债权详情页
   */
  public function crtrAction() {
    $params = [];
    $params['id'] = '4';
    $params['userId'] = '1000011869';
    $this->api('/crtr', $params);
  }

  /**
   * 用户银行卡
   */
  public function userBankCardAction() {
    $params = [];
    $params['userId'] = '1000011856';
    $this->api('/userBankCard', $params);
  }

  /**
   * 修改手机号
   */
  public function updatePhoneAction() {
    $params = [];
    $params['phone'] = '18760419666';
    $params['userId'] = '1000011869';
    $this->link('/updatePhone', $params);
  }

  /**
   * 设置存管密码
   */
  public function setCustodypassAction() {
    $params = [];
    $params['userId'] = '1000011869';
    $this->link('/setCustodypass', $params);
  }

  /**
   * 修改存管密码
   */
  public function updateCustodypassAction() {
    $params = [];
    $params['userId'] = '1000011869';
    $this->link('/updateCustodypass', $params);
  }

  /**
   * 计算器
   */
  public function calculateAction() {
    $params = [];
    $params['account'] = '1000';
    $params['repayType'] = 'monthpay';
    $params['period'] = 12;
    $params['yearRate'] = 0.15;
    $params['periodType'] = 'month';
    $this->api('/calculate', $params);
  }

  /**
   * 解绑银行卡
   */
  public function CardUnbindAction() {
    $params = [];
    $params['userId'] = '1000011877';
    $params['bankNum'] = '6222988812340039';
    $this->api('/CardUnbind', $params, 'post');
  }

  /**
   * 绑定银行卡
   */
  public function CardBindAction() {
    $params = [];
    $params['userId'] = '1000011903';
    $params['bankNum'] = '6222988812340036';
    $params['media'] = 'Android';
    $this->link('/CardBind', $params);
  }

  /**
   * 同步银行卡
   */
  public function CardRefreshAction() {
    $params = [];
    $params['userId'] = '1000011877';
    $this->api('/CardRefresh', $params, 'post');
  }

  /**
   * 获取用户信息
   */
  public function getUserInfoAction() {
    $params = [];
    $params['userId'] = '1000011869';
    $this->api('/getUserInfo', $params, 'post');
  }

  /**
   * 获取银行卡限额
   */
  public function cardLimitAction() {
    $params = [];
    $params['userId'] = '1000011877';
    $this->api('/cardLimit', $params, 'post');
  }

  private function api($url, $params, $method='get') {
    $url = $this->baseUrl . $url;
    $sign = WebSign::sign($params);
    $params[WebSign::SIGN_KEY] = $sign;
    $result = NetworkHelper::curlRequest($url, $params, $method);
    echo $result;
    echo '<pre>';
    print_r(json_decode($result, true));
    echo '</pre>';
  }

  private function link($url, $params) {
    $url = $this->baseUrl . $url;
    $sign = WebSign::sign($params);
    $params[WebSign::SIGN_KEY] = $sign;
    $url = $url . '?' . StringHelper::encodeQueryString($params, false);
    $this->redirect($url);
  }
}