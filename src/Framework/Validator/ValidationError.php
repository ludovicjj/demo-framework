<?php

namespace Framework\Validator;

class ValidationError extends AbstractValidationError
{
    /** @var string */
    private $property;

    /** @var string */
    private $rule;

    /** @var array */
    private $options;

    /** @var array */
    private $messages = [];

    /**
     * ValidationError constructor.
     * @param string $property
     * @param string $rule
     * @param string|null $message
     * @param array $options
     */
    public function __construct(string $property, string $rule, ?string $message, array $options = [])
    {
        $this->property = $property;
        $this->rule = $rule;
        $this->options = $options;
        $this->initializeMessage($message);
    }

    /**
     * Initialise le message d'erreur,
     * soit avec le message par défaut,
     * soit avec le message personnalisé.
     *
     * @param string|null $message
     */
    private function initializeMessage(?string $message): void
    {
        if (!\is_null($message)) {
            $this->messages[$this->rule] = $message;
        } else {
            $this->messages[$this->rule] = static::$defaultMessages[$this->rule];
        }
    }

    /**
     * Recupere le nom de la propriété qui a enfreint la règle de validation
     *
     * @return string
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * Recupere le nom de la règle de validation
     *
     * @return string
     */
    public function getRule(): string
    {
        return $this->rule;
    }

    /**
     * Recupere le message d'erreur
     *
     * @return string
     */
    public function getMessage(): string
    {
        $message = array_merge([$this->messages[$this->getRule()], $this->property], $this->options);
        return call_user_func_array('sprintf', $message);
    }

    public function __toString(): string
    {
        $message = array_merge([$this->messages[$this->getRule()], $this->property], $this->options);
        return call_user_func_array('sprintf', $message);
    }
}
