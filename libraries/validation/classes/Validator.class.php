<?php

    namespace lahaina\libraries\validation;

if (!defined('PATH'))
        exit('Kein direkter Skriptzugriff erlaubt!');

    use lahaina\framework\data\Container;
    use lahaina\framework\exception\LibraryException;

    /**
     * Validator
     * 
     * @version 1.0.2
     * 
     * @author Tasos Bekos <tbekos@gmail.com>
     * @author Chris Gutierrez <cdotgutierrez@gmail.com>
     * @author Corey Ballou <ballouc@gmail.com>
     * @author Jonathan Nessier
     * 
     * @see Modified solution of: https://github.com/blackbelt/php-validation
     * @see Based on idea: http://brettic.us/2010/06/18/form-validation-class-using-php-5-3/
     */
    class Validator {

        protected $messages = array();
        protected $errors = array();
        protected $rules = array();
        protected $fields = array();
        protected $functions = array();
        protected $arguments = array();
        protected $filters = array();

        /**
         * @var Data
         */
        protected $data;

        /**
         * @var Data
         */
        protected $validData;

        /**
         * Constructor
         * 
         * @param mixed $data
         */
        public function __construct($data) {
            return $this->_setData($data);
        }

        /**
         * Set the data to be validated
         * 
         * @param mixed $data
         * @return \lahaina\libraries\validation\Validator
         */
        protected function _setData($data) {
            if (is_array($data)) {
                $data = new Container($data);
            }
            if ($data instanceof Container) {
                $this->data = $data;
                if (is_array($data)) {
                    $this->validData = array();
                } else {
                    $this->validData = new Container();
                }
                return $this;
            } else {
                throw new LibraryException('Data must be an array or an instance of \lahaina\framework\data\Container');
            }
        }

        // ----------------- ADD NEW RULE FUNCTIONS BELOW THIS LINE ----------------

        /**
         * Field, if completed, has to be a valid email address.
         *
         * @param string $message
         * @return FormValidator
         */
        public function preg_match($regex, $message = null) {
            $this->_setRule(__FUNCTION__, function($val) {
                $args = $this->arguments['preg_match'];
                if (preg_match($args[0], $val)) {
                    return true;
                }
                return false;
            }, $message, array($regex));
            return $this;
        }

        /**
         * Field, if completed, has to be a valid email address.
         *
         * @param string $message
         * @return FormValidator
         */
        public function email($message = null) {
            $this->_setRule(__FUNCTION__, function($email) {
                if (strlen($email) == 0)
                    return true;
                $isValid = true;
                $atIndex = strrpos($email, '@');
                if (is_bool($atIndex) && !$atIndex) {
                    $isValid = false;
                } else {
                    $domain = substr($email, $atIndex + 1);
                    $local = substr($email, 0, $atIndex);
                    $localLen = strlen($local);
                    $domainLen = strlen($domain);
                    if ($localLen < 1 || $localLen > 64) {
                        $isValid = false;
                    } else if ($domainLen < 1 || $domainLen > 255) {
                        // domain part length exceeded
                        $isValid = false;
                    } else if ($local[0] == '.' || $local[$localLen - 1] == '.') {
                        // local part starts or ends with '.'
                        $isValid = false;
                    } else if (preg_match('/\\.\\./', $local)) {
                        // local part has two consecutive dots
                        $isValid = false;
                    } else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
                        // character not valid in domain part
                        $isValid = false;
                    } else if (preg_match('/\\.\\./', $domain)) {
                        // domain part has two consecutive dots
                        $isValid = false;
                    } else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $local))) {
                        // character not valid in local part unless
                        // local part is quoted
                        if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $local))) {
                            $isValid = false;
                        }
                    }
                }
                return $isValid;
            }, $message);
            return $this;
        }

        /**
         * Field must be filled in.
         *
         * @param string $message
         * @return FormValidator
         */
        public function required($message = null) {
            $this->_setRule(__FUNCTION__, function($val) {
                if (is_scalar($val)) {
                    $val = trim($val);
                }
                return !empty($val);
            }, $message);
            return $this;
        }

        /**
         * Field must contain a valid float value.
         *
         * @param string $message
         * @return FormValidator
         */
        public function float($message = null) {
            $this->_setRule(__FUNCTION__, function($val) {
                return !(filter_var($val, FILTER_VALIDATE_FLOAT) === FALSE);
            }, $message);
            return $this;
        }

        /**
         * Field must contain a valid integer value.
         *
         * @param string $message
         * @return FormValidator
         */
        public function integer($message = null) {
            $this->_setRule(__FUNCTION__, function($val) {
                return !(filter_var($val, FILTER_VALIDATE_INT) === FALSE);
            }, $message);
            return $this;
        }

        /**
         * Every character in field, if completed, must be a digit.
         * This is just like integer(), except there is no upper limit.
         *
         * @param string $message
         * @return FormValidator
         */
        public function digits($message = null) {
            $this->_setRule(__FUNCTION__, function($val) {
                return (strlen($val) === 0 || ctype_digit((string) $val));
            }, $message);
            return $this;
        }

        /**
         * Field must be a number greater than [or equal to] X.
         *
         * @param numeric $limit
         * @param boolean $include Whether to include limit value.
         * @param string $message
         * @return FormValidator
         */
        public function min($limit, $include = TRUE, $message = null) {
            $this->_setRule(__FUNCTION__, function($val, $args) {
                if (strlen($val) === 0) {
                    return TRUE;
                }
                $val = (float) $val;
                $limit = (float) $args[0];
                $inc = (bool) $args[1];

                return ($val > $limit || ($inc === TRUE && $val === $limit));
            }, $message, array($limit, $include));
            return $this;
        }

        /**
         * Field must be a number greater than [or equal to] X.
         *
         * @param numeric $limit
         * @param boolean $include Whether to include limit value.
         * @param string $message
         * @return FormValidator
         */
        public function max($limit, $include = TRUE, $message = null) {
            $this->_setRule(__FUNCTION__, function($val, $args) {
                if (strlen($val) === 0) {
                    return TRUE;
                }

                $val = (float) $val;
                $limit = (float) $args[0];
                $inc = (bool) $args[1];

                return ($val < $limit || ($inc === TRUE && $val === $limit));
            }, $message, array($limit, $include));
            return $this;
        }

        /**
         * Field must be a number between X and Y.
         *
         * @param numeric $min
         * @param numeric $max
         * @param boolean $include Whether to include limit value.
         * @param string $message
         * @return FormValidator
         */
        public function between($min, $max, $include = TRUE, $message = null) {
            $message = $this->_getDefaultMessage(__FUNCTION__, array($min, $max, $include));

            $this->min($min, $include, $message)->max($max, $include, $message);
            return $this;
        }

        /**
         * Field has to be greater than or equal to X characters long.
         *
         * @param int $len
         * @param string $message
         * @return FormValidator
         */
        public function minlength($len, $message = null) {
            $this->_setRule(__FUNCTION__, function($val, $args) {
                return !(strlen(trim($val)) < $args[0]);
            }, $message, array($len));
            return $this;
        }

        /**
         * Field has to be less than or equal to X characters long.
         *
         * @param int $len
         * @param string $message
         * @return FormValidator
         */
        public function maxlength($len, $message = null) {
            $this->_setRule(__FUNCTION__, function($val, $args) {
                return !(strlen(trim($val)) > $args[0]);
            }, $message, array($len));
            return $this;
        }

        /**
         * Field has to be between minlength and maxlength characters long.
         *
         * @param   int $minlength
         * @param   int $maxlength
         * @
         */
        public function betweenlength($minlength, $maxlength, $message = null) {
            $message = empty($message) ? self::getDefaultMessage(__FUNCTION__, array($minlength, $maxlength)) : NULL;

            $this->minlength($minlength, $message)->max($maxlength, $message);
            return $this;
        }

        /**
         * Field has to be X characters long.
         *
         * @param int $len
         * @param string $message
         * @return FormValidator
         */
        public function length($len, $message = null) {
            $this->_setRule(__FUNCTION__, function($val, $args) {
                return (strlen(trim($val)) == $args[0]);
            }, $message, array($len));
            return $this;
        }

        /**
         * Field is the same as another one (password comparison etc).
         *
         * @param string $field
         * @param string $label
         * @param string $message
         * @return FormValidator
         */
        public function matches($field, $label, $message = null) {
            $this->_setRule(__FUNCTION__, function($val, $args) {
                return ((string) $args[0] == (string) $val);
            }, $message, array($this->_getVal($field), $label));
            return $this;
        }

        /**
         * Field is different from another one.
         *
         * @param string $field
         * @param string $label
         * @param string $message
         * @return FormValidator
         */
        public function notmatches($field, $label, $message = null) {
            $this->_setRule(__FUNCTION__, function($val, $args) {
                return ((string) $args[0] != (string) $val);
            }, $message, array($this->_getVal($field), $label));
            return $this;
        }

        /**
         * Field must start with a specific substring.
         *
         * @param string $sub
         * @param string $message
         * @return FormValidator
         */
        public function startsWith($sub, $message = null) {
            $this->_setRule(__FUNCTION__, function($val, $args) {
                $sub = $args[0];
                return (strlen($val) === 0 || substr($val, 0, strlen($sub)) === $sub);
            }, $message, array($sub));
            return $this;
        }

        /**
         * Field must NOT start with a specific substring.
         *
         * @param string $sub
         * @param string $message
         * @return FormValidator
         */
        public function notstartsWith($sub, $message = null) {
            $this->_setRule(__FUNCTION__, function($val, $args) {
                $sub = $args[0];
                return (strlen($val) === 0 || substr($val, 0, strlen($sub)) !== $sub);
            }, $message, array($sub));
            return $this;
        }

        /**
         * Field must end with a specific substring.
         *
         * @param string $sub
         * @param string $message
         * @return FormValidator
         */
        public function endsWith($sub, $message = null) {
            $this->_setRule(__FUNCTION__, function($val, $args) {
                $sub = $args[0];
                return (strlen($val) === 0 || substr($val, -strlen($sub)) === $sub);
            }, $message, array($sub));
            return $this;
        }

        /**
         * Field must not end with a specific substring.
         *
         * @param string $sub
         * @param string $message
         * @return FormValidator
         */
        public function notendsWith($sub, $message = null) {
            $this->_setRule(__FUNCTION__, function($val, $args) {
                $sub = $args[0];
                return (strlen($val) === 0 || substr($val, -strlen($sub)) !== $sub);
            }, $message, array($sub));
            return $this;
        }

        /**
         * Field has to be valid IP address.
         *
         * @param string $message
         * @return FormValidator
         */
        public function ip($message = null) {
            $this->_setRule(__FUNCTION__, function($val) {
                return (strlen(trim($val)) === 0 || filter_var($val, FILTER_VALIDATE_IP) !== FALSE);
            }, $message);
            return $this;
        }

        /**
         * Field has to be valid internet address.
         *
         * @param string $message
         * @return FormValidator
         */
        public function url($message = null) {
            $this->_setRule(__FUNCTION__, function($val) {
                return (strlen(trim($val)) === 0 || filter_var($val, FILTER_VALIDATE_URL) !== FALSE);
            }, $message);
            return $this;
        }

        /**
         * Date format.
         *
         * @return string
         */
        protected function _getDefaultDateFormat() {
            return 'd/m/Y';
        }

        /**
         * Field has to be a valid date.
         *
         * @param string $message
         * @return FormValidator
         */
        public function date($message = null) {
            $this->_setRule(__FUNCTION__, function($val, $args) {

                if (strlen(trim($val)) === 0) {
                    return TRUE;
                }

                try {
                    $dt = new \DateTime($val, new \DateTimeZone("UTC"));
                    return true;
                } catch (\Exception $e) {
                    return false;
                }
            }, $message, array($format, $separator));
            return $this;
        }

        /**
         * Field has to be a date later than or equal to X.
         *
         * @param   string|int  $date       Limit date
         * @param   string      $format     Date format
         * @param   string      $message
         * @return FormValidator
         */
        public function minDate($date = 0, $format = null, $message = null) {
            if (empty($format)) {
                $format = $this->_getDefaultDateFormat();
            }
            if (is_numeric($date)) {
                $date = new \DateTime($date . ' days'); // Days difference from today
            } else {
                $fieldValue = $this->_getVal($date);
                $date = ($fieldValue == FALSE) ? $date : $fieldValue;

                $date = \DateTime::createFromFormat($format, $date);
            }

            $this->_setRule(__FUNCTION__, function($val, $args) {
                $format = $args[1];
                $limitDate = $args[0];

                return ($limitDate > \DateTime::createFromFormat($format, $val)) ? FALSE : TRUE;
            }, $message, array($date, $format));
            return $this;
        }

        /**
         * Field has to be a date later than or equal to X.
         *
         * @param string|integer $date Limit date.
         * @param string $format Date format.
         * @param string $message
         * @return FormValidator
         */
        public function maxDate($date = 0, $format = null, $message = null) {
            if (empty($format)) {
                $format = $this->_getDefaultDateFormat();
            }
            if (is_numeric($date)) {
                $date = new \DateTime($date . ' days'); // Days difference from today
            } else {
                $fieldValue = $this->_getVal($date);
                $date = ($fieldValue == FALSE) ? $date : $fieldValue;

                $date = \DateTime::createFromFormat($format, $date);
            }

            $this->_setRule(__FUNCTION__, function($val, $args) {
                $format = $args[1];
                $limitDate = $args[0];

                return !($limitDate < \DateTime::createFromFormat($format, $val));
            }, $message, array($date, $format));
            return $this;
        }

        /**
         * Field has to be a valid credit card number format.
         *
         * @see https://github.com/funkatron/inspekt/blob/master/Inspekt.php
         * @param string $message
         * @return FormValidator
         */
        public function ccnum($message = null) {
            $this->_setRule(__FUNCTION__, function($value) {
                $value = str_replace(' ', '', $value);
                $length = strlen($value);

                if ($length < 13 || $length > 19) {
                    return FALSE;
                }

                $sum = 0;
                $weight = 2;

                for ($i = $length - 2; $i >= 0; $i--) {
                    $digit = $weight * $value[$i];
                    $sum += floor($digit / 10) + $digit % 10;
                    $weight = $weight % 2 + 1;
                }

                $mod = (10 - $sum % 10) % 10;

                return ($mod == $value[$length - 1]);
            }, $message);
            return $this;
        }

        /**
         * Field has to be one of the allowed ones.
         *
         * @param string|array $allowed Allowed values.
         * @param string $message
         * @return FormValidator
         */
        public function oneOf($allowed, $message = null) {
            if (is_string($allowed)) {
                $allowed = explode(',', $allowed);
            }

            $this->_setRule(__FUNCTION__, function($val, $args) {
                return in_array($val, $args[0]);
            }, $message, array($allowed));
            return $this;
        }

        // --------------- END [ADD NEW RULE FUNCTIONS ABOVE THIS LINE] ------------

        /**
         * Callback
         * 
         * @param   string  $name
         * @param   mixed   $function
         * @param   string  $message
         * @param   mixed   $params
         * @return  FormValidator
         */
        public function callback($callback, $message = '', $params = array()) {
            if (is_callable($callback)) {

                // If an array is callable, it is a method
                if (is_array($callback)) {
                    $func = new \ReflectionMethod($callback[0], $callback[1]);
                } else {
                    $func = new \ReflectionFunction($callback);
                }

                if (!empty($func)) {
                    // needs a unique name to avoild collisions in the rules array
                    $name = 'callback_' . sha1(uniqid());
                    $this->_setRule($name, function($value) use ($func, $params, $callback) {
                        // Creates merged arguments array with validation target as first argument
                        $args = array_merge(array($value), (is_array($params) ? $params : array($params)));
                        if (is_array($callback)) {
                            // If callback is a method, the object must be the first argument
                            return $func->invokeArgs($callback[0], $args);
                        } else {
                            return $func->invokeArgs($args);
                        }
                    }, $message, $params);
                }
            } else {
                throw new LibraryException(sprintf('%s is not callable.', $function));
            }

            return $this;
        }

        // ------------------ PRE VALIDATION FILTERING -------------------

        /**
         * Add a filter callback for the data
         *
         * @param mixed $callback
         * @return FormValidator
         */
        public function filter($callback) {
            if (is_callable($callback)) {
                $this->filters[] = $callback;
            }

            return $this;
        }

        /**
         * Applies filters based on a data key
         *
         * @access protected
         * @param string $key
         * @return void
         */
        protected function _applyFilters($key) {
            $val = $this->data->get($key);
            $this->_applyFilter($val);
        }

        /**
         * Recursively apply filters to a value
         *
         * @access protected
         * @param mixed $val reference
         * @return void
         */
        protected function _applyFilter(&$val) {
            if (is_array($val)) {
                foreach ($val as $key => &$item) {
                    $this->_applyFilter($item);
                }
            } else {
                foreach ($this->filters as $filter) {
                    $val = $filter($val);
                }
            }
        }

        /**
         * Set validation
         * 
         * @param string $key
         * @param string $label
         * @return boolean
         */
        public function set($key, $recursive = false, $label = '') {
            // set up field name for error message
            $this->fields[$key] = (empty($label)) ? 'Field with the name of "' . $key . '"' : $label;

            // apply filters to the data
            $this->_applyFilters($key);

            $val = $this->_getVal($key);

            // validate the piece of data
            $this->_set($key, $val, $recursive);

            // reset rules
            $this->rules = array();
            $this->filters = array();
            return $val;
        }

        /**
         * Set recursively validation of value
         *
         * @access protected
         * @param string $key
         * @param mixed $val
         * @return boolean
         */
        protected function _set($key, $val, $recursive = false) {
            if ($recursive && is_array($val)) {
                // run validations on each element of the array
                foreach ($val as $index => $item) {
                    if (!$this->_set($key, $item, $recursive)) {
                        // halt validation for this value.
                        return FALSE;
                    }
                }
                return true;
            } else {

                // try each rule function
                foreach ($this->rules as $rule => $is_true) {
                    if ($is_true) {
                        $function = $this->functions[$rule];
                        $args = $this->arguments[$rule]; // Arguments of rule

                        $valid = (empty($args)) ? $function($val) : $function($val, $args);

                        if ($valid === FALSE) {
                            $this->_registerError($rule, $key);

                            $this->rules = array();  // reset rules
                            $this->filters = array();
                            return FALSE;
                        }
                    }
                }

                $this->validData->set($key, $val);
                return true;
            }
        }

        /**
         * Whether errors have been found
         *
         * @return bool
         */
        public function hasErrors() {
            return (count($this->errors) > 0);
        }

        /**
         * Get specific error
         *
         * @param string $field
         * @return string
         */
        public function getError($field) {
            return $this->errors[$field];
        }

        /**
         * Validate setted values
         * 
         * @return lahaina\framework\data\ContainerValidated data
         * @throws ValidationException
         */
        public function validate() {
            // check for errors
            if ($this->hasErrors()) {
                throw new ValidationException('There were validation errors', $this->getAllErrors());
            }

            return $this->getValidData();
        }

        /**
         * Get all errors
         *
         * @return array
         */
        public function getAllErrors($keys = true) {
            return ($keys == true) ? $this->errors : array_values($this->errors);
        }

        /**
         * Returns valid data
         * 
         * @return mixed 
         */
        public function getValidData() {
            return $this->validData;
        }

        /**
         * _getVal with added support for retrieving values from numeric and
         * associative multi-dimensional arrays. When doing so, use DOT notation
         * to indicate a break in keys, i.e.:
         *
         * key = "one.two.three"
         *
         * would search the array:
         *
         * array('one' => array(
         *      'two' => array(
         *          'three' => 'RETURN THIS'
         *      )
         * );
         *
         * @param string $key
         * @return mixed
         */
        protected function _getVal($key) {
            // handle multi-dimensional arrays
            if (strpos($key, '.') !== FALSE) {
                $arrData = NULL;
                $keys = explode('.', $key);
                $keyLen = count($keys);
                for ($i = 0; $i < $keyLen; ++$i) {
                    if (trim($keys[$i]) == '') {
                        return false;
                    } else {
                        if (is_null($arrData)) {
                            if (!$this->data->exists($keys[$i])) {
                                return false;
                            }
                            $arrData = $this->data->get($keys[$i]);
                        } else {
                            if (!$arrData->exists($keys[$i])) {
                                return false;
                            }
                            $arrData = $arrData->get($keys[$i]);
                        }
                    }
                }
                return $arrData;
            } else {
                return ($this->data->exists($key) ? $this->data->get($key) : FALSE);
            }
        }

        /**
         * Register error
         *
         * @param string $rule
         * @param string $key
         * @param string $message
         */
        protected function _registerError($rule, $key, $message = null) {
            if (empty($message)) {
                $message = $this->messages[$rule];
            }

            $this->errors[$key] = sprintf($message, $this->fields[$key]);
        }

        /**
         * Set rule
         *
         * @param string $rule
         * @param closure $function
         * @param string $message
         * @param array $args
         */
        protected function _setRule($rule, $function, $message = '', $args = array()) {
            if (!array_key_exists($rule, $this->rules)) {
                $this->rules[$rule] = true;
                if (!array_key_exists($rule, $this->functions)) {
                    if (!is_callable($function)) {
                        die('Invalid function for rule: ' . $rule);
                    }
                    $this->functions[$rule] = $function;
                }
                $this->arguments[$rule] = $args; // Specific arguments for rule

                $this->messages[$rule] = (empty($message)) ? $this->_getDefaultMessage($rule, $args) : $message;
            }
        }

        /**
         * Get default error message.
         *
         * @param string $key
         * @param array $args
         * @return string
         */
        protected function _getDefaultMessage($rule, $args = null) {

            switch ($rule) {
                case 'email':
                    $message = '%s is an invalid email address.';
                    break;

                case 'ip':
                    $message = '%s is an invalid IP address.';
                    break;

                case 'url':
                    $message = '%s is an invalid url.';
                    break;

                case 'required':
                    $message = '%s is required.';
                    break;

                case 'float':
                    $message = '%s must consist of numbers only.';
                    break;

                case 'integer':
                    $message = '%s must consist of integer value.';
                    break;

                case 'digits':
                    $message = '%s must consist only of digits.';
                    break;

                case 'min':
                    $message = '%s must be greater than ';
                    if ($args[1] == true) {
                        $message .= 'or equal to ';
                    }
                    $message .= $args[0] . '.';
                    break;

                case 'max':
                    $message = '%s must be less than ';
                    if ($args[1] == true) {
                        $message .= 'or equal to ';
                    }
                    $message .= $args[0] . '.';
                    break;

                case 'between':
                    $message = '%s must be between ' . $args[0] . ' and ' . $args[1] . '.';
                    if ($args[2] == FALSE) {
                        $message .= '(Without limits)';
                    }
                    break;

                case 'minlength':
                    $message = '%s must be at least ' . $args[0] . ' characters or longer.';
                    break;

                case 'maxlength':
                    $message = '%s must be no longer than ' . $args[0] . ' characters.';
                    break;

                case 'length':
                    $message = '%s must be exactly ' . $args[0] . ' characters in length.';
                    break;

                case 'matches':
                    $message = '%s must match ' . $args[1] . '.';
                    break;

                case 'notmatches':
                    $message = '%s must not match ' . $args[1] . '.';
                    break;

                case 'startsWith':
                    $message = '%s must start with "' . $args[0] . '".';
                    break;

                case 'notstartsWith':
                    $message = '%s must not start with "' . $args[0] . '".';
                    break;

                case 'endsWith':
                    $message = '%s must end with "' . $args[0] . '".';
                    break;

                case 'notendsWith':
                    $message = '%s must not end with "' . $args[0] . '".';
                    break;

                case 'date':
                    $message = '%s is not valid date.';
                    break;

                case 'mindate':
                    $message = '%s must be later than ' . $args[0]->format($args[1]) . '.';
                    break;

                case 'maxdate':
                    $message = '%s must be before ' . $args[0]->format($args[1]) . '.';
                    break;

                case 'oneof':
                    $message = '%s must be one of ' . implode(', ', $args[0]) . '.';
                    break;

                case 'ccnum':
                    $message = '%s must be a valid credit card number.';
                    break;

                default:
                    $message = '%s has an error.';
                    break;
            }

            return $message;
        }

    }
    