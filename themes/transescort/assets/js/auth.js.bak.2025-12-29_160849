document.addEventListener('DOMContentLoaded', function () {

  // ---------------------------
  // Tabs (Login / Register)
  // ---------------------------
  const tabs = document.querySelectorAll('.auth-tab');
  const loginForm = document.querySelector('.js-login-form');
  const registerForm = document.querySelector('.js-register-form');

  if (tabs.length && loginForm && registerForm) {
    tabs.forEach(tab => {
      tab.addEventListener('click', function () {
        tabs.forEach(t => t.classList.remove('is-active'));
        tab.classList.add('is-active');

        if (tab.dataset.tab === 'login') {
          loginForm.style.display = 'block';
          registerForm.style.display = 'none';
        }

        if (tab.dataset.tab === 'register') {
          loginForm.style.display = 'none';
          registerForm.style.display = 'block';
        }
      });
    });
  }

  // Helper: parse JSON safely
  async function safeJson(res) {
    const text = await res.text();
    try {
      return JSON.parse(text);
    } catch (e) {
      return { success: false, data: { message: 'Server error. Invalid response.' } };
    }
  }

  // Helper: get message from WP json
  function getMsg(payload, fallback) {
    return (payload && payload.data && payload.data.message) ? payload.data.message : (fallback || 'Error');
  }

  // Helper: get redirect from WP json
  function getRedirect(payload) {
    return (payload && payload.data && payload.data.redirect) ? payload.data.redirect : '';
  }

  // ==========================
  // AJAX Login
  // ==========================
  const loginErrorBox = loginForm ? loginForm.querySelector('.auth-error') : null;

  if (loginForm && loginErrorBox) {
    loginForm.addEventListener('submit', function (e) {
      e.preventDefault();

      loginErrorBox.style.display = 'none';
      loginErrorBox.textContent = '';

      const formData = new FormData(loginForm);
      formData.append('action', 'transescort_login');

      fetch(transescortAuth.ajaxurl, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
      })
        .then(safeJson)
        .then(payload => {
          if (payload.success) {
            const redirect = getRedirect(payload) || '/account/';
            window.location.href = redirect;
            return;
          }

          loginErrorBox.textContent = getMsg(payload, 'Login failed.');
          loginErrorBox.style.display = 'block';
        })
        .catch(() => {
          loginErrorBox.textContent = 'Server error. Please try again.';
          loginErrorBox.style.display = 'block';
        });
    });
  }

  // ==========================
  // AJAX Register
  // ==========================
  const regForm = document.querySelector('.js-register-form');
  const regErrorBox = regForm ? regForm.querySelector('.auth-error') : null;

  if (regForm && regErrorBox) {
    regForm.addEventListener('submit', function (e) {
      e.preventDefault();

      regErrorBox.style.display = 'none';
      regErrorBox.textContent = '';

      const formData = new FormData(regForm);
      formData.append('action', 'transescort_register');

      fetch(transescortAuth.ajaxurl, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
      })
        .then(safeJson)
        .then(payload => {
          if (payload.success) {
            const redirect = getRedirect(payload) || '/account/';
            window.location.href = redirect;
            return;
          }

          regErrorBox.textContent = getMsg(payload, 'Registration failed.');
          regErrorBox.style.display = 'block';
        })
        .catch(() => {
          regErrorBox.textContent = 'Server error. Please try again.';
          regErrorBox.style.display = 'block';
        });
    });
  }

});

