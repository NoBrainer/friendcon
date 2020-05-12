//================================
// CAPTCHA v3

function trackStats(action, onError) {
	onError = onError || console.log;
	grecaptcha.ready(() => { //Ensure that reCAPTCHA is ready
		grecaptcha.execute(captchaSiteV3Key, {action: action}).then((token) => {
			// Verify the token with our API
			$.ajax({
				type: 'POST',
				url: '/members/api-v2/captcha/verify.php',
				data: ["token=" + token, "action=" + action].join('&'),
				statusCode: {
					200: (resp) => {
						console.log(resp.message);
					},
					400: (jqXHR) => {
						const resp = jqXHR.responseJSON;
						onError(resp.error);
					},
					401: (jqXHR) => {
						const resp = jqXHR.responseJSON;
						onError(resp.error);
					}
				}
			});
		});
	});
}

//================================
// Formatting

function formatPhoneNumberOnBlur($input) {
	$input.blur((e) => {
		const $this = $(e.currentTarget);
		const value = ($this.val() || "").trim();
		let formattedPhoneNumber = null;
		let pieces;

		if (pieces = value.match(/^\(\d{3}\)\d{3}\-\d{4}$/)) {
			// Input is already in the right format
			formattedPhoneNumber = pieces[0];
		} else if (pieces = value.match(/^\D*(\d{3})\D*(\d{3})\D*(\d{4})$/)) {
			// Support a 10-digit number and removes some non-numeric characters
			// Example: "123-456-7890" or "123 456-7890" or "1234567890" or "123...456,,,7890"
			formattedPhoneNumber = "(" + pieces[1] + ")" + pieces[2] + "-" + pieces[3];
		} else { // Strip away all non-numeric characters and see if we have 10 numbers
			var strippedValue = value.replace(/\D/g, "");
			if (pieces = strippedValue.match(/^\D*(\d{3})\D*(\d{3})\D*(\d{4})$/)) {
				formattedPhoneNumber = "(" + pieces[1] + ")" + pieces[2] + "-" + pieces[3];
			}
		}

		// If we formatted it, use that value
		if (value !== formattedPhoneNumber) {
			$this.val(formattedPhoneNumber);
		}
	});
}

//================================
// Messaging success/error/info to a div

function clearMessage($message) {
	$message.removeClass('alert alert-danger alert-info alert-success alert-warning').text("");
}

function errorMessage($message, text) {
	$message.addClass('alert alert-danger').text(text);
}

function infoMessage($message, text) {
	$message.addClass('alert alert-info').text(text);
}

function successMessage($message, text) {
	$message.addClass('alert alert-success').text(text);
}

function warnMessage($message, text) {
	$message.addClass('alert alert-warning').text(text);
}

//================================
// Rendering Bootstrap dropdowns
// To use these dropdown, you must have templates for #dropdownOption, #dropdownScaffold, and #dropdownStartValue.

function dropdown(id, label, options, onChange) {
	const $dropdown = $($('#dropdownScaffold').html());
	const $options = $dropdown.find('.dropdown-menu');
	const $button = $dropdown.find('.dropdown-toggle');
	$options.attr('aria-labelledby', id);
	$button.prop('id', id);
	$button.html(dropdownStartValue('Pick ' + label));
	_.each(options, (option) => {
		$options.append(dropdownOption(option.index, option.text, option.toggleBtn));
	});
	setupDropdownHandlers($options, onChange);
	return $dropdown;
}

function dropdownOption(index, text, toggleBtn) {
	const $option = $($('#dropdownOption').html());
	const $link = $option.find('a');
	$link.text(text);
	$link.attr('index', index);
	$link.attr('toggleButton', toggleBtn);
	return $option;
}

function dropdownStartValue(text) {
	const $ele = $($('#dropdownStartValue').html());
	$ele.find('.text').text(text);
	return $ele;
}

function getCurrentDropdownIndex(dropdownId) {
	return parseInt($('#' + dropdownId).attr('index'))
}

function setupDropdownHandlers(optionsEle, onChange) {
	$(optionsEle).find('.dropdown-item').off().click((e) => {
		const $btn = $(e.currentTarget);
		const index = $btn.attr('index');
		const text = $btn.text().trim();
		const $target = $($btn.attr('toggleButton'));
		$target.text(text);
		$target.attr('index', index);
		if (_.isFunction(onChange)) onChange();
	});
}

//================================
// Rendering 'select' content/elements

// Create an 'option' element.
function option(text, value, selected) {
	const $option = $(document.createElement('option'));
	$option.text(text);
	if (!!value || isNumber(value)) $option.prop('value', value);
	if (!!selected) $option.prop('selected', true);
	return $option;
}

// Create a 'select' element.
function select(options, id) {
	const $select = $(document.createElement('select'));
	$select.addClass('custom-select');
	if (!!id) $select.prop('id', id);
	if (_.isArray(options)) {
		_.each(options, (opt) => {
			if (_.isObject(opt) && !(opt instanceof $)) {
				$select.append(option(opt.text, opt.value, opt.selected));
			} else {
				$select.append(opt);
			}
		});
	} else {
		$select.html(options);
	}
	return $select;
}

//================================
// Rendering 'table' content/elements

// Create a 'td' element. (The templating method does not work since the browser deletes td tags outside of a table.)
function td(ele, className, id) {
	const $td = $(document.createElement('td'));
	if (!!ele) $td.html(ele);
	if (!!className) $td.addClass(className);
	if (!!id) $td.prop('id', id);
	return $td;
}

// Create a 'tr' element. (The templating method does not work since the browser deletes tr tags outside of a table.)
function tr(content, className, id) {
	const $tr = $(document.createElement('tr'));
	if (_.isArray(content)) {
		_.each(content, (obj) => {
			if (_.isObject(obj) && !(obj instanceof $)) {
				$tr.append(td(obj.ele, obj.className, obj.id));
			} else {
				$tr.append(td(obj));
			}
		});
	} else if (!!content) {
		$tr.html(content);
	}
	if (!!className) $tr.addClass(className);
	if (!!id) $tr.prop('id', id);
	return $tr;
}

//================================
// Cookies

function getCookie(key) {
	let value = "; " + document.cookie;
	let parts = value.split("; " + key + "=");
	if (parts.length === 2) {
		return parts.pop().split(";").shift();
	}
	return undefined;
}

function setCookie(key, value) {
	if (!key) throw new Error("Invalid key for cookie [" + key + "]");
	if (!_.isString(value)) throw new Error("Invalid value for cookie [" + value + "]");
	document.cookie = key + " = " + value;
}
