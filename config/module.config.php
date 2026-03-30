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
        'factories' => [
            'searchHistoryLink' => Service\ViewHelper\SearchHistoryLinkFactory::class,
        ],
    ],
    'form_elements' => [
        'invokables' => [
            Form\SiteSettingsFieldset::class => Form\SiteSettingsFieldset::class,
        ],
    ],
    'controllers' => [
        'invokables' => [
            'SearchHistory\Controller\Site\GuestBoard' => Controller\Site\GuestBoardController::class,
        ],
        'factories' => [
            'SearchHistory\Controller\Site\SearchRequest' => Service\Controller\Site\SearchRequestControllerFactory::class,
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
                                'action' => 'add|browse|clear',
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
                'type' => \Laminas\I18n\Translator\Loader\Gettext::class,
                'base_dir' => dirname(__DIR__) . '/language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
    'js_translate_strings' => [
        'All history', // @translate
        'An error occurred.', // @translate
        'Are you sure you want to delete this saved search?', // @translate
        'Cancel', // @translate
        'Cannot save search: the dialog is missing.', // @translate
        'Check input', // @translate
        'Clear search history', // @translate
        'Confirm', // @translate
        'Delete', // @translate
        'Delete search', // @translate
        'Last hour', // @translate
        'Last 24 hours', // @translate
        'Last 7 days', // @translate
        'Last 30 days', // @translate
        'Save search', // @translate
        'Search deleted from your account.', // @translate
        'Search saved in your account.', // @translate
        'The current search has no query.', // @translate
        'This action is irreversible. Select the period to clear:', // @translate
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
        'site_settings' => [
            'searchhistory_save_automatic' => false,
            'searchhistory_remove_in_account' => false,
        ],
    ],
];
