<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flídr (https://github.com/mvccore/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/4.0.0/LICENCE.md
 */

namespace MvcCore\Ext\Routers\Localization;

trait Preparing
{
	/**
	 * Prepare localizations processing:
	 * - Check if any default localization configured.
	 * - Complete allowed localizations into array keyed by it's values to check by `isset()`.
	 * - Complete default localization string for comparations later.
	 * - Put default localization into allowed localizations for sure.
	 * - Complete localization equivalents with language codes only by router configuration.
	 * - Try to complete switching param from request object global `$_GET` collection.
	 * - Try to complete session language and locale.
	 * - Store request path before next request localization detection request path manipulation.
	 * - Try to complete request language and locale from request query string or path.
	 * @throws \InvalidArgumentException No default localization configured
	 * @return void
	 */
	protected function prepareLocalization () {
		// check all necessary properties configured
		if (!$this->defaultLocalization)
			throw new \InvalidArgumentException("[".__CLASS__."] No default localization configured.");

		// prepare possibly modified allowed localizations
		$this->allowedLocalizations = array_combine($this->allowedLocalizations, $this->allowedLocalizations);
		// add default localization into allowed langs for sure
		$this->defaultLocalizationStr = implode(static::LANG_AND_LOCALE_SEPARATOR, $this->defaultLocalization);
		$this->allowedLocalizations[$this->defaultLocalizationStr] = $this->defaultLocalizationStr;

		// add automaticly into equivalents also all langs parsed from localizations if necessary
		if ($this->detectLocalizationOnlyByLang) {
			foreach ($this->allowedLocalizations as $allowedLocalization => $allowedLocalizationValue) {
				$separatorPos = strpos($allowedLocalization, static::LANG_AND_LOCALE_SEPARATOR);
				if ($separatorPos !== FALSE) {
					$lang = substr($allowedLocalization, 0, $separatorPos);
					if (!isset($this->localizationEquivalents[$lang]))
						$this->localizationEquivalents[$lang] = $allowedLocalization;
				}
			}
		}
		
		// check it with any strict session configuration to have more flexible navigations
		//if ($this->stricModeBySession) {
			$sessStrictModeSwitchUrlParam = static::URL_PARAM_SWITCH_LOCALIZATION;
			if (isset($this->requestGlobalGet[$sessStrictModeSwitchUrlParam])) {
				$globalGetValue = strtolower($this->requestGlobalGet[$sessStrictModeSwitchUrlParam]);
				$separatorPos = strpos($globalGetValue, static::LANG_AND_LOCALE_SEPARATOR);
				if ($separatorPos !== FALSE)
					$globalGetValue = substr($globalGetValue, 0, $separatorPos + 1)
						. strtoupper(substr($globalGetValue, $separatorPos + 1));
				if (isset($this->allowedLocalizations[$globalGetValue]))
					$this->switchUriParamLocalization = $globalGetValue;
			}
		//}
		
		// look into session object if there are or not
		// any record about lang from previous request
		if (isset($this->session->{static::URL_PARAM_LOCALIZATION})) 
			$this->sessionLocalization = $this->session->{static::URL_PARAM_LOCALIZATION};
		
		// store path info localy for routing process
		$this->originalRequestPath = $this->request->GetPath();

		// get current localization from requested url string
		$this->prepareRequestLocalizationFromUrl();
	}

	/**
	 * Try to set up lang (or lang and locale) from request query string and if 
	 * there i no localization query param, try to set up localization from
	 * request path if any routes defined. If there ss any lang detected (or 
	 * lang and locale detected), set up these values into request object.
	 * @return void
	 */
	protected function prepareRequestLocalizationFromUrl () {
		$this->prepareRequestLocalizationFromUrlQueryString();
		if ($this->requestLocalization === NULL && $this->anyRoutesConfigured) 
			$this->prepareRequestLocalizationFromUrlPath();
	}

	/**
	 * Try to set up lang (or lang and locale) from request query string.
	 * @return void
	 */
	protected function prepareRequestLocalizationFromUrlQueryString () {
		$this->requestLocalization = NULL;
		$localizationUrlParam = static::URL_PARAM_LOCALIZATION;
		$langAndLocaleSeparator = static::LANG_AND_LOCALE_SEPARATOR;
		// try ty set up request localization by query string first - query string is always stronger value
		$requestLocalization = $this->request->GetParam(
			$localizationUrlParam, 
			$langAndLocaleSeparator . 'a-zA-Z0-9'
		);
		$requestLocalizationValidStr = $requestLocalization && strlen($requestLocalization) > 0;
		if ($requestLocalizationValidStr) {
			$requestLocalization = strtolower($requestLocalization);
			$separatorPos = strpos($requestLocalization, static::LANG_AND_LOCALE_SEPARATOR);
			if ($separatorPos !== FALSE) 
				$requestLocalization = substr($requestLocalization, 0, $separatorPos + 1)
					. strtoupper(substr($requestLocalization, $separatorPos + 1));
		}
		if ($requestLocalizationValidStr && isset($this->allowedLocalizations[$requestLocalization])) {
			$this->requestLocalization = explode($langAndLocaleSeparator, $requestLocalization);
			$this->request->SetLang($this->requestLocalization[0]);
			if ($this->requestLocalization[1]) $this->request->SetLocale($this->requestLocalization[1]);
		} else if (isset($this->localizationEquivalents[$requestLocalization])) {
			$targetLocalization = explode($langAndLocaleSeparator, $this->localizationEquivalents[$requestLocalization]);
			if ($this->stricModeBySession && $this->sessionLocalization) {
				$this->requestLocalizationEquivalent = $this->sessionLocalization;
			} else {
				$this->requestLocalizationEquivalent = $targetLocalization;
			}
		}
	}
	
	/**
	 * Try to set up lang (or lang and locale) from request path.
	 * If there is any request path detected, remove localization from request 
	 * path and store detected localization in local context.
	 * @return void
	 */
	protected function prepareRequestLocalizationFromUrlPath () {
		// if there is no localization in query string - try to detect localization from path
		$requestPath = $this->request->GetPath(TRUE);
		/**
			* $requestPath = '/'					=> $secondSlashPos = FALSE	=> $firstPathElm = ''
			* $requestPath = '/en'					=> $secondSlashPos = FALSE	=> $firstPathElm = 'en'
			* $requestPath = '/en/'				=> $secondSlashPos = 3		=> $firstPathElm = 'en'
			* $requestPath = '/en/move...'			=> $secondSlashPos = 3		=> $firstPathElm = 'en'
			* $requestPath = '/any-thing'			=> $secondSlashPos = FALSE	=> $firstPathElm = 'any-thing'
			* $requestPath = '/any-thing/'			=> $secondSlashPos = 10		=> $firstPathElm = 'any-thing'
			* $requestPath = '/any-thing/more...'	=> $secondSlashPos = 10		=> $firstPathElm = 'any-thing'
			*/
		$secondSlashPos = mb_strlen($requestPath) > 1 
			? mb_strpos($requestPath, '/', 1) 
			: FALSE;
		if ($secondSlashPos === FALSE) {
			$firstPathElm = $requestPath !== '/' ? mb_substr($requestPath, 1) : '';
		} else {
			$firstPathElm = mb_substr($requestPath, 1, $secondSlashPos - 1);
		}
		$localizationPart = preg_replace(
			"#[^" . static::LANG_AND_LOCALE_SEPARATOR . "a-z0-9]#", 
			'', strtolower($firstPathElm)
		);
		$separatorPos = strpos($localizationPart, static::LANG_AND_LOCALE_SEPARATOR);
		if ($separatorPos !== FALSE) 
			$localizationPart = substr($localizationPart, 0, $separatorPos + 1)
				. strtoupper(substr($localizationPart, $separatorPos + 1));
		if (isset($this->allowedLocalizations[$localizationPart])) {
			$langAndLocale = explode(static::LANG_AND_LOCALE_SEPARATOR, $localizationPart);
			if (strlen($langAndLocale[0]) > 0) {
				$this->requestLocalization = $langAndLocale;
				$newPath = mb_substr($requestPath, strlen($localizationPart) + 1);
				if ($newPath === '') $newPath = '/';
				$this->request
					->SetLang($langAndLocale[0])
					->SetPath($newPath);
				if (count($langAndLocale) > 1 && strlen($langAndLocale[1]) > 0) 
					$this->request->SetLocale($langAndLocale[1]);
			}
		} else if (isset($this->localizationEquivalents[$localizationPart])) {
			$targetLocalization = explode(static::LANG_AND_LOCALE_SEPARATOR, $this->localizationEquivalents[$localizationPart]);
			$newPath = mb_substr($requestPath, strlen($localizationPart) + 1);
			if ($newPath === '') $newPath = '/';
			$this->request->SetPath($newPath);
			if ($this->stricModeBySession && $this->sessionLocalization) {
				$this->requestLocalizationEquivalent = $this->sessionLocalization;
			} else {
				$this->requestLocalizationEquivalent = $targetLocalization;
			}
		}
		if (
			$this->requestLocalization === NULL && 
			(trim($requestPath, '/') === '' && $requestPath !== $this->request->GetScriptName())
		) {
			$this->requestLocalization = $this->defaultLocalization;
			$this->request->SetLang($this->requestLocalization[0]);
			if ($this->requestLocalization[1]) 
				$this->request->SetLocale($this->requestLocalization[1]);
		}
	}
}
