<?php
namespace Racecore\GATracking;

use Racecore\GATracking\Exception\EndpointServerException;
use Racecore\GATracking\Exception\MissingConfigurationException;
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
 * @package Racecore\GATracking\Tracking
 */
class GATracking
{
    /**
     * Google Analytics Account ID UA-...
     *
     * @var
     */
    private $accountID;

    /**
     * Current User Client ID
     *
     * @var string
     */
    private $clientID;

    /**
     * Protocol Version
     *
     * @var string
     */
    private $protocol = '1';

    /**
     * Analytics Endpoint URL
     *
     * @var string
     */
    private $analytics_endpoint = 'http://www.google-analytics.com/collect';

    /**
     * Tacking Holder
     *
     * @var array
     */
    private $tracking_holder = array();

    /**
     * Holds the last Response from Google Analytics Server
     *
     * @var string
     */
    private $last_response = null;

    /**
     * Holds all Responses from GA Server
     *
     * @var array
     */
    private $last_response_stack = array();

    /**
     * Sets the Analytics Account ID
     *
     * @param $account
     */
    public function setAccountID($account)
    {

        $this->accountID = $account;
    }

    /**
     * Set the current Client ID
     *
     * @param $clientID
     * @return $this
     */
    public function setClientID($clientID)
    {
        $this->clientID = $clientID;
        return $this;
    }

    /**
     * Returns the current Client ID
     *
     * @return string
     */
    public function getClientID()
    {
        if (!$this->clientID) {
            $this->clientID = $this->createClientID();
        }

        return $this->clientID;
    }

    /**
     * Return all registered Events
     *
     * @return array
     */
    public function getEvents()
    {
        return $this->tracking_holder;
    }

    /**
     * Returns current Google Account ID
     *
     * @return mixed
     */
    public function getAccountID()
    {
        return $this->accountID;
    }

    /**
     * Constructor
     *
     * @param string $accountID
     */
    public function __construct( $accountID = null )
    {
        $this->setAccountID( $accountID );

        return $this;
    }

    /**
     * Create a GUID on Client specific values
     *
     * @return string
     */
    private function createClientID()
    {
        // collect user specific data
        if (isset($_COOKIE['_ga'])) {

            $gaCookie = explode('.', $_COOKIE['_ga']);
            if( isset($gaCookie[2] ) )
            {
                // check if uuid
                if( $this->checkUUID( $gaCookie[2] ) )
                {
                    // uuid set in cookie
                    return $gaCookie[2];
                }
                elseif( isset($gaCookie[2]) && isset($gaCookie[3]) )
                {
                    // google default client id
                    return $gaCookie[2] . '.' . $gaCookie[3];
                }
            }
        }

        // nothing found - return random uuid client id
        return $this->generateUUID();
    }

    /**
     * Check if is a valid UUID v4
     *
     * @param $uuid
     * @return int
     */
    private function checkUUID( $uuid )
    {
        return preg_match('#^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$#i', $uuid );
    }

