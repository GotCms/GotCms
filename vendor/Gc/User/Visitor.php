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
 * @category    Gc
 * @package     Library
 * @subpackage  User
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\User;

use Gc\Db\AbstractTable,
    Zend\Db\Sql\Select,
    Zend\Db\Sql\Insert,
    Zend\Db\Sql\Predicate\Expression,
    Zend\Db\TableGateway,
    Zend\Uri\Uri,
    Zend\Validator\Ip as ValidateIp;
/**
 * Model of visitor
 */
class Visitor extends AbstractTable
{
    /**
     * Table name
     * @var string
     */
    protected $_name = 'log_visitor';

    /**
     * Get visitor id
     * @param string $session_id
     * @return integer
     */
    public function getVisitorId($session_id)
    {
        $user_agent = empty($_SERVER['HTTP_USER_AGENT']) ? NULL : $_SERVER['HTTP_USER_AGENT'];
        $accept_charset = empty($_SERVER['HTTP_ACCEPT_CHARSET']) ? NULL : $_SERVER['HTTP_ACCEPT_CHARSET'];
        $accept_language = empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? NULL : $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $server_addr = empty($_SERVER['SERVER_ADDR']) ? NULL : $_SERVER['SERVER_ADDR'];
        $remote_addr = empty($_SERVER['REMOTE_ADDR']) ? NULL : $_SERVER['REMOTE_ADDR'];
        $request_uri = empty($_SERVER['REQUEST_URI']) ? NULL : $_SERVER['REQUEST_URI'];
        $referer = empty($_SERVER['HTTP_REFERER']) ? NULL : $_SERVER['HTTP_REFERER'];

        if(!empty($request_uri))
        {
            $request_uri = substr($request_uri, 0, 255);
        }

        if(!empty($referer))
        {
            $referer = substr($referer, 0, 255);
        }

        if(!ctype_alnum($session_id))
        {
            $session_id = NULL;
        }

        if(!ctype_print($user_agent))
        {
            $user_agent = NULL;
        }

        if(!ctype_print($accept_charset))
        {
            $accept_charset = NULL;
        }

        if(!ctype_print($accept_language))
        {
            $accept_language = NULL;
        }

        $validator = new ValidateIp();
        $server_addr = $validator->isValid($server_addr, 'ip') ? ip2long($server_addr) : NULL;
        $remote_addr = $validator->isValid($remote_addr, 'ip') ? ip2long($remote_addr) : NULL;

        $url_id = $this->getUrlId($request_uri, $referer);

        $select = new Select();
        $select->from(array('lv' => $this->_name))
            ->columns(array('id'))
            ->where->equalTo('session_id', empty($session_id) ? NULL : $session_id)
            ->equalTo('http_user_agent', empty($user_agent) ? NULL : $user_agent)
            ->equalTo('remote_addr', $remote_addr);

        $visitor_id = $this->fetchOne($select);

        if(empty($visitor_id))
        {
            $insert = new Insert();
            $insert->into('log_visitor')
                ->columns(array(
                    'session_id',
                    'http_user_agent',
                    'http_accept_charset',
                    'http_accept_language',
                    'server_addr',
                    'remote_addr'
                ))
                ->values(array(
                    $session_id,
                    $user_agent,
                    $accept_charset,
                    $accept_language,
                    $server_addr,
                    $remote_addr
                ));
            $this->execute($insert);
            $visitor_id = $this->getLastInsertId('log_visitor');
        }

        $insert = new Insert();
        $insert->into('log_url')
            ->columns(array(
                'visit_at',
                'log_url_info_id',
                'log_visitor_id'
            ))
            ->values(array(
                new Expression('NOW()'),
                $url_id,
                $visitor_id
            ));

        $this->execute($insert);
        return $visitor_id;
    }

    /**
     * Get url id
     * @param string $request_uri
     * @param string $referer
     * @return integer
     */
    public function getUrlId($request_uri, $referer)
    {
        $select = new Select();
        $select->from('log_url_info')->where->equalTo('url', $request_uri);

        $url_info = $this->fetchRow($select);
        if(!empty($url_info->id))
        {
            $url_id = $url_info->id;
        }
        else
        {
            $insert = new Insert();
            $insert->into('log_url_info')
                ->columns(array('url', 'referer'))
                ->values(array($request_uri, $referer));
            $this->execute($insert);
            $url_id = $this->getLastInsertId('log_url_info');
        }

        return $url_id;
    }

    /**
     * Return total visitors
     * @return array
     */
    public function getTotalVisitors()
    {
        return $this->fetchOne($this->select(function(Select $select)
        {
            $select->columns(array('nb_visitors' => new Expression('COUNT(1)')));
        }));
    }

    /**
     * Return total visits
     * @return array
     */
    public function getTotalPageViews()
    {
        $visit_table = new TableGateway\TableGateway('log_url', TableGateway\Feature\GlobalAdapterFeature::getStaticAdapter());
        return $this->fetchOne($visit_table->select(function(Select $select)
        {
            $select->columns(array('nb' => new Expression('COUNT(1)')));
        }));
    }

