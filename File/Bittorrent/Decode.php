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
* Info on the .torrent file format
* BEncoding is a simple, easy to implement method of associating
* data types with information in a file. The values in a torrent
* file are bEncoded.
* There are 4 different data types that can be bEncoded:
* Integers, Strings, Lists and Dictionaries.
* [http://www.monduna.com/bt/faq.html]
*
* @package File_Bittorrent
* @category File
* @author Markus Tacker <m@tacker.org>
* @version $Id$
*/

/**
* Include required classes
*/
require_once 'PEAR.php';
require_once 'PHP/Compat.php';
require_once 'File/Bittorrent/Encode.php';

/**
* Load replacement functions
*/
PHP_Compat::loadFunction('file_get_contents');

/**
* Encode data in Bittorrent format
*
* Based on
*   Original Python implementation by Petru Paler <petru@paler.net>
*   PHP translation by Gerard Krijgsman <webmaster@animesuki.com>
*   Gerard's regular expressions removed by Carl Ritson <critson@perlfu.co.uk>
* Info on the .torrent file format
* BEncoding is a simple, easy to implement method of associating
* data types with information in a file. The values in a torrent
* file are bEncoded.
* There are 4 different data types that can be bEncoded:
* Integers, Strings, Lists and Dictionaries.
* [http://www.monduna.com/bt/faq.html]
*
* @package File_Bittorrent
* @category File
* @author Markus Tacker <m@tacker.org>
*/
class File_Bittorrent_Decode
{
    /**
    * @var string   Name of the torrent
    */
    var $name = '';

    /**
    * @var string   Filename of the torrent
    */
    var $filename = '';

    /**
    * @var string   Comment
    */
    var $comment = '';

    /**
    * @var int   Creation date as unix timestamp
    */
    var $date = 0;

    /**
    * @var array    Files in the torrent
    */
    var $files = array();

    /**
    * @var int      Size of of the full torrent (after download)
    */
    var $size = 0;

    /**
    * @var string   Signature of the software which created the torrent
    */
    var $created_by = '';

    /**
    * @var string    tracker (the tracker the torrent has been received from)
    */
    var $announce = '';

    /**
    * @var array     List of known trackers for the torrent
    */
    var $announce_list = array();

    /**
    * @var string   Source string
    * @access private
    */
    var $_source = '';

    /**
    * @var int      Source length
    * @access private
    */
    var $_source_length = 0;

    /**
    * @var int      Current position of the string
    * @access private
    */
    var $_position = 0;

    /**
    * @var string   Info hash
    */
    var $info_hash;

    /**
    * Decode a Bencoded string
    *
    * @param string
    * @return mixed
    */
    function decode($str)
    {
        $this->_source = $str;
        $this->_position  = 0;
        $this->_source_length = strlen($this->_source);
        return $this->_bdecode();
    }

    /**
    * Decode .torrent file and accumulate information
    *
    * @param string    Filename
    * @return mixed    Returns an arrayon success or false on error
    */
    function decodeFile($file)
    {
        // Check file
        if (!is_file($file)) {
            PEAR::raiseError('File_Bittorrent_Decode::decode() - Not a file.', null, null, "Given filename '$file' is not a valid file.");
            return false;
        }

        // Reset public attributes
        $this->name          = '';
        $this->filename      = '';
        $this->comment       = '';
        $this->date          = 0;
        $this->files         = array();
        $this->size          = 0;
        $this->created_by    = '';
        $this->announce      = '';
        $this->announce_list = array();
        $this->_position     = 0;
        $this->info_hash     = '';

        // Decode .torrent
        $this->_source = file_get_contents($file);
        $this->_source_length = strlen($this->_source);
        $decoded = $this->_bdecode();

        // Compute info_hash
        $Encoder = new File_Bittorrent_Encode;
        $this->info_hash = sha1($Encoder->encode($decoded['info']));

        // Pull information form decoded data
        $this->filename = basename($file);
        // Name of the torrent - statet by the torrent's author
        $this->name     = $decoded['info']['name'];
        // Authors may add comments to a torrent
        if (isset($decoded['comment'])) {
            $this->comment = $decoded['comment'];
        }
        // Creation date of the torrent as unix timestamp
        if (isset($decoded['creation date'])) {
            $this->date = $decoded['creation date'];
        }
        // This contains the signature of the application used to create the torrent
        if (isset($decoded['created by'])) {
            $this->created_by = $decoded['created by'];
        }
        // There is sometimes an array listing all files
        // in the torrent with their individual filesize
        if (isset($decoded['info']['files']) and is_array($decoded['info']['files'])) {
            foreach ($decoded['info']['files'] as $file) {
                // We are computing the total size of the download heres
                $this->size += $file['length'];
                $this->files[] = array(
                    'filename' => $file['path'][0],
                    'size'     => $file['length'],
                );
            }
        }
        // If the the info->length field is present we are dealing with
        // a single file torrent.
        if (isset($decoded['info']['length']) and $this->size == 0) {
            $this->size = $decoded['info']['length'];
        }

        // This contains the tracker the torrent has been received from
        if (isset($decoded['announce'])) {
            $this->announce = $decoded['announce'];
        }

        // This contains a list of all known trackers for this torrent
        if (isset($decoded['announce-list']) and is_array($decoded['announce-list'])) {
            foreach($decoded['announce-list'] as $item) {
                if (!isset($item[0])) continue;
                $this->announce_list[] = $item[0];
            }
        }

        // Currently, I'm not sure how to determine an error
        // Just try to fetch the info from the decoded data
        // and return it
        return array(
            'name'          => $this->name,
            'filename'      => $this->filename,
            'comment'       => $this->comment,
            'date'          => $this->date,
            'created_by'    => $this->created_by,
            'files'         => $this->files,
            'size'          => $this->size,
            'announce'      => $this->announce,
            'announce_list' => $this->announce_list,
        );
    }

    /**
    * Decode a BEncoded String
    *
    * @access private
    * @return mixed    Returns the representation of the data in the BEncoded string or false on error
    */
    function _bdecode()
    {
        switch ($this->_getChar()) {
        case 'i':
            $this->_position++;
            return $this->_decode_int();
            break;
        case 'l':
            $this->_position++;
            return $this->_decode_list();
            break;
        case 'd':
            $this->_position++;
            return $this->_decode_dict();
            break;
        default:
            return $this->_decode_string();
        }
    }

    /**
    * Decode a BEncoded dictionary
    *
    * Dictionaries are prefixed with a d and terminated by an e. They
    * are similar to list, except that items are in key value pairs. The
    * dictionary {"key":"value", "Monduna":"com", "bit":"Torrents", "number":7}
    * would bEncode to d3:key5:value7:Monduna3:com3:bit:8:Torrents6:numberi7ee
    *
    * @access private
    * @return array
    */
    function _decode_dict()
    {
        while ($char = $this->_getChar()) {
            if ($char == 'e') break;
            $key = $this->_decode_string();
            $val = $this->_bdecode();
            $return[$key] = $val;
        }
        $this->_position++;
        return $return;
    }

    /**
    * Decode a BEncoded string
    *
    * Strings are prefixed with their length followed by a colon.
    * For example, "Monduna" would bEncode to 7:Monduna and "BitTorrents"
    * would bEncode to 11:BitTorrents.
    *
    * @access private
    * @return string|false
    */
    function _decode_string()
    {
        // Find position of colon
        // Supress error message if colon is not found which may be caused by a corrupted or wrong encoded string
        if(!$pos_colon = @strpos($this->_source, ':', $this->_position)) {
            return false;
        }
        // Get length of string
        $str_length = intval(substr($this->_source, $this->_position, $pos_colon));
        // Get string
        $return = substr($this->_source, $pos_colon + 1, $str_length);
        // Move Pointer after string
        $this->_position = $pos_colon + $str_length + 1;
        return $return;
    }

    /**
    * Decode a BEncoded integer
    *
    * Integers are prefixed with an i and terminated by an e. For
    * example, 123 would bEcode to i123e, -3272002 would bEncode to
    * i-3272002e.
    *
    * @access private
    * @return int
    */
    function _decode_int()
    {
        $pos_e  = strpos($this->_source, 'e', $this->_position);
        $return = intval(substr($this->_source, $this->_position, $pos_e - $this->_position));
        $this->_position = $pos_e + 1;
        return $return;
    }

    /**
    * Decode a BEncoded list
    *
    * Lists are prefixed with a l and terminated by an e. The list
    * should contain a series of bEncoded elements. For example, the
    * list of strings ["Monduna", "Bit", "Torrents"] would bEncode to
    * l7:Monduna3:Bit8:Torrentse. The list [1, "Monduna", 3, ["Sub", "List"]]
    * would bEncode to li1e7:Mondunai3el3:Sub4:Listee
    *
    * @access private
    * @return array
    */
    function _decode_list()
    {
        $return = array();
        $char = $this->_getChar();
        while ($this->_source{$this->_position} != 'e') {
            $val = $this->_bdecode();
            $return[] = $val;
        }
        $this->_position++;
        return $return;
    }

    /**
    * Get the char at the current position
    *
    * @access private
    * @return string|false
    */
    function _getChar()
    {
        if (empty($this->_source)) return false;
        if ($this->_position >= $this->_source_length) return false;
        return $this->_source{$this->_position};
    }

    /**
    * Returns the online stats for the torrent
    *
    * @return array|false
    */
    function getStats()
    {
        $packed_hash = pack('H*', $this->info_hash);
        $scrape_url = preg_replace('/\/announce$/', '/scrape', $this->announce) . '?info_hash=' . urlencode($packed_hash);
        $scrape_data = file_get_contents($scrape_url);
        $stats = $this->decode($scrape_data);
        if (!isset($stats['files'][$packed_hash])) {
            PEAR::raiseError('File_Bittorrent_Decode::getStats() - Invalid scrape data: "' . $scrape_data . '"');
            return false;
        }
        return $stats['files'][$packed_hash];
    }
}

?>