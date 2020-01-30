<?php

namespace Framework\Validator;

class Validator
{
    /** @var array */
    private $data;

    private $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Vérifie que la clé existe le tableau de données.
     *
     * @param array[] $constraints
     * @return Validator
     */
    public function required(array ...$constraints): self
    {
        foreach ($constraints as $constraint) {
            $value = $this->getValue($constraint['name']);

            if (\is_null($value)) {
                $this->addError($constraint['name'], 'required', $constraint['message'] ?? null);
            }
        }
        return $this;
    }

    /**
     * Vérifie que la valeur est un slug.
     *
     * @param array[] $constraints
     * @return Validator
     */
    public function slug(array ...$constraints): self
    {
        $pattern = '/^[a-z0-9]+((-[a-z0-9]+){0,})$/';

        foreach ($constraints as $constraint) {
            $value = $this->getValue($constraint['name']);

            if (!\is_null($value) && !preg_match($pattern, $value)) {
                $this->addError($constraint['name'], 'slug', $constraint['message'] ?? null);
            }
        }
        return $this;
    }

    /**
     * Vérifie que la valeur n'est pas vide.
     *
     * @param array[] $constraints
     * @return Validator
     */
    public function notEmpty(array ...$constraints): self
    {
        foreach ($constraints as $constraint) {
            $value = $this->getValue($constraint['name']);

            if (\is_null($value) || empty($value)) {
                $this->addError($constraint['name'], 'empty', $constraint['message'] ?? null);
            }
        }
        return $this;
    }

    /**
     * Vérifie la longeur de la valeur.
     *
     * @param array ...$constraints
     * @return Validator
     */
    public function length(array ...$constraints): self
    {
        foreach ($constraints as $constraint) {
            $length = mb_strlen($this->getValue($constraint['name']));
            $min = $constraint['min'] ?? null;
            $max = $constraint['max'] ?? null;

            //TODO case minLength:
            if (!\is_null($min) && \is_null($max) && $length < $min) {
                $this->addError(
                    $constraint['name'],
                    'minLength',
                    $constraint['message'] ?? null,
                    [$min]
                );
            }

            //TODO case betweenLength:
            if (!\is_null($min) && !\is_null($max) && ($length < $min || $length > $max)) {
                $this->addError(
                    $constraint['name'],
                    'betweenLength',
                    $constraint['message'] ?? null,
                    [$min, $max]
                );
            }

            //TODO case maxLength:
            if (!\is_null($max) && \is_null($min) && $length > $max) {
                $this->addError(
                    $constraint['name'],
                    'maxLength',
                    $constraint['message'] ?? null,
                    [$max]
                );
            }
        }
        return $this;
    }

    /**
     * Vérifie que la valeur est un dateTime valid.
     *
     * @param array[] $constraints
     * @return Validator
     */
    public function dateTime(array ...$constraints): self
    {
        $format = 'Y-m-d H:i:s';
        foreach ($constraints as $constraint) {
            $date = \DateTime::createFromFormat($format, $this->getValue($constraint['name']));
            $error = \DateTime::getLastErrors();

            if ($error['error_count'] > 0 || $error['warning_count'] > 0 || $date === false) {
                $this->addError(
                    $constraint['name'],
                    'datetime',
                    $constraint['message'] ?? null,
                    [$format]
                );
            }
        }
        return $this;
    }

    /**
     * Recupere les erreurs
     *
     * @return array|ValidationError[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Vérifie si le tableau d'erreur est vide
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }

    /**
     * @param string $property
     * @param string $rule
     * @param string|null $message
     * @param array $options
     */
    private function addError(string $property, string $rule, ?string $message, array $options = []): void
    {
        $this->errors[] = new ValidationError($property, $rule, $message, $options);
    }

    /**
     * Recupere la valeur de la clé dans le tableau de données
     *
     * @param string $key
     * @return string|null
     */
    private function getValue(string $key): ?string
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }
        return null;
    }
}
