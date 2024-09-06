<?php namespace views;
	
	class Post extends AbstractView {
		protected const POST_TYPE = '';
		protected $post;

		protected static function getPostType() {
			return static::POST_TYPE;
		}

		public function __construct($session, $post) {
			parent::__construct($session);

			$this->post = $post;
		}

		public function generateURL($action = 'display') {

			switch ($action) {
				default:
				case 'display': return "post.php?id={$this->post->id}";
				case 'edit': return "post.php?id={$this->post->id}&action=edit";
				case 'save': return "post.php?id={$this->post->id}&action=save";
				case 'delete': return "post.php?id={$this->post->id}&type={$this->getPostType()}&action=delete";
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
			$action = $this->generateURL('save');
			$reference_field = $this->generateReferenceField();
			$special_fields = $this->generateSpecialFields();
			$save_buttons = $this->generateSaveButtons();

			$components = 'views\UIComponents';

			echo <<<EOF
			<form class="post" method="post" action="{$action}">
				<div class="flex fields column">
					{$components::getHiddenInput('type', $this->getPostType())}
					{$components::getHiddenInput('id', $this->post->id)}
					{$reference_field}
					{$components::getHiddenInput('author', $this->post->author)}
					{$components::getHiddenInput('date', $this->post->date)}
					{$components::getTextInput('Title', 'title', $this->post->title)}
					{$special_fields}
					{$components::getTextArea('Text', 'text', $this->post->text)}
					<div class="flex footer">
						<div class="flex left">
							{$save_buttons}
						</div>

						<div class="flex right">
						</div>
					</div>
				</div>
			</form>
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
				$items .= UIComponents::getDropdownItem('Delete', 'delete', $this->generateURL('delete'));
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

		protected function generateReferenceField() {
			return UIComponents::getHiddenInput('movie', $this->post->movie);
		}

		protected function generateSpecialFields() {
			return '';
		}

		protected function generateSaveButtons() {
			return UIComponents::getFilledButton('Save changes', 'save');
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

		protected function generateSpecialFields() {
			return UIComponents::getTextInput('Rating', 'rating', $this->post->rating);
		}
	}

	class Comment extends RatedPost {
		protected const POST_TYPE = 'comment';

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

		protected function generateReferenceField() {
			return UIComponents::getHiddenInput('request', $this->post->request);
		}

		protected function generateSpecialFields() {
			return UIComponents::getTextInput('Rating', 'rating', $this->post->rating);
		}
	}

	class Review extends RatedPost {
		protected const POST_TYPE = 'review';
	}

	class Question extends Post {
		protected const POST_TYPE = 'question';

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

		protected function generateSpecialFields() {
			$fields = '';

			$fields .= UIComponents::getHiddenInput('featured', $this->post->featured ? 'true' : 'false');
			$fields .= UIComponents::getHiddenInput('featuredAnswer', $this->post->featuredAnswer);

			return $fields;
		}
	}

	class Spoiler extends RatedPost {
		protected const POST_TYPE = 'spoiler';
	}

	class Extra extends Post {
		protected const POST_TYPE = 'extra';

		protected function generateSpecialFields() {
			return UIComponents::getTextInput('Reputation', 'reputation', $this->post->reputation);
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