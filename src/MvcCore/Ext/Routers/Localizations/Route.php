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

	protected $reverseSectionsLocalized = [];

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
		} else if (is_array($reverse)) {
			$this->reverseLocalized = $reverse;
		} else {
			$this->reverse = $reverse;
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
			if ($this->recordIsLocalized($defaults)) {
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
	public function & GetConstraints ($localization = NULL) {
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
			$localizedConstraints = $this->recordIsLocalized($constraints);
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
	 * TODO: neaktuální
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
		$advancedConfiguration = []
	) {
		if (count(func_get_args()) === 0) return;
		if (is_array($patternOrConfig)) {
			$data = (object) $patternOrConfig;
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
				} else {
					$this->reverse = $data->reverse;
				}
			}
			$this->constructCtrlActionNameDefConstrAndAdvCfg($data);
		} else {
			if ($this->recordIsLocalized($patternOrConfig)) {
				$this->patternLocalized = $patternOrConfig;
			} else {
				$this->pattern = $patternOrConfig;	
			}
			$this->constructCtrlActionDefConstrAndAdvCfg(
				$controllerAction, $defaults, $constraints, $advancedConfiguration
			);
		}
		$this->constructCtrlOrActionByName();
	}

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
		// initialize localization param and route localization key
		$router = & $this->router;
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

		// check reverse initialization
		if (!array_key_exists($routesLocalization, $this->reverseParamsLocalized) || !array_key_exists($routesLocalization, $this->reverseLocalized)) 
			$this->initReverse($routesLocalization);
		
		// complete and filter all params to build reverse pattern
		$reverseParams = $this->reverseParamsLocalized[$routesLocalization];
		if (count($reverseParams) === 0) {
			$allParamsClone = array_merge([], $params);
		} else {
			$emptyReverseParams = array_fill_keys(array_keys($reverseParams), '');
			$allMergedParams = array_merge($this->GetDefaults($routesLocalization), $requestedUrlParams, $params);
			$allParamsClone = array_merge($emptyReverseParams, array_intersect_key($allMergedParams, $emptyReverseParams), $params);
		}

		// filter params
		$localizationContained = array_key_exists($localizationParamName, $allParamsClone);
		$allParamsClone[$localizationParamName] = $localizationStr;
		list(,$filteredParams) = $this->Filter($allParamsClone, $requestedUrlParams, \MvcCore\IRoute::CONFIG_FILTER_OUT);
		if (!$localizationContained) unset($filteredParams[$localizationParamName]);
		
		// split params into domain params array and into path and query params array
		$domainParams = $this->urlGetAndRemoveDomainParams($filteredParams);

		// build reverse pattern
		$result = $this->urlComposeByReverseSectionsAndParams(
			$this->reverseLocalized[$routesLocalization], 
			$this->reverseSectionsLocalized[$routesLocalization], 
			$this->reverseParamsLocalized[$routesLocalization], 
			$filteredParams, 
			$this->GetDefaults($routesLocalization)
		);

		// add all remaining params to query string
		if ($filteredParams) {
			// `http_build_query()` automaticly converts all XSS chars to entities (`< > & " ' &`):
			$result .= (mb_strpos($result, '?') !== FALSE ? $queryStringParamsSepatator : '?')
				. str_replace('%2F', '/', http_build_query($filteredParams, '', $queryStringParamsSepatator, PHP_QUERY_RFC3986));
		}

		return $this->urlSplitResultToBaseAndPathWithQuery($request, $result, $domainParams);
	}

	/**
	 * Initialize all possible protected values (`match`, `reverse` etc...)
	 * This method is not recomanded to use in production mode, it's
	 * designed mostly for development purposes, to see what could be inside route.
	 * @return \MvcCore\Route|\MvcCore\IRoute
	 */
	public function & InitAll () {
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
			$this->throwExceptionIfNoPattern();
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
			$this->throwExceptionIfNoPattern();
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

	/**
	 * Get `TRUE` if given route record contains only allowed localization keys.
	 * @param mixed $record 
	 * @return bool
	 */
	protected function recordIsLocalized ($record) {
		static $allowedLocalizationKeys = [];
		if (count($allowedLocalizationKeys) === 0) {
			$router = & $this->router;
			if ($router === NULL) {
				static $routerStat = NULL;
				if ($routerStat === NULL)
					$routerStat = & \MvcCore\Application::GetInstance()->GetRouter();
				$router = $routerStat;
			}
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
	protected function _old_initMatch ($localization = NULL) {
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
}
