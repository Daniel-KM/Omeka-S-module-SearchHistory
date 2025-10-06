<?php declare(strict_types=1);

namespace SearchHistory\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;
use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Api\Representation\UserRepresentation;

class SearchRequestRepresentation extends AbstractEntityRepresentation
{
    public function getControllerName()
    {
        return 'search-request';
    }

    public function getJsonLdType()
    {
        return 'o:SearchRequest';
    }

    public function getJsonLd()
    {
        $user = $this->user();
        $site = $this->site();
        $modified = $this->modified();

        return [
            'o:id' => $this->id(),
            'o:user' => $user ? $user->getReference()->jsonSerialize() : null,
            'o:comment' => $this->comment(),
            'o:site' => $site ? $site->getReference()->jsonSerialize() : null,
            'o:engine' => $this->engine(),
            'o:query' => $this->query(),
            'o:created' => [
                '@value' => $this->getDateTime($this->created())->jsonSerialize(),
                '@type' => 'http://www.w3.org/2001/XMLSchema#dateTime',
            ],
            'o:modified' => $modified
                ? [
                    '@value' => $this->getDateTime($modified)->jsonSerialize(),
                    '@type' => 'http://www.w3.org/2001/XMLSchema#dateTime',
                ] : null,
        ];
    }

    public function user(): ?UserRepresentation
    {
        $user = $this->resource->getUser();
        return $user
            ? $this->getAdapter('users')->getRepresentation($user)
            : null;
    }

    public function comment(): ?string
    {
        return $this->resource->getComment();
    }

    public function site(): ?SiteRepresentation
    {
        $site = $this->resource->getSite();
        return $site
            ? $this->getAdapter('sites')->getRepresentation($site)
            : null;
    }

    public function engine(): ?string
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

    public function query(): ?string
    {
        return $this->resource->getQuery();
    }

    /**
     * Get the url of the search.
     *
     * The original search engine may be unavailable.
     */
    public function originalUrl(): ?string
    {
        $engine = $this->engine();
        if (!strlen((string) $engine)) {
            return null;
        }

        $basePath = $this->getServiceLocator()->get('ViewHelperManager')->get('basePath');
        $site = $this->site();

        // A numeric engine comes from old modules or from module Search.
        if (is_numeric($engine)
            && class_exists('Search\Module', false)
        ) {
            $api = $this->getServiceLocator()->get('Omeka\ApiManager');
            try {
                $engine = $api->read('search_pages', $engine)->getContent()->path();
            } catch (\Exception $e) {
            }
        }

        return $basePath()
            . ($site ? '/s/' . $site->slug() : '/admin')
            . '/' . $engine
            . '?' . $this->query();
    }

    public function created(): \DateTime
    {
        return $this->resource->getCreated();
    }

    public function modified(): ?\DateTime
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
     */
    protected function isModuleActive(string $moduleClass): bool
    {
        $services = $this->getServiceLocator();
        /** @var \Omeka\Module\Manager $moduleManager */
        $moduleManager = $services->get('Omeka\ModuleManager');
        $module = $moduleManager->getModule($moduleClass);
        return $module
            && $module->getState() === \Omeka\Module\Manager::STATE_ACTIVE;
    }
}
