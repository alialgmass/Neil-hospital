<?php

namespace App\DTOs;

readonly class PatientDTO
{
    public function __construct(
        public string $name,
        public ?string $phone = null,
        public ?int $age = null,
        public ?string $nationalId = null,
        public ?string $gender = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['patient_name'],
            phone: $data['patient_phone'] ?? null,
            age: $data['patient_age'] ?? null,
            nationalId: $data['national_id'] ?? null,
            gender: $data['gender'] ?? null,
        );
    }
}
