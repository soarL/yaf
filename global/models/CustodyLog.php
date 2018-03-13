<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

class CustodyLog extends Model {

    protected $table = 'custody_logs';

    public $timestamps = false;

    public function user() {
        return $this->belongsTo('models\User', 'cardnbr', 'custody_id');
    }
}