<?php declare(strict_types=1);

namespace SearchHistoryTest\Controller\Site;

use CommonTest\AbstractHttpControllerTestCase;
use SearchHistoryTest\SearchHistoryTestTrait;

class GuestBoardControllerTest extends AbstractHttpControllerTestCase
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

    public function testShowActionDisplaysPage(): void
    {
        $this->createSearchRequest(['o:comment' => 'Visible search']);
        $slug = $this->getTestSiteSlug();
        $this->dispatch("/s/$slug/guest/search-history");
        $this->assertResponseStatusCode(200);
        $this->assertControllerName(
            'SearchHistory\Controller\Site\GuestBoard'
        );
        $this->assertActionName('show');
    }
}
