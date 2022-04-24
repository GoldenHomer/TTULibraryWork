<?php
// input values from the contract modal
$name = $_POST["patronName"];
$initials = $_POST["initials"];
$rNumber = $_POST["rNumber"];
$class = $_POST["classification"];
$startDate = $_POST["startDate"];
$endDate = $_POST["endDate"];
$staffName = $_POST["staffName"];
$carrelNum = $_POST["carrel"];

// Include the main TCPDF library (which produces the PDF)
require_once('tcpdf/tcpdf.php');

$pdfTitle = 'TTU_Library_Study_Carrel_Contract.pdf';

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Texas Tech University Libraries');
$pdf->SetTitle($pdfTitle);
$pdf->SetSubject('Library Study Carrels');

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
#$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set line height - found on stackoverflow
$pdf->setCellHeightRatio(1.3);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// set font
$pdf->SetFont('times', '', 10);

// add page
$pdf->AddPage();

// HTML content
$html = "<h1>Library Study Carrel Contract</h1>
		 <p>Study carrels may be reserved by TTU (main campus) faculty, graduate (level 6000, 7000, 8000) and Honors College students working on their senior thesis (HON 3300, 4300).</p>";
		 
# Concatenate appropriate contract text	based on carrel user's TTU status
switch ($class) {
  // faculty and visiting will have the same text
  case 'Faculty':
  case 'Visiting':
	$html .= "<strong>FACULTY</strong> members may keep a carrel for one year with the renewal date being June 1 of each year. Faculty members are asked to relinquish their carrel if they leave or they are no longer using the study carrel on a reasonably regular basis.
			  <ul>
				<li><strong>RENEWAL:</strong> Renewals must be made by contacting <a href='mailto:libraries.studycarrels@ttu.edu'>libraries.studycarrels@ttu.edu</a>.</li>
			  </ul>";
	break;
	
  case 'Graduate':
    $html .= "<strong>GRADUATE</strong> student enrolled in 6000, 7000, and 8000 courses may keep their carrel for one semester.
				<ul>
					<li><strong>RENEWAL: </strong>Renewals must be made by contacting <a href='mailto:libraries.studycarrels@ttu.edu'>libraries.studycarrels@ttu.edu</a> during the first two weeks of each fall and spring semester and the first week of each summer semester. Failure to renew a carrel at the proper time will result in the loss of its use.</li>
				</ul>";
	break;

  case 'Honors':
    $html .= "<strong>HONORS </strong>college students enrolled in HON 3300, or 4300 may keep their carrel for one semester if any are available.
			  <ul>
				<li><strong>RENEWAL: </strong>This carrel may not be renewed.</li>
			  </ul>";
	break;
}		

// for some reason, inline css only works here if style is wrapped with double quotes, not single, which is why $html continues as a single quote string
$html .= '<p><strong>PERSONAL PROPERTY:</strong></p>
		 <ul>
			<li>The Library does not accept responsibility for belongings and/or equipment left in the study carrels. Personal computers, calculators, books or any other items of value should not be left unattended in the carrel.</li>
			<li>Any personal items left in the carrels past the time of expiration will be retained in the Access Services Department for two weeks. Any item(s) not claimed within that time will be taken to the TTU Police Department.</li>
			<li>All Library materials used in the carrels MUST be checked out at the service desk. The carrels will be inspected periodically by Access Services Staff. Items that are not checked out and all non-circulating materials will be removed.</li>
		 </ul>
		 
		 <p><strong>KEYS:</strong></p>
		 <ul>
		   <li>Assigned study carrels have a key lock. Assigned keys must be returned to the Access Services Department at the end of each semester, unless the carrel has been renewed for the next semester.</li>
		   <li><strong>A $60 fee will be charged to the patron for each lost key. </strong><em>Initial here: </em><span style="color: red;">'.$initials.'</span></li>
		 </ul>
		 
		 <p><strong>SAFETY:</strong></p>
		 <ul>
		   <li>Take a moment to locate the nearest exit from your study carrel. In the event of an alarm, please take all personal belongings with you, lock your carrel and leave the building. Elevators will not function during an alarm; please use the stairs.</li>
		   <li>For your safety and other carrel users and patrons, please observe the following guidelines:</li>
		   <ul>
			   <li>No electrical appliances of any kind (printers, microwaves, coffee makers, crockpots, heaters, etc.)</li>
			   <li>No tobacco products or e-cigarettes of any kind (TTU OP 60.15)</li>
			   <li>Please turn off the desk light upon leaving the carrel</li>
			   <li>Do not tamper with the permanent light fixture</li>
			   <li>Do not cover the glass in the door at any time</li>
			   <li>Do not place objects (i.e. wood boards, metal planks) above or below the carrel door</li>
			   <li>Use headphones when listening to audio devices</li>
		   </ul>
		   <li>Failure to observe these guidelines may result in suspension of privilege.</li>
		 </ul>
		 <p>For more information, please contact Access Services at 806-742-2265 or <a href="mailto:libraries.studycarrels@ttu.edu">libraries.studycarrels@ttu.edu</a></p>
		 <p><strong>Date: </strong>'.$startDate.'</p>
		 <p><strong>Patron Name: </strong>'.$name.'</p>
		 <p><strong>R#: </strong>'.$rNumber.'</p>
		 <p><strong>Patron Initials: </strong>'.$initials.'</p>
		 <p><strong>Carrel #: </strong>'.$carrelNum.'</p>
		 <p><strong>Expiration Date: </strong>'.$endDate.'</p>
		 <p><strong>Staff Name: </strong>'.$staffName.'</p>
';

// output the HTML content
//$pdf->writeHTML($html, true, 0, true, 0);
$pdf->writeHTML($html, true, false, true, false, '');

// reset pointer to the last page
$pdf->lastPage();
?>
