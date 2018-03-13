<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/18 0018
 * Time: 18:21
 */

namespace models;


use Illuminate\Database\Eloquent\Model;

class Xinghuo extends Model
{
    protected $table = 'xinghuo';
    public $timestamps = false;

    public static function addOne($user_id,$phone,$token,$userId)
    {
        $user = new self();
        $user->user_id = $user_id;
        $user->phone= $phone;
        $user->register_token = $token;
        $user->userId = $userId;
        $user->addtime = Date('Y-m-d H:i:s',time());
        return $user->save();

    }

    public static function findToken($phone)
    {
        return $token = self::where('phone',$phone)->orWhere('userId',$phone)->value('register_token');
    }

    public static function bind($userId,$user_id,$token,$phone,$user_name,$user_identity,$source)
    {
        $user = new self();
        $user->userId = $userId;
        $user->user_id = $user_id;
        $user->register_token = $token;
        $user->user_name = $user_name;
        $user->phone = $phone;
        $user->user_identity = $user_identity;
        $user->source = $source;
        return $user->save();

    }

    public static function getUserId()
    {
        return self::whereRaw('1=1')->lists('userId');
    }

    public static function findOne($user_id,$userId)
    {
        return $user = self::where('user_id',$user_id)->Where('userId',$userId)->get();
    }
}