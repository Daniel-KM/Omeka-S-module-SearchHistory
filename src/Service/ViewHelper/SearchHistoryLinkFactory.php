<?php declare(strict_types=1);

namespace SearchHistory\Service\ViewHelper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use SearchHistory\View\Helper\SearchHistoryLink;

class SearchHistoryLinkFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new SearchHistoryLink(
            $services->get('Omeka\ApiAdapterManager')->get('search_requests')
        );
    }
}
