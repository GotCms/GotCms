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
 * @category Gc
 * @package  Library
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Gc;

use Zend\Mail\Message,
    Zend\Mail\Transport\Sendmail as SendmailTransport;

/**
 * Extension for Zend\Mail\Message
 *
 * @category Gc
 * @package  Library
 */
class Mail extends Message
{
    /**
     * Initialize mail
     *
     * @param string $encoding
     * @param string $message
     * @param string $from
     * @param string $to
     * @return void
     */
    public function __construct($encoding = NULL, $message = NULL, $from = NULL, $to = NULL)
    {
        if(!empty($encoding))
        {
            $this->setEncoding($encoding);
        }

        if(!empty($message))
        {
            $this->setBody($message);
        }

        if(!empty($from))
        {
            $this->setFrom($from);
        }

        if(!empty($to))
        {
            $this->addTo($to);
        }
    }

    /**
     * Send mail
     *
     * @return void
     */
    public function send()
    {
        $transport = new SendmailTransport();
        $transport->send($this);
    }
}
