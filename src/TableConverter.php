<?php
namespace Cat;
use Cat\Table;
 class TableConverter{
    protected  $Table;
    public  $style;
     public  $id;
     public  $className="table";
     public  $id_form;
     public  $className_form;
     public $baseRoute;
     public $className_input='form-control text-center  vazir';

    public function __construct(Table $table,$baseRoute)
    {
        $this->Table=$table;
        $this->baseRoute=$baseRoute;

    }

    public function Convert(){
        $tableData=$this->Table->GetTable();
        if (count($tableData["body"])<=0){
            if (empty($tableData["info"]["search"])){
                return '<h5 class="text-center">چیزی یافت نشد</h5>';
            }else{
                return '<h5 class="text-center">چیزی یافت نشد</h5>

                    <a href='.$this->baseRoute.' class="btn btn-danger vazir" >حذف جستجو </a>
';

            }
        }


        $header="";
        $body="";
        $search_option="";


        foreach ($tableData["header"] as $key=>$header_){
            if (array_key_first($tableData["header"])==$key)
                $header.="<thead><tr>";

                $link='';
                empty($header_["url"])?: $link='href="'.$header_["url"].'"';
                $header.="<th><a $link>".$header_["title"]."</a></th>";

            if (array_key_last($tableData["header"])==$key)
                $header.="</tr></thead>";
        }
        foreach ($tableData["search_option"] as $option){


            $search_option.="<option ".$option["isSelect"]." value=".$option["value"].">".$option["title"]."</option>";

        }



        foreach ($tableData["body"] as $key=>$row){
            $body.="<tr>";

            foreach ($row as $sell){
                $body.="<td>".$sell."</td>";

            }
            $body.="</tr>";

        }









        $TableName=$this->Table->getTableName();
        return
           "


            <form id='".$this->id_form."' class='".$this->className_form."' >
                <select class='".$this->className_input."' name='".$TableName."_search_type'>$search_option</select>
                <input placeholder='جستجو' class='".$this->className_input."' name='".$TableName."_search' value='".$tableData["info"]["search"]."'>
            </form>


            <table id='.$this->id.'  class='".$this->className."' >". $header.$body."</table>
            ";

    }

}
