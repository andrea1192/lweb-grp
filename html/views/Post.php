<?php namespace views;

	require_once('views/Reaction.php');
	
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
			$URL = "post.php?id={$this->post->id}&type={$this->getPostType()}";

			switch ($action) {
				default:
				case 'display':
					break;
				case 'edit':
				case 'save':
				case 'answer':
				case 'report':
				case 'delete':
				case 'elevate':
					$URL .= "&action={$action}";
					break;
			}

			return htmlspecialchars($URL, ENT_QUOTES | ENT_SUBSTITUTE | ENT_XHTML);
		}

		public function displayReference($active = true, $content = '', $reactions = '') {
			$rating = $this->generateRating();
			$status = ($this->post->status == 'active') ? '' : UIComponents::getIcon('delete', cls: 'translate');

			if ($active) {
				$dropdown_menu = $this->generateDropdownMenu();
				$reaction_buttons = Reaction::generateReactionButtons($this->post->reactions);
				$action_buttons = $this->generateActionButtons();
			} else {
				$dropdown_menu = '';
				$reaction_buttons = '';
				$action_buttons = '';
			}

			if (empty($content))
				$content = $this->post->text;

			echo <<<EOF
			<div class="post">
				<div class="header">
					{$rating}
					<div class="details">
						<h1>{$this->post->title}{$status}</h1>
						<div class="flex small">
							<span class="author">{$this->post->author}</span>
							<span class="date">{$this->post->date}</span>
						</div>
					</div>
					{$dropdown_menu}
				</div>
				<div class="flex content">
					{$content}
				</div>
				<div class="flex footer">
					<div class="flex left">
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
				<div class="flex column">
					{$components::getHiddenInput('type', $this->getPostType())}
					{$components::getHiddenInput('id', $this->post->id)}
					{$components::getHiddenInput('status', $this->post->status)}
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
			$ratings = [
				'ok' => 'ok: This content should be accepted',
				'okma' => 'okma: This content needs some work',
				'ko' => 'ko: This content should be rejected'
			];

			foreach ($ratings as $rating => $label)
				$options[] = UIComponents::getSelectOption($label, $rating);

			return UIComponents::getSelect('Rating', 'rating', $options);
		}
	}

	class Review extends RatedPost {
		protected const POST_TYPE = 'review';
	}

	class Question extends Post {
		protected const POST_TYPE = 'question';

		public function displayFeatured() {
			parent::displayReference(active: false, reactions: $this->generateAnswers(featuredOnly: true));
		}

		public function display() {
			parent::displayReference(reactions: $this->generateAnswers());
		}

		protected function generateActionButtons() {
			$html = '';

			if ($this->session->isMod() && !$this->post->featured)
				$html .= UIComponents::getTextButton('Elevate question', 'verified', $this->generateURL('elevate'));

			if ($this->session->isAllowed())
				$html .= UIComponents::getOutlinedButton('Answer', 'comment', $this->generateURL('answer'), cls: 'colored-blue');

			return $html;
		}

		protected function generateAnswers($featuredOnly = false) {
			$html = '';

			if ($featuredOnly) {
				$answer = $this->getMapper('answers')->getFeaturedAnswer($this->post->id);

				if ($answer) {
					$view = \views\Reaction::factoryMethod($this->session, $answer);
					$html .= $view->generateDisplay(active: false, selected: true);
				}

			} else {
				$answers = $this->post->answers;

				foreach ($answers as $answer) {
					$selected = (bool) ($answer->id == $this->post->featuredAnswer);

					$view = \views\Reaction::factoryMethod($this->session, $answer);
					$html .= $view->generateDisplay(selected: $selected);
				}
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

		public function display() {

			if ($_SERVER['SCRIPT_NAME'] == '/post.php')
				parent::displayReference(content: $this->post->text);
			else
				parent::displayReference(active: false, content: UIComponents::getFilledButton('Read spoiler', 'visibility', $this->generateURL()));
		}
	}

	class Extra extends Post {
		protected const POST_TYPE = 'extra';

		public function display() {

			if ($this->session->getReputation() >= $this->post->reputation)
				parent::displayReference();
			else
				parent::displayReference(active: false, content: 'You don\'t have enough reputation to read this post.');
		}

		protected function generateSpecialFields() {
			return UIComponents::getTextInput('Reputation', 'reputation', $this->post->reputation);
		}
	}

	// class DeletedPost extends Post {

	// 	public function displayReference($active = false, $reactions = '') {

	// 		echo <<<EOF
	// 		<div class="post">
	// 			<div class="header">
	// 				<div class="details">
	// 					<h1>[Deleted Post]</h1>
	// 				</div>
	// 			</div>
	// 			<div class="content">
	// 			</div>
	// 			<div class="flex footer">
	// 				<div class="flex left reactions">
	// 				</div>
	// 				<div class="flex right">
	// 				</div>
	// 			</div>
	// 			{$reactions}
	// 		</div>
	// 		EOF;
	// 	}
	// }
?>