<?php

namespace SearchHistory\Controller\Site;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

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

        $query = $this->params()->fromQuery();
        $query['user_id'] = $user->getId();

        $searchRequests = $this->api()->search('search_requests', $query)->getContent();

        $view = new ViewModel;
        $view
            ->setTemplate('guest/site/guest/search-history')
            ->setVariable('site', $this->currentSite())
            ->setVariable('searchRequests', $searchRequests);
        return $view;
    }
}
