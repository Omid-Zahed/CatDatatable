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
        $key=$column->getKey();
        $params=$this->GetParameterUrl();
        $isExistParamInUrl=in_array($key,$params);

        if ($isExistParamInUrl)  {
            $sort_type=$params["sort_type"]??"asc";
            $sort_type=="asc"?$sort_type="desc":$sort_type="asc";
            return $this->addToURL(["sort"=>$key,"sort_type"=>$sort_type]);
        }
       return $this->addToURL(["sort"=>$key,"sort_type"=>"asc"]);
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
        if(!($Column instanceof Column)) throw new  Exception("Column param should string or Cat\Column");



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
            $this->where[]=[$search_type,"like","%$search%"];
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

    public function GetTable(){

        $header=[];
        $body=[];
        $search_option=[];
        $info=[];




        foreach ($this->Columns as $key=>$column){

            $url=null;
            if ($column->isSortable()==true) {$url=$this->generateHeaderUrl($column);}
            $header[]=[
                 "url"=>$url,
                "title"=>$column->getTitle()
            ];

            if (!$column->isSearchAbel()) continue;
            $isSelect=null;
            $search_type=request("search_type")??"";
            $search_type!=$column->getKey()?:$isSelect="selected";
            $search_option[]=[
                "isSelect"=>$isSelect,
                "title"=>$column->getTitle(),
                "value"=>$column->getKey()
            ];
        }
        foreach ($this->FetchData() as $key=>$model){
            $row=[];
            foreach ($this->Columns as $column){
                $row[]=$column->getRow($model);
            }
            $body[]=$row;
        }

        $search=request("search")??"";
        $info["search"]=$search;

        return [
            "header"=>$header,
            "body"=>$body,
            "search_option"=>$search_option,
            "info"=>$info
        ];

    }



}
