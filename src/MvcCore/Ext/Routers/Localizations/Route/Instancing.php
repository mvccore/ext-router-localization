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

trait Instancing
{
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
			$this->constructDataPatternsDefaultsConstraintsFilters($data);
			$this->constructDataCtrlActionName($data);
			$this->constructDataAdvConf($data);
			$this->config = & $patternOrConfig;
		} else {
			$this->constructVarsPatternDefaultsConstraintsFilters(
				$patternOrConfig, $defaults, $constraints, $advancedConfiguration
			);
			$this->constructVarCtrlActionNameByData($controllerAction);
			$this->constructVarAdvConf($advancedConfiguration);
			$this->config = & $advancedConfiguration;
		}
		$this->constructCtrlOrActionByName();
	}

	protected function constructDataPatternsDefaultsConstraintsFilters (& $data) {
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
		if (isset($data->defaults)) 
			$this->SetDefaults($data->defaults);
		if (isset($data->constraints)) 
			$this->SetConstraints($data->constraints);
		if (isset($data->filters) && is_array($data->filters)) 
			$this->SetFilters($data->filters);
	}

	protected function constructVarsPatternDefaultsConstraintsFilters (& $pattern, & $defaults, & $constraints, & $advCfg) {
		if ($this->is_array($pattern)) {
			$this->patternLocalized = $pattern;
		} else {
			$this->pattern = $pattern;	
		}
		if ($defaults !== NULL)
			$this->SetDefaults($defaults);
		if ($constraints !== NULL)
			$this->SetConstraints($constraints);
		$filterInParam = static::CONFIG_FILTER_IN;
		if (isset($advCfg[$filterInParam]))
			$this->SetFilter($advCfg[$filterInParam], $filterInParam);
		$filterOutParam = static::CONFIG_FILTER_OUT;
		if (isset($advCfg[$filterOutParam]))
			$this->SetFilter($advCfg[$filterOutParam], $filterOutParam);
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
}
