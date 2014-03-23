// Constants

var AJAX_TIMEOUT_SHORT = 10000;
var AJAX_TIMEOUT_MEDIUM = 15000;
var AJAX_TIMEOUT_LONG = 20000;
var AJAX_TIMEOUT_INFINITY = 0;

var PASSWORD_MINIMUM_LENGTH = 6;

var regexpAllNumeric = new RegExp(/^\d*$/);
var regexpZipUS = new RegExp(/(^\d{5}$)|(^\d{5}-\d{4}$)/);
var regexpZipCA = new RegExp(/^[a-zA-Z][0-9][a-zA-Z]\s?[0-9][a-zA-Z][0-9]$/);
var regexpEmail = new RegExp(/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/);
var regexpPhoneUSAndCA = new RegExp(/^[0-9]{10}$/);

// Set up generic working dialog, but must load after page

var standardWorkingDialog = null;
require(["dojo/ready"], function(ready){
	  ready(function(){
		  standardWorkingDialog = new dijit.Dialog({
				title: '&nbsp;&nbsp;&nbsp;Working...&nbsp;&nbsp;&nbsp;',
				content: 'Please Wait<br/><br/><center><img src="/public/images/working.gif" border="0"/></center>'
			});
			
			loginDialog = new dijit.Dialog({
				title: 'Login',
				content: '<form id="modalLoginForm"><table border="0"><tr><td>Email:</td><td>'
					+ '<input name="email" type="text"></td></tr>'
					+ '<tr><td>Password:</td><td><input name="passcode" type="password"/></td></tr>'
					+ '<tr><td colspan="2"><input style="cursor:pointer;margin:10px 10px 10px 0;" type="button" value="Log In" onclick="Auth.doModalLogin();"/>&nbsp;'
					+ '<input style="cursor:pointer;" type="button" value="Cancel" onclick="loginDialog.hide();"></td>'
					+ '</tr><tr><td colspan="2">'
					+ '<a href="/web/auth/forgotpassword">Forgot Password?</a></td></tr></table></form>'
			});
	  });
	});

// Set up namespaces

function Auth() {};
function Events() {};
function Billing() {};
function Tracking() {};
function Phone() {};
function Util() {};
function Promotion() {};

// Phone stuff -----------------------------------------------------------------

Phone.checkPhoneConfirmCode = function(validate)
{
	// Check that what they entered matches what we sent them on the phone.
	
	//standardWorkingDialog.show();
	
	// Hide any old errors and send
	dojo.byId('phoneConfirmError').style.display = 'none';
	dojo.byId('phoneConfirmError').innerHTML = '';
	var phone = dojo.byId('phone1').value + dojo.byId('phone2').value + dojo.byId('phone3').value;
	var code = dojo.byId('confirmCode').value;
	var params = '/phone/' + phone + '/code/' + code; 
	
	dojo.xhrGet({
		url: '/ajax/tracking/checkphonecode' + params,
		handleAs: 'json',
		timeout: AJAX_TIMEOUT_SHORT,
		
		load: function(response, args) {
			//standardWorkingDialog.hide();
			
			if (response.status == 'success') {
				if(validate){
					document.getElementById("promo-code").value = '';
					document.getElementById("cardType").disabled = false;
					document.getElementById("cardNumber").disabled = false;
					document.getElementById("month").disabled = false;
					document.getElementById("year").disabled = false;
					document.getElementById("cvv2").disabled = false;
					document.getElementById("billingmodule").style.display = "block";
					Promotion.validatePromo();
				}
				wizardNext();
				
				// TODO: put something here to cache the fact that their phone
				// is already confirmed... in case they jump around or do something else
				// we don't want them to do this twice in a single transaction or even php session
			} else {
				var errorDiv = dojo.byId('phoneConfirmError');
				errorDiv.innerHTML = response.message;
				errorDiv.style.display = '';
			}
		},
		
		error: function(response, args) {
			//standardWorkingDialog.hide();
			
			var errorDialog = new dijit.Dialog({
				title: 'Error',
				content: 'An error has occurred, please try again.'
			});
			errorDialog.show();
		}
	});	
}

Phone.sendPhoneConfirmCode = function()
{
	// Issue a confirmation code to user's phone, then present them
	// a form to enter it and confirm.
	
	// Collect the phone number

	var phone = dojo.byId('phone1').value + dojo.byId('phone2').value + dojo.byId('phone3').value;

	// If any of the characters in the string aren't numbers, reject
	
	if (phone.match(regexpAllNumeric) == null) {

		var errorDialog = new dijit.Dialog({
			title: 'Notice',
			content: 'Phone numbers must contain only digits.'
		});
		errorDialog.show();
		return;
	}
	
	if (phone.length != 10) {

		var errorDialog = new dijit.Dialog({
			title: 'Notice',
			content: 'Not a valid phone number with area code. '
		});
		errorDialog.show();
		return;		
	}
	
	standardWorkingDialog.show();

	var securityCode = 'fakecode';
	dojo.byId('phoneSendError').style.display = 'none'
	dojo.byId('phoneSendError').innerHTML = '';
	// TODO: real security
	
	dojo.xhrGet({
		url: '/ajax/tracking/sendphonecode/phone/' + phone + '/code/' + securityCode,
		timeout: AJAX_TIMEOUT_INFINITY,
		handleAs: 'json',
		
		load: function(response, args) {
			standardWorkingDialog.hide();
			
			if (response.status == 'success') {
				// Set the carrier ID, if that came back
				var cid = dojo.byId('carrierId');
				if (cid != null) { cid.value = response.carrierId; }
				
				dojo.byId('confirmation').style.display = '';
			} else {
				dojo.byId('phoneSendError').innerHTML = response.message;
				dojo.byId('phoneSendError').style.display = '';
				dojo.byId('confirmation').style.display = '';
			}
		},
		
		error: function(response, args) {
			standardWorkingDialog.hide();
			
			var errorDialog = new dijit.Dialog({
				title: 'Error',
				content: 'An error has occurred, please try again.'
			});
			errorDialog.show();
		}
	});
}

