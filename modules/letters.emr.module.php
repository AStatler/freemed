<?php
 // $Id$
 // note: letters of referral, etc
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 // lic : GPL, v2

if (!defined("__LETTERS_EMR_MODULE_PHP__")) {

define ('__LETTERS_EMR_MODULE_PHP__', true);

class lettersModule extends freemedEMRModule {

	var $MODULE_NAME    = "Letters";
	var $MODULE_VERSION = "0.1";

	var $record_name    = "Letters";
	var $table_name     = "letters";
	var $patient_field  = "letterpatient";

	function lettersModule () {
		// Set vars for patient management summary
		$this->summary_vars = array (
			_("Date") => "letterdt",
			_("To")   => "letterto:physician"
		);
		$this->summary_options = SUMMARY_VIEW | SUMMARY_VIEW_NEWWINDOW;

		// For display action, disable patient box for print
		// but only if we're the correct module
		global $action, $module;
		if (($action=="display") and (strtolower($module)==get_class($this))) {
			$this->disable_patient_box = true;
		}

		// Variables for add/mod
		global $patient;
		$this->variables = array (
			"letterdt" => date_assemble("letterdt"),
			"letterfrom",
			"letterto",
			"lettertext",
			"letterpatient" => $patient
		);

		// The current table definition
		$this->table_definition = array (
			"letterdt" => SQL_DATE,
			"letterfrom" => SQL_VARCHAR(150),
			"letterto" => SQL_VARCHAR(150),
			"lettertext" => SQL_TEXT,
			"lettersent" => SQL_INT_UNSIGNED(0),
			"letterpatient" => SQL_INT_UNSIGNED(0),
			"id" => SQL_NOT_NULL(SQL_AUTO_INCREMENT(SQL_INT(0)))
		);
		$this->freemedEMRModule();
	} // end constructor lettersModule

	function add () {
		global $HTTP_POST_FILES;

		// Check for uploaded msworddoc
		if (!empty($HTTP_POST_FILES["msworddoc"]["tmp_name"]) and file_exists($HTTP_POST_FILES["msworddoc"]["tmp_name"])) {
			$doc = $HTTP_POST_FILES["msworddoc"]["tmp_name"];

			// Convert to the temporary file
			$__command = "/usr/bin/wvWare -x /usr/share/wv/wvText.xml \"$doc\"";
			$output = `$__command`;

			// Read temporary file into lettertext
			global $lettertext;
			$lettertext = $output;

			// Remove uploaded document
			unlink($doc);
		} // end checking for uploaded msworddoc

		// Call wrapped function
		$this->_add();
	} // end function lettersModule->add

	function form () {
		global $display_buffer;
		reset($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global ${$k};

		switch ($action) { // internal action switch
			case "addform":
				// Check for this_user object
				global $this_user;
				if (!is_object($this_user)) {
					$this_user = CreateObject('FreeMED.User');
				}

				// If we're a physician, use us
				if ($this_user->isPhysician()) {
					global $letterfrom;
					$letterfrom = $this_user->user_phy;
				}
			break; // end internal addform
			case "modform":
			if (($patient<1) OR (empty($patient))) {
				$display_buffer .= _("You must select a patient.")."\n";
				template_display ();
			}
			$r = freemed::get_link_rec ($id, $this->table_name);
			foreach ($r AS $k => $v) {
				global ${$k};
				${$k} = stripslashes($v);
			}
			extract ($r);
			break; // end internal modform
		} // end internal action switch

		$display_buffer .= "
		<p/>
		<form ACTION=\"$this->page_name\" METHOD=\"POST\" ".
		"ENCTYPE=\"multipart/form-data\">
		<input TYPE=\"HIDDEN\" NAME=\"MAX_FILE_SIZE\" ".
		"VALUE=\"1000000\">
		<input TYPE=\"HIDDEN\" NAME=\"action\"  VALUE=\"".
			( ($action=="addform") ? "add" : "mod" )."\"\>
		<input TYPE=\"HIDDEN\" NAME=\"id\"      VALUE=\"".prepare($id)."\"\>
		<input TYPE=\"HIDDEN\" NAME=\"patient\" VALUE=\"".prepare($patient)."\"\>
		<input TYPE=\"HIDDEN\" NAME=\"module\"  VALUE=\"".prepare($module)."\"\>
		";

		$display_buffer .= html_form::form_table(array(
		_("Date") =>
		date_entry("letterdt"),

		_("From") =>
		freemed_display_selectbox(
			$sql->query("SELECT * FROM physician WHERE phyref='no' ".
				"ORDER BY phylname"),
			"#phylname#, #phyfname#",
			"letterfrom"
		),
		

		_("To") =>
		freemed_display_selectbox(
			$sql->query("SELECT * FROM physician ORDER BY phylname"),
			"#phylname#, #phyfname#",
			"letterto"
		),

		_("Text") =>
		html_form::text_area("lettertext", 20, 4),

		));

		// Check for Word document attachment ...
		if (($action=="add") or ($action=="addform")) {
			$display_buffer .= "
			<div ALIGN=\"CENTER\">
			<input TYPE=\"FILE\" NAME=\"msworddoc\"/>
			</div>
			";
		}
 
		$display_buffer .= "
		<div ALIGN=\"CENTER\">
		<input TYPE=SUBMIT VALUE=\"  ".
	         ( ($action=="addform") ? _("Add") : _("Modify"))."  \"/>
		<input TYPE=\"RESET\" VALUE=\" "._("Clear")." \"/>
		<input TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"Cancel\"/>
		</div>
		</form>
		";
	} // end function lettersModule->form

	function display () {
		global $display_buffer, $patient, $action, $id, $title,
			$return, $SESSION;

		global $no_template_display; $no_template_display=true;
		global $this_patient;

		$title = _("View Letter");

		// Get link record
		$record = freemed::get_link_rec($id, $this->table_name);

		// Resolve docs
		$from = CreateObject('FreeMED.Physician', $record[letterfrom]);
		$to   = CreateObject('FreeMED.Physician', $record[letterto]  );

		// Create date, address, etc, header
		$display_buffer .= "
		<!-- padding for letterhead -->
		&nbsp;<br/>
		&nbsp;<br/>
		&nbsp;<br/>
		&nbsp;<br/>
		&nbsp;<br/>
		&nbsp;<br/>
		<table width=\"100%\" border=\"0\" cellspacing=\"0\"
		 cellpadding=\"2\" valign=\"top\">
		<tr>
				<!-- date header -->
			<td width=\"50%\">&nbsp;</td>
			<td width=\"50%\" align=\"left\">".fm_date_print($record[letterdt])."</td>
		</tr>
		<tr>
				<!-- padding -->
			<td colspan=\"2\"> &nbsp; </td>
		</tr>
		<tr>
			<td align=\"left\">
				<!-- physician information -->
				
			</td>
			<td align=\"left\">
					<!-- patient information -->
				<u>Re: ".$this->this_patient->fullName()."</u><br/>
				<u>DOB: ".$this->this_patient->dateOfBirth()."</u>
			</td>
		</tr>
		</table>
		";

		$display_buffer .= "
		<div ALIGN=\"CENTER\" CLASS=\"infobox\">
		<table BORDER=\"0\" CELLSPACING=\"0\" WIDTH=\"100%\" ".
		"CELLPADDING=\"2\">
		<tr>
			<td ALIGN=\"RIGHT\" WIDTH=\"25%\">"._("Date")."</td>
			<td ALIGN=\"LEFT\" WIDTH=\"75%\">".$record[letterdt]."</td>
		</tr>
		<tr>
			<TD ALIGN=\"RIGHT\">"._("From")."</TD>
			<TD ALIGN=\"LEFT\">".$from->fullName()."</TD>
		</tr>
		<tr>
			<td ALIGN=\"RIGHT\">"._("To")."</td>
			<td ALIGN=\"LEFT\">".$to->fullName()."</td>
		</tr>
		</table>
		</div>
		<div ALIGN=\"LEFT\" CLASS=\"letterbox\">
		".stripslashes(str_replace("\n", "<br/>", htmlentities($record[lettertext])))."
		</div>
		";
	} // end function lettersModule->display

	function view () {
		global $display_buffer;
		reset ($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;

	$query = "SELECT * FROM ".$this->table_name." ".
		"WHERE (".$this->patient_field."='".addslashes($patient)."') ".
		"ORDER BY letterdt";
	$result = $sql->query ($query);
	$rows = ( ($result > 0) ? $sql->num_rows ($result) : 0 );

	if ($rows < 1) {
		$display_buffer .= "
         <p/>
         <div ALIGN=\"CENTER\">
         "._("This patient has no letters.")."
         </div>
         <p/>
         <div ALIGN=\"CENTER\">
         <a HREF=\"$this->page_name?action=addform&module=$module&patient=$patient\"
          >"._("Add")." "._("$record_name")."</a>
         <b>|</b>
         <a HREF=\"manage.php?id=$patient\"
          >"._("Manage Patient")."</a>
         </div>
         <p/>
		";
		template_display();
	} // if there are none...

	// or else, display them...
	$display_buffer .= "
		<p/>".
		freemed_display_itemlist (
			$result,
			$this->page_name,
			array (
				_("Date") => "letterdt",
				_("From") => "letterfrom",
				_("To") => "letterto"
			),
			array ("", "", ""),
			array (
				"",
				"physician" => "phylname",
				// This is a workaround because it relies on
				// key/value pairs, and it cannot have a
				// duplicate key without being *very* funny.
				"physician " => "phylname"
			)
		);
	} // end function lettersModule->view()

} // end class lettersModule

register_module ("lettersModule");

} // end if defined

?>
