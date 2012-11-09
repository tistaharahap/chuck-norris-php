<?php

/**
 * chuck-norris-php - a PHP client of The Internet Chuck Norris Database.
 *
 * Visit the website http://www.icndb.com/ before Chuck finds out!
 *
 * @package		chuck-norris-php
 * @author		Batista R. Harahap <batista@bango29.com>
 * @link		http://www.bango29.com
 * @license		MIT License - http://www.opensource.org/licenses/mit-license.php
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */

class ChuckNorris {

	private static $BASE_URL = "http://api.icndb.com/";

	public static function getRandomJokes($nums = 1, $inCategories = array(), $notInCategories = array(), $firstName = NULL, $lastName = NULL) {
		$uri = "jokes/random/{$nums}";
		if(!empty($inCategories) || !empty($notInCategories) || !empty($firstName) || !empty($lastName)) {
			$get = array(
				'limitTo' 	=> "",
				'exclude' 	=> "",
				'firstName'	=> !empty($firstName) ? rawurlencode($firstName) : "",
				'lastName'	=> !empty($lastName) ? rawurlencode($lastName) : ""
			);

			if(!empty($inCategories)) {
				$tmp = "";
				foreach($inCategories as $cats) {
					$cats = rawurlencode($cats);
					$tmp .= "{$cats},";
				}
				$tmp = trim($tmp, ',');
				$get['limitTo'] = "[{$tmp}]";
			}
			if(!empty($notInCategories)) {
				$tmp = "";
				foreach($notInCategories as $cats) {
					$cats = rawurlencode($cats);
					$tmp .= "{$cats},";
				}
				$tmp = trim($tmp, ',');
				$get['exclude'] = "[{$tmp}]";
			}

			$uri .= "?limitTo={$get['limitTo']}&exclude={$get['exclude']}&firstName={$get['firstName']}&lastName={$get['lastName']}";
		}

		return self::_assertResponse(
			self::_call($uri)
		);
	}

	/* **
	 * Currently not getting proper response from API,
	 * Chuck has probably found out about the website...
	 *
	 */
	public static function getJokeById($id) {
		if($id > 0) {
			return self::_assertResponse(
				self::_call("jokes/{$id}")
			);
		}
		else {
			self::_chuckIsNotFeelingWell("Don't make fun of Chuck, he'll...");
		}
	}

	public static function getJokesCount() {
		return self::_assertResponse(
			self::_call('jokes/count')
		);
	}

	public static function getJokeCategories() {
		return self::_assertResponse(
			self::_call('categories')
		);
	}

	private static function _assertResponse($response) {
		$check = !empty($response) && !empty($response->value);
		if($check) {
			return $response->value;
		}
	}

	private static function _call($uri, $get = array()) {
		if(empty($uri))
			self::_chuckIsNotFeelingWell("No URI is defined. Don't make fun of Chuck...");

		$url = self::$BASE_URL . $uri;
		if(!empty($get)) {
			$url .= "?";
			foreach($get as $k => $v) {
				$k = rawurlencode($k);
				$v = rawurlencode($v);
				$url .= "{$k}={$v}";
			}
		}

		$c = curl_init();
		curl_setopt($c, CURLOPT_USERAGENT, "chuck-norris-php client");
		curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 1);
		curl_setopt($c, CURLOPT_TIMEOUT, 5);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($c, CURLOPT_URL, $url);

		$response = curl_exec($c);
		$code = curl_getinfo($c, CURLINFO_HTTP_CODE);
		if($code == 200 && !empty($response)) {
			$response = json_decode($response);

			if(!empty($response)) {
				return $response;
			}
			else {
				self::_chuckIsNotFeelingWell("Chuck is angry...naahhh kidding...");
			}
		}
		else {
			self::_chuckIsNotFeelingWell();
		}
	}

	private static function _chuckIsNotFeelingWell($msg = NULL) {
		if(empty($msg))
			$msg = "Chuck is not feeling too well right now...";

		throw new ChuckNorrisException($msg);
	}

}

class ChuckNorrisException extends Exception {}