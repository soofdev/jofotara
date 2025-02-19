<?php

namespace JBadarneh\JoFotara\Contracts;

interface ValidatableSection
{
    /**
     * Validate that all required fields are set and valid
     *
     * @throws \InvalidArgumentException If validation fails
     */
    public function validateSection(): void;
}
