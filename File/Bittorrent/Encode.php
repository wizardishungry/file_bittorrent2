<?php

// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author Markus Tacker <m@tacker.org>                                  |
// +----------------------------------------------------------------------+

/**
* Encode data in Bittorrent format
*
* Based on
*   Original Python implementation by Petru Paler <petru@paler.net>
*   PHP translation by Gerard Krijgsman <webmaster@animesuki.com>
*   Gerard's regular expressions removed by Carl Ritson <critson@perlfu.co.uk>
*
* BEncoding is a simple, easy to implement method of associating
* data types with information in a file. The values in a torrent
* file are bEncoded.
* There are 4 different data types that can be bEncoded:
* Integers, Strings, Lists and Dictionaries.
* [http://www.monduna.com/bt/faq.html]
*
* @package File_Bittorrent
* @category File
*
* @author Markus Tacker <m@tacker.org>
*
* @version $Id$
*/

/**
* Include required classes
*/
require_once 'PEAR.php';

/**
* Encode files in Bittorrent format
*
* @package File_Bittorrent
* @category File
*/
class File_Bittorrent_Encode
{
    /**
    * Encode a var in BEncode format
    *
    * @param mixed    Variable to encode
    * @return string
    */
    function encode($mixed)
    {
        switch (gettype($mixed)) {
        case is_null($mixed):
            return $this->encode_string('');
            break;
        case 'string':
            return $this->encode_string($mixed);
            break;
        case 'integer':
            return  $this->encode_int($mixed);
            break;
        case 'array':
            return $this->encode_array($mixed);
            break;
        default:
            PEAR::raiseError('File_Bittorrent_Encode()::encode() - Unsupported type.', null, null, "Variable must be one of 'string', 'integer' or 'array'");
        }
    }

    /**
    * BEncodes a string
    *
    * Strings are prefixed with their length followed by a colon.
    * For example, "Monduna" would bEncode to 7:Monduna and "BitTorrents"
    * would bEncode to 11:BitTorrents.
    *
    * @param string
    * @return string
    */
    function encode_string($str)
    {
        $str = utf8_encode($str);
        return sprintf('%s:%s', strlen($str), $str);
    }

    /**
    * BEncodes a integer
    *
    * Integers are prefixed with an i and terminated by an e. For
    * example, 123 would bEcode to i123e, -3272002 would bEncode to
    * i-3272002e.
    *
    * @param int
    * @return string
    */
    function encode_int($int)
    {
        return sprintf('i%se', $int);
    }

    /**
    * BEncodes an array
    * This code assumes arrays with purely integer indexes are lists,
    * arrays which use string indexes assumed to be dictionaries.
    *
    * Dictionaries are prefixed with a d and terminated by an e. They
    * are similar to list, except that items are in key value pairs. The
    * dictionary {"key":"value", "Monduna":"com", "bit":"Torrents", "number":7}
    * would bEncode to d3:key5:value7:Monduna3:com3:bit:8:Torrents6:numberi7ee
    *
    * Lists are prefixed with a l and terminated by an e. The list
    * should contain a series of bEncoded elements. For example, the
    * list of strings ["Monduna", "Bit", "Torrents"] would bEncode to
    * l7:Monduna3:Bit8:Torrentse. The list [1, "Monduna", 3, ["Sub", "List"]]
    * would bEncode to li1e7:Mondunai3el3:Sub4:Listee
    *
    * @param array
    * @return string
    */
    function encode_array($array)
    {
        // Check for strings in the keys
        $isList = true;
        foreach (array_keys($array) as $key) {
            if (!is_int($key)) {
                $isList = false;
                break;
            }
        }
        if ($isList) {
            // Wie build a list
            ksort($array, SORT_NUMERIC);
            $return = 'l';
            foreach ($array as $val) {
                $return .= $this->encode($val);
            }
            $return .= 'e';
        } else {
            // We build a Dictionary
            ksort($array, SORT_STRING);
            $return = 'd';
            foreach ($array as $key => $val) {
                $return .= $this->encode(strval($key));
                $return .= $this->encode($val);
            }
            $return .= 'e';
        }
        return $return;
    }
}

?>
