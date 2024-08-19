<?php namespace views;

	class Reaction extends AbstractView {
		protected $reaction;

		public function __construct($session, $reaction) {
			parent::__construct($session);

			$this->reaction = $reaction;
		}
	}

	class Answer extends Reaction {

		public function displayForm($post) {
			$text = $this->generateTextField();
			$save_buttons = $this->generateSaveButtons();

			$form = <<<EOF
			<div class="answers">
				<div class="answer">
					<div class="flex column" style="gap: 10px">
						{$text}
						<div class="flex footer">
							<div class="flex left">
								{$save_buttons}
							</div>

							<div class="flex right">
							</div>
						</div>
					</div>
				</div>
			</div>
			EOF;

			$post_view = \views\Post::factoryMethod($this->session, $post);
			$post_view->displayReference(active: false, reactions: $form);
		}

		protected function generateTextField() {
			return UIComponents::getTextArea('Text', 'text');
		}

		protected function generateSaveButtons() {
			return UIComponents::getFilledButton('Save changes', 'save', '#');
		}
	}

	class Report extends Reaction {

		public function displayForm($post) {
			$message = $this->generateMessageField();
			$save_buttons = $this->generateSaveButtons();

			$form = <<<EOF
			<div class="answers">
				<div class="answer">
					<div class="flex column" style="gap: 10px">
						{$message}
						<div class="flex footer">
							<div class="flex left">
								{$save_buttons}
							</div>

							<div class="flex right">
							</div>
						</div>
					</div>
				</div>
			</div>
			EOF;

			$post_view = \views\Post::factoryMethod($this->session, $post);
			$post_view->displayReference(active: false, reactions: $form);
		}

		protected function generateMessageField() {
			return UIComponents::getTextArea('Message', 'message');
		}

		protected function generateSaveButtons() {
			return UIComponents::getFilledButton('Send report', 'send', '#');
		}
	}

?>