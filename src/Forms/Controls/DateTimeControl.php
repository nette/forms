<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms\Controls;

use Nette;
use Nette\Forms\Form;
use Stringable;


/**
 * Selects date or time or date & time.
 */
class DateTimeControl extends BaseControl
{
	public const
		TypeDate = 1,
		TypeTime = 2,
		TypeDateTime = 3;

	public const
		FormatObject = 'object',
		FormatTimestamp = 'timestamp';

	private int $type;
	private bool $withSeconds;
	private string $format = self::FormatObject;


	public function __construct(
		string|Stringable|null $label = null,
		int $type = self::TypeDate,
		bool $withSeconds = false,
	) {
		$this->type = $type;
		$this->withSeconds = $withSeconds;
		parent::__construct($label);
		$this->control->step = $withSeconds ? 1 : null;
		$this->setOption('type', 'datetime');
	}


	/**
	 * Format of returned value. Allowed values are string (ie 'Y-m-d'), DateTimeControl::FormatObject and DateTimeControl::FormatTimestamp.
	 */
	public function setFormat(string $format): static
	{
		$this->format = $format;
		return $this;
	}


	/**
	 * @param \DateTimeInterface|string|int|null $value
	 */
	public function setValue($value): static
	{
		$this->value = $value === null || $value === ''
			? null
			: $this->normalizeValue($value);
		return $this;
	}


	public function getValue(): \DateTimeImmutable|string|int|null
	{
		return match ($this->format) {
			self::FormatObject => $this->value,
			self::FormatTimestamp => $this->value?->getTimestamp(),
			default => $this->value?->format($this->format),
		};
	}


	/**
	 * @param \DateTimeInterface|string|int $value
	 */
	private function normalizeValue(mixed $value): \DateTimeImmutable
	{
		$dt = match (true) {
			is_numeric($value) => (new \DateTimeImmutable)->setTimestamp((int) $value),
			is_string($value) && $value !== '' => $this->createDateTime($value),
			$value instanceof \DateTimeInterface => \DateTimeImmutable::createFromInterface($value),
			default => throw new \TypeError('Value must be DateTimeInterface|string|int|null, ' . get_debug_type($value) . ' given.'),
		};

		[$h, $m, $s] = [(int) $dt->format('H'), (int) $dt->format('i'), $this->withSeconds ? (int) $dt->format('s') : 0];
		return match ($this->type) {
			self::TypeDate => $dt->setTime(0, 0),
			self::TypeTime => $dt->setDate(1, 1, 1)->setTime($h, $m, $s),
			self::TypeDateTime => $dt->setTime($h, $m, $s),
		};
	}


	public function loadHttpData(): void
	{
		try {
			parent::loadHttpData();
		} catch (\Throwable) {
			$this->value = null;
		}
	}


	public function getControl(): Nette\Utils\Html
	{
		return parent::getControl()->addAttributes([
			...$this->getAttributesFromRules(),
			'value' => $this->value ? $this->formatHtmlValue($this->value) : null,
			'type' => match ($this->type) {
				self::TypeDate => 'date', self::TypeTime => 'time', self::TypeDateTime => 'datetime-local'
			},
		]);
	}


	/**
	 * Formats a date/time for HTML attributes.
	 */
	public function formatHtmlValue(\DateTimeInterface|string|int $value): string
	{
		return $this->normalizeValue($value)->format(match ($this->type) {
			self::TypeDate => 'Y-m-d',
			self::TypeTime => $this->withSeconds ? 'H:i:s' : 'H:i',
			self::TypeDateTime => $this->withSeconds ? 'Y-m-d\\TH:i:s' : 'Y-m-d\\TH:i',
		});
	}


	/**
	 * Formats a date/time according to the locale and formatting options.
	 */
	public function formatLocaleText(\DateTimeInterface|string|int $value): string
	{
		return \IntlDateFormatter::formatObject($this->normalizeValue($value), match ($this->type) {
			self::TypeDate => [\IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE],
			self::TypeTime => [\IntlDateFormatter::NONE, $this->withSeconds ? \IntlDateFormatter::MEDIUM : \IntlDateFormatter::SHORT],
			self::TypeDateTime => [\IntlDateFormatter::MEDIUM, $this->withSeconds ? \IntlDateFormatter::MEDIUM : \IntlDateFormatter::SHORT],
		});
	}


	private function getAttributesFromRules(): array
	{
		$attrs = [];
		$format = fn($val) => is_scalar($val) || $val instanceof \DateTimeInterface
				? $this->formatHtmlValue($val)
				: null;
		foreach ($this->getRules() as $rule) {
			if ($rule->branch) {
			} elseif (!$rule->canExport()) {
				break;
			} elseif ($rule->validator === Form::Min) {
				$attrs['min'] = $format($rule->arg);
			} elseif ($rule->validator === Form::Max) {
				$attrs['max'] = $format($rule->arg);
			} elseif ($rule->validator === Form::Range) {
				$attrs['min'] = $format($rule->arg[0] ?? null);
				$attrs['max'] = $format($rule->arg[1] ?? null);
			}
		}
		return $attrs;
	}


	public function validateMinMax(mixed $min, mixed $max): bool
	{
		$value = $this->normalizeValue($this->value);
		$min = $min === null ? null : $this->normalizeValue($min);
		$max = $max === null ? null : $this->normalizeValue($max);
		return $this->type === self::TypeTime && $min > $max
			? $value >= $min || $value <= $max
			: $value >= $min && ($max === null || $value <= $max);
	}


	private function createDateTime(string $value): \DateTimeImmutable
	{
		$dt = new \DateTimeImmutable($value);
		$errors = \DateTimeImmutable::getLastErrors();
		if ($errors && $errors['warnings']) {
			throw new Nette\InvalidArgumentException(Nette\Utils\Arrays::first($errors['warnings']) . " '$value'");
		}
		return $dt;
	}
}
