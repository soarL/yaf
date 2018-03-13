<?php
use models\Gps;
use Illuminate\Database\Capsule\Manager as DB;

class GpsController extends Controller{

    public function bcxAction() {
        for($i = 0; $i<30; $i++){
            $url = 'http://apiweb.dkwgps.com/SNService.asmx/TerbyKey?Key=0D3B397E6D8D8943691189C09090F40C68572777&page='.$i.'&rowcount=200';
            $xml = file_get_contents($url);
            $list = $this->xmlToArray($xml);
            
            if(isset($list['Info'])) {
                DB::beginTransaction();
                foreach($list['Info'] as $v){
                    $chepai = '';
                    if(is_string($v['bz']) && preg_match('/[\x80-\xff]+[A-Z][0-9a-zA-Z]{5}/', $v['bz'])) {
                        $chepai = $v['bz'];
                    }
                    if(!$chepai && is_string($v['chepai']) && preg_match('/[\x80-\xff]+[A-Z][0-9a-zA-Z]{5}/', $v['chepai'])) {
                        $chepai = $v['chepai'];
                    }
                    $count = Gps::where('imei', $v['IMEI'])->count();
                    if($count>0){
                        Gps::where('imei', $v['IMEI'])->update([
                            'tname'=>$v['tname'], 
                            'gpstime'=>$v['gpstime'],
                            'addtime'=>$v['addtime'],
                            'endtime'=>$v['endtime'],
                            'chepai'=>$chepai,
                            'lon'=>$v['lon'],
                            'lat'=>$v['lat']
                        ]);
                    }else{
                        Gps::insert([
                            'imei'=>$v['IMEI'],
                            'tname'=>$v['tname'], 
                            'addtime'=>$v['addtime'],
                            'endtime'=>$v['endtime'],
                            'gpstime'=>$v['gpstime'],
                            'chepai'=>$chepai,
                            'lon'=>$v['lon'],
                            'lat'=>$v['lat']
                        ]);
                    }
                }
                DB::commit();
                $this->export('gps batch ' . $i . ' success');
            }
        }
        $this->export('bcx ok');
    }

    public function goocarAction() {
        $token = $this->getToken();
        $branchs = ['成都二部1','成都分部1','重庆分部1','福州二部1','福州三部1','福州一部1','贵阳分部1','海口二部1','海口分部1','海口找车','合肥分部1','昆明分部1','龙岩分部1','南安分部1','南昌分部1','南宁分部1','南平分部1','宁德分部1','莆田分部1','泉州分部1','三明分部1','三亚分部1','厦门分部1','铜仁分部1','遵义分部1', '湘潭分部1', '荆门分部1', '毕节分部1', '钦州分部1'];

        foreach($branchs as $branch) {
            $data = $this->getAllImei($branch, $token);

            DB::beginTransaction();
            foreach($data['data'] as $value){
                $imei = Gps::where('imei', $value['imei'])->first(['imei']);
                if($imei){
                    Gps::where('imei', $value['imei'])->update([
                        'gpstime'=>date("Y-m-d H:i:s", $value['gps_time']),
                        'lon'=>$value['lng'],
                        'lat'=>$value['lat']
                    ]);
                }else{
                    Gps::insert([
                        'imei'=>$value['imei'],
                        'gpstime'=>date("Y-m-d H:i:s", $value['gps_time']),
                        'lon'=>$value['lng'],
                        'lat'=>$value['lat']
                    ]);
                }
            }
            DB::commit();

            $data = '';
            $data = $this->getAllDrive($branch, $token);

            DB::beginTransaction();
            foreach($data['data'] as $val){
                Gps::where('imei', $val['imei'])->update([
                    'tname'=> $val['number'],
                    'addtime'=>date("Y-m-d H:i:s", $val['in_time']), 
                    'endtime'=>date("Y-m-d H:i:s", $val['out_time']), 
                    'chepai'=> $val['name']
                ]);
            }
            DB::commit();

            $this->export('gpsoo batch ' . $branch . ' success');
        }
        $this->export( 'goocar ok');
    }


    public function getToken() {
        $username = 'xwsd';
        $password = 'xwsDGpsss24';
        $url = 'http://api.gpsoo.net/1/auth/access_token';
        $time = time();
        $signature = md5(md5($password).$time);
        $url = 'http://api.gpsoo.net/1/auth/access_token?account='.$username.'&time='.$time.'&signature='.$signature;
        $result = file_get_contents($url);
        $row = json_decode($result, true);
        if(empty($row['ret'])) {
            return $row['access_token'];
        } else {
            return $row['msg'];
        }
    }

    public function getAllImei($branch, $token) {
        $url = 'http://api.gpsoo.net/1/account/monitor?access_token='.$token.'&map_type=BAIDU&target='.$branch.'&account=xwsd&time='.time();
        $result = file_get_contents($url);
        $list = json_decode($result, true);
        return $list;
    }

    public function getAllDrive($branch, $token) {
        $url = 'http://api.gpsoo.net/1/account/devinfo?target=' . $branch . '&account=xwsd&access_token=' . $token . '&time=' . time();
        $result = file_get_contents($url);
        $list = json_decode($result, true);
        return $list;
    }

    /**
     * xml转数组
     * @param type $xmlstring
     * @return type array
     */
    public function xmlToArray($xml) {
        $tmp = explode("\n", $xml);
        $xmlStr = '';
        foreach ($tmp as $val) {
            $xmlStr .= trim($val);
        }
        return json_decode(json_encode((array) simplexml_load_string($xmlStr)), true);
    }

}

