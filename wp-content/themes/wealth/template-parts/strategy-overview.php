<?php $managers = wealth_posts_by_id( (array) wealth_meta( 'managers' ), 'team_member' ); ?>
<div class="prose">
	<?php if ( $managers ) : ?>
		<p class="strategy-meta">Managed by <?php echo wp_kses_post( wealth_names_sentence( $managers ) ); ?></p>
	<?php endif; ?>
	<p class="lede"><?php echo esc_html( wealth_meta( 'objective' ) ); ?></p>
	<?php echo wp_kses_post( wealth_paragraphs( (string) wealth_meta( 'intro' ) ) ); ?>
</div>
