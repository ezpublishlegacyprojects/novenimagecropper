<?php
// SOFTWARE NAME: Noven Image Cropper
// SOFTWARE RELEASE: @@@VERSION@@@
// COPYRIGHT NOTICE: Copyright (C) 1999-2009 Noven
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

class novenimagecropperInfo
{
    static function info()
    {
        return array( 'Name' => "Noven Image Cropper",
                      'Version' => "1.0 RC1",
        			  'Author'	=> 'Jérôme Vieilledent',
                      'Copyright' => "Copyright © 2009 NOVEN",
                      'License' => "GNU General Public License v2.0",
                      'Includes the following libraries'              => array( 'jQuery' => 'v1.3.2 http://jquery.com',
                                                                              'jQuery UI' => 'v1.7.2 http://jqueryui.com',
                                                                              'jCrop' => 'v0.9.8 http://deepliquid.com/content/Jcrop.html',
                                                                              'Ajax Upload' => 'v3.5 http://valums.com/ajax-upload/',
                                                                              'eZ Components' => 'http://ezcomponents.org/')
                      );
    }
}
?>
