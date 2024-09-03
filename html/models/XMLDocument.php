<?php namespace models;

	abstract class XMLDocument {
		protected const SCHEMAS_ROOT = 'schemas/';
		protected const DOCUMENT_ROOT = 'static/';
		protected const DOCUMENT_NAME = '';
		protected const ROOT_ELEMENT = '';

		protected $document;
		protected $xpath;

		private static function getSchemaPath() {
			return static::SCHEMAS_ROOT.static::DOCUMENT_NAME.'.xsd';
		}

		private static function getDocumentPath() {
			return static::DOCUMENT_ROOT.static::DOCUMENT_NAME.'.xml';
		}

		protected static function getMapper($mapper) {
			return \controllers\ServiceLocator::resolve($mapper);
		}

		public function __construct() {
			$this->loadDocument();
		}

		protected function loadDocument() {
			$this->document = new \DOMDocument('1.0', 'UTF-8');

			$this->document->load(static::getDocumentPath());
			$this->document->schemaValidate(static::getSchemaPath());

			$this->xpath = new \DOMXPath($this->document);
		}

		protected function saveDocument() {
			$this->document->schemaValidate(static::getSchemaPath());
			$this->document->save(static::getDocumentPath());
		}

		protected function queryDocument($query) {
			return $this->xpath->query($query, $this->document);
		}

		protected function getElementById($id) {
			return $this->document->getElementById($id);
		}

		public function saveObject($object) {
			$element = $this->createElementFromObject($object);

			if ($this->getElementById($object->id))
				$this->replaceElement($object->id, $element);
			else
				$this->appendElement($element);
		}

		protected function replaceElement($id, $node) {
			$this->document->getElementById($id)->replaceWith($node);
			$this->saveDocument();
		}

		protected function appendElement($node) {
			$this->document->documentElement->append($node);
			$this->saveDocument();
		}
	}
?>