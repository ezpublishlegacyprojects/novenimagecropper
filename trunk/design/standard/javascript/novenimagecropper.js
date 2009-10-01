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

var currentAttributeID = null;
var currentAttributeVersion = null;
var divDialog = null;
var divReference = null;
var dialogLoader = null;
var divPreview = null;
var jcrop = null; // Instance of jcrop
var imageReference = null;
var URLPrefix = '';

$(document).ready(function() {
	
	/**
	 * AJAX image upload handler
	 * @uses AjaxUpload - http://valums.com/ajax-upload/
	 */
	$('input.novenimageuploader').each(function() {
		var aImageInfos = $(this).attr('name').split('_data_imagename_');
		var baseName = aImageInfos[0];
		var attributeId = aImageInfos[1];
		var contentObjectVersion = $(this).attr('ez_contentobject_version');
		var contentObjectId = $(this).attr('ez_contentobject_id');
		var curEl = $(this);
		
		// Get the URLPrefix (only for AJAX upload feature)
		$.ez('novimgcrop::getURLPrefix', function(data) {
			URLPrefix = data.content;
			new AjaxUpload(curEl, {
				action: URLPrefix+'novimagecrop/upload',
				name: 'imageFile',
				data: {
					'BaseName': baseName,
					'AttributeID': attributeId,
					'ContentObjectVersion': contentObjectVersion,
					'ContentObjectID' : contentObjectId 
				},
				onSubmit : function(file, ext) {
					$('#novenimageloading_'+attributeId).show(); // Display loader
					$('#novenimagemimetypeerror_'+attributeId).hide(); // Hide errors
					if (! (ext && /^(jpg|png|jpeg|gif)$/.test(ext))){
						// extension is not allowed, cancel upload
						$('#novenimageloading_'+attributeId).hide();
						$('#novenimagemimetypeerror_'+attributeId).show();
                        return false;
					}
				},
				onComplete : function(file) {
					// Hide loader and refresh image infos
					$('#novenimageloading_'+attributeId).hide();
					refreshImageDetails(attributeId, contentObjectVersion, contentObjectVersion);
				}
			});
		});
		
	});
	
	$('.novenimagecropdialog').dialog({
		modal:true,
		width: 800,
		minWidth: 800,
		resizable: true,
		autoOpen: false,
		close: closeDialog
	});
	
	/**
	 * Display crop interface w/ Jquery UI Dialog
	 */
	$('.novenimagecropper_trigger').click(function() {
		attributeInfos = $(this).attr('id').split('_');
		currentAttributeID = attributeInfos[1];
		currentAttributeVersion = attributeInfos[2];
		currentObjectID = attributeInfos[3];
		divDialog = $('#novimagecropdialog_'+currentAttributeID);
		divReferenceArea = $('div#novimagecroprefarea_'+currentAttributeID);
		divReference = $('div#novimagecropreference_'+currentAttributeID);
		divPreviewArea = $('div#novimagecroppreviewarea_'+currentAttributeID);
		divPreview = divDialog.find('div.novenimagecroppreview');
		dialogLoader = divDialog.find('div.loading');
		
		divReference.empty();
		dialogLoader.show();
		divDialog.dialog('open');
		
		// Loading the reference image
		var urlReference = 'novimgcrop::imageReference::'+currentAttributeID+'::'+currentAttributeVersion+'::'+currentObjectID+'?'+(new Date()).getTime();
		divReference.ez(urlReference, {}, null, function() {
				
			dialogLoader.hide();
			imageReference = divReference.find('img:first');
			
			setTimeout('initJCrop()', 500);
		});
	});
	
	/**
	 * Crop aspect ratio
	 */
	$('.novenimagecropper_aspectratio').change(function() {
		var ratio = $(this).val();
		jcrop.setOptions({aspectRatio: parseFloat(ratio)});
		jcrop.focus();
	});
	
	/**
	 * Handle Preview
	 */
	$('input.novenimagecropper_previewbutton').click(function() {
		var urlPreview = 'novimgcrop::crop::'+currentAttributeID+'::'+currentAttributeVersion+'::preview';
		var params = {
			'x': getCropValue('x'),
			'y': getCropValue('y'),
			'w': getCropValue('w'),
			'h': getCropValue('h')
		};
		
		dialogLoader.show();
		divPreview.ez(urlPreview, params, null, function() {
			dialogLoader.hide();
			divReferenceArea.hide();
			divPreviewArea.show();
		});
	});
	
	/**
	 * Handle rollback
	 */
	$('input.novenimagecropper_cancelbutton').click(function() {
		divDialog.dialog('close');
	});
	
	/**
	 * Back to selection
	 */
	$('input.novenimagecropper_backbutton').click(function() {
		deleteTmpImage();
		divPreviewArea.hide();
		divReferenceArea.show();
	});
	
	$('input.novenimagecropper_savebutton').click(function() {
		var urlCrop = 'novimgcrop::crop::'+currentAttributeID+'::'+currentAttributeVersion+'::do';
		var params = {
			'x': getCropValue('x'),
			'y': getCropValue('y'),
			'w': getCropValue('w'),
			'h': getCropValue('h')
		};
		
		dialogLoader.show();
		$.ez(urlCrop, params, function() {
			dialogLoader.hide();
			refreshImageDetails(currentAttributeID, currentAttributeVersion, currentObjectID);
			divDialog.dialog('close');
		});
	});
});


