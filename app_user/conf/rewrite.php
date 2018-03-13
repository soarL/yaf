<?php
return [
    'rewrite' => [
        'admin' => [
            'rule'=> 'admin$',
            'route'=> 'admin/index/index',
        ],
    ],
    'regex' => [
        'spreadRegister' => [
            'rule'=> '#/register/(\w{16})#',
            'route'=> 'register/index',
            'params'=> ['spread'],
        ],
        'oldOdd' => [
            'rule'=> '#/invest/a(\d{11}).html#',
            'route'=> 'invest/view',
            'params'=> ['num'],
        ],
    ],
];