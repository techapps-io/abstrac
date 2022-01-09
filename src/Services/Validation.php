<?php
namespace Cygnis\Services;

use Illuminate\Validation\Validator;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author Usaama Effendi <usaamaeffendi@gmail.com>
 */
class Validation extends Validator
{
    /**
     * @author Usaama Effendi <usaamaeffendi@gmail.com>
     */
    public function __construct(TranslatorInterface $translator, array $data, array $rules,
                                array $messages = [], array $customAttributes = [])
    {
        parent::__construct($translator, $data, $rules, $messages, $customAttributes);
    }

    /**
     * @author Usaama Effendi <usaamaeffendi@gmail.com>
     */
    protected function validateExistsHashed($attribute, $value, $parameters)
    {
        return parent::validateExists($attribute, hashid_decode($value), $parameters);
    }

    /**
     * @author Usaama Effendi <usaamaeffendi@gmail.com>
     */
    protected function validateCountTable($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'count_table');

        $value = array_map(function($a) use ($parameters){
            return isset($a[$parameters[1]]) ? hashid_decode($a[$parameters[1]]) : 0;
        }, $value);

        list($connection, $table) = $this->parseTable($parameters[0]);

        $column = isset($parameters[1]) && $parameters[1] !== 'NULL'
                    ? $parameters[1] : $this->guessColumnForQuery($attribute);

        $expected = (is_array($value)) ? count($value) : 1;

        return $this->getExistCount(
            $connection, $table, $column, $value, $parameters
        ) == $expected;
    }

    /**
     * @author Usaama Effendi <usaamaeffendi@gmail.com>
     */
    public function validateFileExists($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'file_exists');

        return \File::exists($parameters[0].'/'.$value);
    }
}
