<?php
namespace SearchHistory;

return  [
    'api_adapters' => [
        'invokables' => [
            'search_histories' => Api\Adapter\SearchHistoryAdapter::class,
        ],
    ],
    'entity_manager' => [
        'mapping_classes_paths' => [
            dirname(__DIR__) . '/src/Entity',
        ],
        'proxy_paths' => [
            dirname(__DIR__) . '/data/doctrine-proxies',
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    'controllers' => [
        'invokables' => [
            'SearchHistory\Controller\Site\SearchHistory' => Controller\Site\SearchHistoryController::class,
            'SearchHistory\Controller\Site\GuestBoard' => Controller\Site\GuestBoardController::class,
        ],
    ],
    'router' => [
        'routes' => [
            'site' => [
                'child_routes' => [
                    'search-history' => [
                        'type' => \Zend\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/search-history[/:action]',
                            'constraints' => [
                                'action' => 'add|delete',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'SearchHistory\Controller\Site',
                                'controller' => 'SearchHistory',
                                'action' => 'add',
                            ],
                        ],
                    ],
                    'search-history-id' => [
                        'type' => \Zend\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/search-history/:id[/:action]',
                            'constraints' => [
                                'action' => 'add|delete',
                                'id' => '\d+',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'SearchHistory\Controller\Site',
                                'controller' => 'SearchHistory',
                                'action' => 'update',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => dirname(__DIR__) . '/language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
    'blocksdisposition' => [
        'item_set_browse' => [
            'SearchHistory',
        ],
        'item_browse' => [
            'SearchHistory',
        ],
        'media_browse' => [
            'SearchHistory',
        ],
    ],
    'searchhistory' => [
    ],
];
