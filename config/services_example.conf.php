<?php
    return [
        [
            'type'       => 'http',
            'title'      => 'Hello World',
            'params'     => [
                'protocol'  => 'https',
                'userAgent' => 'monitor/1.0',
                'host'      => 'google.com',
                'ip'        => '127.0.0.1',
                'port'      => '443',
                'referer'   => 'yandex.ru',
                'route'     => '/personal/pages',
                'method'    => 'post',
                'data'      => [
                    'login'    => 'some_login',
                    'password' => 'mypassword'
                ],
            ],
            'log'        => true,
            'validators' => [
                'response_codes' => [200],
                'execution_time' => 10
            ]
        ],
    ];