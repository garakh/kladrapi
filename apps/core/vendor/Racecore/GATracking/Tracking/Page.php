<?php
namespace Racecore\GATracking\Tracking;

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
 * @package Racecore\GATracking\Tracking
 */
class Page extends AbstractTracking
{

    private $documentPath = '';
    private $host = '';
    private $title = '';

    /**
     * Set the Request Document Path
     *
     * @param $path
     */
    public function setDocumentPath($path)
    {

        $this->documentPath = $path;
    }

    /**
     * Returns the Request Document Path
     *
     * @return string
     */
    public function getDocumentPath()
    {

        if (!$this->documentPath) {
            return '/';
        }

        return $this->documentPath;
    }

    /**
     * Sets the Document Title in Analytics Report
     *
     * @param $title
     */
    public function setDocumentTitle($title)
    {

        $this->title = $title;
    }

    /**
     * Return Document Title
     *
     * @return string
     */
    public function getDocumentTitle()
    {

        return $this->title;
    }

    /**
     * Return the Document Host Adress
     *
     * @param $host
     * @return $this
     */
    public function setDocumentHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Return Document Host
     *
     * @return string
     */
    public function getDocumentHost()
    {
        return $this->host;
    }

    /**
     * Returns the Google Paket for Campaign Tracking
     *
     * @return array
     */
    public function getPaket()
    {
        return array(
            't' => 'pageview',
            'dh' => $this->getDocumentHost(),
            'dp' => $this->getDocumentPath(),
            'dt' => $this->getDocumentTitle()
        );
    }

}