// Events stuff ----------------------------------------------------------------

Events.selectedAthletes = new Array();


// Auth stuff ------------------------------------------------------------------

Auth.authenticatedUser = null;

Auth.doModalLogin = function()
{
	// Hide dialog
	
	loginDialog.hide();
	
	// Show working dialog instead
	
	standardWorkingDialog.show();
	
	// Do ajax call to log in
	
	dojo.xhrPost({
		form: 'modalLoginForm',
		url: '/ajax/auth/login',
		handleAs: 'json',
		timeout: AJAX_TIMEOUT_MEDIUM,
		
		load: function(response, args) {
			standardWorkingDialog.hide();
			
			if (response.status == 'success') {
				Auth.authenticatedUser = response.authenticatedUser;
				dojo.byId('notloggedin').innerHTML = 'My Account';
				dojo.byId('credentials').style.display = '';
				
				var userId = dojo.byId('userId');
			
				if (userId != null) {
					userId.value = response.authenticatedUser.id;
				}
			} else {
				var errorDlg = new dijit.Dialog({
					title: 'Error',
					content: 'Login Failed: ' + response.message
				});
				errorDlg.show();
			}
		},
		
		error: function(response, args) {
			standardWorkingDialog.hide();
		}
		
	});
	
}

Auth.myAccountSwitch = function(newLocation)
{
	// This is a total kluge shit fix
	// If they're logged in, go to account page... if not, do pop up
	
	if (Auth.authenticatedUser == null) {
		loginDialog.show();
	} else {
		if (newLocation != null) {
			window.location = '/web/user/account';
		}
	}
}

// Billing stuff ---------------------------------------------------------------

Billing.currentPriceTotal = 0;

Billing.finalizeTransaction = function(formName)
{
	// TODO: any last minute verification?
	
	var txDialog = new dijit.Dialog({
		title: 'Processing Transaction...',
		content: 'Processing your transaction.<br/>'
			+ 'This may take a few moments, do not click back or refresh.<br/>'
			+ '<img src="/public/images/working.gif" border="0"/><br/>'
	});	
		
	txDialog.show();
	
	// Decide whether we need a new user or not.
	// If it's track athlete, always false.. no users for trackers, even if they are an athlete too
	// If be tracked, then we have to see if they're logged in or not.
	
	/*var txType = dojo.byId('transactionType').value;
	
	if ((txType == 'BE_TRACKED') && (Auth.authenticatedUser == null)) {
		// Need new user.
		dojo.byId('isNewUser').value = 'true';
	} else {
		// Don't need new user
		dojo.byId('isNewUser').value = 'false';
	}*/
	
	dojo.xhrPost({
		url: '/ajax/billing/submittransaction',
		handleAs: 'json',
		form: formName,
		timeout: AJAX_TIMEOUT_INFINITY,
		
		load: function(response, args) {
			txDialog.hide();
			
			if (response.status == 'success') {
				// Transaction was processed, push to receipt page
				successUrl = '/web/billing/receipt/invoiceId/' + response.invoiceId;
				window.location = successUrl;
			} else {
				// Transaction failed... give them some reason, maybe allow them to
				// go back and edit form?
				
				var errorDialog = new dijit.Dialog({
					title: 'Payment Failed.',
					content: 'Transaction failed: ' + response.message + '<br/>'
							+ 'Please click "Edit Billing Information" and confirm your information.<br/>'
				});
				
				errorDialog.setAttribute('id', 'errorDialog');
				errorDialog.show();

			}
		},

		error: function(response, args) {
			txDialog.hide();
			var errorDialog = new dijit.Dialog({
				title: 'Error.',
				content: 'An error occurred: ' + response.message + '<br/>'
						+ 'Please try again.<br/>'
			});
			
			errorDialog.setAttribute('id', 'errorDialog');
			errorDialog.show();
		}
	});
}

Billing.validateTrackAthleteBillingForm = function() {
	var firstName = dojo.byId('fname').value;
	var lastName = dojo.byId('lname').value;
	var email = dojo.byId('email').value;
	var zip = dojo.byId('zip').value;
	var gender = dojo.byId('gender').value;
	var cardType = dojo.byId('cardType').value;
	var cardNumber = dojo.byId('cardNumber').value;
	var cardExpMonth = dojo.byId('month').value;
	var cardExpYear = dojo.byId('year').value;
	var cardCVV2 = dojo.byId('cvv2').value;
	var agreed = dojo.byId('agreedToTerms').checked;
	
	var errors = '';
	
	if ((firstName == null) || (firstName == '')) { errors += 'Please enter a first name.<br/>';	}
	if ((lastName == null) || (lastName == '')) { errors += 'Please enter a last name.<br/>'; }
	if (email.match(regexpEmail) == null) { errors += 'Please enter a valid email address.<br/>'; }
	if ((gender == null) || (gender == '')) { errors += 'Please select a gender.<br/>'; }		
	if ((zip.match(regexpZipUS) == null) && (zip.match(regexpZipCA) == null)) { errors += 'Please enter a valid zip code.<br/>'; }
	
	if (Billing.currentPriceTotal != 0) {
		if ((cardType == null) || (cardType == '')) { errors += 'Please select a credit card type.<br/>'; }
		
		if ((cardCVV2 == null) || (cardCVV2 == '') || (cardCVV2.match(regexpAllNumeric) == null)) { 
			errors += 'Please enter security code from back of card.<br/>'; 
		}
		
		if ((cardExpMonth == null) || (cardExpYear == '')) { errors += 'Please set the card expiration month.<br/>'; }
		if ((cardExpYear == null) || (cardExpYear == '')) { errors += 'Please set the card expiration year.<br/>'; }
		
	
		if (!Billing.isValidCreditCardNumber(cardNumber, cardType)) {
			errors += 'Please enter a valid credit card number.';
		}
	}
	
	if (agreed == false) { errors += 'You must agree to the Terms of Service.'; }
	
	return errors;
}


