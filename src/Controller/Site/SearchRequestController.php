<?php declare(strict_types=1);

namespace SearchHistory\Controller\Site;

use Common\Mvc\Controller\Plugin\JSend;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use SearchHistory\Api\Adapter\SearchRequestAdapter;

class SearchRequestController extends AbstractActionController
{
    /**
     * @var \SearchHistory\Api\Adapter\SearchRequestAdapter
     */
    protected $searchRequestAdapter;

    public function __construct(SearchRequestAdapter $searchRequestAdapter)
    {
        $this->searchRequestAdapter = $searchRequestAdapter;
    }

    public function addAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->jSend(JSend::FAIL, [
                'method' => $this->translate('Method not allowed'), // @translate
            ], null, Response::STATUS_CODE_405);
        }

        $user = $this->identity();
        if (!$user) {
            return $this->jSend(JSend::FAIL, [
                'user' => $this->translate('Access forbidden'), // @translate
            ], null, Response::STATUS_CODE_403);
        }

        $params = $this->params();

        // Either duplicate cleaning, very quick, or duplicate api, slower.
        $query = $this->searchRequestAdapter->cleanQuery($params->fromPost('query'));
        if (!$query) {
            return $this->jSend(JSend::FAIL, [
                'query' => $this->translate('A query is required'), // @translate
            ]);
        }

        $engine = (string) $params->fromPost('engine');
        if (!strlen($engine)) {
            return $this->jSend(JSend::FAIL, [
                'engine' => $this->translate('The path of the engine is required'), // @translate
            ]);
        }

        $site = $this->currentSite();
        $siteId = $site ? $site->id() : null;

        $api = $this->api();

        // Skip duplicate automatic saves.
        $comment = trim((string) $params->fromPost('comment'));
        if (empty($comment)) {
            $existing = $api->searchOne('search_requests', [
                'user_id' => $user->getId(),
                'site_id' => $siteId ?? 0,
                'query' => $query,
                'engine' => $engine,
            ])->getContent();
            if ($existing) {
                return $this->jSend(JSend::SUCCESS, [
                    'search_request' => $existing,
                    'url_delete' => $this->url()->fromRoute('site/search-history-id', ['action' => 'delete', 'id' => $existing->id()], true),
                ]);
            }
        }
        if (empty($comment)) {
            parse_str($query, $queryParams);
            $text = $queryParams['q'] ?? $queryParams['fulltext_search'] ?? '';
            $date = (new \DateTime())->format('Y-m-d H:i:s');
            $comment = $text !== '' ? $text . ' — ' . $date : $date;
        }

        $searchRequest = [
            'o:user_id' => $user->getId(),
            'o:comment' => $comment,
            'o:site_id' => $siteId,
            'o:engine' => $engine,
            'o:query' => $query,
        ];

        $response = $api->create('search_requests', $searchRequest);
        if (!$response) {
            return $this->jSend(JSend::ERROR, [], $this->translate('Unable to save.')); // @translate
        }

        $searchRequest = $response->getContent();

        return $this->jSend(JSend::SUCCESS, [
            'search_request' => $searchRequest,
            'url_delete' => $this->url()->fromRoute('site/search-history-id', ['action' => 'delete', 'id' => $searchRequest->id()], true),
        ]);
    }

    public function clearAction()
    {
        $user = $this->identity();
        if (!$user) {
            return $this->jSend(JSend::FAIL, [
                'user' => $this->translate('Access forbidden'), // @translate
            ], null, Response::STATUS_CODE_403);
        }

        if (!$this->getRequest()->isPost()) {
            return $this->jSend(JSend::FAIL, [
                'method' => $this->translate('Method not allowed'), // @translate
            ], null, Response::STATUS_CODE_405);
        }

        $period = $this->params()->fromPost('period', 'all');
        $periods = [
            '1h' => '-1 hour',
            '24h' => '-24 hours',
            '7d' => '-7 days',
            '30d' => '-30 days',
            'all' => null,
        ];
        $modifier = $periods[$period] ?? null;

        $entityManager = $this->searchRequestAdapter->getEntityManager();
        $qb = $entityManager->createQueryBuilder();
        $qb->delete(\SearchHistory\Entity\SearchRequest::class, 'sr')
            ->where('sr.user = :userId')
            ->setParameter('userId', $user->getId());

        if ($modifier) {
            $since = new \DateTime($modifier);
            $qb->andWhere('sr.created >= :since')
                ->setParameter('since', $since);
        }

        $count = $qb->getQuery()->execute();

        return $this->jSend(JSend::SUCCESS, [
            'count' => $count,
        ]);
    }

    public function deleteAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->jSend(JSend::FAIL, [
                'method' => $this->translate('Method not allowed'), // @translate
            ], null, Response::STATUS_CODE_405);
        }

        $returnJson = $this->getRequest()->isXmlHttpRequest();

        $user = $this->identity();
        if (!$user) {
            if ($returnJson) {
                return $this->jSend(JSend::FAIL, [
                    'user' => $this->translate('Access forbidden'), // @translate
                ], null, Response::STATUS_CODE_403);
            }
            $this->messenger()->addWarning($this->translate('Access forbidden')); // @translate
            return $this->redirect()->toRoute('site', ['action' => 'index'], true);
        }

        $params = $this->params();
        $id = $params->fromRoute('id') ?: $params->fromPost('id');
        if (!$id) {
            if ($returnJson) {
                return $this->jSend(JSend::FAIL, [
                    'search_request' => $this->translate('Not found'), // @translate
                ], null, Response::STATUS_CODE_404);
            }
            $this->messenger()->addWarning($this->translate('Not found')); // @translate
            return $this->redirect()->toRoute('site/guest/search-history', ['action' => 'show'], true);
        }

        $ids = is_array($id) ? array_map('intval', $id) : [(int) $id];

        $api = $this->api();

        $userId = $user->getId();

        $results = [];
        foreach ($ids as $id) {
            // Avoid to remove requests of another user.
            // TODO Add acl check for own requests.
            $data = [
                'id' => $id,
                'user_id' => $userId,
            ];
            $searchRequest = $api->searchOne('search_requests', $data)->getContent();
            if ($searchRequest) {
                $api->delete('search_requests', ['id' => $id]);
            }
            $results[$id] = null;
        }

        if ($returnJson) {
            return $this->jSend(JSend::SUCCESS, [
                'search_requests' => $results,
            ]);
        }

        return $this->redirect()->toRoute('site/guest/search-history', ['action' => 'show'], true);
    }
}
