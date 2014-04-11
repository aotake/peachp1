<?php
#
# index.php
#
# Copyright 2011-2012, Bmath Web Application Platform Project. (http://bmath.jp)
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>
#
# @copyright     Copyright 2011-2012, Bmath Web Application Platform Project. (http://bmath.jp)
# @link          http://bmath.jp Bmath Web Application Platform Project
# @package       Ao.html
# @since
# @license       GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
# @author        aotake (aotake@bmath.org)
#

define('START_TIME', microtime(true));  
$base_dir = dirname(dirname(__FILE__));
$app_dir = $base_dir . '/webapp';

defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

require_once $app_dir.'/bootstrap.php';
Webapp::bootstrap($app_dir, "debug");
$msg = "Total Time: ".(string)((float)microtime(true) - (float)START_TIME);
if( isset($_SERVER["REQUEST_URI"]) ){
    $msg.= ", REQUEST_URI=".$_SERVER["REQUEST_URI"];
}
Zend_Registry::get("logger")->debug($msg);
