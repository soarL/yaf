<?php
namespace models;

use Illuminate\Database\Eloquent\Model;
use traits\AuthorizeRole;

class Role extends Model {
	use AuthorizeRole;

	const SUPER_ROLE = 'superman';

	protected $table = 'auth_roles';
}