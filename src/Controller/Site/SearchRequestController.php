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

        $engine = $params->fromPost('engine');
        if (!strlen($engine)) {
            return $this->jSend(JSend::FAIL, [
                'engine' => $this->translate('The path of the engine is required'), // @translate
            ]);
        }

        $site = $this->currentSite();
        $siteId = $site ? $site->id() : null;

        $api = $this->api();

        $comment = trim((string) $params->fromPost('comment'));
        if (empty($comment)) {
            $comment = (new \DateTime())->format('Y-m-d H:i:s');
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

    public function deleteAction()
    {
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
        $id = $params->fromRoute('id') ?: $params->fromQuery('id');
        if (!$id) {
            if ($returnJson) {
                return $this->jSend(JSend::FAIL, [
                    'search_request' => $this->translate('Not found'), // @translate
                ], null, Response::STATUS_CODE_404);
            }
            $this->messenger()->addWarning($this->translate('Not found')); // @translate
            return $this->redirect()->toRoute('site/guest/search-history', ['action' => 'show'], true);
        }

        $isMultiple = is_array($id);
        $ids = $isMultiple ? $id : [$id];

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
