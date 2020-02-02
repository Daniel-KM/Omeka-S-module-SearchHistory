<?php
namespace SearchHistory;

if (!class_exists(\Generic\AbstractModule::class)) {
    require file_exists(dirname(__DIR__) . '/Generic/AbstractModule.php')
        ? dirname(__DIR__) . '/Generic/AbstractModule.php'
        : __DIR__ . '/src/Generic/AbstractModule.php';
}

use Generic\AbstractModule;
use Zend\EventManager\Event;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\Mvc\MvcEvent;

/**
 * Search History.
 *
 * @copyright Daniel Berthereau 2019-2020
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 */
class Module extends AbstractModule
{
    const NAMESPACE = __NAMESPACE__;

    protected $dependency = 'Guest';

    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);

        $acl = $this->getServiceLocator()->get('Omeka\Acl');

        $roles = $acl->getRoles();
        $acl
            ->allow(
                $roles,
                [
                    Entity\SearchRequest::class,
                    Api\Adapter\SearchRequestAdapter::class,
                    'SearchHistory\Controller\Site\SearchRequest',
                    'SearchHistory\Controller\Site\GuestBoard',
                ]
        );
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach(
            'Omeka\Controller\Site\ItemSet',
            'view.browse.after',
            [$this, 'handleViewShowAfter']
        );
        $sharedEventManager->attach(
            'Omeka\Controller\Site\Item',
            'view.browse.after',
            [$this, 'handleViewShowAfter']
        );
        $sharedEventManager->attach(
            'Omeka\Controller\Site\Media',
            'view.browse.after',
            [$this, 'handleViewShowAfter']
        );
        $sharedEventManager->attach(
            \Guest\Controller\Site\GuestController::class,
            'guest.widgets',
            [$this, 'handleGuestWidgets']
        );
    }

    public function handleViewShowAfter(Event $event)
    {
        echo $event->getTarget()->linkSearchHistory();
    }

    public function handleGuestWidgets(Event $event)
    {
        $helpers = $this->getServiceLocator()->get('ViewHelperManager');
        $translate = $helpers->get('translate');
        $partial = $helpers->get('partial');

        $widget = [];
        $widget['label'] = $translate('Search History'); // @translate
        $widget['content'] = $partial('guest/site/guest/widget/search-history');

        $widgets = $event->getParam('widgets');
        $widgets['search-history'] = $widget;
        $event->setParam('widgets', $widgets);
    }
}
