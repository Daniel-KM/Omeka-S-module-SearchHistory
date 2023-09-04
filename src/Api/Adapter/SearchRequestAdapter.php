<?php declare(strict_types=1);

namespace SearchHistory\Api\Adapter;

use Doctrine\Inflector\InflectorFactory;
use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;

class SearchRequestAdapter extends AbstractEntityAdapter
{
    protected $sortFields = [
        'id' => 'id',
        'site_id' => 'site',
        'user_id' => 'user',
        'comment' => 'comment',
        'engine' => 'engine',
        'created' => 'created',
        'modified' => 'modified',
    ];

    protected $scalarFields = [
        'id' => 'id',
        'user' => 'user',
        'site' => 'site',
        'comment' => 'comment',
        'engine' => 'engine',
        'query' => 'query',
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
    ): void {
        /** @var \SearchHistory\Entity\SearchRequest $entity */
        $data = $request->getContent();
        $inflector = InflectorFactory::create()->build();
        foreach ($data as $key => $value) {
            $key = str_replace(['o:', 'o-module-search-history:'], '', $key);
            $method = 'set' . ucfirst($inflector->camelize($key));
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

    public function buildQuery(QueryBuilder $qb, array $query): void
    {
        $expr = $qb->expr();

        if (isset($query['user_id'])) {
            $userAlias = $this->createAlias();
            $qb->innerJoin(
                'omeka_root.user',
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
                'omeka_root.site',
                $siteAlias
            );
            if ($query['site_id']) {
                $qb->andWhere($expr->eq(
                    $siteAlias . '.id',
                    $this->createNamedParameter($qb, $query['site_id']))
                );
            }
            // A "0" means a search in admin board.
            else {
                $qb->andWhere($expr->isNull($siteAlias . '.id'));
            }
        }
    }
}
