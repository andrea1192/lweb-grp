<?php namespace models;

	abstract class AbstractModel {
		protected $__errors;
		protected $__source;

		protected function __construct($state) {
			$this->__source = $state;
		}

		public function getState() {
			$state = array_filter(
					get_object_vars($this),
					fn($property) => isset($property));

			unset($state['__source']);
			unset($state['__errors']);

			return $state;
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

		protected function validateNumeric(
				$property,
				$required = true,
				$min = PHP_INT_MIN,
				$max = PHP_INT_MAX
			) {

			if (empty($this->__source[$property])) {

				if ($required) {
					$this->__errors[$property] = "{$property} is required";
				}
				return;
			}

			if (!is_numeric($this->__source[$property])) {
				$this->__errors[$property] = "{$property} must be numeric";
				return;
			}

			if ($this->__source[$property] < $min || $this->__source[$property] > $max) {
				$this->__errors[$property] = "{$property} must be between {$min} and {$max}";
				return $this->__source[$property];
			}

			return $this->__source[$property];
		}


		protected function validateString(
				$property,
				$required = true
			) {

			if (empty($this->__source[$property])) {

				if ($required) {
					$this->__errors[$property] = "{$property} is required";
				}
				return;
			}

			return $this->__source[$property];
		}

		protected function checkValidation() {
			if (!empty($this->__errors))
				throw new InvalidDataException('Validation errors!', $this->__errors);
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


	class InvalidDataException extends \Exception {
		private $__errors;

		public function __construct($message, $errors) {
			parent::__construct($message);

			$this->__errors = $errors;
		}

		public function getErrors() {
			return $this->__errors;
		}
	}