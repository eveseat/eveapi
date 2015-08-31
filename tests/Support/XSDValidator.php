<?php

namespace Seat\Eveapi\Test\Support;

use DOMDocument;

//use XMLReader;

class XSDValidator
{

    protected $xml;

    protected $xsd_file;

    public function setXML($xml)
    {

        $this->xml = $xml;

        return $this;
    }

    public function setXSDFile($xsd_file)
    {

        if (!is_file($xsd_file))
            throw new \Exception('XSD file ' . $xsd_file . ' not found.');

        $this->xsd_file = $xsd_file;

        return $this;
    }

    public function validate()
    {

        libxml_use_internal_errors(true);

        if (!$this->xml)
            throw new \Exception('You must provide XML.');

        $xml = simplexml_load_string($this->xml);
        if (!$xml) {
            $errors = $this->getXMLErrorsString();
            throw new \Exception(
                'XML does not validate XSD file ' . $this->xsd_file . ' : ' . $errors);
        }

        //validating with xsd
        $xml = new DOMDocument();
        $xml->loadXML($this->xml);

        if (!$this->xml)
            throw new \Exception(
                'You must provide a XSD file with XSDValidator::setXSDFile.');

        if (!$xml->schemaValidate($this->xsd_file)) {

            $errors = $this->getXMLErrorsString();
            throw new \Exception(
                'XML does not validate XSD file: ' . basename($this->xsd_file) .
                PHP_EOL . $errors);
        }

        return true;
    }

    public function getXMLErrorsString()
    {

        $errors_string = '';
        $errors = libxml_get_errors();

        foreach ($errors as $key => $error) {

            $level = $error->level === LIBXML_ERR_WARNING ?
                'Warning' : $error->level === LIBXML_ERR_ERROR ? 'Error' : 'Fatal';

            $errors_string .= '[' . $level .'] ' . trim($error->message) .
                ' Check XML on line ' . $error->line . ' col '. $error->column;

//            $errors_string .= PHP_EOL;
        }

        libxml_clear_errors();

        return $errors_string;
    }
}
