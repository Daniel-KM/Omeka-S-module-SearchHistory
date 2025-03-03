<?php declare(strict_types=1);

namespace SearchHistory\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class SearchHistoryLink extends AbstractHelper
{
    /**
     * Get the link to the search history button (to save or to delete).
     *
     * @return string
     */
    public function __invoke()
    {
        $view = $this->getView();

        $site = $this->currentSite();
        $user = $view->identity();
        $engine = null;
        $query = '';
        $searchRequest = null;

        if ($user) {
            $query = $this->cleanQuery();
            $hasQuery = (bool) $query;
            if ($hasQuery) {
                $engine = $this->findEngine();
                if ($engine) {
                    $searchRequest = $view->api()->searchOne('search_requests', [
                        'user_id' => $user->getId(),
                        'site_id' => $site ? $site->id() : 0,
                        'engine' => $engine,
                        'query' => $query,
                    ])->getContent();
                } else {
                    $query = '';
                }
            }
        }

        return $view->partial('common/helper/search-history-button', [
            'site' => $site,
            'engine' => $engine,
            'query' => $query,
            'searchRequest' => $searchRequest,
        ]);
    }

    /**
     * Find the engine from the route.
     *
     * @return string|null;
     */
    protected function findEngine()
    {
        $params = $this->getView()->params();
        $controller = $params->fromRoute('controller');
        switch ($controller) {
            case 'Omeka\Controller\Site\Item':
            case 'Omeka\Controller\Admin\Item':
            case 'item':
                $engine = 'item';
                break;
            case 'Omeka\Controller\Site\ItemSet':
            case 'Omeka\Controller\Admin\ItemSet':
            case 'item-set':
                $engine = 'item-set';
                break;
            case 'Omeka\Controller\Site\Media':
            case 'Omeka\Controller\Admin\Media':
            case 'media':
                $engine = 'media';
                break;
            case \AdvancedSearch\Controller\SearchController::class:
                $engine = $params->fromRoute('search-slug');
                break;
            case \Search\Controller\IndexController::class:
                $engine = $params->fromRoute('id');
                break;
                // TODO Manage forms on standard pages (blocks).
            default:
                $engine = null;
                break;
        }
        return $engine;
    }

    /**
     * Clean a request query.
     *
     * @see \SearchHistory\Controller\Site\SearchRequestController::cleanQuery()
     *
     * @return string
     */
    protected function cleanQuery()
    {
        $view = $this->getView();

        // Clean query for better search.
        $request = $view->params()->fromQuery();
        unset($request['csrf']);
        unset($request['page']);
        unset($request['per_page']);
        unset($request['offset']);
        unset($request['limit']);
        $request = array_filter($request, function ($v) {
            // TODO Improve cleaning of empty sub-arrays in the query.
            return (bool) is_array($v) ? !empty($v) : strlen((string) $v);
        });

        return http_build_query($request);
    }

    /**
     * Get the current site from the view.
     */
    protected function currentSite(): ?\Omeka\Api\Representation\SiteRepresentation
    {
        return $this->view->site ?? $this->view->site = $this->view
            ->getHelperPluginManager()
            ->get('Laminas\View\Helper\ViewModel')
            ->getRoot()
            ->getVariable('site');
    }
}
