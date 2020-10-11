<?php

namespace Cat;
use mysql_xdevapi\Exception;

class Table
{

    public function __construct($model)
    {
        $this->model=new $model;

    }
    public $model;

    protected  function addToURL( $key, $value, $url=null) {

        if ($url==null)$url=url()->full();

        $query = parse_url($url, PHP_URL_QUERY);


        if ($query) {
            $url .= '&'.$key."=".$value;
        } else {
            $url .= '?'.$key."=".$value;;
        }

        return $url;

    }

    /**
     * @var Column[] $Columns
     */
    public  $Columns=[];

    /**
     * @param Column|string $Column
     * @return Table
     */
    public function setColumns( $Column): Table
    {
        if (is_string($Column)){
            $Column=new Column($Column);
        }
        if(get_class($Column)!="Cat\Column") throw new  Exception("Column param should string or Cat\Column");

        $this->Columns[]= $Column;
        return $this;
    }


    /**
     * @return Illuminate\Database\Eloquent\Collection
     */
    private function FetchData(){
        return $this->model::all();
    }
    public function ToHtml(){
        $header="";
        $body="";
        $search_option="";

        foreach ($this->Columns as $key=>$column){
            if (array_key_first($this->Columns)==$key) $header.="<thead><tr>";

            $header.="<th><a href='".$this->addToURL("sort",$column->getTitle())."'>".$column->getTitle()."</a></th>";
            $search_option.="<option value=".$column->getTitle().">".$column->getTitle()."</option>";

            if (array_key_last($this->Columns)==$key) $header.="</tr></thead>";
        }

        /**
         * Collection $dataFetched
         */
        $dataFetched=$this->FetchData();


        foreach ($dataFetched as $key=>$model){
            $body.="<tr>";

            foreach ($this->Columns as $column){
                $body.="<td>".$column->getRow($model)."</td>";

            }
            $body.="</tr>";

        }

        return "<table>". $header.$body."</table>
<form>
<select name='search_type'>
".$search_option."
</select>
<input name='search'>
</form>
";

    }



}
