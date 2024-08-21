<?php namespace models;

	class XMLDocument {
		protected const DOCUMENT_NAME = '';
		protected static $document;
		protected static $xpath;

		protected static function loadDocument() {
			static::$document = new \DOMDocument('1.0', 'UTF-8');

			$name = static::DOCUMENT_NAME;

			static::$document->load("static/{$name}.xml");
			static::$document->schemaValidate("schemas/{$name}.xsd");
		}

		protected static function saveDocument() {
			$name = static::DOCUMENT_NAME;

			static::$document->schemaValidate("schemas/{$name}.xsd");
			static::$document->save("static/{$name}.xml");
		}

		protected static function queryDocument($query) {
			if (!static::$xpath) {
				static::loadDocument();
				static::$xpath = new \DOMXPath(static::$document);
			}

			return static::$xpath->query($query, static::$document);
		}

		protected static function getElementById($id) {
			if (!static::$document)
				static::loadDocument();

			return static::$document->getElementById($id);
		}

		protected static function replaceElementById($id, $node) {
			if (!static::$document)
				static::loadDocument();

			static::$document->getElementById($id)->replaceWith($node);
			static::saveDocument();
		}

		protected static function appendElement($node) {
			if (!static::$document)
				static::loadDocument();

			static::$document->documentElement->append($node);
			static::saveDocument();
		}
	}
?>