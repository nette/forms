<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms\Controls;

use Nette;
use Nette\Forms\Form;


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


	public function __construct($label = null, int $type = self::TypeDate, bool $withSeconds = false)
	{
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
		if ($this->format === self::FormatObject) {
			return $this->value;
		} elseif ($this->format === self::FormatTimestamp) {
			return $this->value ? $this->value->getTimestamp() : null;
		} else {
			return $this->value ? $this->value->format($this->format) : null;
		}
	}


	/**
	 * @param \DateTimeInterface|string|int $value
	 */
	private function normalizeValue(mixed $value): \DateTimeImmutable
	{
		if (is_numeric($value)) {
			$dt = (new \DateTimeImmutable)->setTimestamp((int) $value);
		} elseif (is_string($value) && $value !== '') {
			$dt = $this->createDateTime($value);
		} elseif ($value instanceof \DateTime) {
			$dt = \DateTimeImmutable::createFromMutable($value);
		} elseif ($value instanceof \DateTimeImmutable) {
			$dt = $value;
		} else {
			throw new \TypeError('Value must be DateTimeInterface|string|int|null, ' . gettype($value) . ' given.');
		}

		[$h, $m, $s] = [(int) $dt->format('H'), (int) $dt->format('i'), $this->withSeconds ? (int) $dt->format('s') : 0];
		if ($this->type === self::TypeDate) {
			return $dt->setTime(0, 0);
		} elseif ($this->type === self::TypeTime) {
			return $dt->setDate(1, 1, 1)->setTime($h, $m, $s);
		} elseif ($this->type === self::TypeDateTime) {
			return $dt->setTime($h, $m, $s);
		}
	}


	public function loadHttpData(): void
	{
		try {
			parent::loadHttpData();
		} catch (\Throwable $e) {
			$this->value = null;
		}
	}


	public function getControl(): Nette\Utils\Html
	{
		return parent::getControl()->addAttributes($this->getAttributesFromRules())->addAttributes([
			'value' => $this->value ? $this->formatHtmlValue($this->value) : null,
			'type' => [self::TypeDate => 'date', self::TypeTime => 'time', self::TypeDateTime => 'datetime-local'][$this->type],
		]);
	}


	/**
	 * Formats a date/time for HTML attributes.
	 */
	public function formatHtmlValue(\DateTimeInterface|string|int $value): string
	{
		$value = $this->normalizeValue($value);
		return $value->format([
			self::TypeDate => 'Y-m-d',
			self::TypeTime => $this->withSeconds ? 'H:i:s' : 'H:i',
			self::TypeDateTime => $this->withSeconds ? 'Y-m-d\\TH:i:s' : 'Y-m-d\\TH:i',
		][$this->type]);
	}


	/**
	 * Formats a date/time according to the locale and formatting options.
	 */
	public function formatLocaleText(\DateTimeInterface|string|int $value): string
	{
		$value = $this->normalizeValue($value);
		if ($this->type === self::TypeDate) {
			return \IntlDateFormatter::formatObject($value, [\IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE]);
		} elseif ($this->type === self::TypeTime) {
			return \IntlDateFormatter::formatObject($value, [\IntlDateFormatter::NONE, $this->withSeconds ? \IntlDateFormatter::MEDIUM : \IntlDateFormatter::SHORT]);
		} elseif ($this->type === self::TypeDateTime) {
			return \IntlDateFormatter::formatObject($value, [\IntlDateFormatter::MEDIUM, $this->withSeconds ? \IntlDateFormatter::MEDIUM : \IntlDateFormatter::SHORT]);
		}
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
