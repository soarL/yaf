<?php
namespace traits\handles;

use Yaf\Registry;
use models\UserMail;
use models\Attribute;
use models\Odd;
use models\Crtr;
use tools\Config;
use \Asset;

/**
 * DisplayHandle
 * 页面渲染公共处理器
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
trait DisplayHandle {
	public $request;
    public $menu = 'index';
    public $submenu = '';
    public $mode = '';
    public $title = '';
    public $keywords = '';
    public $description = '';
    public $queries;
    public $moduleAlias;

    /**
     * 页面显示
     * @param  string $template 模版名称
     * @param  array $data 数据
     */
    public function display($template, array $data = null) {
        $request = $this->getRequest();
        $module = $request->module;
        $controller = strtolower($request->controller);
        $action = strtolower($request->action);

        $assets = Config::json('assets', Config::APP);
        
        defined('RSRC')?true:define('RSRC', WEB_ASSET . $assets['rsrc']);

        $moduleAsset = _isset($assets['modules'], $module);
        $assetKey = $controller.'.'.$action;
        $assetConfig = _isset($moduleAsset['actions'], $assetKey);

        $assetConfig['name'] = $assetKey;

        $params = $this->displayParams();

        $params['menu'] = $this->menu;
        $params['submenu'] = $this->submenu;
        $params['mode'] = $this->mode;
        $params['module'] = $module;
        $params['action'] = $action;
        $params['controller'] = $controller;
        $params['moduleAsset'] = $moduleAsset;

        $assetConfig['params'] = $params;

        $asset = new Asset($assetConfig);
        $asset->moduleAlias = $this->moduleAlias;
        Registry::set('asset', $asset);
        
        parent::display($template, $data);

        $this->afterDisplay();

        exit(0);
    }

    protected function displayParams() {
        $attributes = Attribute::getByIdentity(['title', 'keywords', 'description']);
        if($this->title=='') {
            $this->title = $attributes['title'];
        }
        if($this->keywords=='') {
            $this->keywords = $attributes['keywords'];
        }
        if($this->description=='') {
            $this->description = $attributes['description'];
        }

        $params = [];
        $params['title'] = $this->title;
        $params['keywords'] = $this->keywords;
        $params['description'] = $this->description;

        return $params;
    }

    final protected function afterDisplay() {
        $behaviors = isset($this->behaviors['afterDisplay'])?$this->behaviors['afterDisplay']:[];
        foreach ($behaviors as $behaviorClass) {
            $behavior = new $behaviorClass();
            $behavior->todo();
        }
    }
}