Billing.submitTrackAthleteBillingInfo = function(method)
{
	// Validate all billing info (if necessary) and push along to appropriate billing
	// system, or report error for user to correct.
	
	// method will be PAYPAL_EXPRESS or PAYPAL_CC
	// PAYPAL_EXPRESS does not require filling out the form, hence the EXPRESS
	
	if (method == 'PAYPAL_CC') {
		// Collect all fields

		var errors = Billing.validateTrackAthleteBillingForm();
				
		if (errors.length > 0) {
			// If the form doesn't validate, show message and return.
			// DO NOT send for processing.
			
			var errorDialog = new dijit.Dialog({
				title: 'Notice',
				content: errors
			});
			
			errorDialog.show();
			return;
		}
	}
	
	// Either is an express, and no validation needed, OR passed validation.	
	
	// Clear out any old ones
	var parent = dojo.byId('trackAthleteBillingForm');
	var child = dojo.byId('trackedAthletes');
	parent.removeChild(child);
	var holder = document.createElement('div');
	holder.id = 'trackedAthletes';
	parent.appendChild(holder);
	
	// Add tracked athletes to form.
	
	var formContainer = dojo.byId('trackAthleteBillingForm');
	
	for (var i = 0; i < Events.selectedAthletes.length; i++) {
		var athlete = document.createElement('input');
		athlete.setAttribute('type', 'hidden');
		athlete.setAttribute('name', 'trackedAthletes[]');
		athlete.setAttribute('value', Events.selectedAthletes[i]);
		holder.appendChild(athlete);
	}
	
	if (method == 'PAYPAL_CC') {
		wizardNext();
	} else {
		// TODO: finish (push to paypal cc or paypal express)
	}
}

Billing.isValidCreditCardNumber = function(cardNumber, cardType)
{
	// This only ensures that it's a valid card number, the basic checksum.
	// It does not match anything against the other card info.  It's a good
	// baseline front-end check, but will need full verification from PayPal.
	// Taken from: http://www.evolt.org/node/24700

	var isValid = false;
	var ccCheckRegExp = /[^\d ]/;
	isValid = !ccCheckRegExp.test(cardNumber);

	if (isValid)
	{
		var cardNumbersOnly = cardNumber.replace(/ /g, "");
		var cardNumberLength = cardNumbersOnly.length;
		var lengthIsValid = false;
		var prefixIsValid = false;
		var prefixRegExp;

		switch(cardType)
		{
			case "MasterCard": {
				lengthIsValid = (cardNumberLength == 16);
				prefixRegExp = /^5[1-5]/;
		    	break;
		    }

			case "Visa": {
				lengthIsValid = (cardNumberLength == 16 || cardNumberLength == 13);
				prefixRegExp = /^4/;
				break;
			}
			
			case "Amex": {
				lengthIsValid = (cardNumberLength == 15);
				prefixRegExp = /^3(4|7)/;
				break;
			}
			
			default: {
				prefixRegExp = /^$/;
				return false;
			}
		}

		prefixIsValid = prefixRegExp.test(cardNumbersOnly);
		isValid = prefixIsValid && lengthIsValid;
	}

	if (isValid)
	{
		var numberProduct;
		var numberProductDigitIndex;
		var checkSumTotal = 0;

		for (digitCounter = cardNumberLength - 1; digitCounter >= 0; digitCounter--)
		{
			checkSumTotal += parseInt (cardNumbersOnly.charAt(digitCounter));
			digitCounter--;
			numberProduct = String((cardNumbersOnly.charAt(digitCounter) * 2));
			
			for (var productDigitCounter = 0; productDigitCounter < numberProduct.length; productDigitCounter++)
			{
				checkSumTotal += parseInt(numberProduct.charAt(productDigitCounter));
			}
		}

    	isValid = (checkSumTotal % 10 == 0);
	}

	return isValid;
}


