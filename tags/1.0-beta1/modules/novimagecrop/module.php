<?php

//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: Noven Image Cropper
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

$Module = array('name' => 'novimagecrop');

$ViewList = array();

/*
 * AJAX image upload
 */
$ViewList['upload'] = array(
	'script'					=>	'upload.php',
	'params'					=> 	array(),
	'unordered_params'			=> 	array(),	
	'single_post_actions'		=> 	array(),
	'post_action_parameters'	=> 	array()
);

/**
 * AJAX image infos refresh
 */
$ViewList['refreshimage'] = array(
	'script'					=>	'refreshimage.php',
	'params'					=> 	array('AttributeID', 'ContentObjectVersion', 'ContentObjectID'),
	'unordered_params'			=> 	array(
		'mode'		=> 'DisplayMode'
	),	
	'single_post_actions'		=> 	array(),
	'post_action_parameters'	=> 	array()
);

$ViewList['crop'] = array(
	'script'					=>	'crop.php',
	'params'					=> 	array('AttributeID', 'ContentObjectVersion'),
	'unordered_params'			=> 	array(
		'mode'		=> 'Mode'
	),	
	'single_post_actions'		=> 	array(),
	'post_action_parameters'	=> 	array()
);

$ViewList['deletetmpimage'] = array(
	'script'					=>	'deletetmpimage.php',
	'params'					=> 	array('AttributeID', 'ContentObjectVersion'),
	'single_post_actions'		=> 	array(),
	'post_action_parameters'	=> 	array()
);