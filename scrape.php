<?php

    /**
    * Fetch the statistics for a torrent
    *
    * Usage:
    *   # php scrape.php -t file
    *
    * @author Markus Tacker <m@tacker.org>
    * @version $Id$
    */

    // Includes
    require_once 'File/Bittorrent/Decode.php';
    require_once 'Console/Getargs.php';

    // Get filename from command line
    $args_config = array(
        'torrent' => array(
            'short' => 't',
            'min' => 1,
            'max' => 1,
            'desc' => 'Filename of the torrent'
        ),
    );
    $args =& Console_Getargs::factory($args_config);
    if (PEAR::isError($args) or !($torrent = $args->getValue('torrent'))) {
        echo Console_Getargs::getHelp($args_config)."\n";
        exit;
    }

    if (!is_readable($torrent)) {
        echo 'ERROR: "' . $torrent . "\" is not readable.\n";
        exit;
    }

    // Decode the torrent
    $File_Bittorrent_Decode = new File_Bittorrent_Decode;
    $File_Bittorrent_Decode->decodeFile($torrent);

    echo "\nStatistics\n";
    echo "++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
    echo 'Tracker:            ' . $File_Bittorrent_Decode->announce . "\n";
    echo 'info hash:          ' . $File_Bittorrent_Decode->info_hash . "\n";
    foreach ($File_Bittorrent_Decode->getStats() as $key => $val) {
        echo str_pad($key . ':', 20) . $val . "\n";
    }

?>