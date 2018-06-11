<?php

return [    
    //
    'pwd_enc_pre' => '',
    'pwd_enc_post' => '',
    
    //
    'group' => [
        'member' => [
            'login' => 'official.member.login',
            'logout' => 'official.member.logout',
        ],
        'admin' => [
            'login' => 'backend.login',
            'logout' => 'backend.login.logout',
            'super' => [
                'id' => 0,
                'account' => 'manager',
                'password' => '',
                'name' => 'Super Admin',
                'title' => 'Super Admin',
            ],
        ]
    ],
];
