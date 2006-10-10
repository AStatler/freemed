<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // Copyright (C) 1999-2006 FreeMED Software Foundation
 //
 // This program is free software; you can redistribute it and/or modify
 // it under the terms of the GNU General Public License as published by
 // the Free Software Foundation; either version 2 of the License, or
 // (at your option) any later version.
 //
 // This program is distributed in the hope that it will be useful,
 // but WITHOUT ANY WARRANTY; without even the implied warranty of
 // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 // GNU General Public License for more details.
 //
 // You should have received a copy of the GNU General Public License
 // along with this program; if not, write to the Free Software
 // Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

include_once ( dirname(__FILE__).'/php-gettext/gettext.inc' );
include_once ( dirname(__FILE__).'/iso-set.php' );

T_bindtextdomain ( 'freemed', dirname(dirname(__FILE__)).'/locale' );
T_setlocale ( LC_MESSAGES, $_SESSION['language'] ? $_SESSION['language'] : DEFAULT_LANGUAGE );

// Set text domain for page
T_bindtextdomain ( 'freemed', LOCALE_DIR );
T_bind_textdomain_codeset ( 'freemed', language2isoset ( $_SESSION['language'] ? $_SESSION['language'] : DEFAULT_LANGUAGE ) );

// Load text domain for UI
T_textdomain ( UI );

?>
