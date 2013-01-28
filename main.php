<?php
/**
 * main.php
 *
 * The main php file for running the download timings.
 *
 * @author Mustafa Ashurex <mustafa@ashurexconsulting.com>
 * @version $Id$
 * @license Apache 2.0
 */

require('Transfer.php');
require('DocumentList.php');

// The base URL for all file downloads.
define('BASE_URL','http://localhost/documents/');
// Set to true to see verbose output.
define('DEBUG_ENABLED', false);

// The maximum number of times to iterate through the download list.
$maxIterations = 5;

// The label for all the output files.
$fileLabel = 'ashurex';

$docList = new DocumentList();
$docNames = $docList->getDocumentList();
runTest($fileLabel, BASE_URL, $docNames, $maxIterations);

/**
 * Run test can be run as many times as necessasry and can be called more than once
 * runTest(file descriptor, baseUrl, collection of doc names, max number of iterations)
 * It is possible to set the same docs on a second server and run a baseline against that.
 *
 * @param string $fileLabel The label for all output files.
 * @param string $url The base URL for downloading.
 * @param array<string> The list of document file name/paths to download.
 * @param int $maxIterations (Default: 1) The max number of times to go through the document
 *      list and download each file.
 */
function runTest($fileLabel, $url, array $docNames, $maxIterations = 1)
{
    for($i = 0; $i < $maxIterations; $i++)
    {
        $results[] = processDocList($docNames, $url);
    }

    $i = 0;
    foreach($results as $r)
    {
        $fname = sprintf('%02d-%s', $i, $fileLabel);
        $fr = fopen("./output/$fname-results.csv", 'w');

        foreach($r['results'] as $line){ fputcsv($fr, $line); }

        if(count($r['slow']) > 0)
        {
            $fs = fopen("./output/$fname-slow.csv", 'w');
            foreach($r['slow'] as $line){ fputcsv($fs, $line); }
            fclose($fs);
        }

        fclose($fr);

        $i++;
    }
}

/**
 * Process the each docoument in the document list for the base URL specified.
 *
 * @param array<string> $docNames Document name list.
 * @param string $baseUrl the base URL for downloading all the files.
 * @return array Associative array of test results (duration, slow, results).
 */
function processDocList($docNames, $baseUrl)
{
    $xfer = new Transfer();
    $duration = 0;
    $slowFiles = array();
    $results = array();
    foreach($docNames as $name)
    {
        $name = str_replace("\'","'", $name);
        $name = rawurlencode($name);

        $xfer->setUrl($baseUrl . $name);

        $time = $xfer->timeTransfer(DEBUG_ENABLED);

        if($time != null)
        {
            $duration += $time;

            $r = array(rawurldecode($name), $time);
            if($time > 10)
            {
                print "======\n$name\n";
                echo "Time: $time\n";
                print "======\n";
                $slowFiles[] = $r;
            }

            $results[] = $r;
        }
        else
        {
            print "Error downloading: $name!\n";
        }
    }

    echo "\n\n" . count($slowFiles) . " slow files detected:\n";
    echo "Total: $duration\n";

    return array
    (
        'duration'  => $duration,
        'slow'      => $slowFiles,
        'results'   => $results
    );
}
