<?php
/**
 * Link It plugin for Craft CMS 3.x
 *
 * Craft 3 port of the original Link It plugin
 *
 * @link      https://simple.com.au
 * @copyright Copyright (c) 2018 Simple Integrated Marketing
 */

namespace simpleteam\linkit;

use craft\elements\Entry;
use simpleteam\linkit\services\LinkItService as LinkItServiceService;
use simpleteam\linkit\fields\LinkItField as LinkItField;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;

use yii\base\Event;

/**
 * Class LinkIt
 *
 * @author    Simple Integrated Marketing
 * @package   LinkIt
 * @since     1.0.0
 *
 * @property  LinkItServiceService $linkItService
 */
class LinkIt extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var LinkIt
     */
    public static $plugin;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = LinkItField::class;
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Craft::info(
            Craft::t(
                'link-it',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );

    }

    // Protected Methods
    // =========================================================================

}
