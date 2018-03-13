<?php
namespace traits;

trait AuthorizePermission {

    public function roles() {
        return $this->belongsToMany('models\Role', 'auth_permission_role');
    }

    public static function boot() {
        parent::boot();

        static::deleting(function($permission) {
            if (!method_exists('models\Permission', 'bootSoftDeletingTrait')) {
                $permission->roles()->sync([]);
            }

            return true;
        });
    }

}