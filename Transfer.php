<?php
/**
 * curl file transfer class.
 *
 * @author Mustafa Ashurex <mustafa@ashurexconsulting.com>
 * @version $Id$
 * @license Apache 2.0
 */

/**
 * Transfers specified URL using cURL.
 */
class Transfer
{
    /**
     * @var string URL to download from.
     */
    private $url;

    public function __construct(array $args = null)
    {


    }

    /**
     * @param string $url The URL to download from.
     */
    public function setUrl($url){ $this->url = $url; }

    /**
     * @return string The URL being downloaded from.
     */
    public function getUrl(){ return $this->url; }

    /**
     * Downloads the binary specified at $url and returns the
     * total transfer time.
     *
     * @param boolean $verbose (Default: false) If true, extra logging to System.Out is printed.
     * @return int The total duration (ms) for the transfer execution.
     */
    public function timeTransfer($verbose = false)
    {
        try
        {
            $ch = curl_init($this->getUrl());
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            
            $start = microtime(true);
            $doc = curl_exec($ch);
            $stop = microtime(true);
            curl_close($ch);

            if($verbose){ echo "Bytes: " . strlen($doc) . "\n"; }
            if(strlen($doc) < 1){ return null; }

            return $stop - $start;
        }
        catch(Exception $ex)
        {
            echo $ex->getMessage() . "\n";
            return null;
        }
    }
}
