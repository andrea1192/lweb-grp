<?php namespace views;
	
	class Post extends AbstractView {
		protected $post;

		public function __construct($session, $post) {
			parent::__construct($session);

			$this->post = $post;
		}

		public function display() {
			$rating = $this->generateRating();
			$dropdown_menu = $this->generateDropdownMenu();
			$reaction_buttons = $this->generateReactionButtons();
			$action_buttons = $this->generateActionButtons();
			$answers = $this->generateAnswers();

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
				{$answers}
			</div>
			EOF;
		}

		protected function generateRating() {
			return '';
		}

		protected function generateDropdownMenu() {
			$items = '';

			if ($this->session->isAuthor($this->post) || $this->session->isAdmin()) {
				$items .= UIComponents::getDropdownItem('Edit', 'edit');
			}

			if ($this->session->isAuthor($this->post) || $this->session->isMod()) {
				$items .= UIComponents::getDropdownItem('Delete', 'delete');;
			}

			if (!$this->session->isAuthor($this->post) && $this->session->isRegistered()) {
				$items .= UIComponents::getDropdownItem('Report', 'report');;
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

				switch ($type) {
					case 'like':
						$buttons .= UIComponents::getTextButton($stats->count_up, 'thumb_up');
						$buttons .= UIComponents::getTextButton($stats->count_down, 'thumb_down');
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
						$buttons .= UIComponents::getTextButton($stats->average, 'lightbulb', content: $tooltip);
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
						$buttons .= UIComponents::getTextButton($stats->average, 'thumb_up', content: $tooltip);
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
						$buttons .= UIComponents::getTextButton($stats->average, 'speed', content: $tooltip);
						break;

					default: break;
				}
			}

			return $buttons;
		}

		protected function generateActionButtons() {
			return '';
		}

		protected function generateAnswers() {
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

		protected function generateActionButtons() {
			return UIComponents::getOutlinedButton('Answer', 'comment', '#', cls: 'colored');
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
?>