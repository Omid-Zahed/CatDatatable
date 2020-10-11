<?php
namespace Cat;

 use Illuminate\Database\Eloquent\Model;

 class Column
{



        public function __construct($title)
    {
        $this->title=$title;
        $this->SearchFunction=function ($value, $model){
            return $model;
        };
        $this->getRowFunction=function  (Model $model){ return $model[$this->title];};
    }

         private $title;
        /**
         * @return mixed
         */
        public function getTitle()
        {
            return $this->title;
        }
        /**
         * @param mixed $title
         * @return Column
         */
        public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }


     private $isSearchAbel=true;
     /**
      * @return bool
      */
     public function isSearchAbel(): bool
     {
         return $this->isSearchAbel;
     }
     /**
      * @param bool $isSearchAbel
      * @return Column
      */
     public function SearchAbel(bool $isSearchAbel): Column
     {
         $this->isSearchAbel = $isSearchAbel;
         return $this;
     }
     private $SearchFunction;
     /**
      * @param \Closure $SearchFunction
      * @return Column
      */
     public function setSearchFunction(\Closure $SearchFunction): Column
     {
         $this->SearchFunction = $SearchFunction;
         return $this;
     }
     /**
      * @param $value
      * @param Model $model
      * @return Model[]
      */
     function Search($value, $model){
         if (!$this->isSearchAbel)return false;
       return $this->SearchFunction->call($this,$value,$model);
    }


     private $getRowFunction;
     function getRow(Model $model){
        return $this->getRowFunction->call($this,$model);
    }
     /**
      * @param \Closure $getRowFunction
      * @return Column
      */
     public function setRowFunction(\Closure $getRowFunction): Column
     {
         $this->getRowFunction = $getRowFunction;
        return $this;
     }





     function OrderBy(){}
 }
