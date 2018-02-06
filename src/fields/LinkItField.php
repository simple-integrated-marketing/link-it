<?php
/**
 * Link It plugin for Craft CMS 3.x
 *
 * Craft 3 port of the original Link It plugin
 *
 * @link      https://simple.com.au
 * @copyright Copyright (c) 2018 Simple Integrated Marketing
 */

namespace simpleteam\linkit\fields;

use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;
use simpleteam\linkit\LinkIt;
use simpleteam\linkit\assetbundles\linkitfield\LinkItFieldAsset;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Db;
use simpleteam\linkit\models\LinkItContainer;
use simpleteam\linkit\models\LinkItModel;
use yii\db\Schema;
use craft\helpers\Json;

/**
 * @author    Simple Integrated Marketing
 * @package   LinkIt
 * @since     1.0.0
 */
class LinkItField extends Field
{
    // Public Properties
    // =========================================================================
    public $types;
    public $allowCustomText;
    public $defaultText;
    public $allowTarget;
    public $max;

    public $entrySources;
    public $entrySelectionLabel = 'Select an entry';

    public $assetSources;
    public $assetSelectionLabel = 'Select an asset';

    public $categorySources;
    public $categorySelectionLabel = 'Select a category';

    public $productSources;
    public $productSelectionLabel = 'Select a product';

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('link-it', 'Link It Field');
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            ['types','required','message'=>Craft::t('link-it','Please select at least 1 link type.')],
            ['max','number'],
            ['entrySources','required','when'=>function($model){ return is_array($model->types) && in_array('entry', $model->types); },'message'=>Craft::t('link-it','Please select at least 1 entry source.')],
            ['assetSources','required','when'=>function($model){ return is_array($model->types) && in_array('asset', $model->types); },'message'=>Craft::t('link-it','Please select at least 1 asset source.')],
            ['categorySources','required','when'=>function($model){ return is_array($model->types) && in_array('category', $model->types); },'message'=>Craft::t('link-it','Please select at least 1 category source.')],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_TEXT;
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        if ($value instanceof LinkItContainer) {
            return $value;
        }
        // From database
        if( is_string($value) )
        {
            $arrayOfValue = json_decode($value, TRUE);
            $settings = $this;
            $linkContainer = new LinkItContainer();
            foreach ($arrayOfValue as $LinkItModelData) {
                $link = new LinkItModel();
                $link->type = isset($LinkItModelData['type']) && $LinkItModelData['type'] != '' ? $LinkItModelData['type'] : false;
                $link->value = isset($LinkItModelData['value']) ? $LinkItModelData['value'] : false;
                $link->customText = isset($LinkItModelData['customText']) ? $LinkItModelData['customText'] : false;
                $link->defaultText = $settings->defaultText;
                $link->target = isset($LinkItModelData['target']) ? ($LinkItModelData['target'] ? '_blank' : false) : false;
                $link->siteId = isset($element->siteId) ? $element->siteId : Craft::$app->sites->currentSite->handle;
                $linkContainer->addLink($link);
            }
            return $linkContainer;
        }
        // From Submission
        if (is_array($value)) {
            $settings = $this;
            $linkContainer = new LinkItContainer();
            foreach ($value as $LinkItModelData) {
                $link = new LinkItModel();
                $link->type = isset($LinkItModelData['type']) && $LinkItModelData['type'] != '' ? $LinkItModelData['type'] : false;
                if (!$link->type) {
                    continue;
                }
                $link->value = isset($LinkItModelData[$link->type]) ? $LinkItModelData[$link->type] : false;
                $link->customText = isset($LinkItModelData['customText']) ? $LinkItModelData['customText'] : false;
                $link->defaultText = $settings->defaultText;
                $link->target = isset($LinkItModelData['target']) ? ($LinkItModelData['target'] ? '_blank' : false) : false;
                $link->siteId = isset($element->siteId) ? $element->siteId : Craft::$app->sites->currentSite->handle;
                $linkContainer->addLink($link);
            }
            return $linkContainer;
        }



    }

    /**
     * @inheritdoc
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        if ($value instanceof LinkItContainer) {
            return json_encode($value->links);
        } else {
            return parent::serializeValue($value,$element);
        }
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        // Render the settings template
        return Craft::$app->getView()->renderTemplate(
            'link-it/_components/fields/LinkItField_settings',
            [
                'settings'                  => $this,
                'types'                     => $this->_getAvailableLinkItTypes(),
                'elementSources'            => LinkIt::getInstance()->linkItService->getLinkItElementSources(),
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        // Register our asset bundle
        Craft::$app->getView()->registerAssetBundle(LinkItFieldAsset::class);

        // Get our id and namespace
        $settings = $this->getSettings();
        $availableTypes = $this->_getAvailableLinkItTypes();
        $types = [''=>Craft::t('link-it','Link To...')];
        foreach($settings['types'] as $type)
        {
            $types[$type] = $availableTypes[$type];
        }

        // current selected CP locale
        $siteId = isset($element->locale)?$element->locale: Craft::$app->sites->currentSite->handle;

        // Element Select Options
        $elementSelectSettings = array(
            'entry' => array(
                'elementType' => Entry::class,
                'elements' => null,
                'sources' => $settings['entrySources'],
                'criteria' => array(
                    'status' => null,
                    'site' => $siteId,
                ),
                'limit' => 1,
                'addButtonLabel' => $settings['entrySelectionLabel'],
            ),
            'asset' => array(
                'elementType' => Asset::class,
                'elements' => null,
                'sources' => $settings['assetSources'],
                'criteria' => array(
                    'status' => null,
                    'site' => $siteId,
                ),
                'limit' => 1,
                'addButtonLabel' => $settings['assetSelectionLabel'],
            ),
            'category' => array(
                'elementType' => Category::class,
                'elements' => null,
                'sources' => $settings['categorySources'],
                'criteria' => array(
                    'status' => null,
                    'site' => $siteId,
                ),
                'limit' => 1,
                'addButtonLabel' => $settings['categorySelectionLabel'],
            )
        );

        $id = Craft::$app->getView()->formatInputId($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);
        $jsCodeInit = "new LinkIt('" . $namespacedId . "-__LINKITBLOCK__');";

        Craft::$app->getView()->startJsBuffer();
        $htmlAddNew = Craft::$app->getView()->namespaceInputs(Craft::$app->getView()->renderTemplate('link-it/_components/fields/LinkItField_input_single',
            [
                'name' => $this->handle,
                'value' => null,
                'field' => $this,
                'id' => $id,
                'namespacedId' => $namespacedId,
                'settings' => $settings,
                'types' => $types,
                'elementSelectSettings' => $elementSelectSettings,
                'jsCode' => $jsCodeInit,
            ]));
        $jsAddNew = Craft::$app->getView()->clearJsBuffer();


        Craft::$app->view->registerJs("addLinkItBlock = typeof addLinkItBlock == 'undefined'?{}:addLinkItBlock;");
        Craft::$app->view->registerJs("addLinkItBlock['{$namespacedId}'] = new AddLinkItBlock('{$namespacedId}',".json_encode($htmlAddNew).",".json_encode($jsAddNew).");");
        Craft::$app->view->registerJs("$('.link-it-sortable').each(function(){Sortable.create(this,{handle:'.move.icon'});})");





        $linksInfo = [];
        if ($value instanceof LinkItContainer) {
            foreach ($value->links as $key => $link) {
                // Element Select Options
                $elementSelectSettingsPerLink = array(
                    'entry' => array(
                        'elementType' => Entry::class,
                        'elements' => $link && $link->entry ? array($link->entry) : null,
                        'sources' => $settings['entrySources'],
                        'criteria' => array(
                            'status' => null,
                            'site' => $siteId,
                        ),
                        'limit' => 1,
                        'addButtonLabel' => $settings['entrySelectionLabel'],
                    ),
                    'asset' => array(
                        'elementType' => Asset::class,
                        'elements' => $link && $link->asset ? array($link->asset) : null,
                        'sources' => $settings['assetSources'],
                        'criteria' => array(
                            'status' => null,
                            'site' => $siteId,
                        ),
                        'limit' => 1,
                        'addButtonLabel' => $settings['assetSelectionLabel'],
                    ),
                    'category' => array(
                        'elementType' => Category::class,
                        'elements' => $link && $link->category ? array($link->category) : null,
                        'sources' => $settings['categorySources'],
                        'criteria' => array(
                            'status' => null,
                            'site' => $siteId,
                        ),
                        'limit' => 1,
                        'addButtonLabel' => $settings['categorySelectionLabel'],
                    )
                );
                $jsCode = "new LinkIt('" . $namespacedId . "-{$key}');";

                $linksInfo[] = [
                    'key' => $key,
                    'name' => $this->handle,
                    'value' => $link,
                    'field' => $this,
                    'id' => $id,
                    'namespacedId' => $namespacedId,
                    'settings' => $settings,
                    'types' => $types,
                    'elementSelectSettings' => $elementSelectSettingsPerLink,
                    'jsCode' => $jsCode,
                ];
            }
        }

        // Render the input template
        return Craft::$app->getView()->renderTemplate(
            'link-it/_components/fields/LinkItField_input',
            [
                'defaultLink' =>
                [
                    'name' => $this->handle,
                    'value' => null,
                    'field' => $this,
                    'id' => $id,
                    'namespacedId' => $namespacedId,
                    'settings' => $settings,
                    'types' => $types,
                    'elementSelectSettings' => $elementSelectSettings,
                    'jsCode' => $jsCodeInit,
                ]
                ,
                'links' => $linksInfo
            ]
        );
    }

    // Private Methods
    // =========================================================================

    private function _getAvailableLinkItTypes()
    {
        $types = $this->_getLinkItTypes();
        $sources = LinkIt::getInstance()->linkItService->getLinkItElementSources();
        if(!$sources['entry'])
        {
            unset($types['entry']);
        }
        if(!$sources['category'])
        {
            unset($types['category']);
        }
        if(!$sources['asset'])
        {
            unset($types['asset']);
        }
        return $types;
    }

    private function _getLinkItTypes()
    {
        $types = array(
            'email' => Craft::t('link-it','Email Address'),
            'tel' => Craft::t('link-it','Phone Number'),
            'custom' => Craft::t('link-it','Custom URL'),
            'entry' => Craft::t('link-it','Entry'),
            'category' => Craft::t('link-it','Category'),
            'asset' => Craft::t('link-it','Asset'),
        );

        return $types;
    }
}
