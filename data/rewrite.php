<?php
return [
    'rewrite' => [
        'odds' => [
            'rule'=> 'odds',
            'route'=> 'odd/list',
        ],
        /*'admin' => [
            'rule'=> 'admin',
            'route'=> 'admin/index/index',
        ],*/
    ],
    'regex' => [
        'odd' => [
            'rule'=> '#/odd/(\d{20}|\d{14})#',
            'route'=> 'odd/view',
            'params'=> ['num'],
        ],
        'oldOdd' => [
            'rule'=> '#/invest/a(\d{11}).html#',
            'route'=> 'invest/view',
            'params'=> ['num'],
        ],
        'spreadRegister' => [
            'rule'=> '#/register/(\w{16})#',
            'route'=> 'register/index',
            'params'=> ['spread'],
        ],
        /*'news' => [
            'rule'=> '#/news-(\d+).html#',
            'route'=> 'news/show',
            'params'=> ['id'],
        ],*/
    ],
];