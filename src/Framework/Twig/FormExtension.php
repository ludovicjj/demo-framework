<?php

namespace Framework\Twig;

use DateTime;
use Framework\Validator\ValidationError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use function is_array;
use function is_null;
use function count;

class FormExtension extends AbstractExtension
{
    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('field', [$this, 'field'], ['is_safe' => ['html'], 'needs_context' => true])
        ];
    }

    /**
     * @param array $context
     * @param string $key
     * @param mixed $value
     * @param string|null $label
     * @param array $options
     * @return string
     */
    public function field(array $context, string $key, $value, ?string $label = null, array $options = []): string
    {
        /** @var bool|array $errors */
        $errors = $this->getErrors($context, $key);
        $attributes = $this->initializeAttributes($key, $options, $errors);
        $type = $this->getType($options);
        $convertValue = $this->convertValue($value);
        $listOption = $this->getListOption($options);

        //TODO build label
        $labelHtml = $this->getLabel($key, $label);

        //TODO build error
        $errorHtml = $this->buildHtmlErrors($errors, $key);

        if ($type === 'textarea') {
            $inputHtml = $this->getInputTextarea($attributes, $convertValue);
        } elseif ($type === 'select') {
            $inputHtml = $this->getInputSelect($attributes, $listOption, $convertValue);
        } else {
            $inputHtml = $this->getInputText($attributes, $convertValue);
        }

        return "<div class=\"form-group\">{$labelHtml}{$inputHtml}{$errorHtml}</div>";
    }

    /**
     * Genere le HTML pour le <label>
     * @param string $key
     * @param string|null $label
     * @return string
     */
    private function getLabel(string $key, ?string $label = null): string
    {
        $label = $label ?: $key;
        return "<label for=\"{$key}\">{$label}</label>";
    }

    /**
     * @param array $options
     * @return array
     */
    private function getListOption(array $options): array
    {
        return $options['options'] ?? [];
    }

    /**
     * Genere le HTML pour le <input type="text">
     *
     * @param array $attributes
     * @param null|string $value
     * @return string
     */
    private function getInputText(array $attributes, ?string $value): string
    {
        return "<input type=\"text\" {$this->buildHtmlFromArray($attributes)} value=\"{$value}\">";
    }

    /**
     * Genere le HTML pour le <textarea>
     *
     * @param array $attributes
     * @param null|string $value
     * @return string
     */
    private function getInputTextarea(array $attributes, ?string $value): string
    {
        return "<textarea {$this->buildHtmlFromArray($attributes)}>{$value}</textarea>";
    }

    /**
     * Genere le HTML pour le <select>
     *
     * @param array $attributes
     * @param array $listOptions
     * @param string|null $value
     * @return string
     */
    private function getInputSelect(array $attributes, array $listOptions, ?string $value): string
    {
        $htmlOption = $this->buildHtmlOption($listOptions, $value);
        return "<select {$this->buildHtmlFromArray($attributes)}>{$htmlOption}</select>";
    }

    /**
     * Transforme un tableau en attributs HTML
     *
     * @param array $attributes
     * @return string
     */
    private function buildHtmlFromArray(array $attributes): string
    {
        $htmlArray =  array_map(function ($key, $value) {
            if ($value === true) {
                return $key;
            } elseif ($value !== false) {
                return "{$key}=\"{$value}\"";
            }
            return null;
        }, array_keys($attributes), $attributes);

        // Todo filter null value into $htmlArray and return string
        return implode(' ', array_filter($htmlArray, function ($value) {
            return !is_null($value);
        }));
    }

    /**
     * Transforme le tableau option en attributs HTML
     *
     * @param array $listOption
     * @param string|null $value
     * @return string
     */
    private function buildHtmlOption(array $listOption, ?string $value): string
    {
        if (!empty($listOption)) {
            return array_reduce(array_keys($listOption), function (string $carry, $key) use ($listOption, $value) {
                $attr = ['value' => $key, 'selected' => $key === (int)$value];
                return $carry . "<option {$this->buildHtmlFromArray($attr)}>$listOption[$key]</option>";
            }, "");
        }
        return "";
    }

    /**
     * Genere le HTML pour les erreurs.
     *
     * @param array|null $errors
     * @param string $key
     * @return string|null
     */
    private function buildHtmlErrors(?array $errors, string $key): ?string
    {
        if (is_array($errors) && array_key_exists($key, $errors)) {
            $arrayMessages = $errors[$key];
            return implode(
                '',
                preg_filter('/^(.*)$/', '<div class="invalid-feedback">$0</div>', $arrayMessages)
            );
        }
        return null;
    }

    /**
     * Recupere le type du champs.
     *
     * @param array $options
     * @return string
     */
    private function getType(array $options): string
    {
        return $options['type'] ?? 'text';
    }

    /**
     * Recupere la clé "errors" du context.
     *
     * Tant que le formulaire n'est pas soumis :
     * La clé "errors" vaut null, sert de temoin pour savoir si la method est GET.
     * Tant que "errors" vaut null, retourne null.
     *
     * Lorsque le formulaire est soumis :
     * La clé "errors" vaut un array vide ou ValidatorError[].
     * Si "errors" n'est pas vide, recupere le ValidatorError pour la propriété correspondante.
     * Retourne un tableau vide ou un tableau avec les messages d'erreurs
     *
     * @param array $context
     * @param string $key
     * @return array|null
     */
    private function getErrors(array $context, string $key): ?array
    {
        /** @var array|null $constraintList */
        $constraintList = $context['errors'] ?? null;

        if (is_null($constraintList)) {
            return $constraintList;
        }

        $errors = [];
        if (count($constraintList) > 0) {
            /** @var ValidationError $constraint */
            foreach ($constraintList as $constraint) {
                if ($constraint->getProperty() === $key) {
                    $errors[$constraint->getProperty()][] = $constraint->getMessage();
                }
            }
        }
        return $errors;
    }

    /**
     * Construit le tableau d'attributs HTML en fonction des erreurs et des options.
     *
     * @param string $key
     * @param array $options
     * @param array|null $errors
     * @return array
     */
    private function initializeAttributes(string $key, array $options, ?array $errors): array
    {
        $attributes = [
            'id' => $key,
            'name' => $key,
            'class' => 'form-control'
        ];
        $attributes = $this->updateAttributes($attributes, $options);

        if (is_array($errors) && !empty($errors)) {
            $attributes['class'] .= ' is-invalid';
        } elseif (is_array($errors) && empty($errors)) {
            $attributes['class'] .= ' is-valid';
        }

        return $attributes;
    }

    private function updateAttributes(array $attributes, array $options)
    {
        $attrOptions = $options['attr'] ?? [];
        $attrMergeRecursive = array_merge_recursive($attributes, $attrOptions);
        return array_map(function ($value) {
            if (is_array($value)) {
                return implode(' ', $value);
            }
            return $value;
        }, $attrMergeRecursive);
    }

    /**
     * Transforme un object DateTime en string
     *
     * @param mixed $value
     * @return string|null
     */
    private function convertValue($value): ?string
    {
        if ($value instanceof DateTime) {
            return $value->format('Y-m-d H:i:s');
        }
        return $value;
    }
}
