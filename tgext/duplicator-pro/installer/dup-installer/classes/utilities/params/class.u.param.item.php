<?php
/**
 * param descriptor
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * this class describes the value of a parameter.
 * therefore the type of data is sanitization and validation.
 * In addition to other features such as, for example, if it is a persistent parameter.
 * 
 */
class DUPX_Param_item
{

    const INPUT_GET             = 'g';
    const INPUT_POST            = 'p';
    const INPUT_REQUEST         = 'r';
    const INPUT_COOKIE          = 'c';
    const INPUT_SERVER          = 's';
    const INPUT_ENV             = 'e';
    const TYPE_STRING           = 'str';
    const TYPE_ARRAY_STRING     = 'arr_str';
    const TYPE_INT              = 'int';
    const TYPE_ARRAY_INT        = 'arr_int';
    const TYPE_BOOL             = 'bool';
    const STATUS_INIT           = 'init';
    const STATUS_UPD_FROM_INPUT = 'updinp';

    /**
     *  validate regexes for test input
     */
    const VALIDATE_REGEX_INT_NUMBER    = '/^[\+\-]?[0-9]+$/';
    const VALIDATE_REGEX_AZ_NUMBER     = '/^[A-Za-z0-9]+$/';
    const VALIDATE_REGEX_AZ_NUMBER_SEP = '/^[A-Za-z0-9_\-]+$/'; // laddate Az 09 plus - and _
    const VALIDATE_REGEX_DIR_PATH      = '/^([a-zA-Z]:[\\\\\/]|\/|\\\\\\\\|\/\/)[^<>\0]+$/';
    const VALIDATE_REGEX_FILE_PATH     = '/^([a-zA-Z]:[\\\\\/]|\/|\\\\\\\\|\/\/)[^<>\0]+$/';

    protected $name   = null;
    protected $type   = null;
    protected $attr   = array();
    protected $value  = null;
    protected $status = self::STATUS_INIT;

    /**
     * 
     * @param string $name  // param identifier
     * @param string $type  // TYPE_STRING | TYPE_ARRAY_STRING | ...
     * @param array $attr   // list of attributes
     * @throws Exception
     */
    public function __construct($name, $type, $attr = null)
    {
        if (empty($name) || strlen($name) < 4) {
            throw new Exception('the name can\'t be empty or len can\'t be minor of 4');
        }
        $this->type = $type;
        $this->attr = array_merge(self::getDefaultAttrForType($type), (array) $attr);
        if ($type == self::TYPE_ARRAY_STRING || $type == self::TYPE_ARRAY_INT) {
            $this->attr['default'] = (array) $this->attr['default'];
        }
        $this->value = $this->attr['default'];
        $this->name  = $name;

        if (is_null($this->attr['defaultFromInput'])) {
            $this->attr['defaultFromInput'] = $this->attr['default'];
        } else {
            if ($type == self::TYPE_ARRAY_STRING || $type == self::TYPE_ARRAY_INT) {
                $this->attr['defaultFromInput'] = (array) $this->attr['defaultFromInput'];
            }
        }
    }

    /**
     * get current item identifier
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *  get current item value
     * 
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * if it is true, this object is defined as persistent and will be saved in the parameter persistence file otherwise the param manager
     * will not save this value and at each call of the script the parameter will assume the default value.
     * 
     * @return bool
     */
    public function isPersistent()
    {
        return $this->attr['persistence'];
    }

    /**
     * update item attribute
     * 
     * @param strin $key
     * @param mixed $value
     */
    public function setAttr($key, $value)
    {
        $this->attr[$key] = $value;
    }

    /**
     * 
     * @param mixed $value
     * @return boolean  // false if value isn't validated
     * @throws Exception
     */
    public function setValue($value)
    {
        $validateValue = null;
        if (!$this->isValid($value, $validateValue)) {
            return false;
        } else {
            $this->value = $validateValue;
            return true;
        }
    }

    /**
     * get supreb object from method
     * 
     * @param string $method
     * @return array
     * @throws Exception
     */
    protected static function getSuperObjectByMethod($method)
    {
        $superObject = array();
        switch ($method) {
            case self::INPUT_GET:
                $superObject = &$_GET;
                break;
            case self::INPUT_POST:
                $superObject = &$_POST;
                break;
            case self::INPUT_REQUEST:
                $superObject = &$_REQUEST;
                break;
            case self::INPUT_COOKIE:
                $superObject = &$_COOKIE;
                break;
            case self::INPUT_SERVER:
                $superObject = &$_SERVER;
                break;
            case self::INPUT_ENV:
                $superObject = &$_ENV;
                break;
            default:
                throw new Exception('ITEM ERROR '.$this->name.' Invalid method '.$method);
        }
        return $superObject;
    }