    /**
     * Generate UUID v4 function - needed to generate a CID when one isn't available
     *
     * @author Andrew Moore http://www.php.net/manual/en/function.uniqid.php#94959
     * @return string
     */
    private function generateUUID() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }

    /**
     * Send all captured Trackings to Analytics Server
     * Flush all prev. captured tracking responses
     *
     * @return bool
     */
    public function send()
    {
        // clear response logs
        $this->last_response_stack = array();
        $this->last_response = null;

        /** @var AbstractTracking $event */
        foreach ($this->tracking_holder as $tracking) {
            $this->sendTracking($tracking);
        }

        return true;
    }

    /**
     * Returns the Client IP
     * The last octect of the IP address is removed to anonymize the user
     *
     * @param string $address
     * @return string
     */
    function getClientIP($address = '')
    {

        if (!$address) {
            $address = $_SERVER['REMOTE_ADDR'];
        }

        if (!$address) {
            return '';
        }

        // Capture the first three octects of the IP address and replace the forth
        // with 0, e.g. 124.455.3.123 becomes 124.455.3.0
        $regex = "/^([^.]+\.[^.]+\.[^.]+\.).*/";
        if (preg_match($regex, $address, $matches)) {
            return $matches[1] . '0';
        }

        return '';
    }

    /**
     * Build the POST Packet
     *
     * @param AbstractTracking $event
     * @return string
     * @throws Exception\MissingConfigurationException
     */
    private function buildPacket( AbstractTracking $event )
    {
        // get packet
        $eventPacket = $event->getPaket();

        if( ! $this->getAccountID() )
        {
            throw new MissingConfigurationException('Google Account ID is missing');
        }

        // Add Protocol
        $eventPacket['v'] = $this->protocol; // protocol version
        $eventPacket['tid'] = $this->getAccountID(); // account id
        $eventPacket['cid'] = $this->getClientID(); // client id

        $eventPacket = array_reverse($eventPacket);

        // build query
        return http_build_query($eventPacket);
    }

    /**
     * Send an Event to Google Analytics
     * Will be removed
     *
     * @param AbstractTracking $tracking
     * @return bool
     * @throws Exception\EndpointServerException
     * @deprecated Use sendTracking
     */
    public function sendEvent(AbstractTracking $tracking)
    {
        return $this->sendTracking($tracking);
    }

    /**
     * Send an Event to Google Analytics
     *
     * @param AbstractTracking $event
     * @return bool
     * @throws Exception\EndpointServerException
     */
    public function sendTracking(AbstractTracking $event)
    {
        // get packet
        $eventPacket = $this->buildPacket( $event );

        // get endpoint
        $endpoint = parse_url($this->analytics_endpoint);

        // port
        $port = ($endpoint['scheme'] == 'https' ? 443 : 80);

        // connect
        $connection = @fsockopen($endpoint['scheme'] == 'https' ? 'ssl://' : $endpoint['host'], $port, $error, $errorstr, 10);

        if (!$connection) {
            throw new EndpointServerException('Analytics Host not reachable!');
        }

        $header =   'POST ' . $endpoint['path'] . ' HTTP/1.1' . "\r\n" .
                    'Host: ' . $endpoint['host'] . "\r\n" .
                    'User-Agent: Google-Measurement-PHP-Client' . "\r\n" .
                    'Content-Type: application/x-www-form-urlencoded' . "\r\n" .
                    'Content-Length: ' . strlen($eventPacket) . "\r\n" .
                    'Connection: Close' . "\r\n\r\n";

        $this->last_response = '';

        // frwite data
        fwrite($connection, $header);
        fwrite($connection, $eventPacket);

        // response
        $response = '';

        // receive response
        while (!feof($connection)) {
            $response .= fgets($connection, 1024);
        }

        // response
        $responseContainer = explode("\r\n\r\n", $response, 2);
        $responseContainer[0] = explode("\r\n", $responseContainer[0]);

        // save last response
        $this->addResponse( $responseContainer );

        // connection close
        fclose($connection);

        return true;
    }

    /**
     * Add a Response to the Stack
     *
     * @param $response
     * @return bool
     */
    public function addResponse( $response )
    {
        $this->last_response_stack[] = $response;
        $this->last_response = $response;
        return true;
    }

    /**
     * Returns the last Response from Google Analytics Server
     *
     * @author  Marco Rieger
     * @return string
     */
    public function getLastResponse()
    {
        return $this->last_response;
    }

    /**
     * Returns all Responses since the last Send Method Call
     *
     * @return array
     */
    public function getLastResponseStack()
    {
        return $this->last_response_stack;
    }

    /**
     * Add Tracking Event
     *
     * @param AbstractTracking $tracking
     * @return $this
     */
    public function addTracking(AbstractTracking $tracking)
    {
        $this->tracking_holder[] = $tracking;

        return $this;
    }
}