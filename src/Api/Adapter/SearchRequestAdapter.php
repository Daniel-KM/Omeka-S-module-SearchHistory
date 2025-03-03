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

        if (isset($query['engine']) && $query['engine'] !== '') {
            $qb->andWhere($expr->eq(
                'omeka_root.engine',
                $this->createNamedParameter($qb, $query['engine'])
            ));
        }

        if (isset($query['query']) && $query['query'] !== '') {
            $queryQuery = $this->cleanQuery($query['query']);
            $qb->andWhere($expr->eq(
                'omeka_root.query',
                $this->createNamedParameter($qb, $queryQuery)
            ));
        }
    }

    /**
     * Clean a query (remove empty values and sort by key) and get http query.
     *
     * @param string|array $query
     * @return string
     */
    public function cleanQuery($query): string
    {
        if (empty($query)) {
            return '';
        }

        if (!is_array($query)) {
            $q = ltrim((string) $query, "? \t\n\r\0\x0B");
            parse_str($q, $query);
        }

        // Clean query for better search.
        // Keep sort, as it is a user choice.
        unset(
            $query['csrf'],
            $query['page'],
            $query['per_page'],
            $query['offset'],
            $query['limit'],
            $query['submit']
        );

        // "0" is a valid value.
        $arrayFilterRecursiveEmpty = null;
        $arrayFilterRecursiveEmpty = function (array &$array) use (&$arrayFilterRecursiveEmpty): array {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $array[$key] = $arrayFilterRecursiveEmpty($value);
                }
                if ($array[$key] === '' || $array[$key] === null || $array[$key] === []) {
                    unset($array[$key]);
                }
            }
            return $array;
        };
        $arrayFilterRecursiveEmpty($query);

        /** @see https://softwareengineering.stackexchange.com/questions/291809/sort-multidimensional-array-recursively-is-this-reasonable */
        $kSortRecursive = null;
        $kSortRecursive = function (array &$array) use (&$kSortRecursive): void {
            ksort($array);
            foreach ($kSortRecursive as $key => $value) {
                if (is_array($value)) {
                    $kSortRecursive($kSortRecursive[$key]);
                }
            }
        };
        $kSortRecursive($query);

        return urldecode(http_build_query($query, '', '&', PHP_QUERY_RFC3986));
    }
}
