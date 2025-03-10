<?php declare(strict_types=1);

namespace SearchHistory;

if (!class_exists(\Common\TraitModule::class)) {
    require_once dirname(__DIR__) . '/Common/TraitModule.php';
}

use Common\TraitModule;
use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Omeka\Module\AbstractModule;

/**
 * Search History.
 *
 * @copyright Daniel Berthereau 2019-2025
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 */
class Module extends AbstractModule
{
    use TraitModule;

    const NAMESPACE = __NAMESPACE__;

    /**
     * @todo Remove dependency to guest. See Selection.
     */
    protected $dependencies = [
        'Guest',
    ];

    public function onBootstrap(MvcEvent $event): void
    {
        parent::onBootstrap($event);

        /** @var \Omeka\Permissions\Acl $acl */
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

    protected function preInstall(): void
    {
        $services = $this->getServiceLocator();
        $plugins = $services->get('ControllerPluginManager');
        $translate = $plugins->get('translate');

        if (!method_exists($this, 'checkModuleActiveVersion') || !$this->checkModuleActiveVersion('Common', '3.4.66')) {
            $message = new \Omeka\Stdlib\Message(
                $translate('The module %1$s should be upgraded to version %2$s or later.'), // @translate
                'Common', '3.4.66'
            );
            throw new \Omeka\Module\Exception\ModuleCannotInstallException((string) $message);
        }
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager): void
    {
        $sharedEventManager->attach(
            'Omeka\Controller\Site\ItemSet',
            'view.browse.after',
            [$this, 'handleViewBrowseAfter']
        );
        $sharedEventManager->attach(
            'Omeka\Controller\Site\Item',
            'view.browse.after',
            [$this, 'handleViewBrowseAfter']
        );
        $sharedEventManager->attach(
            'Omeka\Controller\Site\Media',
            'view.browse.after',
            [$this, 'handleViewBrowseAfter']
        );
        $sharedEventManager->attach(
            \Guest\Controller\Site\GuestController::class,
            'guest.widgets',
            [$this, 'handleGuestWidgets']
        );
    }

    public function handleViewBrowseAfter(Event $event): void
    {
        echo $event->getTarget()->searchHistoryLink();
    }

    public function handleGuestWidgets(Event $event): void
    {
        $helpers = $this->getServiceLocator()->get('ViewHelperManager');
        $partial = $helpers->get('partial');
        $translate = $helpers->get('translate');

        $widget = [];
        $widget['label'] = $translate('Search History'); // @translate
        $widget['content'] = $partial('guest/site/guest/widget/search-history');

        $widgets = $event->getParam('widgets');
        $widgets['search-history'] = $widget;
        $event->setParam('widgets', $widgets);
    }
}
