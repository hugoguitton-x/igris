<?php

namespace App\Data;

use App\Entity\CategorieDepense;

class DepenseSearchData
{

    /**
     * @var CategorieDepense
     */
    public $categories = null;

    /**
     * @var \DateTime
     */
    public $date;

    public function __construct()
    {
        $this->date = new \DateTime();
    }
}
