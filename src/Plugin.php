<?php

namespace craft\azureblob;

use craft\base\Plugin as BasePlugin;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Volumes;
use yii\base\Event;


/**
 * Plugin represents the Azure Blob Storage plugin.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0
 */
class Plugin extends BasePlugin
{
    // Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    public $schemaVersion = '1.0';


    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        Event::on(Volumes::class, Volumes::EVENT_REGISTER_VOLUME_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = Volume::class;
        });
    }
}
