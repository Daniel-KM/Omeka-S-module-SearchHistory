<?php declare(strict_types=1);

namespace SearchHistory;

use Common\Stdlib\PsrMessage;

/**
 * @var Module $this
 * @var \Laminas\ServiceManager\ServiceLocatorInterface $services
 * @var string $newVersion
 * @var string $oldVersion
 *
 * @var \Omeka\Api\Manager $api
 * @var \Laminas\Log\Logger $logger
 * @var \Omeka\Settings\Settings $settings
 * @var \Doctrine\DBAL\Connection $connection
 * @var \Omeka\Settings\SiteSettings $siteSettings
 * @var \Doctrine\ORM\EntityManager $entityManager
 * @var \Omeka\Mvc\Controller\Plugin\Messenger $messenger
 */
$plugins = $services->get('ControllerPluginManager');
$api = $plugins->get('api');
$logger = $services->get('Omeka\Logger');
$settings = $services->get('Omeka\Settings');
$translate = $plugins->get('translate');
// $translator = $services->get('MvcTranslator');
$connection = $services->get('Omeka\Connection');
$messenger = $plugins->get('messenger');
$siteSettings = $services->get('Omeka\Settings\Site');
$entityManager = $services->get('Omeka\EntityManager');

if (!method_exists($this, 'checkModuleActiveVersion') || !$this->checkModuleActiveVersion('Common', '3.4.66')) {
    $message = new \Omeka\Stdlib\Message(
        $translate('The module %1$s should be upgraded to version %2$s or later.'), // @translate
        'Common', '3.4.66'
    );
    throw new \Omeka\Module\Exception\ModuleCannotInstallException((string) $message);
}

if (version_compare($oldVersion, '3.3.0.4', '<')) {
    $sql = <<<'SQL'
        ALTER TABLE `search_request`
            DROP FOREIGN KEY FK_B6466005A76ED395;
        ALTER TABLE `search_request`
            DROP FOREIGN KEY FK_B6466005F6BD1646;
        ALTER TABLE `search_request`
            CHANGE `user_id` `user_id` INT DEFAULT NULL,
            CHANGE `site_id` `site_id` INT DEFAULT NULL,
            CHANGE `comment` `comment` VARCHAR(190) DEFAULT NULL,
            CHANGE `engine` `engine` VARCHAR(190) DEFAULT NULL,
            CHANGE `modified` `modified` DATETIME DEFAULT NULL;
        ALTER TABLE `search_request`
            ADD CONSTRAINT FK_B6466005A76ED395 FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL;
        ALTER TABLE `search_request`
            ADD CONSTRAINT FK_B6466005F6BD1646 FOREIGN KEY (`site_id`) REFERENCES `site` (`id`) ON DELETE SET NULL;
        SQL;
    $connection->executeStatement($sql);
}
