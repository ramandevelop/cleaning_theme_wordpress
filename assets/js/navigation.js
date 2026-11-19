(function () {
	'use strict';

	var toggle = document.querySelector('.bn-menu-toggle');
	var navigation = document.querySelector('#primary-navigation');

	if (!toggle || !navigation) {
		return;
	}

	toggle.addEventListener('click', function () {
		var isOpen = toggle.getAttribute('aria-expanded') === 'true';
		toggle.setAttribute('aria-expanded', String(!isOpen));
		toggle.setAttribute(
			'aria-label',
			isOpen ? toggle.getAttribute('data-label-open') : toggle.getAttribute('data-label-close')
		);
		navigation.classList.toggle('is-open', !isOpen);
	});

	navigation.addEventListener('click', function (event) {
		if (event.target.closest('a')) {
			toggle.setAttribute('aria-expanded', 'false');
			toggle.setAttribute('aria-label', toggle.getAttribute('data-label-open'));
			navigation.classList.remove('is-open');
		}
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && navigation.classList.contains('is-open')) {
			toggle.setAttribute('aria-expanded', 'false');
			toggle.setAttribute('aria-label', toggle.getAttribute('data-label-open'));
			navigation.classList.remove('is-open');
			toggle.focus();
		}
	});
})();