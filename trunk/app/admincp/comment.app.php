<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: comment.app.php 634 2013-04-03 06:02:53Z coolmoo $
*/
class commentApp{
    function __construct() {
    }
    function doiCMS(){
    	include iACP::view("comment.manage");
    }

}