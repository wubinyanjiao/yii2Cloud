<?php

return [
    'adminEmail' => 'admin@example.com',
    'pageSize' => [
        'manage' => 10,
        'user'   => 10,
        'product' => 10,
        'shift' => 10,
        'order' => 10,
        'default'=>20

    ],
    'defaultValue' => [
        'avatar' => 'assets/admin/img/contact-img.png',
    ],
    'defaultHead'=>env('STORAGE_HOST_INFO').'web/user_default.jpg',
    'express' => [
        1 => '中通快递',
        2 => '顺丰快递',
    ],
    'expressPrice' => [
        1 => 15,
        2 => 20,
    ],

    'publicRest' => [
        'xajdyfyyxb' => 2,
        'xhyy'   => 3,
    ],


    'templateId'=>['xajdyfyyxb'=>['default'=>'qpnHCR1Ks24JZ8_zhRIcqnpRzxnOUyoG6ZglJHEsF58'],
                   'heliteq'=>['default'=>'-pGxtxw8rv7OLXxnFBkMOaOOCxeOm2PljXVRYAriFVs'],
    
    ],
    'customerArr'=>['xajdyfyyxb','heliteq'],
    'work_time_late'=>10,
    'kehuImage'=>[
                    'default'=>[
                            'index'=>env('STORAGE_HOST_INFO').'web/logo_heliteq_index.jpg',
                            'logo'=>env('STORAGE_HOST_INFO').'web/logo_heliteq.png',
                            'small'=>env('STORAGE_HOST_INFO').'web/logo_heliteq_small.png',    
                           ],
                    'xajdyfyyxb'=>[
                            'index'=>env('STORAGE_HOST_INFO').'web/logo_xajdyfyyxb_index.jpg',
                            'logo'=>env('STORAGE_HOST_INFO').'web/logo_xajdyfyyxb.png',
                            'small'=>env('STORAGE_HOST_INFO').'web/logo_xajdyfyyxb_small.png',
                           ],
                    'heliteq'=>[
                            'index'=>env('STORAGE_HOST_INFO').'web/logo_heliteq_index.jpg',
                            'logo'=>env('STORAGE_HOST_INFO').'web/logo_heliteq.png',
                            'small'=>env('STORAGE_HOST_INFO').'web/logo_heliteq_small.png', 
                        ]


    ],
    'performanceBath'=>['1'=>'月度','2'=>'年度'],
    'performanceStatus'=>['0'=>'未下发','10'=>'已下发未上报','11'=>'已下发已上报','1'=>'确认归档'],
];
