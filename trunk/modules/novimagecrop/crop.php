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

include_once( "kernel/common/template.php" );

$Module = $Params["Module"];
$Result = array();
$tpl = templateInit();
$http = eZHTTPTool::instance();
$fileHandler = eZClusterFileHandler::instance();

try
{
	// Fetch the attribute
	$attribute = eZContentObjectAttribute::fetch($Params['AttributeID'], $Params['ContentObjectVersion']);
	$imageHandler = $attribute->content();
	
	$referenceAlias = $imageHandler->attribute('reference'); // Cropping UI is based on "reference" alias
	$referencePath = $fileHandler->fileFetch($referenceAlias['url']);
	$originalAlias = $imageHandler->attribute('original');
	$originalPath = $fileHandler->fileFetch($originalAlias['url']);
	$ratio = $originalAlias['width'] / $referenceAlias['width']; // Ratio between "original" and "reference" aliases. Will be applicated on posted coords
	
	// Posted coords
	$xRef = (int)$http->postVariable('x', 0);
	$yRef = (int)$http->postVariable('y', 0);
	$wRef = (int)$http->postVariable('w', 0);
	$hRef = (int)$http->postVariable('h', 0);
	
	if(!$wRef || !$hRef)
		throw new InvalidArgumentException(ezi18n('extension/novenimagecropper/error', 'Please make a selection to crop your image'));
	
	// Adapt coords if needed
	switch($Params['Mode'])
	{
		case 'do':
			$x = $xRef * $ratio;
			$y = $yRef * $ratio;
			$w = $wRef * $ratio;
			$h = $hRef * $ratio;
			$aliasFrom = $originalAlias;
		break;
		
		case 'preview':
			$x = $xRef;
			$y = $yRef;
			$w = $wRef;
			$h = $hRef;
			$aliasFrom = $referenceAlias;
		break;
		
		default:
			throw new InvalidArgumentException(ezi18n('extension/novenimagecropper/error', 'Invalid crop mode'));
	}
	
	// Cropping w/ eZ Components
	$settings = new ezcImageConverterSettings(
		array(
			new ezcImageHandlerSettings( 'ImageMagick', 'ezcImageImagemagickHandler' ),
			new ezcImageHandlerSettings( 'GD', 'ezcImageGdHandler' ),
		)
	);
	$filters = array( 
		new ezcImageFilter( 
			'crop',
			array( 
				'x'			=> $xRef,
				'y'			=> $yRef,
				'width'		=> $wRef,
				'height'	=> $hRef
			)
		),
	);
	$converter = new ezcImageConverter( $settings );
	$converter->createTransformation( 'crop', $filters, array( 'image/jpeg' ) );
	
	$tmpImage = $aliasFrom['dirpath'].'/'.$aliasFrom['basename'].'_crop-'.$Params['AttributeID'].'-'.$Params['ContentObjectVersion'].'.jpg';
	
	$converter->transform( 
		'crop', 
		$referencePath,
		$tmpImage
	);
	
	if($Params['Mode'] == 'preview')
	{
		$tpl->setVariable('cropPreviewSrc', $tmpImage);
		$fileHandler->fileStore($tmpImage, 'image', true);
		$Result['pagelayout'] = 'novimagecrop/pagelayout_plain.tpl';
		$Result['content'] = $tpl->fetch( "design:novimagecrop/nov_previewcrop.tpl" );
	}
	else if($Params['Mode'] == 'do')
	{
		$attribute->fromString($tmpImage);
		$attribute->store();
		$fileHandler->fileDeleteLocal($tmpImage);
		$fileHandler->fileDelete($tmpImage);
	}
}
catch(Exception $e)
{
	$tpl->setVariable('error_message', $e->getMessage());
	eZDebug::writeError($e->getMessage(), 'NovenImageCropper');
}

