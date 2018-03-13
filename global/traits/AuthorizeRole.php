<?php
namespace traits;

trait AuthorizeRole {

    public function users() {
        return $this->belongsToMany('models\User', 'auth_role_user');
    }

    public function perms() {
        return $this->belongsToMany('models\Permission', 'auth_permission_role');
    }

    public static function boot() {
        parent::boot();

        static::deleting(function($role) {
            if (!method_exists('models\Role', 'bootSoftDeletingTrait')) {
                $role->users()->sync([]);
                $role->perms()->sync([]);
            }

            return true;
        });
    }

    public function savePermissions($inputPermissions) {
        if (!empty($inputPermissions)) {
            $this->perms()->sync($inputPermissions);
        } else {
            $this->perms()->detach();
        }
    }

    public function attachPermission($permission) {
        if (is_object($permission)) {
            $permission = $permission->getKey();
        }

        if (is_array($permission)) {
            $permission = $permission['id'];
        }

        $this->perms()->attach($permission);
    }

    public function detachPermission($permission) {
        if (is_object($permission))
            $permission = $permission->getKey();

        if (is_array($permission))
            $permission = $permission['id'];

        $this->perms()->detach($permission);
    }

    public function attachPermissions($permissions) {
        foreach ($permissions as $permission) {
            $this->attachPermission($permission);
        }
    }

    public function detachPermissions($permissions) {
        foreach ($permissions as $permission) {
            $this->detachPermission($permission);
        }
    }
}