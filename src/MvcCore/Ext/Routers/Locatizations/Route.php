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
	 * @param string $lang
	 * @return string|\string[]|NULL
	 */
	public function GetPattern ($lang = NULL) {
		return $lang !== NULL
			? $this->pattern[$lang]
			: $this->pattern;
	}

	/**
	 * @param string|\string[] $pattern
	 * @param string $lang 
	 * @return \MvcCore\Ext\Routers\Localizations\Route|\MvcCore\Interfaces\IRoute
	 */
	public function & SetPattern ($pattern, $lang = NULL) {
		if ($lang !== NULL) {
			$this->pattern[$lang] = $pattern;
		} else {
			$this->pattern = $pattern;
		}
		return $this;
	}

	/**
	 * @param string $lang 
	 * @return string|\string[]|NULL
	 */
	public function GetMatch ($lang = NULL) {
		return $lang !== NULL
			? $this->match[$lang]
			: $this->match;
	}

	/**
	 * @param string|\string[] $match
	 * @param string $lang 
	 * @return \MvcCore\Ext\Routers\Localizations\Route|\MvcCore\Interfaces\IRoute
	 */
	public function & SetMatch ($match, $lang = NULL) {
		if ($lang !== NULL) {
			$this->match[$lang] = $match;
		} else {
			$this->match = $match;
		}
		return $this;
	}

	/**
	 * @param string $lang 
	 * @return string|\string[]|NULL
	 */
	public function GetReverse ($lang = NULL) {
		return $lang !== NULL
			? $this->reverse[$lang]
			: $this->reverse;
	}

	/**
	 * @param string|\string[] $reverse
	 * @param string $lang 
	 * @return \MvcCore\Ext\Routers\Localizations\Route|\MvcCore\Interfaces\IRoute
	 */
	public function & SetReverse ($reverse, $lang = NULL) {
		if ($lang !== NULL) {
			$this->reverse[$lang] = $reverse;
		} else {
			$this->reverse = $reverse;
		}
		return $this;
	}

	/**
	 * @param string $lang 
	 * @return array|\array[]
	 */
	public function & GetDefaults ($lang = NULL) {
		return $lang !== NULL
			? $this->defaults[$lang]
			: $this->defaults;
	}

	/**
	 * @param array|\array[] $defaults
	 * @param string $lang 
	 * @return \MvcCore\Ext\Routers\Localizations\Route|\MvcCore\Interfaces\IRoute
	 */
	public function & SetDefaults ($defaults = [], $lang = NULL) {
		if ($lang !== NULL) {
			$this->defaults[$lang] = & $defaults;
		} else {
			$this->defaults = & $defaults;
		}
		return $this;
	}

	/**
	 * @param string $lang 
	 * @return array|\array[]
	 */
	public function & GetConstraints ($lang = NULL) {
		return $lang !== NULL
			? $this->constraints[$lang]
			: $this->constraints;
	}

	/**
	 * @param array|\array[] $constraints
	 * @param string $lang 
	 * @return \MvcCore\Ext\Routers\Localizations\Route|\MvcCore\Interfaces\IRoute
	 */
	public function & SetConstraints ($constraints = [], $lang = NULL) {
		if ($lang !== NULL) {
			$this->constraints[$lang] = & $constraints;
			if (!isset($this->defaults[$lang]))
				$this->defaults[$lang] = [];
			$defaults = & $this->defaults[$lang];
			foreach ($constraints as $key => $value)
				if (!isset($defaults[$key]))
					$defaults[$key] = NULL;
		} else {
			$this->constraints = & $constraints;
			foreach ($constraints as $lang => $constraintItem) {
				if (!isset($this->defaults[$lang]))
					$this->defaults[$lang] = [];
				$defaults = & $this->defaults[$lang];
				foreach ($constraintItem as $key => $value)
					if (!isset($defaults[$key]))
						$defaults[$key] = NULL;
			}
		}
		return $this;
	}
}
