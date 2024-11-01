<?php namespace models;

	abstract class AbstractModel {

		public function getState() {
			return array_filter(
					get_object_vars($this),
					fn($property) => isset($property));
		}

		public static function getType($subject) {

			if (is_object($subject)) {
				$class = get_class($subject);

				if ($class == 'DOMElement')
					return $subject->nodeName;
				else
					return str_replace('models\\', '', strtolower($class));

			} else {
				preg_match('/([[:alpha:]]+)([[:digit:]])/', $subject, $matches);

				$prefix = $matches[1];
				$number = $matches[2];

				switch ($prefix) {
					case Movie::ID_PREFIX:
						return 'movie';
					case Request::ID_PREFIX:
						return 'request';

					case Review::ID_PREFIX:
						return 'review';
					case Question::ID_PREFIX:
						return 'question';
					case Spoiler::ID_PREFIX:
						return 'spoiler';
					case Extra::ID_PREFIX:
						return 'extra';
					case Comment::ID_PREFIX:
						return 'comment';

					case Answer::ID_PREFIX:
						return 'answer';
				}
			}
		}

		public static function build($type, $state) {

			switch ($type) {
				case 'movie':
					return new Movie($state);
				case 'request':
					return new Request($state);

				case 'review':
					return new Review($state);
				case 'question':
					return new Question($state);
				case 'spoiler':
					return new Spoiler($state);
				case 'extra':
					return new Extra($state);
				case 'comment':
					return new Comment($state);

				case 'like':
					return new Like($state);
				case 'usefulness':
					return new Usefulness($state);
				case 'agreement':
					return new Agreement($state);
				case 'spoilage':
					return new Spoilage($state);
				case 'answer':
					return new Answer($state);
				case 'report':
					return new Report($state);
			}
		}
	}