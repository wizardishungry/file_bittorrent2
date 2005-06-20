<?php

    /**
    * Info-Hash Test
    * Get Info from a .torrent file
    *
    * Usage:
    *   # php infohash.php -t file.torrent
    *
    * @author Markus Tacker <m@tacker.org>
    * @version $Id$
    */

    // Includes
    require_once 'File/Bittorrent/Decode.php';
    require_once 'File/Bittorrent/Encode.php';
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
    $File_Bittorrent_Encode = new File_Bittorrent_Encode;
    $decoded = $File_Bittorrent_Decode->decode(file_get_contents($torrent));

    echo "\nInfo Hash\n";
    echo "++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
    echo "This:           " . sha1($File_Bittorrent_Encode->encode($decoded['info'])) . "\n";
    
    exec('/usr/bin/btshowmetainfo.py ' . escapeshellarg($torrent), $bt);
    echo "btshowmetainfo: " . substr($bt[3], strpos($bt[3], ':') + 2) . "\n";
    
    // Gagge's version from http://pear.php.net/bugs/bug.php?id=3970
    $filesrc = file_get_contents($torrent);
	$start = strpos($filesrc, 'd5:files');
	$match = substr($filesrc, $start, (strlen($filesrc)-$start-1));
	$file_info['info_hash'] = sha1($match);
	echo "gagge:          " . $file_info['info_hash'] . "\n";

?>