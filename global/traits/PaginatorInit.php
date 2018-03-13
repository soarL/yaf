<?php
namespace traits;

use Illuminate\Pagination\Paginator;
// use Illuminate\Pagination\BootstrapFourPresenter;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;

trait PaginatorInit {

    public function init() {
        parent::init();
        $this->paginatorInit();
    }

    private function paginatorInit() {
        Paginator::currentPageResolver(function() {
            $page = $this->getRequest()->getQuery('page');
            return $page;
        });
        Paginator::currentPathResolver(function() {
            return $this->getRequest()->getRequestUri();
        });
        /*Paginator::presenter(function($paginator) {
            return new BootstrapFourPresenter($paginator);
        });*/
    }

    private function paginate($builder, $queries, $pageSize=15, $total=999999) {
        $page = $this->getQuery('page', 1);
        $records = null;
        if($builder instanceof Builder) {
            $records = $builder->forPage($page, $pageSize)->get();
        } else {
            $records = $builder;
        }
        $paginator = new LengthAwarePaginator($records, $total, $pageSize, $page);
        $paginator->setPath($this->getRequest()->getRequestUri());
        $paginator->appends($queries->all());
        return $paginator;
    }
}