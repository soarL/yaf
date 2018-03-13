<?php
namespace plugins\ancun;

class Enterprise
{

    public $orgCode;

    public $orgName;

    public $orgEmail;

    public function setOrgCode($orgCode)
    {
        $this->orgCode = $orgCode;
    }

    public function getOrgCode()
    {
        return $this->orgCode;
    }

    public function setOrgName($orgName)
    {
        $this->orgName = $orgName;
    }

    public function getOrgName()
    {
        return $this->orgName;
    }

    public function setOrgEmail($orgEmail)
    {
        $this->orgEmail = $orgEmail;
    }

    public function getOrgEmail()
    {
        return $this->orgEmail;
    }
}