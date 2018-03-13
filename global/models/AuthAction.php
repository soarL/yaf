<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use Yaf\Registry;

/**
 * AuthAction|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class AuthAction extends Model {

	protected $table = 'auth_actions';

	public $timestamps = true;

	public function perm() {
		return $this->hasOne('models\Permission', 'act_id');
	}

	/**
	 * 获取当前菜单
	 * @param  array $menus  所有菜单
	 * @param  string $m     标识符
	 * @return array         当前菜单
	 */
	public static function getTitle($menus, $m ,$sm) {
		$activeMenu = false;
		$activesMenu = false;
		foreach ($menus as $menu) {
			if($menu['key']==$m) {
				$activeMenu = $menu['name'];
				foreach ($menu['subs'] as $key => $value) {
					if($value['key']==$sm) {
						$activesMenu = $value['name'];
						break;
					}
				}
				break;
			}
		}
		return $activeMenu.'-'.$activesMenu;
	}


	public static function menus($user) {
		if(!$user) {
			return [];
		}
		$isAuth = Registry::get('isAuth');
		$menus = self::where('is_menu', 'y')->orderBy('rank', 'desc')->orderBy('id', 'asc')->get();
		$showMenus = [];
		foreach ($menus as $menu) {
			if($isAuth) {
				if($user->can($menu->identifier, true)) {
					$showMenus[]  = $menu;
				}
			} else {
				$showMenus[]  = $menu;
			}

	//	$showMenus[]  = $menu;

		}
		$realMenus = [];
		foreach ($showMenus as $key => $subMenu) {
			if($subMenu->parent_id==0) {
				unset($showMenus[$key]);
				$subs = self::getSubMenus($subMenu, $showMenus);
				$realMenus[] = [
					'id' => $subMenu->id,
					'link'=>$subMenu->getRealLink(), 
					'name'=>$subMenu->name, 
					'key'=>$subMenu->identifier, 
					'icon'=>$subMenu->icon, 
					'is_menu'=>$subMenu->is_menu, 
					'subs'=>$subs
				];
			}
		}
		return $realMenus;
	}

	public static function tree() {
		$actions = self::orderByRaw('field(is_menu, ?, ?)', ['y', 'n'])->orderBy('rank', 'desc')->orderBy('id', 'asc')->get();
		$list = [];
		foreach ($actions as $key => $action) {
			if($action->parent_id==0) {
				unset($actions[$key]);
				$subs = self::getSubMenus($action, $actions);
				$list[] = [
					'id' => $action->id,
					'link'=>$action->getRealLink(), 
					'name'=>$action->name, 
					'key'=>$action->identifier, 
					'icon'=>$action->icon, 
					'is_menu'=>$action->is_menu, 
					'subs'=>$subs
				];
			}
		}
		return $list;
	}

	public static function getSubMenus($top, $menus) {
		$list = [];
		foreach ($menus as $key => $menu) {
			if($menu->parent_id==$top->id) {
				unset($menus[$key]);
				$subs = self::getSubMenus($menu, $menus);
				$list[]  = [
					'id' => $menu->id,
					'link'=>$menu->getRealLink(), 
					'name'=>$menu->name, 
					'key'=>$menu->identifier, 
					'icon'=>$menu->icon, 
					'is_menu'=>$menu->is_menu, 
					'subs'=>$subs
				];
			}
		}
		return $list;
	}

	/**
	 * 获取当前菜单
	 * @param  array $menus  所有菜单
	 * @param  string $m     标识符
	 * @return array         当前菜单
	 */
	public static function getActiveMenu($menus, $m) {
		$activeMenu = false;
		foreach ($menus as $menu) {
			if($menu['key']==$m) {
				$activeMenu = $menu;
				break;
			}
		}
		return $activeMenu;
	}

	public function getRealLink() {
		$host = '';
		if($this->domain=='www') {
			$host = WEB_MAIN;
		} else if($this->domain=='user') {
			$host = WEB_USER;
		}
		$module = '';
		if($this->module!='Index') {
			$module = '/' . strtolower($this->module);
		} else {
			$module = '';
		}
		$link = '';
		if($host) {
			$link = $host . $module . $this->link;
		} else {
			$link = $module . $this->link;
		}
		return $link;
	}
}
