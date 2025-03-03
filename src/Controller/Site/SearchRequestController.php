<?php declare(strict_types=1);

namespace SearchHistory\Controller\Site;

use Common\Mvc\Controller\Plugin\JSend;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\Mvc\Controller\AbstractActionController;

class SearchRequestController extends AbstractActionController
{
    public function addAction()
    {
        $user = $this->identity();
        if (!$user) {
            return $this->jSend(JSend::FAIL, [
                'user' => $this->translate('Access forbidden'), // @translate
            ], null, Response::STATUS_CODE_403);
        }

        $params = $this->params();
        $query = $this->cleanQuery();
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
        $user = $this->identity();
        if (!$user) {
            return $this->jSend(JSend::FAIL, [
                'user' => $this->translate('Access forbidden'), // @translate
            ], null, Response::STATUS_CODE_403);
        }

        $params = $this->params();
        $id = $params->fromRoute('id') ?: $params->fromQuery('id');
        if (!$id) {
            return $this->jSend(JSend::FAIL, [
                'search_request' => $this->translate('Not found'), // @translate
            ], null, Response::STATUS_CODE_404);
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

        return $this->jSend(JSend::SUCCESS, [
            'search_requests' => $results,
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
        $query = ltrim($params->fromPost('query'), "? \t\n\r\0\x0B");

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

        return http_build_query($request, '', '&', PHP_QUERY_RFC3986);
    }
}
