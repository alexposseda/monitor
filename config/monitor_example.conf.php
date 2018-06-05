<?php
    return [
        'storage'   => dirname(__DIR__) . '/storage/',
        'version'   => '1.0',
        'messenger' => [
            'login'            => '',
            'password'         => '',
            'host'             => '',
            'port'             => '',
            'security'         => '',
            'message_template' => dirname(__DIR__) . '/templates/email/service.tpl.php',
            'message_layout'   => dirname(__DIR__) . '/templates/email/layout.tpl.php',
        ],
        'report'    => [
            'recipients' => [
                '',
            ],
            'subject'    => 'Monitor',
            'from'       => ['System Monitor' => 'monitor@system.com']
        ],
        'log'       => [
            'dir' => dirname(__DIR__) . '/logs/',
        ]
    ];