<?php
namespace tools;
use \Data;
class Areas {
	public static function getProvinces() {
		$provinces = Data::get('areas');
		$newProvinces = [];
		$i = 0;
		foreach ($provinces as $key => $province) {
			$newProvinces[$i]['key'] = $key;
			$newProvinces[$i]['name'] = $province['provinceName'];
			$i ++;
		}
		return $newProvinces;
	}

	public static function getCitys($provinceId) {
		$provinces = Data::get('areas');
		$citys = [];
		if(isset($provinces[$provinceId]['city'])) {
			$citys = $provinces[$provinceId]['city'];
		}
		$newCitys = [];
		$i = 0;
		foreach ($citys as $key => $city) {
			$newCitys[$i]['key'] = $key;
			$newCitys[$i]['name'] = $city['cityName'];
			$i++;
		}
		return $newCitys;
	}

	public static function getAreas($provinceId, $cityId) {
		$provinces = Data::get('areas');
		$areas = $provinces[$provinceId]['city'][$cityId]['area'];
		$newAreas = [];
		$i = 0;
		foreach ($areas as $key => $area) {
			$newAreas[$i]['key'] = $key;
			$newAreas[$i]['name'] = $area;
			$i++;
		}
		return $newAreas;
	}

	public static function isProvinceExist($provinceId) {
		$provinces = Data::get('areas');
		return isset($provinces[$provinceId]);
	}

	public static function isCityExist($provinceId, $cityId) {
		$provinces = Data::get('areas');
		return isset($provinces[$provinceId]['city'][$cityId]);
	}

	public static function isAreaExist($provinceId, $cityId, $areaId) {
		$provinces = Data::get('areas');
		return isset($provinces[$provinceId]['city'][$cityId]['area'][$areaId]);
	}

	public static function getProvinceName($provinceId) {
		$provinces = Data::get('areas');
		return $provinces[$provinceId]['provinceName'];
	}

	public static function getCityName($provinceId, $cityId) {
		$provinces = Data::get('areas');
		$newCities = [
			1 => '北京',
			2 => '天津',
			9 => '上海',
			22 => '重庆',
		];
		if(isset($newCities[$provinceId])) {
			return $newCities[$provinceId];
		}
		return $provinces[$provinceId]['city'][$cityId]['cityName'];
	}
}