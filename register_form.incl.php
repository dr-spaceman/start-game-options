<?

if(!$_POST['udata'] && !$_POST['noauth']){
	require_once ($_SERVER['DOCUMENT_ROOT']."/bin/php/class.authenticate.php");
	$auth = new authenticate();
}

?>

<form action="register.php" method="post" id="regform">
	<h3>Register for a Videogam.in account</h3>
	
	<input type="hidden" name="loc" value="<?=$loc?>">
	<input type="hidden" name="udata" value="<?=$_POST['udata']?>">
	
	<table border="0" cellpadding="0" cellspacing="0">
		<tr>
		  <td colspan="2">
		  	<div class="fw">
		  		<input type="text" name="sub[username]" value="<?=htmlspecialchars($sub['username'])?>" placeholder="Username" id="user" maxlength="15"/>
		  	</div>
		  	<small>Use letters, numbers, dash (-), and underscore (_)</small>
		  </td>
		</tr>
		<tr>
		  <td colspan="2">
		  	<div class="fw">
		  		<input type="password" name="sub[password]" value="<?=htmlspecialchars($sub['password'])?>" placeholder="Password" id="pass" maxlength="15"/>
		  	</div>
		  	<div class="fw" style="margin-top:5px;">
		  		<input type="password" name="sub[password_match]" value="<?=htmlspecialchars($sub['password_match'])?>" placeholder="Confirm password" id="pass2" maxlength="15"/>
		  	</div>
		  </td>
		</tr>
		<tr>
		  <td colspan="2">
		  	<div class="fw">
		  		<input type="text" name="sub[email]" value="<?=htmlspecialchars($sub['email'])?>" placeholder="E-mail address" maxlength="120"/>
		  	</div>
		  	<small>Your personal information is strictly confidential and will never be shared with a third party, ever. <a href="/terms.php" class="arrow-link" target="_blank">Policy</a></small>
		  </td>
		</tr>
		<tr>
			<th><label for="gender">Gender:</label></th>
			<td>
				<select name="sub[gender]">
					<option value="">Private</option>
				  <option value="male"<?=($sub['gender'] == "male" ? ' selected="selected"' : '')?>>Male</option>
				  <option value="female"<?=($sub['gender'] == "female" ? ' selected="selected"' : '')?>>Female</option>
				  <option value="asexual"<?=($sub['gender'] == "asexual" ? ' selected="selected"' : '')?>>Asexual/Hermaphrodite/Robot</option>
			  </select>
			</td>
		</tr>
		<? if($auth){ ?>
		<tr class="auth">
			<td colspan="2">
		  	<small>Authentication: please fill in the information below to confirm you're a real person</small>
				<?=$auth->form?>
			</td>
		</tr>
		<? } ?>
		<tr>
		  <td colspan="2">
		  	<div class="subm"><input type="submit" name="do" value="Submit Registration" class="forminp"/></div>
		  </td>
		</tr>
	</table>
</form>