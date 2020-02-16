<?php

namespace Framework\Validator;

abstract class AbstractValidationError
{
    protected static $defaultMessages = [
        'required' => 'Le champs %s est requis',
        'slug' => 'Le champs %s n\'est pas un slug valide',
        'empty' => 'Le champs %s ne doit pas être vide',
        'minLength' => 'Le champs %s doit contenir plus de %d caractères',
        'betweenLength' => 'Le champs %s doit contenir entre %d et %d caractères',
        'maxLength' => 'Le champs %s doit contenir moins de %d caractrères',
        'datetime' => 'Le champs %s doit être une date valide (%s)',
        'exist' => 'Le champs %s n\'existe pas dans la table %s'
    ];
}
