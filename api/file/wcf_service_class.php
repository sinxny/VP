<?php
class FileWcfService
{
    protected $wsdl = _WSDL_TRANSFERWEB_URL_;
    /**
     * PHP5용 Class 생성자
     */
    public function __construct($input_wsdl = null){
        $this->FileWcfService($input_wsdl);
    }
    /**
     * Class 생성자
     */
    function FileWcfService($input_wsdl = null)
    {
        if(isset($input_wsdl) && trim($input_wsdl))
        {
            
        }
    }
}