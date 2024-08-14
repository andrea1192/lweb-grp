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
			$form = $this->generateForm();

			$post_view = \views\Post::factoryMethod($this->session, $post);
			$post_view->displayReference(active: false, reactions: $form);
		}

		private function generateForm() {
			$save_buttons = $this->generateSaveButtons();

			return <<<EOF
			<div class="answers">
				<div class="answer">
					<div class="flex column" style="gap: 10px">
						<label>
							<span class="label">Text</span>
							<textarea class="" rows="5" cols="80"></textarea>
						</label>
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
		}

		protected function generateSaveButtons() {
			return UIComponents::getFilledButton('Save changes', 'save', '#');
		}
	}

?>