<?php namespace models;

	/* Classe base per un modello, inteso come oggetto che rappresenta un elemento specifico del
	* dominio di interesse (film, post, reazione...) con le sue proprietà caratterizzanti
	*/
	abstract class AbstractModel {
		protected $__errors;
		protected $__source;

		protected function __construct($state) {
			$this->__source = $state;
		}

		/* Restituisce lo stato dell'oggetto sottoforma di array associativo contenente i valori di
		* tutte le sue proprietà non nulle
		*/
		public function getState() {
			$state = array_filter(
					get_object_vars($this),
					fn($property) => isset($property));

			unset($state['__source']);
			unset($state['__errors']);

			return $state;
		}

		/* Restituisce un array associativo con i valori delle sole proprietà indicate in $keys */
		public function getAttributes($keys) {
			return array_intersect_key($this->getState(), array_flip($keys));
		}

		/* Restituisce il prefisso degli ID per il tipo di oggetto $type */
		public static function getPrefix($type) {
			return static::get($type)::ID_PREFIX;
		}

		/* Restituisce il tipo di oggetto rappresentato da $subject:
		*
		*	se istanza di DOMElement, il tipo è costituito dal nome dell'elemento (nodeName);
		*	se istanza di altra classe, il tipo è costituito dal nome della classe (get_class());
		*	se ID alfanumerico di un oggetto, il tipo è determinato dal prefisso.
		*/
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

		/* Valida la proprietà $property come attributo numerico */
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

		/* Valida la proprietà $property come attributo testuale */
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

		/* Genera un'eccezione se i metodi di validazione utilizzati hanno trovato errori */
		protected function checkValidation() {
			if (!empty($this->__errors))
				throw new InvalidDataException('Validation errors!', $this->__errors);
		}

		/* Restituisce la classe corrispondente al tipo $type */
		public static function get($type) {
			return '\models\\'.ucfirst($type);
		}

		/* Restituisce una istanza di tipo $type costruita utilizzando lo stato $state */
		public static function build($type, $state) {
			$class = static::get($type);

			return new $class($state);
		}
	}


	/* Estensione di Exception che include eventuali messaggi di errore generati dai metodi di
	* validazione degli input
	*/
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