Billing.validateBeTrackedBillingForm = function()
{
	// TODO: on form reporting rather than pop-up.
	
	var firstName = dojo.byId('fname').value;
	var lastName = dojo.byId('lname').value;
	var zip = dojo.byId('zip').value;
	var gender = dojo.byId('gender').value;
	var email1 = dojo.byId('email').value;
	var email2 = dojo.byId('email2').value;
	var pass1 = dojo.byId('pass1').value;
	var pass2 = dojo.byId('pass2').value;
	var cardType = dojo.byId('cardType').value;
	var cardNumber = dojo.byId('cardNumber').value;
	var cardExpMonth = dojo.byId('month').value;
	var cardExpYear = dojo.byId('year').value;
	var cardCVV2 = dojo.byId('cvv2').value;
	var agreed = dojo.byId('agreedToTerms').checked;
	
	var errors = '';
	
	if ((firstName == null) || (firstName == '')) { errors += 'Please enter first name.<br/>'; }
	if ((lastName == null) || (lastName == '')) { errors += 'Please enter a last name.<br/>'; }
	
	if (Auth.authenticatedUser == null) {	
		if (email1.match(regexpEmail) == null) { errors += 'Please enter a valid email address.<br/>'; }
	}
	
	if (email1 != email2) { errors += 'Email addresses must match.<br/>'; }
	if ((gender == null) || (gender == '')) { errors += 'Please select a gender.<br/>'; }		
	
	
	// Only do these checks if there is an actual charge... if only promos, all free, no card check.
	
	if (Billing.currentPriceTotal != 0) {
	
		if ((cardType == null) || (cardType == '')) { errors += 'Please select a credit card type.<br/>'; } 
		
		if ((cardCVV2 == null) || (cardCVV2 == '') || (cardCVV2.match(regexpAllNumeric) == null)) { 
			errors += 'Please enter security code from back of card.<br/>'; 
		}
		
		if ((cardExpMonth == null) || (cardExpYear == '')) { errors += 'Please set the card expiration month.<br/>'; }
		if ((cardExpYear == null) || (cardExpYear == '')) { errors += 'Please set the card expiration year.<br/>'; }
		if ((zip.match(regexpZipUS) == null) && (zip.match(regexpZipCA) == null)) { errors += 'Please enter a valid zip code.<br/>'; }
	
		if (!Billing.isValidCreditCardNumber(cardNumber, cardType)) {
			errors += 'Please enter a valid credit card number.';
		}
	}
	
	// Only check password if NOT already logged in as an authenticated user.
	if (Auth.authenticatedUser == null) {	
		if (!Util.isAcceptablePassword(pass1)) { errors += 'Passwords must be at least 6 characters in length.<br/>'; }	
		if (pass1 != pass2) { errors += 'Passwords must match.<br/>'; }
	}
	if (agreed == false) { errors += 'You must agree to the Terms of Service.<br/>'; }	
	
	return errors;
}

Billing.submitBeTrackedBillingInfo = function(method)
{
	// Validate all billing info (if necessary) and push along to appropriate billing
	// system, or report error for user to correct.
	
	// method will be PAYPAL_EXPRESS or PAYPAL_CC
	// PAYPAL_EXPRESS does not require filling out the form, hence the EXPRESS
	
	if (method == 'PAYPAL_CC') {
		// Collect all fields

		var errors = Billing.validateBeTrackedBillingForm();
				
		if (errors.length > 0) {
			// If the form doesn't validate, show message and return.
			// DO NOT send for processing.
			
			var errorDialog = new dijit.Dialog({
				title: 'Notice',
				content: errors
			});
			
			errorDialog.show();
			return;
		}
	}
	
	// Make sure they aren't trying to add a duplicate account
	
	if (Auth.authenticatedUser == null) {
		standardWorkingDialog.show();
		var ajaxUrl = '/ajax/auth/emailexists/email/' + dojo.byId('email').value;
		
		dojo.xhrGet({
			url: ajaxUrl,
			timeout: AJAX_TIMEOUT_MEDIUM,
			handleAs: 'json',
			
			load: function(response, args) {
				standardWorkingDialog.hide();
				
				if (response.status == 'success') {
					if (response.duplicate == true) {
						var dupeDlg = new dijit.Dialog({
							title: 'Error',
							content: 'An account with this email address exists in the system. '
								+ 'Please click "Login" above or select another email address.'
						});
						dupeDlg.show();
					} else {
						// We're good, move allong
						//passedbeTrackedPage3();
						document.forms['beTrackedBillingForm'].submit();
					}
				} else {
					errDlg = new dijit.Dialog({
						title: 'Error',
						content: 'Request failed: ' + response.message
					});
					errDlg.show();
				}
			},
			
			error: function(response, args) {
				standardWorkingDialog.hide();
				errDlg = new dijit.Dialog({
					title: 'Error',
					content: 'An error has occurred: ' + response
				});
				errDlg.show();
			}
		});
	} else {
		// Already logged in.
		//passedbeTrackedPage3();
		document.forms['beTrackedBillingForm'].submit();
	}
}

function passedbeTrackedPage3() 
{	
	// Either is an express, and no validation needed, OR passed validation.	
	// Add vouchers to form so submission includes them

	// First kill any that are in there from bouncing around wizard steps.
	var parent = dojo.byId('beTrackedBillingForm');
	var child = dojo.byId('vouchers');
	parent.removeChild(child);
	
	var holder = document.createElement('div');
	holder.id = 'vouchers';
	parent.appendChild(holder);
	
	for (var i = 0; i < Tracking.selectedVouchers.length; i++) {
		var voucher = document.createElement('input');
		voucher.type = 'hidden';
		voucher.name = 'vouchers[]';
		var itemId = 'voucher#eid#' + Tracking.selectedVouchers[i].eventId
			+ '#pid#' + Tracking.selectedVouchers[i].personId
			+ '#num#' + Tracking.selectedVouchers[i].id;
		var email = dojo.byId(itemId).value;
		voucher.value = itemId + '#email#' + email;
		holder.appendChild(voucher);
	}
	
	for (var i = 0; i < Tracking.promoVouchers.length; i++) {
		var voucher = document.createElement('input');
		voucher.type = 'hidden';
		voucher.name = 'vouchers[]';
		var itemId = 'promotr' 
			+ '#promoId#' + Tracking.promoVouchers[i].promoId
			+ '#eid#' + Tracking.promoVouchers[i].eventId
			+ '#pid#' + Tracking.promoVouchers[i].personId
			+ '#num#' + Tracking.promoVouchers[i].id;
		var email = dojo.byId(itemId).value;
		voucher.value = itemId + '#email#' + email;
		holder.appendChild(voucher); 
	}

	//wizardNext();
	
}

