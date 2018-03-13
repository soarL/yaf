<?php
namespace traits;

trait ScopeModel {
    public function scopeWithModel($query, $relation, array $columns) {
        return $query->with([$relation => function ($query) use ($columns){
            $query->select($columns);
        }]);
    }
}