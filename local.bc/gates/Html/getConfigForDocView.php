<?php
namespace Html;

class getConfigForDocView extends \Gate
{

	function gate()
	{
		$data = $this->data;
        if(!is_array($data)){
            $data=$data->toArray();
        }

        $document = loadEntity("urn-document-".$data["document_id"]);
        $user = loadEntity("urn-user-".$data["user"]);
        $post = loadEntity("urn-post", ["user"=>$user->urn]);
        if(count($post)){
            $posttype=$post->posttype;
            $depart=$post->department;
        }
        $conf=[];
        $conf["bot"]=[];
        $conf["right"]=[];

        //Конфиг ля показа уже утверждённых документов
        if( $document->docworkflow=="approved" ||
            $document->docworkflow=="oldversion" ||
            $document->docworkflow=="archive"
        ){
            $conf= $this->configForAprovedAndLayter($document, $conf, $user, $post, $posttype, $depart);
            return $conf;
        }else{
            $conf= $this->configBeforAprovedDoc($document, $conf, $user);
            return $conf;
        }


    return $conf;

	}


    function convertForSpecialDocument($doc, $conf){
        $doctype=$doc->doctype;
        //todo
    }

    function configBeforAprovedDoc($doc, $conf, $user){

    if($doc->docworkflow=="new" || $doc->docworkflow=="returned"){
        if($doc->author_id==$user->id){
            $conf["view"]=1;
            $conf["infoblock"]=1;
            $conf["bot"]["sendtovise"]=1;
            $conf["bot"]["save"]=1;

        }
    }

    elseif($doc->docworkflow=="discussion"){

        if($doc->author_id==$user->id){
            $conf["view"]=1;
            $conf["right"]["infoblock"]=1;
            $conf["bot"]["return"]=1;
        }
        if($this->isDiscuse($doc, $user)){
            $conf["view"]=1;
            $conf["right"]["infoblock"]=0;
            $conf["comment"]=1;
            $conf["bot"]["vise"]=1;
        }
    }

    elseif($doc->docworkflow=="vising"){

        if($doc->author_id==$user->id){
            $conf["view"]=1;
            $conf["right"]["infoblock"]=1;
            $conf["bot"]["return"]=1;
        }
        if($this->isVisant($doc, $user)){
            $conf["view"]=1;
            $conf["right"]["infoblock"]=0;
            $conf["bot"]["vise"]=1;
        }
    }
    elseif($doc->docworkflow=="approving"){
        if($doc->author_id==$user->id){
            $conf["view"]=1;
            $conf["right"]["infoblock"]=1;
            $conf["bot"]["return"]=1;
        }
        if($doc->author_id == $user->id){
            $conf["view"]=1;
            $conf["right"]["infoblock"]=0;
            $conf["bot"]["aprove"]=1;
        }

    }
    return $conf;
    }


    function isDiscuse($doc, $user){
        $visants=$doc->discussionusers;
        foreach ($visants as $vs) {
            if($vs->user_id==$user->id) return true;
        }
        return false;
    }

    function isVisant($doc, $user){
        $visants=$doc->visaposts;
        foreach ($visants as $vs) {
            if($vs->user_id==$user->id) return true;
        }
        return false;
    }


    function configForAprovedAndLayter($doc, $conf, $user, $post, $posttype, $depart){


     $virtualcopy=loadEntity("urn-virtualcopy", ["isactive"=>0, "document"=>$doc->urn, "person"=>$user->urn]);
        if(count($virtualcopy)>0){
            $conf["view"]=1;
            $conf["infoblock"]=0;
        }

     $acuser=loadEntity("urn-acuserdocument", ["user"=>$user->urn, "doctype"=>$doc->doctype, "limit"=>1]);
     $acpost=loadEntity("urn-acpostdocument", ["user"=>$post->urn, "doctype"=>$doc->doctype, "limit"=>1]);
     $acposttype=loadEntity("urn-acposttypedocument", ["user"=>$posttype->urn, "doctype"=>$doc->doctype, "limit"=>1]);
     $acdepaartamenttype=loadEntity("urn-acdepartamentdocument", ["user"=>$depart->urn, "doctype"=>$doc->doctype, "limit"=>1]);
     if(count($acuser)>0){
            return  $this->rulehandler($conf, $acuser);
     } elseif (count($acpost)>0){
         return  $this->rulehandler($conf, $acpost);
     } elseif (count($acposttype)>0){
         return $this->rulehandler($conf, $acposttype);
     } elseif (count($acdepaartamenttype)>0){
         return  $this->rulehandler($conf, $acdepaartamenttype);
     }
    }

    function rulehandler($conf, $rule){
        if($rule->permissionread=="granted"){
              $conf["view"]=1;
              $conf["infoblock"]=0;
        }
        if($rule->sendforarchive=="granted") $conf["right"]["btarchive"] = 1;
        if($rule->createchilddoc=="granted") $conf["right"]["btncreatechildren"] = 1;
        if($rule->createrealcopy=="granted") $conf["right"]["realcopyblock"] = 1;
        if($rule->createnoncontrollcopy=="granted") $conf["right"]["noncontrollcopyblock"] = 1;
        if($rule->createvirtualcopy=="granted") $conf["right"]["virtualcopyblock"] = 1;
        if($rule->print=="granted") $conf["right"]["print"] = 1;
        if($rule->addrelativedoc=="granted") $conf["right"]["relativedocument"] = 1;
        if($rule->createchilddoc=="granted") $conf["right"]["btncreatechildren"] = 1;
        if($rule->addrelativedoc=="granted") $conf["right"]["addrelativedocument"] = 1;
            return $conf;
    }


}

?>