Billing.calculatePrice = function(selectedAthletes, selectedVouchers, promoValue, socialPosting, Method)
{
	// Count how many athletes selected, pass as params.
	
	var params = '';
	
	if (selectedAthletes != null) {
		params += '/trackingCount/' + selectedAthletes.length;
	}
	
	if (selectedVouchers != null) {
		params += '/voucherCount/' + selectedVouchers.length;
	}
	
	if (promoValue != null) {
		params += '/promotionCount/' + promoValue;
	}
	
	if(socialPosting != null){
		params += '/social/' + socialPosting;
	}
	
	dojo.xhrGet({
		url: '/ajax/billing/calculatetotal' + params,
		timeout: AJAX_TIMEOUT_SHORT,
		handleAs: 'json',
		
		load: function(response, args) {
			
			if (response.status == 'success') {
				Billing.currentPriceTotal = response.totalPrice;
				if (Method != null) {
					Method(response.totalPrice);
				}
			} else {

				var errorDialog = new dijit.Dialog({
					title: 'Error',
					content: 'An error has occurred (' + response.message + '), please try again.'
				});
				errorDialog.show();
			}
		},
		
		error: function(response, args) {
			var errorDialog = new dijit.Dialog({
				title: 'Error',
				content: 'A calculate price error has occurred, please try again.' + response.message 
			});
			errorDialog.show();
		}
	});
}

// Tracking stuff --------------------------------------------------------------

Tracking.defaultVoucherCount = 5;
Tracking.defaultVoucherGroupSize = 5;
Tracking.selectedVouchers = new Array();
Tracking.promoVouchers = new Array();
Tracking.athleteLookups = new Array();

Tracking.createAthleteBlock = function(name, location, personId, eventId, selected, eventLabel)
{
	// Create a reusable athlete block for the athlete search page, step 1 of Track AThlete workflow.

	var divId = 'eid_' + eventId + '_pid_' + personId;
	var blockDiv = document.createElement('div');

	blockDiv.className = 'athlete hand';
	blockDiv.personId = personId;
	blockDiv.eventId = eventId;
	blockDiv.id = divId;
	blockDiv.onclick = function() { Tracking.toggleAthleteSelected(eventId, personId); };

	var iconDiv = document.createElement('div');
	iconDiv.setAttribute('class', 'icon');

	var imgTag = document.createElement('img');
	imgTag.width = 15;
	imgTag.height = 15;
	imgTag.border = 0;
	imgTag.className = 'hand';
	imgTag.id = 'athleteBlockIcon_' + divId;
	
	if (selected == true) {
		blockDiv.selected = 'true';
		imgTag.src = '/public/images/buttons/cancel.png';
	} else {
		blockDiv.selected = 'false';
		imgTag.src = '/public/images/buttons/plus.png';
	}

	iconDiv.appendChild(imgTag);
	blockDiv.appendChild(iconDiv);
	
	var nameDiv = document.createElement('div');
	nameDiv.className = 'name';
	nameDiv.innerHTML = name;
	blockDiv.appendChild(nameDiv);

	var locDiv = document.createElement('div');
	locDiv.className = 'city';
	locDiv.innerHTML = location;
	blockDiv.appendChild(locDiv);
		
	var eventDiv = document.createElement('div');
	eventDiv.className = 'event';
	eventDiv.innerHTML = eventLabel;
	blockDiv.appendChild(eventDiv);

	var clearDiv = document.createElement('div');
	clearDiv.className = 'brclear';
	blockDiv.appendChild(clearDiv);
	
	// If it doesn't already exist, create an athlete lookup, so we can come back
	// to it later.  This is a kludge fix to handle the confirm page.
	// TODO: better implementation.. make Events.selectedAthletes better.
	
	var event = new Array();
	event['name'] = name;
	event['location'] = location;
	event['personId'] = personId;
	event['eventId'] = eventId;
	event['eventLabel'] = eventLabel;  
	Tracking.athleteLookups[divId] = event;
	
	return blockDiv;
}

