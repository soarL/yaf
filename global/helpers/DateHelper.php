<?php
namespace helpers;
/**
 * DateHelper
 * 时间帮助类，用于时间显示、时间计算。
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class DateHelper {
	/**
	 * 获取当前月的起始时间和结束时间
	 * 如：['2015-11-01 00:00:00', '2015-11-30 00:00:00']
	 * @return array    当前月的起始时间和结束时间
	 */
	public static function getCurrentMonth($date) {
		$firstDay = date('Y-m-01',strtotime($date));
     	$lastDay = date('Y-m-d',strtotime("$firstDay +1 month -1 day"));
     	$firstDay .= ' 00:00:00';
     	$lastDay .= ' 23:59:59';
     	return [$firstDay, $lastDay];
	}

	/**
	 * 获取上个月的起始时间和结束时间
	 * 如：['2015-12-01 00:00:00', '2015-12-31 00:00:00']
	 * @return array    上个月的起始时间和结束时间
	 */
	public static function getLastMonth() {
		$timestamp = time();
		$month = intval(date('m'));
		$year = intval(date('Y'));
		$firstDay = '';
		$lastDay = '';
		if($month==1) {
			$lastYear = $year-1;
			$firstDay = $lastYear.'-12-01';
     		$lastDay = $lastYear.'-12-31';
		} else {
			$firstDay = date('Y-m-01',strtotime(date('Y',$timestamp).'-'.(date('m',$timestamp)-1).'-01'));
     		$lastDay = date('Y-m-d',strtotime("$firstDay +1 month -1 day"));
		}
		$firstDay .= ' 00:00:00';
     	$lastDay .= ' 23:59:59';
		return [$firstDay, $lastDay];
	}

	/**
	 * 获取当前季度的起始时间和结束时间
	 * 如：['2015-10-01 00:00:00', '2015-12-31 00:00:00']
	 * @return array    当前季度的起始时间和结束时间
	 */
	public static function getCurrentSeason() {
		$season = ceil((date('n'))/3);//当月是第几季度
    	$firstDay = date('Y-m-d H:i:s', mktime(0, 0, 0,$season*3-3+1,1,date('Y')));
    	$lastDay = date('Y-m-d H:i:s', mktime(23,59,59,$season*3,date('t',mktime(0, 0, 0,$season*3,1,date('Y'))),date('Y')));
		return [$firstDay, $lastDay];
	}
	
	/**
	 * 获取下个季度的起始时间和结束时间
	 * 如：['2016-01-01 00:00:00', '2016-03-31 00:00:00']
	 * @return array    下个季度的起始时间和结束时间
	 */
	public static function getLastSeason() {
		$season = ceil((date('n'))/3)-1;//上季度是第几季度
		if($season==4) {
			$year = intval(date('Y'));
			$lastYear = $year-1;
			$firstDay = $lastYear.'-10-01 00:00:00';
     		$lastDay = $lastYear.'-12-31 23:59:59';
		} else {
		    $firstDay = date('Y-m-d H:i:s', mktime(0, 0, 0,$season*3-3+1,1,date('Y')));
		    $firstDay = date('Y-m-d H:i:s', mktime(23,59,59,$season*3,date('t',mktime(0, 0 , 0,$season*3,1,date('Y'))),date('Y')));
		}
	    return [$firstDay, $lastDay];
	}

	/**
	 * 获取一个中文显示的时间
	 * 如：3天2时32分39秒
	 * @param  int    	$stamp         秒数
	 * @return string   中文显示的时间字符串
	 */
	public static function getDaySpecial($stamp) {
		$d = intval($stamp/(24*60*60));
		$h = intval(($stamp-$d*(24*60*60))/(60*60));
		$m = intval(($stamp-$d*(24*60*60)-$h*(60*60))/60);
		$s = $stamp-$d*(24*60*60)-$h*(60*60)-$m*60;
		$dStr = ($d==0?'':$d.'天');
		$hStr = ($h==0?'':$h.'时');
		$mStr = ($m==0?'':$m.'分');
		$sStr = ($s==0?'':$s.'秒');
		return $dStr.$hStr.$mStr.$sStr;
	}

	/**
	 * 获取时间距现在多久
	 * 例如：3秒前(后)、7分钟前(后)、1小时前(后)、2天前(后)、3月前(后)、1年前(后)
	 * @param  int    $time         时间戳
	 * @return string               距现在多久
	 */
	public static function getTimeDistance($time) {
		$distance = time() - $time;
		$suffixStr = '后';
		if($distance>=0) {
			$suffixStr = '前';
		}
		$distance = abs($distance);
		$str = '';
		if($distance<60) {
			$str = $distance . '秒' . $suffixStr;
		} else if($distance>=60 && $distance<60*60) {
			$str = intval($distance/60) . '分钟' . $suffixStr;
		} else if($distance>=60*60 && $distance<60*60*24) {
			$str = intval($distance/(60*60)) . '小时' . $suffixStr;
		} else if($distance>=60*60*24 && $distance<60*60*24*30) {
			$str = intval($distance/(60*60*24)) . '天' . $suffixStr;
		} else if($distance>=60*60*24*30 && $distance<60*60*24*30*12) {
			$str = intval($distance/(60*60*24*30)) . '个月' . $suffixStr;
		} else if($distance>=60*60*24*30*12) {
			$str = intval($distance/(60*60*24*30*12)) . '年' . $suffixStr;
		}
		return $str;
	}

	/**
	 * 根据时间获取星期
	 * @param  string $time 表示时间的字符串
	 * @return string       星期几
	 */
	public static function getWeekByTime($time) {
		$weekArray = ['星期日','星期一','星期二','星期三','星期四','星期五','星期六'];
		$week = date('w', $time);
		return $weekArray[$week];
	}

	/**
	 * 根据阿拉伯数字月份获取中文月份
	 * @param  string $month 阿拉伯数字月份
	 * @return string        中文月份
	 */
	public static function getChineseMonth($month) {
		$monthArray = ['1'=>'一', '2'=>'二', '3'=>'三', '4'=>'四', '5'=>'五', '6'=>'六', '7'=>'七', '8'=>'八', '9'=>'九', '10'=>'十', '11'=>'十一', '12'=>'十二'];
		return $monthArray[$month];
	}

	/**
	 * 判断两个时间是否是同一天
	 * @param  int  $day1  时间戳
	 * @param  int  $day2  时间戳
	 * @return boolean     是否同一天
	 */
	public static function isSameDay($day1, $day2) {
		$time1 = date('Y-m-d', $day1);
		$time2 = date('Y-m-d', $day2);
		if($time1==$time2) {
			return true;
		}
		return false;
	}

	/**
	 * 判断是否是昨天
	 * @param  int  $day  时间戳
	 * @return boolean    是否是昨天
	 */
	public static function isYestoday($day) {
		$time1 = date('Y-m-d 00:00:00', $day);
		$time2 = date('Y-m-d');
		$time1 = date('Y-m-d', (strtotime($time1) + 24*60*60));
		if($time1==$time2) {
			return true;
		}
		return false;
	}

	/**
	 * 获取上周的起始时间和结束时间
	 * 如：['2015-12-01 00:00:00', '2015-12-07 00:00:00']
	 * @return array    上周的起始时间和结束时间
	 */
	public static function getLastWeek() {
		$lastMonday = '';
		$lastSunday = '';
		if (date('l',time()) == 'Monday') {
			$lastMonday = date('Y-m-d',strtotime('last monday')) . ' 00:00:00';
		} else {
			$lastMonday = date('Y-m-d',strtotime('-1 week last monday')) . ' 00:00:00';
		}
	    $lastSunday = date('Y-m-d',strtotime('last sunday')) . ' 23:59:59';
	    return [$lastMonday, $lastSunday];
    }

    /**
	 * 获取间隔天数
	 * @param string $stime 开始时间
	 * @param string $etime 结束时间
	 * @return integer    间隔天数
	 */
    public static function getIntervalDay($stime, $etime) {
    	$etime = strtotime(substr($etime, 0, 10));
        $stime = strtotime(substr($stime, 0, 10));
        $day = sprintf("%d", round(($etime - $stime) / 60 / 60 / 24));
        return abs($day);
    }

    
    public static function toChineseNumber($money){
      $money = round($money,2);
      $cnynums = array("零","壹","贰","叁","肆","伍","陆","柒","捌","玖"); 
      $cnyunits = array("圆","角","分");
      $cnygrees = array("拾","佰","仟","万","拾","佰","仟","亿"); 
      @list($int,$dec) = explode(".",$money,2);
      $dec = array_filter(array($dec[1],$dec[0])); 
      $ret = array_merge($dec,array(implode("",self::cnyMapUnit(str_split($int),$cnygrees)),"")); 
      $ret = implode("",array_reverse(self::cnyMapUnit($ret,$cnyunits))); 
      return str_replace(array_keys($cnynums),$cnynums,$ret); 
    }
    public static function cnyMapUnit($list,$units) { 
      $ul=count($units); 
      $xs=array(); 
      foreach (array_reverse($list) as $x) { 
        $l=count($xs); 
        if ($x!="0" || !($l%4)) 
          @$n=($x=='0'?'':$x).($units[($l-1)%$ul]); 
        else @$n=is_numeric($xs[0][0])?$x:''; 
     	array_unshift($xs,$n); 
     } 
     return $xs; 
    }
}