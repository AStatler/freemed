<?php
 // $Id$
 // note: error_handler
 // lic : GPL, v2

if (!defined("__ERROR_HANDLER_PHP__")) {

define ('__ERROR_HANDLER_PHP__', true);

function freemed_standard_error_handler ($no, $str, $file, $line, $context) {
	switch ($no) {
		case E_ERROR:
		case E_PARSE:
		case E_COMPILE_ERROR:
		case E_COMPILE_WARNING:
		case E_CORE_ERROR:
		case E_CORE_WARNING:
		case E_USER_ERROR:
		case E_USER_WARNING:
			$error =
				"Package : ".PACKAGENAME."\n".
				"Version : ".VERSION."\n".
				"phpwebtools Version : ".WEBTOOLS_VERSION."\n".
				"Installation : ".INSTALLATION."\n".
				"IP : ".$GLOBALS["SERVER_NAME"]."\n".
				"Timestamp : ".date("D M d Y h:i a")."\n".
				"Script : ".str_replace(BASE_URL."/", "", $GLOBALS["PHP_SELF"])."\n".
				"File : ".str_replace(chop(`pwd`)."/", "", $file)."\n".
				"Line : ".$line."\n".
				"Error : ".$str."\n";

			// since we are in a error handler we need to check this
			if ($GLOBALS["FREEMED_BOX"] == false)  // we have a box top
			{	
				if (function_exists("freemed_display_html_top"))	
					freemed_display_html_top ();
				if (function_exists("freemed_display_box_top"))	
					freemed_display_box_top ();
			}
			// currently, show error
			echo "<PRE>\n$error\n</PRE>\n";
			if (BUG_TRACKER) {
				echo "
				<P>
				<CENTER>
				<FORM ACTION=\"http://www.freemed.org/report_bug.php\" METHOD=POST
				TARGET=\"bug_report\">
				<INPUT TYPE=HIDDEN NAME=\"report\" VALUE=\"".
				prepare($error)."\">
				<INPUT TYPE=SUBMIT VALUE=\""._("Submit Bug Report")."\">
				</FORM>
				</CENTER>
				";
			} // end if BUG_TRACKER

			// since we are in a error handler we need to check this
			if ($GLOBALS["FREEMED_BOX"] == true)  // we have a box top
			{	
				if (function_exists("freemed_display_box_bottom"))	
					freemed_display_box_bottom ();
				if (function_exists("freemed_display_html_bottom"))	
					freemed_display_html_bottom ();
			}
			
			DIE("");
			break;
		//default: echo "error type : $no<BR>\n"; break;
	} // end switch
} // end function freemed_standard_error_handler

// set as default error handler
error_reporting ( );
$original_error_handler = set_error_handler("freemed_standard_error_handler");

} // end checking for __ERROR_HANDLER_PHP__

?>
