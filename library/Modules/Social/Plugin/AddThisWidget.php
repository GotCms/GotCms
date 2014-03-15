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
 * @subpackage Social\Model
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Social\Plugin;

use Gc\Module\AbstractPlugin;
use Social\Model\AddThis;

/**
 * Social comment table
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage Social\Model
 */
class AddThisWidget extends AbstractPlugin
{
    /**
     * Configuration
     *
     * @var array
     */
    protected $config;

    /**
     * AddThis model
     *
     * @var AddThis
     */
    protected $addthis;

    /**
     * Constructor, prepare addthis widget and configuration
     *
     * @return void
     */
    public function __construct()
    {
        $this->addthis = $this->getServiceLocator()->get('AddThisModel');
        $this->config  = $this->addthis->getConfig();
    }

    /**
     * Generate configuration
     *
     * @return string
     */
    public function getConfig()
    {
        if (empty($this->config)) {
            return '';
        }

        $script = "\n<!-- AddThis Button Begin -->\n"
                 . '<script type="text/javascript">';

        $pub = (isset($this->config['profile_id'])) ? $this->config['profile_id'] : false ;
        if (!$pub) {
            $pub = 'gc-' . hash_hmac('md5', mt_rand(), 'addthis');
        }

        $pub = urlencode($pub);

        $addthisConfig                         = array();
        $addthisConfig['data_track_clickback'] = (
            isset($this->config['data_track_clickback']) and $this->config['data_track_clickback'] == true
        );

        if (isset($this->config['data_ga_property_id'])) {
            $addthisConfig['data_ga_property'] = $this->config['data_ga_property_id'];
            $addthisConfig['data_ga_social']   = true;
        }

        $addthisConfig['data_track_addressbar'] = (
            isset($this->config['data_track_addressbar']) and $this->config['data_track_addressbar'] == true
        );

        if (isset($this->config['language']) and strlen($this->config['language']) == 2) {
            $addthisConfig['ui_language'] = $this->config['language'];
        }

        $addthisConfigJson = isset($this->config['json_config']) ? $this->config['json_config'] : '';
        $script            = $this->mergeConfigWithJsonConfig($script, $addthisConfig, $addthisConfigJson);

        $script .= '</script>';
        $script .= '<script type="text/javascript" src="//s7.addthis.com/js/' . AddThis::ADDTHIS_VERSION
            . '/addthis_widget.js#pubid=' . $pub . '"></script>';

        return $script;
    }

    /**
     * Render addthis widget
     *
     * @param string $identifier Widget identifier
     * @param string $title      Title used for addthis share
     * @param string $url        Url used for addthis share
     *
     * @return string
     */
    public function __invoke($identifier, $title = '', $url = '')
    {
        if (!is_array($this->config['widgets'])) {
            return;
        }

        foreach ($this->config['widgets'] as $widget) {
            if ($identifier == $widget['identifier']) {
                $data = $widget;
                break;
            }
        }

        if (empty($data)) {
            return;
        }

        $styles   = $this->addthis->getDefaultStyles();
        $document = $this->getServiceLocator()->get('CurrentDocument');
        if (!empty($document)) {
            if (empty($url)) {
                $url = $document->getUrl(true);
            }

            if (empty($title)) {
                $title = $document->getName();
            }
        }

        $options = array();
        $addthisIdentifier  = 'addthis:url="' . $url . '" ';
        $addthisIdentifier .= 'addthis:title="' . $title . '"';
        if ($data['settings'] == 'custom_string') {
            $buttons = preg_replace('/<\s*div\s*/', '<div %1$s ', $data['custom_string']);
        } elseif (isset($styles[$data['settings']])) {
            if (!empty($data['chosen_list'])) {
                if ($data['settings'] == 'large_toolbox') {
                    $options['size'] = '32';
                } elseif ($data['settings'] == 'small_toolbox') {
                    $options['size'] = '16';
                }

                $options['type']     = $data['settings'];
                $options['services'] = $data['chosen_list'];
                $buttons             = $this->customToolbox($options, $addthisIdentifier);
            } else {
                $buttons = $styles[$data['settings']]['src'];
            }
        } else {
            return;
        }

        $content  = sprintf($buttons, $addthisIdentifier);
        $content .= $this->getConfig();

        return $content;

    }

    /**
     * Merge the Add this settings with that given using JSON format
     *
     * @param string $appendString  The string to build and return the script
     * @param array  $addthisConfig The setting array for add this config
     * @param string $jsonConfig    The JSON String
     *
     * @return string The string to build and return the script
     */
    protected function mergeConfigWithJsonConfig($appendString, $addthisConfig, $jsonConfig)
    {
        if (!empty($jsonConfig)) {
            $addthisconfigJsonList = json_decode($jsonConfig, true);
            if (!empty($addthisconfigJsonList) and !empty($addthisConfig)) {
                foreach ($addthisconfigJsonList as $keyJson => $valueJson) {
                    $addthisConfig[$keyJson] = $valueJson;
                }
            }
        }

        if (!empty($addthisConfig)) {
            $appendString .= 'var addthis_config = ' . json_encode($addthisConfig) . ';';
        }

        return $appendString;
    }

    /**
     * Buil custom toolbox
     *
     * @param array  $options           Array containing all options
     * @param string $addthisIdentifier Add this identifier
     *
     * @return string
     */
    protected function customToolbox($options, $addthisIdentifier)
    {
        $button = '';
        if (isset($options['type']) and $options['type'] != 'custom_string') {
            $outerClasses = 'addthis_toolbox addthis_default_style';
            if (isset($options['size']) and $options['size'] == '32') {
                $outerClasses .= ' addthis_32x32_style';
            }

            $button = '<div class="' . $outerClasses . '" ' . $addthisIdentifier . ' >';

            if (isset($options['services'])) {
                $services = explode(',', $options['services']);
                foreach ($services as $service) {
                    $service = trim($service);
                    if ($service == 'more' || $service == 'compact') {
                        if (isset($options['type']) and $options['type'] != 'fb_tw_p1_sc') {
                            $button .= '<a class="addthis_button_compact"></a>';
                        }
                    } elseif ($service == 'counter') {
                        if (isset($options['type']) and $options['type'] == 'fb_tw_p1_sc') {
                            $button .= '<a class="addthis_counter addthis_pill_style"></a>';
                        } else {
                            $button .= '<a class="addthis_counter addthis_bubble_style"></a>';
                        }
                    } elseif ($service == 'google_plusone') {
                        $button .= '<a class="addthis_button_google_plusone" g:plusone:size="medium"></a>';
                    } else {
                        $button .= '<a class="addthis_button_' . strtolower($service) . '"></a>';
                    }
                }
            }

            $button .= '</div>';
        }

        return $button;
    }
}