    /**
     * update the value from input if exists ot set the default
     * sanitation and validation are performed
     * 
     * @param string $method
     * @return boolean  // false if value isn't validated
     * @throws Exception
     */
    public function setValueFromInput($method = self::INPUT_POST)
    {
        $superObject = self::getSuperObjectByMethod($method);

        DUPX_Log::info('SET VALUE FROM INPUT KEY ['.$this->name.'] VALUE['.DUPX_Log::varToString(isset($superObject[$this->name]) ? $superObject[$this->name] : '').']', DUPX_Log::LV_DEBUG);

        if (!isset($superObject[$this->name])) {
            $this->value = $this->attr['defaultFromInput'];
            return true;
        } else {
            $inputValue = $this->getSanitizeValue($superObject[$this->name]);

            if (($result = $this->setValue($inputValue)) === false) {
                $msg = 'PARAM ['.$this->name.'] ERROR: Invalid value '.DUPX_Log::varToString($inputValue);
                DUPX_Log::info($msg);
                throw new Exception($msg);
            } else {
                $this->status = self::STATUS_UPD_FROM_INPUT;
            }
        }

        return $result;
    }

    /**
     * 
     * @param mixed $value
     * @param mixed $validateValue // variable passed by reference. Updated to validated value in the case, the value is a valid value.
     * @return bool     // true if is a valid value for this object
     * @throws Exception
     */
    public function isValid($value, &$validateValue = null)
    {
        switch ($this->type) {
            case self::TYPE_STRING:
            case self::TYPE_BOOL:
            case self::TYPE_INT:
                return $this->isValidScalar($value, $validateValue);
            case self::TYPE_ARRAY_STRING:
            case self::TYPE_ARRAY_INT:
                return $this->isValidArray($value, $validateValue);
            default:
                throw new Exception('ITEM ERROR invalid type '.$this->type);
        }
    }

    /**
     * 
     * @param mixed $value
     * @param mixed $validateValue // variable passed by reference. Updated to validated value in the case, the value is a valid value.
     * @return boolean  // false if value isn't a valid value
     * @throws Exception
     */
    protected function isValidScalar($value, &$validateValue = null)
    {
        if (!is_null($value) && !is_scalar($value)) {
            return false;
        }

        $result = false;
        switch ($this->type) {
            case self::TYPE_STRING:
            case self::TYPE_ARRAY_STRING:
                $validateValue = (string) $value;
                if (!empty($this->attr['acceptValues'])) {
                    if (in_array($validateValue, $this->attr['acceptValues'])) {
                        $result = true;
                        break;
                    } else {
                        $result = false;
                        break;
                    }
                }

                if (strlen($validateValue) < $this->attr['min_len'] || ($this->attr['max_len'] > 0 && strlen($validateValue) > $this->attr['mxa_len'])) {
                    $result = false;
                    break;
                }

                if (!empty($this->attr['validateRegex'])) {
                    if (preg_match($this->attr['validateRegex'], $validateValue) === false) {
                        $result = false;
                        break;
                    }
                }

                $result        = true;
                break;
            case self::TYPE_INT:
            case self::TYPE_ARRAY_INT:
                $validateValue = filter_var($value, FILTER_VALIDATE_INT, array(
                    'options' => array(
                        'default'   => false, // value to return if the filter fails
                        'min_range' => $this->attr['min_range'],
                        'max_range' => $this->attr['max_range'],
                    )
                ));

                if ($validateValue === false) {
                    $result = false;
                } else {
                    if (empty($this->attr['acceptValues'])) {
                        $result = true;
                    } else {
                        if (in_array($validateValue, $this->attr['acceptValues'])) {
                            $result = true;
                        } else {
                            $result = false;
                        }
                    }
                }
                break;
            case self::TYPE_BOOL:
                $validateValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                $result        = !is_null($value);
                break;
            default:
                throw new Exception('ITEM ERROR '.$this->name.' Invalid type '.$this->type);
        }

        if (is_callable($this->attr['validateCallback'])) {
            $result = call_user_func($this->attr['validateCallback'], $validateValue);
        }

        if ($result === false) {
            $validateValue = null;
        }
        return $result;
    }

    /**
     * validate each value of array
     * 
     * @param mixed $value
     * @param mixed $validateValue // variable passed by reference. Updated to validated value in the case, the value is a valid value.
     * @return boolean  // false if value isn't a valid value
     * @throws Exception
     */
    protected function isValidArray($value, &$validateValue = null)
    {
        $newValues     = (array) $value;
        $validateValue = array();
        $validValue    = null;

        foreach ($newValues as $newValue) {
            if (!$this->isValidScalar($newValue, $validValue)) {
                return false;
            }
            $validateValue[] = $validValue;
        }
        return true;
    }

    /**
     * return sanitized value
     * 
     * @param mixed $value
     * @return mixed
     * @throws Exception
     */
    public function getSanitizeValue($value)
    {
        switch ($this->type) {
            case self::TYPE_STRING:
            case self::TYPE_BOOL:
            case self::TYPE_INT:
                return $this->getSanitizeValueScalar($value);
            case self::TYPE_ARRAY_STRING:
            case self::TYPE_ARRAY_INT:
                return $this->getSanitizeValueArray($value);
            default:
                throw new Exception('ITEM ERROR invalid type '.$type);
        }
    }

