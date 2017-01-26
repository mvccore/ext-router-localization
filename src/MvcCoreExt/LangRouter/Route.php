<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flídr (https://github.com/mvccore/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/3.0.0/LICENCE.md
 */

class MvcCoreExt_LangRouter_Route extends MvcCore_Route
{
	/**
	 * Create new route
	 * @param $nameOrConfig		string|array	required
	 * @param $controller		string			required|optional
	 * @param $action			string			required|optional
	 * @param $pattern			string|array	required|optional
	 * @param $reverse			string|array	required|optional
	 * @param $params			array			required|optional
	 */
	public function __construct ($nameOrConfig = NULL, $controller = NULL, $action = NULL, $pattern = NULL, $reverse = NULL, $params = array()) {
		$args = func_get_args();
		if (count($args) == 1 && gettype($args[0]) == 'array') {
			$data = (object) $args[0];
			$name = isset($data->name) ? $data->name : '';
			$controller = isset($data->controller) ? $data->controller : '';
			$action = isset($data->action) ? $data->action : '';
			$pattern = isset($data->pattern) ? $data->pattern : '';
			$reverse = isset($data->reverse) ? $data->reverse : '';
			$params = isset($data->params) ? $data->params : array();
		} else {
			list($name, $controller, $action, $pattern, $reverse, $params) = $args;
		}
		if (!$controller && !$action && strpos($name, ':') !== FALSE) {
			list($controller, $action) = explode(':', $name);
		}
		$this->Name = $name;
		$this->Controller = $controller;
		$this->Action = $action;
		$this->Pattern = $pattern;
		if ($reverse) {
			$this->Reverse = $reverse;
		} else {
			if (gettype($pattern) == 'array') {
				$reverse = array();
				foreach ($pattern as $lang => $value) $reverse[$lang] = trim($value, '#^$');
				$this->Reverse = $reverse;
			} else {
				$this->Reverse = trim($pattern, '#^$');
			}
		}
		$this->Params = $params;
	}
}