<?php
namespace models;

use Illuminate\Database\Eloquent\Model;

/**
 * UserFriend|model类
 * 
 * @author elf <360197197@qq.com>
 * @version 1.0
 */
class UserFriend extends Model {

	protected $table = 'user_friend';

	public $timestamps = false;

    protected $primaryKey = 'friend';

	public static function getFriendRecursive($userId) {
		$friends = [];
		$friends_one = self::where('userId', $userId)->get();
		$in_one = [];
		foreach ($friends_one as $friend_one) {
			$in_one[] = $friend_one->friend;
			$friends[] = ['friend'=>$friend_one['friend'], 'level'=>1, 'time'=>$friend_one['time']];
		}
		$friends_two = [];
		if(count($in_one)>0) {
			$friends_two = self::whereIn('userId', $in_one)->get();
		}
		$in_two = [];
		foreach ($friends_two as $friend_two) {
			$in_two[] = $friend_two->friend;
			$friends[] = ['friend'=>$friend_two['friend'], 'level'=>2, 'time'=>$friend_two['time']];
		}
		$friends_three = [];
		if(count($in_two)>0) {
			$friends_three = self::whereIn('userId', $in_two)->get();
		}
		foreach ($friends_three as $friend_three) {
			$friends[] = ['friend'=>$friend_three['friend'], 'level'=>3, 'time'=>$friend_three['time']];
		}
		return $friends;
	}

	public static function addOne($userId, $friend) {
		$userFriend = new self();
		$userFriend->userId = $userId;
		$userFriend->friend = $friend;
		$userFriend->time = date('Y-m-d H:i:s', time());
		return $userFriend->save();
	}
}