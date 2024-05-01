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
use ViaThinkSoft\OIDplus\OIDplusObjectTypePlugin;
// phpcs:disable PSR1.Files.SideEffects
\defined('INSIDE_OIDPLUS') or die;
// phpcs:enable PSR1.Files.SideEffects

class ObjectTypePluginRdap extends OIDplusObjectTypePlugin {

	protected $db_table_exists = false;
	/**
	 * @return string
	 */
	public static function getObjectTypeClassName(): string {
		return \Frdlweb\OIDplus\OIDplusObjectRdap::class;
	}

	public function init($html = true) { 
		$rdapPlugin = OIDplus::getPluginByOid("1.3.6.1.4.1.37476.9000.108.1276945");		         
		if (is_null($rdapPlugin)   ) {
		 throw new OIDplusException(sprintf('You have to install the %s plugin!', '1.3.6.1.4.1.37476.9000.108.1276945'));            
		} 
	}//init	
	
	
}
