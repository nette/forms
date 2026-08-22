<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms;

use Nette;
use Nette\Utils\{Html, Image, Strings};
use function is_array, is_scalar, is_string;


/**
 * Forms helpers.
 */
final class Helpers
{
	use Nette\StaticClass;

	private const UnsafeNames = [
		'attributes', 'children', 'elements', 'focus', 'length', 'reset', 'style', 'submit', 'onsubmit', 'form',
		'presenter', 'action',
	];


	/**
	 * Extracts and sanitizes submitted form data for single control.
	 * @param  mixed[]  $data
	 * @param  int  $type  type Form::DataText, DataLine, DataFile, DataArray
	 * @return string|mixed[]|Nette\Http\FileUpload|null
	 * @deprecated use getSubmittedData()
	 */
	#[\Deprecated('use getSubmittedData()')]
	public static function extractHttpData(
		array $data,
		string $htmlName,
		int $type,
	): string|array|Nette\Http\FileUpload|null
	{
		trigger_error(__METHOD__ . '() is deprecated, use getSubmittedData()', E_USER_DEPRECATED);
		$name = explode('[', str_replace(['[]', ']', '.'], ['', '', '_'], $htmlName));
		$value = Nette\Utils\Arrays::get($data, $name, null);
		$type = str_ends_with($htmlName, '[]')
			? $type | Form::DataList
			: $type & ~Form::DataArray;
		return static::sanitizeData($type, $value);
	}


	/**
	 * Sanitizes a submitted value according to the given data type (Form::DataText, DataLine, DataFile,
	 * optionally combined with DataArray to keep array keys or DataList to renumber them).
	 * @return string|mixed[]|Nette\Http\FileUpload|null
	 */
	public static function sanitizeData(int $type, mixed $value): string|array|Nette\Http\FileUpload|null
	{
		if (($type & Form::DataList) || ($type & Form::DataArray)) {
			if (!is_array($value)) {
				return [];
			}

			$itype = $type & ~(Form::DataList | Form::DataArray);
			if ($itype) {
				foreach ($value as $k => $v) {
					$value[$k] = $v = static::sanitizeData($itype, $v);
					if ($v === null) {
						unset($value[$k]);
					}
				}
			}

			return $type & Form::DataArray
				? $value
				: array_values($value);

		} elseif ($type === Form::DataText) {
			return is_scalar($value)
				? Strings::unixNewLines((string) $value)
				: null;

		} elseif ($type === Form::DataLine) {
			return is_scalar($value)
				? Strings::trim(strtr((string) $value, "\r\n", '  '))
				: null;

		} elseif ($type === Form::DataFile) {
			return $value instanceof Nette\Http\FileUpload ? $value : null;

		} else {
			throw new Nette\InvalidArgumentException('Unknown data type');
		}
	}


	/**
	 * Returns the path of component names between the form and the component (e.g. 'person-name').
	 */
	public static function lookupPathToForm(Nette\ComponentModel\IComponent $component): string
	{
		$path = [];
		while (!$component instanceof Form) {
			$name = $component->getName();
			$parent = $component->getParent();
			if ($name === null || $parent === null) {
				throw new Nette\InvalidStateException('Component is not attached to a form.');
			}

			$path[] = $name;
			$component = $parent;
		}

		return implode(Nette\ComponentModel\IComponent::NameSeparator, array_reverse($path));
	}


	/**
	 * Converts a component path (e.g. 'form-person-name') to the HTML name attribute format (e.g. 'person[name]').
	 */
	public static function generateHtmlName(string $id): string
	{
		$name = str_replace(Nette\ComponentModel\IComponent::NameSeparator, '][', $id, $count);
		if ($count) {
			$pos = strpos($name, ']');
			assert($pos !== false);
			$name = substr_replace($name, '', $pos, 1) . ']';
		}

		return static::sanitizeHtmlName($name);
	}


	/**
	 * Prefixes names that would clash with JavaScript form properties or are numeric,
	 * mirroring the transformation applied by generateHtmlName().
	 */
	public static function sanitizeHtmlName(string $name): string
	{
		return is_numeric($name) || in_array($name, self::UnsafeNames, true)
			? '_' . $name
			: $name;
	}


	/**
	 * Exports validation rules into a JSON-serializable structure for the data-nette-rules attribute.
	 * @return list<array<string, mixed>>
	 * @deprecated use Nette\Forms\ClientRuleExporter::export()
	 */
	#[\Deprecated('use Nette\Forms\ClientRuleExporter::export()')]
	public static function exportRules(Rules $rules): array
	{
		return ClientRuleExporter::export($rules);
	}


