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

trait Matching
{
	/**
	 * Return array of matched params, with matched controller and action names,
	 * if route matches request always `\MvcCore\Request::$path` property by `preg_match_all()`.
	 *
	 * This method is usually called in core request routing process
	 * from `\MvcCore\Router::Route();` method and it's submethods.
	 *
	 * @param \MvcCore\Request $request Request object instance.
	 * @param string $localization Lowercase language code, optionally with dash and uppercase locale code, `NULL` by default, not implemented in core.
	 * @return array Matched and params array, keys are matched
	 *				 params or controller and action params.
	 */
	public function & Matches (\MvcCore\IRequest & $request, $localization = NULL) {
		$matchedParams = [];
		$pattern = & $this->matchesGetPattern($localization);
		$subject = $this->matchesGetSubject($request);
		preg_match_all($pattern, $subject, $matchedValues);
		if (isset($matchedValues[0]) && count($matchedValues[0]) > 0) {
			$matchedParams = $this->matchesParseRewriteParams($matchedValues, $this->GetDefaults($localization));
			if (isset($matchedParams[$this->lastPatternParam])) 
				$matchedParams[$this->lastPatternParam] = rtrim(
				$matchedParams[$this->lastPatternParam], '/'
			);
		}
		return $matchedParams;
	}

	protected function & matchesGetPattern ($localization = NULL) {
		if ($this->match !== NULL) {
			$match = & $this->match;
			$this->matchLocalized[$localization] = & $match;
			if (!array_key_exists($localization, $this->reverseSectionsLocalized))
				$this->initReverse($localization);
		} else {
			if (array_key_exists($localization, $this->matchLocalized)) {
				$this->initReverse($localization);
			} else {
				$this->initMatchAndReverse($localization);
			}
			$match = & $this->matchLocalized[$localization];
		}
		return $match;
	}
}
