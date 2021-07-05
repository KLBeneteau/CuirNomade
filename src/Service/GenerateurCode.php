<?php

namespace App\Service;

class GenerateurCode {

    public function createNewCode() {
        $code = "" ;
        for ($i=1; $i<20 ; $i++){
            $code .= rand(0,9) ;
        }
        return $code;
    }

}
