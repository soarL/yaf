<?php
namespace tools;
use Yaf\Request_Abstract;
use \Tag;
use \Url;
class Pager {
	const DEFAULT_MODULE = 'Index';
	const FIRST = '<<';
	const LAST = '>>';
	const PREVIOUS = '上一页';
	const NEXT = '下一页';

	public $request;
	public $params;

	private $_total;
	private $_url;
	private $_totalPage = 0;
	private $_page = 1;
	private $_limit = 10;
	private $_offset = 0;
	private $_isShowPN = true;
	private $_isShowFL = false;
	private $_showSize = 4;
	private $_pageParam = 'page';
	private $_isDy = false;

	public function __construct($data = array()) {
		if(isset($data['request'])) {
			$this->request = $data['request'];
		} else {
			throw new \Exception('缺少request参数错误!');
		}
		if(isset($data['total'])) {
			$this->_total = $data['total'];
		} else {
			throw new \Exception('缺少total参数错误!');
		}
		if(isset($data['pageParam'])) {
			$this->_pageParam = $data['pageParam'];
		}
		if(isset($data['isShowPN'])) {
			$this->_isShowPN = $data['isShowPN'];
		}
		if(isset($data['isShowFL'])) {
			$this->_isShowFL = $data['isShowFL'];
		}
		if(isset($data['pageSize'])) {
			$this->_limit = $data['pageSize'];
		}
		if(isset($data['showSize'])) {
			$this->_showSize = $data['showSize'];
		}
		if(isset($data['isDy'])) {
			$this->_isDy = $data['isDy'];
		}

		// $this->_limit = $pageSize;
		if($this->_total%$this->_limit==0) {
			$this->_totalPage = $this->_total/$this->_limit;
		} else {
			$this->_totalPage = intval($this->_total/$this->_limit) + 1;
		}

		if($this->request->isGet()) {
			$page = $this->request->getQuery($this->_pageParam,1);
		} else {
			$page = $this->request->getPost($this->_pageParam,1);
		}

		if($page<1) {
			$page = 1;
		}
		if($page>$this->_totalPage) {
			$page = $this->_totalPage;
		}
		$this->_page = $page;
		$this->_offset = ($this->_page-1)*$this->_limit;
		if($this->_offset<0) {
			$this->_offset = 0;
		}
		if(!$this->_isDy) {
			$this->parseUrl();
		}
	}

	public function getPage() {
		return intval($this->_page);
	}

	public function getTotalPage() {
		return intval($this->_totalPage);
	}

	public function html() {
		if($this->_totalPage<=0) {
			return '<div class="pagination no-pagination"><span class="no-result">暂无记录</span></div>';
		}
		$html = '<div class="pagination">#UL_TAG#</div>';
		$liTags = [];
		if($this->_totalPage<=$this->_showSize) {
			for ($i=0; $i < $this->_totalPage; $i++) {
				$liTags[] = $this->generateLiTag($i+1);
			}
		} else {
			$halfLimit = 0;
			$showSizeType = 0;
			if($this->_showSize%2==0) {
				$halfLimit = intval($this->_showSize/2);
				$showSizeType = 1;
			} else {
				$halfLimit = intval(($this->_showSize-1)/2);
			}
			$beginPage = $this->_page - $halfLimit;
			$endPage = $this->_page + $halfLimit;
			if($beginPage<1) {
				$beginPage = 1;
				$endPage = $this->_showSize;
				if($showSizeType==1) {
					$endPage += 1;
				}
			}
			if($this->_page>($this->_totalPage-$halfLimit)) {
				$endPage = $this->_totalPage;
				$beginPage = $this->_totalPage-$this->_showSize + 1;
				if($showSizeType==1) {
					$beginPage -= 1;
				}
			}
			for ($i=$beginPage; $i <= $endPage; $i++) {
				$liTags[] = $this->generateLiTag($i);
			}

		}
		if($this->_isShowPN) {
			$prePage = $this->_page - 1;
			$nextPage = $this->_page + 1;
			if($this->_page<=1) {
				$prePage = 1;
			}
			if($this->_page>=$this->_totalPage) {
				$nextPage = $this->_totalPage;
			}
			array_unshift($liTags, $this->generateLiTag($prePage, 'previous'));
			array_push($liTags, $this->generateLiTag($nextPage, 'next'));
		}
		if($this->_isShowFL) {
			array_unshift($liTags, $this->generateLiTag(1, 'first'));
			array_push($liTags, $this->generateLiTag($this->_totalPage, 'last'));
		}

		$liTagsStr = '';
		foreach ($liTags as $key => $liTag) {
			if($key==0) {
				$liTag->addClass('first-child');
			}
			if($key==count($liTags)-1) {
				$liTag->addClass('last-child');
			}
			$liTagsStr .= $liTag->html();
		}
		$ulTag = '<ul class="pagination">'.$liTagsStr.'</ul>';
		$html = str_replace('#UL_TAG#', $ulTag, $html);
		return $html;
	}

