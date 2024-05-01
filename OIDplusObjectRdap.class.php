<?php

/*
 * OIDplus 2.0
 * Copyright 2019 - 2023 Daniel Marschall, ViaThinkSoft
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Frdlweb\OIDplus;

use ViaThinkSoft\OIDplus\OIDplus;
use ViaThinkSoft\OIDplus\OIDplusObject;
// phpcs:disable PSR1.Files.SideEffects
\defined('INSIDE_OIDPLUS') or die;
// phpcs:enable PSR1.Files.SideEffects

class OIDplusObjectRdap extends OIDplusObject {
	/**
	 * @var string
	 */
	protected $rdap;
	public $delimiter = '-';
	public static $delimiters = ['-', '/', '@'];
    public $sdepth = 0;

	

	
	public function __construct(string $rdap, ?string $delimiter = null) {
		// No syntax checks
		$this->rdap = $rdap;
		$this->sdepth = 0;
		if(null === $delimiter){
	  	 foreach(static::$delimiters as $del){
			if (str_contains($this->rdap, $del)) {
				//$delimiter = $del;
				$this->sdepth++;
			}
		 }	
			
		}//null ==== $delimiter
		
		if(null === $delimiter){
		  $delimiter = 	static::$delimiters[min($this->sdepth, count(static::$delimiters)-1)];
		}
		
		$this->delimiter = $delimiter;
	}

	/**
	 * @param string $node_id
	 * @return OIDplusrdap|null
	 */
	public static function parse(string $node_id)/*: ?OIDplusrdap*/ {
		@list($namespace, $rdap) = explode(':', $node_id, 2);
		if ($namespace !== self::ns()) return null;
		$d = static::$delimiters[0];
		foreach(array_reverse(static::$delimiters) as $del){
			if (str_contains($rdap, $del)) {
				$d = $del;
				break;
			}
		}
		return new self($rdap, $d);
	}

	/**
	 * @return string
	 */
	public static function objectTypeTitle(): string {
		return _L('Services');
	}

	/**
	 * @return string
	 */
	public static function objectTypeTitleShort(): string {
		return _L('Service');
	}

	/**
	 * @return string
	 */
	public static function ns(): string {
		return 'service';
	}

	/**
	 * @return string
	 */
	public static function root(): string {
		return self::ns().':';
	}

	/**
	 * @return bool
	 */
	public function isRoot(): bool {
		return $this->rdap === '';
	}

	/**
	 * @param bool $with_ns
	 * @return string
	 */
	public function nodeId(bool $with_ns=true): string {
		return $with_ns ? self::root().$this->rdap : $this->rdap;
	}

	/**
	 * @param string $str
	 * @return string
	 */
	public function addString(string $str): string {
		if ($this->isRoot()) {
			return self::root() . $str;
		} else {
		//	return $this->nodeId() . $this->delimiter . $str;
			//static::$delimiters[max($this->sdepth, count(static::$delimiters)-1)]
			return $this->nodeId() . static::$delimiters[min($this->sdepth, count(static::$delimiters)-1)] . $str;
		}
	}

	/**
	 * @param OIDplusObject $parent
	 * @return string
	 */
	public function crudShowId(OIDplusObject $parent): string {
		if ($parent->isRoot()) {
			return substr($this->nodeId(), strlen($parent->nodeId()));
		} else {
			return substr($this->nodeId(), strlen($parent->nodeId())+1);
		}
	}

	/**
	 * @param OIDplusObject|null $parent
	 * @return string
	 */
	public function jsTreeNodeName(OIDplusObject $parent = null): string {
		if ($parent == null) return $this->objectTypeTitle();
		if ($parent->isRoot()) {
			return substr($this->nodeId(), strlen($parent->nodeId()));
		} else {
			return substr($this->nodeId(), strlen($parent->nodeId())+1);
		}
	}

	/**
	 * @return string
	 */
	public function defaultTitle(): string {
		$ary = explode($this->delimiter, $this->rdap); // TODO: but if an arc contains "\", this does not work. better read from db?
		$ary = array_reverse($ary);
		return $ary[0];
	}

	/**
	 * @return bool
	 */
	public function isLeafNode(): bool {
		return false;
	}

	/**
	 * @param string $title
	 * @param string $content
	 * @param string $icon
	 * @return void
	 * @throws OIDplusException
	 */
	public function getContentPage(string &$title, string &$content, string &$icon) {
		$icon = file_exists(__DIR__.'/img/main_icon.png') ? OIDplus::webpath(__DIR__,OIDplus::PATH_RELATIVE).'img/main_icon.png' : '';
      //   $selfClass = \get_called_class();
		$selfClass = get_class($this);
		if ($this->isRoot()) {
			$title = $selfClass::objectTypeTitle();

			$res = OIDplus::db()->query("select * from ###objects where parent = ?", array(self::root()));
			if ($res->any()) {
				$content  = '<p>'._L('Please select an object in the tree view at the left to show its contents.').'</p>';
			} else {
				$content  = '<p>'._L('Currently, RDAP or Service objects are registered in the system.').'</p>';
			}

			if (!$this->isLeafNode()) {
				if (OIDplus::authUtils()->isAdminLoggedIn()) {
					$content .= '<h2>'._L('Manage root objects').'</h2>';
				} else {
					$content .= '<h2>'._L('Available objects').'</h2>';
				}
				$content .= '%%CRUD%%';
			}
		} else {
			$title = $this->getTitle();

			$content = '<h2>'._L('Description').'</h2>%%DESC%%'; // TODO: add more meta information about the object type

			if (!$this->isLeafNode()) {
				if ($this->userHasWriteRights()) {
					$content .= '<h2>'._L('Create or change subordinate service objects').'</h2>';
				} else {
					$content .= '<h2>'._L('Subordinate service objects').'</h2>';
				}
				$content .= '%%CRUD%%';
			}
		}
	}

	/**
	 * @return OIDplusrdap|null
	 */
	public function one_up()/*: ?OIDplusrdap*/ {
		$oid = $this->rdap;

		$p = strrpos($oid, $this->delimiter);
		if ($p === false) return self::parse($oid);
		if ($p == 0) return self::parse($this->delimiter);

		$oid_up = substr($oid, 0, $p);

		return self::parse(self::ns().':'.$oid_up);
	}

	/**
	 * @param OIDplusObject|string $to
	 * @return int|null
	 */
	public function distance($to) {
		if (!is_object($to)) $to = OIDplusObject::parse($to);
		if (!$to) return null;
		if (!($to instanceof $this)) return null;

		$a = $to->rdap;
		$b = $this->rdap;

		if (substr($a,0,1) == $a->delimiter) $a = substr($a,1);
		if (substr($b,0,1) == $b->delimiter) $b = substr($b,1);

		$ary = explode($a->delimiter, $a);
		$bry = explode($b->delimiter, $b);

		$min_len = min(count($ary), count($bry));

		for ($i=0; $i<$min_len; $i++) {
			if ($ary[$i] != $bry[$i]) return null;
		}

		return count($ary) - count($bry);
	}

	/**
	 * @return string
	 */
	public function getDirectoryName(): string {
		if ($this->isRoot()) return $this->ns();
		return $this->ns().'_'.md5($this->nodeId(false));
	}

	/**
	 * @param string $mode
	 * @return string
	 */
	public static function treeIconFilename(string $mode): string {
		return 'img/'.$mode.'_icon16.png';
	}
}
