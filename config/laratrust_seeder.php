<?php

return [
    'role_structure' => [
        'super_admin' => [
            'dashboard' => 'c,r,u,d',
            'task_import' => 'c,r,u,d',
            'task' => 'c,r,u,d',
            'board' => 'c,r,u,d',
            'column' => 'c,r,u,d',
            'report' => 'c,r,u,d',
            'board_assignee' => 'c,r,u,d',
        ],
        'admin' => [
            'dashboard' => 'c,r,u,d',
            'task_import' => 'c,r,u,d',
            'report' => 'c,r,u,d',
            'task' => 'c,r,u,d',
            'board' => 'c,r,u,d',
            'column' => 'c,r,u,d',
            'board_assignee' => 'c,r,u,d',
        ],
        'user' => [
            'task' => 'c,r,u,d',
            'column' => 'r,u',
            'board' => 'r',
        ]
    ],
    'permission_structure' => [
        'cru_user' => [
            'profile' => 'c,r,u'
        ],
    ],
    'permissions_map' => [
        'c' => 'create',
        'r' => 'read',
        'u' => 'update',
        'd' => 'delete'
    ]

];