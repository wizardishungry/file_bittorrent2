<?php

//
// +------------------------------------------------------------------------+
// | PHP Version 4                                                          |
// +------------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                  |
// +------------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,        |
// | that is bundled with this package in the file LICENSE, and is          |
// | available at through the world-wide-web at                             |
// | http://www.php.net/license/2_02.txt.                                   |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
// | Author: Justin Jones <j.nagash@gmail.com>                              |
// +------------------------------------------------------------------------+
//

/**
 * Provides a class for making .torrent files
 * from a file or directory. Produces virtually
 * identical torrent files as btmaketorrent.py
 * from Bram Cohen's original BT client.
 *
 * @package File_Bittorrent
 * @category File
 *
 * @author  Justin Jones <j.nagash@gmail.com>
 *
 * @version $Id$
 */

/**
 * Creates .torrent files
 *
 * @package File_Bittorrent
 * @category File
 */
class File_Bittorrent_MakeTorrent
{
    /**
     * Path to the file or directory to create the
     * torrent from.
     *
     * @var string
     */
    var $path = '';

    /**
     * Whether or not $path is a file
     *
     * @var bool
     */
    var $_isFile = false;

    /**
     * Where or not $path is a directory
     *
     * @var bool
     */
    var $_isDir = false;

    /**
     * The .torrent info dictionary
     *
     * @var array
     */
    var $info = array();

    /**
     * The .torrent announce URL
     *
     * @var string
     */
    var $announce = '';

    /**
     * The .torrent announce_list extension
     *
     * @var array
     */
    var $announce_list = array();

    /**
     * The .torrent creation timestamp
     *
     * @var int
     */
    var $creation_date = 0;

    /**
     * The .torrent comment
     *
     * @var string
     */
    var $comment = '';

    /**
     * The .torrent created by string
     *
     * @var string
     */
    var $created_by = 'File_Bittorrent_MakeTorrent $Rev$';

    /**
     * The .torrent suggested name (file/dir)
     *
     * @var string
     */
    var $name = '';

    /**
     * The .torrent packed piece data
     *
     * @var string
     */
    var $pieces = '';

    /**
     * The .torrent piece length
     * The size of each piece in bytes.
     *
     * @var int
     */
    var $piece_length = 524288;

    /**
     * The list of files (if this is
     * a multi-file torrent)
     *
     * @var array
     */
    var $files = array();

    /**
     * The data gap used to join two
     * files into the same piece
     *
     * @var mixed string if it contains data or boolean false
     */
    var $data_gap = false;

    /**
     * Constructor
     *
     * Sets up the path to the file/dir to create
     * a torrent from
     *
     * @param string Path to use
     * @return voide
     * @access public
     */
    function File_Bittorrent_MakeTorrent($path)
    {
        $this->path = $path;
        if (is_dir($path)) {
            $this->_isDir = true;
            $this->name = basename($path);
        } else if (is_file($path)) {
            $this->_isFile = true;
            $this->name = basename($path);
        } else {
            $this->path = '';
        }
    }

    /**
     * Function to set the announce URL for
     * the .torrent file
     *
     * @param string announce url
     * @return void
     * @access public
     */
    function setAnnounce($announce)
    {
        $this->announce = strval($announce);
    }

    /**
     * Function to set the announce list for
     * the .torrent file
     *
     * @param array announce list
     * @return void
     * @access public
     */
    function setAnnounceList($announce_list)
    {
        if (is_array($announce_list))
        {
            $this->announce_list = $announce_list;
        }
    }

    /**
     * Function to set the comment for the
     * .torrent file
     *
     * @param string comment
     * @return void
     * @access public
     */
    function setComment($comment)
    {
        $this->comment = strval($comment);
    }

    /**
     * Function to set the created by timestamp
     * for the .torrent file. If you don't want
     * to use the servers current timestamp
     *
     * @param int timestamp
     * @return void
     * @access public
     */
    function setCreatedBy($created_by)
    {
        $this->created_by = strval($created_by);
    }

    /**
     * Function to set the path for the
     * file/dir to make the .torrent for
     * Can also be set through the constructor.
     *
     * @param string path to file/dir
     * @return void
     * @access public
     */
    function setPath($path)
    {
        $this->path = $path;
        if (is_dir($path)) {
            $this->_isDir = true;
            $this->name = basename($path);
        } else if (is_file($path)) {
            $this->_isFile = true;
            $this->name = basename($path);
        } else {
            $this->path = '';
        }
    }

    /**
     * Function to set the piece length for
     * the .torrent file.
     * min: 32 (32KB), max: 4096 (4MB)
     *
     * @param int piece length in kilobytes
     * @return void
     * @access public
     */
    function setPieceLength($piece_length)
    {
        if ($piece_length >= 32 && $piece_length <= 4096) {
            $this->piece_length = $piece_length * 1024;
        }
    }

    /**
     * Function to build the .torrent file
     * based on the parameters you have set
     * with the set* functions.
     *
     * @return mixed false on failure or a string containing the metainfo
     * @access public
     */
    function buildTorrent()
    {
        if ($this->_isFile) {
            $info = $this->_addFile($this->path);
            if ($info !== false) {
                $metainfo = $this->_encodeTorrent($info);
                return $metainfo;
            }
        } else if ($this->_isDir) {
            $diradd_ok = $this->_addDir($this->path);
            if ($diradd_ok !== false) {
                $metainfo = $this->_encodeTorrent();
                return $metainfo;
            }
        } else {
            return false;
        }
    }

