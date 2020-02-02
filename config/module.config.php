<?php
namespace SearchHistory;

return  [
    'api_adapters' => [
        'invokables' => [
            'search_requests' => Api\Adapter\SearchRequestAdapter::class,
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
            'SearchHistory\Controller\Site\SearchRequest' => Controller\Site\SearchRequestController::class,
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
                                'action' => 'add|browse',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'SearchHistory\Controller\Site',
                                'controller' => 'SearchRequest',
                                'action' => 'browse',
                            ],
                        ],
                    ],
                    'search-history-id' => [
                        'type' => \Zend\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/search-history/:id[/:action]',
                            'constraints' => [
                                'action' => 'delete',
                                'id' => '\d+',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'SearchHistory\Controller\Site',
                                'controller' => 'SearchRequest',
                                'action' => 'edit',
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
