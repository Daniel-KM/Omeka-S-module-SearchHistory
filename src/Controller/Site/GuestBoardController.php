<?php declare(strict_types=1);

namespace SearchHistory\Controller\Site;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class GuestBoardController extends AbstractActionController
{
    public function indexAction()
    {
        $params = $this->params()->fromRoute();
        $params['action'] = 'show';
        return $this->forward()->dispatch(__CLASS__, $params);
    }

    public function showAction()
    {
        $user = $this->identity();
        if (!$user) {
            return $this->redirect()->toRoute('login');
        }

        $site = $this->currentSite();

        $raw = $this->params()->fromQuery();
        $query = [
            'user_id' => $user->getId(),
            'sort_by' => $raw['sort_by'] ?? null,
            'sort_order' => $raw['sort_order'] ?? null,
            'page' => $raw['page'] ?? null,
            'per_page' => $raw['per_page'] ?? null,
        ];
        $query = array_filter($query, function ($v) {
            return $v !== null && $v !== '';
        });

        $this->setBrowseDefaults('created');
        $response = $this->api()->search('search_requests', $query);
        $this->paginator($response->getTotalResults());
        $searchRequests = $response->getContent();

        $view = new ViewModel([
            'site' => $site,
            'user' => $user,
            'searchRequests' => $searchRequests,
            'resources' => $searchRequests,
        ]);
        return $view
            ->setTemplate('guest/site/guest/search-history');
    }
}
