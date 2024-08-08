<?php namespace views;

	class UIComponents {
		private const ICONS_CLASS = 'material-symbols-outlined';

		public static function getIcon($icon) {
			$icons = static::ICONS_CLASS;

			return "<span class=\"{$icons}\">{$icon}</span>";
		}

		public static function getOverlay($label, $icon, $cls = '') {
			$icons = static::ICONS_CLASS;

			return <<<EOF
			<div class="overlay {$cls}">
				<span class="{$icons}">{$icon}</span>
				<span class="label">{$label}</span>
			</div>
			EOF;
		}

		private static function generateButton($type, $label, $icon, $cls, $content) {
			$icons = static::ICONS_CLASS;

			if (!empty($icon))
				$icon = "<span class=\"{$icons}\">{$icon}</span>";

			if (!empty($label) || $label == '0')
				$label = "<span class=\"label\">{$label}</span>";

			return <<<EOF
			<button class="{$type} {$cls}">
				{$icon}
				{$label}
				{$content}
			</button>
			EOF;
		}

		private static function generateActionButton($type, $label, $icon, $href, $cls, $content) {
			$icons = static::ICONS_CLASS;

			if (!empty($icon))
				$icon = "<span class=\"{$icons}\">{$icon}</span>";

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
				$cls = '',
				$content = '') {

			if (empty($href))
				return static::generateButton($type, $label, $icon, $cls, $content);
			else
				return static::generateActionButton($type, $label, $icon, $href, $cls, $content);
		}

		public static function getTextButton(
				$label = '',
				$icon = '',
				$href = '',
				$cls = '',
				$content = '') {
			return static::getButton('text', $label, $icon, $href, $cls, $content);
		}

		public static function getOutlinedButton(
				$label = '',
				$icon = '',
				$href = '',
				$cls = '',
				$content = '') {
			return static::getButton('', $label, $icon, $href, $cls, $content);
		}

		public static function getTonalButton(
				$label = '',
				$icon = '',
				$href = '',
				$cls = '',
				$content = '') {
			return static::getButton('tonal', $label, $icon, $href, $cls, $content);
		}

		public static function getFilledButton(
				$label = '',
				$icon = '',
				$href = '',
				$cls = '',
				$content = '') {
			return static::getButton('filled', $label, $icon, $href, $cls, $content);
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
			$icons = static::ICONS_CLASS;

			return <<<EOF
			<li>
				<a class="flex {$cls}" href="{$href}">
					<span class="{$icons}">{$icon}</span>
					<span class="label">{$label}</span>
				</a>
			</li>
			EOF;
		}
	}
?>