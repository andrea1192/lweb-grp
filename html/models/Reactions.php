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

	class Answers extends \models\XMLDocument {
		protected const DOCUMENT_NAME = 'answers';
		protected static $document;
		protected static $xpath;

		public static function getAnswersByPost($post_id) {
			$query = "/answers/answer[@post='{$post_id}']";
			$matches = self::queryDocument($query);

			return new \models\ReactionList($matches);
		}

		public static function getAnswersByAuthor($author) {
			$query = "/answers/answer[@author='{$author}']";
			$matches = self::queryDocument($query);

			return new \models\ReactionList($matches);
		}
	}

	class Reports extends \models\XMLDocument {
		protected const DOCUMENT_NAME = 'reports';
		protected static $document;
		protected static $xpath;

		public static function getReports() {
			$query = "/reports/*";
			$matches = self::queryDocument($query);

			return new \models\ReactionList($matches);
		}

		public static function getReportsByAuthor($author) {
			$query = "/reports/report[@author='{$author}']";
			$matches = self::queryDocument($query);

			return new \models\ReactionList($matches);
		}

		public static function getReportByPostIdAuthor($post_id, $author) {
			$query = "/reports/report[@post='{$post_id}' and @author='{$author}']";
			$match = self::queryDocument($query)->item(0);

			return \models\Reaction::generateReaction($match);
		}
	}

	class ReactionList extends \IteratorIterator {

		public function current(): \models\Reaction {

			return \models\Reaction::generateReaction(parent::current());
		}
	}
?>