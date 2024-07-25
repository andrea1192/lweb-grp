<?php namespace views;
	
	class Post extends AbstractView {
		public $post;

		public function __construct($session, $post) {
			parent::__construct($session);

			$this->post = $post;
		}

		public function generateDropdownMenu() {
			$html = '';

			if ($this->session->isAuthor($this->post) || $this->session->isMod()) {
				$html .= '<li class="flex edit"><span class="material-symbols-outlined"></span><span class="label">Edit</span></li>';
			}

			if ($this->session->isAuthor($this->post) || $this->session->isAdmin()) {
				$html .= '<li class="flex delete"><span class="material-symbols-outlined"></span><span class="label">Delete</span></li>';
			}

			if ($this->session->isRegistered()) {
				$html .= '<li class="flex report"><span class="material-symbols-outlined"></span><span class="label">Report</span></li>';
			}

			return <<<EOF
			<div class="dropdown">
				<ul>
					{$html}
				</li>
			</div>
			EOF;
		}

		public function generateReactionButtons($post = null) {
			$html = '';

			$reaction_types = ($post) ? $post->reactions : $this->post->reactions;

			if (!$reaction_types) return $html;

			foreach ($reaction_types as $type => $stats) {

				switch ($type) {
					case 'like':
						$html .= <<<EOF
						<button class="text likes">
							<span class="material-symbols-outlined"></span><span class="label">{$stats->count_up}</span>
						</button>
						<button class="text dislikes">
							<span class="material-symbols-outlined"></span><span class="label">{$stats->count_down}</span>
						</button>
						EOF;
						break;

					case 'usefulness':
						$html .= <<<EOF
						<button class="text usefulness" disabled="disabled">
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
						<button class="text agreement" disabled="disabled">
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

		public function generateAnswers() {
			$html = '';

			$answers = $this->post->answers;

			foreach ($answers as $answer) {
				$selected = ($answer->id == $this->post->featuredAnswer) ? 'selected' : '';
				$reaction_buttons = $this->generateReactionButtons($answer);

				$html .= <<<EOF
				<div class="answer {$selected}">
					<span class="material-symbols-outlined"></span>
					<div class="flex header">
						<div class="flex published">
							<span class="author">{$answer->author}</span>
							<span class="date">{$answer->date}</span>
						</div>
						<div class="right flag"><span class="material-symbols-outlined"></span></div>
					</div>
					<div class="content">
						<p>{$answer->text}</p>
					</div>
					<div class="flex footer">
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

		public function render() {

			$rating = (in_array('models\RatedPost', class_parents($this->post))) ? <<<EOF
			<div class="rating">
				<span class="centered">{$this->post->rating}</span>
			</div>
			EOF : '';

			$dropdown_menu = $this->generateDropdownMenu();

			$reaction_buttons = $this->generateReactionButtons();

			$action_buttons = ('models\Question' == get_class($this->post)) ? <<<EOF
			<button class="answer_compose">
				<span class="material-symbols-outlined"></span><span class="label">Answer</span>
			</button>
			EOF : '';

			$answers = ('models\Question' == get_class($this->post)) ?
				$this->generateAnswers() : '';

			echo <<<EOF
			<div class="card post">
				<div class="flex header">
					{$rating}
					<div class="details">
						<h1>{$this->post->title}</h1>
						<div class="flex published">
							<span class="author">{$this->post->author}</span>
							<span class="date">{$this->post->date}</span>
						</div>
					</div>
					<button class="right text kebab">
						<span class="material-symbols-outlined"></span>
						{$dropdown_menu}
					</button>
				</div>
				<div class="content">
					<p>{$this->post->text}</p>
				</div>
				<div class="flex footer">
					<div class="flex left">
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
	}
?>