// ##### FUNCTIONS #####

function initJCrop() {
	jcrop = $.Jcrop(imageReference, {
		onSelect: setCoords,
		onChange: setCoords
	});
}

/**
 * Update hidden fields with coords
 * @param c jcrop object
 */
function setCoords(c) {
	$('input[name="novimagecrop_'+currentAttributeID+'\\[x\\]"]').val(c.x);
	$('input[name="novimagecrop_'+currentAttributeID+'\\[y\\]"]').val(c.y);
	$('input[name="novimagecrop_'+currentAttributeID+'\\[w\\]"]').val(c.w);
	$('input[name="novimagecrop_'+currentAttributeID+'\\[h\\]"]').val(c.h);
}

/**
 * Get one of the crop settings defined by the user
 * @param type Type of the setting needed. May be x, y, w or h
 */
function getCropValue(type) {
	return $('input[name="novimagecrop_'+currentAttributeID+'\\['+type+'\\]"]').val();
}

function closeDialog() {
	deleteTmpImage();
	divPreviewArea.hide();
	divReferenceArea.show();
}

/**
 * Delete temporary cropped image for preview
 */
function deleteTmpImage() {
	var url = 'novimgcrop::deleteTmpImage::'+currentAttributeID+'::'+currentAttributeVersion;
	$.ez(url);
}

function refreshImageDetails(attributeId, contentObjectVersion, contentObjectId) {
	var url = 'novimgcrop::refreshImage::'+attributeId+'::'+contentObjectVersion+'::'+contentObjectId+'?'+(new Date()).getTime();
	$('#imageinfos_'+attributeId).ez(url);
	
	var buttonCrop = $('#novimagecroptrigger_'+attributeId+'_'+contentObjectVersion+'_'+contentObjectId); 
	buttonCrop.removeClass('button-deleted');
	buttonCrop.addClass('button');
	buttonCrop.removeAttr('disabled');
	
	var buttonDeleteImage = $('#delete_image_'+attributeId);
	buttonDeleteImage.removeClass('button-deleted');
	buttonDeleteImage.addClass('button');
	buttonDeleteImage.removeAttr('disabled');
}

/**
 * Callback function that can be called externally to update the image attribute (from another attribute for ex.)
 * @param imagePath New image path
 * @param attributeId
 * @param contentObjectVersion
 * @param contentObjectId
 * @return void
 */
function updateImage(imagePath, attributeId, contentObjectVersion, contentObjectId) {
	var url = 'novimgcrop::updateImageByPath::'+attributeId+'::'+contentObjectVersion;
	var postData = {'imagePath': imagePath};
	$.ez(url, postData, function() {
		refreshImageDetails(attributeId, contentObjectVersion, contentObjectId);
		
		// Send an event on the corresponding element (parent of the image) to notify that image update is OK
		var evt = $.Event('novimgcrop.image_updated');
		evt.cropData = {'attributId': attributeId, 'contentObjectVersion': contentObjectVersion, 'contentObjectId': contentObjectId};
		$('#imageinfos_'+attributeId).trigger(evt);
	});
}