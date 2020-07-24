<?php

namespace Vgsite\PDO;

use PDOException;
use PDOStatement;
use Vgsite\Registry;

class MyPDOStatement extends PDOStatement
{
    protected $_debugValues = null;
    protected $_ValuePos = 0;

    protected function __construct()
    {
        // need this empty construct()!
    }

    // public function bindValue($parameter, $value, $data_type = \PDO::PARAM_STR)
    // {
    //     printf('bind:%s=%s'.PHP_EOL, $parameter, $value);
    //     parent::bindValue($parameter, $value, $data_type);
    // }

    ///\brief overrides execute saving array of values and catching exception with error logging
    public function execute($input_parameters = null)
    {
        $this->_debugValues = $input_parameters;
        $this->_ValuePos    = 0;

        $logger = Registry::get('logger');

        try {
            $t = parent::execute($input_parameters);

            if ($logger) $logger->debug($this->_debugQuery());
        } catch (PDOException $e) {
            if ($logger) {
                $logger->warning('PDOException thrown based on the following query: '.$this->_debugQuery().'; Detail: '.$e->getMessage());
            }

            throw $e;
        }

        return $t;
    }

    ///\brief Retrieves query text with values for placeholders
    public function _debugQuery($replaced = true)
    {
        $q = $this->queryString;

        if (!$replaced) {
            return $q;
        }

        return preg_replace_callback('/(:([0-9a-z_]+)|(\?))/i', array(
            $this,
            '_debugReplace'
        ), $q);
    }

    ///\brief Replaces a placeholder with the corresponding value
    //$m is the name of a placeholder
    protected function _debugReplace($m)
    {
        if ($m[1] == '?') {
            $v = $this->_debugValues[$this->_ValuePos++];
        } else {
            $v = $this->_debugValues[$m[1]];
        }
        if ($v === null) {
            return "NULL";
        }
        if (!is_numeric($v)) {
            $v = str_replace("'", "''", $v);
        }

        return "'" . $v . "'";
    }
}
