<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/22 0022
 * Time: 15:13
 */

namespace models;

use Illuminate\Database\Eloquent\Model;

class BillionPrize extends Model
{
    protected $table='act_billion_user_prize';

    public $timestamps = false;


    /**
     * 添加用户资金日志
     * @param array $data 信息
     * @param Model $user 用户
     * @return boolean 是否添加成功
     */
    public static function addOne($data, $user) {
        $billionPrize = new self();
        $billionPrize->userId = $user->userId;
        $billionPrize->prizeId = $data['prizeId'];
        $billionPrize->addtime = date('Y-m-d H:i:s');
        return $billionPrize->save();
    }

}