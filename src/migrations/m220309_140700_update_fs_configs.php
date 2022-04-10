<?php

namespace craft\azureblob\migrations;

use Craft;
use craft\azureblob\Fs;
use craft\db\Migration;
use craft\services\ProjectConfig;

/**
 * m220309_140700_update_fs_configs migration.
 */
class m220309_140700_update_fs_configs extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Don't make the same changes twice
        $schemaVersion = Craft::$app->getProjectConfig()->get('plugins.google-cloud.schemaVersion', true);
        if (version_compare($schemaVersion, '2.0', '>=')) {
            return true;
        }

        // Update any old configs
        $projectConfig = Craft::$app->getProjectConfig();
        $fsConfigs = $projectConfig->get(ProjectConfig::PATH_FS) ?? [];

        foreach ($fsConfigs as $uid => $config) {
            if ($config['type'] === 'craft\azureblob\Volume') {
                $config['type'] = Fs::class;
                $projectConfig->set(sprintf('%s.%s', ProjectConfig::PATH_FS, $uid), $config);
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m220309_140700_update_fs_configs cannot be reverted.\n";
        return false;
    }
}
