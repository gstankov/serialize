<?php

// Validation
$error = [];

// Radios
$radios = [
	"fileorpages" => null, 
	"copiesorrange" => null, 
	"origin" => null
];

foreach ($radios as $key => $value) {
	if (isset($_POST[$key])) {
		$radios[$key] = $_POST[$key];
	} else {
		$radios[$key] = false;
		$error[] = $key . " value not specified.";
	}
}

// Checkboxes
$checkboxes = [
	"border" => null, 
	"center" => null, 
	"centerx" => null, 
	"centery" => null, 
	"bold" => null
];

foreach ($checkboxes as $key => $value) {
	if (isset($_POST[$key])) {
		$checkboxes[$key] = true;
	} else {
		$checkboxes[$key] = false;
	}
}

// Inputs
$inputs = [
	"width" => null, 
	"height" => null, 
	"margins" => null, 
	"nestwidth" => null, 
	"copies" => null, 
	"maxcopies" => null, 
	"from" => null, 
	"to" => null, 
	"x" => null, 
	"y" => null, 
	"prefix" => null, 
	"fontsize" => null, 
];

foreach ($inputs as $key => $value) {
	if (isset($_POST[$key])) {
		$inputs[$key] = $_POST[$key];
	} else {
		$inputs[$key] = false;
		$error[] = $key . " value not specified.";
	}
}

if (sizeof($error) == 0)  {

	// File must be set
	if ($_FILES['file']['name'] == "") {
		$error[] = "File was not selected.";
	}

	// Width and height
	if ($inputs['width'] <= 0 || $inputs['height'] <= 0) {
		$error[] = "Width or height is too small.";
	}

	// Margins are invalid
	if ($inputs['margins'] < 0) {
		$error[] = "Margins are invalid.";
	}

	// Nestwidth
	if ($radios['fileorpages'] == "file") {
		if ($inputs['nestwidth'] < ($inputs['width'] + ($inputs['margins']*2))) {
			$error[] = "Target width is too small.";
		}
	}

	// Copies
	if ($radios['copiesorrange'] == "range") { // Switch to range

		// Valid from
		if ($inputs['from'] >= $inputs['to']) {
			$error[] = "Valid range is not specified.";
		}

		// If to bigger than max copies
		if ($inputs['to'] > $inputs['maxcopies']) {
			$error[] = "Value 'to' is bigger than max specified.";
		}

	} else {

		// Number of copies specified is too small
		if ($inputs['copies'] < 1) {
			$error[] = "Number of copies specified is too small.";
		}

	}

	// X
	if ($inputs['x'] < 0 && $checkboxes['centerx'] == false) {
		$error[] = "The value of 'x' is invalid.";
	}

	// Y
	if ($inputs['y'] < 0 && $checkboxes['centery'] == false) {
		$error[] = "The value of 'y' is invalid.";
	}

	// Y
	if ($inputs['fontsize'] < 1) {
		$error[] = "Font size is invalid.";
	}

}

// Echo errors
if (sizeof($error) != 0) {

	$out = "";
	$out.= "<h1>Some errors have occured:</h1>";
	$out.= "<ol>";

	foreach ($error as $key => $value) {
		$out.= "<li>" . $value . "</li>";
	}

	$out.= "</ol>";
	echo $out;

}

// Serialize

