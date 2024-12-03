<?php namespace models;

	class Posts extends \models\XMLDocument {
		protected const DOCUMENT_NAME = 'posts';

		public static function getMapperForItem($subject) {
			// if (!$subject)
			// 	return '\models\DeletedPosts';

			$class = get_class($subject);

			if ($class == 'DOMElement')
				$name = $subject->nodeName;
			else
				$name = str_replace('models\\', '', strtolower($class));

			switch ($name) {
				case 'comment':
					return '\models\Comments';
				case 'review':
					return '\models\Reviews';
				case 'question':
					return '\models\Questions';
				case 'spoiler':
					return '\models\Spoilers';
				case 'extra':
					return '\models\Extras';
			}
		}

		public function getFeaturedPosts($movie_id, $type = '*') {
			$query = "/posts/{$type}[@status!='deleted' and @movie='{$movie_id}' and @featured='true']";
			$matches = $this->xpath->query($query);

			return new \models\PostList($matches);
		}

		public function getPostsByMovie($movie_id, $type = '*') {
			$query = "/posts/{$type}[@status!='deleted' and @movie='{$movie_id}']";
			$matches = $this->xpath->query($query);

			return new \models\PostList($matches);
		}

		public function getPostsByAuthor($author, $type = '*') {
			$query = "/posts/{$type}[@status!='deleted' and @author='{$author}']";
			$matches = $this->xpath->query($query);

			return new \models\PostList($matches);
		}

		public function getPostById($id) {
			return $this->read($id);
		}
	}

	class Comments extends Posts {
		protected const DOCUMENT_NAME = 'comments';
		protected const ELEMENT_NAME = 'comment';

		public function getCommentsByRequest($movie_id) {
			$query = "/comments/comment[@status!='deleted' and @request='{$movie_id}']";
			$matches = $this->xpath->query($query);

			return new \models\PostList($matches);
		}

		public function getCommentsByAuthor($author) {
			$query = "/comments/comment[@status!='deleted' and @author='{$author}']";
			$matches = $this->xpath->query($query);

			return new \models\PostList($matches);
		}

		public function getCommentById($id) {
			return $this->read($id);
		}
	}


	class PostList extends ElementList {

		public function current(): \models\Post {
			$element = parent::current();

			$type = \models\AbstractModel::getType($element);
			$mapper = \controllers\ServiceLocator::resolve('posts')::getMapper($type);

			$state = $mapper::createStateFromElement($element);
			return \models\AbstractModel::build($type, $state);
		}
	}
?>