    /**
     * Return all visits
     * @param string $sort
     * @return array
     */
    public function getVisitStats($sort)
    {
        if(!in_array($sort, array('HOUR', 'DAY', 'MONTH', 'YEAR')))
        {
            $sort = 'DAY';
        }

        $rows = $this->fetchAll($this->select(function(Select $select) use ($sort)
        {
            $select->columns(array('date' => new Expression(sprintf('EXTRACT(%s FROM lu.visit_at)', $sort)), 'nb' => new Expression('COUNT(lu.id)')));
            $select->join(array('lu' => 'log_url'), 'lu.log_visitor_id = log_visitor.id', array());

            if($this->getDriverName() == 'pdo_pgsql')
            {
                if($sort == 'HOUR')
                {
                    $select->where("TO_CHAR(lu.visit_at, 'YYYYMMDD') = TO_CHAR(NOW(), 'YYYYMMDD')");
                }
                else
                {
                    $select->where("lu.visit_at > DATE_TRUNC('month', NOW())");
                }
            }
            elseif($this->getDriverName() == 'pdo_mysql')
            {
                if($sort == 'HOUR')
                {
                    $select->where("DATE_FORMAT(lu.visit_at, '%Y/%m/%d') = DATE_FORMAT(NOW(), '%Y/%m/%d')");
                }
                else
                {
                    $select->where("DATE_FORMAT(lu.visit_at, '%Y%m') >= EXTRACT(YEAR_MONTH FROM NOW())");
                }
            }

            $select->order('date ASC');
            $select->group(array('date'));
        }));

        return $this->_sortData($sort, $rows);
    }

    /**
     * Return all visitors
     * @param string $sort
     * @param array $rows
     * @return array
     */
    protected function _sortData($sort, $rows)
    {
        $values = array();
        if(empty($rows))
        {
            return $values;
        }

        switch($sort)
        {
            case 'HOUR':
                for($i = 0;$i < 24; $i++)
                {
                    $values[$i . 'h'] = 0;
                }

                foreach($rows as $row)
                {
                    $values[$row['date'] . 'h'] = $row['nb'];
                }
            break;

            case 'DAY':
                $day_in_month = date('t');
                for($i = 1;$i < $day_in_month; $i++)
                {
                    $values[$i . 'd'] = 0;
                }

                foreach($rows as $row)
                {
                    $values[$row['date'] . 'd'] = $row['nb'];
                }
            break;

            case 'MONTH':
                for($i = 1;$i <= 12; $i++)
                {
                    $values[date( 'M', mktime(0, 0, 0, $i))] = 0;
                }

                foreach($rows as $row)
                {
                    $values[date( 'M', mktime(0, 0, 0, $row['date']))] = $row['nb'];
                }
            break;

            case 'YEAR':
                foreach($rows as $row)
                {
                    $values[$row['date']] = $row['nb'];
                }

                $keys = array_keys($values);
                for($i = min($keys);$i <= max($keys)+1; $i++)
                {
                    $values[$i] = empty($values[$i]) ? 0 : $values[$i];
                }
            break;
        }

        return $values;
    }

    /**
     * Return all visitors
     * @param string $sort
     * @return array
     */
    public function getVisitorStats($sort)
    {
        if(!in_array($sort, array('HOUR', 'DAY', 'MONTH', 'YEAR')))
        {
            $sort = 'DAY';
        }

        $rows = $this->fetchAll($this->select(function(Select $select) use ($sort)
        {
            $select->columns(array('date' => new Expression(sprintf('EXTRACT(%s FROM lu.visit_at)', $sort)), 'nb' => new Expression('COUNT(DISTINCT(log_visitor.id))')));
            $select->join(array('lu' => 'log_url'), 'lu.log_visitor_id = log_visitor.id', array());

            if($this->getDriverName() == 'pdo_pgsql')
            {
                if($sort == 'HOUR')
                {
                    $select->where("TO_CHAR(lu.visit_at, 'YYYYMMDD') = TO_CHAR(NOW(), 'YYYYMMDD')");
                }
                else
                {
                    $select->where("lu.visit_at > DATE_TRUNC('month', NOW())");
                }
            }
            elseif($this->getDriverName() == 'pdo_mysql')
            {
                if($sort == 'HOUR')
                {
                    $select->where("DATE_FORMAT(lu.visit_at, '%Y/%m/%d') = DATE_FORMAT(NOW(), '%Y/%m/%d')");
                }
                else
                {
                    $select->where("DATE_FORMAT(lu.visit_at, '%Y%m') >= EXTRACT(YEAR_MONTH FROM NOW())");
                }
            }
            $select->order('date ASC');
            $select->group(array('date'));
        }));

        return $this->_sortData($sort, $rows);
    }
}
