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

trait PropsGettersSetters
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
	 * This array is parsed automatically by method `\MvcCore\Route::initMatch();` 
	 * if necessary or by method `\MvcCore\Route::initReverse();` after it's 
	 * necessary, to be able to complete URL address string in method and sub
	 * methods of `\MvcCore\Route::Url();`.
	 * Example: 
	 * // For pattern `/products-list/<name>/<color>`
	 * `["name", "color"];`
	 * @var \string[]|NULL
	 */
	#protected $reverseParams	= NULL;

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
		/** @var $this \MvcCore\IRoute */
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
		/** @var $this \MvcCore\IRoute */
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
		/** @var $this \MvcCore\IRoute */
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
		/** @var $this \MvcCore\IRoute */
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
		/** @var $this \MvcCore\IRoute */
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
	 * TODO: dopsat
	 * @return string|array|NULL
	 */
	public function GetGroupName () {
		return $this->groupName;
	}

	/**
	 * TODO: dopsat
	 * @param string|array|NULL $groupName 
	 * @return \MvcCore\Route|\MvcCore\IRoute
	 */
	public function & SetGroupName ($groupName) {
		/** @var $this \MvcCore\IRoute */
		$this->groupName = $groupName;
		return $this;
	}
}
