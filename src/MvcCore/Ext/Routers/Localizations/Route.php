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

namespace MvcCore\Ext\Routers\Localizations;

class Route extends \MvcCore\Route
{
	/**
	 * @param string $localization
	 * @return string|\string[]|NULL
	 */
	public function GetPattern ($localization = NULL) {
		return (
			$localization !== NULL && 
			is_array($this->pattern)
		)
			? (array_key_exists($localization, $this->pattern) ? $this->pattern[$localization] : NULL)
			: $this->pattern;
	}

	/**
	 * @param string|\string[] $pattern
	 * @param string $localization 
	 * @return \MvcCore\Ext\Routers\Localizations\Route|\MvcCore\Interfaces\IRoute
	 */
	public function & SetPattern ($pattern, $localization = NULL) {
		if ($localization !== NULL) {
			if (!is_array($this->pattern)) $this->pattern = [];
			$this->pattern[$localization] = $pattern;
		} else {
			$this->pattern = $pattern;
		}
		return $this;
	}

	/**
	 * @param string $localization 
	 * @return string|\string[]|NULL
	 */
	public function GetMatch ($localization = NULL) {
		return (
			$localization !== NULL && 
			is_array($this->match)
		)
			? (array_key_exists($localization, $this->match) ? $this->match[$localization] : NULL)
			: $this->match;
	}

	/**
	 * @param string|\string[] $match
	 * @param string $localization 
	 * @return \MvcCore\Ext\Routers\Localizations\Route|\MvcCore\Interfaces\IRoute
	 */
	public function & SetMatch ($match, $localization = NULL) {
		if ($localization !== NULL) {
			if (!is_array($this->match)) $this->match = [];
			$this->match[$localization] = $match;
		} else {
			$this->match = $match;
		}
		return $this;
	}

	/**
	 * @param string $localization 
	 * @return string|\string[]|NULL
	 */
	public function GetReverse ($localization = NULL) {
		return (
			$localization !== NULL && 
			is_array($this->reverse)
		)
			? (array_key_exists($localization, $this->reverse) ? $this->reverse[$localization] : NULL)
			: $this->reverse;
	}

	/**
	 * @param string|\string[] $reverse
	 * @param string $localization 
	 * @return \MvcCore\Ext\Routers\Localizations\Route|\MvcCore\Interfaces\IRoute
	 */
	public function & SetReverse ($reverse, $localization = NULL) {
		if ($localization !== NULL) {
			if (!is_array($this->reverse)) $this->reverse = [];
			$this->reverse[$localization] = $reverse;
		} else {
			$this->reverse = $reverse;
		}
		return $this;
	}

	/**
	 * @param string $localization 
	 * @return array|\array[]
	 */
	public function GetDefaults ($localization = NULL) {
		return (
			$localization !== NULL && 
			is_array($this->defaults)
		)
			? (array_key_exists($localization, $this->defaults) ? $this->defaults[$localization] : NULL)
			: $this->defaults;
	}

	/**
	 * @param array|\array[] $defaults
	 * @param string $localization 
	 * @return \MvcCore\Ext\Routers\Localizations\Route|\MvcCore\Interfaces\IRoute
	 */
	public function & SetDefaults ($defaults = [], $localization = NULL) {
		if ($localization !== NULL) {
			if (!is_array($this->defaults)) $this->defaults = [];
			$this->defaults[$localization] = & $defaults;
		} else {
			$this->defaults = & $defaults;
		}
		return $this;
	}

	/**
	 * @param string $localization 
	 * @return array|\array[]
	 */
	public function GetConstraints ($localization = NULL) {
		return (
			$localization !== NULL && 
			is_array($this->constraints)
		)
			? (array_key_exists($localization, $this->constraints) ? $this->constraints[$localization] : NULL)
			: $this->constraints;
	}

	/**
	 * @param array|\array[] $constraints
	 * @param string $localization 
	 * @return \MvcCore\Ext\Routers\Localizations\Route|\MvcCore\Interfaces\IRoute
	 */
	public function & SetConstraints ($constraints = [], $localization = NULL) {
		if ($localization !== NULL) {
			if (!is_array($this->constraints)) $this->constraints = [];
			$this->constraints[$localization] = & $constraints;
			if (!isset($this->defaults[$localization]))
				$this->defaults[$localization] = [];
			$defaults = & $this->defaults[$localization];
			foreach ($constraints as $key => $value)
				if (!isset($defaults[$key]))
					$defaults[$key] = NULL;
		} else {
			$this->constraints = & $constraints;
			foreach ($constraints as $localization => $constraintItem) {
				if (!isset($this->defaults[$localization]))
					$this->defaults[$localization] = [];
				$defaults = & $this->defaults[$localization];
				foreach ($constraintItem as $key => $value)
					if (!isset($defaults[$key]))
						$defaults[$key] = NULL;
			}
		}
		return $this;
	}

