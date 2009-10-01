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
/**
 * ezjscServerFunctions for novenimagecropper
 * @uses ezjscore
 * @uses eZ Components
 * @author Jerome Vieilledent
 */
class NovImgCropServerFunctions extends ezjscServerFunctionsJs
{
	/**
	 * @var eZTemplate
	 */
	private static $tpl;
	
	/**
	 * @var eZHTTPTool
	 */
	private static $http;
	
	private static function init()
	{
		include_once( "kernel/common/template.php" );
		include_once( "kernel/common/i18n.php" );
		self::$tpl = templateInit();
		self::$http = eZHTTPTool::instance();
	}
	
	/**
	 * Returns the image reference for cropping
	 * @param array $args Ordered values are : AttributeID, ContentObjectVersion, ContentObjectID
	 * @return string HTML image tag with the reference image
	 */
	public static function imageReference( array $args )
	{
		self::init();
		$AttributeID = $args[0];
		$ContentObjectVersion = $args[1];
		$ContentObjectID = $args[2];
		
		// Fetch the attribute
		$attribute = eZContentObjectAttribute::fetch($AttributeID, $ContentObjectVersion);
		self::$tpl->setVariable('attribute', $attribute);
		$content = self::$tpl->fetch( "design:novimagecrop/nov_imagereference.tpl" );
		
		return $content;
	}
	
	/**
	 * Refreshes image infos in the edit view of the datatype ezimage
	 * @param array $args Ordered values are : AttributeID, ContentObjectVersion, ContentObjectID
	 * @return string HTML string
	 */
	public static function refreshImage( array $args )
	{
		self::init();
		$AttributeID = $args[0];
		$ContentObjectVersion = $args[1];
		$ContentObjectID = $args[2];
		
		// Fetch the attribute
		$attribute = eZContentObjectAttribute::fetch($AttributeID, $ContentObjectVersion);
		self::$tpl->setVariable('attribute', $attribute);
		$content = self::$tpl->fetch( "design:novimagecrop/nov_refreshimage.tpl" );
		
		return $content;
	}
	
	/**
	 * Does a crop preview or saves the real crop (depending on "Mode" param)
	 * @param array $args Ordered values are : AttributeID, ContentObjectVersion, Mode (can be "preview" or "do")
	 * @return string HTML string for the preview or nothing if in "do" mode
	 */
	public static function crop( array $args )
	{
		self::init();
		$AttributeID = $args[0];
		$ContentObjectVersion = $args[1];
		$Mode = $args[2];
		$fileHandler = eZClusterFileHandler::instance();
	
		try
		{
			// Fetch the attribute
			$attribute = eZContentObjectAttribute::fetch($AttributeID, $ContentObjectVersion);
			$imageHandler = $attribute->content();
			
			$referenceAlias = $imageHandler->attribute('reference'); // Cropping UI is based on "reference" alias
			$fileHandler->filePath = $referencePath = $referenceAlias['url'];
			$fileHandler->fetch();
			
			$originalAlias = $imageHandler->attribute('original');
			$fileHandler->filePath = $originalPath = $originalAlias['url'];
			$fileHandler->fetch();
			$ratio = $originalAlias['width'] / $referenceAlias['width']; // Ratio between "original" and "reference" aliases. Will be applicated on posted coords
			
			// Posted coords
			$xRef = (int)self::$http->postVariable('x', 0);
			$yRef = (int)self::$http->postVariable('y', 0);
			$wRef = (int)self::$http->postVariable('w', 0);
			$hRef = (int)self::$http->postVariable('h', 0);
			
			if(!$wRef || !$hRef)
				throw new InvalidArgumentException(ezi18n('extension/novenimagecropper/error', 'Please make a selection to crop your image'));
			
			// Adapt coords if needed
			switch($Mode)
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
			
			$tmpImage = $aliasFrom['dirpath'].'/'.$aliasFrom['basename'].'_crop-'.$AttributeID.'-'.$ContentObjectVersion.'.jpg';
			
			$converter->transform( 
				'crop', 
				$referencePath,
				$tmpImage
			);
			
			if($Mode == 'preview')
			{
				self::$tpl->setVariable('cropPreviewSrc', $tmpImage);
				$fileHandler->fileStore($tmpImage, 'image', true);
				$content = self::$tpl->fetch( "design:novimagecrop/nov_previewcrop.tpl" );
			}
			else if($Mode == 'do')
			{
				$attribute->fromString($tmpImage);
				$attribute->store();
				$fileHandler->fileDeleteLocal($tmpImage);
				$fileHandler->fileDelete($tmpImage);
				$content = null;
			}
			
			// Delete files fetched from DB to FS, in case of cluster mode
			$fileHandler->fileDeleteLocal($originalAlias);
			$fileHandler->fileDeleteLocal($referenceAlias);
		}
		catch(Exception $e)
		{
			$content = $e->getMessage();
			eZDebug::writeError($e->getMessage(), 'NovenImageCropper');
		}
		
		return $content;
	}
	
	/**
	 * Delete the temporary image used for the preview
	 * @param array $args Ordered values are : AttributeID, ContentObjectVersion
	 * @return void
	 */
	public static function deleteTmpImage( array $args )
	{
		self::init();
		$AttributeID = $args[0];
		$ContentObjectVersion = $args[1];
		$fileHandler = eZClusterFileHandler::instance();
		
		// Fetch the attribute
		$attribute = eZContentObjectAttribute::fetch($AttributeID, $ContentObjectVersion);
		$imageHandler = $attribute->content();
		$originalAlias = $imageHandler->attribute('original');
		
		// Check if file exists and delete it
		$tmpImage = $originalAlias['dirpath'].'/'.$originalAlias['basename'].'_crop-'.$AttributeID.'-'.$ContentObjectVersion.'.jpg';
		if($fileHandler->fileExists($tmpImage))
			$fileHandler->fileDelete($tmpImage);
	}
	
	/**
	 * Returns the right URL Prefix depending on eZ Publish configuration (VHost, URI mode...)
	 * @param array $args
	 * @return string
	 */
	public static function getURLPrefix( array $args=null )
	{
		return self::getIndexDir();
	}
	
	/**
	 * Update the image attribute from a path
	 * @param array $args Ordered values are : AttributeID, ContentObjectVersion
	 * @return string
	 */
	public static function updateImageByPath( array $args )
	{
		self::init();
		
		$newImagePath = self::$http->postVariable('imagePath');
		$AttributeID = $args[0];
		$ContentObjectVersion = $args[1];
		$fileHandler = eZClusterFileHandler::instance();
		
		$fileHandler->filePath = $newImagePath;
		$fileHandler->fetch();
		$newImagePath = realpath($newImagePath);
		$imageAttribute = eZContentObjectAttribute::fetch($AttributeID, $ContentObjectVersion);
		$imageAttribute->fromString($newImagePath);
		$imageAttribute->store();
		
		// Now delete local image
		$fileHandler->deleteLocal($newImagePath);
		
		return 'success';
	}
}