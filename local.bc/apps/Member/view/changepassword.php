<div class="animated" id="notification">
	<div class="content">
		<p class="nottext">Ошибка загрузки</p>
		<p class="notimg">
			<img src="/img/closebig.png">
		</p>
	</div>
</div>

<div>

	<form
		action="/User/Changepassword"
		method="post"
		id="member-changepassword"
		class="gcform"
		data-managedform="yes"
		data-onsuccess="userChangepassword"
		data-onerror="userChangepasswordError">

		<label for="old_password">Старый пароль</label>
		<input type="password" name="old_password" data-selector="old_password" required>
		<br>
		<label for="password">Новый пароль</label>
		<input type="password" name="password"  data-selector="password" required>
		<input type="hidden" value="<?=$this->user->urn?>" data-selector="userUrn">

		<div>
			<input type="submit" value="Сменить пароль">
		</div>

	</form>

</div>