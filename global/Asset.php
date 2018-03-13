<?php
/**
 * Asset
 * 资源配置类，用于css、js调配及公共参数配置
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */

use Yaf\Registry;
class Asset {
	/**
	 * 全局配置标识符
	 */
	const G_CONFIG = 'config';

	/**
	 * 全局依赖标识符
	 */
	const G_ASSET = 'global';

	/**
	 * js是否使用了AMD模式或CMD模式
	 */
	public $ac = true;

	/**
	 * 本资源配置对象的名称
	 */
	public $name = '';
	/**
	 * 是否依赖全局资源@
	 */
	public $ug = true;
	/**
	 * 是否使用系统本身机制生成js模块
	 */
	public $gm = false;
	/**
	 * 使用的js模块，默认为site，当$gm为false才有效
	 */
	public $mod = 'site';
	/**
	 * 使用JS模块的域
	 */
	public $moduleAlias;
	/**
	 * 使用的资源地址（如果使用了外部资源的话）
	 */
	public $url = '';
	/**
	 * 站点，可以是多个
	 */
	public $sites = [];
	/**
	 * head中的title标签的值
	 */
	public $title = '';
	public $metas = [
		/*['http-equiv'=>'Content-Type', 'content'=>'text/html; charset=utf-8'], 
		['name'=>'360-site-verification', 'content'=>'d436fe2e66a413be18f121c21953431f'],*/
	];
	public $css = [];
	public $beginJs = [];
	public $js = [];
	/**
	 * 依赖其他资源，@表示全局资源
	 * 被依赖资源会先加载
	 * 依赖仅对css/js/beginJs有效，其他如js模块属性(gm/mod/ac)及title/metas无依赖
	 */
	public $deps = [];
	public $params = [];
	private $beginTags = [];
	private $endTags = [];
	private $depObjs = [];

	/**
	 * 构造方法
	 * @param array $config 配置参数
	 *   [meta] 元信息
	 *   [beginJs] 头部js
	 *   [css] 样式
	 *   [js] 尾部js，js一般都在尾部
	 *   [deps] 依赖的其他资源, 可以是类名，也可以是配置名
	 *   [params] 其他参数，其中包含title, keywords, description三个重要参数，用于seo
	 */
	public function __construct($config=array()) {
		$this->url = RSRC;
        $this->sites['main'] = WEB_MAIN;
        $this->sites['user'] = WEB_USER;

		$metas = _isset($config, 'metas', []);
		$this->metas = array_merge($this->metas, $metas);

		$this->js = _isset($config, 'js', $this->js);
		$this->beginJs = _isset($config, 'beginJs', $this->beginJs);
		$this->css = _isset($config, 'css', $this->css);

		$this->name = _isset($config, 'name', $this->name);
		$this->params = _isset($config, 'params', $this->params);
		$moduleAsset = _isset($this->params, 'moduleAsset', []);
		$moduleConfig = _isset($moduleAsset, self::G_CONFIG, []);
		$this->ac = _isset($moduleConfig, 'ac', $this->ac);

		$this->ac = _isset($config, 'ac', $this->ac);
		$this->gm = _isset($config, 'gm', $this->gm);
		$this->ug = _isset($config, 'ug', $this->ug);
		$this->mod = _isset($config, 'mod', $this->mod);

		$this->deps = _isset($config, 'deps', $this->deps);
		if($this->ug) {
			$this->deps[] = self::G_ASSET;
		}
		
		$this->title = _isset($this->params, 'title', $this->title);
		$keywords = _isset($this->params, 'keywords', '');
		$this->metas[] = ['name'=>'keywords', 'content'=>$keywords];
		$description = _isset($this->params, 'description', '');
		$this->metas[] = ['name'=>'description', 'content'=>$description];
		
		foreach ($this->deps as $dep) {
			if($this->name!=$dep) {
				if(@class_exists($dep)) {
					$depObj = new $dep();
					$this->depObjs[] = $depObj;
				} else {
					if(isset($moduleAsset['actions'][$dep])) {
						$assetConfig = $moduleAsset['actions'][$dep];
						$assetConfig['name'] = $dep;
						$assetConfig['params'] = $this->params;
						$depObj = new Asset($assetConfig);
						$this->depObjs[] = $depObj;
					}
				}
			}
		}	
	}

	public function init() {

	}

