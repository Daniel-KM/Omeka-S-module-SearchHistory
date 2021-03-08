<?php

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
        $site = $this->currentSite();

        $user = $this->identity();
        $query = $this->params()->fromQuery();
        $query['user_id'] = $user->getId();

        $this->setBrowseDefaults('created');
        $response = $this->api()->search('search_requests', $query);
        $this->paginator($response->getTotalResults());
        $searchRequests = $response->getContent();

        $view = new ViewModel;
        return $view
            ->setTemplate('guest/site/guest/search-history')
            ->setVariable('site', $site)
            ->setVariable('searchRequests', $searchRequests)
            ->setVariable('resources', $searchRequests);
    }
}
