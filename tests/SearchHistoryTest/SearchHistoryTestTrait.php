<?php declare(strict_types=1);

namespace SearchHistoryTest;

use Laminas\ServiceManager\ServiceLocatorInterface;
use Omeka\Api\Manager as ApiManager;

/**
 * Shared test helpers for SearchHistory module tests.
 */
trait SearchHistoryTestTrait
{
    /**
     * @var bool Whether admin is logged in.
     */
    protected bool $isLoggedIn = false;

    /**
     * @var array IDs of search requests created during tests.
     */
    protected array $createdSearchRequests = [];

    /**
     * @var int|null ID of the test site.
     */
    protected ?int $testSiteId = null;

    /**
     * Get the service locator.
     */
    protected function getServiceLocator(): ServiceLocatorInterface
    {
        if (isset($this->application) && $this->application !== null) {
            return $this->application->getServiceManager();
        }
        return $this->getApplication()->getServiceManager();
    }

    /**
     * Get the API manager.
     */
    protected function api(): ApiManager
    {
        if ($this->isLoggedIn) {
            $this->ensureLoggedIn();
        }
        return $this->getServiceLocator()->get('Omeka\ApiManager');
    }

    /**
     * Login as admin user.
     */
    protected function loginAdmin(): void
    {
        $this->isLoggedIn = true;
        $this->ensureLoggedIn();
    }

    /**
     * Ensure admin is logged in on the current application instance.
     */
    protected function ensureLoggedIn(): void
    {
        $services = $this->getServiceLocator();
        $auth = $services->get('Omeka\AuthenticationService');
        if ($auth->hasIdentity()) {
            return;
        }
        $adapter = $auth->getAdapter();
        $adapter->setIdentity('admin@example.com');
        $adapter->setCredential('root');
        $auth->authenticate();
    }

    /**
     * Logout current user.
     */
    protected function logout(): void
    {
        $this->isLoggedIn = false;
        $auth = $this->getServiceLocator()
            ->get('Omeka\AuthenticationService');
        $auth->clearIdentity();
    }

    /**
     * Create a test search request via API.
     */
    protected function createSearchRequest(array $data = []): array
    {
        $defaults = [
            'o:user_id' => $this->getAdminUserId(),
            'o:comment' => 'Test search',
            'o:engine' => 'item',
            'o:query' => 'q=test',
        ];
        $data = array_merge($defaults, $data);
        // Avoid Doctrine error when site_id is null.
        if (!isset($data['o:site_id']) || !$data['o:site_id']) {
            unset($data['o:site_id']);
        }

        $response = $this->api()->create('search_requests', $data);
        $content = $response->getContent();
        $id = $content->id();
        $this->createdSearchRequests[] = $id;

        return [
            'id' => $id,
            'representation' => $content,
        ];
    }

    /**
     * Get admin user ID.
     */
    protected function getAdminUserId(): int
    {
        $services = $this->getServiceLocator();
        $auth = $services->get('Omeka\AuthenticationService');
        return $auth->getIdentity()->getId();
    }

    /**
     * Get or create a test site and return its slug.
     */
    protected function getTestSiteSlug(): string
    {
        if ($this->testSiteId) {
            $site = $this->api()->read('sites', $this->testSiteId)
                ->getContent();
            return $site->slug();
        }
        $sites = $this->api()->search('sites', ['limit' => 1])
            ->getContent();
        if ($sites) {
            $this->testSiteId = $sites[0]->id();
            return $sites[0]->slug();
        }
        $response = $this->api()->create('sites', [
            'o:title' => 'Test Site',
            'o:slug' => 'test',
            'o:theme' => 'default',
        ]);
        $site = $response->getContent();
        $this->testSiteId = $site->id();
        return $site->slug();
    }

    /**
     * Clean up created resources after test.
     */
    protected function cleanupResources(): void
    {
        foreach ($this->createdSearchRequests as $id) {
            try {
                $this->api()->delete('search_requests', $id);
            } catch (\Exception $e) {
            }
        }
        $this->createdSearchRequests = [];
    }
}
