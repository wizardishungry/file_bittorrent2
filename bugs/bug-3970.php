<?php

    /**
    * Test case for Bug #3970
    *
    * @see http://pear.php.net/bugs/bug.php?id=3970
    *
    * @author Markus Tacker <m@tacker.org>
    * @version $Id$
    * @package File_Bittorrent
    * @subpackage Bugs
    */

require_once '../File/Bittorrent/Encode.php';
require_once '../File/Bittorrent/Decode.php';
$Decoder = new File_Bittorrent_Decode;
$Encoder = new File_Bittorrent_Encode;
$torrent = $Decoder->decode(file_get_contents('../freebsd.torrent'));

$info_encoded = $Encoder->encode($torrent);
$decoded_info_encoded = $Decoder->decode($info_encoded);

var_dump($torrent['info']['piece length']);
var_dump($decoded_info_encoded['info']['piece length']);

?>