<?php
/**
 * GpsController
 * GPS控制器
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
use Illuminate\Database\Capsule\Manager as DB;
use models\Gps;
use models\Odd;
use models\User;
use helpers\StringHelper;

class GpsController extends Controller{

    public function gpsLocationAction()
    {
        $odd = $this->getQuery('odd', '');
        $gps = Gps::where('oddNumber', $odd)->first();
        $value = '';
        if(!$gps){
            $value = 'GPS定位正在关联中...';
            $this->display('gps', ['value'=>$value]);
        }else{
            $value = '';
            $this->display('gps', ['value'=>$value]);
        }
    }

    public function ajaxGpsAction()
    {
        $oddNumber = $this->getQuery('oddNumber', '');
        if($oddNumber){
            $odd = Gps::where('oddNumber', $oddNumber)->first();
            $userId = Odd::where('oddNumber', $oddNumber)->first(['userId']);
            $userName = User::where('userId', $userId['userId'])->first(['username']);
            $array['longitude'] = $odd['lon'];
            $array['latitude'] = $odd['lat'];
            if($userName){
                $array['title'] = "用户名:". StringHelper::msubstr($userName['username'],0,1);
            }else{
                $array['title'] = "用户名:null";
            }
            $msg = explode(' ', '车辆名:'.$odd['chepai']);
            $array['msg'] = $msg['0'];
            echo json_encode($array);
        }else{
            exit('no data');
        }
    }
}