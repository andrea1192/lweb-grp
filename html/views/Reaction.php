<?php namespace views;

	class Reaction extends AbstractView {
		protected $reaction;

		public function __construct($session, $reaction) {
			parent::__construct($session);

			$this->reaction = $reaction;
		}
	}

	class Answer extends Reaction {

		public function generateDisplay($active = true, $selected = false) {
			$selected_class = $selected ?
					'selected' : '';
			$selected_icon =  $selected ?
					UIComponents::getIcon('check_circle', 'selected_answer') : '';

			if ($active)
				$reaction_buttons = $this->generateReactionButtons();
			else
				$reaction_buttons = '';

			if ($active && !$selected && $this->session->isMod())
				$select_button = UIComponents::getTextButton('Select answer', 'check_circle', '#');
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

		public function generateInsertForm() {
			$text = UIComponents::getTextArea('Text', 'text');
			$save_buttons = UIComponents::getFilledButton('Save changes', 'save', '#');

			return <<<EOF
			<div class="answers">
				<form class="answer" method="post" action="">
					<div class="flex column">
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

		public function generate() {

			if ($this->reaction->status == 'open') {
				return ($this->session->isMod()) ?
						$this->generateAdminForm() :
						$this->generateDisplay();

			} else {
				return $this->generateDisplay();
			}
		}

		public function generateDisplay() {
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
				<form class="answer" method="post" action="">
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
				</form>
			</div>
			EOF;
		}

		public function generateInsertForm() {

			return $this->generateUserForm();
		}

		public function generateUserForm() {
			$message = UIComponents::getTextArea('Message', 'message');
			$save_buttons = UIComponents::getFilledButton('Send report', 'send', '#');

			return <<<EOF
			<div class="answers">
				<form class="answer" method="post" action="">
					<div class="flex column">
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

		public function generateAdminForm() {
			$message = <<<EOF
			<div>
				<div class="small">Message from {$this->reaction->author}:</div>
				<div class="content">{$this->reaction->message}</div>
			</div>
			EOF;
			$response = UIComponents::getTextArea('Response', 'response');
			$save_buttons = UIComponents::getFilledButton('Send response', 'send', '#');

			return <<<EOF
			<div class="answers">
				<form class="answer" method="post" action="">
					<div class="flex column">
						{$message}
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
	}

?>