<?php namespace views;

	class UIComponents {
		private const ICONS_CLASS = 'material-symbols-outlined';

		public static function getIcon($icon, $cls = '') {
			$icons = static::ICONS_CLASS;

			return "<span class=\"icon {$icons} {$cls}\">{$icon}</span>";
		}

		public static function getOverlay($label, $icon, $cls = '') {
			$icon = static::getIcon($icon);

			return <<<EOF
			<div class="overlay {$cls}">
				{$icon}
				<span class="label">{$label}</span>
			</div>
			EOF;
		}

		private static function generateButton($type, $label, $icon, $enabled, $cls, $content) {

			if (!empty($icon))
				$icon = static::getIcon($icon);

			if (!empty($label) || $label == '0')
				$label = "<span class=\"label\">{$label}</span>";

			$disabled = (!$enabled) ? 'disabled="disabled"' : '';

			return <<<EOF
			<button class="{$type} {$cls}" {$disabled}>
				{$icon}
				{$label}
				{$content}
			</button>
			EOF;
		}

		private static function generateActionButton($type, $label, $icon, $href, $cls, $content) {

			if (!empty($icon))
				$icon = static::getIcon($icon);

			if (!empty($label) || $label == '0')
				$label = "<span class=\"label\">{$label}</span>";

			return <<<EOF
			<a class="button {$type} {$cls}" href="{$href}">
				{$icon}
				{$label}
				{$content}
			</a>
			EOF;
		}

		public static function getButton(
				$type = '',
				$label = '',
				$icon = '',
				$href = '',
				$enabled = true,
				$cls = '',
				$content = '') {

			if (empty($href))
				return static::generateButton($type, $label, $icon, $enabled, $cls, $content);
			else
				return static::generateActionButton($type, $label, $icon, $href, $cls, $content);
		}

		public static function getTextButton(
				$label = '',
				$icon = '',
				$href = '',
				$enabled = true,
				$cls = '',
				$content = '') {
			return static::getButton('text', $label, $icon, $href, $enabled, $cls, $content);
		}

		public static function getOutlinedButton(
				$label = '',
				$icon = '',
				$href = '',
				$enabled = true,
				$cls = '',
				$content = '') {
			return static::getButton('', $label, $icon, $href, $enabled, $cls, $content);
		}

		public static function getTonalButton(
				$label = '',
				$icon = '',
				$href = '',
				$enabled = true,
				$cls = '',
				$content = '') {
			return static::getButton('tonal', $label, $icon, $href, $enabled, $cls, $content);
		}

		public static function getFilledButton(
				$label = '',
				$icon = '',
				$href = '',
				$enabled = true,
				$cls = '',
				$content = '') {
			return static::getButton('filled', $label, $icon, $href, $enabled, $cls, $content);
		}

		public static function getFAB(
				$label = '',
				$icon = '',
				$href = '',
				$enabled = true,
				$cls = '',
				$content = '') {
			return static::getButton('fab', $label, $icon, $href, $enabled, $cls, $content);
		}

		public static function getOverflowMenu($dropdown) {
			return static::getButton('text', '', 'more_vert', cls: 'right', content: $dropdown);
		}

		public static function getDropdownMenu($items, $header = '') {

			return <<<EOF
			<div class="dropdown">
				{$header}
				<ul class="menu">
					{$items}
				</ul>
			</div>
			EOF;
		}

		public static function getDropdownItem($label = '', $icon = '', $href = '', $cls = '') {
			$icon = static::getIcon($icon);

			return <<<EOF
			<li>
				<a class="flex {$cls}" href="{$href}">
					{$icon}
					<span class="label">{$label}</span>
				</a>
			</li>
			EOF;
		}

		public static function getTextContent($text) {
			return "<div class=\"content\">{$text}</div>";
		}

		public static function getHiddenInput($name, $value = '') {
			return <<<EOF
			<input value="{$value}" name="{$name}" type="hidden" />
			EOF;
		}

		public static function getPasswordInput($label, $name, $value = '', $cls = '') {
			return <<<EOF
			<label>
				<span class="label">{$label}</span>
				<input class="{$cls}" value="{$value}" name="{$name}" type="password" />
			</label>
			EOF;
		}

		public static function getTextInput($label, $name, $value = '', $cls = '') {
			return <<<EOF
			<label>
				<span class="label">{$label}</span>
				<input class="{$cls}" value="{$value}" name="{$name}" type="text" />
			</label>
			EOF;
		}

		public static function getTextArea($label, $name, $value = '', $cls = '') {
			return <<<EOF
			<label>
				<span class="label">{$label}</span>
				<textarea class="{$cls}" name="{$name}" rows="5" cols="80">{$value}</textarea>
			</label>
			EOF;
		}

		public static function getFilledTextInput($label, $name, $value = '', $cls = '') {
			return static::getTextInput($label, $name, $value, "filled {$cls}");
		}

		public static function getFilledTextArea($label, $name, $value = '', $cls = '') {
			return static::getTextArea($label, $name, $value, "filled {$cls}");
		}
	}
?>