<?php
/**
 * This source file is part of GotCms.
 *
 * GotCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GotCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with GotCms. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage AddThis\Model
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Social\Model;

use Gc\Core\Object;
use Gc\Core\Config as CoreConfig;
use Gc\Exception;

/**
 * AddThis comment table
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage AddThis\Model
 */
class AddThis extends Object
{
    /**
     * Config table
     *
     * @var CoreConfig
     */
    protected $configTable;

    /**
     * Configuration
     *
     * @var array
     */
    protected $config;

    /**
     * Default options
     *
     * @var array
     */
    protected $defaultOptions = array(
        'profile_id'            => '',
        'username'              => '',
        'password'              => '',
        'show_stats'            => true,
        'language'              => 'en',
        'data_ga_property_id'   => '',
        'data_track_clickback'  => '',
        'data_track_addressbar' => '',
        'config_json'           => '',
        'widgets'               => array(),
    );

    /**
     * @const Addthis version
     */
    const ADDTHIS_VERSION = 300;

    /**
     * Constructor
     *
     * @param CoreConfig $configTable Config table
     *
     * @return void
     */
    public function __construct(CoreConfig $configTable = null)
    {
        if ($configTable === null) {
            throw new Exception('Invalid parameter, $config must be an instance of \Gc\Core\Config');
        }

        $this->configTable = $configTable;
        $config            = @unserialize($this->configTable->getValue('module_addthis'));
        $this->config      = array_merge($this->defaultOptions, $config ? $config : array());

        parent::__construct();
    }

    /**
     * Return default styles
     *
     * @return array
     */
    public function getDefaultStyles()
    {
        return array(
            'large_toolbox' => array(
                'src' => '<div class="addthis_toolbox addthis_default_style addthis_32x32_style" %1$s>'
                    . '<a class="addthis_button_facebook"></a><a class="addthis_button_twitter"></a>'
                    . '<a class="addthis_button_email"></a><a class="addthis_button_pinterest_share"></a>'
                    . '<a class="addthis_button_compact"></a><a class="addthis_counter addthis_bubble_style">'
                    . '</a></div>',
                'img' => 'toolbox-large.png',
                'name' => 'Large Toolbox',
            ),
            'small_toolbox' => array(
                'src' => '<div class="addthis_toolbox addthis_default_style addthis_" %1$s>'
                    . '<a class="addthis_button_facebook"></a><a class="addthis_button_twitter"></a>'
                    . '<a class="addthis_button_email"></a><a class="addthis_button_pinterest_share"></a>'
                    . '<a class="addthis_button_compact"></a><a class="addthis_counter addthis_bubble_style">'
                    . '</a></div>',
                'img' => 'toolbox-small.png',
                'name' => 'Small Toolbox',
            ),
            'fb_tw_p1_sc' => array(
                'src' => '<div class="addthis_toolbox addthis_default_style" %1$s>'
                    . '<a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>'
                    . '<a class="addthis_button_tweet"></a><a class="addthis_button_pinterest_pinit"></a>'
                    . '<a class="addthis_counter addthis_pill_style"></a></div>',
                'img' => 'horizontal_share_rect.png' ,
                'name' => 'Like, Tweet, +1, Share',
            ),
            'button' => array(
                'src' => '<div><a class="addthis_button" href="//addthis.com/bookmark.php?v='
                    . self::ADDTHIS_VERSION . '" %1$s><img src="//cache.addthis.com/cachefly/'
                    . 'static/btn/v2/lg-share-en.gif" width="125" height="16" alt="Bookmark and Share" '
                    . 'style="border:0"/></a></div>',
                'img' => 'horizontal_share.png',
                'name' => 'Classic Share Button',
            ),
            'custom_string' => array(
                'name' => 'Custom string',
            ),
        );
    }
    /**
     * Return languages list
     *
     * @return array
     */
    public function getLanguages()
    {
        return array(
            '' => 'Automatic',
            'af' => 'Afrikaaner',
            'ar' => 'Arabic',
            'zh' => 'Chinese',
            'cs' => 'Czech',
            'da' => 'Danish',
            'nl' => 'Dutch',
            'en' => 'English',
            'fa' => 'Farsi',
            'fi' => 'Finnish',
            'fr' => 'French',
            'ga' => 'Gaelic',
            'de' => 'German',
            'el' => 'Greek',
            'he' => 'Hebrew',
            'hi' => 'Hindi',
            'it' => 'Italian',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'lv' => 'Latvian',
            'lt' => 'Lithuanian',
            'no' => 'Norwegian',
            'pl' => 'Polish',
            'pt' => 'Portugese',
            'ro' => 'Romanian',
            'ru' => 'Russian',
            'sk' => 'Slovakian',
            'sl' => 'Slovenian',
            'es' => 'Spanish',
            'sv' => 'Swedish',
            'th' => 'Thai',
            'ur' => 'Urdu',
            'cy' => 'Welsh',
            'vi' => 'Vietnamese'
        );
    }

    /**
     * Add widgets
     *
     * @param array   $data            New widgets
     * @param boolean $removeUndefined Check if yes or no undefined widget will
     *                                 be removed
     *
     * @return boolean
     */
    public function addWidgets(array $data, $removeUndefined = false)
    {
        if ($removeUndefined === true) {
            $widgets = array();
            foreach ($data as $widget) {
                $widgets[] = $widget;
            }
        } else {
            $widgets = $this->config['widgets'];
            foreach ($data as $fieldsetName => $fieldset) {
                $found = false;
                if (!empty($widgets)) {
                    foreach ($widgets as $idx => $widget) {
                        if ($widget['identifier'] == $fieldset['identifier']) {
                            $widgets[$idx] = $fieldset;
                            $found         = true;
                            break;
                        }
                    }
                }

                if (!$found) {
                    $widgets[] = $fieldset;
                }
            }
        }

        $this->config['widgets'] = $widgets;
        return $this->saveConfig();
    }

    /**
     * Edit configuration and save it
     *
     * @param array $data New config data
     *
     * @return boolean
     */
    public function setConfig(array $data)
    {
        $this->config = array_merge($this->config, $data['config']);
        return $this->saveConfig();
    }

    /**
     * Save configuration to core_config_data table
     *
     * @return boolean
     */
    public function saveConfig()
    {
        return $this->configTable->setValue('module_addthis', serialize($this->config));
    }

    /**
     * Return configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Return widgets
     *
     * @return array
     */
    public function getWidgets()
    {
        return $this->config['widgets'];
    }
}
