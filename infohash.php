<?php

    /**
    * Info-Hash Test
    * Compares the info_hash compution of this package to the original program implementation
    *
    * Usage:
    *   # php infohash.php -t file.torrent
    *
    * @author Markus Tacker <m@tacker.org>
    * @version $Id$
    */

    error_reporting(E_ALL);

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

    $File_Bittorrent_Decode = new File_Bittorrent_Decode;
    $File_Bittorrent_Decode->decodeFile($torrent);

    echo "\nInfo Hash\n";
    echo "++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
    echo "This:           " . $File_Bittorrent_Decode->info_hash . "\n";

    exec('/usr/bin/btshowmetainfo.py ' . escapeshellarg($torrent), $bt);
    echo "btshowmetainfo: " . substr($bt[3], strpos($bt[3], ':') + 2) . "\n";

?>