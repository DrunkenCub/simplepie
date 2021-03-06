<?php
/**
 * Tests for autodiscovery
 *
 * SimplePie
 *
 * A PHP-Based RSS and Atom Feed Framework.
 * Takes the hard work out of managing a complete RSS/Atom solution.
 *
 * Copyright (c) 2004-2012, Ryan Parman, Geoffrey Sneddon, Ryan McCue, and contributors
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are
 * permitted provided that the following conditions are met:
 *
 * 	* Redistributions of source code must retain the above copyright notice, this list of
 * 	  conditions and the following disclaimer.
 *
 * 	* Redistributions in binary form must reproduce the above copyright notice, this list
 * 	  of conditions and the following disclaimer in the documentation and/or other materials
 * 	  provided with the distribution.
 *
 * 	* Neither the name of the SimplePie Team nor the names of its contributors may be used
 * 	  to endorse or promote products derived from this software without specific prior
 * 	  written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS
 * OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS
 * AND CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
 * OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package SimplePie
 * @version 1.3-dev
 * @copyright 2004-2011 Ryan Parman, Geoffrey Sneddon, Ryan McCue
 * @author Ryan Parman
 * @author Geoffrey Sneddon
 * @author Ryan McCue
 * @link http://simplepie.org/ SimplePie
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */

require_once dirname(__FILE__) . '/bootstrap.php';

class LocatorTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Tests from Firefox
	 *
	 * Tests are used under the LGPL license, see file for license
	 * information
	 */
	public static function firefoxtests()
	{
		$data = array(
			array(new SimplePie_File(dirname(__FILE__) . '/data/fftests.html'))
		);
		foreach ($data as &$row)
		{
			$row[0]->headers = array('content-type' => 'text/html');
			$row[0]->method = SIMPLEPIE_FILE_SOURCE_REMOTE;
			$row[0]->url = 'http://example.com/';
		}

		return $data;
	}

	/**
	 * @dataProvider firefoxtests
	 */
	public function test_from_file($data)
	{
		$locator = new SimplePie_Locator($data, 0, null, 'MockSimplePie_File', false);

		$expected = SimplePie_Misc::get_element('link', $data->body);

		$feed = $locator->find(SIMPLEPIE_LOCATOR_ALL, $all);
		$this->assertFalse($locator->is_feed($data), 'HTML document not be a feed itself');
		$this->assertInstanceOf('MockSimplePie_File', $feed);
		$expected = array_map(array(get_class(), 'map_url_attrib'), $expected);
		$success = array_filter($expected, array(get_class(), 'filter_success'));

		$found = array_map(array(get_class(), 'map_url_file'), $all);
		$this->assertEquals($success, $found);
	}

	protected static function filter_success($url)
	{
		return (stripos($url, 'bogus') === false);
	}

	protected static function map_url_attrib($elem)
	{
		return 'http://example.com' . $elem['attribs']['href']['data'];
	}

	protected static function map_url_file($file)
	{
		return $file->url;
	}
}

/**
 * Acts as a fake feed request
 */
class MockSimplePie_File extends SimplePie_File
{
	public function __construct($url)
	{
		$this->url = $url;
		$this->headers = array(
			'content-type' => 'application/atom+xml'
		);
		$this->method = SIMPLEPIE_FILE_SOURCE_REMOTE;
		$this->body = '<?xml charset="utf-8"?><feed />';
		$this->status_code = 200;
	}
}