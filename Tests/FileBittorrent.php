<?php

// +----------------------------------------------------------------------+
// | Decode and Encode data in Bittorrent format                          |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2005 Markus Tacker <m@tacker.org>                 |
// +----------------------------------------------------------------------+
// | This library is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU Lesser General Public           |
// | License as published by the Free Software Foundation; either         |
// | version 2.1 of the License, or (at your option) any later version.   |
// |                                                                      |
// | This library is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU    |
// | Lesser General Public License for more details.                      |
// |                                                                      |
// | You should have received a copy of the GNU Lesser General Public     |
// | License along with this library; if not, write to the                |
// | Free Software Foundation, Inc.                                       |
// | 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA               |
// +----------------------------------------------------------------------+

    /**
    * Test for File_Bittorrent
    *
    * @package File_Bittorrent
    * @subpackage Test
    * @category File
    * @author Markus Tacker <m@tacker.org>
    * @version $Id$
    */

    require_once 'PHPUnit2/Framework/TestCase.php';
    require_once 'File/Bittorrent/Decode.php';

    /**
    * Test for File_Bittorrent
    *
    * @package File_Bittorrent
    * @subpackage Test
    * @category File
    * @author Markus Tacker <m@tacker.org>
    * @version $Id$
    */
    class Tests_FileBittorrent extends PHPUnit2_Framework_TestCase
    {
        public static $torrent = './install-x86-universal-2005.0.iso.torrent';

        public function testInfoHash()
        {
            $File_Bittorrent_Decode = new File_Bittorrent_Decode;
            $File_Bittorrent_Decode->decodeFile(self::$torrent);
            exec('torrentinfo-console ' . escapeshellarg(self::$torrent), $bt);
            $this->assertEquals($File_Bittorrent_Decode->info_hash, substr($bt[3], strpos($bt[3], ':') + 2));
        }
    }

?>