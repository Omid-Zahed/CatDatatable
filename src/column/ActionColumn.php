<?php
namespace Cat\column;
use Cat\Column;

class ActionColumn extends Column
{
public function __construct($actions,$title="اکشن")
{
    parent::__construct($title);
    $this->setSortable(false);
    $this->SearchAbel(false);
    $this->setRowFunction(function ($model)use($actions){
       $action_="";
        foreach ($actions as $action) {
            $color=$action[2]??"";
            $url=$action[1]->call($this,$model);
          $action_.="<a href='$url' class='btn  vazir text-white  btn-sm $color'>".$action[0]."</a>";
        }
        return $action_;
    });
}
}
