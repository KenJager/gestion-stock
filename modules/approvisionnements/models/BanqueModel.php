<?php
// modules/approvisionnements/models/Banque.php

namespace approvisionnements\models;

class BanqueModel extends \Model {
    protected string $table = 'banques';

    public function listerTout(): array {
        return \Database::query("SELECT * FROM banques ORDER BY nom")->fetchAll();
    }
}