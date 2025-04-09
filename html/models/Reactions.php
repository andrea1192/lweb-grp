<?php namespace models;

	class Reactions extends Table {
		protected const DB_VIEW = 'VReactions';
		protected const DB_TABLE = '';
		protected const DB_ATTRIBS = [];
		protected const OB_TYPE = '';
		protected const OB_PRI_KEY = '';

		/* Inizializza il repository */
		public function init($source = null) {

			$this->query(<<<EOF
					CREATE VIEW IF NOT EXISTS VReactions AS
									SELECT author, post, type, CASE
											WHEN type = 'like' THEN 1
											WHEN type = 'dislike' THEN 0
											END AS rating
									FROM Likes
							UNION
									SELECT author, post, 'usefulness' AS type, rating
									FROM Usefulnesses
							UNION
									SELECT author, post, 'agreement' AS type, rating
									FROM Agreements
							UNION
									SELECT author, post, 'spoilage' AS type, rating
									FROM Spoilages
					EOF
			);
		}

		public function getReactionsByPost($post_id, $type = '*') {
			$criteria = ['post' => $post_id];
			$matches = $this->sql_select(static::DB_TABLE, $criteria);
			$objects = [];

			foreach ($matches as $match)
				$objects[] = \models\AbstractModel::build(static::OB_TYPE, $match);

			return $objects;
		}

		public function getReactionCountByPost($post_id, $type) {
			$criteria = [
					'post' => $post_id,
					'type' => $type
			];
			$attributes = 'COUNT(*)';
			$matches = $this->sql_select(static::DB_TABLE, $criteria, $attributes);

			if (count($matches) == 1) {
				return (int) $matches[array_key_first($matches)]['COUNT(*)'];
			}
		}

		public function getReactionAverageByPost($post_id) {
			$criteria = [
					'post' => $post_id
			];
			$attributes = 'AVG(rating)';
			$matches = $this->sql_select(static::DB_TABLE, $criteria, $attributes);

			if (count($matches) == 1) {
				return (float) $matches[array_key_first($matches)]['AVG(rating)'];
			}
		}

		public function getReaction($post_id, $author, $type = null) {
			$criteria = [
					'post' => $post_id,
					'author' => $author
			];
			$matches = $this->sql_select(static::DB_TABLE, $criteria);

			if (count($matches) == 1)
				return \models\AbstractModel::build(static::OB_TYPE, $matches[0]);
		}
	}

	class Likes extends Reactions {
		protected const DB_VIEW = '';
		protected const DB_TABLE = 'Likes';
		protected const DB_ATTRIBS = [
				'author',
				'post',
				'type'
		];
		protected const OB_TYPE = 'like';
		protected const OB_PRI_KEY = ['author', 'post'];

		/* Inizializza il repository */
		public function init($source = null) {

			$this->query(<<<EOF
					CREATE TABLE IF NOT EXISTS Likes (
					author		VARCHAR(160)	NOT NULL REFERENCES Users(username),
					post 		VARCHAR(80)		NOT NULL REFERENCES Posts(id),
					type 		SET(
							'like',
							'dislike'
					),
					PRIMARY KEY(author, post)
					)
					EOF
			);
		}
	}

	class Usefulnesses extends Reactions {
		protected const DB_VIEW = '';
		protected const DB_TABLE = 'Usefulnesses';
		protected const DB_ATTRIBS = [
				'author',
				'post',
				'rating'
		];
		protected const OB_TYPE = 'usefulness';
		protected const OB_PRI_KEY = ['author', 'post'];

		/* Inizializza il repository */
		public function init($source = null) {

			$this->query(<<<EOF
					CREATE TABLE IF NOT EXISTS Usefulnesses (
					author		VARCHAR(160)	NOT NULL REFERENCES Users(username),
					post 		VARCHAR(80)		NOT NULL REFERENCES Posts(id),
					rating		INT 			NOT NULL,
					CONSTRAINT 	rating_dom CHECK (rating BETWEEN 1 AND 5),
					PRIMARY KEY(author, post)
					)
					EOF
			);
		}
	}

	class Agreements extends Reactions {
		protected const DB_VIEW = '';
		protected const DB_TABLE = 'Agreements';
		protected const DB_ATTRIBS = [
				'author',
				'post',
				'rating'
		];
		protected const OB_TYPE = 'agreement';
		protected const OB_PRI_KEY = ['author', 'post'];

		/* Inizializza il repository */
		public function init($source = null) {

			$this->query(<<<EOF
					CREATE TABLE IF NOT EXISTS Agreements (
					author		VARCHAR(160)	NOT NULL REFERENCES Users(username),
					post 		VARCHAR(80)		NOT NULL REFERENCES Posts(id),
					rating		INT 			NOT NULL,
					CONSTRAINT 	rating_dom CHECK (rating BETWEEN 1 AND 5),
					PRIMARY KEY(author, post)
					)
					EOF
			);
		}
	}

	class Spoilages extends Reactions {
		protected const DB_VIEW = '';
		protected const DB_TABLE = 'Spoilages';
		protected const DB_ATTRIBS = [
				'author',
				'post',
				'rating'
		];
		protected const OB_TYPE = 'spoilage';
		protected const OB_PRI_KEY = ['author', 'post'];

		/* Inizializza il repository */
		public function init($source = null) {

			$this->query(<<<EOF
					CREATE TABLE IF NOT EXISTS Spoilages (
					author		VARCHAR(160)	NOT NULL REFERENCES Users(username),
					post 		VARCHAR(80)		NOT NULL REFERENCES Posts(id),
					rating		INT 			NOT NULL,
					CONSTRAINT 	rating_dom CHECK (rating BETWEEN 1 AND 10),
					PRIMARY KEY(author, post)
					)
					EOF
			);
		}
	}

	class Reports extends Reactions {
		protected const DB_VIEW = '';
		protected const DB_TABLE = 'Reports';
		protected const DB_ATTRIBS = [
				'author',
				'post',
				'status',
				'date',
				'message',
				'response'
		];
		protected const OB_TYPE = 'report';
		protected const OB_PRI_KEY = ['author', 'post'];

		/* Inizializza il repository */
		public function init($source = null) {

			$this->query(<<<EOF
					CREATE TABLE IF NOT EXISTS Reports (
					author		VARCHAR(160)	NOT NULL REFERENCES Users(username),
					post 		VARCHAR(80)		NOT NULL REFERENCES Posts(id),
					status 		SET(
							'open',
							'closed',
							'accepted',
							'rejected'
					) DEFAULT 'open',
					date		TIMESTAMP		DEFAULT CURRENT_TIMESTAMP,
					message 	TEXT,
					response	TEXT,
					PRIMARY KEY(author, post)
					)
					EOF
			);
		}

		public function getReports() {
			return $this->readAll();
		}

		public function getReportsByAuthor($author) {
			$criteria = ['author' => $author];
			$matches = $this->sql_select(static::DB_TABLE, $criteria);
			$objects = [];

			foreach ($matches as $match)
				$objects[] = \models\AbstractModel::build(static::OB_TYPE, $match);

			return $objects;
		}
	}
?>