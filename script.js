function showForm(formName) {
	const loginBox = document.getElementById('login-box');
	const registerBox = document.getElementById('register-box');

	if (formName === 'register') {
		loginBox.classList.add('hidden');
		registerBox.classList.remove('hidden');
	} else {
		registerBox.classList.add('hidden');
		loginBox.classList.remove('hidden');
	}
}