	/**
	 * Generates an HTML list of labeled inputs (radio buttons or checkboxes).
	 * @param  mixed[]  $items  value => label pairs
	 * @param  ?array<string, mixed>  $inputAttrs
	 * @param  ?array<string, mixed>  $labelAttrs
	 */
	public static function createInputList(
		array $items,
		?array $inputAttrs = null,
		?array $labelAttrs = null,
		Html|string|null $wrapper = null,
	): string
	{
		[$inputAttrs, $inputTag] = self::prepareAttrs($inputAttrs, 'input');
		[$labelAttrs, $labelTag] = self::prepareAttrs($labelAttrs, 'label');
		$res = '';
		$input = Html::el();
		$label = Html::el();
		[$wrapper, $wrapperEnd] = $wrapper instanceof Html ? [$wrapper->startTag(), $wrapper->endTag()] : [(string) $wrapper, ''];

		foreach ($items as $value => $caption) {
			foreach ($inputAttrs as $k => $v) {
				$input->attrs[$k] = $v[$value] ?? null;
			}

			foreach ($labelAttrs as $k => $v) {
				$label->attrs[$k] = $v[$value] ?? null;
			}

			$input->value = $value;
			$res .= ($res === '' && $wrapperEnd === '' ? '' : $wrapper)
				. $labelTag . $label->attributes() . '>'
				. $inputTag . $input->attributes() . '>'
				. ($caption instanceof Nette\HtmlStringable ? $caption : htmlspecialchars((string) $caption, ENT_NOQUOTES, 'UTF-8'))
				. '</label>'
				. $wrapperEnd;
		}

		return $res;
	}


	/**
	 * Generates a <select> HTML element from the items array.
	 * @param  mixed[]  $items
	 * @param  ?array<string, mixed>  $optionAttrs
	 */
	public static function createSelectBox(array $items, ?array $optionAttrs = null, mixed $selected = null): Html
	{
		if ($selected !== null) {
			$optionAttrs['selected?'] = $selected;
		}

		[$optionAttrs, $optionTag] = self::prepareAttrs($optionAttrs, 'option');
		$option = Html::el();
		$res = $tmp = '';
		foreach ($items as $group => $subitems) {
			if (is_array($subitems)) {
				$res .= Html::el('optgroup')->label($group)->startTag();
				$tmp = '</optgroup>';
			} else {
				$subitems = [$group => $subitems];
			}

			foreach ($subitems as $value => $caption) {
				$option->value = $value;
				foreach ($optionAttrs as $k => $v) {
					$option->attrs[$k] = $v[$value] ?? null;
				}

				if ($caption instanceof Html) {
					$caption = clone $caption;
					$res .= $caption->setName('option')->addAttributes($option->attrs);
				} else {
					$res .= $optionTag . $option->attributes() . '>'
						. htmlspecialchars((string) $caption, ENT_NOQUOTES, 'UTF-8')
						. '</option>';
				}

				if ($selected === $value) {
					unset($optionAttrs['selected'], $option->attrs['selected']);
				}
			}

			$res .= $tmp;
			$tmp = '';
		}

		return Html::el('select')->setHtml($res);
	}


	/**
	 * @param  ?array<string, mixed>  $attrs
	 * @return array{array<string, mixed>, string}
	 */
	private static function prepareAttrs(?array $attrs, string $name): array
	{
		$dynamic = [];
		foreach ((array) $attrs as $k => $v) {
			if ($k[-1] === '?' || $k[-1] === ':') {
				$p = substr($k, 0, -1);
				unset($attrs[$k], $attrs[$p]);
				if ($k[-1] === '?') {
					$dynamic[$p] = array_fill_keys((array) $v, value: true);
				} elseif (is_array($v) && $v) {
					$dynamic[$p] = $v;
				} else {
					$attrs[$p] = $v;
				}
			}
		}

		return [$dynamic, '<' . $name . Html::el(null, $attrs)->attributes()];
	}


	/** @internal */
	public static function iniGetSize(string $name): int
	{
		$value = ini_get($name);
		if ($value === false) {
			return 0;
		}

		$units = ['k' => 10, 'm' => 20, 'g' => 30];
		return isset($units[$ch = strtolower(substr($value, -1))])
			? (int) $value << $units[$ch]
			: (int) $value;
	}


	/**
	 * Returns the single type name from reflection, or null if no type is defined.
	 * @internal
	 */
	public static function getSingleType(\ReflectionParameter|\ReflectionProperty $reflection): ?string
	{
		$type = Nette\Utils\Type::fromReflection($reflection);
		if (!$type) {
			return null;
		} elseif ($res = $type->getSingleName()) {
			return $res;
		} else {
			throw new Nette\InvalidStateException(
				Nette\Utils\Reflection::toString($reflection) . " has unsupported type '$type'.",
			);
		}
	}


	/** @internal */
	public static function tryEnumConversion(
		mixed $value,
		\ReflectionParameter|\ReflectionProperty|null $reflection,
	): mixed
	{
		if ($value === null
			|| !$reflection
			|| !($type = Nette\Utils\Type::fromReflection($reflection)?->getSingleName())
			|| !is_a($type, \BackedEnum::class, allow_string: true)
		) {
			return $value;
		}

		// form values arrive as strings, so int-backed enums need explicit conversion
		if ((new \ReflectionEnum($type))->getBackingType()?->getName() === 'int') {
			$int = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
			return $int === null ? $value : ($type::tryFrom($int) ?? $value);
		}

		return is_string($value) ? ($type::tryFrom($value) ?? $value) : $value;
	}


	/**
	 * @internal
	 * @return list<string>
	 */
	public static function getSupportedImages(): array
	{
		return array_values(array_map(Image::typeToMimeType(...), Image::getSupportedTypes()));
	}
}
