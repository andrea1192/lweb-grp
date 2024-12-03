<?php namespace models;

	class Reactions extends \models\XMLDocument {
		protected const DOCUMENT_NAME = 'reactions';

		public static function getMapperForItem($subject) {
			$class = get_class($subject);

			if ($class == 'DOMElement')
				$name = $subject->nodeName;
			else
				$name = str_replace('models\\', '', strtolower($class));

			switch ($name) {
				case 'like':
					return '\models\Likes';
				case 'usefulness':
					return '\models\Usefulnesses';
				case 'agreement':
					return '\models\Agreements';
				case 'spoilage':
					return '\models\Spoilages';
				case 'answer':
					return '\models\Answers';
				case 'report':
					return '\models\Reports';
			}
		}

		public function getReactionsByPost($post_id, $type = '*') {
			$query = "/reactions/{$type}[@post='{$post_id}']";
			$matches = $this->xpath->query($query);

			return new \models\ReactionList($matches);
		}

		public function getReactionsByAuthor($author, $type = '*') {
			$query = "/reactions/{$type}[@author='{$author}']";
			$matches = $this->xpath->query($query);

			return new \models\ReactionList($matches);
		}

		public function getReactionCountByPost($post_id, $type, $type_bin) {
			$query = "/reactions/{$type}[@post='{$post_id}' and @type='{$type_bin}']";
			$matches = $this->xpath->query($query);

			return count($matches);
		}

		public function getReactionAverageByPost($post_id, $type) {
			$query = "/reactions/{$type}[@post='{$post_id}']";
			$matches = $this->xpath->query($query);
			$mapper = static::getMapper($type);

			$sum = 0;

			foreach ($matches as $match) {
				$state = $mapper::createStateFromElement($match);
				$sum += $state['rating'];
			}

			return (count($matches)) ? ($sum / count($matches)) : 0;
		}

		public function getReaction($post_id, $author, $type) {
			$query = "/*/{$type}[@post='{$post_id}' and @author='{$author}']";
			$match = $this->xpath->query($query)->item(0);

			return $match;
		}

		protected function replaceElement($element) {
			$post_id = $element->getAttribute('post');
			$author = $element->getAttribute('author');
			$type = $element->tagName;

			$query = "/*/{$type}[@post='{$post_id}' and @author='{$author}']";
			$this->xpath->query($query)->item(0)->replaceWith($element);
			$this->saveDocument();
		}
	}

	class Answers extends Reactions {
		protected const DOCUMENT_NAME = 'answers';
		protected const ELEMENT_NAME = 'answer';

		public function getFeaturedAnswer($post_id) {
			$answer_id =
					\controllers\ServiceLocator::resolve('posts')->getPostById($post_id)->featuredAnswer;

			if ($answer_id)
				return $this->getAnswerById($answer_id);
		}

		public function getAnswersByPost($post_id) {
			$query = "/answers/answer[@post='{$post_id}']";
			$matches = $this->xpath->query($query);

			return new \models\ReactionList($matches);
		}

		public function getAnswersByAuthor($author) {
			$query = "/answers/answer[@author='{$author}']";
			$matches = $this->xpath->query($query);

			return new \models\ReactionList($matches);
		}

		public function getAnswerById($id) {
			return $this->read($id);
		}
	}

	class Reports extends Reactions {
		protected const DOCUMENT_NAME = 'reports';
		protected const ELEMENT_NAME = 'report';

		public function getReports() {
			$query = "/reports/*";
			$matches = $this->xpath->query($query);

			return new \models\ReactionList($matches);
		}

		public function getReportsByAuthor($author) {
			$query = "/reports/report[@author='{$author}']";
			$matches = $this->xpath->query($query);

			return new \models\ReactionList($matches);
		}

		protected function replaceElement($element) {
			$post_id = $element->getAttribute('post');
			$author = $element->getAttribute('author');
			$date = $element->getAttribute('date');
			$type = $element->tagName;

			$query = "/*/{$type}[@post='{$post_id}' and @author='{$author}' and @date='{$date}']";
			$this->xpath->query($query)->item(0)->replaceWith($element);
			$this->saveDocument();
		}
	}

	class ReactionList extends \IteratorIterator {

		public function current(): \models\Reaction {
			$element = parent::current();

			$type = \models\AbstractModel::getType($element);
			$mapper = \controllers\ServiceLocator::resolve('reactions')::getMapper($type);

			$state = $mapper::createStateFromElement($element);
			return \models\AbstractModel::build($type, $state);
		}
	}
?>