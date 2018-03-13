<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * Banner|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class Banner extends Model {
	const INDEX_AD_TYPE = 3;
	const INDEX_BANNER_TYPE = 1;
	const INDEX_MEDIA_TYPE = 4;
	const ACTIVE_STATUS = 1;

	protected $table = 'system_banner';

	public $timestamps = false;


	public static function getByType($type_id) {
		$banners = self::where('type_id',$type_id)
			->where('status',self::ACTIVE_STATUS)
			->orderBy('banner_order','asc')
			->orderBy('addtime','desc')
			->get();
		return $banners;
	}

	/**
	 * 获取首页轮播图
	 * @return array 首页轮播图
	 */
	public static function getIndexBanners() {
		$banners = self::where('type_id',self::INDEX_BANNER_TYPE)
			->where('status',self::ACTIVE_STATUS)
			->orderBy('banner_order','asc')
			->orderBy('addtime','desc')
			->get();
		return $banners;
	}

	/**
	 * 获取首页广告图
	 * @return array 首页广告图
	 */
	public static function getIndexAd() {
		$ad = self::where('type_id',self::INDEX_AD_TYPE)
			->where('status',self::ACTIVE_STATUS)
			->orderBy('banner_order','asc')
			->orderBy('addtime','desc')
			->first();
		return $ad;
	}

	/**
	 * 获取首页媒体报告
	 * @return array 首页媒体报告
	 */
	public static function getIndexMedias() {
		$medias = self::where('type_id',self::INDEX_MEDIA_TYPE)
			->where('status',self::ACTIVE_STATUS)
			->orderBy('banner_order','asc')
			->orderBy('addtime','desc')
			->get();
		return $medias;
	}
}