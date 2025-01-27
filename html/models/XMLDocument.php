<?php namespace models;

	require_once('connection.php');

	define('XMLNS_DEF', 'http://www.w3.org/2000/xmlns/');
	define('XSI_DEF', 'http://www.w3.org/2001/XMLSchema-instance');

	/* Classe base per un repository di tipo XML (file XML) */
	abstract class XMLDocument implements IRepository {
		protected const SCHEMAS_ROOT = DIR_SCHEMAS;
		protected const DOCUMENT_ROOT = DIR_STATIC;
		protected const DOCUMENT_NAME = '';

		protected $document;
		protected $xpath;

		/* Restituisce il percorso dello schema che definisce la grammatica del file XML */
		private static function getSchemaPath() {
			return static::SCHEMAS_ROOT.static::DOCUMENT_NAME.'.xsd';
		}

		/* Restituisce il percorso del file XML */
		private static function getDocumentPath() {
			return static::DOCUMENT_ROOT.static::DOCUMENT_NAME.'.xml';
		}

		/* Restituisce l'istanza repository di riferimento per gli oggetti di tipo $type, quella
		* che ne implementa le funzionalità di creazione, recupero, aggiornamento e cancellazione
		* su disco (CRUD) definite da IRepository
		*/
		public static function getRepo($type) {
			$sl = '\\controllers\\ServiceLocator';

			switch ($type) {
				case 'movie':
					return $sl::resolve('movies');
				case 'request':
					return $sl::resolve('requests');

				case 'review':
				case 'question':
				case 'spoiler':
				case 'extra':
					return $sl::resolve('posts');
				case 'comment':
					return $sl::resolve('comments');

				case 'like':
				case 'usefulness':
				case 'agreement':
				case 'spoilage':
					return $sl::resolve('reactions');
				case 'answer':
					return $sl::resolve('answers');
				case 'report':
					return $sl::resolve('reports');
			}
		}

		/* Restituisce la classe mapper di riferimento per gli oggetti di tipo $type, deputata ad
		* estrarre dati dagli elementi recuperati dal disco o ad immettervene di nuovi perchè
		* vengano preservati dopo una modifica
		*/
		public static function getMapper($type) {
			$ns = '\\models\\';

			switch ($type) {
				case 'movie':
					return $ns.'MovieMapper';
				case 'request':
					return $ns.'RequestMapper';

				case 'review':
					return $ns.'ReviewMapper';
				case 'question':
					return $ns.'QuestionMapper';
				case 'spoiler':
					return $ns.'SpoilerMapper';
				case 'extra':
					return $ns.'ExtraMapper';
				case 'comment':
					return $ns.'CommentMapper';

				case 'like':
					return $ns.'LikeMapper';
				case 'usefulness':
					return $ns.'UsefulnessMapper';
				case 'agreement':
					return $ns.'AgreementMapper';
				case 'spoilage':
					return $ns.'SpoilageMapper';
				case 'answer':
					return $ns.'AnswerMapper';
				case 'report':
					return $ns.'ReportMapper';
			}
		}

		public function __construct() {
			$document = static::DOCUMENT_NAME.'.xml';

			if (!$this->loadDocument() && (!str_ends_with($_SERVER['SCRIPT_NAME'], '/install.php')))
				throw new \Exception("Couldn't load {$document}.");
		}

		/* Inizializza il repository, da zero od utilizzando i dati in $source */
		public function init($source = null) {

			if (!is_dir(static::DOCUMENT_ROOT))
				mkdir(static::DOCUMENT_ROOT);

			if (!$source) {
				$this->createDocument();

			} else {
				$doc = $source.static::DOCUMENT_NAME.'.xml';

				if (!is_file($doc))
					throw new \Exception("Couldn't find $doc");

				$this->loadDocument($doc);
				$this->saveDocument();
			}
		}

		/* Ripristina il repository */
		public function restore() {
			if (is_file(static::getDocumentPath()))
				unlink(static::getDocumentPath());

			static::createDocument();
		}

		/* Inizializza il repository da zero. Utilizzato da init(). */
		protected function createDocument() {
			$sch_path = static::getSchemaPath();

			$this->document = new \DOMDocument('1.0', 'UTF-8');

			$root = $this->document->createElement(static::DOCUMENT_NAME);
			$root->setAttributeNS(XMLNS_DEF, 'xmlns:xsi', XSI_DEF);
			$root->setAttributeNS(XSI_DEF, 'noNamespaceSchemaLocation', $sch_path);
			$this->document->appendChild($root);

			$this->saveDocument();
		}

		/* Carica il repository dal disco */
		protected function loadDocument($doc_path = null, $sch_path = null) {
			$doc_path = $doc_path ?? static::getDocumentPath();
			$sch_path = $sch_path ?? static::getSchemaPath();

			if (!is_file($doc_path))
				return false;

			$this->document = new \DOMDocument('1.0', 'UTF-8');
			$lo = $this->document->load($doc_path);
			$vo = $this->document->schemaValidate($sch_path);

			$this->xpath = new \DOMXPath($this->document);

			return ($lo && $vo);
		}

		/* Salva il repository su disco */
		protected function saveDocument($doc_path = null, $sch_path = null) {
			$doc_path = $doc_path ?? static::getDocumentPath();
			$sch_path = $sch_path ?? static::getSchemaPath();

			$this->document->schemaValidate($sch_path);

			return $this->document->save($doc_path);
		}

		public function getDocument() {
			return $this->document;
		}

		// ================================================================================
		// INTERFACCIA AD ALTO LIVELLO
		// ================================================================================

		/* Crea un nuovo elemento di tipo $type, usando $state, e lo aggiunge al repository */
		public function create($type, $state) {
			$repo = static::getRepo($type);
			$mapper = static::getMapper($type);
			$session = \controllers\ServiceLocator::resolve('session');

			$state['id'] = $repo->generateID($type);
			$state['author'] = $session->getUsername();
			$state['date'] = date('c');

			$object = \models\AbstractModel::build($type, $state);
			$element = $mapper::createElementFromState($state);
			$repo->appendElement($element);

			return $object;
		}

		/* Recupera l'elemento identificato da $id dal repository */
		public function read($id) {
			$type = \models\AbstractModel::getType($id);
			$repo = static::getRepo($type);
			$mapper = static::getMapper($type);

			$element = $repo->getElement($id);

			if ($element) {
				$state = $mapper::createStateFromElement($element);
				return \models\AbstractModel::build($type, $state);
			}
		}

		/* Recupera la reazione identificata da $post, $author, $type dal repository */
		public function readReaction($post, $author, $type) {
			$repo = static::getRepo($type);
			$mapper = static::getMapper($type);

			$element = $repo->getReaction($post, $author, $type);

			if ($element) {
				$state = $mapper::createStateFromElement($element);
				return \models\AbstractModel::build($type, $state);
			}
		}

		/* Aggiorna le proprietà dell'elemento $object nel repository, che deve contenere un
		* elemento con proprietà $id corrispondente (vd. replaceElement())
		*/
		public function update($object) {
			$type = \models\AbstractModel::getType($object);
			$repo = static::getRepo($type);
			$mapper = static::getMapper($type);

			$state = $object->getState();
			$element = $mapper::createElementFromState($state);
			$repo->replaceElement($element);

			return $object;
		}

		/* Rimozione non implementata in quanto controller implementa soft-delete */
		// public function delete($object) {}

		// ================================================================================
		// INTERFACCIA A BASSO LIVELLO
		// ================================================================================

		protected function getElement($id) {
			return $this->document->getElementById($id);
		}

		/* Rimpiazza un elemento nel repository corrente, identificandolo con la proprietà $id */
		protected function replaceElement($element) {
			$this->document->getElementById($element->id)->replaceWith($element);
			$this->saveDocument();
		}

		/* Aggiunge un elemento al repository corrente */
		protected function appendElement($node) {
			$this->document->documentElement->append($node);
			$this->saveDocument();
		}

		/* Rimuove un elemento dal repository corrente */
		protected function deleteElement($id) {
			$element = $this->document->getElementById($id);
			$element->parentNode->removeChild($element);

			$this->saveDocument();
		}

		/* Genera un ID appropriato per un elemento di tipo $type */
		protected function generateID($type) {
			$mapper = static::getMapper($type);
			$root = $mapper::DOCUMENT_NAME;
			$elem = $mapper::ELEMENT_NAME;

			$query = "/{$root}/{$elem}/@id";
			$nodes = $this->xpath->evaluate($query);

			if (!$nodes)
				return '';

			$prefix = '';
			$largest = 0;

			foreach ($nodes as $node) {
				preg_match('/([[:alpha:]]+)([[:digit:]])/', $node->nodeValue, $matches);

				$prefix = $matches[1];
				$number = $matches[2];

				if ($number > $largest)
					$largest = $number;
			}

			return $prefix.++$largest;
		}
	}


	class ElementList extends \IteratorIterator implements \Countable {
		protected $count;

		public function __construct($iterator) {
			parent::__construct($iterator);

			$this->count = $iterator->count();
		}

		public function count(): int {

			return $this->count;
		}
	}
?>