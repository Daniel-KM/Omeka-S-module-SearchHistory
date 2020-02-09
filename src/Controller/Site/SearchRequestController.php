<?php

namespace SearchHistory\Controller\Site;

use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class SearchRequestController extends AbstractActionController
{
    public function addAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->jsonErrorNotFound();
        }

        $user = $this->identity();
        if (!$user) {
            return new JsonModel([
                'status' => 'error',
                'message' => $this->translate('Access forbidden'), // @translate
            ]);
        }

        $params = $this->params();
        $query = $this->cleanQuery();
        if (!$query) {
            return new JsonModel([
                'status' => 'fail',
                'data' => [
                    'query' => $this->translate('A query is required'), // @translate
                ],
            ]);
        }

        $engine = $params->fromQuery('engine');
        if (!strlen($engine)) {
            return new JsonModel([
                'status' => 'fail',
                'data' => [
                    'engine' => $this->translate('The path of the engine is required'), // @translate
                ],
            ]);
        }

        $site = $this->currentSite();
        $siteId = $site ? $site->id() : null;

        $api = $this->api();

        $comment = $params->fromQuery('comment');
        if (empty($comment)) {
            $comment = (new \DateTime())->format('Y-m-d H:i:s');
        }

        $searchRequest = [
            'o:user_id' => $user->getId(),
            'o-module-search-history:comment' => $comment,
            'o:site_id' => $siteId,
            'o:engine' => $engine,
            'o:query' => $query,
        ];

        $response = $api->create('search_requests', $searchRequest);
        if (!$response) {
            return new JsonModel([
                'status' => 'error',
                'message' => $this->translate('Unable to save.'), // @translate
            ]);
        }

        $searchRequest = $response->getContent();

        return new JsonModel([
            'status' => 'success',
            'data' => [
                'search_request' => $searchRequest,
                'url_delete' => $this->url()->fromRoute('site/search-history-id', ['action' => 'delete', 'id' => $searchRequest->id()], true),
            ],
        ]);
    }

    public function deleteAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->jsonErrorNotFound();
        }

        $user = $this->identity();
        if (!$user) {
            return new JsonModel([
                'status' => 'error',
                'message' => $this->translate('Access forbidden'), // @translate
            ]);
        }

        $params = $this->params();
        $id = $params->fromRoute('id') ?: $params->fromQuery('id');
        if (!$id) {
            return $this->jsonErrorNotFound();
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

        return new JsonModel([
            'status' => 'success',
            'data' => [
                'search_requests' => $results,
            ],
        ]);
    }

    /**
     * Clean a request query.
     *
     * @see \SearchHistory\View\Helper\LinkSearchHistory::cleanQuery()
     *
     * @return string
     */
    protected function cleanQuery()
    {
        $params = $this->params();
        $query = ltrim($params->fromQuery('query'), '?');

        // Clean query for better search.
        $request = [];
        parse_str($query, $request);
        unset($request['csrf']);
        unset($request['page']);
        unset($request['per_page']);
        unset($request['offset']);
        unset($request['limit']);
        $request = array_filter($request, function ($v) {
            // TODO Improve cleaning of empty sub-arrays in the query.
            return (bool) is_array($v) ? !empty($v) : strlen($v);
        });

        return http_build_query($request);
    }

    protected function jsonErrorNotFound()
    {
        $response = $this->getResponse();
        $response->setStatusCode(Response::STATUS_CODE_404);
        return new JsonModel([
            'status' => 'error',
            'message' => $this->translate('Not found'), // @translate
        ]);
    }
}
