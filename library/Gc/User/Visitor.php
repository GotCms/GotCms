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
 * @category   Gc
 * @package    Library
 * @subpackage User
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\User;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Db\TableGateway;
use Zend\Validator\Ip as ValidateIp;

/**
 * Model of visitor
 *
 * @category   Gc
 * @package    Library
 * @subpackage User
 */
class Visitor extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'log_visitor';

    /**
     * Get visitor id
     *
     * @param string $sessionId Session Id
     *
     * @return integer
     */
    public function getVisitorId($sessionId)
    {
        $userAgent      = empty($_SERVER['HTTP_USER_AGENT']) ? null : $_SERVER['HTTP_USER_AGENT'];
        $acceptCharset  = empty($_SERVER['HTTP_ACCEPT_CHARSET']) ? null : $_SERVER['HTTP_ACCEPT_CHARSET'];
        $acceptLanguage = empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? null : $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $serverAddr     = empty($_SERVER['SERVER_ADDR']) ? null : $_SERVER['SERVER_ADDR'];
        $remoteAddr     = empty($_SERVER['REMOTE_ADDR']) ? null : $_SERVER['REMOTE_ADDR'];
        $requestUri     = empty($_SERVER['REQUEST_URI']) ? '' : substr($_SERVER['REQUEST_URI'], 0 ,255);
        $referer        = empty($_SERVER['HTTP_REFERER']) ? null : substr($_SERVER['HTTP_REFERER'], 0, 255);

        if (!ctype_print($userAgent)) {
            $userAgent = null;
        }

        if (!ctype_print($acceptCharset)) {
            $acceptCharset = null;
        }

        if (!ctype_print($acceptLanguage)) {
            $acceptLanguage = null;
        }

        $validator  = new ValidateIp();
        $serverAddr = $validator->isValid($serverAddr) ? ip2long($serverAddr) : null;
        $remoteAddr = $validator->isValid($remoteAddr) ? ip2long($remoteAddr) : null;

        $urlId = $this->getUrlId($requestUri, $referer);

        $select = new Select();
        $select->from(array('lv' => $this->name))
            ->columns(array('id'))
            ->where->equalTo('session_id', $sessionId)
            ->equalTo('http_user_agent', empty($userAgent) ? null : $userAgent)
            ->equalTo('remote_addr', $remoteAddr);

        $visitorId = $this->fetchOne($select);

        if (empty($visitorId)) {
            $insert = new Insert();
            $insert->into('log_visitor')
                ->values(
                    array(
                        'session_id' => $sessionId,
                        'http_user_agent' => $userAgent,
                        'http_accept_charset' => $acceptCharset,
                        'http_accept_language' => $acceptLanguage,
                        'server_addr' => $serverAddr,
                        'remote_addr' => $remoteAddr,
                    )
                );
            $this->execute($insert);
            $visitorId = $this->getLastInsertId('log_visitor');
        }

        $insert = new Insert();
        $insert->into('log_url')
            ->values(
                array(
                    'visit_at' => new Expression('NOW()'),
                    'log_url_info_id' => $urlId,
                    'log_visitor_id' => $visitorId
                )
            );

        $this->execute($insert);
        return $visitorId;
    }

    /**
     * Get url id
     *
     * @param string $requestUri Request URI
     * @param string $referer    Referer
     *
     * @return integer
     */
    public function getUrlId($requestUri, $referer)
    {
        $select = new Select();
        $select->from('log_url_info')->where->equalTo('url', $requestUri);
        if (is_null($referer)) {
            $select->where->isNull('referer');
        } else {
            $select->where->equalTo('referer', $referer);
        }

        $urlInfo = $this->fetchRow($select);
        if (!empty($urlInfo['id'])) {
            $urlId = $urlInfo['id'];
        } else {
            $insert = new Insert();
            $insert->into('log_url_info')
                ->values(array('url' => $requestUri, 'referer' => $referer));
            $this->execute($insert);
            $urlId = $this->getLastInsertId('log_url_info');
        }

        return $urlId;
    }

    /**
     * Return total visitors
     *
     * @return array
     */
    public function getTotalVisitors()
    {
        return $this->fetchOne(
            $this->select(
                function (Select $select) {
                    $select->columns(array('nb_visitors' => new Expression('COUNT(1)')));
                }
            )
        );
    }

    /**
     * Return total visits
     *
     * @return array
     */
    public function getTotalPageViews()
    {
        $visitTable = new TableGateway\TableGateway(
            'log_url',
            TableGateway\Feature\GlobalAdapterFeature::getStaticAdapter()
        );

        return $this->fetchOne(
            $visitTable->select(
                function (Select $select) {
                    $select->columns(array('nb' => new Expression('COUNT(1)')));
                }
            )
        );
    }

    /**
     * Return all visits
     *
     * @param string $sort Sort by HOUR, DAY, MONTH, YEAR
     *
     * @return array
     */
    public function getNbPagesViews($sort)
    {
        $sort   = $this->checkSort($sort);
        $object = $this;

        $rows = $this->fetchAll(
            $this->select(
                function (Select $select) use ($sort, $object) {
                    $select->columns(
                        array(
                            'date' => new Expression(sprintf('EXTRACT(%s FROM lu.visit_at)', $sort)),
                            'nb' => new Expression('COUNT(lu.id)')
                        )
                    );
                    $select->join(array('lu' => 'log_url'), 'lu.log_visitor_id = log_visitor.id', array());

                    $object->groupByDate($sort, $select);
                    $select->order('date ASC');
                    $select->group(array('date'));
                }
            )
        );

        return $this->sortData($sort, $rows);
    }

    /**
     * Return all visitors
     *
     * @param string $sort Sort by HOUR, DAY, MONTH, YEAR
     * @param array  $rows Rows
     *
     * @return array
     */
    protected function sortData($sort, $rows)
    {
        $values = array();
        if (empty($rows)) {
            return $values;
        }

        switch($sort) {
            case 'HOUR':
                for ($i = 0; $i < 24; $i++) {
                    $values[$i . 'h'] = 0;
                }

                foreach ($rows as $row) {
                    $values[$row['date'] . 'h'] = $row['nb'];
                }
                break;
            case 'DAY':
                $dayInMonth = date('t');
                for ($i = 1; $i < $dayInMonth; $i++) {
                    $values[$i . 'd'] = 0;
                }

                foreach ($rows as $row) {
                    $values[$row['date'] . 'd'] = $row['nb'];
                }
                break;
            case 'MONTH':
                for ($i = 1; $i <= 12; $i++) {
                    $values[date('M', mktime(0, 0, 0, $i))] = 0;
                }

                foreach ($rows as $row) {
                    $values[date('M', mktime(0, 0, 0, $row['date']))] = $row['nb'];
                }
                break;
            case 'YEAR':
                foreach ($rows as $row) {
                    $values[$row['date']] = $row['nb'];
                }

                $keys = array_keys($values);
                for ($i = min($keys); $i <= max($keys); $i++) {
                    $values[$i] = empty($values[$i]) ? 0 : $values[$i];
                }

                $newValues = array();
                foreach ($values as $key => $value) {
                    $newValues[$key . 'y'] = $value;
                }

                $values = $newValues;
                break;
        }

        return $values;
    }

    /**
     * Return all visitors
     *
     * @param string $sort Sort by HOUR, DAY, MONTH, YEAR
     *
     * @return array
     */
    public function getNbVisitors($sort)
    {
        $sort   = $this->checkSort($sort);
        $object = $this;

        $rows = $this->fetchAll(
            $this->select(
                function (Select $select) use ($sort, $object) {
                    $select->columns(
                        array(
                            'date' => new Expression(sprintf('EXTRACT(%s FROM lu.visit_at)', $sort)),
                            'nb' => new Expression('COUNT(DISTINCT(log_visitor.id))')
                        )
                    );
                    $select->join(array('lu' => 'log_url'), 'lu.log_visitor_id = log_visitor.id', array());

                    $object->groupByDate($sort, $select);
                    $select->order('date ASC');
                    $select->group(array('date'));
                }
            )
        );

        return $this->sortData($sort, $rows);
    }

    /**
     * Return all referers
     *
     * @param string  $sort  Sort by HOUR, DAY, MONTH, YEAR
     * @param integer $limit Optional limit, default: 20
     *
     * @return array
     */
    public function getUrlsViews($sort, $limit = 20)
    {
        $sort = $this->checkSort($sort);

        $select = new Select();
        $select->from(array('lu' => 'log_url'))
            ->columns(array('date' => new Expression(sprintf('EXTRACT(%s FROM MAX(lu.visit_at))', $sort))))
            ->join(
                array(
                    'lui' => 'log_url_info'
                ),
                'lui.id = lu.log_url_info_id',
                array(
                    'url',
                    'nb' => new Expression('COUNT(lui.id)')
                )
            )->order('nb DESC')
            ->group(array('lui.url'));

        $this->groupByDate($sort, $select);
        $select->limit($limit);

        return $this->fetchAll($select);
    }

    /**
     * Return all referers
     *
     * @param string  $sort  Sort by HOUR, DAY, MONTH, YEAR
     * @param integer $limit Optional limit, default: 20
     *
     * @return array
     */
    public function getReferers($sort, $limit = 20)
    {
        $sort = $this->checkSort($sort);

        $select = new Select();
        $select->from(array('lu' => 'log_url'))
            ->columns(array('date' => new Expression(sprintf('EXTRACT(%s FROM MAX(lu.visit_at))', $sort))))
            ->join(
                array(
                    'lui' => 'log_url_info'
                ),
                'lui.id = lu.log_url_info_id',
                array(
                    'url' => 'referer',
                    'nb' => new Expression('COUNT(lui.id)')
                )
            )->where('lui.referer IS NOT null')
            ->order('nb DESC')
            ->group(array('lui.referer'));

        $this->groupByDate($sort, $select);
        $select->limit($limit);

        return $this->fetchAll($select);
    }

    /**
     * group by date
     *
     * @param string $sort    Sort by HOUR, DAY, MONTH, YEAR
     * @param Select &$select Select
     *
     * @return void
     */
    public function groupByDate($sort, Select &$select)
    {
        if ($this->getDriverName() == 'pdo_pgsql') {
            switch($sort) {
                case 'HOUR':
                    $select->where("TO_CHAR(lu.visit_at, 'YYYYMMDD') = TO_CHAR(NOW(), 'YYYYMMDD')");
                    break;
                case 'DAY':
                    $select->where("lu.visit_at > DATE_TRUNC('month', NOW())");
                    break;
                case 'MONTH':
                    $select->where("lu.visit_at > DATE_TRUNC('year', NOW())");
                    break;
            }
        } elseif ($this->getDriverName() == 'pdo_mysql') {
            switch($sort) {
                case 'HOUR':
                    $select->where(
                        new Expression("DATE_FORMAT(lu.visit_at, '%Y/%m/%d') = DATE_FORMAT(NOW(), '%Y/%m/%d')")
                    );
                    break;
                case 'DAY':
                    $select->where(
                        new Expression("DATE_FORMAT(lu.visit_at, '%Y%m') >= EXTRACT(YEAR_MONTH FROM NOW())")
                    );
                    break;
                case 'MONTH':
                    $select->where(
                        new Expression("DATE_FORMAT(lu.visit_at, '%Y') >= EXTRACT(YEAR FROM NOW())")
                    );
                    break;
            }
        }
    }

    /**
     * Check sort sql variable
     *
     * @param string $sort Sort by HOUR, DAY, MONTH, YEAR
     *
     * @return string
     */
    protected function checkSort($sort)
    {
        if (!in_array($sort, array('HOUR', 'DAY', 'MONTH', 'YEAR'))) {
            $sort = 'DAY';
        }

        return $sort;
    }
}
