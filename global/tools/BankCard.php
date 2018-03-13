<?php
namespace tools;
use \Data;
class BankCard {

    public static function getBankCName($bankName){
        $banks = Data::get('bankName');
        return isset($banks[$bankName])?$banks[$bankName]:'无法识别的银行卡';
    }
    
    public static function getBinInfo($card) {
        $list = Data::get('bankICList');

        $prefix = substr($card, 0, 8);
        if (isset($list[$prefix])) {
            return $list[$prefix];
        } 

        $prefix = substr($card, 0, 6);
        if (isset($list[$prefix])) { 
            return $list[$prefix];
        }

        $prefix = substr($card, 0, 5);
        if (isset($list[$prefix])) {
            return $list[$prefix];
        }

        $prefix = substr($card, 0, 4);
        if (isset($list[$prefix])) {
            return $list[$prefix];
        }

        return '';
    }
}