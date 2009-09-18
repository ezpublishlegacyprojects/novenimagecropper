<?php

//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: Noven Image Uploader
// SOFTWARE RELEASE: @@@VERSION@@@
// COPYRIGHT NOTICE: Copyright (C) 2009 - Jerome Vieilledent, Noven.
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//
// ## END COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
//

include_once( "kernel/common/template.php" );

$Module = $Params["Module"];
$Result = array();
$tpl = templateInit();

// Fetch the attribute
$attribute = eZContentObjectAttribute::fetch($Params['AttributeID'], $Params['ContentObjectVersion']);
$tpl->setVariable('attribute', $attribute);

$content = '';
switch($Params['DisplayMode'])
{
	case 'imagereference':
		$content = $tpl->fetch( "design:novimagecrop/nov_imagereference.tpl" );
		break;
	default:
		$content = $tpl->fetch( "design:novimagecrop/nov_refreshimage.tpl" );
		break;
}

$Result['pagelayout'] = 'novimagecrop/pagelayout_plain.tpl';
$Result['content'] = $content;

// Clean exit to avoid debug to display
//eZDB::checkTransactionCounter();
//eZExecution::cleanExit();