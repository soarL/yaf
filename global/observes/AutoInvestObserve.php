<?php
namespace observes;
use \models\UserLog;
class AutoInvestObserve {
    public function saved($model) {
    	$columns = ['autostatus', 'investEgisMoney', 'investMoneyUper', 'investMoneyLower', 'staystatus', 'moneyType', 'typesJson', 'mode', 'lottery_id', 'status', 'total', 'successMoney', 'types'];
    	UserLog::saveModel('auto', $model, $columns);
    }

}