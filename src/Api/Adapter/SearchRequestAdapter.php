<?php

namespace SearchHistory\Api\Adapter;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;

class SearchRequestAdapter extends AbstractEntityAdapter
{
    protected $sortFields = [
        'id' => 'id',
        'comment' => 'comment',
        'engine' => 'engine',
        'created' => 'created',
        'modified' => 'modified',
    ];

    public function getResourceName()
    {
        return 'search_requests';
    }

    public function getRepresentationClass()
    {
        return \SearchHistory\Api\Representation\SearchRequestRepresentation::class;
    }

    public function getEntityClass()
    {
        return \SearchHistory\Entity\SearchRequest::class;
    }

    public function hydrate(Request $request, EntityInterface $entity,
        ErrorStore $errorStore
    ) {
        /** @var \AccessResource\Entity\AccessRequest $entity */
        $data = $request->getContent();
        foreach ($data as $key => $value) {
            $key = str_replace(['o:', 'o-module-search-history:'], '', $key);
            $method = 'set' . ucfirst(Inflector::camelize($key));
            if (!method_exists($entity, $method)) {
                continue;
            }
            $entity->$method($value);
        }
        if ($this->shouldHydrate($request, 'o:user_id')) {
            $userId = $request->getValue('o:user_id');
            $entity->setUser($this->getAdapter('users')->findEntity($userId));
        }
        if ($this->shouldHydrate($request, 'o:site_id')) {
            $siteId = $request->getValue('o:site_id');
            $entity->setSite($this->getAdapter('sites')->findEntity($siteId));
        }
        $this->updateTimestamps($request, $entity);
    }

    public function buildQuery(QueryBuilder $qb, array $query)
    {
        $isOldOmeka = \Omeka\Module::VERSION < 2;
        $alias = $isOldOmeka ? $this->getEntityClass() : 'omeka_root';
        $expr = $qb->expr();

        if (isset($query['user_id'])) {
            $userAlias = $this->createAlias();
            $qb->innerJoin(
                $alias . '.user',
                $userAlias
            );
            $qb->andWhere($expr->eq(
                $userAlias . '.id',
                $this->createNamedParameter($qb, $query['user_id']))
            );
        }

        if (isset($query['site_id'])) {
            $siteAlias = $this->createAlias();
            $qb->innerJoin(
                $alias . '.site',
                $siteAlias
            );
            $qb->andWhere($expr->eq(
                $siteAlias . '.id',
                $this->createNamedParameter($qb, $query['site_id']))
            );
        }
    }
}
