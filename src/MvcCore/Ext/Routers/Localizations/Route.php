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
	public function GetDefaults ($localization = NULL) {
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
		} else if (is_array($defaults) && count($defaults) > 0 && is_array(current($defaults))) {
			$this->defaultsLocalized = & $defaults;
		} else {
			$this->defaults = & $defaults;
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
	 * Return parsed reverse params as array with param names from reverse pattern string.
	 * Example: `array("name", "color");`
	 * @return \string[]|NULL
	 */
	public function & GetReverseParams () {
		if ($this->reverseParams === NULL) 
			$this->initReverse();
		return $this->reverseParams;
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
		} else if (is_array($constraints) && count($constraints) > 0 && is_array(current($constraints))) {
			$this->defaultsLocalized = & $constraints;
			foreach ($constraints as $localization => $constraintsLocalized) {
				if (!isset($this->defaultsLocalized[$localization]))
					$this->defaultsLocalized[$localization] = [];
				$defaults = & $this->defaultsLocalized[$localization];
				foreach ($constraintsLocalized as $key => $value)
					if (!isset($defaults[$key]))
						$defaults[$key] = NULL;
			}
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
	 * @param array $method					Optional, http method to only match requests by this method. If `NULL` (by default), request with any http method could be matched by this route. Given value is automaticly converted to upper case.
	 * @return \MvcCore\Route
	 */
	public function __construct (
		$patternOrConfig = NULL,
		$controllerAction = NULL,
		$defaults = [],
		$constraints = [],
		$method = NULL
	) {
		$args = func_get_args();
		$argsCount = count($args);
		if ($argsCount === 0) return;
		if (is_array($patternOrConfig)) {
			$data = (object) $patternOrConfig;
			if (isset($data->controllerAction)) {
				list($this->controller, $this->action) = explode(':', $data->controllerAction);
				if (isset($data->name)) {
					$this->name = $data->name;
				} else {
					$this->name = $data->controllerAction;
				}
			} else {
				$this->controller = isset($data->controller) ? $data->controller : '';
				$this->action = isset($data->action) ? $data->action : '';
				if (isset($data->name)) {
					$this->name = $data->name;
				} else if ($this->controller !== '' && $this->action !== '') {
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
				} else {
					$this->reverse = $data->reverse;	
				}
			}
			if (isset($data->defaults)) {
				if (is_array($data->defaults)) {
					$this->defaultsLocalized = $data->defaults;
				} else {
					$this->defaults = $data->defaults;	
				}
			}
			if (isset($data->constraints)) 
				$this->SetConstraints($data->constraints);
			if (isset($data->method)) 
				$data->method = strtoupper($data->method);
		} else {
			if (is_array($patternOrConfig)) {
				$this->patternLocalized = $patternOrConfig;
			} else {
				$this->pattern = $patternOrConfig;	
			}
			list($this->controller, $this->action) = explode(':', $controllerAction);
			$this->name = '';
			if (is_array($defaults)) {
				$this->defaultsLocalized = $defaults;
			} else {
				$this->defaults = $defaults;
			}
			$this->SetConstraints($constraints);
			if ($method !== NULL) $this->method = strtoupper($method);
		}
		if (!$this->controller && !$this->action && strpos($this->name, ':') !== FALSE && strlen($this->name) > 1) {
			list($this->controller, $this->action) = explode(':', $this->name);
		}
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
	public function & Matches ($requestPath, $requestMethod, $localization = NULL) {
		$matchedParams = [];
		if ($this->method !== NULL && $this->method !== $requestMethod) 
			return $matchedParams;
		if ($this->match !== NULL) {
			$match = $this->match;
		} else {
			if (!array_key_exists($localization, $this->matchLocalized)) {
				list($match, $reverse) = $this->initMatch($localization);
				$this->matchLocalized[$localization] = $match;
				if (!array_key_exists($localization, $this->reverseLocalized))
					$this->reverseLocalized[$localization] = $reverse;
			}
			$match = $this->matchLocalized[$localization];
		}
		preg_match_all($match, $requestPath, $matchedValues, PREG_OFFSET_CAPTURE);
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
			if ($this->lastPatternParam === NULL) 
				$this->reverseLocalized[$localization] = $this->initReverse($localization);
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
		if ($this->reverseParams === NULL || !array_key_exists($routesLocalization, $this->reverseLocalized)) 
			$this->reverseLocalized[$routesLocalization] = $this->initReverse($routesLocalization);
		$result = $this->reverseLocalized[$routesLocalization];
		$routeDefaults = $this->GetDefaults($routesLocalization);
		$givenParamsKeys = array_merge([], $params);
		foreach ($this->reverseParams as $paramName) {
			$paramKeyReplacement = '<'.$paramName.'>';
			if (isset($params[$paramName])) {
				$paramValue = $params[$paramName];
			} else if (isset($requestedUrlParams[$paramName])) {
				$paramValue = $requestedUrlParams[$paramName];
			} else if (isset($routeDefaults[$paramName])) {
				$paramValue = $routeDefaults[$paramName];
			} else {
				$paramValue = '';
			}
			// convert possible XSS chars to entities (`< > & " ' &`):
			$paramValue = htmlspecialchars($paramValue, ENT_QUOTES);
			$result = str_replace($paramKeyReplacement, $paramValue, $result);
			unset($givenParamsKeys[$paramName]);
		}
		$result = & $this->correctTrailingSlashBehaviour($result);
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
		if ($this->lastPatternParam === NULL || $this->reverseParams === NULL) 
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
}
