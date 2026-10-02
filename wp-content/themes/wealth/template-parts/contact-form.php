<form class="contact-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
	<input type="hidden" name="action" value="wealth_enquiry" />
	<?php wp_nonce_field( 'wealth_enquiry', 'wealth_enquiry_nonce' ); ?>
	<div class="visually-hidden" aria-hidden="true">
		<label for="website">Leave this field empty</label>
		<input class="visually-hidden" id="website" name="website" type="text" tabindex="-1" autocomplete="off" aria-hidden="true" />
	</div>
	<div class="contact-form__row">
		<div class="form-field">
			<label class="form-field__label" for="name">Name</label>
			<input class="form-field__control" id="name" name="name" type="text" autocomplete="name" required />
			<p class="form-field__error">Enter your name</p>
		</div>
		<div class="form-field">
			<label class="form-field__label" for="organisation">Organisation <span class="form-field__optional">(optional)</span></label>
			<input class="form-field__control" id="organisation" name="organisation" type="text" autocomplete="organization" />
		</div>
	</div>
	<div class="form-field">
		<label class="form-field__label" for="email">Email</label>
		<p class="form-field__hint" id="email-hint">We will only use this to reply to you.</p>
		<input class="form-field__control" id="email" name="email" type="email" autocomplete="email" required aria-describedby="email-hint" />
		<p class="form-field__error">Enter an email address in the format name@example.com</p>
	</div>
	<div class="form-field">
		<label class="form-field__label" for="phone">Phone <span class="form-field__optional">(optional)</span></label>
		<p class="form-field__hint" id="phone-hint">Include the country code if you are outside the UK.</p>
		<input class="form-field__control" id="phone" name="phone" type="tel" autocomplete="tel" aria-describedby="phone-hint" />
	</div>
	<div class="form-field">
		<label class="form-field__label" for="investor-type">Investor type</label>
		<select class="form-field__control" id="investor-type" name="investor_type" required>
			<option value="" disabled selected>Choose one</option>
			<?php foreach ( WEALTH_INVESTOR_TYPES as $investor_type ) : ?>
				<option value="<?php echo esc_attr( $investor_type ); ?>"><?php echo esc_html( $investor_type ); ?></option>
			<?php endforeach; ?>
		</select>
		<p class="form-field__error">Choose the option that best describes you</p>
	</div>
	<div class="form-field">
		<label class="form-field__label" for="message">Message</label>
		<p class="form-field__hint" id="message-hint">Tell us what you are looking for and, if you can, roughly what size of investment you have in mind.</p>
		<textarea class="form-field__control" id="message" name="message" required aria-describedby="message-hint"></textarea>
		<p class="form-field__error">Enter a message</p>
	</div>
	<div class="form-check">
		<input class="form-check__input" id="consent" name="consent" type="checkbox" required />
		<label class="form-check__label" for="consent">I agree that Wealth may use these details to respond to my enquiry, as described in the <a href="<?php echo esc_url( wealth_page_url( 'privacy' ) ); ?>">privacy notice</a>.</label>
		<p class="form-field__error">Tick the box so that we can reply to you</p>
	</div>
	<button class="button button--solid contact-form__submit" type="submit">Send enquiry</button>
	<?php $form_note = (string) wealth_meta( 'form_note' ); ?>
	<?php if ( $form_note ) : ?>
		<p class="form-field__hint"><?php echo esc_html( $form_note ); ?></p>
	<?php endif; ?>
</form>
