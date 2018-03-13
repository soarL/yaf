<?php
use models\User;
use models\Odd;
use models\OddInfo;
use models\AutoInvest;
use models\BakAutoInvest;
use models\MoneyLog;
use models\Queue;
use models\NewMoneyLog;
use helpers\StringHelper;
use tools\Redis;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * MigrateController
 * 存管迁移控制器
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class MigrateController extends Controller {


    public $count = 0;

    public function runAction() {
        $this->initQueue();
    }

    public function initQueue() {
        Queue::orderBy('location', 'asc')->chunk(200, function ($queues) {
            $key = Redis::getKey('autoInvestQueue');
            foreach ($queues as $queue) {
                Redis::lPush($key, $queue->userId);
            }
        });
    }
    
    /**
     * 自动投标设置表typesJson转化为types
     */
    public function autoTableChange() {
        AutoInvest::chunk(200, function ($autoInvests) {
            $records = [];
            $num = 0;
            foreach ($autoInvests as $autoInvest) {
                $num ++;
                $items = null;
                if($autoInvest->typesJson) {
                    $items = json_decode($autoInvest->typesJson, true);
                }
                $types = [];
                if($items) {
                    foreach ($items as $item) {
                        $types[] = $item['id'];
                    }
                }
                $typesStr = '';
                if(count($types)>0) {
                    $typesStr = '#'.implode('#', $types).'#';
                    $count = AutoInvest::where('userId', $autoInvest->userId)->update(['types'=>$typesStr]);
                    $this->export('finish '.$count);
                }
            }
        });
    }

    /**
     * 标的work_odd分离成work_odd 和 work_oddinfo
     */
    public function oddSeparate() {
        $columns = ['oddNumber', 'oddExteriorPhotos', 'oddPropertyPhotos', 'bankCreditReport', 'otherPhotos', 'oddLoanRemark', 'oddLoanControlList', 'oddLoanControl', 'controlPhotos', 'validateCarPhotos', 'contractVideoUrl'];
        Odd::select($columns)->chunk(200, function ($odds) {
            $records = [];
            $num = 0;
            foreach ($odds as $odd) {
                $num ++;
                $records[] = [
                    'oddNumber' => $odd->oddNumber,
                    'oddExteriorPhotos' => $odd->oddExteriorPhotos,
                    'oddPropertyPhotos' => $odd->oddPropertyPhotos,
                    'bankCreditReport' => $odd->bankCreditReport,
                    'otherPhotos' => $odd->otherPhotos,
                    'oddLoanRemark' => $odd->oddLoanRemark,
                    'oddLoanControlList' => $odd->oddLoanControlList,
                    'oddLoanControl' => $odd->oddLoanControl,
                    'controlPhotos' => $odd->controlPhotos,
                    'validateCarPhotos' => $odd->validateCarPhotos,
                    'contractVideoUrl' => $odd->contractVideoUrl,
                ];
            }
            if(OddInfo::insert($records)) {
                $this->export('分离数据:'.$num.'条!');
            } else {
                $this->export('处理失败!');
            }
        });
    }

    /**
     * 用户资金日志表转化
     */
    public function moneyLogChange() {
        MoneyLog::chunk(200, function ($logs) {
            $records = [];
            $num = 0;
            foreach ($logs as $log) {
                $num ++;
                $records[] = [
                    'type' => $log->type,
                    'mode' => $log->mode,
                    'mvalue' => $log->mvalue,
                    'remark' => $log->remark,
                    'time' => $log->time,
                    'userId' => $log->userId,
                ];
            }
            if(NewMoneyLog::insert($records)) {
                $this->export('处理数据:'.$num.'条!');
            } else {
                $this->export('处理失败!');
            }
        });
    }

    public function saveUsersAction() {
        $columns = ['custody_id', 'userId', 'phone', 'username'];
        $this->count = 0;
        $list = User::select($columns)->chunk(200, function($users) {
            $count = 0;
            foreach ($users as $user) {
                Redis::setUser([
                    'userId'=>$user->userId,
                    'phone'=>$user->phone,
                    'username'=>$user->username,
                    'custody_id'=>$user->custody_id,
                ]);
                $this->count ++;
                $count ++;
            }
            $this->export('success '.$count.' users!');
        });
        $this->export('total success '.$this->count.' users!');
    }

    public function moveUsersAction() {
        $bfusers = ['18850400209'];
        $phone = ['18779970815'];
        $columns = ['cardnum', 'name', 'userId', 'phone'];
        $date = date('Ymd');
        $this->count = 0;
        $list = User::select($columns)->where('userId', '<>', '10022')->where('userId', '<>', '1508000')
            ->whereRaw('LENGTH(cardnum)=18')
            ->where('thirdAccountStatus', '1')
            ->whereNotIn('username', $bfusers)
            ->whereNotIn('phone', $phones)
            ->chunk(200, function($users) use($date){
            $count = 0;
            foreach ($users as $user) {
                $cardType = '01';
                if(strlen($user->cardnum)==15) {
                    $cardType = '02';
                }
                $sex = StringHelper::getSexByCardnum($user->cardnum);
                $sexNum = 1;
                if($sex=='women') {
                    $sexNum = 2;
                }
                $str = $this->appendStr($user->cardnum, 18)         // IDNO 18
                    . $cardType                                     // IDTYPE 2
                    . $this->appendStr($user->name, 60)             // NAME 60
                    . $this->appendStr($sexNum, 1)                  // GEN 1
                    . $this->appendStr($user->phone, 12)            // MOPHONE 12
                    . '0'                                           // ACCTYPE 1
                    . $this->appendStr('', 40)                      // EMAIL 40
                    . $this->appendStr($user->userId, 60)           // APPID 60
                    . $this->appendStr('', 9)                       // BUSID 9
                    . $this->appendStr('', 30)                      // TAXID 30
                    . $this->appendStr('', 20)                      // ADNO 20
                    . '2'                                           // ACC-TYPE 1
                    . $this->appendStr('', 2)                       // FUCOMCODE 2
                    . $this->appendStr('', 100)                     // INFO 100
                    . $this->appendStr('', 42)                      // CACCOUNT 42
                    . $this->appendStr('', 18)                      // BUSID 18
                    . $this->appendStr('', 17);                     // REVERS 17
                $this->count ++;
                $num = 1;
                $num += intval($this->count/50000);
                $batchNo = str_repeat('0', 6-strlen($num)) . $num;
                file_put_contents('/tmp/custody-post/3005-APPZX0083-'.$batchNo.'-'.$date, $str.PHP_EOL, FILE_APPEND);
                $count ++;
            }
            $this->export('success '.$count.' users!');
        });
        $this->export('total success '.$this->count.' users!');
    }

    public function handleUserFileAction() {
        $filePath = APP_PATH.'/public/uploads/custody-res/3005-APPZX0083RES-000003-20170809';
        $items = [
            'CARDNBR'=>19, 
            'IDNO'=>18, 
            'IDTYPE'=>2, 
            'FLAG'=>1, 
            'ERRCODE'=>3, 
            'NAME'=>60, 
            'ACCTYPE'=>1, 
            'APPID'=>60, 
            'MOPHONE'=>12, 
            'INFO'=>100, 
            'REVERS'=>88
        ];
        $rows = $this->parseFile($filePath, $items);
        foreach ($rows as $row) {
            if($row['FLAG']=='S' || $row['FLAG']=='N') {
                $count = User::where('userId', $row['APPID'])->update(['custody_id'=>$row['CARDNBR']]);
                if($count==1) {
                    $this->export('[SUCCESS]用户'.$row['APPID'].'开户成功！');
                } else {
                    $this->export('[WARNING]用户'.$row['APPID'].'开户成功，更新失败！');
                }
            } else {
                $this->export('[ERROR]用户'.$row['userId'].'开户失败，失败原因['.$row['ERRCODE'].']');
            }
        }
    }

    public function moveAutoBidAction() {
        $date = date('Ymd');
        $this->count = 0;
        $list = AutoInvest::select(['userId', 'autostatus'])->with([
                'user'=>function($q) {$q->select(['userId', 'custody_id', 'auto_bid_auth']);}
            ])->where('autostatus', '1')
            ->chunk(200, function($autos) {
            $count = 0;
            $batchNo = '000001';
            $date = date('Ymd');
            foreach ($autos as $auto) {
                $user = $auto->user;
                if($user->custody_id=='' || $user->auto_bid_auth!='') {
                    continue;
                }
                $time = date('His');
                $seq = str_repeat('0', 6-strlen($this->count + 1)) . ($this->count + 1);
                $str = $this->appendStr('3005', 4)
                    . $this->appendStr($batchNo, 6)
                    . $this->appendStr($user->custody_id, 19)
                    . $this->appendStr('MJ', 4)
                    . $this->appendStr('1', 1)
                    . $this->appendStr('000174000020170701'.$time.$seq, 40)
                    . $this->appendStr('20170701', 8)
                    . $this->appendStr($time, 6)
                    . $this->appendStr('', 100)
                    . $this->appendStr('', 100);
                $this->count ++;
                $count ++;
                file_put_contents('/tmp/custody-post/3005-BB-SIGTRAN-'.$batchNo.'-'.$date, $str.PHP_EOL, FILE_APPEND);
            }
            $this->export('success '.$count.' users!');
        });
        $this->export('total success '.$this->count.' users!');
    }

    public function handleAutoBidFileAction() {
        $filePath = APP_PATH.'/public/uploads/custody-res/3005-MJ-SIGRES-000001-20170817';
        $items = [
            'BANK' => 4,
            'BATCH' => 6,
            'CARDNNBR' => 19,
            'FUISSUER' => 4,
            'SIG_TYPE' => 1,
            'SERI_NO' => 40,
            'SIG_DATE' => 8,
            'SIG_TIME' => 6,
            'RSPCODE' => 2,
            'RESERVED' => 100,
            'TRDRESV' => 100,
        ];
        $rows = $this->parseFile($filePath, $items);
        foreach ($rows as $row) {
            if($row['RSPCODE']=='00') {
                $orderId = str_replace('0001740000', '', $row['SERI_NO']);
                $count = User::where('custody_id', $row['CARDNNBR'])->where('auto_bid_auth', '')->update(['auto_bid_auth'=>$orderId]);
                if($count==1) {
                    $this->export('[SUCCESS]用户'.$row['CARDNNBR'].'签约成功['.$orderId.']！');
                } else {
                    $this->export('[WARNING]用户'.$row['CARDNNBR'].'签约成功['.$orderId.']，更新失败！');
                }
            } else {
                $this->export('[ERROR]用户'.$row['CARDNNBR'].'签约失败，失败原因['.$row['RSPCODE'].']');
            }
        }
    }

    public function parseFile($filePath, $items) {
        $file = fopen($filePath, 'r');
        $rows = [];
        while(!feof($file)) {
            $row = [];
            $content = fgets($file);
            foreach ($items as $key => $val) {
                $res = $this->popStr($content, $val);
                $row[$key] = $res[0];
                $content = $res[1];
            }
            $rows[] = $row;
        }
        fclose($file);
        return $rows;
    }
    
    private function popStr($str, $length) {
        $sub = substr($str, 0, $length);
        $sub = iconv('gbk', 'utf-8', trim($sub));
        $less = substr($str, $length);
        return [$sub, $less];
    }

    private function appendStr($str, $length, $type='right', $s=' ') {
        $newStr = iconv('utf-8', 'gbk', $str);
        $len = strlen($newStr);
        $repeat = str_repeat($s, $length-$len);
        if($type=='left') {
            return $repeat . $newStr;
        }
        return $newStr . $repeat;
    }

    private function dnum($num, $f=2) {
        $result = intval($num * pow(10, $f));
        if($result==0) {
            return '0' . str_repeat('0', $f);
        } else {
            return $result;
        }
    }
}
