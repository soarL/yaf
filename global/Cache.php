<?php
use factories\RedisFactory;

class Cache {
    const KEY_PRE = 'cache_';
    
    public static function get($key) {
        $redis = RedisFactory::create();
        $name = self::KEY_PRE . $key;
        return $redis->get($name);
    }

    public static function set($key, $value) {
        $redis = RedisFactory::create();
        $name = self::KEY_PRE . $key;
        return $redis->set($name, $value);
    }
}