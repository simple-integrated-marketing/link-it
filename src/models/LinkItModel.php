<?php
/**
 * Link It plugin for Craft CMS 3.x
 *
 * Craft 3 port of the original Link It plugin
 *
 * @link      https://simple.com.au
 * @copyright Copyright (c) 2018 Simple Integrated Marketing
 */

namespace simpleteam\linkit\models;

use craft\helpers\Template;
use simpleteam\linkit\LinkIt;

use Craft;
use craft\base\Model;
use Symfony\Component\Console\Helper\Helper;

/**
 * @author    Simple Integrated Marketing
 * @package   LinkIt
 * @since     1.0.0
 */
class LinkItModel extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    private $_entry;
    private $_asset;
    private $_category;

    public $type = false;
    public $value = false;
    public $defaultText = false;
    public $customText = false;
    public $target = false;
    public $siteId = '';

    // Public Methods
    // =========================================================================
    public function __toString()
    {
        $htmlLink = $this->getHtmlLink();
        return $htmlLink ? (string) $htmlLink : '';
    }

    public function getHtmlLink($attributes = false)
    {
        $url = $this->getUrl();
        $text = $this->getText();
        if($url && $text)
        {
            // Open  Link
            $htmlLink = '<a href="'.$url.'"';

            // Add Title (if not in attributes)
            if(!is_array($attributes) || !array_key_exists('title', $attributes))
            {
                $htmlLink .= ' title="'.$text.'"';
            }
            // Add Target (if not in attributes)
            if( ( !is_array($attributes) || !array_key_exists('title', $attributes) ) && $this->target )
            {
                $htmlLink .= ' target="'.$this->target.'"';
            }

            // Add Attributes
            if(is_array($attributes))
            {
                foreach ($attributes as $attr => $value)
                {
                    $htmlLink .= ' '.$attr.'="'.$value.'"';
                }
            }

            // Close Up Link
            $htmlLink .= '>'.$text.'</a>';

            // Get Raw
            return Template::raw($htmlLink);
        }
        return false;
    }


    public function getUrl()
    {
        $url = false;
        switch ($this->type)
        {
            case('entry'):
                $entry = $this->_entry ? $this->_entry : $this->getEntry();
                if($entry)
                {
                    $url = $entry->status == 'live' ? $entry->getUrl() : false;
                }
                break;
            case('asset'):
                $asset = $this->_asset ? $this->_asset : $this->getAsset();
                if($asset)
                {
                    $url = $asset->getUrl();
                }
                break;
            case('category'):
                $category = $this->_category ? $this->_category : $this->getCategory();
                if($category)
                {
                    $url = $category->enabled ? $category->getUrl() : false;
                }
                break;
            case('custom'):
                $url = $this->value;
                break;
            case('tel'):
                $url = 'tel:'.str_replace(' ', '', $this->value);
                break;
            case('email'):
                $url = 'mailto:'.$this->value;
                break;
            default:
                break;
        }
        return $url;
    }


    public function getText()
    {
        if($this->customText)
        {
            return $this->customText;
        }

        if($this->defaultText)
        {
            return $this->defaultText;
        }

        $text = '';
        switch ($this->type)
        {
            case('entry'):
                $entry = $this->_entry ? $this->_entry : $this->getEntry();
                if($entry)
                {
                    $text = $entry->title;
                }
                break;
            case('asset'):
                $asset = $this->_asset ? $this->_asset : $this->getAsset();
                if($asset)
                {
                    $text = $asset->title;
                }
                break;
            case('category'):
                $category = $this->_category ? $this->_category : $this->getCategory();
                if($category)
                {
                    $text = $category->title;
                }
                break;
            default:
                break;

        }
        return $text;
    }


    public function getElement()
    {

        switch ($this->type)
        {
            case('entry'):
                $element = $this->entry;
                break;
            case('asset'):
                $element = $this->asset;
                break;
            case('category'):
                $element = $this->category;
                break;
            default:
                $element = false;
        }
        return $element;
    }


    public function getEntry()
    {
        if($this->type != 'entry')
        {
            return false;
        }

        if(!$this->_entry)
        {
            $id = is_array($this->value) ? $this->value[0] : false;
            $siteId = isset($this->siteId) ? $this->siteId : null;
            if( $id && $entry =  Craft::$app->entries->getEntryById($id, $siteId) )
            {
                $this->_entry = $entry;
            }
        }
        return $this->_entry;
    }


    public function getAsset()
    {
        if($this->type != 'asset')
        {
            return false;
        }

        if(!$this->_asset)
        {
            $id = is_array($this->value) ? $this->value[0] : false;
            $siteId = isset($this->siteId) ? $this->siteId : null;
            if( $id && $asset = Craft::$app->assets->getAssetById($id, $siteId) )
            {
                $this->_asset = $asset;
            }
        }
        return $this->_asset;
    }


    public function getCategory()
    {
        if($this->type != 'category')
        {
            return false;
        }

        if(!$this->_category)
        {
            $id = is_array($this->value) ? $this->value[0] : false;
            if( $id && $category = Craft::$app->categories->getCategoryById($id) )
            {
                $this->_category = $category;
            }
        }
        return $this->_category;
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        switch($this->type)
        {
            case('email'):
                return [
                    ['value','email','message'=>Craft::t('link-it','Please enter a valid email address.')]
                ];

            case('tel'):
                return [
                    ['value','match','pattern'=>'/^[0-9+\(\)#\.\s\/ext-]+$/','message'=>Craft::t('link-it','Please enter a valid telephone.')]
                ];

            case('custom'):
                return [
                    ['value','url','message'=>Craft::t('link-it','Please enter a valid url.')]
                ];

            case('entry'):
                return [
                    ['value','required','message'=>Craft::t('link-it','Please select an entry.')]
                ];

            case('asset'):
                return [
                    ['value','required','message'=>Craft::t('link-it','Please select an asset.')]
                ];

            case('category'):
                return [
                    ['value','required','message'=>Craft::t('link-it','Please select a category.')]
                ];

            default:
                return [];
        }
    }
}
