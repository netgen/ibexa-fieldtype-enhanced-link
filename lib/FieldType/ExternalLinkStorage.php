<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLink\FieldType;

use Ibexa\Contracts\Core\FieldType\GatewayBasedStorage;
use Ibexa\Contracts\Core\FieldType\StorageGatewayInterface;
use Ibexa\Contracts\Core\Persistence\Content\Field;
use Ibexa\Contracts\Core\Persistence\Content\VersionInfo;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ExternalLinkStorage extends GatewayBasedStorage
{
    protected LoggerInterface $logger;

    /** @var \Netgen\IbexaFieldTypeEnhancedLink\FieldType\ExternalLinkStorage\Gateway */
    protected $gateway;

    public function __construct(StorageGatewayInterface $gateway, ?LoggerInterface $logger = null)
    {
        parent::__construct($gateway);
        $this->logger = $logger ?? new NullLogger();
    }

    public function storeFieldData(VersionInfo $versionInfo, Field $field, array $context)
    {
        $url = $field->value->externalData;

        if (empty($url)) {
            return false;
        }

        $map = $this->gateway->getUrlIdMap([$url]);

        if (isset($map[$url])) {
            $urlId = $map[$url];
        } else {
            $urlId = $this->gateway->insertUrl($url);
        }

        $this->gateway->linkUrl($urlId, $field->id, $versionInfo->versionNo);

        $this->gateway->unlinkUrl(
            $field->id,
            $versionInfo->versionNo,
            [$urlId],
        );

        $field->value->data['id'] = $urlId;

        // Signals that the Value has been modified and that an update is to be performed
        return true;
    }

    public function getFieldData(VersionInfo $versionInfo, Field $field, array $context)
    {
        if ($field->value->data === null) {
            return;
        }

        $id = $field->value->data['id'];
        if (empty($id) || $field->value->data['type'] !== Type::LINK_TYPE_EXTERNAL) {
            // $field->value->externalData = null;

            return;
        }

        $map = $this->gateway->getIdUrlMap([$id]);

        // URL id is not in the DB
        if (!isset($map[$id])) {
            $this->logger->error("URL with ID '{$id}' not found");
        }

        $field->value->externalData = $map[$id] ?? null;
    }

    public function deleteFieldData(VersionInfo $versionInfo, array $fieldIds, array $context)
    {
        foreach ($fieldIds as $fieldId) {
            $this->gateway->unlinkUrl($fieldId, $versionInfo->versionNo);
        }
    }

    public function hasFieldData()
    {
        return true;
    }

    public function getIndexData(VersionInfo $versionInfo, Field $field, array $context)
    {
    }
}
