// ===== REGISTER FORM VALIDATION =====
const regForm = document.getElementById('regForm');
if (regForm) {
  regForm.addEventListener('submit', function(e) {
    const name    = regForm.querySelector('[name=name]').value.trim();
    const phone   = regForm.querySelector('[name=phone]').value.trim();
    const email   = regForm.querySelector('[name=email]').value.trim();
    const type    = regForm.querySelector('[name=user_type]').value;
    const pass    = regForm.querySelector('[name=password]').value;
    const confirm = regForm.querySelector('[name=confirm_password]').value;
    const errBox  = document.getElementById('js-error');

    function showErr(msg) { errBox.textContent = msg; errBox.style.display = 'block'; e.preventDefault(); }
    errBox.style.display = 'none';

    if (!name)                        return showErr('Full name is required.');
    if (!/^\d{10}$/.test(phone))      return showErr('Enter a valid 10-digit phone number.');
    if (!/\S+@\S+\.\S+/.test(email))  return showErr('Enter a valid email address.');
    if (!type)                        return showErr('Please select a user type.');
    if (pass.length < 6)              return showErr('Password must be at least 6 characters.');
    if (pass !== confirm)             return showErr('Passwords do not match.');
  });
}

// ===== LOGIN FORM VALIDATION =====
const loginForm = document.getElementById('loginForm');
if (loginForm) {
  loginForm.addEventListener('submit', function(e) {
    const email  = loginForm.querySelector('[name=email]').value.trim();
    const pass   = loginForm.querySelector('[name=password]').value;
    const errBox = document.getElementById('js-error');

    function showErr(msg) { errBox.textContent = msg; errBox.style.display = 'block'; e.preventDefault(); }
    errBox.style.display = 'none';

    if (!/\S+@\S+\.\S+/.test(email)) return showErr('Enter a valid email address.');
    if (!pass)                        return showErr('Password is required.');
  });
}
