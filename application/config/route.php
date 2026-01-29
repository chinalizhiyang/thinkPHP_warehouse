<?php
return [
    // 路由规则
    'rule' => [
        // 首页
        '/' => 'index/index',
        
        // 用户相关
        'login' => 'user/login',
        'logout' => 'user/logout',
        'user/profile' => 'user/profile',
        'user' => 'user/index',
        'user/add' => 'user/add',
        'user/edit/:id' => 'user/edit',
        'user/delete/:id' => 'user/delete',
        
        // 角色相关
        'role' => 'role/index',
        'role/add' => 'role/add',
        'role/edit/:id' => 'role/edit',
        'role/delete/:id' => 'role/delete',
        'role/assign/:id' => 'role/assign',
        
        // 物料相关
        'material' => 'material/index',
        'material/add' => 'material/add',
        'material/edit/:id' => 'material/edit',
        'material/delete/:id' => 'material/delete',
        'material/category' => 'material/category',
        
        // 入库相关
        'inbound' => 'inbound/index',
        'inbound/add' => 'inbound/add',
        'inbound/edit/:id' => 'inbound/edit',
        'inbound/delete/:id' => 'inbound/delete',
        
        // 出库相关
        'outbound' => 'outbound/index',
        'outbound/add' => 'outbound/add',
        'outbound/edit/:id' => 'outbound/edit',
        'outbound/delete/:id' => 'outbound/delete',
        
        // 库存相关
        'inventory' => 'inventory/index',
        'inventory/check' => 'inventory/check',
        'inventory/report' => 'inventory/report',
        
        // 记录相关
        'record' => 'record/index',
        'record/system' => 'record/system',
        'record/backup' => 'record/backup',
        
        // 入库历史相关
        'inbound-history' => 'inbound_history/index',
        'inbound-history/detail/:id' => 'inbound_history/detail',
        'inbound-history/search' => 'inbound_history/search',
        
        // 出库历史相关
        'outbound-history' => 'outbound_history/index',
        'outbound-history/detail/:id' => 'outbound_history/detail',
        'outbound-history/search' => 'outbound_history/search',
    ],
];
