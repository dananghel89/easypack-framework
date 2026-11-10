<?php
/**
 * Epack Translation Class
 *
 * Locale files are PHP files that return an array (`locales/en.php`,
 * `locales/de-AT.php`, …). epack_init() points the class at
 * APP_PATH/locales with APP_LANG as the default language.
 *
 * Adapted from php-translation-class: configure(), the validated
 * Accept-Language parsing and the fallbacks were added.
 *
 * Original notice:
 *
 *   The MIT License (MIT)
 *   Copyright (c) 2015 Interativa Digital
 *
 *   Permission is hereby granted, free of charge, to any person obtaining a copy
 *   of this software and associated documentation files (the "Software"), to deal
 *   in the Software without restriction, including without limitation the rights
 *   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *   copies of the Software, and to permit persons to whom the Software is
 *   furnished to do so, subject to the following conditions:
 *
 *   The above copyright notice and this permission notice shall be included in
 *   all copies or substantial portions of the Software.
 *
 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *   FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *   SOFTWARE.
 *
 * @see https://github.com/InterativaDigital/php-translation-class
 */

namespace Epack\Tools;

use Exception;

class Translation {

	/**
	 * Directory path of the locale files.
	 * @type string
	 */
	protected static $localesDir = 'locales';

	/**
	 * The default language to use if the client language or the forced language locales are not found.
	 * @type string
	 */
	protected static $defaultLanguage = 'en-US';

	/**
	 * Array where loaded locales will be kept to avoid the need of reload the locale file.
	 * @type array
	 */
	protected static $loadedLocales = array();

	/**
	 * The current language being used to translate.
	 * @type string
	 */
	protected static $currentLanguage = '';

	/**
	 * Determine whether the class has been initialized.
	 * @type bool
	 */
	protected static $initialized = false;

	/**
	 * Definition of the enclosing characters for the parameters inside the translation strings.
	 * @type array
	 */
	protected static $parameterEnclosingChars = array('{', '}');

	/**
	 * Sets the locales directory and the default language in one go, without
	 * requiring either to exist: a missing directory or file just means
	 * "no translations" (the keys are returned as they are).
	 * @param string $localesDir
	 * @param string $defaultLanguage
	 */
	public static function configure($localesDir, $defaultLanguage)
	{
		static::$localesDir = (string) $localesDir;
		static::$defaultLanguage = (string) $defaultLanguage;
		static::$loadedLocales = array();
		static::$currentLanguage = static::resolveLanguage(static::getClientLanguage());
		static::$initialized = true;
	}

	/**
	 * Returns the translation of a specific key from the current language locale.
	 * Optionally you can fill the parameters array with the corresponding string replacements.
	 * @param string $localeKey
	 * @param array $parameters
	 * @return string|null
	 */
	public static function of($localeKey, $parameters = array())
	{
		static::checkIfHasBeenInitialized();

		if (!is_string($localeKey)) {
			return null;
		}

		$text = static::lookup(static::$currentLanguage, $localeKey);

		if ($text === null && static::$currentLanguage !== static::$defaultLanguage) {
			$text = static::lookup(static::$defaultLanguage, $localeKey);
		}

		if ($text === null) {
			return null;
		}

		if (!empty($parameters) && is_array($parameters)) {
			foreach ($parameters as $parameter => $replacement) {
				$text = str_replace(static::$parameterEnclosingChars[0] . $parameter . static::$parameterEnclosingChars[1], $replacement, $text);
			}
		}

		return $text;
	}

	/**
	 * Used to set a custom locale directory.
	 * @param string $localesDir
	 * @throws Exception when the directory does not exist
	 */
	public static function setLocalesDir($localesDir)
	{
		if (!empty($localesDir) && is_string($localesDir)) {
			if (!is_dir($localesDir)) {
				throw new Exception('Locales directory not found: ' . basename($localesDir));
			}

			static::$localesDir = $localesDir;
			static::$loadedLocales = array();
		}
	}