	/**
	 * 生成头部标签
	 * @return array 标签数组
	 */
	protected function generateBeginTags() {
		$tags = ['meta'=>[], 'css'=>[], 'js'=>[]];
		$metaTags = [];
		$jsTags = [];
		$cssTags = [];

		foreach ($this->depObjs as $depObj) {
			$tags = $depObj->generateBeginTags();
			$metaTags = array_merge($metaTags, $tags['meta']);
			$jsTags = array_merge($jsTags, $tags['js']);
			$cssTags = array_merge($cssTags, $tags['css']);
		}

		$metaTags = array_unique($metaTags);
		$jsTags = array_unique($jsTags);
		$cssTags = array_unique($cssTags);

		foreach ($this->metas as $meta) {
			$tag = '';
			$tag = '<meta';
			foreach ($meta as $a => $v) {
				$tag .= ' ' . $a . '="' . $v . '"';
			}
			$tag .= '/>';
			if(!in_array($tag, $metaTags)) {
				$metaTags[] = $tag;
			}
		}
		foreach ($this->css as $c) {
			$tag = '';
			if(strpos($c, 'http://')===false&&strpos($c, 'https://')===false&&strpos($c, '//')!==0) {
				$tag = '<link type="text/css"  href="' . $this->url . $c . '" rel="stylesheet"/>';
			} else {
				$tag = '<link type="text/css"  href="' . $c . '" rel="stylesheet"/>';
			}
			if(!in_array($tag, $cssTags)) {
				$cssTags[] = $tag;
			}
		}
		foreach ($this->beginJs as $j) {
			$tag = '';
			if(strpos($j, '@')===0) {
				$tag = '<script type="text/javascript">'
					. rtrim(ltrim($j, '@{'), '}')
					. '</script>';
			} else {
				if(strpos($j, 'http://')===false&&strpos($j, 'https://')===false&&strpos($j, '//')!==0) {
					$tag = '<script type="text/javascript" src="' . $this->url . $j . '"></script>';
				} else {
					$tag = '<script type="text/javascript" src="' . $j . '"></script>';
				}
			}
			if(!in_array($tag, $jsTags)) {
				$jsTags[] = $tag;
			}
		}
		return ['meta'=>$metaTags, 'css'=>$cssTags, 'js'=>$jsTags];
	}

	/**
	 * 生成尾部标签
	 * @return array 标签数组
	 */
	protected function generateEndTags() {
		$tags = [];
		foreach ($this->depObjs as $depObj) {
			$depTags = $depObj->generateEndTags();
			$tags = array_merge($tags, $depTags);
		}
		$tags = array_unique($tags);
		foreach ($this->js as $j) {
			if(strpos($j, '@')===0) {
				$tag = '<script type="text/javascript">'
					. rtrim(ltrim($j, '@{'), '}')
					. '</script>';
			} else {
				if(strpos($j, 'http://')===false&&strpos($j, 'https://')===false&&strpos($j, '//')!==0) {
					$tag = '<script type="text/javascript" src="' . $this->url . $j . '"></script>';
				} else {
					$tag = '<script type="text/javascript" src="' . $j . '"></script>';
				}
			}
			if(!in_array($tag, $tags)) {
				$tags[] = $tag;
			}
		}
		return $tags;
	}

	/**
	 * 输出头部标签
	 * @return array 标签
	 */
	public function begin() {
		$beginTags = $this->generateBeginTags();
		$this->beginTags = array_merge($beginTags['meta'], $beginTags['css'], $beginTags['js']);
		echo '<title>' . $this->title . '</title>'."\n";
		echo implode("\n", $this->beginTags)."\n";
	}

	/**
	 * 输出尾部标签
	 * @return string 标签
	 */
	public function end() {
		$this->endTags = $this->generateEndTags();
		echo implode("\n", $this->endTags)."\n";
		$this->generateJSModule();
	}

	/**
	 * 生成js模块
	 * @return array 标签
	 */
	private function generateJSModule() {
		if($this->ac) {
			$mods = [];
			$node = '\'';
			$realModule = $this->moduleAlias?$this->moduleAlias:$this->params['module'];
			if($this->gm) {
				$mods[] = $node . $realModule . '/' . $this->params['controller'] . '/' . $this->params['action'] . $node;
			} else {
				if(is_array($this->mod)) {
					foreach ($this->mod as $m) {
						$mods[] = $node . $realModule . '/' . $m . $node;
					}
				} else {
					$mods[] = $node . $realModule . '/' . $this->mod . $node;
				}
			}
			echo '<script type="text/javascript">require([' . implode(',', $mods) . '])</script>'."\n";
		}
	}
}