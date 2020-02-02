<?php
namespace SearchHistory\Site\Navigation\Link;

use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Site\Navigation\Link\LinkInterface;
use Omeka\Stdlib\ErrorStore;

class SearchHistory implements LinkInterface
{
    public function getName()
    {
        return 'Search History'; // @translate
    }

    public function getFormTemplate()
    {
        return 'common/navigation-link-form/label';
    }

    public function isValid(array $data, ErrorStore $errorStore)
    {
        if (!isset($data['label'])) {
            $errorStore->addError('o:navigation', sprintf('Invalid navigation: link without label (%s)', $this->getName())); // @translate
            return false;
        }
        return true;
    }

    public function getLabel(array $data, SiteRepresentation $site)
    {
        return isset($data['label']) && trim($data['label']) !== ''
            ? $data['label']
            : 'Search History'; // @translate
    }

    public function toZend(array $data, SiteRepresentation $site)
    {
        return [
            'label' => $data['label'],
            'route' => 'site/guest/search-history',
            'class' => 'search-history-link',
            'params' => [
                'site-slug' => $site->slug(),
            ],
        ];
    }

    public function toJstree(array $data, SiteRepresentation $site)
    {
        return [
            'label' => isset($data['label']) ? trim($data['label']) : '',
        ];
    }
}
