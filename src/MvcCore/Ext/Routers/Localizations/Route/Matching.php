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

/**
 * @mixin \MvcCore\Ext\Routers\Localizations\Route
 */
trait Matching {

	/**
	 * Return array of matched params if incoming request match this route
	 * or `NULL` if doesn't. Returned array must contain all matched reverse 
	 * params with matched controller and action names by route and by matched 
	 * params. Route is matched usually if request property `path` matches by 
	 * PHP `preg_match_all()` route `match` pattern. Sometimes, matching subject 
	 * could be different if route specifies it - if route `pattern` (or `match`) 
	 * property contains domain (or base path part) - it means if it is absolute 
	 * or if `pattern` (or `match`) property contains a query string part.
	 * This method is usually called in core request routing process
	 * from `\MvcCore\Router::Route();` method and it's sub-methods.
	 * @param \MvcCore\Request $request The request object instance.
	 * @throws \LogicException Route configuration property is missing.
	 * @throws \InvalidArgumentException Wrong route pattern format.
	 * @return array Matched and params array, keys are matched
	 *				 params or controller and action params.
	 */
	public function Matches (\MvcCore\IRequest $request, $localization = NULL) {
		$matchedParams = NULL;
		$pattern = $this->matchesGetPattern($localization);
		$subject = $this->matchesGetSubject($request);
		$matchedValues = $this->match($pattern, $subject);
		if (isset($matchedValues[0]) && count($matchedValues[0]) > 0) {
			$defaultsLocalized = $this->GetDefaults($localization);
			$matchedParams = $this->matchesParseRewriteParams(
				$matchedValues, $defaultsLocalized
			);
			if (isset($matchedParams[$this->lastPatternParam])) 
				$matchedParams[$this->lastPatternParam] = rtrim(
				$matchedParams[$this->lastPatternParam], '/'
			);
		}
		return $matchedParams;
	}

	/**
	 * Return localized pattern value used for `preg_match_all()` route match 
	 * processing. Check if non-localized property `match` has any value and 
	 * if it has, use it as result match. If there is not `match` property 
	 * defined, check if there is any localized `match` defined and if it is, 
	 * use it as result match. If there is any result match in those places, 
	 * process internal route initialization only on `reverse` (or `pattern`) 
	 * property (or on their localized equivalents), because `match` regular 
	 * expression is probably prepared and initialized manually. If there is no 
	 * value for result match (`NULL`), process internal initialization on 
	 * `pattern` property (or on `reverse` if exists or on their localized 
	 * equivalents) and complete regular expression into result match and 
	 * metadata about `reverse` property (or localized `reverse` value) to 
	 * build URL address any time later on this route.
	 * @param string $localization	Lower case language code, optionally with 
	 *								dash and upper case locale code.
	 * @throws \LogicException Route configuration property is missing.
	 * @throws \InvalidArgumentException Wrong route pattern format.
	 * @return string
	 */
	protected function matchesGetPattern ($localization = NULL) {
		if ($this->match !== NULL) {
			$match = $this->match;
			$this->matchLocalized[$localization] = $match;
			if (!array_key_exists($localization, $this->reverseSectionsLocalized))
				$this->initReverse($localization);
		} else {
			if (array_key_exists($localization, $this->matchLocalized)) {
				$this->initReverse($localization);
			} else {
				$this->initMatchAndReverse($localization);
			}
			$match = $this->matchLocalized[$localization];
		}
		return $match;
	}
}