Tracking.ajaxAthleteSearch = function()
{
	// Go get new results
	
	var theSpace = " "; 
	var eventId = dojo.byId("eventId").value;
	eventId = eventId.replace(/[^0-9]/g, "");
	var athleteName = dojo.byId("searchAthleteName").value;

	athleteName = athleteName.replace('"', '');
	athleteName = athleteName.replace("'", '');
	athleteName = athleteName.replace(/[^A-Za-z0-9- ]/g, "");
	dojo.byId("searchAthleteName").value = athleteName; 
	
	if ((eventId == null) || (eventId == '')) {
		
		var dlg = new dijit.Dialog({
			title: 'Notice',
			content: 'Please select an event.'
		});
		dlg.show();

	} else if ((athleteName == null) || (athleteName == '')) {

		var dlg = new dijit.Dialog({
			title: 'Notice',
			content: 'Please enter an athlete name.'
		});
		dlg.show();

	} else if (athleteName.trim().indexOf(theSpace) == -1) {

		dojo.byId("searchAthleteName").value = '';
		
		var dlg = new dijit.Dialog({
			title: 'Notice',
			content: 'Please input the First Name and Last Name separated by a space.'
		});
		dlg.show();
				
	} else { 
	
	
	// AJAX refresh of athlete search, repopulates page with new results.
	
	standardWorkingDialog.show();
	
	// Remove old results

	var resultsDiv = dojo.byId("metoo");
	var athletes = resultsDiv.getElementsByTagName("div");
	resultsDiv.innerHTML = '';

	
	dojo.xhrPost({
		url: '/ajax/events/athletesearch/eventId/' + eventId + '/athleteName/' + athleteName,
		handleAs: 'json',
		timeout: AJAX_TIMEOUT_LONG,
		
		load: function(response, args) {
			standardWorkingDialog.hide();
			
			if (response.status == 'success') {
				var colDiv = dojo.byId('metoo');
				//$GLOBALS['logger']->err("colDiv is : ".colDiv);	
				
				for (var i = 0; i < response.athletes.length; i++) {
				
					// Only insert into list if NOT in already selected list
					
					var ath = response.athletes[i];
					var divId = 'eid_' + ath.eventId + '_pid_' + ath.personId;
					var exists = dojo.byId(divId);

					if (exists == undefined) {	
						var block = Tracking.createAthleteBlock(ath.firstName + ' ' + ath.lastName,
										ath.location, ath.personId, ath.eventId, 'false', 
										response.events[ath.eventId].name);

						colDiv.appendChild(block);
					}
				}
			} else {
				
				var errorDialog = new dijit.Dialog({
					title: 'Error',
					content: 'An error has occurred (' + response.message + '), please try again.'
				});
				errorDialog.show();
			} 	
		},
		
		error: function(response, args) {
			standardWorkingDialog.hide();
			
			var errorDialog = new dijit.Dialog({
				title: 'Error',
				content: 'An error has occurred, please try again. ' + response
			});
			errorDialog.show();
		}
	});
	
	}
	
}


Tracking.createVoucherGroupHeaderBlock = function(groupNum)
{
	// Create divider between groups on Be Tracked step 2.
	
	var groupDiv = document.createElement('div');
	groupDiv.setAttribute('class', 'radiobtn');
	groupDiv.setAttribute('style', 'border-bottom:#999 solid 1px; margin-bottom:5px; width: 90%; float: left; clear: both;');
	groupDiv.setAttribute('id', 'divider_' + groupNum);
	groupDiv.innerHTML = 'Group ' + groupNum;
	return groupDiv;
}

Tracking.createVoucherBlock = function(id)
{
	// Create a voucher form element on Be Tracked page 2.
	
	var slot = document.createElement('input');
	slot.setAttribute('type', 'text');
	slot.className = 'specialoffer';
	slot.setAttribute('name', id);
	slot.setAttribute('id', id);	
	return slot;
}

Tracking.updateVoucherCount = function()
{
	// When user selects a different number of "packs/groups" of trackers to prepay,
	// adjust the form and the price.
	
	standardWorkingDialog.show();
	var packCount = dojo.byId('trackerPackCount').value;
	// Adjust tracking slots on page
	
	var newVoucherCount = packCount * Tracking.defaultVoucherGroupSize; 
	var oldVoucherCount = Tracking.selectedVouchers.length;
	// If there are more vouchers than before, add them
	// If there are fewer, remove them... this is tricky
	
	if (newVoucherCount > oldVoucherCount) {
		// Simply add new blocks
		var container = dojo.byId('voucherList');
			
		for (var i = (oldVoucherCount); i < newVoucherCount; i++) {
			// If we are on a new group, add a divider block

			if ((i % Tracking.defaultVoucherGroupSize) == 0) {
				var divider = Tracking.createVoucherGroupHeaderBlock((i / Tracking.defaultVoucherGroupSize) + 1);
				container.appendChild(divider);
			}
			
			var itemId = 'voucher#eid#' + selectedEventId + '#pid#' + selectedPersonId + '#num#' + (i + 1);
			var voucher = Tracking.createVoucherBlock(itemId);
			container.appendChild(voucher);
			
			var voucher = Array();
			voucher['personId'] = selectedPersonId;
			voucher['eventId'] = selectedEventId;
			voucher['id'] = (i + 1);
			Tracking.selectedVouchers.push(voucher);
		}
		
	} else if (newVoucherCount < oldVoucherCount) {
	
		// Pull out and destroy any over the count.
		// Also destroy any dividers

		var container = dojo.byId('voucherList');
		
		for (var i = (newVoucherCount + 1); i <= oldVoucherCount; i++) {
			var itemId = 'voucher#eid#' + selectedEventId + '#pid#' + selectedPersonId + '#num#' + i;
			var voucher = dojo.byId(itemId);
			container.removeChild(voucher);

			// Remove from selectedVouchers list
			
			Tracking.selectedVouchers.splice(newVoucherCount, oldVoucherCount - newVoucherCount + 1);

			// Also destroy divider
			
			if (((i - 1) % Tracking.defaultVoucherGroupSize) == 0) {
				var divider = dojo.byId('divider_' + Math.floor((i / Tracking.defaultVoucherGroupSize) + 1));
				container.removeChild(divider);
			}
		}
	}
	
	// Recalculate price
	
	Billing.calculatePrice(null, Tracking.selectedVouchers, null, null, Tracking.updatePriceLabel);	
	standardWorkingDialog.hide();
}

