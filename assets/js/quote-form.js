(function () {
	'use strict';

	var forms = document.querySelectorAll('.bn-quote-form');

	Array.prototype.forEach.call(forms, function (form) {
		var steps = Array.prototype.slice.call(form.querySelectorAll('[data-quote-step]'));
		var progress = Array.prototype.slice.call(form.querySelectorAll('[data-quote-progress]'));
		var status = form.querySelector('[data-quote-status]');
		var backButton = form.querySelector('[data-quote-back]');
		var nextButton = form.querySelector('[data-quote-next]');
		var submitButton = form.querySelector('[data-quote-submit]');
		var currentStep = 0;

		if (!steps.length || !backButton || !nextButton || !submitButton) {
			return;
		}

		function showStep(index, moveFocus) {
			currentStep = index;
			steps.forEach(function (step, stepIndex) {
				step.hidden = stepIndex !== currentStep;
			});
			progress.forEach(function (item, stepIndex) {
				item.classList.toggle('is-active', stepIndex === currentStep);
				item.classList.toggle('is-complete', stepIndex < currentStep);
				if (stepIndex === currentStep) {
					item.setAttribute('aria-current', 'step');
				} else {
					item.removeAttribute('aria-current');
				}
			});
			backButton.hidden = currentStep === 0;
			nextButton.hidden = currentStep === steps.length - 1;
			submitButton.hidden = currentStep !== steps.length - 1;

			if (status) {
				status.textContent = 'Step ' + (currentStep + 1) + ' of ' + steps.length + ': ' + steps[currentStep].querySelector('legend').textContent;
			}
			if (moveFocus) {
				var heading = steps[currentStep].querySelector('legend');
				heading.setAttribute('tabindex', '-1');
				heading.focus();
			}
		}

		function currentStepIsValid() {
			var fields = steps[currentStep].querySelectorAll('input, select, textarea');
			var firstInvalid = null;

			Array.prototype.forEach.call(fields, function (field) {
				if (field.willValidate && !field.checkValidity() && !firstInvalid) {
					firstInvalid = field;
				}
			});
			if (firstInvalid) {
				firstInvalid.reportValidity();
				firstInvalid.focus();
				return false;
			}
			return true;
		}

		backButton.addEventListener('click', function () {
			if (currentStep > 0) {
				showStep(currentStep - 1, true);
			}
		});

		nextButton.addEventListener('click', function () {
			if (currentStepIsValid() && currentStep < steps.length - 1) {
				showStep(currentStep + 1, true);
			}
		});

		showStep(0, false);
	});
})();
