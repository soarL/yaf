<?php
namespace plugins\ancun;

class ClientUser
{

    public $userIdcard;

    public $userMobile;

    public $userTruename;

    public function getUserIdcard()
    {
        return $this->userIdcard;
    }

    public function setUserIdcard($userIdcard)
    {
        $this->userIdcard = $userIdcard;
    }

    public function getUserMobile()
    {
        return $this->userMobile;
    }

    public function setUserMobile($userMobile)
    {
        $this->userMobile = $userMobile;
    }

    public function getUserTruename()
    {
        return $this->userTruename;
    }

    public function setUserTruename($userTruename)
    {
        $this->userTruename = $userTruename;
    }
}