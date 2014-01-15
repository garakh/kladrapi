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
class Screen extends AbstractTracking
{
    /** @var string */
    private $appName;

    /** @var string */
    private $appVersion;

    /** @var string */
    private $contentDescription;

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
     * Set the Application Version
     *
     * @param string $appVersion
     */
    public function setAppVersion($appVersion)
    {
        $this->appVersion = $appVersion;
    }

    /**
     * Get the Application Version
     *
     * @return string
     */
    public function getAppVersion()
    {
        return $this->appVersion;
    }

    /**
     * Set the Content Description/Screen Name
     *
     * @param string $contentDescription
     */
    public function setContentDescription($contentDescription)
    {
        $this->contentDescription = $contentDescription;
    }

    /**
     * Get the Content Description/Screen Name
     *
     * @return string
     */
    public function getContentDescription()
    {
        return $this->contentDescription;
    }

    /**
     * Returns the Paket for App Screen Tracking
     *
     * @return array
     */
    public function getPaket()
    {
        return array(
            't' => 'appview',
            'an' => $this->getAppName(),
            'av' => $this->getAppVersion(),
            'cd' => $this->getContentDescription()
        );
    }
}