Tracking.showParticipantDetails = function(personId, eventId)
{
	// On Be Tracked step 1, after an athlete record is selected, show the details
	// and provide user the confirmation button to move on.

	selectedEventId = eventId;
	selectedPersonId = personId;
	
	// Go through our list of results and pull up the full details
	
	for (var i = 0; i < athleteResults.length; i++) {
		if ((athleteResults[i].personId == personId) && (athleteResults[i].eventId == eventId)) {
			
			// Inject all that info into the details page, then turn it on.
			
			dojo.byId('confirmName').innerHTML = athleteResults[i].firstName + ' ' + athleteResults[i].lastName;
			dojo.byId('confirmAge').innerHTML = athleteResults[i].age;
			dojo.byId('confirmRegLastFour').innerHTML 
				= athleteResults[i].registrationCode.substring(athleteResults[i].registrationCode.length - 4);

			for (var j = 0; j < eventResults.length; j++) {
				if (athleteResults[i].eventId == eventResults[j].id) {
					dojo.byId('confirmEventName').innerHTML = eventResults[j].name;
					break;
				}
			}

			// TODO: additionally will want to set things for latter steps up here.
			dojo.byId("selectedPersonId").value = selectedPersonId;
			
			dojo.byId('detailsColumn').style.display = '';
			break;
		}
	}
}


Tracking.toggleAthleteSelected = function(eventId, personId)
{
	// Move an athlete block from the Found-in-Search column
	// to the Selected-for-Tracking column, or vice-versa.

	standardWorkingDialog.show();

	// Find the DOM object first, find out which column it's in.
	
	var divId = 'eid_' + eventId + '_pid_' + personId;

	var athleteDiv = dojo.byId(divId);

	if (athleteDiv != null) {

		var isSelected = athleteDiv.getAttribute('selected');
	
		// Remove from parent
	
		var iconImgTag = dojo.byId('athleteBlockIcon_' + divId);
		athleteDiv.parentNode.removeChild(athleteDiv);
		
		// Change parameters
		
		if (isSelected == 'true') {
			// Need to find and remove from selected athlete list
			
			var idx = -1;
			for (var j = 0; j < Events.selectedAthletes.length; j++) {
				if (divId == Events.selectedAthletes[j]) { idx = j; break; }
			}
		
			if (idx != -1) {
				Events.selectedAthletes.splice(idx, 1);
			}
		
			var newHomeDiv = 'metoo';
			iconImgTag.setAttribute('src', '/public/images/buttons/plus.png');
			athleteDiv.setAttribute('selected', 'false');
			
		} else {
			// Add to selected athlete list
			
			Events.selectedAthletes.push(divId);
			var newHomeDiv = 'trackedmetoo';
			iconImgTag.setAttribute('src', '/public/images/buttons/cancel.png');
			athleteDiv.setAttribute('selected', 'true');
		}
		
		// Add to new home
		
		var newHome = dojo.byId(newHomeDiv);
		newHome.appendChild(athleteDiv);

		
		// If the number of selected athletes > 0, turn on continue button else disable
		
		var continueImg = dojo.byId('wizardPage1ContinueImage');
		var continueImg1 = dojo.byId('wizardPage1ContinueImage-bottom');
		
		if (Events.selectedAthletes.length > 0) {
			continueImg.src = '/public/images/buttons/continue.png';
			continueImg.onclick = wizardNext;
			continueImg1.src = '/public/images/buttons/continue.png';
			continueImg1.onclick = wizardNext;
		} else {
			continueImg.src = '/public/images/buttons/continue-off.png';
			continueImg.onclick = null;
			continueImg1.src = '/public/images/buttons/continue-off.png';
			continueImg1.onclick = null;
		}
		
		Billing.calculatePrice(Events.selectedAthletes, null, null, null, Tracking.updatePriceLabel);
		
	}
	standardWorkingDialog.hide();
}


Tracking.updatePriceLabel = function(price)
{
	dojo.byId('total').innerHTML = '$' + price.toFixed(2);
	dojo.byId('total-bottom').innerHTML = '$' + price.toFixed(2);
}

Tracking.saveVouchers = function (formName)
{
	var txDialog = new dijit.Dialog({
		title: 'Processing Transaction...',
		content: 'This may take a few moments, do not click back or refresh.<br/>'
			+ '<img src="/public/images/working.gif" border="0"/><br/>'
	});	
		
	txDialog.show();
	
	dojo.xhrPost({
		url: '/ajax/tracking/savevouchers',
		form: formName,
		handleAs: 'json',
		timeout: AJAX_TIMEOUT_INFINITY,
		
		load: function(response, args) {
			
			
			if (response.status == 'success') {

			} else {
				// Transaction failed... give them some reason, maybe allow them to
				// go back and edit form?
				
				var errorDialog = new dijit.Dialog({
					title: 'Payment Failed.',
					content: 'Transaction failed: ' + response.message + '<br/>'
							+ 'Please click "Edit Billing Information" and confirm your information.<br/>'
				});
				
				errorDialog.setAttribute('id', 'errorDialog');
				errorDialog.show();

			}
			txDialog.hide();
		},

		error: function(response, args) {
			txDialog.hide();
			var errorDialog = new dijit.Dialog({
				title: 'Error.',
				content: 'An error occurred: ' + response.message + '<br/>'
						+ 'Please try again.<br/>'
			});
			
			errorDialog.setAttribute('id', 'errorDialog');
			errorDialog.show();
		}
	});


}

// Other stuff -----------------------------------------------------------------

Util.moveCursor = function(textbox, nextElementId)
{
	if (textbox.value.length == textbox.getAttribute("maxlength")) {
		var nextElem = dojo.byId(nextElementId);
		nextElem.focus();
	}
}

Util.isAcceptablePassword = function(password)
{
	if (password.length < PASSWORD_MINIMUM_LENGTH) {
		return false;
	} 
	return true;
}