	/**
	 * Return array of matched params, with matched controller and action names,
	 * if route matches request `\MvcCore\Request::$Path` property by `preg_match_all()`.
	 *
	 * This method is usually called in core request routing process
	 * from `\MvcCore\Router::Route();` method and it's submethods.
	 *
	 * @param string $requestPath Requested application path, never with any query string.
	 * @param string $requestMethod Uppercase request http method.
	 * @param string $localization Lowercase language code, optionally with dash and uppercase locale code, `NULL` by default, not implemented in core.
	 * @return array Matched and params array, keys are matched params or controller and action params.
	 */
	public function Matches ($requestPath, $requestMethod, $localization = NULL) {
		$matchedParams = [];
		if ($this->match === NULL || (is_array($this->match) && !array_key_exists($localization, $this->match))) {
			list($match, $reverse) = $this->initMatch($localization);
			$this->match = array_merge(is_array($this->match) ? $this->match : [], $match);
			if ($this->reverse === NULL || (is_array($this->reverse) && !array_key_exists($localization, $this->reverse))) 
				$this->reverse = array_merge(is_array($this->reverse) ? $this->reverse : [], $reverse);
		}
		if ($this->method !== NULL && $this->method !== $requestMethod) 
			return $matchedParams;
		$regExpMatch = is_array($this->match) ? $this->match[$localization] : $this->match;
		preg_match_all($regExpMatch, $requestPath, $matchedValues, PREG_OFFSET_CAPTURE);
		if (isset($matchedValues[0]) && count($matchedValues[0])) {
			$controllerName = $this->controller ?: '';
			$toolClass = \MvcCore\Application::GetInstance()->GetToolClass();
			$matchedParams = [
				'controller'	=>	$toolClass::GetDashedFromPascalCase(str_replace(['_', '\\'], '/', $controllerName)),
				'action'		=>	$toolClass::GetDashedFromPascalCase($this->action ?: ''),
			];
			array_shift($matchedValues); // first item is always matched whole `$request->GetPath()` string.
			$index = 0;
			$matchedKeys = array_keys($matchedValues);
			$matchedKeysCount = count($matchedKeys) - 1;
			$defaults = $this->GetDefaults($localization);
			while ($index < $matchedKeysCount) {
				$matchedKey = $matchedKeys[$index];
				$matchedValue = $matchedValues[$matchedKey];
				// if captured offset value is the same like in next matched record - skip next matched record:
				if (isset($matchedKeys[$index + 1])) {
					$nextKey = $matchedKeys[$index + 1];
					$nextValue = $matchedValues[$nextKey];
					if ($matchedValue[0][1] === $nextValue[0][1]) $index += 1;
				}
				// 1 line bellow is only for route debug panel, only for cases when you
				// forget to define current rewrite param, this defines null value by default
				if (!isset($defaults[$matchedKey])) $defaults[$matchedKey] = NULL;
				$matchedParams[$matchedKey] = $matchedValue[0][0];
				$index += 1;
			}
			if ($this->lastPatternParam === NULL) {
				$reverse = $this->initReverse($localization);
			$this->reverse = array_merge(is_array($this->reverse) ? $this->reverse : [], $reverse);
			}
			if (isset($matchedParams[$this->lastPatternParam])) {
				$matchedParams[$this->lastPatternParam] = rtrim($matchedParams[$this->lastPatternParam], '/');
			}
		}
		$this->matchedParams = $matchedParams;
		return $matchedParams;
	}