	/**
	 * Used to set a custom default language.
	 * @param string $defaultLanguage
	 * @throws Exception when there is no locale file for the language
	 */
	public static function setDefaultLanguage($defaultLanguage)
	{
		if (!empty($defaultLanguage) && is_string($defaultLanguage)) {
			if (!static::localeExists($defaultLanguage)) {
				throw new Exception('Default language locale not found!');
			}

			static::$defaultLanguage = $defaultLanguage;
		}
	}

	/**
	 * Used to force the translations in a specific language from now on.
	 * To cancel the enforcement, at any time, you can call "forceLanguage(null)",
	 * then it restarts using the client language or the default language.
	 * @param string|null $language
	 */
	public static function forceLanguage($language)
	{
		static::checkIfHasBeenInitialized();

		$language = (!empty($language) && is_string($language)) ? $language : static::getClientLanguage();

		static::$currentLanguage = static::resolveLanguage($language);
	}

	/**
	 * Used to set custom parameter enclosing characters.
	 * @param string $openingChar
	 * @param string $closingChar
	 */
	public static function setParameterEnclosingChars($openingChar = '{', $closingChar = '}')
	{
		if (!empty($openingChar) && is_string($openingChar) && !empty($closingChar) && is_string($closingChar)) {
			static::$parameterEnclosingChars = array($openingChar, $closingChar);
		}
	}

	/**
	 * Returns a key of a language, loading its locale file once.
	 * @param string $language
	 * @param string $localeKey
	 * @return string|null
	 */
	protected static function lookup($language, $localeKey)
	{
		if (!array_key_exists($language, static::$loadedLocales)) {
			$locale = static::localeExists($language) ? require static::localeFile($language) : array();
			static::$loadedLocales[$language] = is_array($locale) ? $locale : array();
		}

		$text = static::$loadedLocales[$language][$localeKey] ?? null;

		return (is_string($text) && $text !== '') ? $text : null;
	}

	/**
	 * Picks the language to use: the requested one, then its base language
	 * (`de` for `de-AT`), then the default language.
	 * @param string|null $language a validated language code
	 * @return string
	 */
	protected static function resolveLanguage($language)
	{
		if (!empty($language)) {
			$candidates = array($language, substr($language, 0, 2));

			foreach ($candidates as $candidate) {
				if (static::localeExists($candidate)) {
					return $candidate;
				}
			}
		}

		return static::$defaultLanguage;
	}

	/**
	 * Returns the client language code from the Accept-Language header,
	 * like 'en-US' or 'de', or null when it is missing or not a valid code.
	 * Only `xx` and `xx-YY` codes are accepted, so the header can never
	 * point to another file.
	 * @return string|null
	 */
	protected static function getClientLanguage()
	{
		$header = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';

		if (!is_string($header) || !preg_match('/^\s*([a-z]{2})(?:[-_]([a-z]{2}))?\s*(?:[,;]|$)/i', $header, $matches)) {
			return null;
		}

		$language = strtolower($matches[1]);

		if (!empty($matches[2])) {
			$language .= '-' . strtoupper($matches[2]);
		}

		return $language;
	}

	/**
	 * Checks if a locale file exists for a language.
	 * @param string $language
	 * @return bool
	 */
	protected static function localeExists($language)
	{
		return is_string($language)
			&& preg_match('/^[a-z]{2}(-[A-Z]{2})?$/', $language)
			&& is_file(static::localeFile($language));
	}

	/**
	 * Returns the path of a language's locale file.
	 * @param string $language
	 * @return string
	 */
	protected static function localeFile($language)
	{
		return static::$localesDir . '/' . $language . '.php';
	}

	/**
	 * Checks if the class has been initialized.
	 */
	protected static function checkIfHasBeenInitialized()
	{
		if (!static::$initialized) {
			static::configure(static::$localesDir, static::$defaultLanguage);
		}
	}

}
