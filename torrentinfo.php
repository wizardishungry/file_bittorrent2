<?php

    /**
    * File_Bittorrent Example
    * Get Info from a .torrent file
    *
    * Usage: 
    *   # php torrentinfo.php -t file.torrent
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

    $File_Bittorrent_Decode = new File_Bittorrent_Decode;
    $info = $File_Bittorrent_Decode->decodeFile($torrent);

    foreach ($info as $key => $val) {
        echo str_pad($key . ': ', 20, ' ', STR_PAD_LEFT);
        switch($key) {
        case 'files':
            echo "\n";
            foreach ($val as $file) {
                echo str_repeat(' ', 20) . '- ' . $file['filename'] . "\n";
            }
            break;
        case 'announce_list':
            echo "\n";
            foreach ($val as $url) {
                echo str_repeat(' ', 20) . '- ' . $url . "\n";
            }
            break;
        default:
            echo $val . "\n";
        }
    }

?>