	/**
	 * Complete route url by given params array and route
	 * internal reverse replacements pattern string.
	 * If there are more given params in first argument
	 * than count of replacement places in reverse pattern,
	 * then create url with query string params after reverse
	 * pattern, containing that extra record(s) value(s).
	 *
	 * Example:
	 *	Input (`$params`):
	 *		`array(
	 *			"name"		=> "cool-product-name",
	 *			"color"		=> "blue",
	 *			"variants"	=> array("L", "XL"),
	 *		);`
	 *	Input (`\MvcCore\Route::$reverse`):
	 *		`"/products-list/<name>/<color*>"`
	 *	Output:
	 *		`"/products-list/cool-product-name/blue?variant[]=L&amp;variant[]=XL"`
	 * @param array $params
	 * @param array $requestedUrlParams Requested url route prams nad query string params without escaped HTML special chars: `< > & " ' &`.
	 * @param string $queryStringParamsSepatator Query params separator, `&` by default. Always automaticly completed by router instance.
	 * @return string
	 */
	public function Url (& $params = [], & $requestedUrlParams = [], $queryStringParamsSepatator = '&') {
		$router = & \MvcCore\Application::GetInstance()->GetRouter();
		$localizationParamName = $router::LOCALIZATION_URL_PARAM;
		$localizationStr = (
			isset($params[$localizationParamName])
				? $params[$localizationParamName]
				: (isset($requestedUrlParams[$localizationParamName])
					? $requestedUrlParams[$localizationParamName]
					: implode($router::LANG_AND_LOCALE_SEPARATOR, $router->GetDefaultLocalization()))
		);
		$localization = explode($router::LANG_AND_LOCALE_SEPARATOR, $localizationStr);
		$routesLocalization = $router->GetRouteRecordsByLanguageAndLocale()
			? $localizationStr
			: $localization[0];
		if (
			$this->reverseParams === NULL || (
			is_array($this->reverse) && !array_key_exists($routesLocalization, $this->reverse)
		)) {
			$reverse = $this->initReverse($routesLocalization);
			$this->reverse = array_merge(is_array($this->reverse) ? $this->reverse : [], $reverse);
		}
		$result = $this->reverse[$routesLocalization];
		$routeDefaults = $this->GetDefaults($routesLocalization);
		$givenParamsKeys = array_merge([], $params);
		foreach ($this->reverseParams as $paramName) {
			$paramKeyReplacement = '<'.$paramName.'>';
			$paramValue = (
				isset($params[$paramName])
					? $params[$paramName]
					: (isset($requestedUrlParams[$paramName])
						? $requestedUrlParams[$paramName]
						: (isset($routeDefaults[$paramName])
							? $routeDefaults[$paramName]
							: ''))
			);
			// convert possible XSS chars to entities (`< > & " ' &`):
			$paramValue = htmlspecialchars($paramValue, ENT_QUOTES);
			$result = str_replace($paramKeyReplacement, $paramValue, $result);
			unset($givenParamsKeys[$paramName]);
		}
		if ($givenParamsKeys) {
			// `http_build_query()` automaticly converts all XSS chars to entities (`< > & " ' &`):
			$result .= (mb_strpos($result, '?') !== FALSE ? $queryStringParamsSepatator : '?')
				. str_replace('%2F', '/', http_build_query($givenParamsKeys, '', $queryStringParamsSepatator));
		}
		return $result;
	}

	/**
	 * Initialize all possible protected values (`match`, `reverse` etc...)
	 * This method is not recomanded to use in production mode, it's
	 * designed mostly for development purposes, to see what could be inside route.
	 * @return \MvcCore\Route|\MvcCore\Interfaces\IRoute
	 */
	public function & InitAll () {
		$router = & \MvcCore\Application::GetInstance()->GetRouter();
		$localization = implode($router::LANG_AND_LOCALE_SEPARATOR, $router->GetLocalization());
		if ($this->match === NULL || (is_array($this->match) && !array_key_exists($localization, $this->match))) {
			list($match, $reverse) = $this->initMatch($localization);
			$this->match = array_merge(is_array($this->match) ? $this->match : [], $match);
			if ($this->reverse === NULL || (is_array($this->reverse) && !array_key_exists($localization, $this->reverse))) 
				$this->reverse = array_merge(is_array($this->reverse) ? $this->reverse : [], $reverse);
		}

		if ($this->lastPatternParam === NULL || $this->reverseParams === NULL) {
			$reverse = $this->initReverse($localization);
			$this->reverse = array_merge(is_array($this->reverse) ? $this->reverse : [], $reverse);
		}
		return $this;
	}

	/**
	 * Initialize `\MvcCore\Router::$Match` property (and `\MvcCore\Router::$lastPatternParam`
	 * property) from `\MvcCore\Router::$Pattern`, optionaly initialize
	 * `\MvcCore\Router::$Reverse` property if there is nothing inside.
	 * - Add backslashes for all special regex chars excluding `<` and `>` chars.
	 * - Parse all `<param>` occurrances in pattern into statistics array `$matchPatternParams`.
	 * - Complete from the statistic array the match property and if there no reverse property,
	 *   complete also reverse property.
	 * This method is usually called in core request routing process from
	 * `\MvcCore\Router::Matches();` method.
	 * @param string $localization Lowercase language code, optionally with dash and uppercase locale code, `NULL` by default, not implemented in core.
	 * @return \string[]|array
	 */
	protected function initMatch ($localization = NULL) {
		$match = NULL;
		$reverse = NULL;
		// if there is no match regular expression - parse `\MvcCore\Route::\$Pattern`
		// and compile `\MvcCore\Route::\$Match` regular expression property.
		$pattern = $this->GetPattern($localization);
		if ($pattern === NULL) throw new \LogicException(
			"[".__CLASS__."] Route configuration property `\MvcCore\Route::\$pattern` is missing "
			."to parse it and complete property(ies) `\MvcCore\Route::\$match` "
			."(and `\MvcCore\Route::\$reverse`) correctly ($this)."
		);
		// escape all regular expression special characters before parsing except `<` and `>`:
		$matchPattern = addcslashes($pattern, "#[](){}-?!=^$.+|:\\");
		// parse all presented `<param>` occurances in `$pattern` argument:
		$matchPatternParams = $this->parsePatternParams($matchPattern);
		// compile match regular expression from parsed params and custom constraints:
		$reverseVal = $this->GetReverse($localization);
		if ($reverseVal === NULL) {
			list($match, $reverse) = $this->compileMatchAndReversePattern(
				$matchPattern, $matchPatternParams, TRUE, $localization
			);
		} else {
			list($match, $reverse) = $this->compileMatchAndReversePattern(
				$matchPattern, $matchPatternParams, FALSE, $localization
			);
		}
		return [$match, $reverse];
	}

