<?php namespace models;

	require_once('models/XMLDocument.php');
	require_once('models/Reaction.php');

	class Reactions extends \models\XMLDocument {
		protected const DOCUMENT_NAME = 'reactions';
		protected static $document;
		protected static $xpath;

		public static function getReactionsByPost($post_id, $type = '*') {
			$query = "/reactions/{$type}[@post='{$post_id}']";
			$matches = self::queryDocument($query);

			return new \models\ReactionList($matches);
		}

		public static function getReactionsByAuthor($author, $type = '*') {
			$query = "/reactions/{$type}[@author='{$author}']";
			$matches = self::queryDocument($query);

			return new \models\ReactionList($matches);
		}

		public static function getReactionCountByPost($post_id, $type, $type_bin) {
			$query = "/reactions/{$type}[@post='{$post_id}' and @type='{$type_bin}']";
			$matches = self::queryDocument($query);

			return count($matches);
		}

		public static function getReactionAverageByPost($post_id, $type) {
			$query = "/reactions/{$type}[@post='{$post_id}']";
			$matches = self::queryDocument($query);

			$sum = 0;

			foreach ($matches as $match) {
				$reaction = \models\Reaction::generateReaction($match);
				$sum += $reaction->rating;
			}

			return (count($matches)) ? ($sum / count($matches)) : 0;
		}
	}

	class ReactionList extends \IteratorIterator {

		public function current(): \models\Reaction {

			return \models\Reaction::generateReaction(parent::current());
		}
	}
?>