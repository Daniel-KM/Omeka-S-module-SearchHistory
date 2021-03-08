<?php declare(strict_types=1);

namespace SearchHistory\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;

class SearchRequestRepresentation extends AbstractEntityRepresentation
{
    public function getControllerName()
    {
        return \SearchHistory\Controller\Admin\SearchRequestController::class;
    }

    public function getJsonLdType()
    {
        return 'o-module-search-history:SearchRequest';
    }

    public function getJsonLd()
    {
        $user = $this->user();
        $site = $this->site();

        $created = [
            '@value' => $this->getDateTime($this->created()),
            '@type' => 'http://www.w3.org/2001/XMLSchema#dateTime',
        ];

        $modified = $this->modified();
        if ($modified) {
            $modified = [
                '@value' => $this->getDateTime($modified),
                '@type' => 'http://www.w3.org/2001/XMLSchema#dateTime',
            ];
        }

        return [
            'o:id' => $this->id(),
            'o:user' => $user ? $user->getReference() : null,
            'o-module-search-history:comment' => $this->comment(),
            'o:site' => $site ? $site->getReference() : null,
            'o:engine' => $this->engine(),
            'o:query' => $this->query(),
            'o:created' => $created,
            'o:modified' => $modified,
        ];
    }

    /**
     * @return \Omeka\Api\Representation\UserRepresentation
     */
    public function user()
    {
        $user = $this->resource->getUser();
        return $this->getAdapter('users')->getRepresentation($user);
    }

    /**
     * @return string
     */
    public function comment()
    {
        return $this->resource->getComment();
    }

    /**
     * @return \Omeka\Api\Representation\SiteRepresentation|null
     */
    public function site()
    {
        $site = $this->resource->getSite();
        return $site
            ? $this->getAdapter('sites')->getRepresentation($site)
            : null;
    }

    /**
     * @return string
     */
    public function engine()
    {
        $engine = $this->resource->getEngine();
        // Manage the module Search: get the string from the id of the page.
        // Note: the search engine may have been changed.
        if (is_numeric($engine)) {
            if ($this->isModuleActive('Search')) {
                $api = $this->getServiceLocator()->get('Omeka\ApiManager');
                $engine = $api->search('search_pages', ['id' => $engine], ['returnScalar' => 'path'])->getContent();
                $engine = $engine ? reset($engine) : null;
            }
        }
        return $engine;
    }

    /**
     * @return string
     */
    public function query()
    {
        return $this->resource->getQuery();
    }

    /**
     * Get the url of the search.
     *
     * The original search engine may be unavailable.
     *
     * @return string|null
     */
    public function originalUrl()
    {
        $engine = $this->engine();
        if (!strlen((string) $engine)) {
            return null;
        }

        $basePath = $this->getServiceLocator()->get('ViewHelperManager')->get('basePath');
        $site = $this->site();

        return $basePath()
            . ($site ? '/s/' . $site->slug() : '/admin')
            . '/' . $engine
            . '?' . $this->query();
    }

    /**
     * @return \DateTime
     */
    public function created()
    {
        return $this->resource->getCreated();
    }

    /**
     * @return \DateTime
     */
    public function modified()
    {
        return $this->resource->getModified();
    }

    public function siteUrl($siteSlug = null, $canonical = false)
    {
        if (!$siteSlug) {
            $siteSlug = $this->getServiceLocator()->get('Application')
                ->getMvcEvent()->getRouteMatch()->getParam('site-slug');
        }
        $url = $this->getViewHelper('Url');
        return $url(
            'site/guest/search-history',
            [
                'site-slug' => $siteSlug,
                'id' => $this->id(),
            ],
            ['force_canonical' => $canonical]
        );
    }

    /**
     * Check if a module is active.
     *
     * @param string $moduleClass
     * @return bool
     */
    protected function isModuleActive($moduleClass)
    {
        $services = $this->getServiceLocator();
        /** @var \Omeka\Module\Manager $moduleManager */
        $moduleManager = $services->get('Omeka\ModuleManager');
        $module = $moduleManager->getModule($moduleClass);
        return $module
            && $module->getState() === \Omeka\Module\Manager::STATE_ACTIVE;
    }
}
