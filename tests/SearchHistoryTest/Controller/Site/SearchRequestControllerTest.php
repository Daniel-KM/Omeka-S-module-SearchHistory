<?php declare(strict_types=1);

namespace SearchHistoryTest\Controller\Site;

use CommonTest\AbstractHttpControllerTestCase;
use SearchHistoryTest\SearchHistoryTestTrait;

class SearchRequestControllerTest extends AbstractHttpControllerTestCase
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

    public function testAddActionRequiresPost(): void
    {
        $slug = $this->getTestSiteSlug();
        $this->dispatch("/s/$slug/search-history/add");
        $this->assertResponseStatusCode(405);
    }

    public function testAddActionRequiresQuery(): void
    {
        $slug = $this->getTestSiteSlug();
        $this->dispatch(
            "/s/$slug/search-history/add",
            'POST',
            ['engine' => 'item']
        );
        $json = json_decode($this->getResponse()->getBody(), true);
        $this->assertNotNull($json, 'Response is not valid JSON');
        $this->assertSame('fail', $json['status']);
        $this->assertArrayHasKey('query', $json['data']);
    }

    public function testAddActionRequiresEngine(): void
    {
        $slug = $this->getTestSiteSlug();
        $this->dispatch(
            "/s/$slug/search-history/add",
            'POST',
            ['query' => 'q=test']
        );
        $json = json_decode($this->getResponse()->getBody(), true);
        $this->assertNotNull($json, 'Response is not valid JSON');
        $this->assertSame('fail', $json['status']);
        $this->assertArrayHasKey('engine', $json['data']);
    }

    public function testAddActionCreatesSearchRequest(): void
    {
        $slug = $this->getTestSiteSlug();
        $this->dispatch(
            "/s/$slug/search-history/add",
            'POST',
            [
                'query' => 'q=test',
                'engine' => 'item',
                'comment' => 'My test',
            ]
        );
        $json = json_decode($this->getResponse()->getBody(), true);
        $this->assertNotNull($json, 'Response is not valid JSON');
        $this->assertSame('success', $json['status']);
        $this->assertArrayHasKey('search_request', $json['data']);
        if (isset($json['data']['search_request']['o:id'])) {
            $this->createdSearchRequests[] =
                $json['data']['search_request']['o:id'];
        }
    }

    public function testDeleteActionRequiresPost(): void
    {
        $slug = $this->getTestSiteSlug();
        $this->dispatch("/s/$slug/search-history/1/delete");
        $this->assertResponseStatusCode(405);
    }

    public function testClearActionRequiresPost(): void
    {
        $slug = $this->getTestSiteSlug();
        $this->dispatch("/s/$slug/search-history/clear");
        $this->assertResponseStatusCode(405);
    }

    public function testClearActionDeletesSearchRequests(): void
    {
        $this->createSearchRequest(['o:comment' => 'To clear']);
        $slug = $this->getTestSiteSlug();
        $this->dispatch(
            "/s/$slug/search-history/clear",
            'POST',
            ['period' => 'all']
        );
        $json = json_decode($this->getResponse()->getBody(), true);
        $this->assertNotNull($json, 'Response is not valid JSON');
        $this->assertSame('success', $json['status']);
        $this->createdSearchRequests = [];
    }
}
