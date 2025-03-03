<?php declare(strict_types=1);

namespace SearchHistory\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use SearchHistory\Api\Adapter\SearchRequestAdapter;

class SearchHistoryLink extends AbstractHelper
{
    /**
     * The default partial view script.
     */
    const PARTIAL_NAME = 'common/search-history-button';

    /**
     * @var \SearchHistory\Api\Adapter\SearchRequestAdapter
     */
    protected $searchRequestAdapter;

    public function __construct(SearchRequestAdapter $searchRequestAdapter)
    {
        $this->searchRequestAdapter = $searchRequestAdapter;
    }

    /**
     * Get the link to the search history button (to save or to delete).
     *
     * @param array $options Supported options:
     * - template (string)
     */
    public function __invoke(array $options = []): string
    {
        $view = $this->getView();

        $defaultOptions = [
            'template' => null,
        ];

        $options += $defaultOptions;

        $site = $this->currentSite();
        $user = $view->identity();
        $engine = null;
        $query = '';
        $searchRequest = null;

        if ($user) {
            $engine = $this->findEngine();
            if ($engine) {
                $request = $view->params()->fromQuery();
                // Either duplicate cleaning, very quick, or duplicate api, slower.
                $request = $this->searchRequestAdapter->cleanQuery($request);
                if ($request) {
                    $query = $request;
                    $searchRequest = $view->api()->searchOne('search_requests', [
                        'user_id' => $user->getId(),
                        'site_id' => $site ? $site->id() : 0,
                        'engine' => $engine,
                        'query' => $query,
                    ])->getContent();
                }
            }
        }

        $template = $options['template'] ?? self::PARTIAL_NAME;

        return $view->partial($template, [
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
        /** @var \Omeka\View\Helper\Params $params */
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
