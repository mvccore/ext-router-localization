<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flidr (https://github.com/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/5.0.0/LICENSE.md
 */

namespace MvcCore\Ext\Routers\Localizations\Route;

trait InternalInits {

	/**
	 * Initialize all possible protected values (`match`, `reverse` etc...) for 
	 * all configured localizations defined in localized router. This method is 
	 * not recommended to use in production mode, it's designed mostly for 
	 * development purposes, to see what could be inside route object.
	 * @return \MvcCore\Ext\Routers\Localizations\Route
	 */
	public function InitAll () {
		/** @var $this \MvcCore\Ext\Routers\Localizations\Route */
		$router = $this->router;
		$allowedLocalizations = $router->GetAllowedLocalizations();
		$routeRecordsByLanguageAndLocale = $router->getRouteRecordsByLanguageAndLocale();
		foreach ($allowedLocalizations as $allowedLocalization) {
			if ($routeRecordsByLanguageAndLocale) {
				$routeLocalization = $allowedLocalization;
			} else {
				$allowedLocalizationExpl = explode($router::LANG_AND_LOCALE_SEPARATOR, $allowedLocalization);
				$routeLocalization = strtolower($allowedLocalizationExpl[0]);
			}
			$noMatch = $this->match === NULL && !array_key_exists($routeLocalization, $this->matchLocalized);
			$noReverse = $this->reverse === NULL && !array_key_exists($routeLocalization, $this->reverseLocalized);
			if ($noMatch && $noReverse) {
				$this->initMatchAndReverse($routeLocalization);
			} else if ($noReverse) {
				$this->initReverse($routeLocalization);
			}
		}
		return $this;
	}

	/**
	 * Initialize properties `match`, `reverse` and other internal properties
	 * about those values under specific localization key only. This method 
	 * is called when there is necessary to prepare localized `pattern` value 
	 * for: a) PHP `preg_match_all()` route match processing, b) for `reverse` 
	 * value for later self URL building. This method is usually called in core 
	 * request routing process from `\MvcCore\Router::Matches();` method on each 
	 * route. 
	 * @param string $localization	Lower case language code, optionally with 
	 *								dash and upper case locale code.
	 * @throws \LogicException Route configuration property is missing.
	 * @throws \InvalidArgumentException Wrong route pattern format.
	 * @return void
	 */
	protected function initMatchAndReverse ($localization = NULL) {
		/** @var $this \MvcCore\Ext\Routers\Localizations\Route */
		if (array_key_exists($localization, $this->reverseSectionsLocalized)) return;
		$pattern = NULL;
		$reverse = NULL;
		if ($this->pattern !== NULL) {
			$pattern = $this->pattern;
		} else if (isset($this->patternLocalized[$localization])) {
			$pattern = $this->patternLocalized[$localization];
		} else {
			$this->throwExceptionIfKeyPropertyIsMissing(
				'pattern', 'patternLocalized'
			);
		}
		if ($this->reverse !== NULL) {
			$reverse = $this->reverse;
		} else if (isset($this->reverseLocalized[$localization])) {
			$reverse = $this->reverseLocalized[$localization];
		}
		
		$this->lastPatternParam = NULL;
		$match = addcslashes($pattern, "#(){}-?!=^$.+|:*\\");
		$reverse = $reverse !== NULL
			? $reverse
			: $pattern;
		
		list($reverseSections, $matchSections) = $this->initSectionsInfoForMatchAndReverse(
			$reverse, $match
		);
		$this->reverseSectionsLocalized[$localization] = $reverseSections;
		$this->reverseLocalized[$localization] = $reverse;
		$constraintsLocalized = $this->GetConstraints($localization);
		$reverseParams = $this->initReverseParams(
			$reverse, $reverseSections, $constraintsLocalized, $match
		);
		$this->reverseParamsLocalized[$localization] = $reverseParams;
		$this->reverseParams = array_keys($reverseParams);
		$this->initFlagsByPatternOrReverse($reverse);
		$this->matchLocalized[$localization] = $this->initMatchComposeRegex(
			$match, $matchSections, $reverseParams, $constraintsLocalized
		);
	}

	/**
	 * Initialize property `reverse` and other internal properties about this 
	 * value under specific localization key only. This method is called, when 
	 * there is necessary to prepare it for: a) URL building, b) for request 
	 * routing, when there is configured `match` property directly an when is 
	 * necessary to initialize route flags from `reverse` to complete correctly 
	 * subject to match.
	 * @param string $localization	Lower case language code, optionally with 
	 *								dash and upper case locale code.
	 * @throws \LogicException Route configuration property is missing.
	 * @throws \InvalidArgumentException Wrong route pattern format.
	 * @return void
	 */
	protected function initReverse ($localization = NULL) {
		/** @var $this \MvcCore\Ext\Routers\Localizations\Route */
		if (array_key_exists($localization, $this->reverseSectionsLocalized)) return;
		$reverse = NULL;
		if ($this->reverse !== NULL) {
			$reverse = $this->reverse;
		} else if (isset($this->reverseLocalized[$localization])) {
			$reverse = $this->reverseLocalized[$localization];
		} else if ($this->pattern !== NULL) {
			$reverse = $this->pattern;
		} else if (isset($this->patternLocalized[$localization])) {
			$reverse = $this->patternLocalized[$localization];
		} else {
			if ($this->redirect !== NULL) {
				$pattern = NULL;
				$match = NULL;
				if ($this->pattern !== NULL) {
					$pattern = $this->pattern;
				} else if (isset($this->patternLocalized[$localization])) {
					$pattern = $this->patternLocalized[$localization];
				}
				if ($this->match !== NULL) {
					$match = $this->match;
				} else if (isset($this->matchLocalized[$localization])) {
					$match = $this->matchLocalized[$localization];
				}
				return $this->initFlagsByPatternOrReverse(
					$pattern !== NULL ? $pattern : str_replace(['\\', '(?', ')?', '/?'], '', $match)
				);
			}
			$this->throwExceptionIfKeyPropertyIsMissing(
				'reverse', 'reverseLocalized', 'pattern', 'patternLocalized'
			);
		}
		
		$this->lastPatternParam = NULL;
		
		$reverseSections = $this->initSectionsInfo($reverse);
		$this->reverseSectionsLocalized[$localization] = $reverseSections;
		$this->reverseLocalized[$localization] = $reverse;

		$match = NULL;
		$constraintsLocalized = $this->GetConstraints($localization);
		$reverseParams = $this->initReverseParams(
			$reverse, $reverseSections, $constraintsLocalized, $match
		);
		$this->reverseParamsLocalized[$localization] = $reverseParams;
		$this->reverseParams = array_keys($reverseParams);

		$this->initFlagsByPatternOrReverse($reverse);
	}
}
