<?php declare(strict_types=1);

namespace SearchHistory\Service\Controller\Site;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use SearchHistory\Controller\Site\SearchRequestController;

class SearchRequestControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new SearchRequestController(
            $services->get('Omeka\ApiAdapterManager')->get('search_requests')
        );
    }
}
