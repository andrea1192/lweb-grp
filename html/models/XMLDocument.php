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

		protected static function queryDocument($query) {

			if (!(static::$document)) {
				static::loadDocument();
				static::$xpath = new \DOMXPath(static::$document);
			}

			return static::$xpath->query($query, static::$document);
		}
	}
?>