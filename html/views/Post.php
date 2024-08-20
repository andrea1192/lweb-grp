<?php namespace views;
	
	class Post extends AbstractView {
		protected $post;

		public function __construct($session, $post) {
			parent::__construct($session);

			$this->post = $post;
		}

		protected function generateURL($action = 'display') {

			switch ($action) {
				default:
				case 'display': return "post.php?id={$this->post->id}";
				case 'edit': return "post.php?id={$this->post->id}&action=edit";
				case 'report': return "post.php?id={$this->post->id}&action=report";
			}
		}

		public function displayReference($active = true, $reactions = '') {
			$rating = $this->generateRating();

			if ($active) {
				$dropdown_menu = $this->generateDropdownMenu();
				$reaction_buttons = $this->generateReactionButtons();
				$action_buttons = $this->generateActionButtons();
			} else {
				$dropdown_menu = '';
				$reaction_buttons = '';
				$action_buttons = '';
			}

			echo <<<EOF
			<div class="post">
				<div class="header">
					{$rating}
					<div class="details">
						<h1>{$this->post->title}</h1>
						<div class="flex small">
							<span class="author">{$this->post->author}</span>
							<span class="date">{$this->post->date}</span>
						</div>
					</div>
					{$dropdown_menu}
				</div>
				<div class="content">
					{$this->post->text}
				</div>
				<div class="flex footer">
					<div class="flex left reactions">
						{$reaction_buttons}
					</div>
					<div class="flex right">
						{$action_buttons}
					</div>
				</div>
				{$reactions}
			</div>
			EOF;
		}

		public function display() {

			static::displayReference();
		}

		public function edit() {
			$save_buttons = $this->generateSaveButtons();

			echo <<<EOF
			<div class="post">
				<div class="flex column" style="gap: 10px">
					<label>
						<span class="label">Title</span>
						<input class="" name="title" type="text" value="{$this->post->title}" />
					</label>
					<label>
						<span class="label">Text</span>
						<textarea class="" rows="5" cols="80">{$this->post->text}</textarea>
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
			EOF;
		}

		protected function generateRating() {
			return '';
		}

		protected function generateDropdownMenu() {
			$items = '';

			if (!$this->session->isAllowed())
				return '';

			if ($this->session->isAuthor($this->post) || $this->session->isAdmin()) {
				$items .= UIComponents::getDropdownItem('Edit', 'edit', $this->generateURL('edit'));
			}

			if ($this->session->isAuthor($this->post) || $this->session->isMod()) {
				$items .= UIComponents::getDropdownItem('Delete', 'delete');
			}

			if (!$this->session->isAuthor($this->post)) {
				$items .= UIComponents::getDropdownItem('Report', 'report', $this->generateURL('report'));
			}

			if ($items == '')
				return '';
			else
				$dropdown = UIComponents::getDropdownMenu($items);

			return UIComponents::getOverflowMenu($dropdown);
		}

		protected function generateReactionButtons($post = null) {
			$buttons = '';

			$reaction_types = ($post) ? $post->reactions : $this->post->reactions;

			if (!$reaction_types) return '';

			foreach ($reaction_types as $type => $stats) {

				if (!$this->session->isLoggedIn())
					$login_prompt = '<div class="tooltip">Sign in to react</div>';
				else
					$login_prompt = '<div class="tooltip">Your account has been disabled</div>';

				$status = $this->session->isAllowed();

				switch ($type) {
					case 'like':
						$buttons .= UIComponents::getTextButton(
								$stats->count_up,
								'thumb_up',
								enabled: $status,
								content: $status ? '' : $login_prompt);
						$buttons .= UIComponents::getTextButton(
								$stats->count_down,
								'thumb_down',
								enabled: $status,
								content: $status ? '' : $login_prompt);
						break;

					case 'usefulness':
						$tooltip = <<<EOF
						<div class="tooltip">
							<span class="material-symbols-outlined"></span>Useful?
							<span class="rate">
								<span class="material-symbols-outlined">star</span>
								<span class="material-symbols-outlined">star</span>
								<span class="material-symbols-outlined">star</span>
								<span class="material-symbols-outlined">star</span>
								<span class="material-symbols-outlined">star</span>
							</span>
						</div>
						EOF;
						$buttons .= UIComponents::getTextButton(
								$stats->average,
								'lightbulb',
								enabled: $status,
								content: $status ? $tooltip : $login_prompt);
						break;

					case 'agreement':
						$tooltip = <<<EOF
						<div class="tooltip">
							<span class="material-symbols-outlined"></span>Agree?
							<span class="rate">
								<span class="material-symbols-outlined">star</span>
								<span class="material-symbols-outlined">star</span>
								<span class="material-symbols-outlined">star</span>
								<span class="material-symbols-outlined">star</span>
								<span class="material-symbols-outlined">star</span>
							</span>
						</div>
						EOF;
						$buttons .= UIComponents::getTextButton(
								$stats->average,
								'thumb_up',
								enabled: $status,
								content: $status ? $tooltip : $login_prompt);
						break;

					case 'spoilage':
						$tooltip = <<<EOF
						<div class="tooltip">
							<span class="material-symbols-outlined"></span>Spoiler level:
							<span class="rate">
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
							</span>
						</div>
						EOF;
						$buttons .= UIComponents::getTextButton(
								$stats->average,
								'speed',
								enabled: $status,
								content: $status ? $tooltip : $login_prompt);
						break;

					default: break;
				}
			}

			return $buttons;
		}

		protected function generateSaveButtons() {
			return UIComponents::getFilledButton('Save changes', 'save', '#');;
		}

		protected function generateActionButtons() {
			return '';
		}
	}

	class RatedPost extends Post {

		protected function generateRating() {
			$rating = $this->post->rating;

			return <<<EOF
			<div class="featured">
				<span class="centered rating">{$rating}</span>
			</div>
			EOF;
		}
	}

	class Comment extends Post {

		protected function generateRating() {
			$icon = '';

			switch ($this->post->rating) {
				case 'ok': $icon = 'thumb_up'; break;
				case 'okma': $icon = 'thumbs_up_down'; break;
				case 'ko': $icon = 'thumb_down'; break;
			}

			$rating = UIComponents::getIcon($icon);

			return <<<EOF
			<div class="featured">
				<span class="centered rating">{$rating}</span>
			</div>
			EOF;
		}
	}

	class Question extends Post {

		public function display() {

			parent::displayReference(reactions: $this->generateAnswers());
		}

		protected function generateActionButtons() {

			if ($this->session->isAllowed())
				return UIComponents::getOutlinedButton('Answer', 'comment', "post.php?action=answer&post={$this->post->id}", cls: 'colored');
		}

		protected function generateAnswers() {
			$html = '';

			$answers = $this->post->answers;

			foreach ($answers as $answer) {
				$selected_answer = (bool) $answer->id == $this->post->featuredAnswer;
				$selected_class = $selected_answer ?
						'selected' :
						'';
				$selected_icon =  $selected_answer ?
						UIComponents::getIcon('check_circle', 'selected_answer') :
						'';

				$reaction_buttons = $this->generateReactionButtons($answer);

				$html .= <<<EOF
				<div class="answer {$selected_class}">
					{$selected_icon}
					<div class="header">
						<div class="flex small">
							<span class="author">{$answer->author}</span>
							<span class="date">{$answer->date}</span>
						</div>
						<div class="right"></div>
					</div>
					<div class="content">
						{$answer->text}
					</div>
					<div class="flex footer reactions">
						{$reaction_buttons}
					</div>
				</div>
				EOF;
			}

			return <<<EOF
			<div class="answers">
				{$html}
			</div>
			EOF;
		}
	}

	class DeletedPost extends Post {

		public function displayReference($active = false, $reactions = '') {

			echo <<<EOF
			<div class="post">
				<div class="header">
					<div class="details">
						<h1>[Deleted Post]</h1>
					</div>
				</div>
				<div class="content">
				</div>
				<div class="flex footer">
					<div class="flex left reactions">
					</div>
					<div class="flex right">
					</div>
				</div>
				{$reactions}
			</div>
			EOF;
		}
	}
?>