Promotion.currentPromosTotal = 0;

Promotion.validateParams = function(){
	//var email = encodeURI(document.getElementById("email").value);
	var promoCode = document.getElementById("promo-code").value;
	var eventId = document.getElementById("eventId").value;
	/*if(email == ''){
		var errorDialog = new dijit.Dialog({
			title: 'Error',
			content: 'You must input a valid email address before recalculating the price.'
		});
		errorDialog.show();
	}
	else*/ 
	if(promoCode == ''){
		var errorDialog = new dijit.Dialog({
			title: 'Error',
			content: 'You must input a valid promotion code before recalculating the price.'
		});
		errorDialog.show();
	}
	else{	
		Promotion.validatePromo(eventId);
	}
}

Promotion.validatePromo = function(eventId){
	standardWorkingDialog.show();
	//var email = encodeURI(document.getElementById("email").value);
	var promoCode = document.getElementById("promo-code").value;
	var eventId = document.getElementById("eventId").value;
	if(document.getElementById("trackAthleteBillingForm")){
		trackerType = "spectator";
	}
	else{
		trackerType = "runner";
	}
	var params = '';
	//if(email != '' && promoCode != ''){
	if(promoCode != ''){
		//params += '/promoCode/' + promoCode + '/email/' + email;
		params += '/promoCode/' + promoCode + '/trackerType/' + trackerType;
		dojo.xhrGet({
			url: '/ajax/promotion/validatepromocode' + params,
			timeout: AJAX_TIMEOUT_INFINITY,
			handleAs: 'json',
			
			load: function(response, args) {
				standardWorkingDialog.hide();
				if (response.status == 'success') {
					var validPromo = response.valid;
					var promoValue = 0;
					if (validPromo.value > 0) {
						promoValue = validPromo.value;
						Promotion.setPromoClaimed(validPromo.id, eventId, promoValue);
						Promotion.currentPromosTotal += 1;
						var successDialog = new dijit.Dialog({
							title: 'Success',
							content: 'Your promotion code has been successfully redeemed.'
						});
						successDialog.show();
						document.getElementById("promo-code").disabled = true;
						document.getElementById("promotion-update").style.display="none";
					}
					Billing.calculatePrice(
							Events.selectedAthletes, 
							Tracking.selectedVouchers, 
							promoValue,
							null,
							function(price){
								document.getElementById('total-billing').innerHTML = '$' + price.toFixed(2);
								if(price == 0){
									document.getElementById("cardType").disabled = true;
									document.getElementById("cardNumber").disabled = true;
									document.getElementById("month").disabled = true;
									document.getElementById("year").disabled = true;
									document.getElementById("cvv2").disabled = true;
									if(document.getElementById("billingInfoSection")){
										document.getElementById("billingInfoSection").style.display = "none";
									}
									if(document.getElementById("billingmodule")){
										document.getElementById("billingmodule").style.display ="none";
									}
								}
							});
				} else {
	
					var errorDialog = new dijit.Dialog({
						title: 'Error',
						content: 'An error has occurred (' + response.valid + '), please try again.'
					});
					errorDialog.show();
				}
			},
			
			error: function(response, args) {
				standardWorkingDialog.hide();
				var errorDialog = new dijit.Dialog({
					title: 'Error',
					content: 'A validate promo error has occurred, please try again.'
				});
				errorDialog.show();
			}
		});
	}
	else{
		standardWorkingDialog.hide();
		var promosArray = new Array();
		for( var i = 0; i< Promotion.currentPromosTotal; i++){
			promosArray[i] = i;
		}
		Billing.calculatePrice(
				Events.selectedAthletes, 
				Tracking.selectedVouchers, 
				promosArray,
				null,
				function(price){
					document.getElementById('total-billing').innerHTML = '$' + price.toFixed(2);
					if(price == 0){
						document.getElementById("cardType").disabled = true;
						document.getElementById("cardNumber").disabled = true;
						document.getElementById("month").disabled = true;
						document.getElementById("year").disabled = true;
						document.getElementById("cvv2").disabled = true;
						document.getElementById("billingmodule").style.display = "none";
					}
				});
		
	}
	//document.getElementById("promo-code").value= '';
}

Promotion.setPromoClaimed = function(promoId, eventId, promoValue){
	var params = '';
	//var eventId = document.getElementById("searchEventId").value;
	params += '/promoCode/' + promoId + '/eventid/' + eventId;
	
	var ajaxUrl = '/ajax/promotion/promoclaimed' + params;
	
	dojo.xhrPost({
		url: ajaxUrl,
		timeout: AJAX_TIMEOUT_INFINITY,
		handleAs: 'json',
		
		load: function(response, args) {
			standardWorkingDialog.hide();
			
			if (response.status == 'success') {
				var promoCodesDiv = document.getElementById("promo-codes-used");
				var promoCode = document.createElement('input');
				promoCode.setAttribute('type', 'hidden');
				promoCode.setAttribute('name', 'promo_codes_used');
				promoCode.setAttribute('id', 'promo_codes_used');
				promoCode.setAttribute('value', response.promoCode + '-' + promoValue);
				promoCodesDiv.appendChild(promoCode);
			} else {
				errDlg = new dijit.Dialog({
					title: 'Error',
					content: 'Request failed: ' + response.message
				});
				errDlg.show();
			}
		},
		
		error: function(response, args) {
			standardWorkingDialog.hide();
			errDlg = new dijit.Dialog({
				title: 'Error',
				content: 'An error has occurred: ' + response
			});
			errDlg.show();
		}
	});
}