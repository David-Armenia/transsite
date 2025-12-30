<?php
/*
Template Name: Login
*/
get_header();
?>

<main class="auth-page">

  <div class="auth-wrapper">

    <!-- LEFT / HERO -->
    <div class="auth-visual">
      <div class="auth-visual-inner">

        <div class="auth-stats">
          <div class="auth-stat">
            <strong>500+</strong>
            <span>Profiles</span>
          </div>
          <div class="auth-stat">
            <strong>150+</strong>
            <span>Requests daily</span>
          </div>
        </div>

        <div class="auth-brand">
          <h1 class="auth-logo">Trans<br>Escort</h1>
        </div>

      </div>
    </div>

    <!-- RIGHT / FORM -->
    <div class="auth-form-wrap">

      <div class="auth-form">

        <h2 class="auth-title">Trans Escort</h2>

        <div class="auth-tabs">
          <button type="button" class="auth-tab is-active" data-tab="login">Login</button>
<button type="button" class="auth-tab" data-tab="register">Register</button>
 </div>

        <!-- LOGIN FORM -->
        <form class="auth-form-inner js-login-form" method="post">
          <?php wp_nonce_field('transescort_login', 'login_nonce'); ?>
          <div class="auth-remember">
            <label><input type="checkbox" name="remember" value="1"> Запомнить меня</label>
          </div>


          <div class="field">
            <label>Email</label>
            <input type="email" class="input" name="user_email" placeholder="email or username" required>
          </div>

          <div class="field">
            <label>Password</label>
            <input type="password" class="input" name="user_password" placeholder="••••••••" required>
          </div>

          <div class="field field-checkbox">
            <label>
              <input type="checkbox" name="remember_me" value="1">
              Remember me
            </label>
          </div>

          <button type="submit" class="btn btn-primary btn-full">
            Login
          </button>

          <!-- AJAX error block -->
          <div class="auth-error" style="display:none;"></div>

          <div class="auth-divider">
            <span>or</span>
          </div>

          <button type="button" class="btn btn-outline btn-full">
            Login via Telegram
          </button>

          <p class="auth-note">
            By continuing you agree to our Terms & Privacy Policy
          </p>

        </form>

        <!-- REGISTER FORM (hidden by default) -->
        <form class="auth-form-inner js-register-form" method="post" style="display:none;">
          <?php wp_nonce_field('transescort_register', 'register_nonce'); ?>

          <div class="field">
            <label>Email</label>
            <input type="email" class="input" name="user_email" required>
          </div>

          <div class="field">
            <label>Password</label>
            <input type="password" class="input" name="user_password" required>
          </div>

          <div class="field">
            <label>Repeat password</label>
            <input type="password" class="input" name="user_password_repeat" required>
          </div>

          <div class="field field-checkbox">
            <label>
              <input type="checkbox" name="terms" required>
              I confirm I am 18+ and accept Terms
            </label>
          </div>

          <button type="submit" class="btn btn-primary btn-full">
            Register
          </button>

          <div class="auth-error" style="display:none;"></div>
        </form>

      </div>

    </div>

  </div>

</main>

<?php get_footer(); ?>

