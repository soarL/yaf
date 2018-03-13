<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use \Cache;

/**
 * News|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class News extends Model {

	protected $table = 'system_news';

	public $timestamps = false;

	public function type() {
  		return $this->hasOne('models\NewsType', 'type_key', 'news_type');
  	}

	public function expectOdd() {
  		return $this->hasMany('models\ExpectOdd', 'news_id');
  	}
	
	public static function getList($type, $limit=3, $cache=true) {
		$list = [];
		if($cache) {
			$data = Cache::get('news_'.$type);
			if($data) {
				$list = json_decode($data);
				if(count($list) > $limit){
					$list = array_slice($list,0,$limit);
				}
				return $list;
			}
		}

		$newsColumns = ['id','news_title', 'news_time'];
		$list = self::where('news_type', $type)
			->orderBy('news_order', 'asc')
			->orderBy('news_time', 'desc')
			->limit($limit)
			->get($newsColumns);
		return $list;
	}

	/**
	 * 更新首页新闻缓存
	 */
	public static function updateIndexCache($type) {
		$newsColumns = ['id','news_title', 'news_time', 'news_image', 'news_abstract'];
		$limit = 7;
		$typeNums = [
			'tiqian'=> 7,
			'officenews'=> 7,
			'announce'=> 7,
			'seo'=> 10,
			'notice'=> 7
		];
		if(isset($typeNums[$type])) {
			$limit  = $typeNums[$type];
		}
		$list = self::where('news_type', $type)
			->orderBy('news_order', 'asc')
			->orderBy('news_time', 'desc')
			->limit($limit)
			->get($newsColumns)
			->toArray();

		return Cache::set('news_'.$type, json_encode($list));
	}
}