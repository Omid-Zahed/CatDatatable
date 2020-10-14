<?php

namespace Cat;


use Illuminate\Database\Eloquent\Builder;

class Table
{

    public function __construct($model)
    {

        $this->model=new $model;


    }
    public $model;


    protected  function addToURL( $key, $value="", $url=null) {


        if ($url==null)$url=url()->full();

        $parse_url = parse_url($url);
        $queries = $this->GetParameterUrl($url);
        if (is_array($key)){
            $queries=array_merge($queries,$key);
        }else{
            $queries[$key]=$value;
        }
        $parse_url["query"]= http_build_query($queries);

        if ( isset($parse_url["port"])){
            $parse_url["port"]=":".$parse_url["port"];
        }else $parse_url["port"]='';


       $url= $parse_url["scheme"]."://".$parse_url["host"].$parse_url["port"].$parse_url["path"]."?".
           $parse_url["query"];



        return $url;

    }
    protected  function GetParameterUrl($url=null){
        if ($url==null)$url=url()->full();
       $result=[];
       $query=parse_url($url,PHP_URL_QUERY);
       if ( $query==null)return [];
       parse_str($query,$result);
       return $result;
    }
    protected  function generateHeaderUrl(Column $column){
        $title=$column->getTitle();
        $params=$this->GetParameterUrl();
        $isExistParamInUrl=in_array($title,$params);

        if ($isExistParamInUrl)  {
            $sort_type=$params["sort_type"]??"asc";
            $sort_type=="asc"?$sort_type="desc":$sort_type="asc";
            return $this->addToURL(["sort"=>$title,"sort_type"=>$sort_type]);
        }
       return $this->addToURL(["sort"=>$title,"sort_type"=>"asc"]);
    }

    /**
     * @var Column[] $Columns
     */
    protected  $Columns=[];
    protected  $header=[];

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

        $this->header[]=$Column->getTitle();
        $this->Columns[]= $Column;
        return $this;
    }


    private $where=[];
    public function addWhere($where){

        $this->where[]=$where;

        return $this;
    }
    protected function CreateWhere($model){
        $search_type=request("search_type");
        $search=request("search");
        if ($search!=null && $search_type!=null && in_array($search_type,$this->header)){
           return $model::where($search_type,"like","%$search%");
        }

        return   $model::where($this->where);
    }
    protected function CreateSort(Builder $builder){
        $sort=request("sort");
        if ($sort!=null &&  in_array($sort,$this->header)){

            $sort_type=request("sort_type")??"desc";
            in_array($sort_type,["asc","desc"])==true?:$sort_type="desc";


            return $builder->orderBy($sort,$sort_type);
        }
        return $builder;
    }
    /**
     * @return Illuminate\Database\Eloquent\Collection
     */
    private function FetchData(){
        $where=$this->CreateWhere($this->model);
        if ($where) return $this->CreateSort($where)->get();

        return $this->CreateSort($this->model::where("id","!=",0))->get();
    }
    public function ToHtml(){

        $this->GetParameterUrl();
        $header="";
        $body="";
        $search_option="";

        foreach ($this->Columns as $key=>$column){
            if (array_key_first($this->Columns)==$key) $header.="<thead><tr>";

            $header.="<th><a href='".$this->generateHeaderUrl($column)."'>".$column->getTitle()."</a></th>";
            $isSelect=null;
            $search_type=request("search_type")??"";
            $search_type!=$column->getTitle()?:$isSelect="selected";

            $search_option.="<option ".$isSelect." value=".$column->getTitle().">".$column->getTitle()."</option>";

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

        $search=request("search")??"";
        return "<table>". $header.$body."</table>
<form>
<select name='search_type'>
".$search_option."
</select>
<input name='search' value='".$search."'>

</form>
";

    }



}
