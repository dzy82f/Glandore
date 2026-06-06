<?php
/**
 * Glandore – Problem Cards
 *
 * DB-backed problem cards.
 *
 * Shortcode:
 * [glandore_problem_card slug="informal-organisation"]
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function glandore_problem_cards_table() : string {
	global $wpdb;
	return $wpdb->prefix . 'problem_cards';
}

function glandore_problem_card_table( string $name ) : string {
	global $wpdb;
	return $wpdb->prefix . 'problem_card_' . $name;
}

function glandore_problem_card_get_by_slug( string $slug ) : ?object {
	global $wpdb;

	$table = glandore_problem_cards_table();

	$card = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT *
			 FROM {$table}
			 WHERE slug = %s
			   AND status = 'published'
			 LIMIT 1",
			$slug
		)
	);

	return $card ?: null;
}

function glandore_problem_card_get_steps( int $card_id ) : array {
	global $wpdb;

	$table = glandore_problem_card_table( 'steps' );

	return $wpdb->get_results(
		$wpdb->prepare(
			"SELECT *
			 FROM {$table}
			 WHERE card_id = %d
			 ORDER BY display_order ASC, id ASC",
			$card_id
		)
	) ?: array();
}

function glandore_problem_card_get_step_items( int $step_id ) : array {
	global $wpdb;

	$table = glandore_problem_card_table( 'step_items' );

	return $wpdb->get_results(
		$wpdb->prepare(
			"SELECT *
			 FROM {$table}
			 WHERE step_id = %d
			 ORDER BY display_order ASC, id ASC",
			$step_id
		)
	) ?: array();
}

function glandore_problem_card_render_item( object $step, object $item ) : void {
	$field_id   = 'g_problem_card_' . sanitize_key( $step->step_key . '_' . $item->item_key );
	$field_name = sanitize_key( $step->step_key . '_' . $item->item_key );
	$type       = sanitize_key( (string) $item->item_type );
	$options    = array();

	if ( ! empty( $item->options_json ) ) {
		$decoded = json_decode( (string) $item->options_json, true );

		if ( is_array( $decoded ) ) {
			$options = $decoded;
		}
	}
	?>

	<div class="g-problem-card__field">
		<label for="<?php echo esc_attr( $field_id ); ?>">
			<?php echo esc_html( $item->item_label ); ?>
		</label>

		<?php if ( ! empty( $item->item_help ) ) : ?>
			<p class="g-problem-card__help">
				<?php echo esc_html( $item->item_help ); ?>
			</p>
		<?php endif; ?>

		<?php if ( 'checkboxes' === $type && ! empty( $options ) ) : ?>
			<div class="g-problem-card__checks">
				<?php foreach ( $options as $index => $option ) : ?>
					<?php
					$option_label = is_array( $option ) && isset( $option['label'] )
						? (string) $option['label']
						: (string) $option;

					$option_value = is_array( $option ) && isset( $option['value'] )
						? (string) $option['value']
						: sanitize_title( $option_label );

					$checkbox_id = $field_id . '_' . $index;
					?>

					<label class="g-problem-card__check" for="<?php echo esc_attr( $checkbox_id ); ?>">
						<input
							type="checkbox"
							id="<?php echo esc_attr( $checkbox_id ); ?>"
							name="<?php echo esc_attr( $field_name ); ?>[]"
							value="<?php echo esc_attr( $option_value ); ?>"
						/>
						<span><?php echo esc_html( $option_label ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<textarea
				id="<?php echo esc_attr( $field_id ); ?>"
				name="<?php echo esc_attr( $field_name ); ?>"
				rows="6"
				placeholder="<?php echo esc_attr( (string) $item->placeholder_text ); ?>"
			></textarea>
		<?php endif; ?>
	</div>

	<?php
}

function glandore_problem_card_shortcode( array $atts ) : string {
	$atts = shortcode_atts(
		array(
			'slug' => '',
		),
		$atts,
		'glandore_problem_card'
	);

	$slug = sanitize_title( (string) $atts['slug'] );

	if ( '' === $slug ) {
		return '<p class="g-problem-card-error">Problem card slug missing.</p>';
	}

	$card = glandore_problem_card_get_by_slug( $slug );

	if ( ! $card ) {
		return '<p class="g-problem-card-error">Problem card not found.</p>';
	}

	$steps = glandore_problem_card_get_steps( (int) $card->id );

	ob_start();
	?>

	<article
		class="g-problem-card"
		data-problem-card="<?php echo esc_attr( $card->slug ); ?>"
	>
		<header class="g-problem-card__header">
			<p class="g-problem-card__eyebrow">Problem Card</p>

			<h1 class="g-problem-card__title">
				<?php echo esc_html( $card->title ); ?>
			</h1>

			<?php if ( ! empty( $card->strapline ) ) : ?>
				<p class="g-problem-card__strapline">
					<?php echo esc_html( $card->strapline ); ?>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $card->problem_statement ) ) : ?>
				<div class="g-problem-card__problem">
					<?php echo wpautop( esc_html( $card->problem_statement ) ); ?>
				</div>
			<?php endif; ?>
		</header>

		<?php if ( ! empty( $steps ) ) : ?>

			<form class="g-problem-card__form" method="post">
				<div class="g-problem-card__reflection" data-problem-card-reflection>
					<nav class="g-problem-card__progress" aria-label="Problem card steps">
						<?php foreach ( $steps as $index => $step ) : ?>
							<button
								type="button"
								class="g-problem-card__progress-button <?php echo 0 === $index ? 'is-active' : ''; ?>"
								data-problem-card-step-button="<?php echo esc_attr( $step->step_key ); ?>"
							>
								<span><?php echo esc_html( (string) ( $index + 1 ) ); ?></span>
								<?php echo esc_html( $step->step_title ); ?>
							</button>
						<?php endforeach; ?>
					</nav>

					<?php foreach ( $steps as $index => $step ) : ?>
						<section
							class="g-problem-card__step <?php echo 0 === $index ? 'is-active' : ''; ?>"
							data-problem-card-step="<?php echo esc_attr( $step->step_key ); ?>"
						>
							<header class="g-problem-card__step-header">
								<p class="g-problem-card__step-type">
									<?php echo esc_html( $step->step_type ); ?>
								</p>

								<h2><?php echo esc_html( $step->step_title ); ?></h2>

								<?php if ( ! empty( $step->intro ) ) : ?>
									<div class="g-problem-card__step-intro">
										<?php echo wpautop( esc_html( $step->intro ) ); ?>
									</div>
								<?php endif; ?>
							</header>

							<?php $items = glandore_problem_card_get_step_items( (int) $step->id ); ?>

							<?php if ( ! empty( $items ) ) : ?>
								<div class="g-problem-card__items">
									<?php foreach ( $items as $item ) : ?>
										<?php glandore_problem_card_render_item( $step, $item ); ?>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $step->completion_prompt ) ) : ?>
								<div class="g-problem-card__completion">
									<?php echo wpautop( esc_html( $step->completion_prompt ) ); ?>
								</div>
							<?php endif; ?>

							<div class="g-problem-card__actions">
								<?php if ( $index < count( $steps ) - 1 ) : ?>
									<button
										type="button"
										class="g-problem-card__next"
										data-problem-card-next
									>
										Next step
									</button>
								<?php else : ?>
									<button type="button" class="g-problem-card__save">
										Save completed reflection
									</button>
								<?php endif; ?>

								<span class="g-problem-card__status" aria-live="polite"></span>
							</div>

							<?php if ( $index === count( $steps ) - 1 ) : ?>
								<div class="g-problem-card__saved-message" hidden>
									<strong>Completed reflection saved.</strong>
									<span>Your responses and snapshot have been stored in the database.</span>
								</div>

								<div class="g-problem-card__snapshot" hidden>
									<h3>Your completed reflection</h3>
									<div class="g-problem-card__snapshot-body"></div>
								</div>
							<?php endif; ?>
						</section>
					<?php endforeach; ?>
				</div>
			</form>

		<?php else : ?>

			<p class="g-problem-card-error">This problem card has no steps yet.</p>

		<?php endif; ?>
	</article>

	<?php
	return ob_get_clean();
}

add_shortcode( 'glandore_problem_card', 'glandore_problem_card_shortcode' );

add_action( 'wp_ajax_glandore_problem_card_save_completed', 'glandore_problem_card_save_completed' );
add_action( 'wp_ajax_nopriv_glandore_problem_card_save_completed', 'glandore_problem_card_save_completed' );

function glandore_problem_card_get_or_create_instance( object $card ) : int {
	global $wpdb;

	$instance_table = glandore_problem_card_table( 'instances' );

	$title = 'Reflection – ' . $card->title . ' – ' . current_time( 'Y-m-d H:i' );

	$wpdb->insert(
		$instance_table,
		array(
			'card_id'     => (int) $card->id,
			'user_id'     => get_current_user_id(),
			'title'       => $title,
			'description' => '',
			'status'      => 'active',
			'created_at'  => current_time( 'mysql' ),
			'updated_at'  => current_time( 'mysql' ),
		),
		array(
			'%d',
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
		)
	);

	return (int) $wpdb->insert_id;
}

function glandore_problem_card_save_instance_classes( int $instance_id ) : void {
	// Classification front-door removed.
	// Function retained so existing save flow remains stable.
}

function glandore_problem_card_get_instance( int $instance_id ) : ?object {
	global $wpdb;

	$table = glandore_problem_card_table( 'instances' );

	$row = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT *
			 FROM {$table}
			 WHERE id = %d
			 LIMIT 1",
			$instance_id
		)
	);

	return $row ?: null;
}

function glandore_problem_card_save_completed() : void {
	global $wpdb;

	$card_slug = sanitize_title( (string) ( $_POST['card_slug'] ?? '' ) );

	if ( '' === $card_slug ) {
		wp_send_json_error( array( 'message' => 'Missing card slug.' ) );
	}

	$card = glandore_problem_card_get_by_slug( $card_slug );

	if ( ! $card ) {
		wp_send_json_error( array( 'message' => 'Problem card not found.' ) );
	}

	$user_id     = get_current_user_id();
	$instance_id = glandore_problem_card_get_or_create_instance( $card );

	if ( $instance_id <= 0 ) {
		wp_send_json_error( array( 'message' => 'Could not create problem instance.' ) );
	}

	glandore_problem_card_save_instance_classes( $instance_id );

	$session_table  = glandore_problem_card_table( 'sessions' );
	$response_table = glandore_problem_card_table( 'responses' );
	$steps_table    = glandore_problem_card_table( 'steps' );
	$items_table    = glandore_problem_card_table( 'step_items' );

	$wpdb->insert(
		$session_table,
		array(
			'card_id'      => (int) $card->id,
			'instance_id'  => $instance_id,
			'user_id'      => $user_id,
			'session_key'  => wp_generate_password( 64, false ),
			'status'       => 'completed',
			'completed_at' => current_time( 'mysql' ),
			'updated_at'   => current_time( 'mysql' ),
		)
	);

	$session_id = (int) $wpdb->insert_id;

	if ( $session_id <= 0 ) {
		wp_send_json_error(
			array(
				'message'  => 'Could not create problem card session.',
				'db_error' => $wpdb->last_error,
			)
		);
	}

	$steps = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT *
			 FROM {$steps_table}
			 WHERE card_id = %d
			 ORDER BY display_order ASC, id ASC",
			(int) $card->id
		)
	);

	foreach ( $steps as $step ) {
		$items = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
				 FROM {$items_table}
				 WHERE step_id = %d
				 ORDER BY display_order ASC, id ASC",
				(int) $step->id
			)
		);

		foreach ( $items as $item ) {
			$field_name = sanitize_key( $step->step_key . '_' . $item->item_key );

			if ( ! array_key_exists( $field_name, $_POST ) ) {
				continue;
			}

			$value = wp_unslash( $_POST[ $field_name ] );

			if ( is_array( $value ) ) {
				$response_text = '';
				$response_json = wp_json_encode( array_map( 'sanitize_text_field', $value ) );
			} else {
				$response_text = sanitize_textarea_field( (string) $value );
				$response_json = null;
			}

			$wpdb->insert(
				$response_table,
				array(
					'session_id'    => $session_id,
					'step_id'       => (int) $step->id,
					'item_id'       => (int) $item->id,
					'response_text' => $response_text,
					'response_json' => $response_json,
					'created_at'    => current_time( 'mysql' ),
					'updated_at'    => current_time( 'mysql' ),
				)
			);
		}
	}

	$instance          = glandore_problem_card_get_instance( $instance_id );
	$snapshot_title    = $instance ? (string) $instance->title : 'Completed reflection – ' . $card->title;
	$snapshot_markdown = glandore_problem_card_build_snapshot_markdown( $card, $session_id, $instance );

	$snapshot_inserted = $wpdb->insert(
		glandore_problem_card_table( 'snapshots' ),
		array(
			'session_id'        => $session_id,
			'snapshot_title'    => $snapshot_title,
			'snapshot_markdown' => $snapshot_markdown,
			'created_at'        => current_time( 'mysql' ),
		)
	);

	if ( false === $snapshot_inserted ) {
		wp_send_json_error(
			array(
				'message'  => 'Responses saved, but snapshot creation failed.',
				'db_error' => $wpdb->last_error,
			)
		);
	}

	wp_send_json_success(
		array(
			'message'           => 'Completed reflection saved.',
			'instance_id'       => $instance_id,
			'session_id'        => $session_id,
			'snapshot_id'       => (int) $wpdb->insert_id,
			'snapshot_markdown' => $snapshot_markdown,
			'snapshot_html'     => wpautop( esc_html( $snapshot_markdown ) ),
		)
	);
}

function glandore_problem_card_build_snapshot_markdown( object $card, int $session_id, ?object $instance = null ) : string {
	global $wpdb;

	$response_table = glandore_problem_card_table( 'responses' );
	$steps_table    = glandore_problem_card_table( 'steps' );
	$items_table    = glandore_problem_card_table( 'step_items' );

	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT
				s.display_order,
				s.step_title,
				i.item_label,
				r.response_text,
				r.response_json
			 FROM {$response_table} r
			 JOIN {$steps_table} s ON r.step_id = s.id
			 JOIN {$items_table} i ON r.item_id = i.id
			 WHERE r.session_id = %d
			 ORDER BY s.display_order ASC, i.display_order ASC",
			$session_id
		)
	);

	$lines = array();

	$lines[] = '# ' . ( $instance ? (string) $instance->title : (string) $card->title );
	$lines[] = '';

	$lines[] = '## Source card';
	$lines[] = (string) $card->title;
	$lines[] = '';

	if ( ! empty( $card->strapline ) ) {
		$lines[] = (string) $card->strapline;
		$lines[] = '';
	}

	if ( ! empty( $card->problem_statement ) ) {
		$lines[] = '## Problem';
		$lines[] = trim( (string) $card->problem_statement );
		$lines[] = '';
	}

	$current_step = '';

	foreach ( $rows as $row ) {
		if ( $current_step !== $row->step_title ) {
			$current_step = $row->step_title;
			$lines[]      = '## ' . (string) $current_step;
			$lines[]      = '';
		}

		$value = trim( (string) $row->response_text );

		if ( '' === $value && ! empty( $row->response_json ) ) {
			$decoded = json_decode( (string) $row->response_json, true );

			if ( is_array( $decoded ) ) {
				$value = implode( ', ', array_map( 'sanitize_text_field', $decoded ) );
			}
		}

		$lines[] = '**' . (string) $row->item_label . '**';
		$lines[] = '';
		$lines[] = $value;
		$lines[] = '';
	}

	$lines[] = '## Re-entry note';
	$lines[] = 'This reflection should be revisited after action has been taken, to test whether the system has shifted or merely absorbed the intervention.';

	return trim( implode( "\n", $lines ) );
}