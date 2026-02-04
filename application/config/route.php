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
        'user/role-permission' => 'user/rolePermission',
        
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
        'material/search' => 'material/search',
        'material/get-by-code/:code' => 'material/getByCode',
        
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
        'inventory/export-csv' => 'inventory/exportCsv',
        
        // 系统管理相关
        'record' => 'system/index',
        'record/system' => 'system/system',
        'record/backup' => 'system/backup',
        'record/clean-operation' => 'system/cleanOperation',
        'record/clean-system' => 'system/cleanSystem',
        'record/download/:filename' => 'system/download',
        'record/delete/:filename' => 'system/delete',
        'record/restore/:filename' => 'system/restore',
        
        // 入库历史相关
        'inbound-history' => 'inbound_history/index',
        'inbound-history/detail/:id' => 'inbound_history/detail',
        'inbound-history/edit/:id' => 'inbound_history/edit',
        'inbound-history/delete/:id' => 'inbound_history/delete',
        'inbound-history/search' => 'inbound_history/search',
        'inbound-history/export-csv' => 'inbound_history/exportCsv',
        
        // 出库历史相关
        'outbound-history' => 'outbound_history/index',
        'outbound-history/detail/:id' => 'outbound_history/detail',
        'outbound-history/edit/:id' => 'outbound_history/edit',
        'outbound-history/delete/:id' => 'outbound_history/delete',
        'outbound-history/search' => 'outbound_history/search',
        'outbound-history/export-csv' => 'outbound_history/exportCsv',
    ],
];
