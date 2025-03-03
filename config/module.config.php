<?php declare(strict_types=1);
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
    'view_helpers' => [
        'invokables' => [
            'searchHistoryLink' => View\Helper\SearchHistoryLink::class,
        ],
        'aliases' => [
            // Deprecated alias.
            'linkSearchHistory' => 'searchHistoryLink',
        ],
    ],
    'controllers' => [
        'invokables' => [
            'SearchHistory\Controller\Site\SearchRequest' => Controller\Site\SearchRequestController::class,
            'SearchHistory\Controller\Site\GuestBoard' => Controller\Site\GuestBoardController::class,
        ],
    ],
    'navigation_links' => [
        'invokables' => [
            'searchHistory' => Site\Navigation\Link\SearchHistory::class,
        ],
    ],
    'navigation' => [
        'site' => [
            [
                'label' => 'Search History', // @translate
                'route' => 'site/guest/search-history',
                'controller' => 'SearchHistory\Controller\Site\GuestBoard',
                'action' => 'show',
                'useRouteMatch' => true,
                'visible' => false,
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'site' => [
                'child_routes' => [
                    'search-history' => [
                        'type' => \Laminas\Router\Http\Segment::class,
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
                        'type' => \Laminas\Router\Http\Segment::class,
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
                    'guest' => [
                        'type' => \Laminas\Router\Http\Literal::class,
                        'options' => [
                            'route' => '/guest',
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'search-history' => [
                                'type' => \Laminas\Router\Http\Literal::class,
                                'options' => [
                                    'route' => '/search-history',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'SearchHistory\Controller\Site',
                                        'controller' => 'GuestBoard',
                                        'action' => 'show',
                                    ],
                                ],
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
    'js_translate_strings' => [
        'No query is set.', // @translate
        'Your search is saved.', // @translate
        'You can find it in your account.', // @translate
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
