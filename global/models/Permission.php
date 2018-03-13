<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use traits\AuthorizePermission;

class Permission extends Model {
	use AuthorizePermission;
	
	const PERM_MANAGER = 'manager';

	protected $table = 'auth_permissions';

    public function action() {
        return $this->belongsTo('models\AuthAction', 'act_id');
    }
}