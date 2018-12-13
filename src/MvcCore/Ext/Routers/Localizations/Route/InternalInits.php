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

namespace MvcCore\Ext\Routers\Localizations\Route;

trait InternalInits
{
	/**
	 * Initialize all possible protected values (`match`, `reverse` etc...)
	 * This method is not recommended to use in production mode, it's
	 * designed mostly for development purposes, to see what could be inside route.
	 * @return \MvcCore\Route|\MvcCore\IRoute
	 */
	public function & InitAll () {
		/** @var $this \MvcCore\IRoute */
		$router = & $this->router;
		$localization = $router->GetLocalization();
		$localizationStr = implode($router::LANG_AND_LOCALE_SEPARATOR, $router->GetLocalization());
		$routesLocalization = $router->GetRouteRecordsByLanguageAndLocale()
			? $localizationStr
			: $localization[0];
		$noMatch = $this->match === NULL && !array_key_exists($routesLocalization, $this->matchLocalized);
		$noReverse = $this->reverse === NULL && !array_key_exists($routesLocalization, $this->reverseLocalized);
		if ($noMatch && $noReverse) {
			$this->initMatchAndReverse($routesLocalization);
		} else if ($noReverse) {
			$this->initReverse($routesLocalization);
		}
		return $this;
	}

	protected function initMatchAndReverse ($localization = NULL) {
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
		$this->reverseSectionsLocalized[$localization] = & $reverseSections;
		$this->reverseLocalized[$localization] = & $reverse;
		$constraintsLocalized = & $this->GetConstraints($localization);
		$reverseParams = $this->initReverseParams(
			$reverse, $reverseSections, $constraintsLocalized, $match
		);
		$this->reverseParamsLocalized[$localization] = & $reverseParams;
		$this->reverseParams = array_keys($reverseParams);
		$this->initFlagsByPatternOrReverse($reverse);
		$this->matchLocalized[$localization] = $this->initMatchComposeRegex(
			$match, $matchSections, $reverseParams, $constraintsLocalized
		);
	}

	/**
	 * TODO: dopsat
	 * @param string|NULL $localization 
	 * @return void
	 */
	protected function initReverse ($localization = NULL) {
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
		$this->reverseSectionsLocalized[$localization] = & $reverseSections;
		$this->reverseLocalized[$localization] = & $reverse;

		$match = NULL;
		$reverseParams = $this->initReverseParams(
			$reverse, $reverseSections, $this->GetConstraints($localization), $match
		);
		$this->reverseParamsLocalized[$localization] = & $reverseParams;
		$this->reverseParams = array_keys($reverseParams);

		$this->initFlagsByPatternOrReverse($reverse);
	}
}
