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
			$html = '';

			if ($this->session->isAuthor($this->post) || $this->session->isAdmin()) {
				$html .= '<li><a href="" class="flex edit"><span class="material-symbols-outlined"></span><span class="label">Edit</span></a></li>';
			}

			if ($this->session->isAuthor($this->post) || $this->session->isMod()) {
				$html .= '<li><a href="" class="flex delete"><span class="material-symbols-outlined"></span><span class="label">Delete</span></a></li>';
			}

			if (!$this->session->isAuthor($this->post) && $this->session->isRegistered()) {
				$html .= '<li><a href="" class="flex report"><span class="material-symbols-outlined"></span><span class="label">Report</span></a></li>';
			}

			if ($html == '') return $html;

			return <<<EOF
			<button class="right text kebab">
				<span class="material-symbols-outlined"></span>
				<div class="dropdown">
					<ul class="menu">
						{$html}
					</li>
				</div>
			</button>
			EOF;
		}

		protected function generateReactionButtons($post = null) {
			$html = '';

			$reaction_types = ($post) ? $post->reactions : $this->post->reactions;

			if (!$reaction_types) return $html;

			foreach ($reaction_types as $type => $stats) {

				switch ($type) {
					case 'like':
						$html .= <<<EOF
						<button class="text likes">
							<span class="material-symbols-outlined"></span>
							<span class="label">{$stats->count_up}</span>
						</button>
						<button class="text dislikes">
							<span class="material-symbols-outlined"></span>
							<span class="label">{$stats->count_down}</span>
						</button>
						EOF;
						break;

					case 'usefulness':
						$html .= <<<EOF
						<button class="text usefulness">
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
							<span class="material-symbols-outlined"></span><span class="label">{$stats->average}</span>
						</button>
						EOF;
						break;

					case 'agreement':
						$html .= <<<EOF
						<button class="text agreement">
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
							<span class="material-symbols-outlined"></span><span class="label">{$stats->average}</span>
						</button>
						EOF;
						break;

					case 'spoilage':
						$html .= <<<EOF
						<button class="text spoil_level" disabled="disabled">
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
							<span class="material-symbols-outlined"></span><span class="label">{$stats->average}</span>
						</button>
						EOF;
						break;

					default:
						$html .= '<div>Unknown reaction type</div>';
						break;
				}
			}

			return $html;
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

			$rating = "<span class=\"material-symbols-outlined\">{$icon}</span>";

			return <<<EOF
			<div class="featured">
				<span class="centered rating">{$rating}</span>
			</div>
			EOF;
		}
	}

	class Question extends Post {

		protected function generateActionButtons() {
			return <<<EOF
			<a class="button colored answer_compose" href="">
				<span class="material-symbols-outlined"></span>
				<span class="label">Answer</span>
			</a>
			EOF;
		}

		protected function generateAnswers() {
			$html = '';

			$answers = $this->post->answers;

			foreach ($answers as $answer) {
				$selected_answer = (bool) $answer->id == $this->post->featuredAnswer;
				$selected_class = $selected_answer ? 'selected' : '';
				$selected_icon = $selected_answer ? '<span class="material-symbols-outlined"></span>' : '';

				$reaction_buttons = $this->generateReactionButtons($answer);

				$html .= <<<EOF
				<div class="answer {$selected_class}">
					{$selected_icon}
					<div class="header">
						<div class="flex small">
							<span class="author">{$answer->author}</span>
							<span class="date">{$answer->date}</span>
						</div>
						<div class="right flag"><span class="material-symbols-outlined"></span></div>
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