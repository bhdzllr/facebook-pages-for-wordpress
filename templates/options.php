<div class="wrap fbpfwp-options">
	<h1>Facebook Pages for WordPress</h1>

	<form id="fbpfwp-options-form" method="post" action="<?php echo admin_url( 'options.php' ); ?>">
		<fieldset>
			<legend>Options</legend>
			<?php 

			settings_fields( 'fbpfwp_options' ); 
			do_settings_sections( 'fbpfwp.php' ); 

			?>

			<p>
				<label for="fbpfwp-app-id">App Id:</label>
				<input type="text" name="fbpfwp_options[app_id]" id="fbpfwp-app-id" value="<?php if ( isset( $this->options['app_id'] ) ) echo $this->options['app_id']; ?>" placeholder="App Id" />
			</p>

			<p>
				<label for="fbpfwp-app-secret">App Secret:</label>
				<input type="text" name="fbpfwp_options[app_secret]" id="fbpfwp-app-secret" value="<?php if ( isset( $this->options['app_secret'] ) ) echo $this->options['app_secret']; ?>" placeholder="App Secret" />
			</p>

			<p>
				<label for="fbpfwp-page-id">Page Id:</label>
				<input type="text" name="fbpfwp_options[page_id]" id="fbpfwp-page-id" value="<?php if ( isset( $this->options['page_id'] ) ) echo $this->options['page_id']; ?>" placeholder="Page Id" />
			</p>

			<p>
				<!-- <label for="fbpfwp-access-token">Access Token:</label> -->
				<input type="hidden" name="fbpfwp_options[access_token]" id="fbpfwp-access-token" value="<?php if ( isset( $this->options['access_token'] ) ) echo $this->options['access_token']; ?>" placeholder="Access Token" />
				<span class="assistive">
					<?php if ( ! $this->fbOptions['accessToken'] ) : ?>
					&times; Access Token: 
					<a href="<?php echo $this->fbOptions['loginUrl']; ?>">Facebook Login</a>
					<?php else: ?>
					&#10003; Access Token
					<?php endif; ?>
				</span>
			</p>

			<?php submit_button(); ?>
		</fieldset>
	</form>
</div>