	/**
	 * Internal method for `\MvcCore\Route::initMatch();` processing,
	 * always called from `\MvcCore\Router::Matches();` request routing.
	 *
	 * Compile and return value for `\MvcCore\Route::$match` pattern,
	 * (optionaly by `$compileReverse` also for `\MvcCore\Route::$reverse`)
	 * from escaped `\MvcCore\Route::$pattern` and given params statistics
	 * and from configured route constraints for regular expression:
	 * - If pattern starts with slash `/`, set automaticly into
	 *   result regular expression start rule (`#^/...`).
	 * - If there is detected trailing slash in match pattern,
	 *   set automaticly into result regular expression end rule
	 *   for trailing slash `...(?=/$|$)#` or just only end rule `...$#`.
	 * - If there is detected any last param with possible trailing slash
	 *   after, complete `\MvcCore\Route::$lastPatternParam` property
	 *   by this detected param name.
	 *
	 * Example:
	 *	Input (`$matchPattern`):
	 *		`"/products-list/<name>/<color*>"`
	 *	Input (`$matchPatternParams`):
	 *		`array(
	 *			array(
	 *				"name",		// param name
	 *				"<name>",	// param name for regex match pattern
	 *				15,			// `"<name>"` occurance position
	 *				6,			// `"<name>"` string length
	 *				FALSE		// greedy param star flag
	 *			),
	 *			array(
	 *				"color",	// param name
	 *				"<color>",	// param name for regex match pattern
	 *				22,			// `"<color*>"` occurance position
	 *				8,			// `"<color*>"` string length
	 *				TRUE		// greedy param star flag
	 *			)
	 *		);`
	 *	Input (`$compileReverse`):
	 *		`TRUE`
	 *	Input (`$this->constraints`):
	 *		`array(
	 *			"name"	=> "[^/]*",
	 *			"color"	=> "[a-z]*",
	 *		);`
	 *	Output:
	 *		`array(
	 *			"#^/products\-list/(?<name>[^/]*)/(?<color>[a-z]*)(?=/$|$)#",
	 *			"/products-list/<name>/<color>"
	 *		)`
	 * @param string $matchPattern
	 * @param \array[] $matchPatternParams
	 * @param string $localization Lowercase language code, optionally with dash and uppercase locale code, `NULL` by default, not implemented in core.
	 * @return \string[]
	 */
	protected function compileMatchAndReversePattern (& $matchPattern, & $matchPatternParams, $compileReverse, $localization = NULL) {
		list($matchLocalized, $reverseLocalized) = parent::compileMatchAndReversePattern(
			$matchPattern, $matchPatternParams, $compileReverse, $localization
		);
		if ($localization === NULL) 
			return [$matchLocalized, $reverseLocalized];
		$match = [];
		$reverse = [];
		$match[$localization] = $matchLocalized;
		$reverse[$localization] = $reverseLocalized;
		return [$match, $reverse];
	}

	/**
	 * Internal method, always called from `\MvcCore\Router::Matches();` request routing,
	 * when route has been matched and when there is still no `\MvcCore\Route::$reverseParams`
	 * defined (`NULL`). It means that matched route has been defined by match and reverse
	 * patterns, because there was no pattern property parsing to prepare values bellow before.
	 * @param string $localization Lowercase language code, optionally with dash and uppercase locale code, `NULL` by default, not implemented in core.
	 * @return string|array
	 */
	protected function initReverse ($localization = NULL) {
		$reverseLocalized = parent::initReverse($localization);
		if ($localization === NULL) return $reverseLocalized;
		$reverse = [];
		$reverse[$localization] = is_array($reverseLocalized)
			? $reverseLocalized[$localization]
			: $reverseLocalized;
		return $reverse;
	}
}
