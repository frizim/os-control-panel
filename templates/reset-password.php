<form class="login100-form validate-form flex-sb flex-w" action="index.php?page=reset-password&token=<?= $v['reset-token'] ?>" method="post">
	<span class="login100-form-title p-b-51">
		Neues Passwort festlegen
	</span>

	<div class="flex-sb-m w-full p-t-3 p-b-24" style="color: red;">
		<?= $v['message'] ?>
	</div>

	<div class="wrap-input100 validate-input m-b-16" data-validate="Bitte gib dein neues Passwort ein">
		<input class="input100" type="password" name="password" placeholder="Passwort">
		<span class="focus-input100"></span>
	</div>

	<div class="wrap-input100 validate-input m-b-16" data-validate="Bitte gib das Passwort erneut ein">
		<input class="input100" type="password" name="passwordRepeat" placeholder="Passwort wiederholen">
		<span class="focus-input100"></span>
	</div>

	<div class="container-login100-form-btn m-t-17">
		<?= $csrf ?>
		<input type="hidden" name="resetToken" value="<?= $v['reset-token'] ?>">
		<button class="login100-form-btn" name="reset-password">
			Passwort ändern
		</button>
	</div>
</form>