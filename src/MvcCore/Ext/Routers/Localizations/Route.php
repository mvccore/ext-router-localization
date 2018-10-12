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
	 * @var array
	 */
	protected $patternLocalized = [];

	/**
	 * @var array
	 */
	protected $matchLocalized = [];

	/**
	 * @var array
	 */
	protected $reverseLocalized = [];

	/**
	 * @var array
	 */
	protected $defaultsLocalized = [];

	/**
	 * @var array
	 */
	protected $constraintsLocalized = [];

	/**
	 * @var array
	 */
	protected $reverseParamsLocalized = [];

	/**
	 * Array with `string` by all reverse pattern params.
	 * This array is parsed automaticly by method `\MvcCore\Route::initMatch();` 
	 * if necessary or by method `\MvcCore\Route::initReverse();` after it's 
	 * necessary, to be able to complete URL address string in method and sub
	 * methods of `\MvcCore\Route::Url();`.
	 * Example: 
	 * // For pattern `/products-list/<name>/<color>`
	 * `["name", "color"];`
	 * @var \string[]|NULL
	 */
	protected $reverseParams	= NULL;

	/**
	 * @param string $localization
	 * @return string|array|NULL
	 */
	public function GetPattern ($localization = NULL) {
		if (
			$localization !== NULL && 
			array_key_exists($localization, $this->patternLocalized)
		) {
			return $this->patternLocalized[$localization];
		} else {
			return $this->pattern;
		}
	}

	/**
	 * @param string|array $pattern
	 * @param string|NULL $localization 
	 * @return \MvcCore\Ext\Routers\Localizations\Route|\MvcCore\IRoute
	 */
	public function & SetPattern ($pattern, $localization = NULL) {
		if ($localization !== NULL) {
			$this->patternLocalized[$localization] = $pattern;
		} else if (is_array($pattern)) {
			$this->patternLocalized = $pattern;
		} else {
			$this->pattern = $pattern;
		}
		return $this;
	}

	/**
	 * @param string $localization 
	 * @return string|array|NULL
	 */
	public function GetMatch ($localization = NULL) {
		if (
			$localization !== NULL && 
			array_key_exists($localization, $this->matchLocalized)	
		) {
			return $this->matchLocalized[$localization];
		} else {
			return $this->match;
		}
	}

	/**
	 * @param string|array $match
	 * @param string|NULL $localization 
	 * @return \MvcCore\Ext\Routers\Localizations\Route|\MvcCore\IRoute
	 */
	public function & SetMatch ($match, $localization = NULL) {
		if ($localization !== NULL) {
			$this->matchLocalized[$localization] = $match;
		} else if (is_array($match)) {
			$this->matchLocalized = $match;
		} else {
			$this->match = $match;
		}
		return $this;
	}

	/**
	 * @param string $localization 
	 * @return string|array|NULL
	 */
	public function GetReverse ($localization = NULL) {
		if (
			$localization !== NULL && 
			array_key_exists($localization, $this->reverseLocalized)
		) {
			return $this->reverseLocalized[$localization];
		}
		return $this->reverse;
	}

	/**
	 * @param string|array $reverse
	 * @param string|NULL $localization 
	 * @return \MvcCore\Ext\Routers\Localizations\Route|\MvcCore\IRoute
	 */
	public function & SetReverse ($reverse, $localization = NULL) {
		if ($localization !== NULL) {
			$this->reverseLocalized[$localization] = $reverse;
			$this->initFlagsByReverse($reverse);
		} else if (is_array($reverse)) {
			$this->reverseLocalized = $reverse;
			$this->initFlagsByReverse(current($reverse));
		} else {
			$this->reverse = $reverse;
			$this->initFlagsByReverse($reverse);
		}
		return $this;
	}

	/**
	 * @param string|array $localization 
	 * @return array|\array[]
	 */
	public function & GetDefaults ($localization = NULL) {
		if (
			$localization !== NULL && 
			array_key_exists($localization, $this->defaultsLocalized) && 
			is_array($this->defaultsLocalized[$localization])
		) {
			return $this->defaultsLocalized[$localization];
		}
		return $this->defaults;
	}

	/**
	 * @param array|\array[] $defaults
	 * @param string|NULL $localization 
	 * @return \MvcCore\Ext\Routers\Localizations\Route|\MvcCore\IRoute
	 */
	public function & SetDefaults ($defaults = [], $localization = NULL) {
		if ($localization !== NULL) {
			$this->defaultsLocalized[$localization] = & $defaults;
		} else {
			if (static::recordIsLocalized($defaults)) {
				$this->defaultsLocalized = $defaults;
			} else {
				$this->defaults = $defaults;	
			}
		}
		return $this;
	}

	/**
	 * @param string|array $localization 
	 * @return array|\array[]
	 */
	public function GetConstraints ($localization = NULL) {
		if (
			$localization !== NULL && 
			array_key_exists($localization, $this->constraintsLocalized) && 
			is_array($this->constraintsLocalized[$localization])
		) {
			return $this->constraintsLocalized[$localization];
		}
		return $this->constraints;
	}

	/**
	 * @param array|\array[] $constraints
	 * @param string $localization 
	 * @return \MvcCore\Ext\Routers\Localizations\Route|\MvcCore\IRoute
	 */
	public function & SetConstraints ($constraints = [], $localization = NULL) {
		if ($localization !== NULL) {
			$this->constraintsLocalized[$localization] = & $constraints;
			if (!isset($this->defaultsLocalized[$localization]))
				$this->defaultsLocalized[$localization] = [];
			$defaults = & $this->defaultsLocalized[$localization];
			foreach ($constraints as $key => $value)
				if (!isset($defaults[$key]))
					$defaults[$key] = NULL;
		} else {
			$localizedConstraints = static::recordIsLocalized($constraints);
			if ($localization === NULL && $localizedConstraints) {
				$this->constraintsLocalized = & $constraints;
				$defaults = & $this->defaultsLocalized;
				foreach ($constraints as $localization => $constraintsLocalized) {
					if (!isset($this->defaultsLocalized[$localization]))
						$this->defaultsLocalized[$localization] = [];
					$defaults = & $this->defaultsLocalized[$localization];
					foreach ($constraintsLocalized as $key => $value)
						if (!isset($defaults[$key]))
							$defaults[$key] = NULL;
				}
			} else if ($localization === NULL && !$localizedConstraints) {
				$this->constraints = & $constraints;
				$defaults = & $this->defaults;
				foreach ($constraints as $key => $value) {
					if (!isset($defaults[$key]))
						$defaults[$key] = NULL;
				}
			}
		}
		return $this;
	}

	/**
	 * Return only reverse params names as `string`s array.
	 * Example: `["name", "color"];`
	 * @return \string[]|NULL
	 */
	public function GetReverseParams () {
		return $this->reverseParams;
	}

	/**
	 * Set up internal reverse params info.
	 * @param array $reverseParams
	 * @param string|NULL $localization
	 * @return \MvcCore\Route
	 */
	protected function setReverseParams (array & $reverseParams = [], $localization = NULL) {
		$this->reverseParamsLocalized[$localization] = & $reverseParams;
		$this->reverseParams = array_keys($reverseParams);
		return $this;
	}

	/**
	 * Create new route instance.
	 * First argument should be configuration array or
	 * route pattern value to parse into match and reverse patterns.
	 * Example:
	 * `new Route(array(
	 *		"pattern"			=> [
	 *			"en"		=> "/products-list/<name>/<color>",
	 *			"de"		=> "/produkt-liste/<name>/<color>"
	 *		],
	 *		"controllerAction"	=> "Products:List",
	 *		"defaults"			=> [
	 *			"en" => ["name" => "default-name", "color" => "red"],
	 *			"de" => ["name"	=> "standard-name","color" => "rot"],
	 *		],
	 *		"constraints"		=> [
	 *			"name" => "[^/]*",			
	 *			"color" => "[a-z]*"
	 *		]
	 * ));`
	 * or:
	 * `new Route(
	 *		"/products-list/<name>/<color>",
	 *		"Products:List",
	 *		[
	 *			"en" => ["name" => "default-name", "color" => "red"],
	 *			"de" => ["name"	=> "standard-name","color" => "rot"],
	 *		],
	 *		["name" => "[^/]*",			"color" => "[a-z]*"]
	 * );`
	 * or:
	 * `new Route([
	 *		"name"			=> "products_list",
	 *		"match"		=> [
	 *			"en"	=> "#^/products\-list/(?<name>[^/]*)/(?<color>[a-z]*)(?=/$|$)#",
	 *			"de"	=> "#^/produkt\-liste/(?<name>[^/]*)/(?<color>[a-z]*)(?=/$|$)#"
	 *		],
	 *		"reverse"		=> [
	 *			"en"	=> "/products-list/<name>/<color>",
	 *			"de"	=> "/produkt-liste/<name>/<color>"
	 *		],
	 *		"controller"	=> "Products",
	 *		"action"		=> "List",
	 *		"defaults"		=> [
	 *			"en" => ["name" => "default-name", "color" => "red"],
	 *			"de" => ["name"	=> "standard-name","color" => "rot"],
	 *		],
	 * ]);`
	 * @param string|array $patternOrConfig	Required, configuration array or route pattern value to parse into match and reverse patterns.
	 * @param string $controllerAction		Optional, controller and action name in pascale case like: `"Photogallery:List"`.
	 * @param array $defaults				Optional, default param values like: `["name" => "default-name", "page" => 1]`.
	 * @param array $constraints			Optional, params regex constraints for regular expression match fn no `"match"` record in configuration array as first argument defined.
	 * @param array	$filters				Optional, callable function(s) under keys `"in" | "out"` to filter in and out params accepting arguments: `array $params, array $defaultParams, \MvcCore\IRequest $request`.
	 * @param array $method					Optional, http method to only match requests by this method. If `NULL` (by default), request with any http method could be matched by this route. Given value is automaticly converted to upper case.
	 * @return \MvcCore\Route
	 */
	public function __construct (
		$patternOrConfig = NULL,
		$controllerAction = NULL,
		$defaults = [],
		$constraints = [],
		$filters = [],
		$method = NULL
	) {
		$args = func_get_args();
		$argsCount = count($args);
		if ($argsCount === 0) return;
		if (is_array($patternOrConfig)) {
			$data = (object) $patternOrConfig;
			if (isset($data->controllerAction)) {
				list($ctrl, $action) = explode(':', $data->controllerAction);
				if ($ctrl) $this->controller = $ctrl;
				if ($action) $this->action = $action;
				if (isset($data->name)) {
					$this->name = $data->name;
				} else {
					$this->name = $data->controllerAction;
				}
			} else {
				$this->controller = isset($data->controller) ? $data->controller : NULL;
				$this->action = isset($data->action) ? $data->action : NULL;
				if (isset($data->name)) {
					$this->name = $data->name;
				} else if ($this->controller !== NULL && $this->action !== NULL) {
					$this->name = $this->controller . ':' . $this->action;
				} else {
					$this->name = NULL;
				}
			}
			if (isset($data->pattern)) {
				if (is_array($data->pattern)) {
					$this->patternLocalized = $data->pattern;
				} else {
					$this->pattern = $data->pattern;	
				}
			}
			if (isset($data->match)) {
				if (is_array($data->match)) {
					$this->matchLocalized = $data->match;
				} else {
					$this->match = $data->match;	
				}
			}
			if (isset($data->reverse)) {
				if (is_array($data->reverse)) {
					$this->reverseLocalized = $data->reverse;
					$this->initFlagsByReverse(current($data->reverse));
				} else {
					$this->reverse = $data->reverse;
					$this->initFlagsByReverse($data->reverse);
				}
			}
			if (isset($data->defaults)) 
				$this->SetDefaults($data->defaults);
			if (isset($data->constraints)) 
				$this->SetConstraints($data->constraints);
			if (isset($data->filters) && is_array($data->filters)) 
				$this->SetFilters($data->filters);
			if (isset($data->method) && is_string($data->method)) 
				$this->method = strtoupper($data->method);
		} else {
			if (static::recordIsLocalized($patternOrConfig)) {
				$this->patternLocalized = $patternOrConfig;
			} else {
				$this->pattern = $patternOrConfig;	
			}
			if ($controllerAction !== NULL) 
				list($this->controller, $this->action) = explode(':', $controllerAction);
			if ($defaults !== NULL)
				$this->SetDefaults($defaults);
			if ($constraints !== NULL)
				$this->SetConstraints($constraints);
			if (is_array($filters)) 
				$this->SetFilters($filters);
			if (is_string($method)) 
				$this->method = strtoupper($method);
		}
		if (!$this->controller && !$this->action && strpos($this->name, ':') !== FALSE && strlen($this->name) > 1) {
			list($ctrl, $action) = explode(':', $this->name);
			if ($ctrl) $this->controller = $ctrl;
			if ($action) $this->action = $action;
		}
	}

	protected function matchesGetPattern (\MvcCore\IRequest & $request, $localization = NULL) {
		if ($this->match !== NULL) {
			$match = $this->match;
			$this->matchLocalized[$localization] = $match;
		} else {
			if (!array_key_exists($localization, $this->matchLocalized)) {
				list($match, $reverse) = $this->initMatch($localization);
				$this->matchLocalized[$localization] = $match;
				if (!array_key_exists($localization, $this->reverseLocalized))
					$this->reverseLocalized[$localization] = $reverse;
			}
			$match = $this->matchLocalized[$localization];
		}
		return $match;
	}

	protected function matchesTrimLastParamTrailingSlash ($localization = NULL) {
		if ($this->lastPatternParam === NULL && !array_key_exists($localization, $this->reverseLocalized)) 
			$this->reverseLocalized[$localization] = $this->initReverse($localization);
		if (isset($this->matchedParams[$this->lastPatternParam])) {
			$this->matchedParams[$this->lastPatternParam] = rtrim($this->matchedParams[$this->lastPatternParam], '/');
		}
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
	 * @param \MvcCore\Request $request Currently requested request object.
	 * @param array $params URL params from application point completed by developer.
	 * @param array $requestedUrlParams Requested url route prams nad query string params without escaped HTML special chars: `< > & " ' &`.
	 * @param string $queryStringParamsSepatator Query params separator, `&` by default. Always automaticly completed by router instance.
	 * @return \string[] Result URL addres in two parts - domain part with base path and path part with query string.
	 */
	public function Url (\MvcCore\IRequest & $request, array & $params = [], array & $requestedUrlParams = [], $queryStringParamsSepatator = '&') {
		$absolute = $this->urlGetAbsoluteParam($params);
		static $router = NULL;
		if ($router === NULL) $router = & \MvcCore\Application::GetInstance()->GetRouter();
		$localizationParamName = $router::URL_PARAM_LOCALIZATION;
		if (isset($params[$localizationParamName])) {
			$localizationStr = $params[$localizationParamName];
			unset($params[$localizationParamName]);
		} else if (isset($requestedUrlParams[$localizationParamName])) {
			$localizationStr = $requestedUrlParams[$localizationParamName];
			unset($requestedUrlParams[$localizationParamName]);
		} else {
			$localizationStr = implode($router::LANG_AND_LOCALE_SEPARATOR, $router->GetLocalization());
		}
		$localization = explode($router::LANG_AND_LOCALE_SEPARATOR, $localizationStr);
		$routesLocalization = $router->GetRouteRecordsByLanguageAndLocale()
			? $localizationStr
			: $localization[0];
		if (!array_key_exists($routesLocalization, $this->reverseParamsLocalized) || !array_key_exists($routesLocalization, $this->reverseLocalized)) 
			$this->reverseLocalized[$routesLocalization] = $this->initReverse($routesLocalization);
		
		$routeDefaults = $this->GetDefaults($routesLocalization);
		// complete params for necessary values to build reverse pattern
		$reverseParams = $this->reverseParamsLocalized[$routesLocalization];
		$reverseParamsKeys = [];
		$reverseParamsCount = count($reverseParams);
		$noReverseParamsCount = $reverseParamsCount === 0;
		if ($noReverseParamsCount) {
			$allParamsClone = array_merge([], $params);
		} else {
			$reverseParamsKeys = array_keys($reverseParams);
			$emptyReverseParams = array_fill_keys($reverseParamsKeys, '');
			$allMergedParams = array_merge($routeDefaults, $requestedUrlParams, $params);
			$allParamsClone = array_merge($emptyReverseParams, array_intersect_key($allMergedParams, $emptyReverseParams), $params);
		}
		// filter params out
		$localizationContained = array_key_exists($localizationParamName, $allParamsClone);
		$allParamsClone[$localizationParamName] = $localizationStr;
		list(,$filteredParams) = $this->Filter($allParamsClone, $requestedUrlParams, \MvcCore\IRoute::FILTER_OUT);
		if (!$localizationContained) unset($filteredParams[$localizationParamName]);
		// build reverse pattern
		$resultPattern = $this->reverseLocalized[$routesLocalization];
		if ($noReverseParamsCount) {
			$result = $resultPattern;
		} else {
			$result = mb_substr($resultPattern, 0, $reverseParams[$reverseParamsKeys[0]][0]);
			$current = 0;
			while (TRUE) {
				$paramName = $reverseParamsKeys[$current];
				$currentEnd = $reverseParams[$paramName][1];
				// convert possible XSS chars to entities (`< > & " ' &`):
				$result .= htmlspecialchars($filteredParams[$paramName], ENT_QUOTES);
				unset($filteredParams[$paramName]);
				// try to get next record and shift
				$next = $current + 1;
				if ($next < $reverseParamsCount) {
					$nextParamName = $reverseParamsKeys[$next];
					$nextStart = $reverseParams[$nextParamName][0];
					$result .= mb_substr($resultPattern, $currentEnd, $nextStart - $currentEnd);
				} else {
					$result .= mb_substr($resultPattern, $currentEnd);
					break;
				}
			}
		}
		$result = & $this->correctTrailingSlashBehaviour($result);
		if ($filteredParams) {
			// `http_build_query()` automaticly converts all XSS chars to entities (`< > & " ' &`):
			$result .= (mb_strpos($result, '?') !== FALSE ? $queryStringParamsSepatator : '?')
				. str_replace('%2F', '/', http_build_query($filteredParams, '', $queryStringParamsSepatator));
		}
		return $this->urlSplitResultToBaseAndPathWithQuery($request, $result, $absolute);
	}

	/**
	 * Initialize all possible protected values (`match`, `reverse` etc...)
	 * This method is not recomanded to use in production mode, it's
	 * designed mostly for development purposes, to see what could be inside route.
	 * @return \MvcCore\Route|\MvcCore\IRoute
	 */
	public function & InitAll () {
		$router = & \MvcCore\Application::GetInstance()->GetRouter();
		$localization = $router->GetLocalization();
		$localizationStr = implode($router::LANG_AND_LOCALE_SEPARATOR, $router->GetLocalization());
		$routesLocalization = $router->GetRouteRecordsByLanguageAndLocale()
			? $localizationStr
			: $localization[0];
		if ($this->match === NULL && !array_key_exists($routesLocalization, $this->matchLocalized)) {
			list($match, $reverse) = $this->initMatch($routesLocalization);
			$this->matchLocalized[$routesLocalization] = $match;
			if (!array_key_exists($routesLocalization, $this->reverseLocalized))
				$this->reverseLocalized[$routesLocalization] = $reverse;
		}
		if (($this->lastPatternParam === NULL || $this->reverseParams === NULL) && !array_key_exists($routesLocalization, $this->reverseParamsLocalized)) 
			$this->reverseLocalized[$routesLocalization] = $this->initReverse($routesLocalization);
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
	 * @return \string[]
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
		// parse all presented `<param>` occurances in `$pattern` argument:
		list($matchPattern, $patternParams) = $this->parsePatternParams($pattern);
		// compile match regular expression from parsed params and custom constraints:
		$reverseVal = $this->GetReverse($localization);
		if ($reverseVal === NULL) {
			list($match, $reverse) = $this->initMatchAndReverse(
				[$matchPattern, $pattern], $patternParams, TRUE, $localization
			);
		} else {
			list($match, $reverse) = $this->initMatchAndReverse(
				[$matchPattern, $pattern], $patternParams, FALSE, $localization
			);
		}
		return [$match, $reverse];
	}

	/**
	 * Get `TRUE` if given route record contains only allowed localization keys.
	 * @param mixed $record 
	 * @return bool
	 */
	protected static function recordIsLocalized ($record) {
		static $allowedLocalizationKeys = [];
		if (count($allowedLocalizationKeys) === 0) {
			$router = & \MvcCore\Router::GetInstance();
			$allowedLocalizations = $router->GetAllowedLocalizations();
			if (!$router->GetRouteRecordsByLanguageAndLocale()) {
				foreach ($allowedLocalizations as $allowedLocalization)	{
					$dashPos = strpos($allowedLocalization, $router::LANG_AND_LOCALE_SEPARATOR);
					if ($dashPos === FALSE) continue;
					$allowedLocalization = substr($allowedLocalization, 0, $dashPos);
					$allowedLocalizationKeys[$allowedLocalization] = $allowedLocalization;
				}
			}
		}
		$localizationKeys = TRUE;
		$recordKeys = array_keys($record);
		foreach ($recordKeys as $recordKey) {
			if (!isset($allowedLocalizationKeys[$recordKey])) {
				$localizationKeys = FALSE;
				break;
			}
		}
		return $localizationKeys;
	}
}
