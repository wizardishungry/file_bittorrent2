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
* Test for Bug #15453
*
* @link http://pear.php.net/bugs/bug.php?id=15453
* @package File_Bittorrent2
* @subpackage Test
* @category File
* @author Markus Tacker <m@tacker.org>
* @version $Id$
*/

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'File/Bittorrent2/Decode.php';

error_reporting( E_ALL | E_STRICT );
ini_set( 'display_errors', 1 );
ini_set( 'log_errors', 0 );

/**
* Test for Bug #15453
*
* @link http://pear.php.net/bugs/bug.php?id=15453
* @package File_Bittorrent2
* @subpackage Test
* @category File
* @author Markus Tacker <m@tacker.org>
* @version $Id$
*/
class Tests_Bug15453 extends PHPUnit_Framework_TestCase
{
	public function testDecodeTorrent()
	{
		$File_Bittorrent2_Decode = new File_Bittorrent2_Decode;
		$info = $File_Bittorrent2_Decode->decode( file_get_contents( './bugs/bug-15453/Brothers.and.Sisters.S03E03.HDTV.XviD-NoTV.avi.4442311.TPB.torrent' ) );
		$info[ 'info' ][ 'pieces' ] = null;
		$this->assertEquals( $info, array (
		  'announce' => 'http://tracker.thepiratebay.org/announce',
		  'announce-list' => array (
			  array ( 'http://tracker.thepiratebay.org/announce' ),
			  array ( 'udp://tracker.thepiratebay.org:80/announce' ),
		  ),
		  'comment' => 'Torrent downloaded from http://thepiratebay.org',
		  'creation date' => 1223867991,
		  'encoding' => 'ANSI_X3.4-1968',
		  'info' => array (
				'length' => 367221186,
				'name' => 'Brothers.and.Sisters.S03E03.HDTV.XviD-NoTV.avi',
				'name.utf-8' => 'Brothers.and.Sisters.S03E03.HDTV.XviD-NoTV.avi',
				'piece length' => 524288,
				'pieces' => NULL,
				'playtime' => '00:41:04.560',
			),
		));
	}
	
	public function testInfoTorrent()
	{
		$File_Bittorrent2_Decode = new File_Bittorrent2_Decode;
		$info = $File_Bittorrent2_Decode->decodeFile( './bugs/bug-15453/Brothers.and.Sisters.S03E03.HDTV.XviD-NoTV.avi.4442311.TPB.torrent' );
		$this->assertEquals( $info, array (
		  'name' => 'Brothers.and.Sisters.S03E03.HDTV.XviD-NoTV.avi',
		  'filename' => 'Brothers.and.Sisters.S03E03.HDTV.XviD-NoTV.avi.4442311.TPB.torrent',
		  'comment' => 'Torrent downloaded from http://thepiratebay.org',
		  'date' => 1223867991,
		  'created_by' => '',
		  'files' => array (
			array (
			  'filename' => 'Brothers.and.Sisters.S03E03.HDTV.XviD-NoTV.avi',
			  'size' => 367221186,
			),
		  ),
		  'size' => 367221186,
		  'announce' => 'http://tracker.thepiratebay.org/announce',
		  'announce_list' => array (
			array ( 'http://tracker.thepiratebay.org/announce' ),
			array ( 'udp://tracker.thepiratebay.org:80/announce' ),
		  ),
		  'info_hash' => '1f6700e842326d207ec0f0f07c72e43774a0c6f1',
		));
	}
}