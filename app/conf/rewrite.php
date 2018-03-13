<?php
return [
    'rewrite' => [
        'odds' => [
            'rule'=> 'odds',
            'route'=> 'odd/list',
        ],
        'new' => [
            'rule'=> 'newhand',
            'route'=> 'odd/new',
        ],
        /*'admin' => [
            'rule'=> 'admin',
            'route'=> 'admin/index/index',
        ],*/
    ],
    'regex' => [
        'odd' => [
            'rule'=> '#/odd/(\d{20}|\d{14}|XFJR\d{15})#',
            'route'=> 'odd/view',
            'params'=> ['num'],
        ],
        'oldOdd' => [
            'rule'=> '#/invest/a(\d{11}).html#',
            'route'=> 'invest/view',
            'params'=> ['num'],
        ],
        /*'about' => [
            'rule'=> '#/about/(.*).html#',
            'route'=> 'about/:page',
            'params'=> ['page'],
        ],*/
        /*'news' => [
            'rule'=> '#/news-(\d+).html#',
            'route'=> 'news/show',
            'params'=> ['id'],
        ],*/
    ],
];