	private function generateLiTag($page, $special='') {
		$url = 'javascript:;';
		if(!$this->_isDy) {
			$url = $this->_url . $this->_pageParam . '=' . $page;
		}

		$liTag = new Tag('li');
		$aTag = new Tag('a');
		if($this->_isDy) {
			$aTag->setAttribute('data-page', $page);
		}
		if($special=='next') {
			$aTag->setAttribute('href', $url)->setContent(self::NEXT)->setAttribute('title', self::NEXT);
			$liTag->addClass('next')->setContent($aTag->html());
		} else if($special=='previous') {
			$aTag->setAttribute('href', $url)->setContent(self::PREVIOUS)->setAttribute('title', self::PREVIOUS);
			$liTag->addClass('previous')->setContent($aTag->html());
		} else if($special=='first') {
			$aTag->setAttribute('href', $url)->setContent(self::FIRST)->setAttribute('title', '第一页');
			$liTag->addClass('first')->setContent($aTag->html());
		} else if($special=='last') {
			$aTag->setAttribute('href', $url)->setContent(self::LAST)->setAttribute('title', '最后一页');
			$liTag->addClass('last')->setContent($aTag->html());
		} else {
			$aTag->setAttribute('href', $url)->setContent($page);
			$liTag->addClass('index');
			if($this->_page==$page) {
				$liTag->addClass('current');

				// 后台样式需要
				$liTag->addClass('active');

				$aTag->addClass('active');
			}
			$liTag->setContent($aTag->html());
		}
		return $liTag;
	}

	public function getLimit() {
		return intval($this->_limit);
	}

	public function getOffset() {
		return intval($this->_offset);
	}

	private function parseUrl() {
		$url = '';
		$module = $this->request->getModuleName();
		$controller = $this->request->getControllerName();
		$action = $this->request->getActionName();
		
		if($module == self::DEFAULT_MODULE) {
			$url = '/' . $controller . '/' . $action;
		} else {
			$url = '/' . $module . '/' . $controller . '/' . $action;
		}
		$this->_url = Url::to(strtolower($url));

		$params = [];
		if($this->request->isGet()) {
			$params = $this->request->getQuery();
		} else {
			$params = $this->request->getPost();
		}
		$this->_url .= '?';
		foreach ($params as $key => $value) {
			if(is_string($value)) {
				$value = htmlspecialchars($value);
			}
			if($key!=$this->_pageParam) {
				$this->_url .= $key . '=' . $value . '&';
			}
			$this->$key = $value;
			$params[$key] = $value;
		}
		$this->params = $params;
	}

	public function getParam($name, $default='') {
		return isset($this->params[$name])?$this->params[$name]:$default;
	}

	public function implodeParams($default = []) {
		$paramsStr = '';
		foreach ($this->params as $key => $value) {
			$paramsStr .= $key . '=' . $value . '&';
		}
		foreach ($default as $key => $value) {
			if(!isset($this->params[$key])) {
				$paramsStr .= $key . '=' . $value . '&';
			}
		}
		$paramsStr = '?'.rtrim($paramsStr, '&');
		return $paramsStr;
	}
}