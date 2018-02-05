<?php
/**
 * Link It plugin for Craft CMS 3.x
 *
 * Craft 3 port of the original Link It plugin
 *
 * @link      https://simple.com.au
 * @copyright Copyright (c) 2018 Simple Integrated Marketing
 */

namespace simpleteam\linkit\services;

use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use simpleteam\linkit\LinkIt;

use Craft;
use craft\base\Component;

/**
 * @author    Simple Integrated Marketing
 * @package   LinkIt
 * @since     1.0.0
 */
class LinkItService extends Component
{
    protected $plugin;
    protected $pluginHandle;
    // Public Methods
    // =========================================================================
    public function __construct()
    {
        parent::__construct();
        $this->plugin = Craft::$app->plugins->getPlugin('link-it');
        $this->pluginHandle = $this->plugin->getHandle();
    }
    public function getLinkItElementSources()
    {
        return array(
            'entry' => $this->_getElementSourcesWithUrls(Entry::class),
            'asset' => $this->_getElementSourcesWithUrls(Asset::class),
            'category' => $this->_getElementSourcesWithUrls(Category::class),
        );
    }


    private function _getElementSourcesWithUrls($type)
    {
        $sources = array();

        foreach ($type::sources() as $source)
        {
            if (!isset($source['heading']))
            {
                $sources[] = array(
                    'label' => $source['label'],
                    'value' => $source['key']
                );
            }
        }
        return $sources;
    }
}
