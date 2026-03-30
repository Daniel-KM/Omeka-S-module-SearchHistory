<?php declare(strict_types=1);

namespace SearchHistoryTest\Api\Adapter;

use CommonTest\AbstractHttpControllerTestCase;
use SearchHistoryTest\SearchHistoryTestTrait;

class SearchRequestAdapterTest extends AbstractHttpControllerTestCase
{
    use SearchHistoryTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->loginAdmin();
    }

    public function tearDown(): void
    {
        $this->cleanupResources();
        parent::tearDown();
    }

    public function testCreateSearchRequest(): void
    {
        $result = $this->createSearchRequest([
            'o:comment' => 'My search',
            'o:engine' => 'item',
            'o:query' => 'q=hello&sort_by=created',
        ]);
        $this->assertNotNull($result['id']);
        $repr = $result['representation'];
        $this->assertSame('My search', $repr->comment());
        $this->assertSame('item', $repr->engine());
    }

    public function testCleanQuerySortsKeys(): void
    {
        $result = $this->createSearchRequest([
            'o:query' => 'z=1&a=2&m=3',
        ]);
        $repr = $result['representation'];
        $this->assertSame('a=2&m=3&z=1', $repr->query());
    }

    public function testCleanQueryRemovesEmptyValues(): void
    {
        $result = $this->createSearchRequest([
            'o:query' => 'q=test&empty=&keep=1',
        ]);
        $repr = $result['representation'];
        $this->assertStringNotContainsString('empty=', $repr->query());
        $this->assertStringContainsString('keep=1', $repr->query());
        $this->assertStringContainsString('q=test', $repr->query());
    }

    public function testCleanQueryRemovesPagination(): void
    {
        $result = $this->createSearchRequest([
            'o:query' => 'q=test&page=3&per_page=25&limit=10&offset=5',
        ]);
        $repr = $result['representation'];
        $this->assertSame('q=test', $repr->query());
    }

    public function testCleanQueryRemovesCsrf(): void
    {
        $result = $this->createSearchRequest([
            'o:query' => 'q=test&csrf=abc123&submit=Search',
        ]);
        $repr = $result['representation'];
        $this->assertSame('q=test', $repr->query());
    }

    public function testSearchByUserId(): void
    {
        $this->createSearchRequest(['o:comment' => 'User search']);
        $userId = $this->getAdminUserId();
        $results = $this->api()->search('search_requests', [
            'user_id' => $userId,
        ])->getContent();
        $this->assertNotEmpty($results);
        $found = false;
        foreach ($results as $r) {
            if ($r->comment() === 'User search') {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    public function testSearchByEngine(): void
    {
        $this->createSearchRequest([
            'o:engine' => 'item-set',
            'o:comment' => 'Engine test',
        ]);
        $results = $this->api()->search('search_requests', [
            'engine' => 'item-set',
        ])->getContent();
        $this->assertNotEmpty($results);
    }

    public function testDeleteSearchRequest(): void
    {
        $result = $this->createSearchRequest();
        $id = $result['id'];
        $this->api()->delete('search_requests', $id);
        // Remove from cleanup list since already deleted.
        $this->createdSearchRequests = array_diff(
            $this->createdSearchRequests,
            [$id]
        );
        $results = $this->api()->search('search_requests', [
            'id' => $id,
        ])->getContent();
        $this->assertEmpty($results);
    }

    public function testHydrateDoesNotSetCreated(): void
    {
        $fakeDate = '2000-01-01 00:00:00';
        $result = $this->createSearchRequest([
            'o:created' => $fakeDate,
        ]);
        $repr = $result['representation'];
        $created = $repr->created()->format('Y-m-d');
        $this->assertNotSame('2000-01-01', $created);
    }
}
