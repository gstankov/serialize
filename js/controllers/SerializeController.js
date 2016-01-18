app.controller('SerializeController', function ($scope) {

	// Scope math functions
	$scope.Math = window.Math;

	// Main object
	$scope.data = {};

	// Defaults

	$scope.data.fileSelected = false;

	// Input output
	$scope.data.filename 	= "No file chosen"; 	// Must select
	$scope.data.fileorpages = "file"; 				// file || pages

	// Prinfile
	$scope.data.width   = ""; 						// Min 1
	$scope.data.height  = ""; 						// Min 1
	$scope.data.margins = ""; 						// Min 0
	$scope.data.border 	= true;

	// Nest
	$scope.data.nestwidth = ""; 					// Min width + (margins * 2)
	$scope.data.center 	  = true;

	// Numbering
	$scope.data.copiesorrange 	= "copies"; 		// copies || range
	$scope.data.copies 			= ""; 				// Min 1
	$scope.data.maxcopies		= ""; 				// Min 1
	$scope.data.from 			= ""; 				// Min 0 - Max (to - 1)
	$scope.data.to 				= ""; 				// Min (from + 1), Max (maxcopies	)
	$scope.data.origin 			= "TL";				// TL || TR || BR || BL
	$scope.data.x 				= ""; 				// Min 0 || center
	$scope.data.y 				= ""; 				// Min 0 || center
	$scope.data.prefix 			= "";
	$scope.data.centerx			= false;
	$scope.data.centery 		= false;
	$scope.data.fontsize 		= ""; 				// Min 1
	$scope.data.bold 			= false;

	// Watch changes and validate
	$scope.$watch('data', function () {

		// Nestwidth
		if ($scope.data.fileorpages == "file") {
			
			if ($scope.data.nestwidth < ($scope.data.width+($scope.data.margins*2))) {
				$scope.serialize.nestwidth.$setValidity("min", false);
			} else {
				$scope.serialize.nestwidth.$setValidity("min", true);
			}
			
		} else {
			$scope.serialize.nestwidth.$setValidity("min", true);
		}

		// From - to range
		if ($scope.data.copiesorrange == "range") { // Switch to range

			if ($scope.data.from >= $scope.data.to) {

				$scope.serialize.from.$setValidity("max", false);
				$scope.serialize.to.$setValidity("min", false);

			} else {

				$scope.serialize.from.$setValidity("max", true);
				$scope.serialize.to.$setValidity("min", true);

			}

			// If to bigger than max copies
			if ($scope.data.to > $scope.data.maxcopies) {
				$scope.serialize.to.$setValidity("max", false);
			} else {
				$scope.serialize.to.$setValidity("max", true);
			}

		} else { // Switch back to copies

			$scope.serialize.from.$setValidity("max", true);
			$scope.serialize.to.$setValidity("min", true);

		}

		// X and Y centering
		if ($scope.data.centerx) {
			$scope.serialize.x.$setValidity("min", true);
		} else if ($scope.data.x < 0) {
			$scope.serialize.x.$setValidity("min", false);
		}

		if ($scope.data.centery) {
			$scope.serialize.y.$setValidity("min", true);
		} else if ($scope.data.y < 0) {
			$scope.serialize.y.$setValidity("min", false);
		}

	}, true);

	// Set filename on change
	$scope.fileNameChanged = function (element) {

		if (element.value != "") { // If file selected

			// File selected flag
			$scope.data.fileSelected = true;

			// Get filename with extension from full path
			var filename = element.value.replace(/^.*[\\\/]/, '');

			// Name and extension
			var name = filename.replace(/.[^.]+$/,'');
			var ext  = filename.replace(/^.*\./,'');

			// If name longer than 17 chars
			if(name.length >= 17) {
				name = name.substring(0, 17);
				name = name+"... ";
			}

			// Combine string and set value
		 	$scope.data.filename = name+"."+ext;

		 	// Apply
			$scope.$apply();

		} else { // If canceled

			// File selected flag
			$scope.data.fileSelected = false;
			$scope.data.filename 	 = "No file chosen";

			// Apply
			$scope.$apply();

		}

	}

});