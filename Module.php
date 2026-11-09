<?php declare(strict_types=1);

namespace SearchHistory;

// Common may be installed but not registered in autoloader, in particular
// during upgrade. So dynamically register all classes of the module.
if (!defined('COMMON_PSR4_FALLBACK')) {
    foreach ([
        OMEKA_PATH . '/modules/Common/src',
        OMEKA_PATH . '/composer-addons/modules/Common/src',
        dirname(__DIR__) . '/Common/src',
    ] as $commonSrc) {
        if (file_exists($commonSrc . '/TraitModule.php')) {
            define('COMMON_PSR4_FALLBACK', $commonSrc);
            spl_autoload_register(static function ($class): void {
                if (str_starts_with($class, 'Common\\')) {
                    $file = COMMON_PSR4_FALLBACK . '/' . strtr(substr($class, 7), '\\', '/') . '.php';
                    if (file_exists($file)) {
                        require_once $file;
                    }
                }
            });
            break;
        }
    }
}

use Common\TraitModule;
use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Omeka\Module\AbstractModule;

/**
 * Search History.
 *
 * @copyright Daniel Berthereau 2019-2026
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
                [Entity\SearchRequest::class],
                ['create', 'read', 'update', 'delete']
            )
            ->allow(
                $roles,
                [Api\Adapter\SearchRequestAdapter::class],
                ['search', 'read', 'create', 'update', 'delete']
            )
            ->allow(
                $roles,
                [
                    'SearchHistory\Controller\Site\SearchRequest',
                    'SearchHistory\Controller\Site\GuestBoard',
                ]
            );
    }

    protected function preInstall(): void
    {
        $services = $this->getServiceLocator();
        $plugins = $services->get('ControllerPluginManager');
        $translator = $services->get('MvcTranslator');

        if (!method_exists($this, 'checkModuleActiveVersion') || !$this->checkModuleActiveVersion('Common', '3.4.96')) {
            $message = new \Omeka\Stdlib\Message(
                $translator->translate('The module %1$s should be upgraded to version %2$s or later.'), // @translate
                'Common', '3.4.96'
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
            \Omeka\Form\SiteSettingsForm::class,
            'form.add_elements',
            [$this, 'handleSiteSettings']
        );
        $sharedEventManager->attach(
            \Guest\Controller\Site\GuestController::class,
            'guest.widgets',
            [$this, 'handleGuestWidgets']
        );
    }

    public function handleViewBrowseAfter(Event $event): void
    {
        $services = $this->getServiceLocator();
        $currentTheme = $services->get('Omeka\Site\ThemeManager')->getCurrentTheme();
        if (method_exists($currentTheme, 'isConfigurableResourcePageBlocks') && $currentTheme->isConfigurableResourcePageBlocks()) {
            return;
        }
        $placements = $services->get('Omeka\Settings\Site')->get('searchhistory_placement', []);
        $easyMeta = $services->get('Common\EasyMeta');
        $view = $event->getTarget();
        $params = $view->params();
        $controller = $params->fromRoute()['__CONTROLLER'] ?? $params->fromRoute()['controller'] ?? null;
        $resourceName = $easyMeta->resourceName($controller);
        $key = 'browse/' . $resourceName;
        if (!in_array($key, $placements)) {
            return;
        }
        echo $view->searchHistoryLink();
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
