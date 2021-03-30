<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace craft\azureblob;

use Craft;
use craft\base\FlysystemVolume;
use craft\behaviors\EnvAttributeParserBehavior;
use craft\helpers\ArrayHelper;
use craft\helpers\Assets;
use craft\helpers\DateTimeHelper;
use DateTime;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

/**
 * Class Volume
 *
 * @property mixed $settingsHtml
 * @property string $rootUrl
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0.0
 */
class Volume extends FlysystemVolume
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return 'Azure Blob Storage';
    }

    /**
     * @var bool Whether this is a local source or not. Defaults to false.
     */
    protected bool $isVolumeLocal = false;

    /**
     * @var string Subfolder to use
     */
    public string $subfolder = '';

    /**
     * @var string Azure connection string
     */
    public string $connectionString = '';

    /**
     * @var string Bucket selection mode ('choose' or 'manual')
     */
    public string $containerSelectionMode = 'choose';

    /**
     * @var string Container to use
     */
    public string $container = '';

    /**
     * @var string Cache expiration period.
     */
    public string $expires = '';

    /**
     * @inheritdoc
     */
    public function __construct(array $config = [])
    {
        if (array_key_exists('manualContainer', $config)) {
            if (isset($config['containerSelectionMode']) && $config['containerSelectionMode'] === 'manual') {
                $config['container'] = ArrayHelper::remove($config, 'manualContainer');
            } else {
                unset($config['manualContainer']);
            }
        }

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['parser'] = [
            'class' => EnvAttributeParserBehavior::class,
            'attributes' => [
                'connectionString',
                'container',
                'subfolder',
            ],
        ];
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['container', 'connectionString'], 'required'];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('azure-blob/_volume-settings', [
            'volume' => $this,
            'periods' => array_merge(['' => ''], Assets::periodList()),
        ]);
    }

    /**
     * Get the bucket list using the specified credentials.
     *
     * @param string $connectionString
     * @return array
     */
    public static function loadContainerList(string $connectionString): array
    {
        $client = static::client($connectionString);

        $containers = $client->listContainers()->getContainers();
        $containerList = [];

        foreach ($containers as $container) {
            $containerList[] = [
                'container' => $container->getName(),
                'urlPrefix' => $container->getUrl(),
            ];
        }

        return $containerList;
    }

    /**
     * @inheritdoc
     */
    public function getRootUrl()
    {
        return parent::getRootUrl() . $this->_subfolder();
    }

    /**
     * @inheritdoc
     * @return AzureBlobStorageAdapter
     */
    protected function createAdapter(): AzureBlobStorageAdapter
    {
        $client = static::client(Craft::parseEnv($this->connectionString));

        return new AzureBlobStorageAdapter($client, Craft::parseEnv($this->container), $this->_subfolder());
    }

    /**
     * Get the Azure Blob Storage client.
     *
     * @param string $connectionString Connection string to use
     * @return BlobRestProxy
     */
    protected static function client(string $connectionString): BlobRestProxy
    {
        return BlobRestProxy::createBlobService($connectionString);
    }

    /**
     * @inheritdoc
     */
    protected function addFileMetadataToConfig(array $config): array
    {
        if (!empty($this->expires) && DateTimeHelper::isValidIntervalString($this->expires)) {
            $expires = new DateTime();
            $now = new DateTime();
            $expires->modify('+' . $this->expires);
            $diff = $expires->format('U') - $now->format('U');
            $config['CacheControl'] = 'max-age=' . $diff;
        }

        return parent::addFileMetadataToConfig($config);
    }

    /**
     * Returns the parsed subfolder path
     *
     * @return string|null
     */
    private function _subfolder(): string
    {
        if ($this->subfolder && ($subfolder = rtrim(Craft::parseEnv($this->subfolder), '/')) !== '') {
            return $subfolder . '/';
        }

        return '';
    }
}
