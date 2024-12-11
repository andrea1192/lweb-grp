<?php namespace views;

	class Reaction extends AbstractView {
		protected $reaction;
		protected $ref;

		/* Genera pulsanti per inserire reazioni a partire dall'array associativo che riceve, che
		* associa ogni tipo di reazione ammessa per il post in questione ad un oggetto che contiene
		* le statistiche sulle reazioni di quel tipo già inserite. Ciascun pulsante conterrà un
		* conteggio o una media delle valutazioni espresse.
		*/
		public static function generateReactionButtons($reactions) {
			$buttons = '';

			if (!$reactions) return $buttons;

			$components = 'views\UIComponents';
			$session = \controllers\ServiceLocator::resolve('session');

			foreach ($reactions as $type => $stats) {
				$button = '';
				$reaction_view = new ReactionType($stats);

				if (!$session->isLoggedIn())
					$tooltip = '<div class="tooltip">Sign in to react</div>';
				else
					$tooltip = '<div class="tooltip">Your account has been disabled</div>';

				$action = $reaction_view->generateURL('add_reaction', $type);
				$status = $session->isAllowed();

				switch ($type) {
					case 'like':
						$buttons .= UIComponents::getTextButton(
								$stats->count_up,
								'thumb_up',
								$reaction_view->generateURL('add_reaction', 'like'),
								action: null,
								enabled: $status,
								content: $status ? '' : $tooltip);
						$buttons .= UIComponents::getTextButton(
								$stats->count_down,
								'thumb_down',
								$reaction_view->generateURL('add_reaction', 'dislike'),
								action: null,
								enabled: $status,
								content: $status ? '' : $tooltip);
						continue 2;

					case 'usefulness':
						$popup = <<<EOF
						<div class="popup">
							<div>Useful?</div>
							<div class="rate">
								<select name="rating">
									<option value="1">1</option>
									<option value="2">2</option>
									<option value="3">3</option>
									<option value="4">4</option>
									<option value="5">5</option>
								</select>
							</div>
							{$components::getSubmitButton()}
						</div>
						EOF;
						$button .= UIComponents::getTextButton(
								round($stats->average, 1),
								'lightbulb',
								action: null,
								enabled: $status,
								content: $status ? $popup : $tooltip);
						break;

					case 'agreement':
						$popup = <<<EOF
						<div class="popup">
							<div>Agree?</div>
							<div class="rate">
								<select name="rating">
									<option value="1">1</option>
									<option value="2">2</option>
									<option value="3">3</option>
									<option value="4">4</option>
									<option value="5">5</option>
								</select>
							</div>
							{$components::getSubmitButton()}
						</div>
						EOF;
						$button .= UIComponents::getTextButton(
								round($stats->average, 1),
								'thumb_up',
								action: null,
								enabled: $status,
								content: $status ? $popup : $tooltip);
						break;

					case 'spoilage':
						$popup = <<<EOF
						<div class="popup">
							<div>Spoiler level:</div>
							<div class="rate">
								<select name="rating">
									<option value="1">1</option>
									<option value="2">2</option>
									<option value="3">3</option>
									<option value="4">4</option>
									<option value="5">5</option>
									<option value="6">6</option>
									<option value="7">7</option>
									<option value="8">8</option>
									<option value="9">9</option>
									<option value="10">10</option>
								</select>
							</div>
							{$components::getSubmitButton()}
						</div>
						EOF;
						$button .= UIComponents::getTextButton(
								round($stats->average, 1),
								'speed',
								action: null,
								enabled: $status,
								content: $status ? $popup : $tooltip);
						break;

					default: break;
				}

				$buttons .= <<<EOF
				<form method="post" action="{$action}">
					<div>
						{$button}
					</div>
				</form>
				EOF;
			}

			return $buttons;
		}

		public function __construct($reaction = null, $ref = null) {
			parent::__construct();

			$this->reaction = $reaction;
			$this->ref = $ref ?? $reaction->post;
		}

		/* Genera un URL per l'azione richiesta, se ammessa sull'oggetto di riferimento */
		public function generateURL($action = 'display', $reaction_type = 'like') {
			if ($this->reaction)
				$post_id = $this->reaction->post;
			else
				$post_id = $this->ref->id;

			$URL = "post.php?id={$post_id}";

			switch ($action) {
				default:
				case 'create':
					$URL = "post.php?&action=create";
					break;
				case 'add_reaction':
					$URL .= "&action=add_reaction";
					$URL .= "&type={$reaction_type}";
					break;
				case 'send_report':
				case 'close_report':
					$URL .= "&action={$action}";
					break;
				case 'select_answer':
					$URL .= "&action=select_answer";
					$URL .= "&answer={$this->reaction->id}";
					break;
			}

			return htmlspecialchars($URL, ENT_QUOTES | ENT_SUBSTITUTE | ENT_XHTML);
		}
	}

	class Answer extends Reaction {

		/* Genera il codice per visualizzare una risposta. Non visualizza direttamente l'output, ma
		* è utilizzato dal metodo Question::display() che fa uso di Question::generateAnswers() per
		* iterare sulle risposte e di Post::displayReference() per la visualizzazione.
		*/
		public function generateDisplay($active = true, $selected = false) {
			$selected_class = $selected ?
					'selected' : '';
			$selected_icon =  $selected ?
					UIComponents::getIcon('check_circle', 'selected_answer') : '';

			if ($active)
				$reaction_buttons = static::generateReactionButtons($this->reaction->reactions);
			else
				$reaction_buttons = '';

			if ($active && !$selected && $this->session->isMod())
				$select_button = UIComponents::getTextButton(
						'Select answer',
						'check_circle',
						$this->generateURL('select_answer')
				);
			else
				$select_button = '';

			return <<<EOF
			<div class="answer {$selected_class}">
				{$selected_icon}
				<div class="header">
					<div class="flex small">
						<span class="author">{$this->reaction->author}</span>
						<span class="date">{$this->reaction->date}</span>
					</div>
					<div class="right"></div>
				</div>
				<div class="content">
					{$this->reaction->text}
				</div>
				<div class="flex footer">
					<div class="flex left reactions">
						{$reaction_buttons}
					</div>
					<div class="flex right">
						{$select_button}
					</div>
				</div>
			</div>
			EOF;
		}

		/* Genera il form di inserimento di una risposta */
		public function generateInsertForm() {
			$action = $this->generateURL('create');
			$text = UIComponents::getTextArea('Text', 'text');
			$save_buttons = UIComponents::getFilledButton('Submit', 'send');

			$components = 'views\UIComponents';

			return <<<EOF
			<div class="answers">
				<form class="answer" method="post" action="{$action}">
					<div class="flex column">
						{$components::getHiddenInput('type', 'answer')}
						{$components::getHiddenInput('post', $this->ref->id)}
						{$text}
						<div class="flex footer">
							<div class="flex left">
								{$save_buttons}
							</div>

							<div class="flex right">
							</div>
						</div>
					</div>
				</form>
			</div>
			EOF;
		}
	}

	class Report extends Reaction {

		/* Genera il codice per visualizzare una segnalazione in modo opportuno.
		*
		* 	segnalazioni aperte: ai mod è presentato un form, agli utenti il contenuto attuale
		*	segnalazioni chiuse: indipendentemente dal livello di privilegio, è presentato il
		*			contenuto attuale della segnalazione
		*
		* Non visualizza direttamente l'output, ma è utilizzato dal metodo ReportsView::printList()
		* che fa uso di Post::displayReference().
		*/
		public function generate() {
			if ($this->reaction->status == 'open') {
				return ($this->session->isMod()) ?
						$this->generateAdminForm() :
						$this->generateDisplay();

			} else {
				return $this->generateDisplay();
			}
		}

		/* Genera il codice per visualizzare il contenuto attuale della segnalazione */
		public function generateDisplay() {
			$overlay = $this->generateStatus();

			$message = <<<EOF
			<div>
				<div class="small">Message from {$this->reaction->author}:</div>
				<div class="content">{$this->reaction->message}</div>
			</div>
			EOF;
			$response = <<<EOF
			<div>
				<div class="small">Response from staff:</div>
				<div class="content">{$this->reaction->response}</div>
			</div>
			EOF;

			return <<<EOF
			<div class="answers">
				{$overlay}
				<div class="answer">
					<div class="flex column">
						{$message}
						{$response}
						<div class="flex footer">
							<div class="flex left">
							</div>
							<div class="flex right">
							</div>
						</div>
					</div>
				</div>
			</div>
			EOF;
		}

		/* Genera il form di inserimento di una segnalazione */
		public function generateInsertForm() {
			$send = $this->generateURL('send_report');
			$message = UIComponents::getTextArea('Message', 'message');
			$save_buttons = UIComponents::getFilledButton('Send report', 'send');

			$components = 'views\UIComponents';

			return <<<EOF
			<div class="answers">
				<form class="answer" method="post" action="{$send}">
					<div class="flex column">
						{$components::getHiddenInput('type', 'report')}
						{$components::getHiddenInput('post', $this->ref->id)}
						{$components::getHiddenInput('response', '')}
						{$message}
						<div class="flex footer">
							<div class="flex left">
								{$save_buttons}
							</div>
							<div class="flex right">
							</div>
						</div>
					</div>
				</form>
			</div>
			EOF;
		}

		/* Genera il form di gestione di una segnalazione */
		public function generateAdminForm() {
			$overlay = $this->generateStatus();
			$send = $this->generateURL('close_report');
			$rep_accept = '+'.$this->reaction::REPUTATION_DELTAS['accepted'];
			$rep_reject = ''.$this->reaction::REPUTATION_DELTAS['rejected'];
			$actions = [
				'closed' => 'Close: Close this report without taking further actions',
				'accepted' => "Accept: Accept this report and reward the submitter ({$rep_accept} reputation)",
				'rejected' => "Reject: Reject this report and punish the submitter ({$rep_reject} reputation)"
			];
			$message = <<<EOF
			<div>
				<div class="small">Message from {$this->reaction->author}:</div>
				<div class="content">{$this->reaction->message}</div>
			</div>
			EOF;

			foreach ($actions as $action => $label)
				$options[] = UIComponents::getSelectOption($label, $action);

			$status = UIComponents::getSelect('Action', 'status', $options);
			$response = UIComponents::getTextArea('Response', 'response', $this->reaction->response);
			$save_buttons = UIComponents::getFilledButton('Send response', 'send');

			$components = 'views\UIComponents';

			return <<<EOF
			<div class="answers">
				{$overlay}
				<form class="answer" method="post" action="{$send}">
					<div class="flex column">
						{$components::getHiddenInput('type', 'report')}
						{$components::getHiddenInput('post', $this->reaction->post)}
						{$components::getHiddenInput('author', $this->reaction->author)}
						{$components::getHiddenInput('date', $this->reaction->date)}
						{$components::getHiddenInput('message', $this->reaction->message)}
						{$message}
						{$status}
						{$response}
						<div class="flex footer">
							<div class="flex left">
								{$save_buttons}
							</div>
							<div class="flex right">
							</div>
						</div>
					</div>
				</form>
			</div>
			EOF;
		}

		/* Genera codice per visualizzare lo status della segnalazione */
		protected function generateStatus() {
			$label = '';

			switch ($this->reaction->status) {
				default:
				case 'open':
					$label = 'Pending review';
					$icon = 'pending_actions';
					break;
				case 'accepted':
					$label = 'Accepted';
					$icon = 'thumb_up';
					break;
				case 'rejected':
					$label = 'Rejected';
					$icon = 'thumb_down';
					break;
				case 'closed':
					$label = 'Closed';
					$icon = 'cancel';
					break;
			}

			return UIComponents::getOverlay($label, $icon, cls: 'right status');
		}
	}


	class ReactionType extends Reaction {}

?>