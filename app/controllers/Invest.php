<?php
use helpers\StringHelper;
use Yaf\Registry;
use tools\Pager;
use models\OldOdd;
use models\OldInvest;
use models\User;
use models\UserOffice;
use helpers\HtmlHelper;
use exceptions\HttpException;

/**
 * InvestController
 * 旧系统标的及投资数据
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class InvestController extends Controller {
	public $menu = 'odd';
	
	public function viewAction($num=0) {
		$user = $this->getUser();
		if($num==0) {
			throw new HttpException(404);
		}
		$select = [
			'user',
			'name',
			'account',
			'borrow_type',
			'borrow_apr',
			'borrow_contents',
			'borrow_period',
			'borrow_style',
			'addtime',
			'verify_time',
			'reverify_time',
			'borrow_nid'
		];
		$odd = OldOdd::where('borrow_nid', $num)->first();
		$oddUser = User::find($odd->user_id);
		$oddUserCompany = UserOffice::where('userId', $odd->user_id)->first();

		$builder = OldInvest::where('status', 1)->where('borrow_nid', $num);
		$count = $builder->count();
		$totalMoney = $builder->sum('account');

		$this->display('view', [
			'odd'=>$odd, 
			'oddUser'=>$oddUser, 
			'user'=>$user,
			'count'=>$count,
			'totalMoney'=>$totalMoney,
			'oddUserCompany'=>$oddUserCompany,
		]);
	}

	public function tendersAction() {
		if($this->getRequest()->isXmlHttpRequest()) {
			$request = $this->getRequest();
			$num = $request->getPost('num');

			$builder = OldInvest::where('status', 1)->where('borrow_nid', $num);
			$count = $builder->count();
			$pager = new Pager(['total'=>$count, 'request'=>$request, 'isDy'=>true, 'pageSize'=>10]);
			$limit = $pager->getLimit();
			$offset = $pager->getOffset();
			$tenders = $builder->with('user')->skip($offset)->limit($limit)->get();
			$newTenders = [];
			foreach ($tenders as $key => $tender) {
				$newTender = [];
				$newTender['normalKey'] = ($pager->getPage()-1)*$limit+$key+1;
				$newTender['username'] = StringHelper::getHideUsername($tender->user->username);
				$newTender['addtime'] = date('Y-m-d H:i:s', $tender->addtime);
				$newTender['account'] = $tender->account;
				
				if(strpos($tender->contents, '自动投标')!==false) {
					$newTender['bidType'] = '自动投标';
				} else {
					$newTender['bidType'] = '手动投标';
				}
				$newTenders[$key] = $newTender;
			}
			$template = '<tr class="dark">'
                .  '<td>#normalKey#</td>'
                .  '<td><a href="#" target="_blank">#username#</a></td>'
                .  '<td class="text-right"><em>#account#</em>元</td>'
                .  '<td>#bidType#</td>'
                .  '<td class="text-right">#addtime#</td>'
                .'</tr>';
			$rdata = [];
			$rdata['pager'] = $pager->html();
			$rdata['records'] = HtmlHelper::tableRecords($newTenders, $template);
			$this->backJson($rdata);
		}
	}
}