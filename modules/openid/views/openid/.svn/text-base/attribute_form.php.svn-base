		<h1><?php echo $title; ?></h1>

		<p><?php echo $subtitle; ?></p>

		<?php echo form::open();

	foreach ($user_attributes as $key => $value): ?>

			<label for="<?php echo $key; ?>" ><?php echo ($key == 'fullname')? 'Username' : ucfirst($key); ?></label>

			<input type="text" id="<?php echo $key; ?>" name="<?php echo $key; ?>" value="<?php echo $value; ?>"  />
	<?php
		if (isset($formerrors[$key])): ?>

			<p class="error"><?php echo $formerrors[$key];?></p>
	<?php
		endif;?>

<?php
	endforeach; ?>

			<input type="submit" id="submit" name="submit" value="Submit"  />

		</form>