    /**
     * if sanitizeCallback is apply sanitizeCallback at current value else return value.
     * 
     * @param mixed $value
     * @return mixed
     */
    protected function getSanitizeValueScalar($value)
    {
        if (is_callable($this->attr['sanitizeCallback'])) {
            return call_user_func($this->attr['sanitizeCallback'], $value);
        } else {
            return $value;
        }
    }

    /**
     * if sanitizeCallback is apply sanitizeCallback at each value of array.
     * 
     * @param mixed $value
     * @return array
     */
    protected function getSanitizeValueArray($value)
    {
        $newValues      = (array) $value;
        $sanitizeValues = array();

        foreach ($newValues as $newValue) {
            $sanitizeValues[] = $this->getSanitizeValueScalar($newValue);
        }

        return $sanitizeValues;
    }

    /**
     * set value from array. This function is used to set data from json array
     * 
     * @param array $data
     * @return boolean
     */
    public function fromArrayData($data)
    {
        $data = (array) $data;

        if (isset($data['status'])) {
            $this->status = $data['status'];
        }
        if (isset($data['value'])) {
            return $this->setValue($data['value']);
        } else {
            return true;
        }
    }

    /**
     * return array dato to store in json array data
     * @return array
     */
    public function toArrayData()
    {
        return array(
            'value'  => $this->value,
            'status' => $this->status
        );
    }

    /**
     * return a copy of this object with a new name ad overwrite attr
     * 
     * @param string $newName
     * @return \DUPX_Param_item
     */
    public function getCopyWithNewName($newName, $attr = array())
    {
        $copy    = clone $this;
        $reflect = new ReflectionObject($copy);

        $nameProp = $reflect->getProperty('name');
        $nameProp->setAccessible(true);
        $nameProp->setValue($copy, $newName);

        $attrProp = $reflect->getProperty('attr');
        $attrProp->setAccessible(true);
        $newAttr  = array_merge($attrProp->getValue($copy), $attr);
        $attrProp->setValue($copy, $newAttr);

        $valueProp = $reflect->getProperty('value');
        $valueProp->setAccessible(true);
        $valueProp->setValue($copy, $newAttr['default']);

        return $copy;
    }

    /**
     * this function return the default attr for each type.
     * in the constructor an array merge is made between the result of this function and the parameters passed.
     * In this way the values ​​in $ this -> ['attr'] are always consistent.
     * 
     * @param string $type
     * @return array
     * @throws Exception
     */
    protected static function getDefaultAttrForType($type)
    {
        $attrs = array(
            'default'          => null, // the default value on init
            'defaultFromInput' => null, // if value isn't set in query form when setValueFromInput is called set this valus. (normally defaultFromInput is equal to default)
            'acceptValues'     => array(), // if not empty accept only values in list
            'sanitizeCallback' => null, // function (DUPX_Param_item $obj, $inputValue)
            'validateCallback' => null, // function (DUPX_Param_item $obj, $validateValue, $originalValue)
            'persistence'      => true  // if false don't store value in persistance file
        );

        switch ($type) {
            case self::TYPE_STRING:     // value type is a string
                $attrs['min_len']          = 0; // min string len. used in validation
                $attrs['max_len']          = 0; // max string len. used in validation
                $attrs['default']          = ''; // set default at empty string
                $attrs['validateRegex']    = null; // if isn;t null this regex is called to pass for validation. Can be combined with validateCallback. If both are active, the validation must pass both.
                break;
            case self::TYPE_ARRAY_STRING: // value type is array of string 
                $attrs['min_len']          = 0; // min string len. used in validation
                $attrs['max_len']          = 0; // max string len. used in validation
                $attrs['default']          = array();  // set default at empty array
                $attrs['validateRegex']    = null; // if isn;t null this regex is called to pass for validation. Can be combined with validateCallback. If both are active, the validation must pass both.
                break;
            case self::TYPE_INT:    // value type is a int
                $attrs['min_range']        = PHP_INT_MAX * -1;
                $attrs['max_range']        = PHP_INT_MAX;
                $attrs['default']          = 0; // set default at 0
                break;
            case self::TYPE_ARRAY_INT:  // value type is an array of int
                $attrs['min_range']        = PHP_INT_MAX * -1;
                $attrs['max_range']        = PHP_INT_MAX;
                $attrs['default']          = array(); // set default at empty array
                break;
            case self::TYPE_BOOL:
                $attrs['default']          = false; // set default fals
                $attrs['defaultFromInput'] = false; // if value isn't set in input the default must be false for bool values
                break;
            default:
                // don't accept unknown type
                throw new Exception('ITEM ERROR invalid type '.$type);
        }
        return $attrs;
    }
}