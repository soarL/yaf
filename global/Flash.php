<?php
use Yaf\Registry;

class Flash {
      private static $flashKey = '__flash__';

      private static function getSession() {
            return Registry::get('session');
      }
      
      public static function set($data) {
            return self::getSession()->set(self::$flashKey, $data);
      }

      public static function get() {
            $flashValue = self::getSession()->get(self::$flashKey);
            self::getSession()->del(self::$flashKey);
            return $flashValue;
      }

      public static function has() {
            return self::getSession()->has(self::$flashKey);
      }

      public static function error($info) {
            $data = [];
            $data['type'] = 'error';
            $data['info'] = $info;
            self::set($data);
      }

      public static function info($info) {
            $data = [];
            $data['type'] = 'info';
            $data['info'] = $info;
            self::set($data);
      }

      public static function success($info) {
            $data = [];
            $data['type'] = 'success';
            $data['info'] = $info;
            self::set($data);
      }

      public static function warning($info) {
            $data = [];
            $data['type'] = 'warning';
            $data['info'] = $info;
            self::set($data);
      }
}