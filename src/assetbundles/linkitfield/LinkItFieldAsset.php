<?php
/**
 * Link It plugin for Craft CMS 3.x
 *
 * Craft 3 port of the original Link It plugin
 *
 * @link      https://simple.com.au
 * @copyright Copyright (c) 2018 Simple Integrated Marketing
 */

namespace simpleteam\linkit\assetbundles\linkitfield;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Simple Integrated Marketing
 * @package   LinkIt
 * @since     1.0.0
 */
class LinkItFieldAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@simpleteam/linkit/assetbundles/linkitfield/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/LinkItField.js',
        ];

        $this->css = [
            'css/LinkItField.css',
        ];

        parent::init();
    }
}
