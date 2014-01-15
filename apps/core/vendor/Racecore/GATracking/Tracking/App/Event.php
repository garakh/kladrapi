<?php
namespace Racecore\GATracking\Tracking\App;

use Racecore\GATracking\Tracking\AbstractTracking;

/**
 * Google Analytics Measurement PHP Class
 * Licensed under the 3-clause BSD License.
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * Google Documentation
 * https://developers.google.com/analytics/devguides/collection/protocol/v1/
 *
 * @author  Marco Rieger
 * @email   Rieger(at)racecore.de
 * @git     https://github.com/ins0
 * @url     http://www.racecore.de
 * @package Racecore\GATracking\Tracking\App
 */
class Event extends AbstractTracking
{
    /** @var string */
    private $appName;

    /** @var string */
    private $eventCategory;

    /** @var string */
    private $eventAction;

    /**
     * Set the Application Name
     *
     * @param string $appName
     */
    public function setAppName($appName)
    {
        $this->appName = $appName;
    }

    /**
     * Get the Application Name
     *
     * @return string
     */
    public function getAppName()
    {
        return $this->appName;
    }

    /**
     * Set the Event Action
     *
     * @param string $eventAction
     */
    public function setEventAction($eventAction)
    {
        $this->eventAction = $eventAction;
    }

    /**
     * Get the Event Action
     *
     * @return string
     */
    public function getEventAction()
    {
        return $this->eventAction;
    }

    /**
     * Set the Event Category
     *
     * @param string $eventCategory
     */
    public function setEventCategory($eventCategory)
    {
        $this->eventCategory = $eventCategory;
    }

    /**
     * Get the Event Category
     *
     * @return string
     */
    public function getEventCategory()
    {
        return $this->eventCategory;
    }

    /**
     * Returns the Paket for App Event Tracking
     *
     * @return array
     */
    public function getPaket()
    {
        return array(
            't' => 'event',
            'an' => $this->getAppName(),
            'ec' => $this->getEventCategory(),
            'ea' => $this->getEventAction()
        );
    }
}