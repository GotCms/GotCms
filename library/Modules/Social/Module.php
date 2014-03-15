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
 * @subpackage Social
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Social;

use Gc\Module\AbstractModule;
use Gc\Document\Model as DocumentModel;
use Zend\EventManager\EventInterface as Event;
use Zend\Http\Client;

/**
 * Social module bootstrap
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage Social
 */
class Module extends AbstractModule
{
    /**
     * Get module configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Boostrap
     *
     * @param Event $e Event
     *
     * @return void
     */
    public function onBootstrap(Event $e)
    {
        $this->events()->attach('Admin\Controller\IndexController', 'dashboard', array($this, 'dashboard'));
    }

    /**
     * Dashboard widget
     *
     * @param Event $e Event
     *
     * @return false|null
     */
    public function dashboard(Event $e)
    {
        $addthis = $e->getTarget()->getServiceLocator()->get('AddThisModel');
        $options = $addthis->getConfig();

        if (empty($options['show_stats'])) {
            return false;
        }

        if (isset($options['username'])) {
            $username = $options['username'];
        } else {
            return false;
        }

        if (isset($options['password'])) {
            $password = $options['password'];
        } else {
            return false;
        }

        if (isset($options['profile_id'])) {
            $profile = $options['profile_id'];
        } else {
            return false;
        }

        $_services = array(
            'netvibes'     => 'Netvibes',
            'google'       => 'Google Reader',
            'yahoo'        => 'Yahoo',
            'rojo'         => 'Rojo',
            'aol'          => 'AOL',
            'newsgator-on' => 'Newsgator Online',
            'pluck-on'     => 'Pluck Online',
            'bloglines'    => 'Bloglines',
            'feedlounge'   => 'Feedlounge',
            'newsburst'    => 'Newsburst',
            'msn'          => 'MSN',
            'winlive'      => 'Windows Live',
            'technorati'   => 'Technorati',
            'pageflakes'   => 'Pageflakes',
            'newsalloy'    => 'News Alloy',
            'feedreader'   => 'FeedReader',
            'mymsn'        => 'My MSN',
            'newsisfree'   => 'Newsisfree',
            'feeddemon'    => 'FeedDemon',
            'netnewswire'  => 'NetNewWire',
            'pluck'        => 'Pluck',
            'newsgator'    => 'NewsGator',
            'sharpreader'  => 'SharpReader',
            'awasu'        => 'Awasu',
            'myearthlink'  => 'myEarthLink',
            'rss'          => 'Direct Feed Link',
            'googlebuzz'   => 'Google Buzz',
            'youtube'      => 'YouTube',
            'facebook'     => 'Facebook',
            'flickr'       => 'Flickr',
            'twitter'      => 'Twitter',
            'linkedin'     => 'LinkedIn'
        );


        $document = DocumentModel::fromUrlKey('');
        $domain   = parse_url($document->getUrl(true), PHP_URL_HOST);

        $requests = array(
            array('metric' => 'shares',  'dimension' => '',   'domain' => $domain, 'period' => 'day'),
            array('metric' => 'shares', 'dimension' => '',   'domain' => $domain, 'period' => 'week'),
            array('metric' => 'shares', 'dimension' => '',    'domain' => $domain, 'period' => 'month'),
            array('metric' => 'clickbacks', 'dimension' => '', 'domain' => $domain, 'period' => 'day'),
            array('metric' => 'clickbacks', 'dimension' => '', 'domain' => $domain, 'period' => 'week'),
            array('metric' => 'clickbacks', 'dimension' => '', 'domain' => $domain, 'period' => 'month'),
            array('metric' => 'shares', 'dimension' => 'service' , 'domain' => $domain, 'period' => 'month'),
            array('metric' => 'clickbacks', 'dimension' => 'service', 'domain' => $domain, 'period' => 'month'),
            array('metric' => 'shares', 'dimension' => 'url' , 'domain' => $domain, 'period' => 'month'),
            array('metric' => 'clickbacks', 'dimension' => 'url', 'domain' => $domain, 'period' => 'month'),
        );


        foreach ($requests as $request) {
            $dimension = $metric = $domain = $period = '';
            extract($request);
            $dimension                             = ($dimension != '') ? '/' . $dimension : '';
            $stats[$metric . $dimension . $period] = $this->executeQuery(
                $metric,
                $dimension,
                $domain,
                $period,
                $username,
                $password,
                $profile
            );

            if (!$stats[$metric . $dimension . $period]) {
                $data = array('error' => true);
                break;
            } elseif ($stats[$metric . $dimension . $period]->getStatusCode() == 401) {
                $data = array('unauthorized' => true);
            } elseif ($stats[$metric . $dimension . $period]->getStatusCode() == 500) {
                $data = array('error' => true);
                break;
            } elseif ($stats[$metric . $dimension . $period]->getStatusCode() == 501) {
                $data = array('error' => true);
                break;
            }
        }

        if (empty($data) and !empty($stats['sharesday']) and $stats['sharesday']->getStatusCode() == 200) {
            if ($stats['sharesmonth']->getBody() == '[]') {
                $data = array('noData' => true);
            } else {
                $shareurls               = json_decode($stats['shares/urlmonth']->getBody());
                $clickbackurls           = json_decode($stats['clickbacks/urlmonth']->getBody());
                $yesterday['shares']     = json_decode($stats['sharesday']->getBody());
                $yesterday['shares']     = isset($yesterday['shares'][0]->shares) ?
                    $yesterday['shares'][0]->shares :
                    '';
                $yesterday['clickbacks'] = json_decode($stats['clickbacksday']->getBody());
                $yesterday['clickbacks'] = isset($yesterday['clickbacks'][0]->clickbacks) ?
                    $yesterday['clickbacks'][0]->clickbacks :
                    '';
                $yesterday['viral']      = ($yesterday['shares'] > 0 && $yesterday['clickbacks'] > 0 ) ?
                    $yesterday['clickbacks'] / $yesterday['shares'] * 100 . '%' :
                    'n/a';

                if (!$yesterday['clickbacks']) {
                    $yesterday['clickbacks'] = 0;
                }

                if (!$yesterday['shares']) {
                    $yesterday['shares'] = 0;
                }

                $decodedLastWeek    = json_decode($stats['sharesweek']->getBody());
                $lastweek['shares'] = 0;
                foreach ($decodedLastWeek as $share) {
                    $lastweek['shares'] += $share->shares;
                }

                $decodedLastWeek        = json_decode($stats['clickbacksweek']->getBody());
                $lastweek['clickbacks'] = 0;
                foreach ($decodedLastWeek as $clickback) {
                    $lastweek['clickbacks'] += $clickback->clickbacks;
                }

                $lastweek['viral'] = ($lastweek['shares'] > 0 && $lastweek['clickbacks'] > 0 ) ?
                    $lastweek['clickbacks'] / $lastweek['shares'] * 100 . '%' :
                    'n/a';

                $decodedLastMonth    = json_decode($stats['sharesmonth']->getBody());
                $lastmonth['shares'] = 0;
                foreach ($decodedLastMonth as $share) {
                    $lastmonth['shares'] += $share->shares;
                }

                $decodedLastMonth        = json_decode($stats['clickbacksmonth']->getBody());
                $lastmonth['clickbacks'] = 0;
                foreach ($decodedLastMonth as $clickback) {
                    $lastmonth['clickbacks'] += $clickback->clickbacks;
                }

                $lastmonth['viral'] = ($lastmonth['shares'] > 0 && $lastmonth['clickbacks'] ) ?
                    $lastmonth['clickbacks'] / $lastmonth['shares'] * 100 . '%' :
                    'n/a';

                $services['shares'] = json_decode($stats['shares/servicemonth']->getBody());
                if (is_null($services['shares'])) {
                    $services['shares'] = array();
                }

                $services['clickbacks'] = json_decode($stats['clickbacks/servicemonth']->getBody());
                if (is_null($services['clickbacks'])) {
                    $services['clickbacks'] = array();
                }

                $data = array(
                    'yesterday'      => $yesterday,
                    'lastweek'       => $lastweek,
                    'lastmonth'      => $lastmonth,
                    'shareurls'      => $shareurls,
                    'clickbackurls'  => $clickbackurls,
                    'domain'         => $domain,
                );
            }
        }

        if (!empty($data)) {
            $widgets = $e->getParam('widgets');

            $widgets['addthis']['id']      = 'addthis';
            $widgets['addthis']['title']   = 'AddThis';
            $widgets['addthis']['content'] = $this->addPath(__DIR__ . '/views')->render(
                'dashboard.phtml',
                $data
            );

            $e->setParam('widgets', $widgets);
        }
    }

    /**
     * Execute request to addthis api
     *
     * @param string $metric    Metric
     * @param string $dimension Dimension
     * @param string $domain    Domain
     * @param string $period    Period
     * @param string $username  Usernamer
     * @param string $password  Password
     * @param string $profile   Profile id
     *
     * @return mixed
     */
    protected function executeQuery($metric, $dimension, $domain, $period, $username, $password, $profile)
    {
        $client = new Client(
            'https://api.addthis.com/analytics/1.0/pub/' . $metric . $dimension . '.json?',
            array(
                'sslverifypeer' => false,
                'timeout' => 2,
            )
        );

        $parameters = array(
            'domain'   => $domain,
            'period'   => $period,
            'username' => $username,
            'password' => $password,
            'origin'   => 'gotcms_plugin',
        );
        if (!empty($profile)) {
            $parameters['pubid'] = $profile;
        }

        $client->setParameterGet($parameters);
        try {
            $response = $client->send();
            return $response;
        } catch (\Exception $e) {
            //Don't care
        }

        return false;
    }
}
