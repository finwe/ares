<?php declare(strict_types=1);

namespace h4kuna\Ares\Ares\Core;

use DateTimeImmutable;
use h4kuna\Ares\Adis\StatusBusinessSubjects\Subject;
use h4kuna\Ares\Ares\Sources;
use h4kuna\Ares\Tools\Strings;
use JsonSerializable;
use stdClass;
use Stringable;

/**
 * @phpstan-type DataType array<string, mixed>
 */
class Data implements JsonSerializable
{
	public bool $active;

	public ?string $city = null;

	public ?string $company = null;

	public DateTimeImmutable $created;

	public ?DateTimeImmutable $dissolved = null;

	public ?string $city_district = null;

	public ?string $city_post = null;

	public string $in;

	public bool $is_person;

	public int $legal_form_code;

	public ?string $house_number = null;

	public ?string $street = null;

	public ?string $district = null;

	/**
	 * <prefix>DIČ
	 * @todo https://github.com/h4kuna/ares/issues/30#issuecomment-1719170527
	 */
	public ?string $tin = null;

	public ?bool $vat_payer = null;

	public ?string $zip = null;

	public ?string $country = null;

	public ?string $country_code = null;

	/**
	 * @var array<string>
	 */
	public array $nace = [];

	/**
	 * @var array<Sources::SER*, true|string>
	 */
	public array $sources = [];

	public ?stdClass $original = null;

	public ?Subject $adis = null;


	public function setAdis(Subject $adis): void
	{
		if ($adis->exists) {
			$this->vat_payer = $adis->isVatPayer;
			$this->tin = $adis->tin;
		} else {
			$this->vat_payer = null;
			$this->tin = null;
		}

		$this->adis = $adis;
	}


	/**
	 * @return array<string, scalar|array<string>>
	 */
	public function jsonSerialize()
	{
		$data = $this->toArray();
		$data['created'] = Strings::exportDate($this->created);

		if ($this->dissolved !== null) {
			$data['dissolved'] = Strings::exportDate($this->dissolved);
		}

		/** @var  array<string, scalar|array<string>> $data */
		return $data;
	}


	public function __toString()
	{
		return (string) json_encode($this, JSON_THROW_ON_ERROR);
	}


	/**
	 * @return DataType
	 */
	public function __serialize(): array
	{
		return $this->toArray();
	}


	/**
	 * @param DataType $data
	 */
	public function __unserialize(array $data): void
	{
		foreach ($data as $name => $value) {
			$this->$name = $value;
		}
	}


	/**
	 * @return DataType
	 */
	public function toArray(): array
	{
		$data = get_object_vars($this);
		unset($data['original'], $data['adis']);

		return $data;
	}

}