if(isset($_POST['submit']) && sizeof($error) == 0) { // If submited

	if ($_FILES['file']['name'] != "") { // If file specified 

		// Dependencies

		// Upload class
		require_once('class/upload/class.upload.php');

		// Upload
		$file = new Upload($_FILES['file']);

		if ($file->uploaded) { // If uploaded

			// Allow only PDF's
			$file->allowed = array('application/pdf');
			$file->mime_check = true;
			$file->file_safe_name = true;
			$file->file_new_name_body = 'template';

			// Process
			$file->Process('data/');

			if ($file->processed) { // No processing error

				// PDF classes
				require_once('class/tfpdf/tfpdf.php');
				require_once('class/FPDI-1.4.2/fpdi.php');
				require_once('class/FPDI-1.4.2/FPDI_Protection.php');

				// Radios
				$fileorpages 	= $radios['fileorpages'];
				$copiesorrange 	= $radios['copiesorrange'];
				$origin 		= $radios['origin'];

				// Checkboxes
				$border 	= $checkboxes['border'];
				$center 	= $checkboxes['center'];
				$centerx 	= $checkboxes['centerx'];
				$centery 	= $checkboxes['centery'];
				$bold 		= $checkboxes['bold'];

				// Inputs	
				$width 		= $inputs['width'];
				$height 	= $inputs['height'];
				$margins 	= $inputs['margins'];
				$nestwidth 	= $inputs['nestwidth'];
				$copies 	= $inputs['copies'];
				$maxcopies 	= $inputs['maxcopies'];
				$from 		= $inputs['from'];
				$to 		= $inputs['to'];
				$x 			= $inputs['x'];
				$y 			= $inputs['y'];
				$prefix 	= $inputs['prefix'];
				$fontsize 	= $inputs['fontsize'];

				// Copies or range
				if($copiesorrange == "range") {
					$copies = sizeof(range($from, $to));
				}

				// OUTPUT NEST
				if ($fileorpages == "file") {

					$leftMargin = 0;

					// Set page width and height an calculate items per row
					$pWidth = $nestwidth;

					// If copies fit in width
					if (($copies * ($width + $margins)) < $pWidth) {

						$perRow  = $copies;
						$pHeight = $height + $margins;

					} else {

						$perRow  = floor($pWidth / ($width + $margins));
						$pHeight = ceil($copies / $perRow) * ($height + $margins);

					}

					// Add last margin
					$pHeight = $pHeight + $margins;
					$pWidth  = $pWidth + $margins;

					// Center print
					if ($center) {
						$leftMargin = ($pWidth - (($perRow * ($width + $margins)) + $margins)) / 2;
					}

					// Write to document
					class PDF extends FPDI_Protection {

						// Set data
						function setData($width, $height, $margins, $copies, $pWidth, $pHeight, $perRow, $leftMargin, $border, $x, $y, $prefix, $origin, $fontsize, $copiesorrange, $from, $centerx, $centery, $maxcopies, $bold) {

							$this->width   		 = $width;
							$this->height  		 = $height;
							$this->margins 		 = $margins;
							$this->copies  		 = $copies;
							$this->pWidth  		 = $pWidth;
							$this->pHeight 		 = $pHeight;
							$this->perRow  		 = $perRow;
							$this->leftMargin    = $leftMargin;
							$this->border   	 = $border;
							$this->nx   		 = $x;
							$this->ny   		 = $y;
							$this->prefix		 = $prefix;
							$this->origin		 = $origin;
							$this->fontsize		 = $fontsize;
							$this->copiesorrange = $copiesorrange;
							$this->from 		 = $from;
							$this->centerx 		 = $centerx;
							$this->centery 		 = $centery;
							$this->maxcopies	 = $maxcopies;
							$this->bold 		 = $bold;

						}

						// Serialize function
						function Serialize() {

							// Bold?
							if ($this->bold) {
								// Add bold font
								$this->AddFont('Arial', 'B', 'arialbd.ttf', true);
							} else {
								// Add normal font
								$this->AddFont('Arial', '', 'arial.ttf', true);
							}

							// Font size in points
							$pointFontSize = $this->fontsize * 2.83464567;

							// Bold?
							if ($this->bold) {
								// Set bold font
								$this->SetFont('Arial', 'B', $pointFontSize);
							} else {
								// Set normal font
								$this->SetFont('Arial', '', $pointFontSize);
							}

							// Start position
							$x = $this->leftMargin + $this->margins;
							$y = $this->margins;

							// For each copy
							for ($i=1; $i <= $this->copies; $i++) {

								// Format number
								if ($this->copiesorrange == "range") {

									$formatted = str_pad(
										$this->from + $i-1, strlen(
												(string) $this->maxcopies
										), '0', STR_PAD_LEFT
									);

								} else {

									$formatted = str_pad(
										$i-1, strlen(
											(string) $this->copies
										), '0', STR_PAD_LEFT
									);

								}

								// Set serial no
								$serial = $formatted;

								// Add prefix if set
								if ($this->prefix != "") $serial = $this->prefix . " " . $formatted;

								// Cell width (+ 2mm)
								$cellWidth = $this->GetStringWidth($serial) + 2;

								// Set number position
								$cx = 0;
								$cy = 0;

								switch ($this->origin) {

									// Top left
									case "TL":

										$cx = $x + $this->nx;
										$cy = $y + $this->ny;

									break;

									// Top right
									case "TR":

										$cx = $x + (($this->width - ($cellWidth)) - $this->nx);
										$cy = $y + $this->ny;
										
									break;

									// Bottom right
									case "BR":

										$cx = $x + (($this->width - ($cellWidth)) - $this->nx);
										$cy = $y + (($this->height - ($this->fontsize + 1)) - $this->ny);

									break;

									// Bottom left
									case "BL":

										$cx = $x + $this->nx;
										$cy = $y + (($this->height - ($this->fontsize + 1)) - $this->ny);

									break;

								}

								// Center X
								if ($this->centerx) {
									$cx = $x + (($this->width / 2) - ($cellWidth / 2));
								}

								// Center Y
								if ($this->centery) {
									$cy = $y - ((($this->height / 2) + (($this->fontsize + 1) / 2)) + $this->margins);
								}

								// If both (?)
								if ($this->centerx && $this->centery) {
									$cx = $x + (($this->width / 2) - ($cellWidth / 2));
									$cy = $y - ((($this->height / 2) + (($this->fontsize + 1) / 2)) + $this->margins);
								}

								// Set computed position
								$this->setXY($cx, $cy);

								// Draw rect
								if ($this->border) $this->Rect($x, $y, $this->width, $this->height);

								// Create number cell
								$this->Cell($cellWidth, ($this->fontsize + 1), $serial, 1, 1, "C");

								// Increment X by width
								$x = $x + ($this->width + $this->margins);

								// If diviseable
								if ($i % $this->perRow == 0) {
									$i != $this->copies ? $y = $y + ($this->height + $this->margins) : $y = $y + $this->height; // Increment Y by height
									$x = $this->leftMargin + $this->margins;	// Reset X
								}

							}
							
						}

						// No page breaking
						function AcceptPageBreak() {
							return false;
						}

					} // End of class

					// Portrait or landscape
					$pWidth > $pHeight ? $pl = "L" : $pl = "P";

					// PDF constructor
					$pdf = new PDF($pl, 'mm', array($pWidth, $pHeight));

					// Add a page
					$pdf->AddPage();

					// Set uploaded file
					$pdf->setSourceFile($file->file_dst_path . $file->file_dst_name);

					// Import 1st page
					$template = $pdf->importPage(1);

					// Set data
					$pdf->setData($width, $height, $margins, $copies, $pWidth, $pHeight, $perRow, $leftMargin, $border, $x, $y, $prefix, $origin, $fontsize, $copiesorrange, $from, $centerx, $centery, $maxcopies, $bold);

					// Place template
					$x = $leftMargin + $margins;
					$y = $margins;

					for ($i=1; $i <= $copies; $i++) {

						// Draw template
						$pdf->useTemplate($template, $x, $y, $width, $height);

						$x = $x + ($width + $margins);

						if ($i % $perRow == 0) {
							$i != $copies ? $y = $y + ($height + $margins) : $y = $y + $height; // Increment Y by height
							$x = $leftMargin + $margins;
						}

					}

					// Do serialization
					$pdf->Serialize();

					// Output file inline
					$pdf->Output("Serialized.pdf", 'I');
					
				}

				// OUTPUT PAGES
				if ($fileorpages == "pages") {

					// Set page width
					$pWidth  = $width + ($margins * 2);
					$pHeight = $height + ($margins * 2);

					// Write to document
					class PDF extends FPDI_Protection {

						// Set data
						function setData($width, $height, $margins, $copies, $pWidth, $pHeight, $border, $x, $y, $prefix, $origin, $fontsize, $copiesorrange, $from, $centerx, $centery, $maxcopies, $bold, $serial) {

							$this->width   		 = $width;
							$this->height  		 = $height;
							$this->margins 		 = $margins;
							$this->copies  		 = $copies;
							$this->pWidth  		 = $pWidth;
							$this->pHeight 		 = $pHeight;
							$this->border   	 = $border;
							$this->nx   		 = $x;
							$this->ny   		 = $y;
							$this->prefix		 = $prefix;
							$this->origin		 = $origin;
							$this->fontsize		 = $fontsize;
							$this->copiesorrange = $copiesorrange;
							$this->from 		 = $from;
							$this->centerx 		 = $centerx;
							$this->centery 		 = $centery;
							$this->maxcopies	 = $maxcopies;
							$this->bold 		 = $bold;
							$this->serial 		 = $serial;

						}

						// Serialize function
						function Serialize() {

							// Bold?
							if ($this->bold) {
								// Add bold font
								$this->AddFont('Arial', 'B', 'arialbd.ttf', true);
							} else {
								// Add normal font
								$this->AddFont('Arial', '', 'arial.ttf', true);
							}

							// Start position
							$x = $this->margins;
							$y = $this->margins;

							// Cell width (+ 2mm)
							$cellWidth = $this->GetStringWidth($this->serial) + 2;

							// Set number position
							$cx = 0;
							$cy = 0;

							switch ($this->origin) {

								// Top left
								case "TL":

									$cx = $x + $this->nx;
									$cy = $y + $this->ny;

								break;

								// Top right
								case "TR":

									$cx = $x + (($this->width - ($cellWidth)) - $this->nx);
									$cy = $y + $this->ny;
									
								break;

								// Bottom right
								case "BR":

									$cx = $x + (($this->width - ($cellWidth)) - $this->nx);
									$cy = $y + (($this->height - ($this->fontsize + 1)) - $this->ny);

								break;

								// Bottom left
								case "BL":

									$cx = $x + $this->nx;
									$cy = $y + (($this->height - ($this->fontsize + 1)) - $this->ny);

								break;

							}

							// Center X
							if ($this->centerx) {
								$cx = $x + (($this->width / 2) - ($cellWidth / 2));
							}

							// Center Y
							if ($this->centery) {
								$cy = $y - ((($this->height / 2) + (($this->fontsize + 1) / 2)) + ($this->margins * 2));
							}

							// If both (?)
							if ($this->centerx && $this->centery) {
								$cx = $x + (($this->width / 2) - ($cellWidth / 2));
								$cy = $y - ((($this->height / 2) + (($this->fontsize + 1) / 2)) + ($this->margins * 2));
							}

							// Set computed position
							$this->setXY($cx, $cy);

							// Draw rect
							if ($this->border) $this->Rect($x, $y, $this->width, $this->height);

							// Create number cell
							$this->Cell($cellWidth, ($this->fontsize + 1), $this->serial, 1, 1, "C");
							
						}

						// No page breaking
						function AcceptPageBreak() {
							return false;
						}

					} // End of class

					// Portrait or landscape
					$pWidth > $pHeight ? $pl = "L" : $pl = "P";

					// PDF constructor
					$pdf = new PDF($pl, 'mm', array($pWidth, $pHeight));

					// Set uploaded file
					$pdf->setSourceFile($file->file_dst_path . $file->file_dst_name);

					// Import 1st page
					$template = $pdf->importPage(1);

					for ($i=1; $i <= $copies; $i++) {

						// Font size in points
						$pointFontSize = $fontsize * 2.83464567;

						// Bold?
						if ($bold) {
							// Set bold font
							$pdf->SetFont('Arial', 'B', $pointFontSize);
						} else {
							// Set normal font
							$pdf->SetFont('Arial', '', $pointFontSize);
						}

						// Format number
						if ($copiesorrange == "range") {
							$formatted = str_pad($from + $i-1, strlen((string) $maxcopies), '0', STR_PAD_LEFT);
						} else {
							$formatted = str_pad($i-1, strlen((string) $copies), '0', STR_PAD_LEFT);
						}

						// Set serial no
						$serial = $formatted;

						// Add prefix if set
						if ($prefix != "") $serial = $prefix . " " . $formatted;

						// Set data
						$pdf->setData($width, $height, $margins, $copies, $pWidth, $pHeight, $border, $x, $y, $prefix, $origin, $fontsize, $copiesorrange, $from, $centerx, $centery, $maxcopies, $bold, $serial);

						// Add a page
						$pdf->AddPage();

						// Draw template
						$pdf->useTemplate($template, $margins, $margins, $width, $height);

						// Do serialization
						$pdf->Serialize($serial);

					}

					// Output file inline
					$pdf->Output("Serialized.pdf", 'I');

				}

				// Delete uploaded file
				unlink($file->file_dst_path . $file->file_dst_name);

			} else { // If problem processing

				echo "Error: " . $file->error; exit();

			}

		} else { // If problem uploading

			echo "Error: Problem while uploading."; exit();

		}

	} else { // If submit not set

		echo "Error: No file specified."; exit();

	}

}

?>