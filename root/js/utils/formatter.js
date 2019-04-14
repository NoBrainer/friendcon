// Dependencies: jQuery
(function(){

// When $input loses focus, its value will be formatted into the accepted phone number format
window.formatPhoneNumberOnBlur = function formatPhoneNumberOnBlur($input){
	$input.blur(function(){
		var $this = $(this);
		var value = ($this.val() || "").trim();
		
		var formattedPhoneNumber = null;
		var pieces;
		
		// See if the input is already in the right format
		if(pieces = value.match(/^\(\d{3}\)\d{3}\-\d{4}$/)){
			formattedPhoneNumber = pieces[0];
		}
		// Support a 10-digit number and removes some non-numeric characters
		// Example: "123-456-7890" or "123 456-7890" or "1234567890" or "123...456,,,7890"
		else if(pieces = value.match(/^\D*(\d{3})\D*(\d{3})\D*(\d{4})$/)){
			formattedPhoneNumber = "(" + pieces[1] + ")" + pieces[2] + "-" + pieces[3];
		}
		// Strip away all non-numeric characters and see if we have 10 numbers
		else{
			var strippedValue = value.replace(/\D/g, "");
			if(pieces = strippedValue.match(/^\D*(\d{3})\D*(\d{3})\D*(\d{4})$/)){
				formattedPhoneNumber = "(" + pieces[1] + ")" + pieces[2] + "-" + pieces[3];
			}
		}
		
		// If we formatted it, use that value
		if(formattedPhoneNumber != null && $this.val() != formattedPhoneNumber){
			$this.val(formattedPhoneNumber);
		}
	});
};
})();