    /**
     * Internal function which bencodes the data
     * into a valid torrent metainfo string
     *
     * @param array file data
     * @return mixed false on failure or the bencoded metainfo string
     * @access private
     */
    function _encodeTorrent($info = array())
    {
        require_once 'File/Bittorrent/Encode.php';
        $benc = new File_Bittorrent_Encode;

        $bencdata = array();
        $bencdata['info'] = array();
        if ($this->_isFile) {
            $bencdata['info']['length'] = $info['length'];
            $bencdata['info']['md5sum'] = $info['md5sum'];
        } else if ($this->_isDir) {
            if ($this->data_gap !== false) {
                $this->pieces .= pack('H*', sha1($this->data_gap));
                $this->data_gap = false;
            }
            $bencdata['info']['files'] = $this->files;
        } else {
            return false;
        }
        $bencdata['info']['name'] = $this->name;
        $bencdata['info']['piece length'] = $this->piece_length;
        $bencdata['info']['pieces'] = $this->pieces;
        $bencdata['announce'] = $this->announce;
        //$bencdata['announce-list'] = array($this->announce)
        $bencdata['creation date'] = time();
        $bencdata['comment'] = $this->comment;
        $bencdata['created by'] = $this->created_by;
        return $benc->encode_array($bencdata);
    }

    /**
     * Internal function which generates
     * metainfo data for a file
     *
     * @param string path to the file
     * @return mixed false on failure or file metainfo data
     * @access private
     */
    function _addFile($file)
    {
        $fp = &File_Bittorrent_MakeTorrent::_openfile($file);
        if ($fp) {
            $filelength = 0;
            $md5sum = md5_file($file);
            $piece_length = $this->piece_length;

            while (!feof($fp)) {
                $data = '';
                $datalength = 0;

                if ($this->_isDir && $this->data_gap !== false) {
                    $data = $this->data_gap;
                    $datalength = strlen($data);
                    $this->data_gap = false;
                }

                while ( !feof($fp) && ($datalength < $piece_length) ) {
                    $readlength = 8192;
                    if ( ($datalength + 8192) > $piece_length ) {
                        $readlength = $piece_length - $datalength;
                    }

                    $tmpdata = fread($fp, $readlength);
                    $actual_readlength = strlen($tmpdata);
                    $datalength += $actual_readlength;
                    $filelength += $actual_readlength;

                    $data .= $tmpdata;

                    flush();
                }

                /* We've either reached the end of the file, or
                 * we have a whole piece, or
                 * both.
                 */
                if ($datalength == $piece_length) {
                    // We have a piece.
                    $this->pieces .= pack('H*', sha1($data));
                }
                if ( ($datalength != $piece_length) && feof($fp) ) {
                    // We've reached the end of the file, and
                    // we dont have a whole piece.
                    if ($this->_isDir) {
                        $this->data_gap = $data;
                    } else {
                        $this->pieces .= pack('H*', sha1($data));
                    }
                }
            }
            // Close the file pointer.
            File_Bittorrent_MakeTorrent::_closefile($fp);
            $info = array(
                    'length' => $filelength,
                    'md5sum' => $md5sum
                    );
            return $info;
        }
        return false;
    }

    /**
     * Internal function which iterates through
     * directories and subdirectories, using
     * _addFile for each file it finds.
     *
     * @param string path to the directory
     * @return void
     * @access private
     */
    function _addDir($path)
    {
        $filelist = $this->_dirList($path);
        sort($filelist);

        foreach ($filelist as $file) {
            $filedata = $this->_addFile($file);
            if ($filedata !== false) {
                $filedata['path'] = array();
                $filedata['path'][] = basename($file);
                $dirname = dirname($file);
                while ( basename($dirname) != $this->name ) {
                    $filedata['path'][] = basename($dirname);
                    $dirname = dirname($dirname);
                }
                $filedata['path'] = array_reverse($filedata['path'], false);
                $this->files[] = $filedata;
            }
        }
    }

    /**
     * Internal function which recurses through
     * subdirectory and returns an array of file paths
     *
     * @param string path to the directory
     * @return array file list
     * @access private
     */
    function _dirList($dir)
    {
        $dir = realpath($dir);
        $file_list = '';
        $stack[] = $dir;

        while ($stack) {
            $current_dir = array_pop($stack);
            if ($dh = opendir($current_dir)) {
                while ( ($file = readdir($dh)) !== false ) {
                    if ($file !== '.' && $file !== '..') {
                        $current_file = $current_dir . '/' . $file;

                        if ( is_file($current_file) ) {
                            $file_list[] = $current_dir . '/' . $file;
                        } else if ( is_dir($current_file) ) {
                            $stack[] = $current_file;
                        }
                    }
                }
            }
        }
        return $file_list;
    }

    /**
     * Internal function to get the filesize
     * of a file. Workaround for files >2GB.
     *
     * @param string path to the file
     * @return int the filesize
     * @access private
     */
    function _filesize($file) {
        $size = @filesize($file);
        if ($size == 0) {
            $size = exec('du -b "'.$file.'"');
        }
        return $size;
    }

    /**
     * Internal function to open a file.
     * Workaround for files >2GB using popen
     *
     * @param string path to the file
     * @return mixed file pointer or false
     * @access private
     */
    function &_openfile($file) {
        $fsize = File_Bittorrent_MakeTorrent::_filesize($file);
        if ($fsize <= 2*1024*1024*1024) {
            $fp = fopen($file, 'r');
            $this->_fopen = true;
        } else {
            $fp = popen('cat "'.$file.'"', 'r');
            $this->_fopen = false;
        }
        return $fp;
    }

    /**
     * Internal function to close a file pointer
     *
     * @param resource File Pointer
     * @access private
     */
    function _closefile(&$fp)
    {
        if ($this->_fopen == true) {
            fclose($fp);
        } else {
            pclose($fp);
        }
    }
}

?>