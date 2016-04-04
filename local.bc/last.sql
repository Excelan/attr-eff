--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


--
-- Name: plv8; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plv8 WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plv8; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plv8 IS 'PL/JavaScript (v8) trusted procedural language';


SET search_path = public, pg_catalog;

--
-- Name: action_add(json); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION action_add(m json) RETURNS text
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$
  
  var urn=m.to.split("-")
  var entity = urn[1]
  var entity_config = plv8.find_function("entity_config_"+entity)();
  var id = urn[2] || m.id || null
  if(!id){plv8.elog(ERROR, "cant find id")}
  
  var field = urn[3]
  if(entity_config.lists[field]){
  var urn2 = m.urn.split("-")
  var r = plv8.execute("UPDATE"+entity+" SET "+ field+" = "+field+" || "+urn2[2]+" where id ="+id);
  if(entity_config.lists[field].reverse){
	  var revf=entity_config.lists[field].reverse
	   var r = plv8.execute("UPDATE"+urn2[1]+" SET "+ revf+" = "+revf+" || "+id+" where id ="+urn2[2]);
  }
  }
  
  
  $$;


ALTER FUNCTION public.action_add(m json) OWNER TO bc;

--
-- Name: action_create(json); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION action_create(m json) RETURNS text
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$
  var urn = m.urn.split("-")
  var entity = urn[1]
  var entity_config = plv8.find_function("entity_config_"+entity)();
  var typeConvertor = plv8.find_function("typeConvertor");
 if(entity_config == null){plv8.elog(ERROR, "urn '"+m.urn+"' does  not exist");}
 

 var simplefields=entity_config.fields
 var statuses=entity_config.status
 var useone=entity_config.useone
 var usemany=entity_config.usemany
 var lists=entity_config.lists
 
 var keys = ""

 var values=""

 if(m.data==null){
	  var rezult={}
 /* {"name":"title", "type":"text", "default":""}*/
  var id=m["id"] || urn[2] || null
     rezult.id=id
  if(id===null){plv8.elog(ERROR, "cannot find id, in json  "+JSON.stringify(m));}
  keys+="id, "
  values+=id+", "
  
  for(var i in simplefields){
  var field = simplefields[i] 
  if(m[field.name] == null||m[field.name]==""){
	  keys+= field.name+", ";
	  values+= typeConvertor(field.type, field.default)+", ";
	 if(!field.lazy){rezult[field.name]=field.default}
	  continue;
	  }
  keys+=field.name+", "
  values+=typeConvertor(field.type, m[field.name])+", "
  if(!field.lazy){rezult[field.name]=m[field.name]}
  }
  
  for(var i in statuses){
  var field = statuses[i]
  if(m[field.name] == null||m[field.name]==""){
	  keys+= field.name+", ";
	  values+="'"+field.default+"'::INTEGER , "
	  rezult[field.name]=field.default;
	  continue;
	  }
	  keys+= field.name+", ";
	  values+="'"+m[field.name]+"'::INTEGER , "  
	  rezult[field.name]=m[field.name];
	  
  }
	  /* useone:[{"entity":"category", "alias":"category"}]  */
   for(var i in useone){
    var field = useone[i]
    if(m[field.alias] == null||m[field.alias]==""){continue}
	var temp = m[field.alias].split("-")[2]
	if(temp==null){continue}
  	keys+=field.alias+"_id, "
	values += "'"+temp+"'::INTEGER , "
	rezult[field.alias]=temp;
  }

  for(var i in lists){
  var field = lists[i]
    if(m[field.alias] == null||m[field.alias]=="" || !Array.isArray(m[field.alias])){continue}
	// TODO РАСЧИТАНО НА ТО ЧТО ПРИДЁТ МАССИВ ИЗ ЧИСТЫХ ИД А НЕ ИЗ УРЛОВ
	// var temp = m[key].split("-")[2]
	// if(temp==null){continue}
  	keys+=field.alias+", "
    values += "'{"+m[field.alias].join(',')+"}'::INTEGER[], "
	rezult[field.alias]=temp;
  }
  
    values=values.slice(0,-2)
	keys=keys.slice(0,-2)
  
var exe='INSERT INTO '+entity+'('+keys+") values ("+values+")"
 plv8.elog(NOTICE, "SQL command = ", exe)
 var id = plv8.execute(exe)
 return JSON.stringify(rezult)
  }
  
 if(m.data!=null&&Array.isArray(m.data)&& m.data.length>0){
	 var rezult=[]
    // plv8.elog(INFO, "fieldsTitle"+fieldsTitle+"\n fieldsHasOne"+fieldsHasOne)
	// keys = '('+fieldsTitle.join(",")+","+fieldsHasOne.join(",")+')'
	keys = 'id, '
	for(var i in simplefields){
		keys+=simplefields[i].name+", "
	}
	for(var i in statuses){
		keys+=statuses[i].name+", "
	}
	for(var i in useone){
		keys+=useone[i].alias+"_id, "
	}

	for(var i in lists){
		keys+=lists[i].alias+", "
	}
	
	
	
	values = '('
	for(var d in m.data){
	var rez={}		
	var md = m.data[d]
	 var id=md["id"] || null
        rez.id=id
  if(id===null){plv8.elog(ERROR, "cannot find id, in json.data at index  "+d);}
  
  values+=id+", "
  
  for(var i in simplefields){
  var field = simplefields[i] 
  if(md[field.name] == null||md[field.name]==""){
	
	  values+= typeConvertor(field.type, field.default)+", ";
	 if(!field.lazy){rez[field.name]=field.default}
	  continue;
	  }
 
  values+=typeConvertor(field.type, md[field.name])+", "
  if(!field.lazy){rez[field.name]=md[field.name]}
  }
  
  for(var i in statuses){
  var field = statuses[i]
  if(md[field.name] == null||md[field.name]==""){
	 
	  values+="'"+field.default+"'::INTEGER , "
	  rez[field.name]=field.default;
	  continue;
	  }
	  values+="'"+md[field.name]+"'::INTEGER , "  
	  rez[field.name]=md[field.name];
  }
	  /* useone:[{"entity":"category", "alias":"category"}]  */
 for(var i in useone){
  var field = useone[i]
    if(md[field.alias] == null||md[field.alias]==""){values +="null, ";continue}
	var temp = md[field.alias].split("-")[2]
	if(temp==null){values +="null, ";continue}
  	
	values += "'"+temp+"'::INTEGER , "
	rez[field.alias]=temp;
  }

  for(var i in lists){
  var field = lists[i]
    if(md[field.alias] == null||md[field.alias]=="" || !Array.isArray(md[field.alias])){
		values +="null, ";continue}
  
    values += "'{"+md[field.alias].join(',')+"}'::INTEGER[], "
	rez[field.alias]=temp;
  }
	
		rezult.push(rez);
		values=values.slice(0,-2)
		values+='),('
		
	}
	values=values.slice(0,-2)
	var exe='INSERT INTO '+entity+" ("+keys.slice(0,-2)+") values "+values
	plv8.elog(NOTICE, "SQL command = ", exe)
	var id = plv8.execute(exe)
	return JSON.stringify(rezult)
	}
  
 
plv8.elog(ERROR,'wrong json object')

$$;


ALTER FUNCTION public.action_create(m json) OWNER TO bc;

--
-- Name: action_delete(json); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION action_delete(m json) RETURNS text
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$
var urn=m.urn.split("-")
  var entity = urn[1]
 var entity_config = plv8.find_function("entity_config_"+entity)();
 if(entity_config == null){plv8.elog(ERROR, "urn '"+m.urn+"' does  not exist");}
 var id= m.id || urn[2] 
 plv8.elog(NOTICE, "urn[2] = "+urn[2])
 plv8.elog(NOTICE, "m.id  = "+m.id )
 plv8.elog(NOTICE, "id  = "+id )
 if(id==null){ plv8.elog(ERROR, '{"error":"msg doesn\'t contain id"}')}
  
 var fieldsTitle=entity_config.fields
 var fieldsType=entity_config.types
 var fieldsHasOne=entity_config.hasone




if(Array.isArray(id)&& id.length>0){
var d=[]
for (var f in id){
var exe='DELETE FROM '+entity+' WHERE id = '+id[f]+" RETURNING id"
plv8.elog(NOTICE, "SQL comand = ", exe)
d.push(plv8.execute(exe)[0])
}
return JSON.stringify(d)
}
/* если 'id' не является массивом */
else{
var exe='DELETE FROM '+entity+' WHERE id = '+id+" RETURNING id"
plv8.elog(NOTICE, "SQL comand = ", exe)
var d =  plv8.execute(exe)
return JSON.stringify(d)
}


$$;


ALTER FUNCTION public.action_delete(m json) OWNER TO bc;

--
-- Name: action_exists(json); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION action_exists(m json) RETURNS text
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$
  
  var urn=m.to.split("-")
  var entity = urn[1]
  var entity_config = plv8.find_function("entity_config_"+entity)();
  var id = urn[2] || m.id || null
  if(!id){plv8.elog(ERROR, "cant find id")}
  
  var field = urn[3]
  if(entity_config.lists[field]){
  var urn2 = m.urn.split("-")
  var r = plv8.execute("select id from"+ entity+" where id="+id+" and "+urn2[2]+"= ANY("+field+")" );
  if(r&&r.id!=undefined){
  return "true" 
  }else{return false}
  }
  
  
  $$;


ALTER FUNCTION public.action_exists(m json) OWNER TO bc;

--
-- Name: action_load(json); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION action_load(m json) RETURNS text
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$
 var urn=m.urn.split("-")
 var entity = urn[1]
 var entity_config = plv8.find_function("entity_config_"+entity)();

 var func1 = plv8.find_function("load_useone")	
  var func2 = plv8.find_function("load_usemany")
 if(entity_config == null){plv8.elog(ERROR, "urn '"+m.urn+"' does  not exist");}
 var typeConvertor = plv8.find_function("typeConvertor");
 
 var simplefields={} 				// [ title:{"name":"title", "type":"text",     "default":""}, ...]
 var notlazysimplefields={}         // [ title:{"name":"title", "type":"text",     "default":""}, ...]
 var statuses={} 					// [ active:{"name":"active","default":0}], ...]
 var useone={}  					// [ img:{"entity":"img", "alias":"img"}, ...]
 var lists={} 						// [ likers:{"entity":"user", "alias":"likers", "reverse":"likedcomments"} ...]
 var usemany=entity_config.usemany  // [ "comments", ...]
 

/* plv8.elog(INFO, "entity_config.fields " + JSON.stringify(entity_config.fields)+"\n entity_config.status"+JSON.stringify(entity_config.status)
	+"\n entity_config.useone"+JSON.stringify(entity_config.useone)+"\n entity_config.lists" + JSON.stringify(entity_config.lists))
  */
 for(var i in entity_config.fields){
	 simplefields[entity_config.fields[i].name]=entity_config.fields[i]
	 if(!entity_config.fields[i].lazy){
		 notlazysimplefields[entity_config.fields[i].name]=entity_config.fields[i]
	 }
 }
  
 for(var i in entity_config.status){
	 statuses[entity_config.status[i].name]=entity_config.status[i]
 }
  for(var i in entity_config.useone){
	 useone[entity_config.useone[i].alias]=entity_config.useone[i]
 }
   for(var i in entity_config.lists){
	 lists[entity_config.lists[i].alias]=entity_config.lists[i]
 }
/*  plv8.elog(INFO, "simplefields " + JSON.stringify(simplefields)+"\n statuses"+JSON.stringify(statuses)+"\n useone"+
	 JSON.stringify(useone)+"\n lists" + JSON.stringify(lists)) 
  */
 var order=""
 if(m.order){
	
	 for(var  f in m.order){
		 if(!simplefields[f]&&!statuses[f]&&!useone[f]&&!lists[f]){continue}
		 if(m.order[f]=="desc" || m.order[f]=="asc"){
		 order += f+" "+m.order[f]+" , "
	 }}
	 order=order.slice(0, -2)
 }
 

 
  var where = ""
  if(m.id&&!Array.isArray(m.id)){where+=" id ="+m.id+" AND "}
  if(m.id&&Array.isArray(m.id)&&m.id.length==2){where+=" id >"+m.id[0]+" AND "+"id<"+m.id[1]+" AND "}
  if(m.id&&Array.isArray(m.id)&&m.id.length>2){where+=" id in ("+m.id+") AND "}
  if(urn[2]){where+=" id ="+urn[2]+" AND "}

 for(var f in simplefields){

	 if(typeof m[f] != "undefined"){

		 var field=simplefields[f]
		 var val =  m[f]
		
		if(Array.isArray(val)){
			if(val.length==2){
				where += f+">"+typeConvertor(field.type, val[0])+" AND "+f+"<"+typeConvertor(field.type, val[1])
			}
			if(val.length>2){
				where += f+" IN ("+val.join(", ")+") "
			}
		}else{
		if(typeof val == "number" || typeof val == "string"|| val === null ){

			where += f+"="+typeConvertor(field.type, val)
		}
		
		if(typeof val == "object" && val !=null){
			if(val == null){where+=field.name+" is null"
			}else{
			for(var i in val){//"id":{"not":[43]} Цыкл используется чисто чтобы не доставать ключи
				var znak="="
				switch (i) {
					case "not":znak="!=";break;
					case "gt":znak=">";break;
					case "lt":znak="<";break;
				}
				if(val[i].length=1){					
				where += f+znak+typeConvertor(field.type, val[i][0])
				continue
				}
				if(val[i].length>1&&znak=="!="){
				where += f +" NOT IN ("+val[i].join(",")+") "
				}
			
			}				
			}
		}
		}
		where += " AND "
	 }
 }
  
 for(var f in statuses){
	 
	 if(typeof m[f] != "undefined"){
		 
		 var field=statuses[f]
		 var val =  m[f]
		
		if(Array.isArray(val)){
			if(val.length==2){
				where += f+">"+typeConvertor("INTEGER", val[0])+" AND "+f+"<"+typeConvertor("INTEGER", val[1])
			}
			if(val.length>2){
				where += f+" IN ("+val.join(", ")+") "
				
			}
		}else{
		if(typeof val == "number" || typeof val == "string"|| val === null ){
			where += f+"="+typeConvertor("INTEGER", val)
		
		}
		
		if(typeof val == "object" && val !=null){
			if(val == null){where+=field.name+" is null"
			}else{
			for(var i in val){//"id":{"not":[43]} Цыкл используется чисто чтобы не доставать ключи
				var znak="="
				switch (i) {
					case "not":znak="!=";break;
					case "gt":znak=">";break;
					case "lt":znak="<";break;
				}
				if(val[i].length=1){					
				where += f+znak+typeConvertor("INTEGER", val[i][0])
				continue
				}
				if(val[i].length>1&&znak=="!="){
				where += f +" NOT IN ("+val[i].join(",")+") "
				}
			
			}				
			}
			
		}
		}
		where += " AND "
	 }
 }
 
 
   
 for(var f in useone){

	 if(typeof m[f] != "undefined"){
		 var field=useone[f]
		 var val =  m[f].split('-')[2];

		if(Array.isArray(val)){

			if(val.length==2){
				where += f+"_id >"+typeConvertor("INTEGER", val[0])+" AND "+f+"<"+typeConvertor("INTEGER", val[1])
			}
			if(val.length>2){
				where += f+"_id  IN ("+val.join(", ")+") "
				
			}
		}else{

		if(typeof val == "number" || typeof val == "string"|| val === null ){
			where += f+"_id  ="+typeConvertor("INTEGER", val)
		}
		
		if(typeof val == "object" && val !=null){
			if(val == null){where+=f+"_id is null"
			}else{
			for(var i in val){//"id":{"not":[43]} Цыкл используется чисто чтобы не доставать ключи
				var znak="="
				switch (i) {
					case "not":znak="!=";break;
					case "gt":znak=">";break;
					case "lt":znak="<";break;
				}
				if(val[i].length=1){					
				where += f+"_id "+znak+typeConvertor("INTEGER", val[i][0])
				continue
				}
				if(val[i].length>1&&znak=="!="){
				where += f +"_id  NOT IN ("+val[i].join(",")+") "
				}
			
			}				
			}
			
		}
		}
		where += " AND "
	 }
 }
 
 
 
 
 where=where.slice(0, -5)
 
     var limit=""
	 if(m.limit){limit=" LIMIT "+m.limit}
	 if(where!=""){where=" WHERE "+where}
	 if(order!=""){ order = " ORDER BY "+order}

 var simplefieldsforload=[]
 var additionentityforload={}//{category:[title, image.uri]}
 var ownfields=['id']
 var enemyuseone={}
 var enemyusemany={}
 var listfields={}//{likers:[title]}
 
 
 // if  selectonly is define
 if(Array.isArray(m.selectonly)){
     plv8.elog(INFO, "statuses ---> "+JSON.stringify(simplefields))
     plv8.elog(INFO, "m.selectonly ---> "+m.selectonly)
  for(var p in m.selectonly){
	 var t = m.selectonly[p].split(".")
	 if(t.length==1&&(simplefields[t[0]]||statuses[t[0]]||useone[t[0]]||lists[t[0]])){
         if(useone[t[0]]){
             ownfields.push(t[0]+'_id')
         }else{
	 	ownfields.push(t[0])}
     continue;
	 }
		
	if(t.length>1&&(simplefields[t[0]]||statuses[t[0]]||useone[t[0]])){
  
		var en=t.shift()+'_id'
		if(!enemyuseone[en]){enemyuseone[en]=[]}
		enemyuseone[en].push(t.join("."))
	    continue;
	}
	if(t.length>1&&lists[t[0]]){
		var en=t.shift()
		if(!listfields[en]){listfields[en]=[]}
	 listfields[en].push(t.join("."))
	    continue;
	}
	
	if(t.length>1&&usemany.indexOf(t[0])!=-1){
		var en=t.shift()	
		if(!enemyusemany[en]){enemyusemany[en]=[]}
	enemyusemany[en].push(t.join("."))
	}
	
    }
     //plv8.elog(INFO, "ownfields ---> "+ownfields)
     //plv8.elog(INFO, "ownfields ---> "+Object.keys(enemyuseone))
	var tf = ownfields.concat(Object.keys(enemyuseone)).concat(Object.keys(listfields))
	var exe = "SELECT "+tf.join(",")+' FROM "'+entity+'"'+where+order+limit
	//plv8.elog(INFO, "SQL command ---> "+exe)
	var first_objects =  plv8.execute(exe)
	var hso ={}
		for(var i in first_objects){
		/* plv8.elog(INFO, " POINT 9 "+JSON.stringify(enemyuseone))  */
				/* //обработка has one */
		for(var q in  enemyuseone){
		// plv8.elog(INFO, " POINT 10 "+JSON.stringify(first_objects[i]))
		 plv8.elog(INFO, " POINT 10 "+JSON.stringify(q.split('-')))

			if(!hso[q]){hso[q]={}}
		var cid=first_objects[i][q]
		if(hso[q][cid]){ first_objects[i][q]=hso[q][cid];continue}
	    var vl = func1(useone[q.split('_')[0]].entity, cid, enemyuseone[q])/*     (entity, id, fileds) (category, 112, [title, img.uri])->{"sport", img:{uri:"./sport/12454.png"}} */
		first_objects[i][q]=vl
		hso[q][cid]=vl
	    }
	    /* // обработка has Many */
		// plv8.elog(INFO, " POINT 11 ") 
		for(var m in  enemyusemany){
		var va = func2(m, entity, [first_objects[i]["id"]], enemyusemany[m])/* //(entity, hasoneField, hasoneId, fields) (comments, news, 7, [autor])->[{autor:петя}, {author:вася}] */
		first_objects[i][m]=va
		}
		/* обработка листов TODO */
		// plv8.elog(INFO, " POINT 12 ") 
		for(var l in listfields){
			
			if(first_objects[i][l]&&first_objects[i][l].lengh!=0){
		var va = func2(lists[l].entity, "id", first_objects[i][l], listfields[l])/* //(entity, hasoneField, hasoneId, fields) (comments, news, 7, [autor])->[{autor:петя}, {author:вася}] */
		first_objects[i][l]=va
			}
		}		
	}
	return JSON.stringify(first_objects);
	
 }
 
 
	/* 
 var ownfields=[]
 var enemyuseone={}
 var enemyusemany={}
 var listfields={}//{likers:[title]} */
	m.additionalload    //Дополнительные поля
	notlazysimplefields //Поля который в конфиге указаны как не лейзи
	
	for(var p in m.additionalload){
	 var t = m.additionalload[p].split(".")
	 if(t.length==1&&(simplefields[t[0]]||statuses[t[0]]||useone[t[0]]||lists[t[0]])){
	 	ownfields.push(t[0])
     continue;
	 }
		
	if(t.length>1&&useone[t[0]]){

		var en=t.shift()
		if(!enemyuseone[en]){enemyuseone[en]=[]}
		enemyuseone[en].push(t.join("."))
	    continue;
	}
	if(t.length>1&&lists[t[0]]){
		var en=t.shift()
		if(!listfields[en]){listfields[en]=[]}
	 listfields[en].push(t.join("."))
	    continue;
	}
	
	if(t.length>1&&usemany.indexOf(t[0])!=-1){
		var en=t.shift()	
		if(!enemyusemany[en]){enemyusemany[en]=[]}
	enemyusemany[en].push(t.join("."))
	}
	
    }
	
	for(var i in notlazysimplefields){
		if(ownfields.indexOf(notlazysimplefields[i])==-1){
			ownfields.push(notlazysimplefields[i].name)
		}
	}
	
	var tf = ownfields.concat(Object.keys(enemyuseone)).concat(Object.keys(listfields))
		//plv8.elog(INFO, "TF "+JSON.stringify(listfields))
	var exe = "SELECT "+tf.join(",")+' FROM "'+entity+'" '+where+order+limit
		plv8.elog(INFO, "SQL command "+exe)
	var first_objects =  plv8.execute(exe)
	var hso ={}
		for(var i in first_objects){
				/* //обработка has one */
		for(var q in  enemyuseone){
			if(!hso[q]){hso[q]={}}
		var cid=first_objects[i][q]
		if(hso[q][cid]){ first_objects[i][q]=hso[q][cid];continue}
	    var vl = func1(q, cid, enemyuseone[q])/* // (entity, id, fileds) (category, 112, [title, img.uri])->{"sport", img:{uri:"./sport/12454.png"}} */
		first_objects[i][q]=vl
		hso[q][cid]=vl
	    }
	    /* // обработка has Many */
		for(var m in  enemyusemany){
		var va = func2(m, entity, [first_objects[i]["id"]], enemyusemany[m])/* //(entity, hasoneField, hasoneId, fields) (comments, news, 7, [autor])->[{autor:петя}, {author:вася}] */
		first_objects[i][m]=va
		}
		/* обработка листов TODO */
		for(var l in listfields){
		if(first_objects[i][l]&&first_objects[i][l].lengh!=0){
		var va = func2(lists[l].entity, "id", first_objects[i][l], listfields[l])/* //(entity, hasoneField, hasoneId, fields) (comments, news, 7, [autor])->[{autor:петя}, {author:вася}] */
		first_objects[i][l]=va
			}
		}
	}
	return JSON.stringify(first_objects);
	 
	
 
 
 $$;


ALTER FUNCTION public.action_load(m json) OWNER TO bc;

--
-- Name: action_loadbyexample(json); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION action_loadbyexample(m json) RETURNS text
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$
 var urn=m.urn.split("-")
 var entity = urn[1]
 var entity_config = plv8.find_function("entity_config_"+entity)();

 var func1 = plv8.find_function("load_useone")	
  var func2 = plv8.find_function("load_usemany")
 if(entity_config == null){plv8.elog(ERROR, "urn '"+m.urn+"' does  not exist");}
 var typeConvertor = plv8.find_function("typeConvertor");
 
 var simplefields={} 				// [ title:{"name":"title", "type":"text",     "default":""}, ...]
 var notlazysimplefields={}         // [ title:{"name":"title", "type":"text",     "default":""}, ...]
 var statuses={} 					// [ active:{"name":"active","default":0}], ...]
 var useone={}  					// [ img:{"entity":"img", "alias":"img"}, ...]
 var lists={} 						// [ likers:{"entity":"user", "alias":"likers", "reverse":"likedcomments"} ...]
 var usemany=entity_config.usemany  // [ "comments", ...]
 

/* plv8.elog(INFO, "entity_config.fields " + JSON.stringify(entity_config.fields)+"\n entity_config.status"+JSON.stringify(entity_config.status)
	+"\n entity_config.useone"+JSON.stringify(entity_config.useone)+"\n entity_config.lists" + JSON.stringify(entity_config.lists))
  */
 for(var i in entity_config.fields){
	 simplefields[entity_config.fields[i].name]=entity_config.fields[i]
	 if(!entity_config.fields[i].lazy){
		 notlazysimplefields[entity_config.fields[i].name]=entity_config.fields[i]
	 }
 }
  
 for(var i in entity_config.status){
	 statuses[entity_config.status[i].name]=entity_config.status[i]
 }
  for(var i in entity_config.useone){
	 useone[entity_config.useone[i].alias]=entity_config.useone[i]
 }
   for(var i in entity_config.lists){
	 lists[entity_config.lists[i].alias]=entity_config.lists[i]
 }
/*  plv8.elog(INFO, "simplefields " + JSON.stringify(simplefields)+"\n statuses"+JSON.stringify(statuses)+"\n useone"+
	 JSON.stringify(useone)+"\n lists" + JSON.stringify(lists)) 
  */
 var order=""
 if(m.order){
	
	 for(var  f in m.order){
		 if(!simplefields[f]&&!statuses[f]&&!useone[f]&&!lists[f]){continue}
		 if(m.order[f]=="desc" || m.order[f]=="asc"){
		 order += f+" "+m.order[f]+" , "
	 }}
	 order=order.slice(0, -2)
 }
 

 
  var where = ""
  if(m.id&&!Array.isArray(m.id)){where+=" id ="+m.id+" AND "}
  if(m.id&&Array.isArray(m.id)&&m.id.length==2){where+=" id >"+m.id[0]+" AND "+"id<"+m.id[1]+" AND "}
  if(m.id&&Array.isArray(m.id)&&m.id.length>2){where+=" id in ("+m.id+") AND "}
  if(urn[2]){where+=" id ="+urn[2]+" AND "}
  
 for(var f in simplefields){
	 
	 if(typeof m[f] != "undefined"){
		 
		 var field=simplefields[f]
		 var val =  m[f]
		
		if(Array.isArray(val)){
			if(val.length==2){
				where += f+">"+typeConvertor(field.type, val[0])+" AND "+f+"<"+typeConvertor(field.type, val[1])
			}
			if(val.length>2){
				where += f+" IN ("+val.join(", ")+") "
				
			}
		}else{
		if(typeof val == "number" || typeof val == "string"|| val === null ){
			where += f+"="+typeConvertor(field.type, val)
		
		}
		
		if(typeof val == "object" && val !=null){
			if(val == null){where+=field.name+" is null"
			}else{
			for(var i in val){//"id":{"not":[43]} Цыкл используется чисто чтобы не доставать ключи
				var znak="="
				switch (i) {
					case "not":znak="!=";break;
					case "gt":znak=">";break;
					case "lt":znak="<";break;
				}
				if(val[i].length=1){					
				where += f+znak+typeConvertor(field.type, val[i][0])
				continue
				}
				if(val[i].length>1&&znak=="!="){
				where += f +" NOT IN ("+val[i].join(",")+") "
				}
			
			}				
			}
			
		}
		}
		where += " AND "
	 }
 }
  
 for(var f in statuses){
	 
	 if(typeof m[f] != "undefined"){
		 
		 var field=statuses[f]
		 var val =  m[f]
		
		if(Array.isArray(val)){
			if(val.length==2){
				where += f+">"+typeConvertor("INTEGER", val[0])+" AND "+f+"<"+typeConvertor("INTEGER", val[1])
			}
			if(val.length>2){
				where += f+" IN ("+val.join(", ")+") "
				
			}
		}else{
		if(typeof val == "number" || typeof val == "string"|| val === null ){
			where += f+"="+typeConvertor("INTEGER", val)
		
		}
		
		if(typeof val == "object" && val !=null){
			if(val == null){where+=field.name+" is null"
			}else{
			for(var i in val){//"id":{"not":[43]} Цыкл используется чисто чтобы не доставать ключи
				var znak="="
				switch (i) {
					case "not":znak="!=";break;
					case "gt":znak=">";break;
					case "lt":znak="<";break;
				}
				if(val[i].length=1){					
				where += f+znak+typeConvertor("INTEGER", val[i][0])
				continue
				}
				if(val[i].length>1&&znak=="!="){
				where += f +" NOT IN ("+val[i].join(",")+") "
				}
			
			}				
			}
			
		}
		}
		where += " AND "
	 }
 }
 
 
   
 for(var f in useone){
	 
	 if(typeof m[f] != "undefined"){
		 
		 var field=useone[f]
		 var val =  m[f]
		
		if(Array.isArray(val)){
			if(val.length==2){
				where += f+">"+typeConvertor("INTEGER", val[0])+" AND "+f+"<"+typeConvertor("INTEGER", val[1])
			}
			if(val.length>2){
				where += f+" IN ("+val.join(", ")+") "
				
			}
		}else{
		if(typeof val == "number" || typeof val == "string"|| val === null ){
			where += f+"="+typeConvertor("INTEGER", val)
		
		}
		
		if(typeof val == "object" && val !=null){
			if(val == null){where+=f+" is null"
			}else{
			for(var i in val){//"id":{"not":[43]} Цыкл используется чисто чтобы не доставать ключи
				var znak="="
				switch (i) {
					case "not":znak="!=";break;
					case "gt":znak=">";break;
					case "lt":znak="<";break;
				}
				if(val[i].length=1){					
				where += f+znak+typeConvertor("INTEGER", val[i][0])
				continue
				}
				if(val[i].length>1&&znak=="!="){
				where += f +" NOT IN ("+val[i].join(",")+") "
				}
			
			}				
			}
			
		}
		}
		where += " AND "
	 }
 }
 
 
 
 
 where=where.slice(0, -5)
 
     var limit=""
	 if(m.limit){limit=" LIMIT "+m.limit}
	 if(where!=""){where=" WHERE "+where}
	 if(order!=""){ order = " ORDER BY "+order}

 var simplefieldsforload=[]
 var additionentityforload={}//{category:[title, image.uri]}
 var ownfields=[]
 var enemyuseone={}
 var enemyusemany={}
 var listfields={}//{likers:[title]}
 
 
 // if  example is define
 if(Array.isArray(m.example)){
	
  for(var p in m.example){
	 var t = m.example[p].split(".")
	 if(t.length==1&&(simplefields[t[0]]||statuses[t[0]]||useone[t[0]]||lists[t[0]])){
	 	ownfields.push(t[0])
     continue;
	 }
		
	if(t.length>1&&(simplefields[t[0]]||statuses[t[0]]||useone[t[0]])){
  
		var en=t.shift()
		if(!enemyuseone[en]){enemyuseone[en]=[]}
		enemyuseone[en].push(t.join("."))
	    continue;
	}
	if(t.length>1&&lists[t[0]]){
		var en=t.shift()
		if(!listfields[en]){listfields[en]=[]}
	 listfields[en].push(t.join("."))
	    continue;
	}
	
	if(t.length>1&&usemany.indexOf(t[0])!=-1){
		var en=t.shift()	
		if(!enemyusemany[en]){enemyusemany[en]=[]}
	enemyusemany[en].push(t.join("."))
	}
	
    }

	var tf = ownfields.concat(Object.keys(enemyuseone)).concat(Object.keys(listfields))
	var exe = "SELECT "+tf.join(",")+' FROM "'+entity+'"'+where+order+limit
	plv8.elog(INFO, "SQL command"+exe)
	var first_objects =  plv8.execute(exe)
	var hso ={}
		for(var i in first_objects){
		/* plv8.elog(INFO, " POINT 9 "+JSON.stringify(enemyuseone))  */
				/* //обработка has one */
		for(var q in  enemyuseone){
		// plv8.elog(INFO, " POINT 10 "+JSON.stringify(first_objects[i])) 
			if(!hso[q]){hso[q]={}}
		var cid=first_objects[i][q]
		if(hso[q][cid]){ first_objects[i][q]=hso[q][cid];continue}
	    var vl = func1(q, cid, enemyuseone[q])/*     (entity, id, fileds) (category, 112, [title, img.uri])->{"sport", img:{uri:"./sport/12454.png"}} */
		first_objects[i][q]=vl
		hso[q][cid]=vl
	    }
	    /* // обработка has Many */
		// plv8.elog(INFO, " POINT 11 ") 
		for(var m in  enemyusemany){
		var va = func2(m, entity, [first_objects[i]["id"]], enemyusemany[m])/* //(entity, hasoneField, hasoneId, fields) (comments, news, 7, [autor])->[{autor:петя}, {author:вася}] */
		first_objects[i][m]=va
		}
		/* обработка листов TODO */
		// plv8.elog(INFO, " POINT 12 ") 
		for(var l in listfields){
			
			if(first_objects[i][l]&&first_objects[i][l].lengh!=0){
		var va = func2(lists[l].entity, "id", first_objects[i][l], listfields[l])/* //(entity, hasoneField, hasoneId, fields) (comments, news, 7, [autor])->[{autor:петя}, {author:вася}] */
		first_objects[i][l]=va
			}
		}		
	}
	return JSON.stringify(first_objects);
	
 }
 
 
	/* 
 var ownfields=[]
 var enemyuseone={}
 var enemyusemany={}
 var listfields={}//{likers:[title]} */
	m.additionalload    //Дополнительные поля
	notlazysimplefields //Поля который в конфиге указаны как не лейзи
	
	for(var p in m.additionalload){
	 var t = m.additionalload[p].split(".")
	 if(t.length==1&&(simplefields[t[0]]||statuses[t[0]]||useone[t[0]]||lists[t[0]])){
	 	ownfields.push(t[0])
     continue;
	 }
		
	if(t.length>1&&useone[t[0]]){

		var en=t.shift()
		if(!enemyuseone[en]){enemyuseone[en]=[]}
		enemyuseone[en].push(t.join("."))
	    continue;
	}
	if(t.length>1&&lists[t[0]]){
		var en=t.shift()
		if(!listfields[en]){listfields[en]=[]}
	 listfields[en].push(t.join("."))
	    continue;
	}
	
	if(t.length>1&&usemany.indexOf(t[0])!=-1){
		var en=t.shift()	
		if(!enemyusemany[en]){enemyusemany[en]=[]}
	enemyusemany[en].push(t.join("."))
	}
	
    }
	
	for(var i in notlazysimplefields){
		if(ownfields.indexOf(notlazysimplefields[i])==-1){
			ownfields.push(notlazysimplefields[i].name)
		}
	}
	
	var tf = ownfields.concat(Object.keys(enemyuseone)).concat(Object.keys(listfields))
		//plv8.elog(INFO, "TF "+JSON.stringify(listfields))
	var exe = "SELECT "+tf.join(",")+' FROM "'+entity+'"'+where+order+limit
		plv8.elog(INFO, "SQL command "+exe)
	var first_objects =  plv8.execute(exe)
	var hso ={}
		for(var i in first_objects){
				/* //обработка has one */
		for(var q in  enemyuseone){
			if(!hso[q]){hso[q]={}}
		var cid=first_objects[i][q]
		if(hso[q][cid]){ first_objects[i][q]=hso[q][cid];continue}
	    var vl = func1(q, cid, enemyuseone[q])/* // (entity, id, fileds) (category, 112, [title, img.uri])->{"sport", img:{uri:"./sport/12454.png"}} */
		first_objects[i][q]=vl
		hso[q][cid]=vl
	    }
	    /* // обработка has Many */
		for(var m in  enemyusemany){
		var va = func2(m, entity, [first_objects[i]["id"]], enemyusemany[m])/* //(entity, hasoneField, hasoneId, fields) (comments, news, 7, [autor])->[{autor:петя}, {author:вася}] */
		first_objects[i][m]=va
		}
		/* обработка листов TODO */
		for(var l in listfields){
		if(first_objects[i][l]&&first_objects[i][l].lengh!=0){
		var va = func2(lists[l].entity, "id", first_objects[i][l], listfields[l])/* //(entity, hasoneField, hasoneId, fields) (comments, news, 7, [autor])->[{autor:петя}, {author:вася}] */
		first_objects[i][l]=va
			}
		}
	}
	return JSON.stringify(first_objects);
	 
	
 
 
 $$;


ALTER FUNCTION public.action_loadbyexample(m json) OWNER TO bc;

--
-- Name: action_loadjson(json); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION action_loadjson(m json) RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$
 var urn=m.urn.split("-")
 var entity = urn[1]
 var entity_config = plv8.find_function("entity_config_"+entity)();
 //var func_useone = plv8.find_function("load_useone");
 if(entity_config == null){plv8.elog(ERROR, "urn '"+m.urn+"' does  not exist");}
 var typeConvertor = plv8.find_function("typeConvertor");
 
 var simplefields={} 				// [ title:{"name":"title", "type":"text",     "default":""}, ...]
 var notlazysimplefields={} 		// [ title:{"name":"title", "type":"text",     "default":""}, ...]
 var statuses={} 					// [ active:{"name":"active","default":0}], ...]
 var useone={}  					// [ img:{"entity":"img", "alias":"img"}, ...]
 var lists={} 						// [ likers:{"entity":"user", "alias":"likers", "reverse":"likedcomments"} ...]
 var usemany=entity_config.usemany  // [ "comments", ...]
 var id = m.id || urn[2] || null

 
 for(var i in entity_config.fields){
	 simplefields[entity_config.fields[i].name]=entity_config.fields[i]
	 if(!entity_config.fields[i].lazy){
		 notlazysimplefields[entity_config.fields[i].name]=entity_config.fields[i]
	 }
 }
  
 for(var i in entity_config.status){
	 statuses[entity_config.status[i].name]=entity_config.status[i]
 }
  for(var i in entity_config.useone){
	 statuses[entity_config.useone[i].alias]=entity_config.useone[i]
 }
   for(var i in entity_config.lists){
	 statuses[entity_config.lists[i].alias]=entity_config.lists[i]
 }
 

 var order=""
 if(m.order){
	
	 for(var  f in m.order){
		 if(!simplefields[f]&&!statuses[f]&&!useone[f]&&!lists[f]){continue}
		 if(m.order[f]=="desc" || m.order[f]=="asc"){
		 order += f+" "+m.order[f]+" , "
	 }}
	 order=order.slice(0, -2)
 }
 

 
  var where = ""
  if(id&&!Array.isArray(id)){where+=" id ="+id+" AND "}
  if(id&&Array.isArray(id)&&id.length==2){where+=" id >"+id[0]+" AND "+"id<"+id[1]+" AND "}
  if(id&&Array.isArray(id)&&id.length>2){where+=" id in ("+id+") AND "}
 
 for(var f in simplefields){
	 
	 if(typeof m[f] != "undefined"){
		 
		 var field=simplefields[f]
		 var val =  m[f]
		
		if(Array.isArray(val)){
			if(val.length==2){
				where += f+">"+typeConvertor(field.type, val[0])+" AND "+f+"<"+typeConvertor(field.type, val[1])
			}
			if(val.length>2){
				where += f+" IN ("+val.join(", ")+") "
				
			}
		}else{
		if(typeof val == "number" || typeof val == "string"|| val === null ){
			where += f+"="+typeConvertor(field.type, val)
			
		}
		
		if(typeof val == "object" && val !=null){
			if(val == null){where+=field.name+" is null"
			}else{
			for(var i in val){//"id":{"not":[43]} Цыкл используется чисто чтобы не доставать ключи
				var znak="="
				switch (i) {
					case "not":znak="!=";break;
					case "gt":znak=">";break;
					case "lt":znak="<";break;
				}
				if(val[i].length=1){					
				where += f+znak+typeConvertor(field.type, val[i][0])
				continue
				}
				if(val[i].length>1&&znak=="!="){
				where += f +" NOT IN ("+val[i].join(",")+") "
				}
			
			}				
			}	
		}
		}
		where += " AND "
	 }
 }
  
 for(var f in statuses){
	 
	 if(typeof m[f] != "undefined"){
		 
		 var field=statuses[f]
		 var val =  m[f]
		
		if(Array.isArray(val)){
			if(val.length==2){
				where += f+">"+typeConvertor("INTEGER", val[0])+" AND "+f+"<"+typeConvertor("INTEGER", val[1])
			}
			if(val.length>2){
				where += f+" IN ("+val.join(", ")+") "
				
			}
		}else{
		if(typeof val == "number" || typeof val == "string"|| val === null ){
			where += f+"="+typeConvertor("INTEGER", val)
			
		}
		
		if(typeof val == "object" && val !=null){
			if(val == null){where+=field.name+" is null"
			}else{
			for(var i in val){//"id":{"not":[43]} Цыкл используется чисто чтобы не доставать ключи
				var znak="="
				switch (i) {
					case "not":znak="!=";break;
					case "gt":znak=">";break;
					case "lt":znak="<";break;
				}
				if(val[i].length=1){					
				where += f+znak+typeConvertor("INTEGER", val[i][0])
				continue
				}
				if(val[i].length>1&&znak=="!="){
				where += f +" NOT IN ("+val[i].join(",")+") "
				}
			
			}				
			}
			
		}
		}
		where += " AND "
	 }
 }
 
 
   
 for(var f in useone){
	 
	 if(typeof m[f] != "undefined"){
		 
		 var field=useone[f]
		 var val =  m[f]
		
		if(Array.isArray(val)){
			if(val.length==2){
				where += f+"_id >"+typeConvertor("INTEGER", val[0])+" AND "+f+"<"+typeConvertor("INTEGER", val[1])
			}
			if(val.length>2){
				where += f+"_id IN ("+val.join(", ")+") "
				
			}
		}else{
		if(typeof val == "number" || typeof val == "string"|| val === null ){
			where += f+"_id ="+typeConvertor("INTEGER", val)
			
		}
		
		if(typeof val == "object" && val !=null){
			if(val == null){where+=f+" is null"
			}else{
			for(var i in val){//"id":{"not":[43]} Цыкл используется чисто чтобы не доставать ключи
				var znak="="
				switch (i) {
					case "not":znak="!=";break;
					case "gt":znak=">";break;
					case "lt":znak="<";break;
				}
				if(val[i].length=1){					
				where += f+'_id '+znak+typeConvertor("INTEGER", val[i][0])
				continue
				}
				if(val[i].length>1&&znak=="!="){
				where += f +"_id NOT IN ("+val[i].join(",")+") "
				}
			
			}				
			}
			
		}
		}
		where += " AND "
	 }
 }
 
 
 
 
 
 where=where.slice(0, -5)
 
 
	var useoneFields=[]
	var selectFields = "id, "
	
 
     var limit=""
	 if(m.limit){limit=" LIMIT "+m.limit}
	 if(where!=""){where=" WHERE "+where}
	 if(order!=""){ order = " ORDER BY "+order}
// if  selectonly is define
 var simplefieldsforload=[]
 simplefieldsforload.push("id")
 var additionentityforload={}//{category:[title, image.uri]}
 
 if(Array.isArray(m.selectonly)){
 

	 for(var f  in m.selectonly){
		 var ar = m.selectonly[f].split(".")
		 if(ar.length==1&&(simplefields[ar[0]] || statuses[ar[0]] || useone[ar[0]] || lists[ar[0]])){
			simplefieldsforload.push(ar[0]);	  
			continue;
			}
		 
		 if(ar.length>1&&(simplefields[ar[0]] || statuses[ar[0]] || useone[ar[0]] || lists[ar[0]]) ){
			 for(var i in useone){
				 var field = useone[i]
				 if(field.name == ar[0]){
					  var n =  ar.shift()
					  simplefieldsforload.push(n);
					  if(!Array.isArray(additionentityforload[n])){additionentityforload[n]=[]}
					  additionentityforload[n].push(ar.join("."))
				 }
			 }
		 }
	 } 
	var exe = "SELECT "+simplefieldsforload.join(",")+' FROM "'+entity+'"'+where+order+limit
	plv8.elog(INFO, "SQL COMAND "+exe)
	var d =  plv8.execute(exe)

	var entity_collection={}
	
	for(var f in additionentityforload){
		for(e in d){
			var eid = e[f]
			if(entity_collection[f]&&entity_collection[f][eid]){
				e[f]=entity_collection[f][eid]
				continue
			}
			var temp = func_useone(f, eid, additionentityforload[f])//entity text, id integer, fields text[]
			entity_collection[f][eid]=temp
			e[f]=temp
		}
	}
	
	return d
	 
 }else{ 
	 plv8.elog(ERROR, "m.selectonly does not exist")
 }

 
 $$;


ALTER FUNCTION public.action_loadjson(m json) OWNER TO bc;

--
-- Name: action_members(json); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION action_members(m json) RETURNS text
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$
  
  var urn=m.urn.split("-")
  var entity = urn[1]
  var entity_config = plv8.find_function("entity_config_"+entity)();
  var id = urn[2] || m.id || null
  if(!id){plv8.elog(ERROR, "cant find id")}
  
  var field = urn[3]
  if(entity_config.lists[field]){
  var r = plv8.execute("select "+field+" from"+ entity+" where id="+id);
  return JSON.stringify(r)
  }
  $$;


ALTER FUNCTION public.action_members(m json) OWNER TO bc;

--
-- Name: action_remove(json); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION action_remove(m json) RETURNS text
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$
  
  var urn=m.from.split("-")
  var entity = urn[1]
  var entity_config = plv8.find_function("entity_config_"+entity)();
  var id = urn[2] || m.id || null
  if(!id){plv8.elog(ERROR, "cant find id")}
  
  var field = urn[3]
  if(entity_config.lists[field]){
  var urn2 = m.urn.split("-")
  var r = plv8.execute("UPDATE"+entity+" SET "+ field+" = array_remove("+field+", "+urn2[2]+") where id ="+id);
  if(entity_config.lists[field].reverse){
	  var revf=entity_config.lists[field].reverse
	   var r = plv8.execute("UPDATE"+urn2[1]+" SET "+ revf+" = array_remove("+revf+", "+id+") where id ="+urn2[2]);
  }
  }
  
  
  $$;


ALTER FUNCTION public.action_remove(m json) OWNER TO bc;

--
-- Name: action_update(json); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION action_update(m json) RETURNS text
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$
  var urn=m.urn.split("-")
  var entity = urn[1]
 var entity_config = plv8.find_function("entity_config_"+entity)();
 if(entity_config == null){plv8.elog(ERROR, "urn '"+m.urn+"' does  not exist");}
  var typeConvertor = plv8.find_function("typeConvertor");
  
 var simplefields=entity_config.fields
 var statuses=entity_config.status
 var useone=entity_config.useone
 
 //TODO -------------
 var keysVals=""
 var id = m.id || urn[2] || null
 
 if(id==null){plv8.elog(ERROR, '{"error":"msg doesn\'t contain id"}')}

for(var f in simplefields){
  var field = simplefields[f]
  if(m[field.name] != null){
      var newValue= m[field.name]

    if(m[field.name].append){
              keysVals+=field.name +"="+field.name+' || '+typeConvertor(field.type, m[field.name].append)+", "
              continue;
    }

    if(m[field.name].prepend){
              keysVals+=field.name +"="+typeConvertor(field.type, m[field.name].prepend)+' || '+field.name+", "
              continue;
    }



     if(m[field.name].set != null||m[field.name].push != null||m[field.name].changeposition != null){
   var originJsonField =     plv8.execute("SELECT "+field.name+" from "+entity+" WHERE id="+id)[0][field.name];
    for (var keyPath in m[field.name].set){
        var pathOrgin = keyPath.split(".")
        var innerKey = pathOrgin.pop()
        if(!isNaN(innerKey)){
            innerKey = innerKey-1;
        }
        var insideField = originJsonField;
      for (var i in pathOrgin){
          var key = pathOrgin[i];
          if(isNaN(key)){
              insideField = insideField[key];
          }else{
              insideField = insideField[key-1];
          }
      }
      insideField[innerKey] = m[field.name].set[keyPath]
  }
      for (var keyPath in m[field.name].push){
          var pathOrgin = keyPath.split(".")
          var innerKey = pathOrgin.pop()
          var insideField = originJsonField;

          for (var i in pathOrgin){
              var key = pathOrgin[i];
              if(isNaN(key)){
                  insideField = insideField[key];
              }else{
                  insideField = insideField[key-1];
              }
          }
        if( !Array.isArray(insideField[innerKey])){plv8.elog(ERROR, innerKey+" is not Array")}
          insideField[innerKey].push(m[field.name].push[keyPath])
      }

for (var keyPath in m[field.name].changeposition){
          var pathOrgin = keyPath.split(".")
          var innerKey = pathOrgin.pop()
          var insideField = originJsonField;
          for (var i in pathOrgin){
              var key = pathOrgin[i];
              if(isNaN(key)){
                  insideField = insideField[key];
              }else{
                  insideField = insideField[key-1];
              }
          }
        if( !Array.isArray(insideField[innerKey])){plv8.elog(ERROR, innerKey+" is not Array")}
		var oldIndex=m[field.name].changeposition[keyPath][0]-1
		var newIndex = m[field.name].changeposition[keyPath][1]-1
        var insertVal = insideField[innerKey].splice(oldIndex, 1)[0]
    insideField[innerKey].splice(newIndex, 0, insertVal)
      }
         newValue=originJsonField

  }

      keysVals+=field.name +"="+typeConvertor(field.type, newValue)+", "

  }
  }

    for(var f in statuses){
			var field = statuses[f]
			if(m[field.name] == null||m[field.name]===""){continue}
			keysVals += field.name+"='"+m[field.name]+"'::INTEGER , "
    }
  
    for(var f in useone){
			var field = useone[f]
			if(m[field.alias] == null || m[field.alias]==""){continue}
			var temp = m[field.alias].split("-")[2]
			if(temp==null){continue}
			keysVals += field.alias+"_id='"+temp+"'::INTEGER , "
    }

 //plv8.elog(INFO, "POINT 4 "+keysVals)
 keysVals = keysVals.slice(0,-2)

 var exe='UPDATE '+entity+' SET '+keysVals+' WHERE id = '+id
 plv8.elog(NOTICE, "SQL comand = ", exe)
 var d =  plv8.execute(exe)
 return JSON.stringify(d)

$$;


ALTER FUNCTION public.action_update(m json) OWNER TO bc;

--
-- Name: entity_config_accreditation(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_accreditation() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"iscurrent","default":0},{"name":"isadmission","default":0}],"fields":[{"name":"expirationdate","type":"INT","default":"integer","lazy":false},{"name":"enddate","type":"INT","default":"integer","lazy":false},{"name":"startdate","type":"INT","default":"integer","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"educationalprogram","alias":"educationalprogram"}],"usemany":[{"entity":"attestation"}],"lists":[{"entity":"user","alias":"users","reverse":false}]} $$;


ALTER FUNCTION public.entity_config_accreditation() OWNER TO bc;

--
-- Name: entity_config_acdepartamentdocument(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_acdepartamentdocument() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"doctype","type":"SMALLINT","default":null,"lazy":false},{"name":"permissioncreate","type":"INT","default":null,"lazy":false},{"name":"permissionread","type":"INT","default":null,"lazy":false},{"name":"sendforarchive","type":"INT","default":null,"lazy":false},{"name":"createchilddoc","type":"INT","default":null,"lazy":false},{"name":"addrelativedoc","type":"INT","default":null,"lazy":false},{"name":"createrealcopy","type":"INT","default":null,"lazy":false},{"name":"print","type":"INT","default":null,"lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"department","alias":"department"}]} $$;


ALTER FUNCTION public.entity_config_acdepartamentdocument() OWNER TO bc;

--
-- Name: entity_config_acdocument(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_acdocument() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"doctype","type":"SMALLINT","default":null,"lazy":false},{"name":"permissioncreate","type":"INT","default":null,"lazy":false},{"name":"permissionread","type":"INT","default":null,"lazy":false},{"name":"permissionmodify","type":"INT","default":null,"lazy":false},{"name":"permissioncomment","type":"INT","default":null,"lazy":false}],"useone":[{"entity":"posttype","alias":"posttype"}]} $$;


ALTER FUNCTION public.entity_config_acdocument() OWNER TO bc;

--
-- Name: entity_config_acpostdocument(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_acpostdocument() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"doctype","type":"SMALLINT","default":null,"lazy":false},{"name":"permissioncreate","type":"INT","default":null,"lazy":false},{"name":"permissionread","type":"INT","default":null,"lazy":false},{"name":"sendforarchive","type":"INT","default":null,"lazy":false},{"name":"createchilddoc","type":"INT","default":null,"lazy":false},{"name":"addrelativedoc","type":"INT","default":null,"lazy":false},{"name":"createrealcopy","type":"INT","default":null,"lazy":false},{"name":"print","type":"INT","default":null,"lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"post","alias":"post"}]} $$;


ALTER FUNCTION public.entity_config_acpostdocument() OWNER TO bc;

--
-- Name: entity_config_acposttypedocument(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_acposttypedocument() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"doctype","type":"SMALLINT","default":null,"lazy":false},{"name":"permissioncreate","type":"INT","default":null,"lazy":false},{"name":"permissionread","type":"INT","default":null,"lazy":false},{"name":"sendforarchive","type":"INT","default":null,"lazy":false},{"name":"createchilddoc","type":"INT","default":null,"lazy":false},{"name":"addrelativedoc","type":"INT","default":null,"lazy":false},{"name":"createrealcopy","type":"INT","default":null,"lazy":false},{"name":"print","type":"INT","default":null,"lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"posttype","alias":"posttype"}]} $$;


ALTER FUNCTION public.entity_config_acposttypedocument() OWNER TO bc;

--
-- Name: entity_config_actionrecord(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_actionrecord() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"urnlink","type":"VARCHAR(255)","default":"string","lazy":false}],"useone":[{"entity":"actiontype","alias":"actiontype"},{"entity":"user","alias":"user"}]} $$;


ALTER FUNCTION public.entity_config_actionrecord() OWNER TO bc;

--
-- Name: entity_config_actiontype(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_actiontype() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"uri","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"title","type":"VARCHAR(255)","default":"string","lazy":false}]} $$;


ALTER FUNCTION public.entity_config_actiontype() OWNER TO bc;

--
-- Name: entity_config_acuserdocument(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_acuserdocument() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"doctype","type":"SMALLINT","default":null,"lazy":false},{"name":"permissioncreate","type":"INT","default":null,"lazy":false},{"name":"permissionread","type":"INT","default":null,"lazy":false},{"name":"sendforarchive","type":"INT","default":null,"lazy":false},{"name":"createchilddoc","type":"INT","default":null,"lazy":false},{"name":"addrelativedoc","type":"INT","default":null,"lazy":false},{"name":"createrealcopy","type":"INT","default":null,"lazy":false},{"name":"createnoncontrollcopy","type":"INT","default":null,"lazy":false},{"name":"createvirtualcopy","type":"INT","default":null,"lazy":false},{"name":"print","type":"INT","default":null,"lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"user","alias":"user"}]} $$;


ALTER FUNCTION public.entity_config_acuserdocument() OWNER TO bc;

--
-- Name: entity_config_answer(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_answer() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"iscorrect","default":0}],"fields":[{"name":"orderindex","type":"INT","default":"integer","lazy":false},{"name":"content","type":"TEXT","default":"text","lazy":true},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"test","alias":"test"}]} $$;


ALTER FUNCTION public.entity_config_answer() OWNER TO bc;

--
-- Name: entity_config_attestation(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_attestation() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"iscomplete","default":0}],"fields":[{"name":"users","type":"JSON","default":"json","lazy":false},{"name":"starttime","type":"INT","default":"integer","lazy":false},{"name":"endtime","type":"INT","default":"integer","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"accreditation","alias":"accreditation"}],"usemany":[{"entity":"usertestresult"}]} $$;


ALTER FUNCTION public.entity_config_attestation() OWNER TO bc;

--
-- Name: entity_config_businessrole(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_businessrole() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"title","type":"TEXT","default":"text","lazy":true},{"name":"description","type":"TEXT","default":"text","lazy":true}]} $$;


ALTER FUNCTION public.entity_config_businessrole() OWNER TO bc;

--
-- Name: entity_config_calendar(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_calendar() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"name","type":"TEXT","default":"text","lazy":true},{"name":"startdate","type":"DATE","default":"date","lazy":false},{"name":"periodicity","type":"INT","default":"integer","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}]} $$;


ALTER FUNCTION public.entity_config_calendar() OWNER TO bc;

--
-- Name: entity_config_capa(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_capa() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"workflowcapa","type":"SMALLINT","default":null,"lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"user","alias":"author"},{"entity":"user","alias":"approver"}],"usemany":[{"entity":"capaproblem","alias":"capaproblem"}]} $$;


ALTER FUNCTION public.entity_config_capa() OWNER TO bc;

--
-- Name: entity_config_capaevent(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_capaevent() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"description","type":"TEXT","default":"text","lazy":true},{"name":"comment","type":"TEXT","default":"text","lazy":true},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"capaproblem","alias":"capaproblem"},{"entity":"department","alias":"department"},{"entity":"risktype","alias":"risktype"},{"entity":"user","alias":"performer"}],"usemany":[{"entity":"capaeventvariant","alias":"capaeventvariant"}]} $$;


ALTER FUNCTION public.entity_config_capaevent() OWNER TO bc;

--
-- Name: entity_config_capaeventvariant(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_capaeventvariant() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"realization","type":"SMALLINT","default":null,"lazy":false},{"name":"realizationdate","type":"DATE","default":"date","lazy":false},{"name":"cost","type":"NUMERIC(14,2)","default":"money","lazy":false},{"name":"description","type":"TEXT","default":"text","lazy":true},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"capaevent","alias":"capaevent"}]} $$;


ALTER FUNCTION public.entity_config_capaeventvariant() OWNER TO bc;

--
-- Name: entity_config_capaproblem(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_capaproblem() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"description","type":"TEXT","default":"text","lazy":true},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"capa","alias":"capa"},{"entity":"problemscope","alias":"problemscope"}],"usemany":[{"entity":"capaevent","alias":"capaevent"}]} $$;


ALTER FUNCTION public.entity_config_capaproblem() OWNER TO bc;

--
-- Name: entity_config_capatask(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_capatask() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"confirmed","default":0},{"name":"done","default":0}],"fields":[{"name":"description","type":"TEXT","default":"text","lazy":true},{"name":"comment","type":"TEXT","default":"text","lazy":true},{"name":"enddata","type":"INT","default":"timestamp","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"capaevent","alias":"capaevent"},{"entity":"capaeventvariant","alias":"capaeventvariant"},{"entity":"user","alias":"performer"}]} $$;


ALTER FUNCTION public.entity_config_capatask() OWNER TO bc;

--
-- Name: entity_config_comment(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_comment() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"isreply","default":0},{"name":"iseditingsuggestion","default":0}],"fields":[{"name":"content","type":"TEXT","default":"text","lazy":true},{"name":"appliedtatus","type":"SMALLINT","default":null,"lazy":false},{"name":"approvedstatus","type":"SMALLINT","default":null,"lazy":false},{"name":"editingsuggestionstatus","type":"SMALLINT","default":null,"lazy":false},{"name":"docpath","type":"TEXT","default":"text","lazy":true},{"name":"documentversion","type":"INT","default":"integer","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"document","alias":"document"},{"entity":"comment","alias":"parent"},{"entity":"user","alias":"autor"},{"entity":"user","alias":"appliedautor"},{"entity":"user","alias":"approvedautor"},{"entity":"user","alias":"editingsuggestionautor"}],"usemany":[{"entity":"comment","alias":"comment"}]} $$;


ALTER FUNCTION public.entity_config_comment() OWNER TO bc;

--
-- Name: entity_config_company(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_company() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"title","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"ba","type":"INT","default":"integer","lazy":false},{"name":"mfo","type":"INT","default":"integer","lazy":false},{"name":"edrpou","type":"INT","default":"integer","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"user","alias":"boss"}],"usemany":[{"entity":"companycontact"}]} $$;


ALTER FUNCTION public.entity_config_company() OWNER TO bc;

--
-- Name: entity_config_companycontact(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_companycontact() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"type","type":"SMALLINT","default":null,"lazy":false},{"name":"value","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"company","alias":"company"}]} $$;


ALTER FUNCTION public.entity_config_companycontact() OWNER TO bc;

--
-- Name: entity_config_companyprocess(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_companyprocess() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"name","type":"TEXT","default":"text","lazy":true},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"lists":[{"entity":"document","alias":"parentdocs","reverse":false},{"entity":"document","alias":"relateddocs","reverse":false},{"entity":"object","alias":"objects","reverse":false}]} $$;


ALTER FUNCTION public.entity_config_companyprocess() OWNER TO bc;

--
-- Name: entity_config_complaint(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_complaint() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"isresolved","default":0}],"fields":[{"name":"complaintworkflow","type":"SMALLINT","default":null,"lazy":false},{"name":"clienttext","type":"TEXT","default":"text","lazy":true},{"name":"warehouse","type":"INT","default":"integer","lazy":false},{"name":"startdate","type":"INT","default":"integer","lazy":false},{"name":"enddate","type":"INT","default":"integer","lazy":false},{"name":"description","type":"TEXT","default":"text","lazy":true},{"name":"additionalfield","type":"TEXT","default":"text","lazy":true},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"user","alias":"autor"},{"entity":"user","alias":"client"},{"entity":"complainttype","alias":"complainttype"},{"entity":"document","alias":"mirror"}]} $$;


ALTER FUNCTION public.entity_config_complaint() OWNER TO bc;

--
-- Name: entity_config_complainttype(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_complainttype() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"title","type":"TEXT","default":"text","lazy":true},{"name":"titleforfield","type":"TEXT","default":"text","lazy":true},{"name":"group","type":"SMALLINT","default":null,"lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"post","alias":"responsible"}]} $$;


ALTER FUNCTION public.entity_config_complainttype() OWNER TO bc;

--
-- Name: entity_config_contract(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_contract() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"signed","default":0},{"name":"done","default":0}],"fields":[{"name":"urnlink","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"task","alias":"task"},{"entity":"user","alias":"user"}]} $$;


ALTER FUNCTION public.entity_config_contract() OWNER TO bc;

--
-- Name: entity_config_contractor(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_contractor() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"isactive","default":1},{"name":"isclient","default":0},{"name":"iscontractor","default":0}],"fields":[{"name":"name","type":"TEXT","default":"text","lazy":true},{"name":"other","type":"TEXT","default":"text","lazy":true},{"name":"letter","type":"TEXT","default":"text","lazy":true},{"name":"ba","type":"INT","default":"integer","lazy":false},{"name":"mfo","type":"INT","default":"integer","lazy":false},{"name":"edrpou","type":"INT","default":"integer","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"object","alias":"object"}],"usemany":[{"entity":"contractorcontact"}],"lists":[{"entity":"document","alias":"relativedoc","reverse":false}]} $$;


ALTER FUNCTION public.entity_config_contractor() OWNER TO bc;

--
-- Name: entity_config_contractorcontact(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_contractorcontact() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"type","type":"SMALLINT","default":null,"lazy":false},{"name":"value","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"contractor","alias":"contractor"}]} $$;


ALTER FUNCTION public.entity_config_contractorcontact() OWNER TO bc;

--
-- Name: entity_config_controlaction(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_controlaction() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"title","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"periodicity","type":"INT","default":"integer","lazy":false},{"name":"description","type":"TEXT","default":"text","lazy":true},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"risk","alias":"risk"},{"entity":"risk","alias":"risk"}]} $$;


ALTER FUNCTION public.entity_config_controlaction() OWNER TO bc;

--
-- Name: entity_config_currency(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_currency() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"title","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}]} $$;


ALTER FUNCTION public.entity_config_currency() OWNER TO bc;

--
-- Name: entity_config_delegation(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_delegation() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"startdate","type":"INT","default":"integer","lazy":false},{"name":"enddate","type":"INT","default":"integer","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"user","alias":"delegationfrom"},{"entity":"user","alias":"delegationto"}]} $$;


ALTER FUNCTION public.entity_config_delegation() OWNER TO bc;

--
-- Name: entity_config_department(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_department() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"title","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"_parent","type":"INT","default":"integer","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}]} $$;


ALTER FUNCTION public.entity_config_department() OWNER TO bc;

--
-- Name: entity_config_docclass(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_docclass() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"title","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"uri","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"timlimitforvissing","type":"INT","default":"integer","lazy":false},{"name":"timlimitforapproving","type":"INT","default":"integer","lazy":false}],"useone":[{"entity":"post","alias":"approver"}],"usemany":[{"entity":"doctype"},{"entity":"doctypegroup"}],"lists":[{"entity":"post","alias":"visaposts","reverse":false}]} $$;


ALTER FUNCTION public.entity_config_docclass() OWNER TO bc;

--
-- Name: entity_config_doctype(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_doctype() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"title","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"uri","type":"VARCHAR(255)","default":"string","lazy":false}],"useone":[{"entity":"docclass","alias":"docclass"},{"entity":"doctypegroup","alias":"doctypegroup"}],"usemany":[{"entity":"document"}]} $$;


ALTER FUNCTION public.entity_config_doctype() OWNER TO bc;

--
-- Name: entity_config_doctypegroup(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_doctypegroup() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"title","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"uri","type":"VARCHAR(255)","default":"string","lazy":false}],"useone":[{"entity":"docclass","alias":"docclass"}],"usemany":[{"entity":"doctype"}]} $$;


ALTER FUNCTION public.entity_config_doctypegroup() OWNER TO bc;

--
-- Name: entity_config_document(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_document() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"draft","default":0}],"fields":[{"name":"title","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"metadata","type":"JSON","default":"json","lazy":false},{"name":"data","type":"JSON","default":"json","lazy":false},{"name":"history","type":"text[]","default":"tarray","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"user","alias":"user"}]} $$;


ALTER FUNCTION public.entity_config_document() OWNER TO bc;

--
-- Name: entity_config_documentfieldsnapshot(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_documentfieldsnapshot() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"data","type":"JSON","default":"json","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"document","alias":"document"},{"entity":"user","alias":"user"}]} $$;


ALTER FUNCTION public.entity_config_documentfieldsnapshot() OWNER TO bc;

--
-- Name: entity_config_documenthistoryaction(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_documenthistoryaction() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"ispositive","default":0}],"fields":[{"name":"actiontype","type":"SMALLINT","default":null,"lazy":false},{"name":"content","type":"TEXT","default":"text","lazy":true},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"document","alias":"document"},{"entity":"user","alias":"user"},{"entity":"documenthistoryworkflow","alias":"documenthistoryworkflow"}]} $$;


ALTER FUNCTION public.entity_config_documenthistoryaction() OWNER TO bc;

--
-- Name: entity_config_documenthistoryworkflow(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_documenthistoryworkflow() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"actiontype","type":"SMALLINT","default":null,"lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"document","alias":"document"},{"entity":"user","alias":"user"}]} $$;


ALTER FUNCTION public.entity_config_documenthistoryworkflow() OWNER TO bc;

--
-- Name: entity_config_educationalprogram(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_educationalprogram() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"iscriticality","default":0},{"name":"isarchive","default":0}],"fields":[{"name":"correctanswerspercent","type":"INT","default":"integer","lazy":false},{"name":"content","type":"INT","default":"integer","lazy":false},{"name":"timelimitexam","type":"INT","default":"integer","lazy":false},{"name":"timelimitattestation","type":"INT","default":"integer","lazy":false},{"name":"timelimitonetest","type":"INT","default":"integer","lazy":false},{"name":"periodicity","type":"INT","default":"integer","lazy":false},{"name":"workflow","type":"SMALLINT","default":null,"lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"document","alias":"document"},{"entity":"document","alias":"mirror"},{"entity":"user","alias":"author"},{"entity":"user","alias":"teacher"},{"entity":"humantask","alias":"humantask"}],"usemany":[{"entity":"examquestion"}],"lists":[{"entity":"posttype","alias":"posts","reverse":false}]} $$;


ALTER FUNCTION public.entity_config_educationalprogram() OWNER TO bc;

--
-- Name: entity_config_entity(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_entity() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"title","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"uri","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"price","type":"NUMERIC(14,2)","default":"money","lazy":false}]} $$;


ALTER FUNCTION public.entity_config_entity() OWNER TO bc;

--
-- Name: entity_config_examquestion(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_examquestion() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"orderindex","type":"INT","default":"integer","lazy":false},{"name":"content","type":"TEXT","default":"text","lazy":true},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"educationalprogram","alias":"educationalprogram"}],"usemany":[{"entity":"test"}]} $$;


ALTER FUNCTION public.entity_config_examquestion() OWNER TO bc;

--
-- Name: entity_config_file(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_file() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"uri","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"mediatype","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"filesize","type":"INT","default":"integer","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false}]} $$;


ALTER FUNCTION public.entity_config_file() OWNER TO bc;

--
-- Name: entity_config_humantask(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_humantask() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"iscomplete","default":0},{"name":"isproceed","default":0},{"name":"isforuser","default":0}],"fields":[{"name":"fromdate","type":"INT","default":"integer","lazy":false},{"name":"todate","type":"INT","default":"integer","lazy":false},{"name":"enddate","type":"INT","default":"integer","lazy":false},{"name":"typeset","type":"SMALLINT","default":null,"lazy":false},{"name":"entityid","type":"INT","default":"integer","lazy":false},{"name":"textcontent","type":"TEXT","default":"text","lazy":true},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"post","alias":"post"},{"entity":"user","alias":"user"},{"entity":"document","alias":"document"}]} $$;


ALTER FUNCTION public.entity_config_humantask() OWNER TO bc;

--
-- Name: entity_config_invoice(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_invoice() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"payed","default":0},{"name":"closed","default":0}],"fields":[{"name":"amount","type":"REAL","default":"float","lazy":false},{"name":"total","type":"NUMERIC(14,2)","default":"money","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"payedat","type":"INT","default":"timestamp","lazy":false},{"name":"mqname","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"urnlink","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"targetuser","type":"INT","default":"integer","lazy":false},{"name":"units","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"subject","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"uri","type":"VARCHAR(255)","default":"string","lazy":false}],"useone":[{"entity":"user","alias":"user"}]} $$;


ALTER FUNCTION public.entity_config_invoice() OWNER TO bc;

--
-- Name: entity_config_longtransaction(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_longtransaction() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"autocloseinvoice","default":0},{"name":"payed","default":0}],"fields":[{"name":"paymentgateway","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"maxage","type":"INT","default":"integer","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"payedat","type":"INT","default":"timestamp","lazy":false},{"name":"openedamount","type":"INT","default":"integer","lazy":false},{"name":"openedcurr","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"closedamount","type":"INT","default":"integer","lazy":false},{"name":"closedcurr","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"remoteid","type":"INT","default":"integer","lazy":false},{"name":"ip","type":"inet","default":"ipv4","lazy":false},{"name":"phoneverified","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"phoneprovided","type":"VARCHAR(255)","default":"string","lazy":false}],"useone":[{"entity":"user","alias":"user"},{"entity":"invoice","alias":"invoice"}]} $$;


ALTER FUNCTION public.entity_config_longtransaction() OWNER TO bc;

--
-- Name: entity_config_mailplain(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_mailplain() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"title","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"uri","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"layout","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"fromname","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"fromemail","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"headerplain","type":"TEXT","default":"text","lazy":true},{"name":"contentplain","type":"TEXT","default":"text","lazy":true},{"name":"footerplain","type":"TEXT","default":"text","lazy":true},{"name":"specialplain","type":"TEXT","default":"text","lazy":true}]} $$;


ALTER FUNCTION public.entity_config_mailplain() OWNER TO bc;

--
-- Name: entity_config_mailtemplate(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_mailtemplate() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"title","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"uri","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"fromname","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"fromemail","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"headerhtml","type":"TEXT","default":"richtext","lazy":true},{"name":"contenthtml","type":"TEXT","default":"richtext","lazy":true},{"name":"footerhtml","type":"TEXT","default":"richtext","lazy":true},{"name":"specialhtml","type":"TEXT","default":"richtext","lazy":true}]} $$;


ALTER FUNCTION public.entity_config_mailtemplate() OWNER TO bc;

--
-- Name: entity_config_meeting(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_meeting() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"isuserlisteditable","default":0},{"name":"ispostsactive","default":0}],"fields":[{"name":"relatedocument","type":"INT","default":"integer","lazy":false},{"name":"title","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"month","type":"INT","default":"integer","lazy":false},{"name":"date","type":"INT","default":"integer","lazy":false},{"name":"workflow","type":"SMALLINT","default":null,"lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"meetingtype","alias":"meetingtype"},{"entity":"user","alias":"curator"},{"entity":"humantask","alias":"humantask"},{"entity":"document","alias":"mirror"}],"lists":[{"entity":"user","alias":"userlist","reverse":false},{"entity":"post","alias":"postlist","reverse":false},{"entity":"user","alias":"planned","reverse":false},{"entity":"user","alias":"visausers","reverse":false}]} $$;


ALTER FUNCTION public.entity_config_meeting() OWNER TO bc;

--
-- Name: entity_config_meetingtype(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_meetingtype() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"delegation","default":0}],"fields":[{"name":"title","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"usemany":[{"entity":"meeting"}]} $$;


ALTER FUNCTION public.entity_config_meetingtype() OWNER TO bc;

--
-- Name: entity_config_noncontrollcopy(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_noncontrollcopy() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"person","type":"TEXT","default":"text","lazy":true},{"name":"issuedate","type":"INT","default":"integer","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"document","alias":"document"},{"entity":"user","alias":"master"}]} $$;


ALTER FUNCTION public.entity_config_noncontrollcopy() OWNER TO bc;

--
-- Name: entity_config_notapprovedrisk(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_notapprovedrisk() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"isactive","default":1}],"fields":[{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"document","alias":"parentdocument"},{"entity":"object","alias":"object"},{"entity":"capa","alias":"capa"},{"entity":"risk","alias":"parentrisk"}]} $$;


ALTER FUNCTION public.entity_config_notapprovedrisk() OWNER TO bc;

--
-- Name: entity_config_notifymessage(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_notifymessage() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"isnew","default":0}],"fields":[{"name":"content","type":"TEXT","default":"text","lazy":true},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"user","alias":"user"}]} $$;


ALTER FUNCTION public.entity_config_notifymessage() OWNER TO bc;

--
-- Name: entity_config_oauth2link(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_oauth2link() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"userid64","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"oauth2service","type":"VARCHAR(255)","default":"string","lazy":false}],"useone":[{"entity":"user","alias":"user"}]} $$;


ALTER FUNCTION public.entity_config_oauth2link() OWNER TO bc;

--
-- Name: entity_config_oauth2session(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_oauth2session() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"oauth2service","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"oauthaccesstoken","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"oauthtokensecret","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"expire","type":"INT","default":"integer","lazy":false}],"useone":[{"entity":"user","alias":"user"}]} $$;


ALTER FUNCTION public.entity_config_oauth2session() OWNER TO bc;

--
-- Name: entity_config_object(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_object() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"name","type":"TEXT","default":"text","lazy":true},{"name":"_parent","type":"INT","default":"integer","lazy":false},{"name":"inventorynumber","type":"TEXT","default":"text","lazy":true},{"name":"description","type":"TEXT","default":"text","lazy":true},{"name":"currentproperties","type":"TEXT","default":"text","lazy":true},{"name":"value","type":"NUMERIC(14,2)","default":"money","lazy":false},{"name":"maker","type":"TEXT","default":"text","lazy":true},{"name":"location","type":"TEXT","default":"text","lazy":true},{"name":"expirationdate","type":"INT","default":"timestamp","lazy":false},{"name":"startupdate","type":"INT","default":"timestamp","lazy":false},{"name":"periodicity\u043caintenance","type":"INT","default":"integer","lazy":false},{"name":"periodicityvalidation","type":"INT","default":"integer","lazy":false},{"name":"periodicityverification","type":"INT","default":"integer","lazy":false},{"name":"periodicity","type":"INT","default":"integer","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"currency","alias":"currency"},{"entity":"user","alias":"charger"}],"lists":[{"entity":"document","alias":"parentdocs","reverse":false},{"entity":"document","alias":"relateddocs","reverse":false}]} $$;


ALTER FUNCTION public.entity_config_object() OWNER TO bc;

--
-- Name: entity_config_offer(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_offer() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"content","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"workflow","type":"SMALLINT","default":null,"lazy":false},{"name":"_parent","type":"INT","default":"integer","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"participation","alias":"participation"}],"usemany":[{"entity":"pricesuggestion"}]} $$;


ALTER FUNCTION public.entity_config_offer() OWNER TO bc;

--
-- Name: entity_config_online(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_online() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"securehash","type":"INT","default":"integer","lazy":false},{"name":"hash","type":"INT","default":"integer","lazy":false},{"name":"renewhash","type":"INT","default":"integer","lazy":false},{"name":"ip","type":"inet","default":"ipv4","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"user","alias":"user"}]} $$;


ALTER FUNCTION public.entity_config_online() OWNER TO bc;

--
-- Name: entity_config_participation(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_participation() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"contractor","alias":"contractor"},{"entity":"tender","alias":"tender"}],"usemany":[{"entity":"offer"}]} $$;


ALTER FUNCTION public.entity_config_participation() OWNER TO bc;

--
-- Name: entity_config_passwordchangeintent(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_passwordchangeintent() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"activationcode","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"emailssent","type":"INT","default":"integer","lazy":false}],"useone":[{"entity":"user","alias":"user"}]} $$;


ALTER FUNCTION public.entity_config_passwordchangeintent() OWNER TO bc;

--
-- Name: entity_config_photo(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_photo() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"title","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"uri","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"ext","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"width","type":"INT","default":"integer","lazy":false},{"name":"height","type":"INT","default":"integer","lazy":false},{"name":"filesize","type":"INT","default":"integer","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"facecount","type":"INT","default":"integer","lazy":false},{"name":"facelist","type":"VARCHAR(255)","default":"string","lazy":false}],"useone":[{"entity":"file","alias":"file"}]} $$;


ALTER FUNCTION public.entity_config_photo() OWNER TO bc;

--
-- Name: entity_config_post(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_post() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"isactive","default":1}],"fields":[{"name":"name","type":"TEXT","default":"text","lazy":true}],"useone":[{"entity":"department","alias":"department"},{"entity":"posttype","alias":"posttype"},{"entity":"user","alias":"user"}]} $$;


ALTER FUNCTION public.entity_config_post() OWNER TO bc;

--
-- Name: entity_config_posthistory(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_posthistory() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"islast","default":0}],"fields":[{"name":"startdate","type":"INT","default":"integer","lazy":false},{"name":"enddate","type":"INT","default":"integer","lazy":false}],"useone":[{"entity":"post","alias":"post"},{"entity":"user","alias":"user"},{"entity":"posthistory","alias":"next"},{"entity":"posthistory","alias":"prev"}]} $$;


ALTER FUNCTION public.entity_config_posthistory() OWNER TO bc;

--
-- Name: entity_config_posttype(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_posttype() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"title","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"description","type":"TEXT","default":"text","lazy":true}]} $$;


ALTER FUNCTION public.entity_config_posttype() OWNER TO bc;

--
-- Name: entity_config_pricesuggestion(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_pricesuggestion() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"price","type":"REAL","default":"float","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"offer","alias":"offer"}]} $$;


ALTER FUNCTION public.entity_config_pricesuggestion() OWNER TO bc;

--
-- Name: entity_config_problemscope(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_problemscope() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"title","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}]} $$;


ALTER FUNCTION public.entity_config_problemscope() OWNER TO bc;

--
-- Name: entity_config_process(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_process() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"active","default":1}],"fields":[{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"task","alias":"task"},{"entity":"contract","alias":"contract"},{"entity":"wfa","alias":"wfa"}]} $$;


ALTER FUNCTION public.entity_config_process() OWNER TO bc;

--
-- Name: entity_config_pushtemplate(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_pushtemplate() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"title","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"description","type":"TEXT","default":"text","lazy":true}]} $$;


ALTER FUNCTION public.entity_config_pushtemplate() OWNER TO bc;

--
-- Name: entity_config_realcopy(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_realcopy() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"workflow","type":"SMALLINT","default":null,"lazy":false},{"name":"issuedate","type":"INT","default":"integer","lazy":false},{"name":"withdrawaldate","type":"INT","default":"integer","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"document","alias":"document"},{"entity":"user","alias":"person"},{"entity":"user","alias":"master"}]} $$;


ALTER FUNCTION public.entity_config_realcopy() OWNER TO bc;

--
-- Name: entity_config_redirect(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_redirect() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"uri","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"target","type":"VARCHAR(255)","default":"string","lazy":false}]} $$;


ALTER FUNCTION public.entity_config_redirect() OWNER TO bc;

--
-- Name: entity_config_redirectimg(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_redirectimg() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"uri","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"target","type":"VARCHAR(255)","default":"string","lazy":false}]} $$;


ALTER FUNCTION public.entity_config_redirectimg() OWNER TO bc;

--
-- Name: entity_config_registerintent(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_registerintent() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"email","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"activationcode","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"emailssent","type":"INT","default":"integer","lazy":false}]} $$;


ALTER FUNCTION public.entity_config_registerintent() OWNER TO bc;

--
-- Name: entity_config_reply(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_reply() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"content","type":"TEXT","default":"text","lazy":true},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"comment","alias":"parent"},{"entity":"user","alias":"autor"},{"entity":"document","alias":"document"}]} $$;


ALTER FUNCTION public.entity_config_reply() OWNER TO bc;

--
-- Name: entity_config_requisition(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_requisition() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"content","type":"JSON","default":"json","lazy":false},{"name":"workflow","type":"SMALLINT","default":null,"lazy":false},{"name":"enddate","type":"INT","default":"timestamp","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"user","alias":"author"},{"entity":"user","alias":"approver"},{"entity":"user","alias":"responsible"},{"entity":"user","alias":"currentuser"},{"entity":"document","alias":"mirror"}],"lists":[{"entity":"user","alias":"visauser","reverse":false},{"entity":"user","alias":"alreadyviseuser","reverse":false},{"entity":"document","alias":"basedocuments","reverse":false}]} $$;


ALTER FUNCTION public.entity_config_requisition() OWNER TO bc;

--
-- Name: entity_config_risk(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_risk() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"withcorrection","default":0}],"fields":[{"name":"riskworkflow","type":"SMALLINT","default":null,"lazy":false},{"name":"productaffect","type":"INT","default":"integer","lazy":false},{"name":"appearprobability","type":"INT","default":"integer","lazy":false},{"name":"nondetectprobability","type":"INT","default":"integer","lazy":false},{"name":"content","type":"TEXT","default":"text","lazy":true},{"name":"weight","type":"INT","default":"integer","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"user","alias":"controler"},{"entity":"object","alias":"object"},{"entity":"companyprocess","alias":"companyprocess"},{"entity":"sla","alias":"sla"}],"usemany":[{"entity":"controlaction"}],"lists":[{"entity":"document","alias":"relateddocumets","reverse":false}]} $$;


ALTER FUNCTION public.entity_config_risk() OWNER TO bc;

--
-- Name: entity_config_risktype(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_risktype() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"title","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"_parent","type":"INT","default":"integer","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}]} $$;


ALTER FUNCTION public.entity_config_risktype() OWNER TO bc;

--
-- Name: entity_config_role(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_role() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"title","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"homeuri","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"name","type":"VARCHAR(255)","default":"string","lazy":false}],"lists":[{"entity":"user","alias":"delegatedto","reverse":false}]} $$;


ALTER FUNCTION public.entity_config_role() OWNER TO bc;

--
-- Name: entity_config_roleuser(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_roleuser() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"isactive","default":1}],"useone":[{"entity":"user","alias":"user"},{"entity":"businessrole","alias":"businessrole"}]} $$;


ALTER FUNCTION public.entity_config_roleuser() OWNER TO bc;

--
-- Name: entity_config_selfinspection(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_selfinspection() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"done","default":0}],"fields":[{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"capa","alias":"capa"},{"entity":"document","alias":"mirror"}],"usemany":[{"entity":"selfinspectionitem"}]} $$;


ALTER FUNCTION public.entity_config_selfinspection() OWNER TO bc;

--
-- Name: entity_config_selfinspectionitem(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_selfinspectionitem() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"selfinspection","alias":"selfinspection"},{"entity":"capaevent","alias":"capaevent"}]} $$;


ALTER FUNCTION public.entity_config_selfinspectionitem() OWNER TO bc;

--
-- Name: entity_config_sla(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_sla() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"name","type":"TEXT","default":"text","lazy":true},{"name":"created","type":"INT","default":"timestamp","lazy":false}]} $$;


ALTER FUNCTION public.entity_config_sla() OWNER TO bc;

--
-- Name: entity_config_staffcontractor(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_staffcontractor() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"isactive","default":1}],"fields":[{"name":"name","type":"TEXT","default":"text","lazy":true},{"name":"mail","type":"TEXT","default":"text","lazy":true},{"name":"number","type":"TEXT","default":"text","lazy":true},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"contractor","alias":"contractor"},{"entity":"user","alias":"user"}]} $$;


ALTER FUNCTION public.entity_config_staffcontractor() OWNER TO bc;

--
-- Name: entity_config_substitution(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_substitution() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"startdate","type":"INT","default":"integer","lazy":false},{"name":"enddate","type":"INT","default":"integer","lazy":false},{"name":"processurn","type":"TEXT","default":"text","lazy":true},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"user","alias":"delegationfrom"},{"entity":"user","alias":"delegationto"}]} $$;


ALTER FUNCTION public.entity_config_substitution() OWNER TO bc;

--
-- Name: entity_config_systemevent(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_systemevent() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"notify","default":0}],"fields":[{"name":"name","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"type","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"context","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"origin","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"resource","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"delegate","type":"TEXT","default":"text","lazy":true},{"name":"triggerrule","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"exampleeventdata","type":"JSON","default":"json","lazy":false}],"useone":[{"entity":"pushtemplate","alias":"pushtemplate"},{"entity":"mailplain","alias":"mailplain"}]} $$;


ALTER FUNCTION public.entity_config_systemevent() OWNER TO bc;

--
-- Name: entity_config_systemeventrecord(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_systemeventrecord() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"origin","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"resource","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"context","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"details","type":"JSON","default":"json","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"systemevent","alias":"systemevent"}]} $$;


ALTER FUNCTION public.entity_config_systemeventrecord() OWNER TO bc;

--
-- Name: entity_config_task(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_task() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"automated","default":0}],"fields":[{"name":"title","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"timelimit","type":"INT","default":"integer","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"wfa","alias":"wfa"}]} $$;


ALTER FUNCTION public.entity_config_task() OWNER TO bc;

--
-- Name: entity_config_tender(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_tender() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"isactive","default":1},{"name":"isover","default":0}],"fields":[{"name":"name","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"usemany":[{"entity":"participation"}]} $$;


ALTER FUNCTION public.entity_config_tender() OWNER TO bc;

--
-- Name: entity_config_test(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_test() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"orderindex","type":"INT","default":"integer","lazy":false},{"name":"content","type":"TEXT","default":"text","lazy":true},{"name":"correctanswerscount","type":"TEXT","default":"text","lazy":true},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"examquestion","alias":"examquestion"}],"usemany":[{"entity":"answer"}]} $$;


ALTER FUNCTION public.entity_config_test() OWNER TO bc;

--
-- Name: entity_config_timeplan(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_timeplan() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"busytype","type":"SMALLINT","default":null,"lazy":false},{"name":"startdate","type":"INT","default":"integer","lazy":false},{"name":"enddate","type":"INT","default":"integer","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"user","alias":"user"},{"entity":"meeting","alias":"meeting"}]} $$;


ALTER FUNCTION public.entity_config_timeplan() OWNER TO bc;

--
-- Name: entity_config_tour1(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_tour1() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"isover","default":0}],"fields":[{"name":"timelimit","type":"INT","default":"integer","lazy":false},{"name":"startdate","type":"INT","default":"integer","lazy":false},{"name":"enddate","type":"INT","default":"integer","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"tender","alias":"tender"}],"lists":[{"entity":"participation","alias":"participationlist","reverse":false}]} $$;


ALTER FUNCTION public.entity_config_tour1() OWNER TO bc;

--
-- Name: entity_config_tour2(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_tour2() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"isover","default":0}],"fields":[{"name":"timelimit","type":"INT","default":"integer","lazy":false},{"name":"startdate","type":"INT","default":"integer","lazy":false},{"name":"enddate","type":"INT","default":"integer","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"tender","alias":"tender"}],"usemany":[{"entity":"pricesuggestion"}],"lists":[{"entity":"offer","alias":"offerlist","reverse":false}]} $$;


ALTER FUNCTION public.entity_config_tour2() OWNER TO bc;

--
-- Name: entity_config_tour3(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_tour3() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"isover","default":0}],"fields":[{"name":"timelimit","type":"INT","default":"integer","lazy":false},{"name":"startdate","type":"INT","default":"integer","lazy":false},{"name":"enddate","type":"INT","default":"integer","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"tender","alias":"tender"}],"lists":[{"entity":"offer","alias":"offerlist","reverse":false}]} $$;


ALTER FUNCTION public.entity_config_tour3() OWNER TO bc;

--
-- Name: entity_config_transition(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_transition() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"startpoint","default":0}],"fields":[{"name":"taskfrom","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"taskto","type":"VARCHAR(255)","default":"string","lazy":false}],"useone":[{"entity":"wfa","alias":"wfa"}]} $$;


ALTER FUNCTION public.entity_config_transition() OWNER TO bc;

--
-- Name: entity_config_user(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_user() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"active","default":1},{"name":"tester","default":0},{"name":"system","default":0}],"fields":[{"name":"email","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"phone","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"dynamicsalt","type":"INT","default":"integer","lazy":false},{"name":"password","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"name","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"wallet","type":"NUMERIC(14,2)","default":"money","lazy":false},{"name":"bonus","type":"REAL","default":"float","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"lastlogin","type":"INT","default":"timestamp","lazy":false},{"name":"prefs","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"wrbacgroups","type":"integer[]","default":"iarray","lazy":false}],"useone":[{"entity":"role","alias":"role"}],"usemany":[{"entity":"oauth2link","alias":"oauth2link"},{"entity":"oauth2session","alias":"oauth2session"}],"lists":[{"entity":"role","alias":"actas","reverse":false}]} $$;


ALTER FUNCTION public.entity_config_user() OWNER TO bc;

--
-- Name: entity_config_user_limits(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_user_limits() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"max","type":"INT","default":"integer","lazy":false},{"name":"used","type":"INT","default":"integer","lazy":false}],"useone":[{"entity":"user","alias":"user"},{"entity":"entity","alias":"entity"}]} $$;


ALTER FUNCTION public.entity_config_user_limits() OWNER TO bc;

--
-- Name: entity_config_useraccreditationlistener(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_useraccreditationlistener() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"iscomplete","default":0}],"fields":[{"name":"why","type":"TEXT","default":"text","lazy":true},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"user","alias":"slave"},{"entity":"user","alias":"listener"},{"entity":"educationalprogram","alias":"educationalprogram"}]} $$;


ALTER FUNCTION public.entity_config_useraccreditationlistener() OWNER TO bc;

--
-- Name: entity_config_userfieldsnapshot(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_userfieldsnapshot() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"isactive","default":1}],"fields":[{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"documentfieldsnapshot","alias":"documentfieldsnapshot"},{"entity":"user","alias":"user"},{"entity":"document","alias":"document"}]} $$;


ALTER FUNCTION public.entity_config_userfieldsnapshot() OWNER TO bc;

--
-- Name: entity_config_usertestresult(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_usertestresult() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"issuccessfully","default":0},{"name":"ispassed","default":1}],"fields":[{"name":"correctanswers","type":"INT","default":"integer","lazy":false},{"name":"failanswers","type":"INT","default":"integer","lazy":false},{"name":"correctanswerspercent","type":"INT","default":"integer","lazy":false},{"name":"details","type":"JSON","default":"json","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"user","alias":"user"},{"entity":"attestation","alias":"attestation"}]} $$;


ALTER FUNCTION public.entity_config_usertestresult() OWNER TO bc;

--
-- Name: entity_config_virtualcopy(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_virtualcopy() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"status":[{"name":"iscomplete","default":0},{"name":"isactive","default":1},{"name":"isresp","default":0}],"fields":[{"name":"issuedate","type":"INT","default":"integer","lazy":false},{"name":"withdrawaldate","type":"INT","default":"integer","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"document","alias":"document"},{"entity":"user","alias":"person"},{"entity":"user","alias":"master"}]} $$;


ALTER FUNCTION public.entity_config_virtualcopy() OWNER TO bc;

--
-- Name: entity_config_vizant(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_vizant() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"document","type":"SMALLINT","default":null,"lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false},{"name":"updated","type":"INT","default":"timestamp","lazy":false}],"useone":[{"entity":"user","alias":"user"}]} $$;


ALTER FUNCTION public.entity_config_vizant() OWNER TO bc;

--
-- Name: entity_config_wfa(); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION entity_config_wfa() RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$ return {"fields":[{"name":"title","type":"VARCHAR(255)","default":"string","lazy":false},{"name":"created","type":"INT","default":"timestamp","lazy":false}],"usemany":[{"entity":"task","alias":"task"},{"entity":"transition","alias":"transition"}]} $$;


ALTER FUNCTION public.entity_config_wfa() OWNER TO bc;

--
-- Name: load_usemany(text, text, integer[], text[]); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION load_usemany(entity text, hasonefield text, hasoneid integer[], fields text[]) RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$
  var entity_config = plv8.find_function("entity_config_"+entity)();
  var func_load = plv8.find_function("action_loadjson")	
  var func1 = plv8.find_function("load_useone")	
  var func2 = plv8.find_function("load_usemany")
  var simpleFields=[] //[title, date, money]
  var hasoneFields={} // {category:[title, img.uri]}
  var hasmanyFields={} // {comment:[author], photo:[]}
  
  
  
   
 var simplefields={} 				// [ title:{"name":"title", "type":"text",     "default":""}, ...]
 var notlazysimplefields={} 		// [ title:{"name":"title", "type":"text",     "default":""}, ...]
 var statuses={} 					// [ active:{"name":"active","default":0}], ...]
 var useone={}  					// [ img:{"entity":"img", "alias":"img"}, ...]
 var lists={} 						// [ likers:{"entity":"user", "alias":"likers", "reverse":"likedcomments"} ...]
 var usemany=entity_config.usemany  // [ "comments", ...]
 

 
 for(var i in entity_config.fields){
	 simplefields[entity_config.fields[i].name]=entity_config.fields[i]
 } 
 for(var i in entity_config.status){
	 statuses[entity_config.status[i].name]=entity_config.status[i]
 }
  for(var i in entity_config.useone){
	 useone[entity_config.useone[i].alias]=entity_config.useone[i]
 }
   for(var i in entity_config.lists){
	 lists[entity_config.lists[i].alias]=entity_config.lists[i]
 }
 
  
 
  
  
 var ownfields=[]
 var enemyuseone={}
 var enemyusemany={}
 var listfields={}//{likers:[title]}
   for(var p in fields){
	 var t = fields[p].split(".")
	 if(t.length==1&&(simplefields[t[0]]||statuses[t[0]]||useone[t[0]]||lists[t[0]])){
	 	ownfields.push(t[0])
     continue;
	 }
	
	 
		/* plv8.elog(INFO, "POINT 1") */
	if(t.length>1&&(simplefields[t[0]]||statuses[t[0]]||useone[t[0]])){
		/* plv8.elog(INFO, "POINT 2") */
		var en=t.shift()
		/* plv8.elog(INFO, "PO */
		if(!enemyuseone[en]){enemyuseone[en]=[]}
		enemyuseone[en].push(t.join("."))
		/* plv8.elog(INFO, "POINT 4 "+JSON.stringify(enemyuseone)) */
	    continue;
	}
	if(t.length>1&&lists[t[0]]){
		var en=t.shift()
		if(!listfields[en]){listfields[en]=[]}
	listfields[en].push(t.join("."))
	    continue;
	}
	
	if(t.length>1&&usemany.indexOf(t[0])!=-1){
		var en=t.shift()	
		if(!enemyusemany[en]){enemyusemany[en]=[]}
	enemyusemany[en].push(t.join("."))
	}
	
    }
	
		
 /* //SELECT action_load('{"action":"load", "urn":"urn-news",  "money":{"gt": "123,11"},"select":["title", "money", "photo"]}'   ); */
 var tf = ownfields.concat(Object.keys(enemyuseone)).concat(Object.keys(listfields))
 // plv8.elog(INFO, "tf = "+tf)
 // Array.prototype.push.apply(tf, Object.keys(hasmanyFields))
 tf.push("id")
 var m={urn:"urn-"+entity}
 if(hasoneid.length>1){
 m[hasonefield]=hasoneid
 }else{
 m[hasonefield]=hasoneid[0] 
 }

 m.selectonly=tf	
 
 //plv8.elog(INFO, "SQL usemany for "+entity+" ownfields: "+ownfields+" ; fields: "+JSON.stringify(fields)+" ; enemyusemany: "+JSON.stringify(hasmanyFields)+"; enemyuseone"+JSON.stringify(enemyuseone))
 //plv8.elog(INFO, "SQL usemany m : "+JSON.stringify(m))
 var first_objects = func_load(m) 
 //plv8.elog(INFO, "SQL hasone rezult"+JSON.stringify(first_objects))
/* //обработка has one */
	var hso ={} /* //{category:{123:{"sport", "./sport/12454.png"}, 112:{"sport", "./sport/12454.png"}}} */

	for(var i in first_objects){
				/* //обработка has one */
		for(var q in  enemyuseone){
			if(!hso[q]){hso[q]={}}
		var cid=first_objects[i][q]
		if(hso[q][cid]){ first_objects[i][q]=hso[q][cid];continue}
	    var vl = func1(q, cid, enemyuseone[q])/* // (entity, id, fileds) (category, 112, [title, img.uri])->{"sport", img:{uri:"./sport/12454.png"}} */
		first_objects[i][q]=vl
		hso[q][cid]=vl
	    }
	    /* // обработка has Many */
		for(var m in  enemyusemany){
		var va = func2(m, entity, [first_objects[i]["id"]], enemyusemany[m])/* //(entity, hasoneField, hasoneId, fields) (comments, news, 7, [autor])->[{autor:петя}, {author:вася}] */
		first_objects[i][m]=va
		}
		/* обработка листов TODO */
		for(var l in listfields){
			if(first_objects[i][m]&&first_objects[i][m].lengh!=0){
		var va = func2(m, "id", first_objects[i][l], listfields[m])/* //(entity, hasoneField, hasoneId, fields) (comments, news, 7, [autor])->[{autor:петя}, {author:вася}] */
		first_objects[i][l]=va
			}
		}
		
}
 
 
 return first_objects
 

  $$;


ALTER FUNCTION public.load_usemany(entity text, hasonefield text, hasoneid integer[], fields text[]) OWNER TO bc;

--
-- Name: load_useone(text, integer, text[]); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION load_useone(entity text, id integer, fields text[]) RETURNS json
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$
  var entity_config = plv8.find_function("entity_config_"+entity)();
  var func_load = plv8.find_function("action_loadjson")	
  var func1 = plv8.find_function("load_useone")	
  var func2 = plv8.find_function("load_usemany") 
     
 var simplefields={} 				// [ title:{"name":"title", "type":"text",     "default":""}, ...]
 var notlazysimplefields={} 		// [ title:{"name":"title", "type":"text",     "default":""}, ...]
 var statuses={} 					// [ active:{"name":"active","default":0}], ...]
 var useone={}  					// [ img:{"entity":"img", "alias":"img"}, ...]
 var lists={} 						// [ likers:{"entity":"user", "alias":"likers", "reverse":"likedcomments"} ...]
 var usemany=entity_config.usemany  // [ "comments", ...]
 

 
 for(var i in entity_config.fields){
	 simplefields[entity_config.fields[i].name]=entity_config.fields[i]
 } 
 for(var i in entity_config.status){
	 statuses[entity_config.status[i].name]=entity_config.status[i]
 }
  for(var i in entity_config.useone){
	 useone[entity_config.useone[i].alias]=entity_config.useone[i]
 }
   for(var i in entity_config.lists){
	 lists[entity_config.lists[i].alias]=entity_config.lists[i]
 }
 
  
 
  
  
 var ownfields=[]
 var enemyuseone={}
 var enemyusemany={}
 var listfields={}//{likers:[title]}
   for(var p in fields){
	 var t = fields[p].split(".")
	 if(t.length==1&&(simplefields[t[0]]||statuses[t[0]]||useone[t[0]]||lists[t[0]])){
	 	ownfields.push(t[0])
     continue;
	 }
	
	 
		/* plv8.elog(INFO, "POINT 1") */
	if(t.length>1&&(simplefields[t[0]]||statuses[t[0]]||useone[t[0]])){
		/* plv8.elog(INFO, "POINT 2") */
		var en=t.shift()
		/* plv8.elog(INFO, "PO */
		if(!enemyuseone[en]){enemyuseone[en]=[]}
		enemyuseone[en].push(t.join("."))
		/* plv8.elog(INFO, "POINT 4 "+JSON.stringify(enemyuseone)) */
	    continue;
	}
	if(t.length>1&&lists[t[0]]){
		var en=t.shift()
		if(!listfields[en]){listfields[en]=[]}
	listfields[en].push(t.join("."))
	    continue;
	}
	
	if(t.length>1&&usemany.indexOf(t[0])!=-1){
		var en=t.shift()	
		if(!enemyusemany[en]){enemyusemany[en]=[]}
	enemyusemany[en].push(t.join("."))
	}
	
    }
	
		
 /* //SELECT action_load('{"action":"load", "urn":"urn-news",  "money":{"gt": "123,11"},"select":["title", "money", "photo"]}'   ); */
 var tf = ownfields.concat(Object.keys(enemyuseone)).concat(Object.keys(listfields))
 // plv8.elog(INFO, "tf = "+tf)
 tf.push("id")
 var m={urn:"urn-"+entity}
 m.id=id
 m.selectonly=tf	
 
 m.limit=1 
 //plv8.elog(INFO, "SQL useone for "+entity+" ownfields: "+ownfields+" ; fields: "+JSON.stringify(fields)+" ; enemyusemany: "+JSON.stringify(enemyusemany)+"; enemyuseone"+JSON.stringify(enemyuseone))
 //plv8.elog(INFO, "SQL useone m : "+JSON.stringify(m))
 var first_objects = func_load(m) 
 //plv8.elog(INFO, "SQL hasone rezult"+JSON.stringify(first_objects))
/* //обработка has one */
	var hso ={} /* //{category:{123:{"sport", "./sport/12454.png"}, 112:{"sport", "./sport/12454.png"}}} */

	for(var i in first_objects){
		/* //обработка has one */
		for(var q in  enemyuseone){
			if(!hso[q]){hso[q]={}}
		var cid=first_objects[i][q]
		if(hso[q][cid]){ first_objects[i][q]=hso[q][cid];continue}
	    var vl = func1(q, cid, enemyuseone[q])/* // (entity, id, fileds) (category, 112, [title, img.uri])->{"sport", img:{uri:"./sport/12454.png"}} */
		first_objects[i][q]=vl
		hso[q][cid]=vl
	    }
	    /* // обработка has Many */
		for(var m in  enemyusemany){
		var va = func2(m, entity, first_objects[i]["id"], enemyusemany[m])/* //(entity, hasoneField, hasoneId, fields) (comments, news, 7, [autor])->[{autor:петя}, {author:вася}] */
		first_objects[i][m]=va
		}
		/* обработка листов TODO */
		for(var l in listfields){
		var va = func2(l, "id", first_objects[i]["id"], listfields[m])/* //(entity, hasoneField, hasoneId, fields) (comments, news, 7, [autor])->[{autor:петя}, {author:вася}] */
		first_objects[i][l]=va
		
		}
}
 
 
 return first_objects[0]
 
  $$;


ALTER FUNCTION public.load_useone(entity text, id integer, fields text[]) OWNER TO bc;

--
-- Name: typeconvertor(text, text); Type: FUNCTION; Schema: public; Owner: bc
--

CREATE FUNCTION typeconvertor(typ text, val text) RETURNS text
    LANGUAGE plv8 IMMUTABLE STRICT
    AS $$
var b
if(val==null){return null}
switch (typ){
  case "text": b="'"+val+"'::text"  ;break
  case "money":b="'"+val+"'::money";break
  case "timestamp":b="'"+val+"'::timestamp";break
  case "XML":b="'"+val+"'::XML";break
  case "INTEGER":b="'"+val+"'::INTEGER";break
  case "inet":b="'"+val+"'::inet";break
  case "NUMERIC(14,2)":b="'"+val+"'::NUMERIC(14,2)";break
  case "REAL":b="'"+val+"'::REAL";break
  case "DATE":b="'"+val+"'::DATE";break
  case "VARCHAR(255)":b="'"+val+"'::VARCHAR(255)";break
  case "INT":b="'"+val+"'::INTEGER";break
  case "VARCHAR(32)":b="'"+val+"'::VARCHAR(32)";break
  case "int[]":b="'{"+val+"}'::INTEGER[]";break
  case "INTEGER[]":b="'{"+val+"}'::INTEGER[]";break
  case "integer[]":b="'{"+val+"}'::INTEGER[]";break
  case "text[]":b="'{"+val+"}'::text[]";break
  case "TEXT[]":b="'{"+val+"}'::text[]";break
  case "json":b="'"+JSON.stringify(val)+"'::json";break
  case "JSON":b="'"+JSON.stringify(val)+"'::json";break
  case "boolean":b="'"+val+"'::boolean";break
  //TODO json type check for valid
  default:  b = "'"+val+"'::"+typ
  }
//plv8.elog(INFO, "typeConvertor --- typ-"+typ+"  val-"+val+"  b-"+b)
  return b
$$;


ALTER FUNCTION public.typeconvertor(typ text, val text) OWNER TO bc;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: Actor_Role_System; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Actor_Role_System" (
    id bigint NOT NULL,
    "delegatedto_ActorUserSystem" integer[],
    title character varying(255) DEFAULT NULL::character varying,
    homeuri character varying(255) DEFAULT NULL::character varying,
    name character varying(255) DEFAULT NULL::character varying
);


ALTER TABLE "Actor_Role_System" OWNER TO bc;

--
-- Name: Actor_User_System; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Actor_User_System" (
    id bigint NOT NULL,
    active smallint DEFAULT 0,
    tester smallint DEFAULT 0,
    system smallint DEFAULT 0,
    "ActorRoleSystem" integer,
    "actas_ActorRoleSystem" integer[],
    "following_ActorUserSystem" integer[],
    "followers_ActorUserSystem" integer[],
    email character varying(255) DEFAULT NULL::character varying,
    phone character varying(255) DEFAULT NULL::character varying,
    dynamicsalt integer,
    password character varying(255) DEFAULT NULL::character varying,
    name character varying(255) DEFAULT NULL::character varying,
    wallet numeric(14,2) DEFAULT NULL::numeric,
    bonus real,
    created integer,
    lastlogin integer,
    prefs character varying(255) DEFAULT NULL::character varying,
    wrbacgroups integer[]
);


ALTER TABLE "Actor_User_System" OWNER TO bc;

--
-- Name: BusinessObject_Record_Polymorph; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "BusinessObject_Record_Polymorph" (
    id bigint NOT NULL,
    isarchive smallint DEFAULT 0,
    "DefinitionClassBusinessObject" integer,
    "DefinitionTypeBusinessObject" integer,
    "MateriallyResponsible" integer,
    periodicityvalidation integer,
    periodicityverification integer,
    periodicitycalibration integer,
    periodicitycleaning integer,
    title character varying(255) DEFAULT NULL::character varying,
    _parent integer,
    inventorynumber character varying(255) DEFAULT NULL::character varying,
    description text,
    currentproperties text,
    value numeric(14,2) DEFAULT NULL::numeric,
    currency character varying(255) DEFAULT NULL::character varying,
    maker character varying(255) DEFAULT NULL::character varying,
    expirationdate integer,
    startupdate integer,
    maintenancework text,
    created integer,
    updated integer,
    ordered integer,
    location integer,
    periodicitymaintenance bigint,
    boofclient bigint,
    serialnumber character varying(255) DEFAULT NULL::character varying,
    "ResponsibleMaintenance" bigint,
    "ResponsibleValidation" bigint,
    "ResponsibleVerification" bigint,
    "ResponsibleCalibration" bigint,
    "ResponsibleCleaning" bigint
);


ALTER TABLE "BusinessObject_Record_Polymorph" OWNER TO bc;

--
-- Name: Calendar_Period_Month; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Calendar_Period_Month" (
    id bigint NOT NULL,
    isactive smallint DEFAULT 0,
    title character varying(255) DEFAULT NULL::character varying,
    created integer,
    updated integer,
    ordered integer,
    everynmonth integer
);


ALTER TABLE "Calendar_Period_Month" OWNER TO bc;

--
-- Name: Communication_Comment_Level2withEditingSuggestion; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Communication_Comment_Level2withEditingSuggestion" (
    id bigint NOT NULL,
    iseditingsuggestion smallint DEFAULT 0,
    autor integer,
    appliedautor integer,
    approvedautor integer,
    editingsuggestionautor integer,
    content text,
    appliedstatus smallint,
    approvedstatus smallint,
    editingsuggestionstatus smallint,
    document character varying(255) DEFAULT NULL::character varying,
    docpath character varying(255) DEFAULT NULL::character varying,
    replyto integer,
    created integer,
    cancel smallint DEFAULT 0,
    toreplyto integer
);


ALTER TABLE "Communication_Comment_Level2withEditingSuggestion" OWNER TO bc;

--
-- Name: Company_LegalEntity_Counterparty; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Company_LegalEntity_Counterparty" (
    id bigint NOT NULL,
    isactive smallint DEFAULT 0,
    isclient smallint DEFAULT 0,
    iscontractor smallint DEFAULT 0,
    "BusinessArea" integer,
    "BusinessObjectRecordPolymorph" integer,
    title character varying(255) DEFAULT NULL::character varying,
    legaladdress character varying(255) DEFAULT NULL::character varying,
    ba character varying(255) DEFAULT NULL::character varying,
    mfo character varying(255) DEFAULT NULL::character varying,
    edropou character varying(255) DEFAULT NULL::character varying,
    contactname character varying(255) DEFAULT NULL::character varying,
    mail character varying(255) DEFAULT NULL::character varying,
    number integer,
    letter character varying(255) DEFAULT NULL::character varying,
    signatoryname character varying(255) DEFAULT NULL::character varying,
    baseaction character varying(255) DEFAULT NULL::character varying,
    other character varying(255) DEFAULT NULL::character varying,
    created integer,
    updated integer,
    ordered integer,
    "ManagementPostIndividual" bigint
);


ALTER TABLE "Company_LegalEntity_Counterparty" OWNER TO bc;

--
-- Name: Company_Structure_Companygroup; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Company_Structure_Companygroup" (
    id bigint NOT NULL,
    title character varying(255) DEFAULT NULL::character varying,
    created integer,
    updated integer,
    ordered integer
);


ALTER TABLE "Company_Structure_Companygroup" OWNER TO bc;

--
-- Name: Company_Structure_Department; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Company_Structure_Department" (
    id bigint NOT NULL,
    "HeadOfDepartment" integer,
    title character varying(255) DEFAULT NULL::character varying,
    created integer,
    updated integer,
    ordered integer,
    _parent integer
);


ALTER TABLE "Company_Structure_Department" OWNER TO bc;

--
-- Name: Company_Structure_Division; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Company_Structure_Division" (
    id bigint NOT NULL,
    "CompanyStructureDepartment" integer,
    "HeadOfDepartment" integer,
    title character varying(255) DEFAULT NULL::character varying,
    created integer,
    updated integer,
    ordered integer
);


ALTER TABLE "Company_Structure_Division" OWNER TO bc;

--
-- Name: DMS_Copy_Controled; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "DMS_Copy_Controled" (
    id bigint NOT NULL,
    isactive smallint DEFAULT 0,
    "DocumentRegulationsSOP" bigint,
    master bigint,
    "holders_PeopleEmployeeInternal" integer[],
    "previous_PeopleEmployeeInternal" integer[],
    created integer,
    dateissue integer,
    datereturn integer
);


ALTER TABLE "DMS_Copy_Controled" OWNER TO bc;

--
-- Name: DMS_Copy_Realnoncontrolcopy; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "DMS_Copy_Realnoncontrolcopy" (
    id bigint NOT NULL,
    isactive smallint DEFAULT 0,
    isreturn smallint DEFAULT 0,
    realcopy bigint,
    master bigint,
    "holders_PeopleEmployeeInternal" integer[],
    "previous_PeopleEmployeeInternal" integer[],
    created integer,
    dateissue integer
);


ALTER TABLE "DMS_Copy_Realnoncontrolcopy" OWNER TO bc;

--
-- Name: DMS_DecisionSheet_Signed; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "DMS_DecisionSheet_Signed" (
    id bigint NOT NULL,
    closed smallint DEFAULT 0,
    needsignfrom character varying(255)[],
    hassignfrom character varying(255)[],
    document character varying(255) DEFAULT NULL::character varying,
    created integer,
    hascancelfrom character varying(255)[]
);


ALTER TABLE "DMS_DecisionSheet_Signed" OWNER TO bc;

--
-- Name: DMS_Document_Universal; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "DMS_Document_Universal" (
    id bigint NOT NULL,
    document character varying(255) DEFAULT NULL::character varying,
    created integer,
    indexabletext text,
    version integer,
    code character varying(255) DEFAULT NULL::character varying,
    initiator character varying(255) DEFAULT NULL::character varying,
    vised boolean,
    approved boolean,
    done boolean,
    archived boolean,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    "DefinitionPrototypeSystem" bigint,
    title character varying(255) DEFAULT NULL::character varying
);


ALTER TABLE "DMS_Document_Universal" OWNER TO bc;

--
-- Name: DMS_Viewaccess_ByProcedure; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "DMS_Viewaccess_ByProcedure" (
    id bigint NOT NULL,
    isactive smallint DEFAULT 0,
    isreturn smallint DEFAULT 0,
    master bigint,
    "holder_PeopleEmployeeInternal" integer[],
    dateissue integer,
    datereturn integer
);


ALTER TABLE "DMS_Viewaccess_ByProcedure" OWNER TO bc;

--
-- Name: Definition_Class_BusinessObject; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Definition_Class_BusinessObject" (
    id bigint NOT NULL,
    title character varying(255) DEFAULT NULL::character varying,
    created integer,
    updated integer,
    ordered integer
);


ALTER TABLE "Definition_Class_BusinessObject" OWNER TO bc;

--
-- Name: Definition_DocumentClass_ForPrototype; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Definition_DocumentClass_ForPrototype" (
    id bigint NOT NULL,
    title character varying(255) DEFAULT NULL::character varying,
    name character varying(255) DEFAULT NULL::character varying
);


ALTER TABLE "Definition_DocumentClass_ForPrototype" OWNER TO bc;

--
-- Name: Definition_Prototype_System; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Definition_Prototype_System" (
    id bigint NOT NULL,
    title character varying(255) DEFAULT NULL::character varying,
    indomain character varying(255) DEFAULT NULL::character varying,
    ofclass character varying(255) DEFAULT NULL::character varying,
    oftype character varying(255) DEFAULT NULL::character varying,
    isprocess smallint DEFAULT 0,
    approver integer,
    "visants_ManagementPostIndividual" integer[],
    unmanaged smallint DEFAULT 0,
    withhardcopy smallint DEFAULT 0
);


ALTER TABLE "Definition_Prototype_System" OWNER TO bc;

--
-- Name: Definition_Type_BusinessObject; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Definition_Type_BusinessObject" (
    id bigint NOT NULL,
    "DefinitionClassBusinessObject" integer,
    title character varying(255) DEFAULT NULL::character varying,
    created integer,
    updated integer,
    ordered integer
);


ALTER TABLE "Definition_Type_BusinessObject" OWNER TO bc;

--
-- Name: Directory_AdditionalSection_Simple; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Directory_AdditionalSection_Simple" (
    id bigint NOT NULL,
    sectiontitle character varying(255) DEFAULT NULL::character varying,
    sectiontext text,
    "DocumentRegulationsI" bigint,
    "DocumentRegulationsSOP" bigint,
    "approvedrisks_RiskManagementRiskApproved" integer[],
    "DocumentRegulationsP" bigint
);


ALTER TABLE "Directory_AdditionalSection_Simple" OWNER TO bc;

--
-- Name: Directory_Branch_Item; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Directory_Branch_Item" (
    id bigint NOT NULL,
    title character varying(255) DEFAULT NULL::character varying,
    created integer,
    updated integer,
    ordered integer
);


ALTER TABLE "Directory_Branch_Item" OWNER TO bc;

--
-- Name: Directory_BusinessProcess_Item; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Directory_BusinessProcess_Item" (
    id bigint NOT NULL,
    responsible integer,
    title character varying(255) DEFAULT NULL::character varying,
    created integer,
    updated integer,
    ordered integer
);


ALTER TABLE "Directory_BusinessProcess_Item" OWNER TO bc;

--
-- Name: Directory_BusinessProjects_Item; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Directory_BusinessProjects_Item" (
    id bigint NOT NULL,
    title character varying(255) DEFAULT NULL::character varying,
    created integer,
    updated integer,
    ordered integer
);


ALTER TABLE "Directory_BusinessProjects_Item" OWNER TO bc;

--
-- Name: Directory_CalendarPlan_Simple; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Directory_CalendarPlan_Simple" (
    id bigint NOT NULL,
    "DocumentRegulationsMP" bigint,
    "BusinessObjectRecordPolymorph" bigint,
    "DocumentRegulationsPV" character varying(255)[],
    date date
);


ALTER TABLE "Directory_CalendarPlan_Simple" OWNER TO bc;

--
-- Name: Directory_ControlAction_Universal; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Directory_ControlAction_Universal" (
    id bigint NOT NULL,
    "CalendarPeriodMonth" bigint,
    description text,
    created integer,
    updated integer,
    ordered integer
);


ALTER TABLE "Directory_ControlAction_Universal" OWNER TO bc;

--
-- Name: Directory_Deviation_PreCapa; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Directory_Deviation_PreCapa" (
    id bigint NOT NULL,
    "CompanyStructureDepartment" integer,
    description text,
    created integer,
    updated integer,
    "approvedrisks_RiskManagementRiskApproved" integer[],
    "notapprovedrisks_RiskManagementRiskNotApproved" integer[],
    ordered integer
);


ALTER TABLE "Directory_Deviation_PreCapa" OWNER TO bc;

--
-- Name: Directory_Fixedasset_Simple; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Directory_Fixedasset_Simple" (
    id bigint NOT NULL,
    "DocumentProtocolVT" bigint,
    equipment bigint,
    numberequipment character varying(255) DEFAULT NULL::character varying,
    specification text
);


ALTER TABLE "Directory_Fixedasset_Simple" OWNER TO bc;

--
-- Name: Directory_KindOfOperations_Item; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Directory_KindOfOperations_Item" (
    id bigint NOT NULL,
    title character varying(255) DEFAULT NULL::character varying,
    created integer,
    updated integer,
    ordered integer
);


ALTER TABLE "Directory_KindOfOperations_Item" OWNER TO bc;

--
-- Name: Directory_Materialbase_Simple; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Directory_Materialbase_Simple" (
    id bigint NOT NULL,
    "DocumentRegulationsPV" bigint,
    "BusinessObjectRecordPolymorph" bigint,
    numberequipment character varying(255) DEFAULT NULL::character varying,
    specification text
);


ALTER TABLE "Directory_Materialbase_Simple" OWNER TO bc;

--
-- Name: Directory_Media_Attributed; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Directory_Media_Attributed" (
    id bigint NOT NULL,
    text character varying(255) DEFAULT NULL::character varying,
    attachment character varying(255) DEFAULT NULL::character varying,
    ordered integer
);


ALTER TABLE "Directory_Media_Attributed" OWNER TO bc;

--
-- Name: Directory_MissingPeople_Item; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Directory_MissingPeople_Item" (
    id bigint NOT NULL,
    "ManagementPostIndividual" bigint,
    why character varying(255) DEFAULT NULL::character varying,
    missingdate integer,
    created integer,
    updated integer,
    ordered integer
);


ALTER TABLE "Directory_MissingPeople_Item" OWNER TO bc;

--
-- Name: Directory_Options_Simple; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Directory_Options_Simple" (
    id bigint NOT NULL,
    "DocumentRegulationsPV" bigint,
    titleparametr character varying(255) DEFAULT NULL::character varying,
    descriptionmethodic text
);


ALTER TABLE "Directory_Options_Simple" OWNER TO bc;

--
-- Name: Directory_Replacement_Item; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Directory_Replacement_Item" (
    id bigint NOT NULL,
    missing bigint,
    replacement bigint,
    why character varying(255) DEFAULT NULL::character varying,
    missingdate integer,
    created integer,
    updated integer,
    ordered integer
);


ALTER TABLE "Directory_Replacement_Item" OWNER TO bc;

--
-- Name: Directory_Responsible_Simple; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Directory_Responsible_Simple" (
    id bigint NOT NULL,
    "DocumentRegulationsPV" bigint,
    "ManagementPostIndividual" bigint,
    typeofwork text
);


ALTER TABLE "Directory_Responsible_Simple" OWNER TO bc;

--
-- Name: Directory_Responsibletwo_Simple; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Directory_Responsibletwo_Simple" (
    id bigint NOT NULL,
    "DocumentProtocolVT" bigint,
    "ManagementPostIndividual" bigint,
    worktype text
);


ALTER TABLE "Directory_Responsibletwo_Simple" OWNER TO bc;

--
-- Name: Directory_RiskProtocolSolution_SI; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Directory_RiskProtocolSolution_SI" (
    id bigint NOT NULL,
    "DocumentProtocolSI" integer,
    solutiononrisk smallint,
    comment text
);


ALTER TABLE "Directory_RiskProtocolSolution_SI" OWNER TO bc;

--
-- Name: Directory_SLA_Item; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Directory_SLA_Item" (
    id bigint NOT NULL,
    title character varying(255) DEFAULT NULL::character varying
);


ALTER TABLE "Directory_SLA_Item" OWNER TO bc;

--
-- Name: Directory_Solutionvariants_Simple; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Directory_Solutionvariants_Simple" (
    id bigint NOT NULL,
    "DocumentProtocolKI" bigint,
    checkingresult smallint,
    comment text,
    "DocumentCorrectionCapa" bigint
);


ALTER TABLE "Directory_Solutionvariants_Simple" OWNER TO bc;

--
-- Name: Directory_TechnicalTask_ForWorks; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Directory_TechnicalTask_ForWorks" (
    id bigint NOT NULL,
    "DocumentTechnicalTaskForWorks" bigint,
    name character varying(255) DEFAULT NULL::character varying,
    datebegin date,
    dateend date,
    volume character varying(255) DEFAULT NULL::character varying
);


ALTER TABLE "Directory_TechnicalTask_ForWorks" OWNER TO bc;

--
-- Name: Directory_TechnicalTask_Materials; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Directory_TechnicalTask_Materials" (
    id bigint NOT NULL,
    name character varying(255) DEFAULT NULL::character varying,
    quantity character varying(255) DEFAULT NULL::character varying,
    date date,
    materialdesription text
);


ALTER TABLE "Directory_TechnicalTask_Materials" OWNER TO bc;

--
-- Name: Directory_TechnicalTask_Works; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Directory_TechnicalTask_Works" (
    id bigint NOT NULL,
    datebegin date,
    dateend date,
    materialdesription text
);


ALTER TABLE "Directory_TechnicalTask_Works" OWNER TO bc;

--
-- Name: Directory_TenderBidder_Simple; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Directory_TenderBidder_Simple" (
    id bigint NOT NULL,
    "CompanyLegalEntityCounterparty" bigint,
    "DocumentTenderExtended" bigint,
    docpermitsneed character varying(255)[] DEFAULT NULL::character varying[],
    commercialoffer character varying(255) DEFAULT NULL::character varying,
    techvalidation smallint,
    biddersolution smallint,
    commentcounterparty text,
    techvalidationcomment text
);


ALTER TABLE "Directory_TenderBidder_Simple" OWNER TO bc;

--
-- Name: Directory_TenderPosition_Simple; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Directory_TenderPosition_Simple" (
    id bigint NOT NULL,
    "DocumentTenderExtended" bigint,
    titleposition character varying(255) DEFAULT NULL::character varying
);


ALTER TABLE "Directory_TenderPosition_Simple" OWNER TO bc;

--
-- Name: Document_Capa_Deviation; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Capa_Deviation" (
    id bigint NOT NULL,
    created integer,
    updated integer,
    descriptiondeviation text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[],
    related character varying(255)[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    "RiskManagementRiskApproved_RiskManagementRiskApproved" integer[],
    ordered integer,
    "RiskManagementRiskNotApproved_RiskManagementRiskNotApproved" integer[],
    eventplace character varying(255) DEFAULT NULL::character varying,
    eventtime integer
);


ALTER TABLE "Document_Capa_Deviation" OWNER TO bc;

--
-- Name: Document_Claim_R_LSC; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Claim_R_LSC" (
    id bigint NOT NULL,
    companyproject bigint,
    "CompanyLegalEntityCounterparty" bigint,
    warehouse bigint,
    "DocumentSolutionUniversal" bigint,
    "solutionvariants_DocumentSolutionUniversal" integer[],
    specialrequirement text,
    attachments character varying(255)[],
    purchaseorder text,
    attachmentdocs character varying(255)[],
    plancomingdate date,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[],
    related character varying(255)[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    rtaken smallint
);


ALTER TABLE "Document_Claim_R_LSC" OWNER TO bc;

--
-- Name: Document_Claim_R_LSD; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Claim_R_LSD" (
    id bigint NOT NULL,
    companyproject integer,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    "DocumentSolutionUniversal" integer,
    specialrequirement text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    pickingrequest character varying(255) DEFAULT NULL::character varying,
    wmsdocs character varying(255)[] DEFAULT NULL::character varying[],
    planshipmentdate date,
    cargoreceiver text,
    reasonshipping text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "solutionvariants_DocumentSolutionUniversal" integer[],
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    rtaken smallint
);


ALTER TABLE "Document_Claim_R_LSD" OWNER TO bc;

--
-- Name: Document_Claim_R_LSM; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Claim_R_LSM" (
    id bigint NOT NULL,
    companyproject integer,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    "DocumentSolutionUniversal" integer,
    specialrequirement text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    reason text,
    goodsdocs character varying(255)[] DEFAULT NULL::character varying[],
    desireddate date,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "solutionvariants_DocumentSolutionUniversal" integer[],
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    rtaken smallint
);


ALTER TABLE "Document_Claim_R_LSM" OWNER TO bc;

--
-- Name: Document_Claim_R_LST; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Claim_R_LST" (
    id bigint NOT NULL,
    companyproject integer,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    "DocumentSolutionUniversal" integer,
    specialrequirement text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    customsdeclarationtype smallint,
    customsclearancedocs character varying(255)[] DEFAULT NULL::character varying[],
    customsclearancedate date,
    adddescription text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "solutionvariants_DocumentSolutionUniversal" integer[],
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    rtaken smallint
);


ALTER TABLE "Document_Claim_R_LST" OWNER TO bc;

--
-- Name: Document_Claim_R_LSС; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Claim_R_LSС" (
    id bigint NOT NULL,
    companyproject integer,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    "DocumentSolutionUniversal" integer,
    "proposedsolutions_DocumentSolutionUniversal" integer[],
    specialrequirement text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    purchaseorder text,
    attachmentdocs character varying(255)[] DEFAULT NULL::character varying[],
    plancomingdate date,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer
);


ALTER TABLE "Document_Claim_R_LSС" OWNER TO bc;

--
-- Name: Document_Claim_R_OQF; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Claim_R_OQF" (
    id bigint NOT NULL,
    companyproject integer,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    "DocumentSolutionUniversal" integer,
    responsibleold integer,
    warehouseold integer,
    responsiblenew integer,
    warehousenew integer,
    specialrequirement text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    codenumber text,
    descriptionneed text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "solutionvariants_DocumentSolutionUniversal" integer[],
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    rtaken smallint
);


ALTER TABLE "Document_Claim_R_OQF" OWNER TO bc;

--
-- Name: Document_Claim_R_OQR; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Claim_R_OQR" (
    id bigint NOT NULL,
    companyproject integer,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    "DocumentSolutionUniversal" integer,
    purchaseuser integer,
    specialrequirement text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    descriptionneed text,
    purchasename text,
    purchasequantity character varying(255) DEFAULT NULL::character varying,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "solutionvariants_DocumentSolutionUniversal" integer[],
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    rtaken smallint
);


ALTER TABLE "Document_Claim_R_OQR" OWNER TO bc;

--
-- Name: Document_Claim_R_PAD; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Claim_R_PAD" (
    id bigint NOT NULL,
    companyproject integer,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    "DocumentSolutionUniversal" integer,
    specialrequirement text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    software text,
    descriptionneed text,
    description text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "solutionvariants_DocumentSolutionUniversal" integer[],
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    rtaken smallint
);


ALTER TABLE "Document_Claim_R_PAD" OWNER TO bc;

--
-- Name: Document_Claim_R_PAI; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Claim_R_PAI" (
    id bigint NOT NULL,
    companyproject integer,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    "DocumentSolutionUniversal" integer,
    "employees_ManagementPostIndividual" integer[],
    specialrequirement text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    software text,
    descriptionneed text,
    description text,
    link text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "solutionvariants_DocumentSolutionUniversal" integer[],
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    rtaken smallint
);


ALTER TABLE "Document_Claim_R_PAI" OWNER TO bc;

--
-- Name: Document_Claim_R_PAT; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Claim_R_PAT" (
    id bigint NOT NULL,
    companyproject integer,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    "DocumentSolutionUniversal" integer,
    "hardwareuser_ManagementPostIndividual" integer[],
    specialrequirement text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    hardware text,
    minimumrequirement text,
    descriptionneed text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "solutionvariants_DocumentSolutionUniversal" integer[],
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    rtaken smallint
);


ALTER TABLE "Document_Claim_R_PAT" OWNER TO bc;

--
-- Name: Document_Claim_R_QDA; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Claim_R_QDA" (
    id bigint NOT NULL,
    companyproject bigint,
    "CompanyLegalEntityCounterparty" bigint,
    warehouse bigint,
    "DocumentSolutionUniversal" bigint,
    auditwarehouse bigint,
    auditcounterparty bigint,
    "CalendarPeriodMonth" bigint,
    "solutionvariants_DocumentSolutionUniversal" integer[],
    specialrequirement text,
    attachments character varying(255)[],
    eventtype smallint,
    dateprev date,
    datenext date,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[],
    related character varying(255)[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    rtaken smallint
);


ALTER TABLE "Document_Claim_R_QDA" OWNER TO bc;

--
-- Name: Document_Claim_R_QDC; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Claim_R_QDC" (
    id bigint NOT NULL,
    companyproject integer,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    "DocumentSolutionUniversal" integer,
    specialrequirement text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    initialsituation text,
    changesdescription text,
    expectedresult text,
    link text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "solutionvariants_DocumentSolutionUniversal" integer[],
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    rtaken smallint
);


ALTER TABLE "Document_Claim_R_QDC" OWNER TO bc;

--
-- Name: Document_Claim_R_QDE; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Claim_R_QDE" (
    id bigint NOT NULL,
    companyproject integer,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    "DocumentSolutionUniversal" integer,
    "CalendarPeriodMonth" integer,
    "trainingprogram_DocumentRegulationsTA" integer[],
    "student_ManagementPostIndividual" integer[],
    specialrequirement text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    reason text,
    dateprev date,
    datenext date,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "solutionvariants_DocumentSolutionUniversal" integer[],
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    rtaken smallint
);


ALTER TABLE "Document_Claim_R_QDE" OWNER TO bc;

--
-- Name: Document_Claim_R_QDM; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Claim_R_QDM" (
    id bigint NOT NULL,
    companyproject integer,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    "DocumentSolutionUniversal" integer,
    "BusinessObjectRecordPolymorph" integer,
    specialrequirement text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    descriptionneed text,
    eventtype smallint,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "solutionvariants_DocumentSolutionUniversal" integer[],
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    rtaken smallint
);


ALTER TABLE "Document_Claim_R_QDM" OWNER TO bc;

--
-- Name: Document_Claim_R_QDА; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Claim_R_QDА" (
    id bigint NOT NULL,
    companyproject integer,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    "DocumentSolutionUniversal" integer,
    auditwarehouse integer,
    auditcounterparty integer,
    "CalendarPeriodMonth" integer,
    "proposedsolutions_DocumentSolutionUniversal" integer[],
    specialrequirement text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    eventtype smallint,
    dateprev date,
    datenext date,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer
);


ALTER TABLE "Document_Claim_R_QDА" OWNER TO bc;

--
-- Name: Document_Claim_R_RDC; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Claim_R_RDC" (
    id bigint NOT NULL,
    companyproject integer,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    "DocumentSolutionUniversal" integer,
    "DirectoryBusinessProcessItem" integer,
    scaleapplication integer,
    specialrequirement text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    descriptionneed text,
    regulatingdocument character varying(255) DEFAULT NULL::character varying,
    docname text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "solutionvariants_DocumentSolutionUniversal" integer[],
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    rtaken smallint
);


ALTER TABLE "Document_Claim_R_RDC" OWNER TO bc;

--
-- Name: Document_Claim_R_RDD; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Claim_R_RDD" (
    id bigint NOT NULL,
    companyproject integer,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    "DocumentSolutionUniversal" integer,
    purchaseuser integer,
    specialrequirement text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    descriptionneed text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "solutionvariants_DocumentSolutionUniversal" integer[],
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    "DocumentCopyControled" bigint,
    rtaken smallint
);


ALTER TABLE "Document_Claim_R_RDD" OWNER TO bc;

--
-- Name: Document_Claim_R_RDE; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Claim_R_RDE" (
    id bigint NOT NULL,
    companyproject integer,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    "DocumentSolutionUniversal" integer,
    specialrequirement text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    regulatingdocument character varying(255) DEFAULT NULL::character varying,
    reasonforchange text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "solutionvariants_DocumentSolutionUniversal" integer[],
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    rtaken smallint
);


ALTER TABLE "Document_Claim_R_RDE" OWNER TO bc;

--
-- Name: Document_Claim_R_TD; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Claim_R_TD" (
    id bigint NOT NULL,
    companyproject integer,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    "DocumentSolutionUniversal" integer,
    specialrequirement text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    goal text,
    purchasetype smallint,
    purchasename text,
    purchaseparam text,
    priority text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "solutionvariants_DocumentSolutionUniversal" integer[],
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    rtaken smallint
);


ALTER TABLE "Document_Claim_R_TD" OWNER TO bc;

--
-- Name: Document_Claim_R_UPC; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Claim_R_UPC" (
    id bigint NOT NULL,
    companyproject integer,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    "DocumentSolutionUniversal" integer,
    mailuser integer,
    "skduser_ManagementPostIndividual" integer[],
    specialrequirement text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    descriptionneed text,
    permissionsnew text,
    processtype smallint,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "solutionvariants_DocumentSolutionUniversal" integer[],
    permissionscurrent text,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    rtaken smallint
);


ALTER TABLE "Document_Claim_R_UPC" OWNER TO bc;

--
-- Name: Document_Claim_R_UPE; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Claim_R_UPE" (
    id bigint NOT NULL,
    companyproject integer,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    "DocumentSolutionUniversal" integer,
    mailusernew integer,
    mailuserold integer,
    specialrequirement text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    descriptionneed text,
    login character varying(255) DEFAULT NULL::character varying,
    signature text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "solutionvariants_DocumentSolutionUniversal" integer[],
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    rtaken smallint
);


ALTER TABLE "Document_Claim_R_UPE" OWNER TO bc;

--
-- Name: Document_Claim_R_UPI; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Claim_R_UPI" (
    id bigint NOT NULL,
    companyproject integer,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    "DocumentSolutionUniversal" integer,
    internetuser integer,
    specialrequirement text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    descriptionneed text,
    link text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "solutionvariants_DocumentSolutionUniversal" integer[],
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    rtaken smallint
);


ALTER TABLE "Document_Claim_R_UPI" OWNER TO bc;

--
-- Name: Document_Claim_R_UPK; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Claim_R_UPK" (
    id bigint NOT NULL,
    companyproject integer,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    "DocumentSolutionUniversal" integer,
    printuser integer,
    specialrequirement text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    descriptionneed text,
    printname text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "solutionvariants_DocumentSolutionUniversal" integer[],
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    rtaken smallint
);


ALTER TABLE "Document_Claim_R_UPK" OWNER TO bc;

--
-- Name: Document_Claim_R_UPL; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Claim_R_UPL" (
    id bigint NOT NULL,
    companyproject integer,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    "DocumentSolutionUniversal" integer,
    recipient integer,
    specialrequirement text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    descriptionneed text,
    materialquantity character varying(255) DEFAULT NULL::character varying,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "solutionvariants_DocumentSolutionUniversal" integer[],
    permissionscurrent text,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    rtaken smallint
);


ALTER TABLE "Document_Claim_R_UPL" OWNER TO bc;

--
-- Name: Document_Claim_R_UPP; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Claim_R_UPP" (
    id bigint NOT NULL,
    companyproject integer,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    "DocumentSolutionUniversal" integer,
    specialrequirement text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    descriptionneed text,
    permissionnewsdescription text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "solutionvariants_DocumentSolutionUniversal" integer[],
    permissionscurrent text,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    rtaken smallint
);


ALTER TABLE "Document_Claim_R_UPP" OWNER TO bc;

--
-- Name: Document_Complaint_C_IS; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Complaint_C_IS" (
    id bigint NOT NULL,
    fromclient smallint DEFAULT 0,
    stillactual smallint DEFAULT 0,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    object integer,
    datestart date,
    dateend date,
    description text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint
);


ALTER TABLE "Document_Complaint_C_IS" OWNER TO bc;

--
-- Name: Document_Complaint_C_IV; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Complaint_C_IV" (
    id bigint NOT NULL,
    fromclient smallint DEFAULT 0,
    stillactual smallint DEFAULT 0,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    object integer,
    datestart date,
    dateend date,
    description text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint
);


ALTER TABLE "Document_Complaint_C_IV" OWNER TO bc;

--
-- Name: Document_Complaint_C_IW; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Complaint_C_IW" (
    id bigint NOT NULL,
    fromclient smallint DEFAULT 0,
    stillactual smallint DEFAULT 0,
    client integer,
    warehouse integer,
    counterparty integer,
    "DocumentContractTME" integer,
    datestart date,
    dateend date,
    description text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint
);


ALTER TABLE "Document_Complaint_C_IW" OWNER TO bc;

--
-- Name: Document_Complaint_C_LB; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Complaint_C_LB" (
    id bigint NOT NULL,
    fromclient smallint DEFAULT 0,
    stillactual smallint DEFAULT 0,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    datestart date,
    dateend date,
    description text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    invoice character varying(255) DEFAULT NULL::character varying,
    invoicedate date,
    invoicesum numeric(14,2) DEFAULT NULL::numeric,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint
);


ALTER TABLE "Document_Complaint_C_LB" OWNER TO bc;

--
-- Name: Document_Complaint_C_LC; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Complaint_C_LC" (
    id bigint NOT NULL,
    fromclient smallint DEFAULT 0,
    stillactual smallint DEFAULT 0,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    datestart date,
    dateend date,
    description text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    productname character varying(255) DEFAULT NULL::character varying,
    seriesofproduct character varying(255) DEFAULT NULL::character varying,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint
);


ALTER TABLE "Document_Complaint_C_LC" OWNER TO bc;

--
-- Name: Document_Complaint_C_LP; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Complaint_C_LP" (
    id bigint NOT NULL,
    fromclient smallint DEFAULT 0,
    stillactual smallint DEFAULT 0,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    datestart date,
    dateend date,
    description text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    documentnumber character varying(255) DEFAULT NULL::character varying,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint
);


ALTER TABLE "Document_Complaint_C_LP" OWNER TO bc;

--
-- Name: Document_Complaint_C_LT; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Complaint_C_LT" (
    id bigint NOT NULL,
    fromclient smallint DEFAULT 0,
    stillactual smallint DEFAULT 0,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    datestart date,
    dateend date,
    description text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    transportdocument character varying(255) DEFAULT NULL::character varying,
    transportdate date,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint
);


ALTER TABLE "Document_Complaint_C_LT" OWNER TO bc;

--
-- Name: Document_ContractAgreement_SAE; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_ContractAgreement_SAE" (
    id bigint NOT NULL,
    "contractapplication_DocumentContractApplicationUniversal" integer[],
    text character varying(255) DEFAULT NULL::character varying,
    attachment character varying(255) DEFAULT NULL::character varying,
    contractlink character varying(255) DEFAULT NULL::character varying,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    date date,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint
);


ALTER TABLE "Document_ContractAgreement_SAE" OWNER TO bc;

--
-- Name: Document_ContractApplication_Universal; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_ContractApplication_Universal" (
    id bigint NOT NULL,
    text text DEFAULT NULL::character varying,
    contractlink character varying(255) DEFAULT NULL::character varying,
    "MediaAttributed_DirectoryMediaAttributed" integer[],
    "DMSDocumentUniversal" bigint,
    privatedraft boolean,
    state character varying(255) DEFAULT NULL::character varying,
    code character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[],
    related character varying(255)[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[],
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    ordered integer
);


ALTER TABLE "Document_ContractApplication_Universal" OWNER TO bc;

--
-- Name: Document_Contract_BW; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Contract_BW" (
    id bigint NOT NULL,
    timecontract integer,
    timenotifyfor integer,
    "DirectoryBusinessProjectsItem" integer,
    "CompanyLegalEntityCounterparty" integer,
    "BusinessObjectRecordPolymorph" integer,
    "CompanyStructureCompanygroup" integer,
    tenderdoc character varying(255),
    "contractapplication_DocumentContractApplicationUniversal" integer[],
    "notifyusercompany_ManagementPostIndividual" integer[],
    "notifyusercounterparty_PeopleEmployeeCounterparty" integer[],
    place character varying(255) DEFAULT NULL::character varying,
    date date,
    prolongation smallint,
    enddate date,
    summ numeric(14,2) DEFAULT NULL::numeric,
    justification text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    introduction text,
    contractsubject text,
    rightsandliabilities text,
    timeofworks text,
    termofcustompayments text,
    payments text,
    specialconditions text,
    otherconditions text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    notifyusercounterparty bigint,
    "DMSDocumentUniversal" bigint
);


ALTER TABLE "Document_Contract_BW" OWNER TO bc;

--
-- Name: Document_Contract_LC; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Contract_LC" (
    id bigint NOT NULL,
    timecontract integer,
    timenotifyfor integer,
    "DirectoryBusinessProjectsItem" integer,
    "CompanyLegalEntityCounterparty" integer,
    "BusinessObjectRecordPolymorph" integer,
    "CompanyStructureCompanygroup" integer,
    tenderdoc character varying(255),
    "contractapplication_DocumentContractApplicationUniversal" integer[],
    "notifyusercompany_ManagementPostIndividual" integer[],
    "notifyusercounterparty_PeopleEmployeeCounterparty" integer[],
    place character varying(255) DEFAULT NULL::character varying,
    date date,
    prolongation smallint,
    enddate date,
    summ numeric(14,2) DEFAULT NULL::numeric,
    justification text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    introduction text,
    contractsubject text,
    payments text,
    disputeresolutions text,
    final text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    rightsandliabilities text
);


ALTER TABLE "Document_Contract_LC" OWNER TO bc;

--
-- Name: Document_Contract_LOP; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Contract_LOP" (
    id bigint NOT NULL,
    timecontract integer,
    timenotifyfor integer,
    "DirectoryBusinessProjectsItem" integer,
    "CompanyLegalEntityCounterparty" integer,
    "BusinessObjectRecordPolymorph" integer,
    "CompanyStructureCompanygroup" integer,
    tenderdoc character varying(255),
    "contractapplication_DocumentContractApplicationUniversal" integer[],
    "notifyusercompany_ManagementPostIndividual" integer[],
    "notifyusercounterparty_PeopleEmployeeCounterparty" integer[],
    place character varying(255) DEFAULT NULL::character varying,
    date date,
    prolongation smallint,
    enddate date,
    summ numeric(14,2) DEFAULT NULL::numeric,
    justification text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    introduction text,
    objectforrent text,
    timeofrent text,
    priceandterms text,
    responsibilitiesoflandlord text,
    responsibilities text,
    termsofreturn text,
    liabilities text,
    disputesresolving text,
    forcemajeure text,
    contracttermination text,
    otherconditions text,
    appendix text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    contractsubject text
);


ALTER TABLE "Document_Contract_LOP" OWNER TO bc;

--
-- Name: Document_Contract_LWP; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Contract_LWP" (
    id bigint NOT NULL,
    timecontract integer,
    timenotifyfor integer,
    "DirectoryBusinessProjectsItem" integer,
    "CompanyLegalEntityCounterparty" integer,
    "BusinessObjectRecordPolymorph" integer,
    "CompanyStructureCompanygroup" integer,
    tenderdoc character varying(255),
    "contractapplication_DocumentContractApplicationUniversal" integer[],
    "notifyusercompany_ManagementPostIndividual" integer[],
    "notifyusercounterparty_PeopleEmployeeCounterparty" integer[],
    place character varying(255) DEFAULT NULL::character varying,
    date date,
    prolongation smallint,
    enddate date,
    summ numeric(14,2) DEFAULT NULL::numeric,
    justification text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    introduction text,
    definitions text,
    contractsubject text,
    warehouseconditions text,
    leabilities text,
    rights text,
    lenlordleabilities text,
    lenlordrights text,
    rentpayments text,
    partyliabilities text,
    contractterm text,
    specialconditions text,
    final text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint
);


ALTER TABLE "Document_Contract_LWP" OWNER TO bc;

--
-- Name: Document_Contract_MT; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Contract_MT" (
    id bigint NOT NULL,
    timecontract integer,
    timenotifyfor integer,
    "DirectoryBusinessProjectsItem" integer,
    "CompanyLegalEntityCounterparty" integer,
    "BusinessObjectRecordPolymorph" integer,
    "CompanyStructureCompanygroup" integer,
    tenderdoc character varying(255),
    "contractapplication_DocumentContractApplicationUniversal" integer[],
    "notifyusercompany_ManagementPostIndividual" integer[],
    "notifyusercounterparty_PeopleEmployeeCounterparty" integer[],
    place character varying(255) DEFAULT NULL::character varying,
    date date,
    prolongation smallint,
    enddate date,
    summ numeric(14,2) DEFAULT NULL::numeric,
    justification text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    introduction text,
    contractsubject text,
    qualityofgoods text,
    deliveryconditions text,
    goodstransfer text,
    termsofpayment text,
    termsofcontract text,
    liabilities text,
    final text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint
);


ALTER TABLE "Document_Contract_MT" OWNER TO bc;

--
-- Name: Document_Contract_RSS; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Contract_RSS" (
    id bigint NOT NULL,
    timecontract integer,
    timenotifyfor integer,
    "DirectoryBusinessProjectsItem" integer,
    "CompanyLegalEntityCounterparty" integer,
    "BusinessObjectRecordPolymorph" integer,
    "CompanyStructureCompanygroup" integer,
    tenderdoc character varying(255),
    "contractapplication_DocumentContractApplicationUniversal" integer[],
    "notifyusercompany_ManagementPostIndividual" integer[],
    "notifyusercounterparty_PeopleEmployeeCounterparty" integer[],
    place character varying(255) DEFAULT NULL::character varying,
    date date,
    prolongation smallint,
    enddate date,
    summ numeric(14,2) DEFAULT NULL::numeric,
    justification text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    introduction text,
    wordsdefinition text,
    subjectofcontract text,
    responsibilityofdoer text,
    responsibility text,
    priceandterm text,
    insurance text,
    accounting text,
    trademarks text,
    confidentiality text,
    timeofcontract text,
    forcemajeure text,
    refuce text,
    fullcontract text,
    language text,
    jurisdiction text,
    otherconditions text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint
);


ALTER TABLE "Document_Contract_RSS" OWNER TO bc;

--
-- Name: Document_Contract_SS; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Contract_SS" (
    id bigint NOT NULL,
    timecontract integer,
    timenotifyfor integer,
    "DirectoryBusinessProjectsItem" integer,
    "CompanyLegalEntityCounterparty" integer,
    "BusinessObjectRecordPolymorph" integer,
    "CompanyStructureCompanygroup" integer,
    tenderdoc character varying(255),
    "contractapplication_DocumentContractApplicationUniversal" integer[],
    "notifyusercompany_ManagementPostIndividual" integer[],
    "notifyusercounterparty_PeopleEmployeeCounterparty" integer[],
    place character varying(255) DEFAULT NULL::character varying,
    date date,
    prolongation smallint,
    enddate date,
    summ numeric(14,2) DEFAULT NULL::numeric,
    justification text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    introduction text,
    contractsubject text,
    price text,
    payments text,
    termofworks text,
    maintanance text,
    worksdoing text,
    guarantees text,
    executedworks text,
    partiesliabilities text,
    changes text,
    timeofcontract text,
    forcemajeure text,
    otherconditions text,
    appendix text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint
);


ALTER TABLE "Document_Contract_SS" OWNER TO bc;

--
-- Name: Document_Contract_TMC; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Contract_TMC" (
    id bigint NOT NULL,
    timecontract integer,
    timenotifyfor integer,
    "DirectoryBusinessProjectsItem" integer,
    "CompanyLegalEntityCounterparty" integer,
    "BusinessObjectRecordPolymorph" integer,
    "CompanyStructureCompanygroup" integer,
    tenderdoc character varying(255),
    "contractapplication_DocumentContractApplicationUniversal" integer[],
    "notifyusercompany_ManagementPostIndividual" integer[],
    "notifyusercounterparty_PeopleEmployeeCounterparty" integer[],
    place character varying(255) DEFAULT NULL::character varying,
    date date,
    prolongation smallint,
    enddate date,
    summ numeric(14,2) DEFAULT NULL::numeric,
    justification text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    introduction text,
    contractsubject text,
    orderofworksexecution text,
    rights text,
    termsofpayment text,
    liabilities text,
    changesofcontracts text,
    specialconditions text,
    termsofcontract text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint
);


ALTER TABLE "Document_Contract_TMC" OWNER TO bc;

--
-- Name: Document_Contract_TME; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Contract_TME" (
    id bigint NOT NULL,
    timecontract integer,
    timenotifyfor integer,
    "DirectoryBusinessProjectsItem" integer,
    "CompanyLegalEntityCounterparty" integer,
    "BusinessObjectRecordPolymorph" integer,
    "CompanyStructureCompanygroup" integer,
    tenderdoc character varying(255),
    "contractapplication_DocumentContractApplicationUniversal" integer[],
    "notifyusercompany_ManagementPostIndividual" integer[],
    "notifyusercounterparty_PeopleEmployeeCounterparty" integer[],
    place character varying(255) DEFAULT NULL::character varying,
    date date,
    prolongation smallint,
    enddate date,
    summ numeric(14,2) DEFAULT NULL::numeric,
    justification text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    introduction text,
    contractsubject text,
    orderofworksexecution text,
    costofworks text,
    partyliabilities text,
    responsibilityofpartie text,
    timeofcontracts text,
    final text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    notifyusercounterparty bigint,
    "DMSDocumentUniversal" bigint
);


ALTER TABLE "Document_Contract_TME" OWNER TO bc;

--
-- Name: Document_Copy_Controled; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Copy_Controled" (
    id bigint NOT NULL,
    isactive smallint DEFAULT 0,
    "DocumentRegulationsSOP" integer,
    master integer,
    "holders_PeopleEmployeeInternal" integer[],
    "previous_PeopleEmployeeInternal" integer[],
    created integer,
    dateissue integer,
    datereturn integer
);


ALTER TABLE "Document_Copy_Controled" OWNER TO bc;

--
-- Name: Document_Copy_Realnoncontrolcopy; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Copy_Realnoncontrolcopy" (
    id bigint NOT NULL,
    isactive smallint DEFAULT 0,
    isreturn smallint DEFAULT 0,
    realcopy integer,
    "holders_PeopleEmployeeInternal" integer[],
    "previous_PeopleEmployeeInternal" integer[],
    created integer,
    master bigint,
    dateissue integer
);


ALTER TABLE "Document_Copy_Realnoncontrolcopy" OWNER TO bc;

--
-- Name: Document_Correction_Capa; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Correction_Capa" (
    id bigint NOT NULL,
    confirmed smallint DEFAULT 0,
    taskcompleted smallint DEFAULT 0,
    "DocumentCapaDeviation" integer,
    "CompanyStructureDepartment" integer,
    comment text,
    created integer,
    updated integer,
    eventplace integer,
    controlresponsible integer,
    selectedsolution integer,
    descriptioncorrection text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[],
    related character varying(255)[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    realizationtype smallint,
    selecttype smallint DEFAULT 0,
    selectsolution smallint DEFAULT 0,
    ordered integer,
    "DirectorySolutionvariantsSimple" bigint
);


ALTER TABLE "Document_Correction_Capa" OWNER TO bc;

--
-- Name: Document_Detective_C_IS; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Detective_C_IS" (
    id bigint NOT NULL,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    responsible integer,
    "commissionmember_ManagementPostIndividual" integer[],
    "checkbo_BusinessObjectRecordPolymorph" integer[],
    datestart date,
    dateend date,
    actual smallint,
    description text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    troublefix smallint,
    troublefixdate date,
    troubleevent text,
    investigationdate date,
    factdetected text,
    complaintstatus smallint,
    materialsused text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "DocumentComplaintC_IS" bigint,
    "internaldocuments_DMSDocumentUniversal" integer[],
    conclusion text,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    "deviations_DirectoryDeviationPreCapa" integer[],
    "riskapproved_RiskManagementRiskApproved" integer[],
    "risknotapproved_RiskManagementRiskNotApproved" integer[]
);


ALTER TABLE "Document_Detective_C_IS" OWNER TO bc;

--
-- Name: Document_Detective_C_IV; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Detective_C_IV" (
    id bigint NOT NULL,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    responsible integer,
    "commissionmember_ManagementPostIndividual" integer[],
    "checkbo_BusinessObjectRecordPolymorph" integer[],
    datestart date,
    dateend date,
    actual smallint,
    description text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    troublefix smallint,
    troublefixdate date,
    troubleevent text,
    investigationdate date,
    factdetected text,
    complaintstatus smallint,
    materialsused text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "DocumentComplaintC_IV" bigint,
    "internaldocuments_DMSDocumentUniversal" integer[],
    conclusion text,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    "deviations_DirectoryDeviationPreCapa" integer[],
    "riskapproved_RiskManagementRiskApproved" integer[],
    "risknotapproved_RiskManagementRiskNotApproved" integer[]
);


ALTER TABLE "Document_Detective_C_IV" OWNER TO bc;

--
-- Name: Document_Detective_C_IW; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Detective_C_IW" (
    id bigint NOT NULL,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    responsible integer,
    "commissionmember_ManagementPostIndividual" integer[],
    "checkbo_BusinessObjectRecordPolymorph" integer[],
    datestart date,
    dateend date,
    actual smallint,
    description text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    troublefix smallint,
    troublefixdate date,
    troubleevent text,
    investigationdate date,
    factdetected text,
    complaintstatus smallint,
    materialsused text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "DocumentComplaintC_IW" bigint,
    "internaldocuments_DMSDocumentUniversal" integer[],
    conclusion text,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    "deviations_DirectoryDeviationPreCapa" integer[],
    "riskapproved_RiskManagementRiskApproved" integer[],
    "risknotapproved_RiskManagementRiskNotApproved" integer[]
);


ALTER TABLE "Document_Detective_C_IW" OWNER TO bc;

--
-- Name: Document_Detective_C_LB; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Detective_C_LB" (
    id bigint NOT NULL,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    responsible integer,
    "commissionmember_ManagementPostIndividual" integer[],
    "checkbo_BusinessObjectRecordPolymorph" integer[],
    datestart date,
    dateend date,
    actual smallint,
    description text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    troublefix smallint,
    troublefixdate date,
    troubleevent text,
    investigationdate date,
    factdetected text,
    complaintstatus smallint,
    materialsused text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "DocumentComplaintC_LB" bigint,
    "internaldocuments_DMSDocumentUniversal" integer[],
    conclusion text,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    "deviations_DirectoryDeviationPreCapa" integer[],
    "riskapproved_RiskManagementRiskApproved" integer[],
    "risknotapproved_RiskManagementRiskNotApproved" integer[]
);


ALTER TABLE "Document_Detective_C_LB" OWNER TO bc;

--
-- Name: Document_Detective_C_LC; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Detective_C_LC" (
    id bigint NOT NULL,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    responsible integer,
    "commissionmember_ManagementPostIndividual" integer[],
    "checkbo_BusinessObjectRecordPolymorph" integer[],
    datestart date,
    dateend date,
    actual smallint,
    description text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    troublefix smallint,
    troublefixdate date,
    troubleevent text,
    investigationdate date,
    factdetected text,
    complaintstatus smallint,
    materialsused text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "DocumentComplaintC_LC" bigint,
    "internaldocuments_DMSDocumentUniversal" integer[],
    conclusion text,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    "deviations_DirectoryDeviationPreCapa" integer[],
    "riskapproved_RiskManagementRiskApproved" integer[],
    "risknotapproved_RiskManagementRiskNotApproved" integer[]
);


ALTER TABLE "Document_Detective_C_LC" OWNER TO bc;

--
-- Name: Document_Detective_C_LP; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Detective_C_LP" (
    id bigint NOT NULL,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    responsible integer,
    "commissionmember_ManagementPostIndividual" integer[],
    "checkbo_BusinessObjectRecordPolymorph" integer[],
    datestart date,
    dateend date,
    actual smallint,
    description text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    troublefix smallint,
    troublefixdate date,
    troubleevent text,
    investigationdate date,
    factdetected text,
    complaintstatus smallint,
    materialsused text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "DocumentComplaintC_LP" bigint,
    "internaldocuments_DMSDocumentUniversal" integer[],
    conclusion text,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    "deviations_DirectoryDeviationPreCapa" integer[],
    "riskapproved_RiskManagementRiskApproved" integer[],
    "risknotapproved_RiskManagementRiskNotApproved" integer[]
);


ALTER TABLE "Document_Detective_C_LP" OWNER TO bc;

--
-- Name: Document_Detective_C_LT; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Detective_C_LT" (
    id bigint NOT NULL,
    "CompanyLegalEntityCounterparty" integer,
    warehouse integer,
    responsible integer,
    "commissionmember_ManagementPostIndividual" integer[],
    "checkbo_BusinessObjectRecordPolymorph" integer[],
    datestart date,
    dateend date,
    actual smallint,
    description text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    troublefix smallint,
    troublefixdate date,
    troubleevent text,
    investigationdate date,
    factdetected text,
    complaintstatus smallint,
    materialsused text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "DocumentComplaintC_LT" bigint,
    "internaldocuments_DMSDocumentUniversal" integer[],
    conclusion text,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    "deviations_DirectoryDeviationPreCapa" integer[],
    "riskapproved_RiskManagementRiskApproved" integer[],
    "risknotapproved_RiskManagementRiskNotApproved" integer[]
);


ALTER TABLE "Document_Detective_C_LT" OWNER TO bc;

--
-- Name: Document_Protocol_CT; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Protocol_CT" (
    id bigint NOT NULL,
    bo bigint,
    warehouse bigint,
    client bigint,
    "CalendarPeriodMonth" bigint,
    date date,
    contractforcalibration character varying(255) DEFAULT NULL::character varying,
    relateddocuments character varying(255)[],
    results text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[],
    related character varying(255)[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    upload character varying(255)[],
    "ResponsibleCalibration" bigint,
    datep date
);


ALTER TABLE "Document_Protocol_CT" OWNER TO bc;

--
-- Name: Document_Protocol_EA; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Protocol_EA" (
    id bigint NOT NULL,
    commisionhead integer,
    date date,
    results text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "commisionmembers_ManagementPostIndividual" integer[],
    attachments character varying(255)[],
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    "boprocedure_BusinessObjectRecordPolymorph" integer[],
    datep date
);


ALTER TABLE "Document_Protocol_EA" OWNER TO bc;

--
-- Name: Document_Protocol_EC; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Protocol_EC" (
    id bigint NOT NULL,
    commisionhead integer,
    date date,
    results text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "commisionmembers_ManagementPostIndividual" integer[],
    attachments character varying(255)[],
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    datep date
);


ALTER TABLE "Document_Protocol_EC" OWNER TO bc;

--
-- Name: Document_Protocol_KI; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Protocol_KI" (
    id bigint NOT NULL,
    "BusinessObjectRecordPolymorph" integer,
    date date,
    placetime integer,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    "DocumentCapaDeviation" bigint
);


ALTER TABLE "Document_Protocol_KI" OWNER TO bc;

--
-- Name: Document_Protocol_MT; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Protocol_MT" (
    id bigint NOT NULL,
    bo integer,
    warehouse integer,
    client integer,
    "CalendarPeriodMonth" integer,
    date date,
    relateddocuments character varying(255)[] DEFAULT NULL::character varying[],
    results text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    contractforverif character varying(255) DEFAULT NULL::character varying,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    upload character varying(255)[],
    "ResponsibleVerification" bigint,
    datep date
);


ALTER TABLE "Document_Protocol_MT" OWNER TO bc;

--
-- Name: Document_Protocol_RR; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Protocol_RR" (
    id bigint NOT NULL,
    "BusinessObjectRecordPolymorph" bigint,
    "DirectoryBusinessProcessItem" bigint,
    "DMSDocumentUniversal" bigint,
    "riskapproved_RiskManagementRiskApproved" integer[],
    date date,
    privatedraft boolean,
    state character varying(255) DEFAULT NULL::character varying,
    code character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[] DEFAULT NULL::character varying[],
    additionalvisants character varying(255)[] DEFAULT NULL::character varying[],
    "commissionmember_ManagementPostIndividual" integer[]
);


ALTER TABLE "Document_Protocol_RR" OWNER TO bc;

--
-- Name: Document_Protocol_SI; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Protocol_SI" (
    id bigint NOT NULL,
    "BusinessObjectRecordPolymorph" integer,
    date date,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    "riskapproved_RiskManagementRiskApproved" integer[]
);


ALTER TABLE "Document_Protocol_SI" OWNER TO bc;

--
-- Name: Document_Protocol_TM; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Protocol_TM" (
    id bigint NOT NULL,
    bo integer,
    responsiblemo integer,
    responsibleto integer,
    warehouse integer,
    client integer,
    "CalendarPeriodMonth" integer,
    servicedate date,
    relateddocuments character varying(255)[] DEFAULT NULL::character varying[],
    results text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    contractforcalibration character varying(255) DEFAULT NULL::character varying,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    upload character varying(255)[],
    datep date
);


ALTER TABLE "Document_Protocol_TM" OWNER TO bc;

--
-- Name: Document_Protocol_VT; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Protocol_VT" (
    id bigint NOT NULL,
    bo integer,
    warehouse integer,
    client integer,
    "CalendarPeriodMonth" integer,
    scaleapplication integer,
    "equipment_BusinessObjectRecordPolymorph" integer[],
    normativebase text,
    consecutivenumber integer,
    latestcheck date,
    nextcheck date,
    chemicals text,
    defabbr text,
    masterpart text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    finalrecommend text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    currentcheck date,
    industryscope text,
    "MateriallyResponsible" bigint,
    "ResponsibleMaintenance" bigint,
    "ResponsibleValidation" bigint,
    "ManagementPostIndividual_DirectoryResponsibletwoSimple" integer[],
    serialnumber character varying(255) DEFAULT NULL::character varying,
    worktype character varying(255) DEFAULT NULL::character varying,
    numberequipment character varying(255) DEFAULT NULL::character varying,
    specification text
);


ALTER TABLE "Document_Protocol_VT" OWNER TO bc;

--
-- Name: Document_Protocol_СТ; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Protocol_СТ" (
    id bigint NOT NULL,
    bo integer,
    responsible integer,
    warehouse integer,
    client integer,
    "CalendarPeriodMonth" integer,
    date date,
    "сontractforcalibration" character varying(255) DEFAULT NULL::character varying,
    relateddocuments character varying(255)[] DEFAULT NULL::character varying[],
    results text,
    upload character varying(255) DEFAULT NULL::character varying,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer
);


ALTER TABLE "Document_Protocol_СТ" OWNER TO bc;

--
-- Name: Document_Regulations_AO; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Regulations_AO" (
    id bigint NOT NULL,
    title character varying(255) DEFAULT NULL::character varying,
    preamble text,
    textorder text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    scaleapplication bigint,
    "userprocedure_ManagementPostIndividual" integer[],
    target text,
    effectivedate date
);


ALTER TABLE "Document_Regulations_AO" OWNER TO bc;

--
-- Name: Document_Regulations_ASR; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Regulations_ASR" (
    id bigint NOT NULL,
    "DocumentRegulationsSOP" bigint,
    "DocumentRegulationsTA" bigint,
    "DMSDocumentUniversal" bigint,
    planneddate date,
    realeventdate date,
    privatedraft boolean,
    state character varying(255) DEFAULT NULL::character varying,
    code character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[] DEFAULT NULL::character varying[],
    additionalvisants character varying(255)[] DEFAULT NULL::character varying[],
    plannedattendees character varying(255)[] DEFAULT NULL::character varying[],
    notpassed character varying(255)[] DEFAULT NULL::character varying[],
    successpassed character varying(255)[] DEFAULT NULL::character varying[],
    failedpassed character varying(255)[] DEFAULT NULL::character varying[]
);


ALTER TABLE "Document_Regulations_ASR" OWNER TO bc;

--
-- Name: Document_Regulations_I; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Regulations_I" (
    id bigint NOT NULL,
    "DirectoryBusinessProcessItem" integer,
    scaleapplication integer,
    "CalendarPeriodMonth" integer,
    "boprocedure_BusinessObjectRecordPolymorph" integer[],
    "userprocedure_ManagementPostIndividual" integer[],
    title character varying(255) DEFAULT NULL::character varying,
    fileprocessattachment character varying(255) DEFAULT NULL::character varying,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    causeedit text,
    effectivedate date,
    enddate date,
    target text,
    realmuse text,
    response text,
    resource text,
    procedure text,
    extrachapter text,
    report text,
    docforlink text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint
);


ALTER TABLE "Document_Regulations_I" OWNER TO bc;

--
-- Name: Document_Regulations_JD; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Regulations_JD" (
    id bigint NOT NULL,
    instructionname character varying(255) DEFAULT NULL::character varying,
    "position" text,
    duty text,
    authority text,
    responsibility text,
    conditions text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    "userprocedure_ManagementPostIndividual" integer[],
    effectivedate date
);


ALTER TABLE "Document_Regulations_JD" OWNER TO bc;

--
-- Name: Document_Regulations_MP; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Regulations_MP" (
    id bigint NOT NULL,
    "CalendarPeriodMonth" integer,
    initialdate date,
    lastdate date,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    policy text
);


ALTER TABLE "Document_Regulations_MP" OWNER TO bc;

--
-- Name: Document_Regulations_P; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Regulations_P" (
    id bigint NOT NULL,
    "DirectoryBusinessProcessItem" integer,
    scaleapplication integer,
    "CalendarPeriodMonth" integer,
    "boprocedure_BusinessObjectRecordPolymorph" integer[],
    "userprocedure_ManagementPostIndividual" integer[],
    title character varying(255) DEFAULT NULL::character varying,
    fileprocessattachment character varying(255) DEFAULT NULL::character varying,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    causeedit text,
    effectivedate date,
    enddate date,
    target text,
    realmuse text,
    response text,
    resource text,
    procedure text,
    extrachapter text,
    report text,
    docforlink text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint
);


ALTER TABLE "Document_Regulations_P" OWNER TO bc;

--
-- Name: Document_Regulations_PV; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Regulations_PV" (
    id bigint NOT NULL,
    "BusinessObjectRecordPolymorph" integer,
    programm text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    title character varying(255) DEFAULT NULL::character varying
);


ALTER TABLE "Document_Regulations_PV" OWNER TO bc;

--
-- Name: Document_Regulations_SOP; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Regulations_SOP" (
    id bigint NOT NULL,
    "DirectoryBusinessProcessItem" integer,
    scaleapplication integer,
    "CalendarPeriodMonth" integer,
    "boprocedure_BusinessObjectRecordPolymorph" integer[],
    "userprocedure_ManagementPostIndividual" integer[],
    title character varying(255) DEFAULT NULL::character varying,
    trainingdocument smallint,
    fileprocessattachment character varying(255) DEFAULT NULL::character varying,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    causeedit text,
    effectivedate date,
    enddate date,
    target text,
    realmuse text,
    response text,
    resource text,
    procedure text,
    extrachapter text,
    report text,
    docforlink text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "ManagementPostIndividual" bigint,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "userproceduregroup_ManagementPostGroup" integer[],
    "DMSDocumentUniversal" bigint
);


ALTER TABLE "Document_Regulations_SOP" OWNER TO bc;

--
-- Name: Document_Regulations_TA; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Regulations_TA" (
    id bigint NOT NULL,
    trainer integer,
    "CalendarPeriodMonth" integer,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    moreinfo text,
    questions text,
    number text,
    questiondescription text,
    questiondescrip text,
    percentage character varying(255) DEFAULT NULL::character varying,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "time" integer,
    percent integer,
    statementoftopics text,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DocumentRegulationsSOP" bigint,
    "DMSDocumentUniversal" bigint,
    "interval" integer
);


ALTER TABLE "Document_Regulations_TA" OWNER TO bc;

--
-- Name: Document_Risk_Approved; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Risk_Approved" (
    id bigint NOT NULL,
    critical smallint DEFAULT 0,
    "BusinessObjectRecordPolymorph" integer,
    "DirectoryBusinessProcessItem" integer,
    "DirectorySLAItem" integer,
    "ManagementPostIndividual" integer,
    controlperiod integer,
    riskapproved text DEFAULT NULL::character varying,
    producteffect integer,
    emergenceprobability integer,
    undetectedprobability integer,
    weighted integer,
    controlact text,
    "DMSDocumentUniversal" bigint,
    privatedraft boolean,
    state character varying(255) DEFAULT NULL::character varying,
    code character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[],
    related character varying(255)[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[],
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[]
);


ALTER TABLE "Document_Risk_Approved" OWNER TO bc;

--
-- Name: Document_Risk_NotApproved; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Risk_NotApproved" (
    id bigint NOT NULL,
    identified smallint DEFAULT 0,
    "DocumentRiskApproved" integer,
    bo integer,
    process character varying(255),
    risknotapproved text,
    documentoforigin character varying(255) DEFAULT NULL::character varying,
    bprocess bigint,
    "DMSDocumentUniversal" bigint,
    privatedraft boolean,
    state character varying(255) DEFAULT NULL::character varying,
    code character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[],
    related character varying(255)[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[],
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[]
);


ALTER TABLE "Document_Risk_NotApproved" OWNER TO bc;

--
-- Name: Document_Solution_Correction; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Solution_Correction" (
    id bigint NOT NULL,
    "DocumentCorrectionCapa" integer,
    realizationdate date,
    cost numeric(14,2) DEFAULT NULL::numeric,
    created integer,
    updated integer,
    executor integer,
    realizationtype smallint,
    descriptionsolution text,
    "visauser_ManagementPostIndividual" integer[],
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[],
    related character varying(255)[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    approveded smallint DEFAULT 0,
    "DMSDocumentUniversal" bigint,
    ready smallint DEFAULT 0,
    approved boolean,
    ordered integer,
    matches smallint,
    comment text
);


ALTER TABLE "Document_Solution_Correction" OWNER TO bc;

--
-- Name: Document_Solution_Universal; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Solution_Universal" (
    id bigint NOT NULL,
    executor integer,
    realizationtype smallint,
    realizationdate date,
    cost numeric(14,2) DEFAULT NULL::numeric,
    description text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "visedby_ManagementPostIndividual" integer[],
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    ordered integer
);


ALTER TABLE "Document_Solution_Universal" OWNER TO bc;

--
-- Name: Document_Staffdoc_OF; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Staffdoc_OF" (
    id bigint NOT NULL,
    "CompanyStructureDepartment" integer,
    manager integer,
    reason text,
    dateofdismissal date,
    severancepay character varying(255) DEFAULT NULL::character varying,
    dateunusedvacation character varying(255) DEFAULT NULL::character varying,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    base character varying(255) DEFAULT NULL::character varying,
    "DMSDocumentUniversal" bigint,
    "ManagementPostIndividual" bigint
);


ALTER TABLE "Document_Staffdoc_OF" OWNER TO bc;

--
-- Name: Document_Staffdoc_OR; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Staffdoc_OR" (
    id bigint NOT NULL,
    "CompanyStructureDepartment" integer,
    "ManagementPostGroup" integer,
    manager integer,
    employeename character varying(255) DEFAULT NULL::character varying,
    date date,
    dateend date,
    dateterm character varying(255) DEFAULT NULL::character varying,
    actual smallint,
    long character varying(255) DEFAULT NULL::character varying,
    salary character varying(255) DEFAULT NULL::character varying,
    jobtype smallint,
    moremoney character varying(255) DEFAULT NULL::character varying,
    evenmoremoney character varying(255) DEFAULT NULL::character varying,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint
);


ALTER TABLE "Document_Staffdoc_OR" OWNER TO bc;

--
-- Name: Document_Staffdoc_SD; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Staffdoc_SD" (
    id bigint NOT NULL,
    employee integer,
    addressed text,
    masterpart text,
    createdate date,
    date date,
    based smallint,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint
);


ALTER TABLE "Document_Staffdoc_SD" OWNER TO bc;

--
-- Name: Document_Staffdoc_SV; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Staffdoc_SV" (
    id bigint NOT NULL,
    employee integer,
    addressed text,
    masterpart text,
    createdate date,
    date date,
    datestart date,
    dateend date,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint
);


ALTER TABLE "Document_Staffdoc_SV" OWNER TO bc;

--
-- Name: Document_TechnicalTask_ForMaterials; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_TechnicalTask_ForMaterials" (
    id bigint NOT NULL,
    "CompanyStructureCompanygroup" integer,
    type character varying(255) DEFAULT NULL::character varying,
    docpermitsneed text,
    supplierauditneeded smallint,
    deliveryconditions text,
    priority text,
    "desc" text,
    requirement text,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "DirectoryBranchItem" integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "CompanyLegalEntityCounterparty_CompanyLegalEntityCounterparty" integer[],
    personreceive bigint,
    contactperson bigint,
    "DirectoryTechnicalTaskMaterials_DirectoryTechnicalTaskMaterials" integer[],
    "DMSDocumentUniversal" bigint
);


ALTER TABLE "Document_TechnicalTask_ForMaterials" OWNER TO bc;

--
-- Name: Document_TechnicalTask_ForWorks; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_TechnicalTask_ForWorks" (
    id bigint NOT NULL,
    "CompanyStructureCompanygroup" integer,
    workstype character varying(255) DEFAULT NULL::character varying,
    docpermitsneed text,
    supplauditneeded smallint,
    contactperson text,
    projdocchangesneeded smallint,
    projectdocsmusthave text,
    sevicedesription text,
    volume text,
    workinside text,
    priorities text,
    "desc" text,
    requirements text,
    sectionsdesc text,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    "DirectoryBranchItem" integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "CompanyLegalEntityCounterparty_CompanyLegalEntityCounterparty" integer[],
    personreceive bigint,
    projectdocneed text,
    deliveryconditions text,
    "DMSDocumentUniversal" bigint,
    attachments character varying(255)[]
);


ALTER TABLE "Document_TechnicalTask_ForWorks" OWNER TO bc;

--
-- Name: Document_Tender_Extended; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Tender_Extended" (
    id bigint NOT NULL,
    title character varying(255) DEFAULT NULL::character varying,
    attachment character varying(255) DEFAULT NULL::character varying,
    attachments character varying(255)[] DEFAULT NULL::character varying[],
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[],
    related character varying(255)[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    docpermitsneed text,
    currency smallint
);


ALTER TABLE "Document_Tender_Extended" OWNER TO bc;

--
-- Name: Document_Tender_Table; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Tender_Table" (
    id bigint NOT NULL,
    currency smallint,
    titleposition character varying(255) DEFAULT NULL::character varying,
    priceoffer character varying(255) DEFAULT NULL::character varying,
    code character varying(255) DEFAULT NULL::character varying,
    state character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[],
    related character varying(255)[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[],
    privatedraft boolean,
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[],
    additionalvisants character varying(255)[],
    "DMSDocumentUniversal" bigint,
    "DirectoryTenderBidderSimple" bigint,
    priceofferarray character varying(255)[] DEFAULT NULL::character varying[]
);


ALTER TABLE "Document_Tender_Table" OWNER TO bc;

--
-- Name: Document_Tender_TableAdditional; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Tender_TableAdditional" (
    id bigint NOT NULL,
    "DMSDocumentUniversal" bigint,
    "DirectoryTenderBidderSimple" bigint,
    titleposition character varying(255) DEFAULT NULL::character varying,
    priceoffer character varying(255) DEFAULT NULL::character varying,
    priceofferarray character varying(255)[] DEFAULT NULL::character varying[],
    privatedraft boolean,
    state character varying(255) DEFAULT NULL::character varying,
    code character varying(255) DEFAULT NULL::character varying,
    process character varying(255) DEFAULT NULL::character varying,
    parent character varying(255) DEFAULT NULL::character varying,
    children character varying(255)[] DEFAULT NULL::character varying[],
    related character varying(255)[] DEFAULT NULL::character varying[],
    initiator character varying(255) DEFAULT NULL::character varying,
    authors character varying(255)[] DEFAULT NULL::character varying[],
    returned boolean,
    done boolean,
    archived boolean,
    vised boolean,
    approved boolean,
    created integer,
    updated integer,
    basevisants character varying(255)[] DEFAULT NULL::character varying[],
    additionalvisants character varying(255)[] DEFAULT NULL::character varying[]
);


ALTER TABLE "Document_Tender_TableAdditional" OWNER TO bc;

--
-- Name: Document_Viewaccess_ByProcedure; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Document_Viewaccess_ByProcedure" (
    id bigint NOT NULL,
    isactive smallint DEFAULT 0,
    isreturn smallint DEFAULT 0,
    "holder_PeopleEmployeeInternal" integer[],
    dateissue integer,
    datereturn integer,
    master bigint
);


ALTER TABLE "Document_Viewaccess_ByProcedure" OWNER TO bc;

--
-- Name: Event_ProcessExecutionPlanned_Staged; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Event_ProcessExecutionPlanned_Staged" (
    id bigint NOT NULL,
    isdateset smallint DEFAULT 0,
    ismpestarted smallint DEFAULT 0,
    planningresponsible bigint,
    "ManagedProcessExecutionRecord" bigint,
    "participants_ManagementPostIndividual" integer[],
    eventyear integer,
    eventmonth integer,
    eventdate date,
    subject character varying(255) DEFAULT NULL::character varying,
    processproto character varying(255) DEFAULT NULL::character varying,
    subjectproto character varying(255) DEFAULT NULL::character varying,
    created integer
);


ALTER TABLE "Event_ProcessExecutionPlanned_Staged" OWNER TO bc;

--
-- Name: Feed_Inbox_Document; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Feed_Inbox_Document" (
    id bigint NOT NULL,
    isprocessed smallint DEFAULT 0,
    "PeopleEmployeeInternal" integer,
    urn character varying(255) DEFAULT NULL::character varying,
    created integer
);


ALTER TABLE "Feed_Inbox_Document" OWNER TO bc;

--
-- Name: Feed_MPETicket_InboxItem; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Feed_MPETicket_InboxItem" (
    id bigint NOT NULL,
    isvalid smallint DEFAULT 0,
    allowopen smallint DEFAULT 0,
    allowsave smallint DEFAULT 0,
    allowcomplete smallint DEFAULT 0,
    allowcomment smallint DEFAULT 0,
    allowreadcomments smallint DEFAULT 0,
    allowknowcuurentstage smallint DEFAULT 0,
    allowseejournal smallint DEFAULT 0,
    allowearly smallint DEFAULT 0,
    "ManagementPostIndividual" bigint,
    "ManagedProcessExecutionRecord" bigint,
    activateat timestamp with time zone,
    expiresat timestamp with time zone,
    created integer
);


ALTER TABLE "Feed_MPETicket_InboxItem" OWNER TO bc;

--
-- Name: HTTP_Redirect_FromURIToURI; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "HTTP_Redirect_FromURIToURI" (
    id bigint NOT NULL,
    uri character varying(255) DEFAULT NULL::character varying,
    target character varying(255) DEFAULT NULL::character varying
);


ALTER TABLE "HTTP_Redirect_FromURIToURI" OWNER TO bc;

--
-- Name: Mail_Template_HTML; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Mail_Template_HTML" (
    id bigint NOT NULL,
    translated_ru integer,
    translated_en integer,
    title character varying(255) DEFAULT NULL::character varying,
    title_en character varying(255) DEFAULT NULL::character varying,
    fromname character varying(255) DEFAULT NULL::character varying,
    fromname_en character varying(255) DEFAULT NULL::character varying,
    headerhtml text,
    headerhtml_en text,
    contenthtml text,
    contenthtml_en text,
    footerhtml text,
    footerhtml_en text,
    specialhtml text,
    specialhtml_en text,
    uri character varying(255) DEFAULT NULL::character varying,
    fromemail character varying(255) DEFAULT NULL::character varying
);


ALTER TABLE "Mail_Template_HTML" OWNER TO bc;

--
-- Name: Mail_Template_Plain; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Mail_Template_Plain" (
    id bigint NOT NULL,
    translated_ru integer,
    translated_en integer,
    title character varying(255) DEFAULT NULL::character varying,
    title_en character varying(255) DEFAULT NULL::character varying,
    fromname character varying(255) DEFAULT NULL::character varying,
    fromname_en character varying(255) DEFAULT NULL::character varying,
    headerplain text,
    headerplain_en text,
    contentplain text,
    contentplain_en text,
    footerplain text,
    footerplain_en text,
    specialplain text,
    specialplain_en text,
    uri character varying(255) DEFAULT NULL::character varying,
    layout character varying(255) DEFAULT NULL::character varying,
    fromemail character varying(255) DEFAULT NULL::character varying
);


ALTER TABLE "Mail_Template_Plain" OWNER TO bc;

--
-- Name: ManagedProcess_Execution_Record; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "ManagedProcess_Execution_Record" (
    id bigint NOT NULL,
    initiator character varying(255) DEFAULT NULL::character varying,
    prototype character varying(255) DEFAULT NULL::character varying,
    returntopme character varying(255) DEFAULT NULL::character varying,
    subject character varying(255) DEFAULT NULL::character varying,
    metadata json,
    done boolean,
    nextstage character varying(255) DEFAULT NULL::character varying,
    nextactor character varying(255) DEFAULT NULL::character varying,
    currentstage character varying(255) DEFAULT NULL::character varying,
    currentactor character varying(255) DEFAULT NULL::character varying,
    created timestamp without time zone
);


ALTER TABLE "ManagedProcess_Execution_Record" OWNER TO bc;

--
-- Name: ManagedProcess_Journal_Record; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "ManagedProcess_Journal_Record" (
    id bigint NOT NULL,
    "ManagedProcessExecutionRecord" integer,
    stagedirection smallint,
    operationtime timestamp without time zone,
    stage character varying(255) DEFAULT NULL::character varying,
    actor character varying(255) DEFAULT NULL::character varying,
    metadata json
);


ALTER TABLE "ManagedProcess_Journal_Record" OWNER TO bc;

--
-- Name: Management_Post_Group; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Management_Post_Group" (
    id bigint NOT NULL,
    title character varying(255) DEFAULT NULL::character varying,
    description character varying(255) DEFAULT NULL::character varying,
    created integer,
    updated integer,
    ordered integer
);


ALTER TABLE "Management_Post_Group" OWNER TO bc;

--
-- Name: Management_Post_Individual; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Management_Post_Individual" (
    id bigint NOT NULL,
    isactive smallint DEFAULT 0,
    "ManagementPostGroup" integer,
    "CompanyStructureDepartment" integer,
    title character varying(255) DEFAULT NULL::character varying,
    created integer,
    updated integer,
    ordered integer,
    "takepartinevents_EventProcessExecutionPlannedStaged" integer[]
);


ALTER TABLE "Management_Post_Individual" OWNER TO bc;

--
-- Name: Membership_Online_Record; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Membership_Online_Record" (
    id bigint NOT NULL,
    "ActorUserSystem" integer,
    securehash integer,
    hash integer,
    renewhash integer,
    ip inet,
    created integer
);


ALTER TABLE "Membership_Online_Record" OWNER TO bc;

--
-- Name: Membership_PasswordChangeIntent_ActivationToken; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Membership_PasswordChangeIntent_ActivationToken" (
    id bigint NOT NULL,
    "ActorUserSystem" integer,
    activationcode character varying(255) DEFAULT NULL::character varying,
    created integer,
    emailssent integer
);


ALTER TABLE "Membership_PasswordChangeIntent_ActivationToken" OWNER TO bc;

--
-- Name: Membership_RegisterIntent_ActivationToken; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Membership_RegisterIntent_ActivationToken" (
    id bigint NOT NULL,
    email character varying(255) DEFAULT NULL::character varying,
    activationcode character varying(255) DEFAULT NULL::character varying,
    created integer,
    emailssent integer
);


ALTER TABLE "Membership_RegisterIntent_ActivationToken" OWNER TO bc;

--
-- Name: OAuth_Link_UserId; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "OAuth_Link_UserId" (
    id bigint NOT NULL,
    "ActorUserSystem" integer,
    userid64 character varying(255) DEFAULT NULL::character varying,
    oauth2service character varying(255) DEFAULT NULL::character varying
);


ALTER TABLE "OAuth_Link_UserId" OWNER TO bc;

--
-- Name: OAuth_Session_Tokens; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "OAuth_Session_Tokens" (
    id bigint NOT NULL,
    "ActorUserSystem" integer,
    oauth2service character varying(255) DEFAULT NULL::character varying,
    oauthaccesstoken character varying(255) DEFAULT NULL::character varying,
    oauthtokensecret character varying(255) DEFAULT NULL::character varying,
    created integer,
    expire integer
);


ALTER TABLE "OAuth_Session_Tokens" OWNER TO bc;

--
-- Name: People_Employee_Counterparty; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "People_Employee_Counterparty" (
    id bigint NOT NULL,
    isactive smallint DEFAULT 0,
    "CompanyLegalEntityCounterparty" integer,
    "ActorUserSystem" integer,
    title character varying(255) DEFAULT NULL::character varying,
    mail character varying(255) DEFAULT NULL::character varying,
    number integer,
    created integer,
    updated integer,
    ordered integer
);


ALTER TABLE "People_Employee_Counterparty" OWNER TO bc;

--
-- Name: People_Employee_Internal; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "People_Employee_Internal" (
    id bigint NOT NULL,
    isactive smallint DEFAULT 0,
    "ActorUserSystem" integer,
    "ManagementPostIndividual" integer,
    title character varying(255) DEFAULT NULL::character varying,
    medicalinspectiondate integer,
    fluorographydate integer,
    created integer,
    updated integer,
    ordered integer,
    istrener smallint DEFAULT 0,
    "issuedrealcopy_DMSCopyControled" integer[],
    "removedrealcopy_DMSCopyControled" integer[]
);


ALTER TABLE "People_Employee_Internal" OWNER TO bc;

--
-- Name: PushNotification_Template_Simple; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "PushNotification_Template_Simple" (
    id bigint NOT NULL,
    translated_ru integer,
    translated_en integer,
    title character varying(255) DEFAULT NULL::character varying,
    description text
);


ALTER TABLE "PushNotification_Template_Simple" OWNER TO bc;

--
-- Name: RBAC_DocumentPrototypeResponsible_System; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "RBAC_DocumentPrototypeResponsible_System" (
    id bigint NOT NULL,
    delegationactive smallint DEFAULT 0,
    managementrole integer,
    processprototype integer,
    subjectprototype integer,
    stage character varying(255) DEFAULT NULL::character varying,
    ordered integer
);


ALTER TABLE "RBAC_DocumentPrototypeResponsible_System" OWNER TO bc;

--
-- Name: RBAC_ProcessStartPermission_System; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "RBAC_ProcessStartPermission_System" (
    id bigint NOT NULL,
    accessactive smallint DEFAULT 0,
    managementrole integer,
    processprototype integer,
    subjectprototype integer,
    ordered integer
);


ALTER TABLE "RBAC_ProcessStartPermission_System" OWNER TO bc;

--
-- Name: RiskManagement_Risk_Approved; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "RiskManagement_Risk_Approved" (
    id bigint NOT NULL,
    critical smallint DEFAULT 0,
    "BusinessObjectRecordPolymorph" bigint,
    "DirectoryBusinessProcessItem" bigint,
    "DirectorySLAItem" bigint,
    "ManagementPostIndividual" bigint,
    controlperiod bigint,
    title character varying(255) DEFAULT NULL::character varying,
    producteffect integer,
    emergenceprobability integer,
    undetectedprobability integer,
    weighted integer,
    controlact text,
    riskdescription text,
    "controlactions_DirectoryControlActionUniversal" integer[]
);


ALTER TABLE "RiskManagement_Risk_Approved" OWNER TO bc;

--
-- Name: RiskManagement_Risk_NotApproved; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "RiskManagement_Risk_NotApproved" (
    id bigint NOT NULL,
    identified smallint DEFAULT 0,
    "RiskManagementRiskApproved" bigint,
    documentoforigin character varying(255) DEFAULT NULL::character varying,
    riskdescription text,
    "DirectoryBusinessProcessItem" bigint,
    "BusinessObjectRecordPolymorph" bigint
);


ALTER TABLE "RiskManagement_Risk_NotApproved" OWNER TO bc;

--
-- Name: Study_RegulationStudy_A; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Study_RegulationStudy_A" (
    id bigint NOT NULL,
    "StudyRegulationStudyQ" bigint,
    content character varying(255) DEFAULT NULL::character varying,
    correctly smallint,
    ordered integer,
    created integer,
    updated integer
);


ALTER TABLE "Study_RegulationStudy_A" OWNER TO bc;

--
-- Name: Study_RegulationStudy_Q; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Study_RegulationStudy_Q" (
    id bigint NOT NULL,
    "DocumentRegulationsTA" bigint,
    content character varying(255) DEFAULT NULL::character varying,
    ordered integer,
    created integer,
    updated integer
);


ALTER TABLE "Study_RegulationStudy_Q" OWNER TO bc;

--
-- Name: Study_RegulationStudy_R; Type: TABLE; Schema: public; Owner: bc; Tablespace: 
--

CREATE TABLE "Study_RegulationStudy_R" (
    id bigint NOT NULL,
    questionnaire bigint,
    "user" bigint,
    question json,
    useranswer json,
    done integer,
    trua integer,
    falsea integer,
    alla integer,
    starttime integer,
    endtime integer,
    created integer,
    updated integer,
    "DocumentRegulationsASR" bigint
);


ALTER TABLE "Study_RegulationStudy_R" OWNER TO bc;

--
-- Data for Name: Actor_Role_System; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Actor_Role_System" (id, "delegatedto_ActorUserSystem", title, homeuri, name) FROM stdin;
\.


--
-- Data for Name: Actor_User_System; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Actor_User_System" (id, active, tester, system, "ActorRoleSystem", "actas_ActorRoleSystem", "following_ActorUserSystem", "followers_ActorUserSystem", email, phone, dynamicsalt, password, name, wallet, bonus, created, lastlogin, prefs, wrbacgroups) FROM stdin;
1268536884	0	0	0	\N	\N	\N	\N	test@attracti.com	\N	8735988	8796b56572c6fc3257655ab894d6ec611c019929	Test Test	0.00	0	1450708907	\N	\N	\N
340056898	0	0	0	\N	\N	\N	\N	userx@attracti.com	\N	916673593	fdada14a2733cb485eb95de6378b2aab27c5223a	UserX	0.00	0	1450775995	\N	\N	\N
93140008	1	0	0	\N	\N	\N	\N	userz@attracti.com	\N	942782056	b8b99ad9a6ac4562aaf79c639aadc002fc5d9781	UserZ	0.00	0	1450777645	1450777685	\N	\N
13558749	1	0	0	\N	\N	\N	\N	root@attracti.com	\N	328267673	e81236b7c314b41cac22c9787b3ca0d098ffde28	ROOT	0.00	0	1450863501	1450863501	\N	\N
755312870	1	0	0	\N	\N	\N	\N	test24@attracti.com	\N	487034055	c341329c5f2b691d150a4f45d4803a5c52030cfa	Петрина О.И.	0.00	0	1451309749	1451309749	\N	\N
645239602	1	0	0	\N	\N	\N	\N	test20@attracti.com	\N	505605396	1e5231f3a4993080fc2ceb290ac9ff9f54b2d804	Прозорова О.О.	0.00	0	1451308005	1451308005	\N	\N
1513412582	1	0	0	\N	\N	\N	\N	test21@attracti.com	\N	695806777	d70f8061d8cfe92e7f1e1a70ea28e9c6420b27c3	Кузьмицкий К.	0.00	0	1451308134	1451308134	\N	\N
96636757	1	0	0	\N	\N	\N	\N	test22@attracti.com	\N	637857012	a89734cdd92d5c3c1419b933703c49fc62280684	Пижук В.М.	0.00	0	1451308198	1451308198	\N	\N
1271104544	1	0	0	\N	\N	\N	\N	test25@attracti.com	\N	828969905	00dd45742faa3b268788da37b041aac04e19750b	Фурс С.Л.	0.00	0	1451309727	\N	\N	\N
758119360	1	0	0	\N	\N	\N	\N	test19@attracti.com	\N	258478097	32f611d4c7dc39ac44e4408de4948b3c648a851f	Белоусова О.В.	0.00	0	1451304587	\N	\N	\N
1933913319	1	0	0	\N	\N	\N	\N	test18@attarcti.com	\N	1168898554	41d0429db04c4fc33adf84900b99f30ad4cb1637	Колесник В.Л.	0.00	0	1451301657	\N	\N	\N
1381457867	1	0	0	\N	\N	\N	\N	test17@attracti.com	\N	72812974	8908a2efcab3d96d03b4543cdb72b61a83912755	Клестов М.М.	0.00	0	1451300594	\N	\N	\N
682905848	1	0	0	\N	\N	\N	\N	test16@attracti.com	\N	1712623099	8a9fb5d2b7b67d32e0d2a5be5a9185434df748ef	Шлапак Д.Л.	0.00	0	1451300389	\N	\N	\N
688030890	1	0	0	\N	\N	\N	\N	test15@attracti.com	\N	1660266742	2e8d9a5843fefcd202fb99312a9cd4092b370ab5	Осокина М.И.	0.00	0	1451291905	\N	\N	\N
542035496	1	0	0	\N	\N	\N	\N	test14@attracti.com	\N	599145462	141bc572493e979851aa3a72ff5a10b86f48f862	Грибенник В.А.	0.00	0	1451291704	\N	\N	\N
1769099644	1	0	0	\N	\N	\N	\N	test12@attracti.com	\N	110455271	31871174ba17dbb101a59a8a571b48f9e9aa7cdf	Стопыкин В.С.	0.00	0	1451160644	\N	\N	\N
621859804	1	0	0	\N	\N	\N	\N	test11@attracti.com	\N	1387219132	10db0f0653de84801fbd6e4199030c1fc528610b	Павлова В.В.	0.00	0	1451159811	\N	\N	\N
1410396892	1	0	0	\N	\N	\N	\N	test7@attracti.com	\N	2050764955	5221e2de2e5824fd7d2e28676e626855d1de5e30	Лаврухина Г.В.	0.00	0	1451156135	\N	\N	\N
1454454205	1	0	0	\N	\N	\N	\N	test23@attracti.com	\N	405379544	76a562300d1c5973a1638722dfcdf5aa8bc0804d	Бычковская Т.Л.	0.00	0	1451308784	1451308784	\N	\N
1113816525	1	0	0	\N	\N	\N	\N	test26@attracti.com	\N	249228713	6ebaf8dedf4de02005f60684956287544b93d465	Тарасенко И.И.	0.00	0	1451310926	\N	\N	\N
1045410703	1	0	0	\N	\N	\N	\N	test26@afftracti.com	\N	1807046411	9cfca0b3bd2cab291d98e201521b90d581707bdb	Топильский А.О.	0.00	0	1451318192	\N	\N	\N
441433506	1	0	0	\N	\N	\N	\N	test27@attracti.com	\N	578964095	207a7917478461fbb7feb0e3e92a4ce236b7fd66	Копань А.В.	0.00	0	1451318332	\N	\N	\N
851333727	1	0	0	\N	\N	\N	\N	test27@attarcti.com	\N	1062214666	c16007c67d1a8fbf4af1d507b0c6cb65322915bf	Якушко И.В.	0.00	0	1451319331	\N	\N	\N
341959841	1	0	0	\N	\N	\N	\N	test28@attracti.com	\N	1850433805	d2b40a5c9f23af1e04dcc9d28360579866b83ff9	Демченко Р.В.	0.00	0	1451320286	\N	\N	\N
1288440648	1	0	0	\N	\N	\N	\N	test29@attracti.com	\N	1141425863	543f32cf5b342e0d4ed7e9ac60a6e5a6cdf7352d	Ерко А.В.	0.00	0	1451328061	\N	\N	\N
183895516	1	0	0	\N	\N	\N	\N	test1@attracti.com	\N	810189183	f3ed3eea5087ca433dd1059477dcb2ef7a55f036	Пшеничная И.В.	0.00	0	1450800316	1453053770	\N	\N
540038425	1	0	0	\N	\N	\N	\N	inna@attracti.com	\N	55121351	d0028f6f48518666a5cb392f21b72df797b8c93b	Inna Okun	0.00	0	1450217017	1454055228	\N	\N
1051038495	1	0	0	\N	\N	\N	\N	max@attracti.com	\N	1871529528	a8db372474feaa8b6d0a9a0bd6236689894f5705	Max Bezugly	0.00	0	1450863798	1456505881	\N	\N
\.


--
-- Data for Name: BusinessObject_Record_Polymorph; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "BusinessObject_Record_Polymorph" (id, isarchive, "DefinitionClassBusinessObject", "DefinitionTypeBusinessObject", "MateriallyResponsible", periodicityvalidation, periodicityverification, periodicitycalibration, periodicitycleaning, title, _parent, inventorynumber, description, currentproperties, value, currency, maker, expirationdate, startupdate, maintenancework, created, updated, ordered, location, periodicitymaintenance, boofclient, serialnumber, "ResponsibleMaintenance", "ResponsibleValidation", "ResponsibleVerification", "ResponsibleCalibration", "ResponsibleCleaning") FROM stdin;
206674127	0	1512578880	534317887	\N	\N	\N	\N	\N	Склад №1	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1451393503	1451393503	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
10341110	0	1512578880	534317887	\N	\N	\N	\N	\N	Склад №2	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1451393581	1451393581	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
643421900	0	1227147098	1872431938	\N	246037407	\N	\N	\N	Штабелёр MK5656	\N	34334534	Автоматизированное устройство для перемещения паллет с грузом на высоту стеллажного оборудования до 5 м	Номинальная грузоподъемность - 1200 кг\r\nМаксимальная высота  подъема - 1090-5350 мм\r\nШирина рабочего прохода поддоном 800*1200 вдоль вил - 2243 мм	67676.00	UAH	ХЗХЗ	\N	\N	\N	1451394392	1451394392	\N	1293144792	\N	\N	\N	\N	\N	\N	\N	\N
966042166	0	1227147098	1872431938	\N	2059004404	\N	\N	\N	Рокла ER 243	\N	74356546	Ручное механическое устройство для напольного перемещения груза	Номинальная грузоподъемность- 2000 кг\r\nВысота подъема – 190 мм\r\nДлина вил- 1150 мм\r\nМатериал колес: полиуретан	2344.00	UAH	TRTR	\N	\N	\N	1451394496	1451394496	\N	10341110	\N	\N	\N	\N	\N	\N	\N	\N
2064133034	0	31040347	1440323944	\N	464841811	1464808265	246037407	\N	Температурный датчик SN673456	463803818	456789876	test testtesttest test testtesttest test testtesttest test testtesttest test testtesttest test testtesttest test testtesttest 	test testtesttest test testtesttest test testtesttest test testtesttest test testtesttest test testtesttest 	5656.00	UAH	RTdbv	\N	\N	\N	1451395036	1451395036	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
463803818	0	31040347	1147303614	357363473	1464808265	1464808265	246037407	47836635	Холодильная установка Sumsung FG5647	\N	000099999	test test test test test testtest testtest testtest testtest testtest testvtest testtest testtest testtest test	test test test test test testtest testtest testtest testtest testtest testvtest testtest testtest testtest test	\N	UAH	Sumsung	\N	\N	\N	1451393971	1451396149	\N	206674127	\N	\N	\N	\N	\N	\N	\N	\N
614895960	0	31040347	248206306	357363473	464841811	464841811	2059004404	47836635	Вентиляционная установка VENTS 7878	\N	98989898989898	test test test test test test test test test test test test test test test test test test test test test test test test 	test test test test test test test test test test test test test test test test test test test test test test test test 	5656565.00	UAH	VENTS	\N	\N	\N	1451394145	1451396170	\N	1293144792	\N	\N	\N	\N	\N	\N	\N	\N
1293144792	0	1512578880	534317887	\N	\N	\N	\N	\N	Склад №3	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1451393598	1453209118	\N	\N	\N	1156372664	\N	\N	\N	\N	\N	\N
\.


--
-- Data for Name: Calendar_Period_Month; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Calendar_Period_Month" (id, isactive, title, created, updated, ordered, everynmonth) FROM stdin;
47836635	1	1 месяц	1450612370	1450612370	1	\N
464841811	1	2 месяца	1450612385	1450612385	2	\N
1464808265	1	3 месяца	1450612393	1450612393	3	\N
2059004404	1	4 месяца	1450612401	1450612401	4	\N
20354926	1	5 месяцев	1450612408	1450612408	5	\N
246037407	1	6 месяцев	1450612424	1451394835	6	\N
\.


--
-- Data for Name: Communication_Comment_Level2withEditingSuggestion; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Communication_Comment_Level2withEditingSuggestion" (id, iseditingsuggestion, autor, appliedautor, approvedautor, editingsuggestionautor, content, appliedstatus, approvedstatus, editingsuggestionstatus, document, docpath, replyto, created, cancel, toreplyto) FROM stdin;
\.


--
-- Data for Name: Company_LegalEntity_Counterparty; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Company_LegalEntity_Counterparty" (id, isactive, isclient, iscontractor, "BusinessArea", "BusinessObjectRecordPolymorph", title, legaladdress, ba, mfo, edropou, contactname, mail, number, letter, signatoryname, baseaction, other, created, updated, ordered, "ManagementPostIndividual") FROM stdin;
115997801	1	1	0	512452820	\N	Санофи	Юридический адрес 1	1234567890	098765	12345	Иванов И.И.	test111@test.com	123456780	F	Иванов И.И.	директор	\N	1451297818	1451310019	1	1404952896
1156372664	1	1	0	1471555169	\N	Тева	Юридический адрес 2	09876543	56789876	23456	\N	\N	\N	\N	\N	\N	\N	1451297877	1451311043	2	1897084668
404906315	1	0	1	512452820	\N	Аттракти	test	12345678900987654321	456765476	234343234	Окунь И.С.	inna@attracti.com	673127171	A	Окунь И.С.	Директор	\N	1451391853	1451391853	3	\N
\.


--
-- Data for Name: Company_Structure_Companygroup; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Company_Structure_Companygroup" (id, title, created, updated, ordered) FROM stdin;
1642094893	Тестовая Группа компаний 1	1450614898	1450614898	1
267613092	Тестовая Группа компаний 2	1450614905	1450614905	2
1421664145	Тестовая Группа компаний 3	1450614911	1450614911	3
\.


--
-- Data for Name: Company_Structure_Department; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Company_Structure_Department" (id, "HeadOfDepartment", title, created, updated, ordered, _parent) FROM stdin;
1330893965	\N	Отдел ВЭД	1451156946	1451156946	7	\N
1870096956	\N	Склад	1451156966	1451156966	8	\N
908427138	\N	Вспомогательный отдел	1451157110	1451157110	12	\N
984894068	\N	ВМК	1451157129	1451157129	13	\N
1641958276	\N	Автотранспортный отдел	1451157147	1451157147	14	\N
905805394	\N	Административный отдел	1451157321	1451157321	17	\N
828479089	990258053	Финансовый отдел	1451156932	1451291704	6	\N
1743222417	921220994	Служба эксплуатации	1451157183	1451301657	16	\N
1261974287	275450805	Отдел качества	1451157070	1451304588	11	\N
1190511098	2065101785	Таможенный отдел	1451156985	1451308693	9	\N
1658312748	1982566019	Отдел IT	1451157026	1451319331	10	\N
1412798552	1939617403	Отдел кадров	1451157162	1451328061	15	\N
\.


--
-- Data for Name: Company_Structure_Division; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Company_Structure_Division" (id, "CompanyStructureDepartment", "HeadOfDepartment", title, created, updated, ordered) FROM stdin;
\.


--
-- Data for Name: DMS_Copy_Controled; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "DMS_Copy_Controled" (id, isactive, "DocumentRegulationsSOP", master, "holders_PeopleEmployeeInternal", "previous_PeopleEmployeeInternal", created, dateissue, datereturn) FROM stdin;
\.


--
-- Data for Name: DMS_Copy_Realnoncontrolcopy; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "DMS_Copy_Realnoncontrolcopy" (id, isactive, isreturn, realcopy, master, "holders_PeopleEmployeeInternal", "previous_PeopleEmployeeInternal", created, dateissue) FROM stdin;
\.


--
-- Data for Name: DMS_DecisionSheet_Signed; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "DMS_DecisionSheet_Signed" (id, closed, needsignfrom, hassignfrom, document, created, hascancelfrom) FROM stdin;
819689488	0	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	urn:Document:Detective:C_IS:857294619	1456413733	\N
994356948	0	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	{urn:Management:Post:Individual:1118804000}	urn:Document:Detective:C_LC:583758799	1456417306	\N
\.


--
-- Data for Name: DMS_Document_Universal; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "DMS_Document_Universal" (id, document, created, indexabletext, version, code, initiator, vised, approved, done, archived, process, parent, "DefinitionPrototypeSystem", title) FROM stdin;
1034011664	urn:Document:Complaint:C_LT:917825520	1456429770	\N	0	C_LT-535342	urn:Actor:User:System:1	\N	\N	f	f	UPN:DMS:Complaints:Complaint:9995676	\N	703651450	 
942913781	urn:Document:Detective:C_LC:583758799	1456417058	C_LC-985360	0	C_LC-985360	urn:Actor:User:System:1	\N	\N	f	f	UPN:DMS:Complaints:Detective:5734686	\N	77748383	 
1035562271	urn:Document:Complaint:C_LC:88687448	1456416942	\N	0	C_LC-358384	urn:Actor:User:System:1	\N	\N	f	f	UPN:DMS:Complaints:Complaint:1626004	\N	1697834005	 
519158613	urn:Document:Detective:C_IS:857294619	1456413446	C_IS-786018	0	C_IS-786018	urn:Actor:User:System:1	\N	\N	f	f	UPN:DMS:Complaints:Detective:7006567	\N	2105755130	 
943481548	urn:Document:Complaint:C_IS:76828139	1456410043	\N	0	C_IS-979940	urn:Actor:User:System:1	\N	\N	f	f	UPN:DMS:Complaints:Complaint:7508668	\N	404140251	C_IS-979940 Жалоба
226146143	urn:Document:Detective:C_LT:839948176	1456407350	test C_LT-323120	0	C_LT-323120	urn:Actor:User:System:1	\N	\N	f	f	UPN:DMS:Complaints:Detective:150798	\N	1531101050	C_LT-323120 Расследование
1806352408	urn:Document:Complaint:C_LT:24913852	1456407233	test 2 C_LT-757339	0	C_LT-757339	urn:Actor:User:System:1	\N	\N	f	f	UPN:DMS:Complaints:Complaint:2084582	\N	703651450	C_LT-757339 Жалоба
\.


--
-- Data for Name: DMS_Viewaccess_ByProcedure; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "DMS_Viewaccess_ByProcedure" (id, isactive, isreturn, master, "holder_PeopleEmployeeInternal", dateissue, datereturn) FROM stdin;
\.


--
-- Data for Name: Definition_Class_BusinessObject; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Definition_Class_BusinessObject" (id, title, created, updated, ordered) FROM stdin;
1512578880	Помещения	1451298163	1451298163	1
31040347	Оборудования	1451298171	1451298171	2
1227147098	Техника	1451391904	1451391904	3
\.


--
-- Data for Name: Definition_DocumentClass_ForPrototype; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Definition_DocumentClass_ForPrototype" (id, title, name) FROM stdin;
351311080	Жалобы	Complaint
1936018484	Заявки	Claim
379049347	САРА	Capa
1761375626	Договора	Contract
2139908796	Мероприятия (CAPA)	Correction
698818114	Приложения к Договору	ContractApplication
1479262907	Протоколы служебного расследования	Detective
2000138623	Протоколы	Protocol
1177787957	Регламентирующие	Regulations
10680944	Кадровые	Staffdoc
207416553	Технические задания	TechnicalTask
954771065	Тендеры	Tender
\.


--
-- Data for Name: Definition_Prototype_System; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Definition_Prototype_System" (id, title, indomain, ofclass, oftype, isprocess, approver, "visants_ManagementPostIndividual", unmanaged, withhardcopy) FROM stdin;
1343947548	Аттестация	DMS	Regulation	Attestation	1	\N	\N	0	0
1282849890	Корректирующие и предупреждающие действия (CAPA)	DMS	Correction	CAPA	1	\N	\N	0	0
460955489	Заявка	DMS	Claims	Claim	1	\N	\N	0	0
1400449434	Жалоба	DMS	Complaints	Complaint	1	\N	\N	0	0
125012940	Договор	DMS	Contracts	Contract	1	\N	\N	0	0
167680249	Стандартный	DMS	Process	Simple	1	\N	\N	0	0
1544741608	Стандартные операционные процедуры (СОП)	DMS	Regulation	SOP	1	\N	\N	0	0
979439350	Программа обучения	DMS	Regulation	Study	1	\N	\N	0	0
1600382509	Техническое задание	DMS	Tenders	TechTask	1	\N	\N	0	0
545291303	Выдача УКД	DMS	Regulation	UKD	1	\N	\N	0	0
219564286	Мероприятия (CAPA)	Document	Correction	Capa	0	\N	\N	0	0
804966377	Решения по мероприятию (CAPA)	Document	Solution	Correction	0	\N	\N	0	0
1222319301	Приложение к Договору	Document	ContractApplication	Universal	0	\N	\N	0	0
340896185	Служебное расследование	DMS	Complaints	Detective	1	\N	\N	0	0
378576175	Стандартный с планированием	DMS	Process	SimpleWithPlan	1	\N	\N	0	0
2084105390	Проведение Тендера	DMS	Tenders	Tender	1	\N	\N	0	0
257466779	Решение	Document	Solution	Universal	0	\N	\N	0	0
940458045	Сводная таблица Результат тендера	Document	Tender	Table	0	\N	\N	0	0
174317803	Заявка на приход	Document	Claim	R_LSC	0	1118804000	{1118804000,1816416714}	0	0
1214964131	Заявка на отгрузку	Document	Claim	R_LSD	0	1118804000	{1118804000,1816416714}	0	0
416562378	Заявка на покупку/доработку ПО	Document	Claim	R_PAD	0	1118804000	{1118804000,1816416714}	0	0
288174676	Заявка на закрепление/перезакрепление ОС, НМА в 1С	Document	Claim	R_OQF	0	1118804000	{1118804000,1816416714}	0	0
1257729064	Заявка на покупку/получение расходных материалов	Document	Claim	R_OQR	0	1118804000	{1118804000,1816416714}	0	0
1048668441	Заявка на повторное обучение/переаттестацию	Document	Claim	R_QDE	0	1118804000	{1118804000,1816416714}	0	0
1137292124	Заявка на создание нового регламентирующего документа	Document	Claim	R_RDC	0	1118804000	{1118804000,1816416714}	0	0
1083584237	Заявка на выдачу/изъятие копий документов	Document	Claim	R_RDD	0	1118804000	{1118804000,1816416714}	0	0
418793814	Заявка на поставку материалов, поставку и установку оборудования, проведение работ	Document	Claim	R_TD	0	1118804000	{1118804000,1816416714}	0	0
1285839997	Заявка на выдачу/изъятие пластиковой карточки/изменение категории доступа СКД	Document	Claim	R_UPC	0	1118804000	{1118804000,1816416714}	0	0
1622205108	Заявка на создание/удаление корпоративного почтового ящика	Document	Claim	R_UPE	0	1118804000	{1118804000,1816416714}	0	0
170900260	Заявка на выдачу/удаление доступа к интернет ресурсам	Document	Claim	R_UPI	0	1118804000	{1118804000,1816416714}	0	0
939900508	Заявка на покупку/замену картриджа	Document	Claim	R_UPK	0	1816416714	{1118804000,1816416714}	0	0
733452792	Заявка на покупку/получение этикеток для печати LPN	Document	Claim	R_UPL	0	1118804000	{1118804000,1816416714}	0	0
1960794193	Жалоба на выполнение инженерно-технических работ	Document	Complaint	C_IW	0	\N	\N	1	0
1960563853	Жалоба на брокерские услуги и ВМК	Document	Complaint	C_LB	0	\N	\N	1	0
1697834005	Жалоба на переупаковку товаров	Document	Complaint	C_LC	0	\N	\N	1	0
1036859738	Жалоба на на услуги по обработке товаров	Document	Complaint	C_LP	0	\N	\N	1	0
703651450	Жалоба на транспортно-экспедиционные услуги	Document	Complaint	C_LT	0	\N	\N	1	0
156864361	Договор аренды складских помещений	Document	Contract	LWP	0	1816416714	{1816416714,1118804000}	0	1
138262019	Договор на закупку материалов	Document	Contract	MT	0	1816416714	{1118804000,1816416714}	0	1
1132211558	Договор на оказание регулярных услуг	Document	Contract	RSS	0	1816416714	{1118804000,1816416714}	0	1
1867192760	Договор выполнения подрядных работ	Document	Contract	SS	0	1816416714	{1118804000,1816416714}	0	1
872530236	Договор на ТО и ремонт оборудования	Document	Contract	TME	0	1816416714	{1118804000,1816416714}	0	1
2105755130	Протокол служебного расследования DC_IS	Document	Detective	C_IS	0	1118804000	{1118804000,1816416714}	0	1
1167203278	Протокол служебного расследования DC_IV	Document	Detective	C_IV	0	1118804000	{1118804000,1816416714}	0	1
1531101050	Протокол служебного расследования DC_LT	Document	Detective	C_LT	0	1118804000	{1118804000,1816416714}	0	1
1360624655	Протокол контрольной инспекции по CAPA	Document	Protocol	KI	0	1118804000	\N	0	1
296424216	Протокол поверки	Document	Protocol	MT	0	1118804000	{1118804000,1816416714}	0	1
567888976	Протокол самоинспекции по риску	Document	Protocol	SI	0	1118804000	\N	0	1
1081213037	Протокол технического обслуживания	Document	Protocol	TM	0	1118804000	{1118804000,1816416714}	0	1
873954393	Приказ	Document	Regulations	AO	0	1118804000	{1118804000,1816416714}	0	1
629815234	Контрольная самоинспекция по САРА	DMS	Correction	CAPAInspection	1	\N	\N	0	0
1610780024	Самоинспекция по рискам	DMS	Deviations	RISKInspection	1	\N	\N	0	0
437670027	Визирование	DMS	Decisions	Visa	1	\N	\N	0	0
1148817928	Утверждение	DMS	Decisions	Approvement	1	\N	\N	0	0
2036780319	Исполнение	DMS	Execution	Doing	1	\N	\N	0	0
1536406172	Проверка	DMS	Decisions	Reviewing	1	\N	\N	0	0
1266772606	Планирование	DMS	Decisions	Plan	1	\N	\N	0	0
446113163	Тестирование	DMS	Attestations	Test	1	\N	\N	0	0
80301308	Техническое задание на проведение работ	Document	TechnicalTask	ForWorks	0	1118804000	{1118804000}	0	0
359991105	Заявка на перемещение	Document	Claim	R_LSM	0	1118804000	{1118804000,1816416714}	0	0
1928699298	Заявка на проведение самоинспекций, аудитов, проверок	Document	Claim	R_QDA	0	1118804000	{1118804000,1816416714}	0	0
2105419881	Заявка на проведение валидационных исследований/аттестации/квалификации/калибровки или ТО	Document	Claim	R_QDM	0	1118804000	{1118804000,1816416714}	0	0
604705749	Заявка на покупку/установку компьютерной техники	Document	Claim	R_PAT	0	1118804000	{1118804000,1816416714}	0	0
40262480	Заявка на внесение изменений в регламентирующий документ	Document	Claim	R_RDE	0	1118804000	{1118804000,1816416714}	0	0
545570964	Заявка на таможенное оформление	Document	Claim	R_LST	0	1118804000	{1118804000,1816416714}	0	0
336749675	Заявка на запуск процесса управления изменениями (САРА)	Document	Claim	R_QDC	0	1118804000	{1118804000,1816416714}	0	0
1268611089	Заявка на изменение в параметрах доступа СКД	Document	Claim	R_UPP	0	1118804000	{1118804000,1816416714}	0	0
665478613	Программа обучения и аттестации	Document	Regulations	TA	0	1118804000	{1118804000,1816416714}	0	0
44702326	Техническое задание на закупку материалов	Document	TechnicalTask	ForMaterials	0	1118804000	{1118804000,1816416714}	0	0
1543010125	Тендер	Document	Tender	Extended	0	1118804000	{1118804000,1816416714}	0	0
475765487	Договор на оказание услуг ТЛС и СТЗ	Document	Contract	BW	0	1816416714	{1118804000,1816416714}	0	1
1677181573	Жалоба на валидационные исследования, проверки, калибровки	Document	Complaint	C_IV	0	\N	\N	1	0
971604962	Договор о предоставлении услуг логистического комплекса, автостоянки	Document	Contract	LC	0	1816416714	{1118804000,1816416714}	0	1
1001425402	Корректирующие мероприятия (САРА)	Document	Capa	Deviation	0	1118804000	{1118804000,1816416714}	0	0
404140251	Жалоба на работу инженерных систем/оборудования, состояние помещений	Document	Complaint	C_IS	0	\N	{}	1	0
122654907	Договор на ТО холодильников, кондиционеров	Document	Contract	TMC	0	1816416714	{1118804000,1816416714}	0	1
1562279611	Договор аренды офисных помещений	Document	Contract	LOP	0	1816416714	{1118804000,1816416714}	0	1
986165029	Протокол служебного расследования DC_IW	Document	Detective	C_IW	0	1118804000	{1118804000,1816416714}	0	1
878247650	Протокол служебного расследования DC_LB	Document	Detective	C_LB	0	1118804000	{1118804000,1816416714}	0	1
77748383	Протокол служебного расследования DC_LC	Document	Detective	C_LC	0	1118804000	{1118804000,1816416714}	0	1
1256701256	Протокол служебного расследования DC_LP	Document	Detective	C_LP	0	1118804000	{1118804000,1816416714}	0	1
1885773414	Протокол калибровки	Document	Protocol	CT	0	1118804000	{1118804000,1816416714}	0	1
1331286282	Протокол аудита	Document	Protocol	EA	0	1118804000	{1118804000,1816416714}	0	1
845111633	Протокол проверки	Document	Protocol	EC	0	1118804000	{1118804000,1816416714}	0	1
996324008	Протокол валидационного исследования	Document	Protocol	VT	0	1118804000	{1118804000,1816416714}	0	1
302885503	Инструкция	Document	Regulations	I	0	1118804000	{1816416714,1118804000}	0	1
1058982785	Должностная инструкция	Document	Regulations	JD	0	1118804000	{1816416714,1118804000}	0	1
998804143	Положение/Регламент/Политика	Document	Regulations	P	0	1118804000	{1816416714,1118804000}	0	1
527566292	Программа валидационного исследования	Document	Regulations	PV	0	1118804000	{1816416714,1118804000}	0	1
676050443	Стандартные операционные процедуры (СОП)	Document	Regulations	SOP	0	1118804000	{1118804000,1816416714}	0	1
465354554	Приказ на увольнение	Document	Staffdoc	OF	0	1118804000	{1118804000,1816416714}	0	1
1398643653	Приказ на принятие на работу	Document	Staffdoc	OR	0	1118804000	{1118804000,1816416714}	0	1
486464159	Заявление на увольнение	Document	Staffdoc	SD	0	1118804000	{1118804000,1816416714}	0	1
2057180597	Заявление на отпуск	Document	Staffdoc	SV	0	1118804000	{1118804000,1816416714}	0	1
99930200	Пересмотр рисков	DMS	Deviations	RISKReview	1	\N	\N	0	0
1061101692	Протокол пересмотра рисков	Document	Protocol	RR	0	\N	\N	1	0
1579880001	Стандартный с конфигурацией	DMS	Process	SimpleWithConfiguring	1	\N	\N	0	0
1863241730	Заявка на покупку/установку ПО	Document	Claim	R_PAI	0	1118804000	{1118804000}	0	0
\.


--
-- Data for Name: Definition_Type_BusinessObject; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Definition_Type_BusinessObject" (id, "DefinitionClassBusinessObject", title, created, updated, ordered) FROM stdin;
534317887	1512578880	Склады	1451393123	1451393123	\N
1147303614	31040347	Холодильные установки	1451393158	1451393158	\N
248206306	31040347	Вентиляционные установки	1451393404	1451393404	\N
1872431938	1227147098	Погрузочная техника	1451393142	1451394211	\N
1440323944	31040347	Комплектующие	1451394955	1451394955	\N
\.


--
-- Data for Name: Directory_AdditionalSection_Simple; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Directory_AdditionalSection_Simple" (id, sectiontitle, sectiontext, "DocumentRegulationsI", "DocumentRegulationsSOP", "approvedrisks_RiskManagementRiskApproved", "DocumentRegulationsP") FROM stdin;
1499432052	\N	\N	\N	269476718	\N	\N
1939855007	доп	текст топ	\N	569726514	\N	\N
1900586120	щшекуывапсрмои	<p>лгнекыуявачпсрмои</p>	\N	432473727	\N	\N
1410712638	щшгнекуываспрмоилтд	<p>некуывчапсрмоилтдь</p>	\N	432473727	\N	\N
1957330082	ГРАФИК ПРОВЕДЕНИЯ САМОИНСПЕКЦИИ НА 201__ ГОД	<p> </p>\n<table><tr><td>\n<p>№ пп</p>\n</td>\n<td>\n<p>Подразделения/объекты</p>\n</td>\n<td>\n<p>Дата самоинспекции</p>\n</td>\n<td>\n<p>Отметка о проведении</p>\n</td>\n<td>\n<p>Подпись инспектора</p>\n</td>\n</tr><tr><td>\n<p>1</p>\n</td>\n<td> </td>\n<td> </td>\n<td>\n<p> </p>\n</td>\n<td>\n<p> </p>\n</td>\n</tr><tr><td>\n<p>2</p>\n</td>\n<td> </td>\n<td> </td>\n<td>\n<p> </p>\n</td>\n<td>\n<p> </p>\n</td>\n</tr><tr><td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n</tr><tr><td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n</tr><tr><td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n</tr><tr><td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n</tr><tr><td>\n<p> </p>\n</td>\n<td>\n<p> </p>\n</td>\n<td>\n<p> </p>\n</td>\n<td>\n<p> </p>\n</td>\n<td>\n<p> </p>\n</td>\n</tr><tr><td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n</tr></table>	\N	682182351	\N	\N
1055225080	ГРАФИК ПРОВЕДЕНИЯ САМОИНСПЕКЦИИ НА 201__ ГОД	<p> </p>\n<table><tr><td>\n<p>№ пп</p>\n</td>\n<td>\n<p>Подразделения/объекты</p>\n</td>\n<td>\n<p>Дата самоинспекции</p>\n</td>\n<td>\n<p>Отметка о проведении</p>\n</td>\n<td>\n<p>Подпись инспектора</p>\n</td>\n</tr><tr><td>\n<p>1</p>\n</td>\n<td> </td>\n<td> </td>\n<td>\n<p> </p>\n</td>\n<td>\n<p> </p>\n</td>\n</tr><tr><td>\n<p>2</p>\n</td>\n<td> </td>\n<td> </td>\n<td>\n<p> </p>\n</td>\n<td>\n<p> </p>\n</td>\n</tr><tr><td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n</tr><tr><td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n</tr><tr><td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n</tr><tr><td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n</tr><tr><td>\n<p> </p>\n</td>\n<td>\n<p> </p>\n</td>\n<td>\n<p> </p>\n</td>\n<td>\n<p> </p>\n</td>\n<td>\n<p> </p>\n</td>\n</tr><tr><td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n</tr></table>	\N	682182351	\N	\N
1664797045	ГРАФИК ПРОВЕДЕНИЯ САМОИНСПЕКЦИИ НА 201__ ГОД	<p> </p>\n<table><tr><td>\n<p>№ пп</p>\n</td>\n<td>\n<p>Подразделения/объекты</p>\n</td>\n<td>\n<p>Дата самоинспекции</p>\n</td>\n<td>\n<p>Отметка о проведении</p>\n</td>\n<td>\n<p>Подпись инспектора</p>\n</td>\n</tr><tr><td>\n<p>1</p>\n</td>\n<td> </td>\n<td> </td>\n<td>\n<p> </p>\n</td>\n<td>\n<p> </p>\n</td>\n</tr><tr><td>\n<p>2</p>\n</td>\n<td> </td>\n<td> </td>\n<td>\n<p> </p>\n</td>\n<td>\n<p> </p>\n</td>\n</tr><tr><td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n</tr><tr><td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n</tr><tr><td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n</tr><tr><td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n</tr><tr><td>\n<p> </p>\n</td>\n<td>\n<p> </p>\n</td>\n<td>\n<p> </p>\n</td>\n<td>\n<p> </p>\n</td>\n<td>\n<p> </p>\n</td>\n</tr><tr><td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n</tr></table>	\N	682182351	\N	\N
1916684631	ГРАФИК ПРОВЕДЕНИЯ САМОИНСПЕКЦИИ НА 201__ ГОД	<p> </p>\n<table><tr><td>\n<p>№ пп</p>\n</td>\n<td>\n<p>Подразделения/объекты</p>\n</td>\n<td>\n<p>Дата самоинспекции</p>\n</td>\n<td>\n<p>Отметка о проведении</p>\n</td>\n<td>\n<p>Подпись инспектора</p>\n</td>\n</tr><tr><td>\n<p>1</p>\n</td>\n<td> </td>\n<td> </td>\n<td>\n<p> </p>\n</td>\n<td>\n<p> </p>\n</td>\n</tr><tr><td>\n<p>2</p>\n</td>\n<td> </td>\n<td> </td>\n<td>\n<p> </p>\n</td>\n<td>\n<p> </p>\n</td>\n</tr><tr><td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n</tr><tr><td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n</tr><tr><td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n</tr><tr><td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n</tr><tr><td>\n<p> </p>\n</td>\n<td>\n<p> </p>\n</td>\n<td>\n<p> </p>\n</td>\n<td>\n<p> </p>\n</td>\n<td>\n<p> </p>\n</td>\n</tr><tr><td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n</tr></table>	\N	682182351	\N	\N
1657757231	\N	\N	\N	288412693	\N	\N
1006540214	\N	\N	\N	368434041	\N	\N
\.


--
-- Data for Name: Directory_Branch_Item; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Directory_Branch_Item" (id, title, created, updated, ordered) FROM stdin;
285787959	Филиал/Подразделение 1	1451298063	1451298063	1
1549580271	Филиал/Подразделение 2	1451298069	1451298069	2
1471302773	Филиал/Подразделение 3	1451298074	1451298074	3
\.


--
-- Data for Name: Directory_BusinessProcess_Item; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Directory_BusinessProcess_Item" (id, responsible, title, created, updated, ordered) FROM stdin;
1937134718	\N	Тестовый процесс 1	1450609865	1450609865	1
1355639494	\N	Тестовый процесс 2	1450609871	1450609871	2
1887907087	\N	Тестовый процесс 3	1450609878	1450609878	3
\.


--
-- Data for Name: Directory_BusinessProjects_Item; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Directory_BusinessProjects_Item" (id, title, created, updated, ordered) FROM stdin;
364846007	Тестовый проект компании 1	1451372935	1451372935	1
1286808364	Тестовый проект компании 2	1451372941	1451372941	2
1117149226	Тестовый проект компании 3	1451372947	1451372947	3
1422911556	Тестовый проект компании 4	1451372955	1451372955	4
\.


--
-- Data for Name: Directory_CalendarPlan_Simple; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Directory_CalendarPlan_Simple" (id, "DocumentRegulationsMP", "BusinessObjectRecordPolymorph", "DocumentRegulationsPV", date) FROM stdin;
\.


--
-- Data for Name: Directory_ControlAction_Universal; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Directory_ControlAction_Universal" (id, "CalendarPeriodMonth", description, created, updated, ordered) FROM stdin;
1056995091	47836635	fbgfgb	1454546741	1454546764	1
301171611	47836635	fgbfgbgnn	1454546764	1454546764	3
570027031	1464808265	dvfdv	1454546741	1454546764	2
\.


--
-- Data for Name: Directory_Deviation_PreCapa; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Directory_Deviation_PreCapa" (id, "CompanyStructureDepartment", description, created, updated, "approvedrisks_RiskManagementRiskApproved", "notapprovedrisks_RiskManagementRiskNotApproved", ordered) FROM stdin;
21899740	1641958276	Отклонение 1	1453930169	1453930497	\N	\N	\N
1365100333	1412798552	Отклонение 2	1453930169	1453930497	\N	\N	\N
2019794579	1641958276	ьрпарпам	1453932886	1453932934	\N	\N	\N
408480846	905805394	лраеламлгнам	1453932934	1453932934	\N	\N	\N
26480294	1641958276	bfgbfgbfgb	1456407338	1456407338	{1836802929}	{834990974}	1
1936997503	1641958276	ot	1456413437	1456413657	{1836802929}	{2139582129,2139582129,1003463425,2139582129,1003463425}	2
59619772	1658312748	222	1456413657	1456413657	\N	\N	3
1128633188	984894068	dfvfdvdf	1456417045	1456417301	{1836802929}	{298830768,298830768}	4
9264227	984894068	dfvdfv	1456417301	1456417301	\N	\N	5
285535151	\N	\N	1456429798	1456429798	\N	\N	6
\.


--
-- Data for Name: Directory_Fixedasset_Simple; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Directory_Fixedasset_Simple" (id, "DocumentProtocolVT", equipment, numberequipment, specification) FROM stdin;
150677583	244456918	\N	\N	\N
1172598804	244456918	\N	\N	\N
2010972262	244456918	\N	\N	\N
1824419816	244456918	\N	\N	\N
118329062	244456918	\N	\N	\N
1385627475	244456918	\N	\N	\N
1529729682	244456918	\N	\N	\N
1314476664	244456918	\N	\N	\N
\.


--
-- Data for Name: Directory_KindOfOperations_Item; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Directory_KindOfOperations_Item" (id, title, created, updated, ordered) FROM stdin;
512452820	Тестовое направление деятельности компании 1	1450614188	1450614188	1
1471555169	Тестовое направление деятельности компании 2	1450614193	1450614193	2
940475396	Тестовое направление деятельности компании 3	1450614198	1450614198	3
1400864147	Тестовое направление деятельности компании 4	1450614204	1450614204	4
\.


--
-- Data for Name: Directory_Materialbase_Simple; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Directory_Materialbase_Simple" (id, "DocumentRegulationsPV", "BusinessObjectRecordPolymorph", numberequipment, specification) FROM stdin;
\.


--
-- Data for Name: Directory_Media_Attributed; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Directory_Media_Attributed" (id, text, attachment, ordered) FROM stdin;
1051549032	fdvdfvf	/original/urn-Document-Contract-BW-591082645/KABINET-5131871.png	1
1638661325	\N	\N	2
73945746	\N	\N	3
2133954157	\N	\N	4
165119542	\N	\N	5
573823332	\N	\N	6
1251376077	\N	\N	7
1546767246	СХЕМА ОБОРУДОВАНИЯ	\N	8
873870228	\N	\N	9
\.


--
-- Data for Name: Directory_MissingPeople_Item; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Directory_MissingPeople_Item" (id, "ManagementPostIndividual", why, missingdate, created, updated, ordered) FROM stdin;
\.


--
-- Data for Name: Directory_Options_Simple; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Directory_Options_Simple" (id, "DocumentRegulationsPV", titleparametr, descriptionmethodic) FROM stdin;
\.


--
-- Data for Name: Directory_Replacement_Item; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Directory_Replacement_Item" (id, missing, replacement, why, missingdate, created, updated, ordered) FROM stdin;
\.


--
-- Data for Name: Directory_Responsible_Simple; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Directory_Responsible_Simple" (id, "DocumentRegulationsPV", "ManagementPostIndividual", typeofwork) FROM stdin;
\.


--
-- Data for Name: Directory_Responsibletwo_Simple; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Directory_Responsibletwo_Simple" (id, "DocumentProtocolVT", "ManagementPostIndividual", worktype) FROM stdin;
941088448	244456918	\N	\N
19146595	244456918	\N	dfvdfv
1027082818	244456918	\N	\N
1705936041	244456918	\N	dfvdfv
100279644	244456918	\N	\N
409293932	244456918	1816416714	dfvdfv
1903214402	244456918	\N	\N
597877467	244456918	\N	dfvdfv
\.


--
-- Data for Name: Directory_RiskProtocolSolution_SI; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Directory_RiskProtocolSolution_SI" (id, "DocumentProtocolSI", solutiononrisk, comment) FROM stdin;
\.


--
-- Data for Name: Directory_SLA_Item; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Directory_SLA_Item" (id, title) FROM stdin;
2033393079	Критерий SLA 1
1722975295	Критерий SLA 2
822410459	Критерий SLA 3
425196661	Критерий SLA 4
1872997529	Критерий SLA 5
\.


--
-- Data for Name: Directory_Solutionvariants_Simple; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Directory_Solutionvariants_Simple" (id, "DocumentProtocolKI", checkingresult, comment, "DocumentCorrectionCapa") FROM stdin;
\.


--
-- Data for Name: Directory_TechnicalTask_ForWorks; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Directory_TechnicalTask_ForWorks" (id, "DocumentTechnicalTaskForWorks", name, datebegin, dateend, volume) FROM stdin;
\.


--
-- Data for Name: Directory_TechnicalTask_Materials; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Directory_TechnicalTask_Materials" (id, name, quantity, date, materialdesription) FROM stdin;
388211603	mat1	2	2016-01-17	dfv
73398106	mat2	1	2016-01-21	dfvdvdvdf dfvfdvdf
\.


--
-- Data for Name: Directory_TechnicalTask_Works; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Directory_TechnicalTask_Works" (id, datebegin, dateend, materialdesription) FROM stdin;
\.


--
-- Data for Name: Directory_TenderBidder_Simple; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Directory_TenderBidder_Simple" (id, "CompanyLegalEntityCounterparty", "DocumentTenderExtended", docpermitsneed, commercialoffer, techvalidation, biddersolution, commentcounterparty, techvalidationcomment) FROM stdin;
\.


--
-- Data for Name: Directory_TenderPosition_Simple; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Directory_TenderPosition_Simple" (id, "DocumentTenderExtended", titleposition) FROM stdin;
\.


--
-- Data for Name: Document_Capa_Deviation; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Capa_Deviation" (id, created, updated, descriptiondeviation, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, basevisants, additionalvisants, "DMSDocumentUniversal", "RiskManagementRiskApproved_RiskManagementRiskApproved", ordered, "RiskManagementRiskNotApproved_RiskManagementRiskNotApproved", eventplace, eventtime) FROM stdin;
1405455698	1450217230	1450217230	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
761327733	1453040861	1453040861	Описание отклонения	Deviation-761327733	draft	UPN:DMS:Correction:CAPA:8617106	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	{urn:Management:Post:Individual:1724728515,urn:Management:Post:Individual:990258053,urn:Management:Post:Individual:275450805}	\N	\N	\N	\N	\N	\N	\N
412193703	1453053515	1453053515	Отклоненеие	Deviation-412193703	draft	UPN:DMS:Correction:CAPA:5420650	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	{urn:Management:Post:Individual:1724728515,urn:Management:Post:Individual:990258053,urn:Management:Post:Individual:275450805}	\N	\N	\N	\N	\N	\N	\N
961713773	1453055171	1453055171	Отк	Deviation-961713773	draft	UPN:DMS:Correction:CAPA:2417723	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	{urn:Management:Post:Individual:1724728515,urn:Management:Post:Individual:990258053,urn:Management:Post:Individual:275450805}	\N	\N	\N	\N	\N	\N	\N
297872527	1453976530	1453976530	ЛУ: п. 3.4.3:\n2.\tКомната приготовления растворов не имеет вытяжки, слива, столика для приготовления растворов; недостаточно места для хранения моечной машины, которая заносится через общий коридор через помещение склада.	Deviation-140660	CreateDraft	UPN:DMS:Correction:CAPA:3019707	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	913514955	\N	\N	\N	\N	\N
276608231	1453973804	1453973804	ОПИСАНИЕ ОТКЛОНЕНИЯ	Deviation-330618	Editing	UPN:DMS:Correction:CAPA:5391979	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	{urn:Management:Post:Individual:1724728515,urn:Management:Post:Individual:990258053,urn:Management:Post:Individual:275450805}	{urn:Management:Post:Individual:1118804000}	315753258	\N	\N	\N	\N	\N
123943761	1453975274	1453975274	ВЫЯВЛЕННОЕ ОТКЛОНЕНИЕ 1	Deviation-194242	Approving	UPN:DMS:Correction:CAPA:5255966	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	1877988158	\N	\N	\N	\N	\N
929622499	1454057217	1454057217	fdvdv	Deviation-858702	Editing	UPN:DMS:Correction:CAPA:4625548	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	369098829	\N	\N	\N	\N	\N
683381847	1454059017	1454059017	\N	\N	CreateDraft	UPN:DMS:Correction:CAPA:9261921	\N	{}	\N	urn:Actor:User:System:1	\N	t	f	f	f	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Claim_R_LSC; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Claim_R_LSC" (id, companyproject, "CompanyLegalEntityCounterparty", warehouse, "DocumentSolutionUniversal", "solutionvariants_DocumentSolutionUniversal", specialrequirement, attachments, purchaseorder, attachmentdocs, plancomingdate, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal", rtaken) FROM stdin;
98863735	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1453721959	\N	\N	\N	\N	\N
174515724	364846007	404906315	206674127	\N	{1688212046,1388824898}	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	{/original/urn-Document-Claim-R_LSC-174515724/1.jpg}	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	{/original/urn-Document-Claim-R_LSC-174515724/google-beta.png}	2016-01-28	R_LSC-174515724	draft	UPN:DMS:Claims:Claim:5824569	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453737284	1453737284	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
389910812	1286808364	115997801	10341110	\N	{1745142289,1284686991}	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	{/original/urn-Document-Claim-R_LSC-389910812/1.jpg,/original/urn-Document-Claim-R_LSC-389910812/dior.jpg}	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.\nhttp://local.bc/process/act/361497413http://local.bc/process/act/361497413http://local.bc/process/act/361497413http://local.bc/process/act/361497413	{/original/urn-Document-Claim-R_LSC-389910812/755310-R3L8T8D-1000-101.jpg}	2016-01-29	R_LSC-389910812	draft	UPN:DMS:Claims:Claim:9671868	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453736881	1453736881	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
\.


--
-- Data for Name: Document_Claim_R_LSD; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Claim_R_LSD" (id, companyproject, "CompanyLegalEntityCounterparty", warehouse, "DocumentSolutionUniversal", specialrequirement, attachments, pickingrequest, wmsdocs, planshipmentdate, cargoreceiver, reasonshipping, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "solutionvariants_DocumentSolutionUniversal", basevisants, additionalvisants, "DMSDocumentUniversal", rtaken) FROM stdin;
1597607518	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217230	\N	\N	\N	\N	\N	\N
7107518	364846007	404906315	206674127	1620678598	ПАКЕТ ДОКУМЕНТОВ ДЛЯ ОТГРУЗКИПАКЕТ ДОКУМЕНТОВ ДЛЯ ОТГРУЗКИПАКЕТ ДОКУМЕНТОВ ДЛЯ ОТГРУЗКИПАКЕТ ДОКУМЕНТОВ ДЛЯ ОТГРУЗКИПАКЕТ ДОКУМЕНТОВ ДЛЯ ОТГРУЗКИ	{/original/urn-Document-Claim-R_LSD-7107518/1.jpg}	98765432йцывапролдю	{/original/urn-Document-Claim-R_LSD-7107518/dior.jpg}	2016-01-31	ПАКЕТ ДОКУМЕНТОВ ДЛЯ ОТГРУЗКИПАКЕТ ДОКУМЕНТОВ ДЛЯ ОТГРУЗКИПАКЕТ ДОКУМЕНТОВ ДЛЯ ОТГРУЗКИПАКЕТ ДОКУМЕНТОВ ДЛЯ ОТГРУЗКИ	ПАКЕТ ДОКУМЕНТОВ ДЛЯ ОТГРУЗКИПАКЕТ ДОКУМЕНТОВ ДЛЯ ОТГРУЗКИПАКЕТ ДОКУМЕНТОВ ДЛЯ ОТГРУЗКИПАКЕТ ДОКУМЕНТОВ ДЛЯ ОТГРУЗКИПАКЕТ ДОКУМЕНТОВ ДЛЯ ОТГРУЗКИ	R_LSD-7107518	draft	UPN:DMS:Claims:Claim:5847531	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453739120	1453739120	{1620678598,516996784}	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
\.


--
-- Data for Name: Document_Claim_R_LSM; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Claim_R_LSM" (id, companyproject, "CompanyLegalEntityCounterparty", warehouse, "DocumentSolutionUniversal", specialrequirement, attachments, reason, goodsdocs, desireddate, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "solutionvariants_DocumentSolutionUniversal", basevisants, additionalvisants, "DMSDocumentUniversal", rtaken) FROM stdin;
643518962	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217230	\N	\N	\N	\N	\N	\N
943557515	1117149226	1156372664	1293144792	1655907378	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	{/original/urn-Document-Claim-R_LSM-943557515/755310-R3L8T8D-1000-101.jpg}	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	{/original/urn-Document-Claim-R_LSM-943557515/google-beta.png}	2016-01-31	R_LSM-943557515	draft	UPN:DMS:Claims:Claim:7442765	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453738730	1453738730	{1655907378,660511853}	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
\.


--
-- Data for Name: Document_Claim_R_LST; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Claim_R_LST" (id, companyproject, "CompanyLegalEntityCounterparty", warehouse, "DocumentSolutionUniversal", specialrequirement, attachments, customsdeclarationtype, customsclearancedocs, customsclearancedate, adddescription, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "solutionvariants_DocumentSolutionUniversal", basevisants, additionalvisants, "DMSDocumentUniversal", rtaken) FROM stdin;
68620352	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217230	\N	\N	\N	\N	\N	\N
640439056	1117149226	1156372664	1293144792	1359860492	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	{/original/urn-Document-Claim-R_LST-640439056/1.jpg}	2	{/original/urn-Document-Claim-R_LST-640439056/dior.jpg}	2016-01-28	Editing R_LST-640439056	R_LST-640439056	draft	UPN:DMS:Claims:Claim:3601632	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453737567	1453737567	{1359860492,786579161}	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
617532504	1117149226	1156372664	1293144792	\N	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	{/original/urn-Document-Claim-R_LST-617532504/1.jpg}	2	{/original/urn-Document-Claim-R_LST-617532504/dior.jpg}	2016-01-30	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	R_LST-617532504	draft	UPN:DMS:Claims:Claim:9265932	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453738589	1453738589	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
\.


--
-- Data for Name: Document_Claim_R_LSС; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Claim_R_LSС" (id, companyproject, "CompanyLegalEntityCounterparty", warehouse, "DocumentSolutionUniversal", "proposedsolutions_DocumentSolutionUniversal", specialrequirement, attachments, purchaseorder, attachmentdocs, plancomingdate, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated) FROM stdin;
316983193	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217230	\N
\.


--
-- Data for Name: Document_Claim_R_OQF; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Claim_R_OQF" (id, companyproject, "CompanyLegalEntityCounterparty", warehouse, "DocumentSolutionUniversal", responsibleold, warehouseold, responsiblenew, warehousenew, specialrequirement, attachments, codenumber, descriptionneed, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "solutionvariants_DocumentSolutionUniversal", basevisants, additionalvisants, "DMSDocumentUniversal", rtaken) FROM stdin;
551955508	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217230	\N	\N	\N	\N	\N	\N
299168040	1286808364	115997801	10341110	1167875494	1118804000	10341110	1138179996	1293144792	okokokokokokokokokokokokokokokokokokokokok	{/original/urn-Document-Claim-R_OQF-299168040/1.jpg}	okokokokokokokokokokokokokokokokokokokokokokokokokokok	okokokokokokokokokokokokokokokokokokokokokokokok	R_OQF-299168040	draft	UPN:DMS:Claims:Claim:8371091	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453740791	1453740791	{1167875494,1959136953}	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
\.


--
-- Data for Name: Document_Claim_R_OQR; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Claim_R_OQR" (id, companyproject, "CompanyLegalEntityCounterparty", warehouse, "DocumentSolutionUniversal", purchaseuser, specialrequirement, attachments, descriptionneed, purchasename, purchasequantity, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "solutionvariants_DocumentSolutionUniversal", basevisants, additionalvisants, "DMSDocumentUniversal", rtaken) FROM stdin;
455400454	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217230	\N	\N	\N	\N	\N	\N
735024877	1117149226	115997801	10341110	1387578668	1349687451	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	{/original/urn-Document-Claim-R_OQR-735024877/1.jpg}	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	94	R_OQR-735024877	draft	UPN:DMS:Claims:Claim:5547762	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453736318	1453736318	{1387578668,496568797}	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
349870542	364846007	404906315	206674127	718419272	1982566019	nononononononononononononononono	{/original/urn-Document-Claim-R_OQR-349870542/1.jpg}	nononononononononononononononono	nononononononononononononononononononononononononononononononono	34	\N	Reviewing	UPN:DMS:Claims:Claim:3051228	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	f	f	t	f	t	t	1453758849	1453758849	{1943402235,959441299,718419272}	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
\.


--
-- Data for Name: Document_Claim_R_PAD; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Claim_R_PAD" (id, companyproject, "CompanyLegalEntityCounterparty", warehouse, "DocumentSolutionUniversal", specialrequirement, attachments, software, descriptionneed, description, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "solutionvariants_DocumentSolutionUniversal", basevisants, additionalvisants, "DMSDocumentUniversal", rtaken) FROM stdin;
1742062035	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217230	\N	\N	\N	\N	\N	\N
936647525	1117149226	115997801	10341110	\N	guefog79guefog79guefog79guefog79guefog79guefog79guefog79guefog79	{"/original/urn-Document-Claim-R_PAD-936647525/2015-08-02-20-19-15 New Tab.png"}	guefog79guefog79guefog79guefog79guefog79guefog79guefog79guefog79	guefog79guefog79guefog79guefog79guefog79guefog79guefog79guefog79	guefog79guefog79guefog79guefog79guefog79guefog79guefog79guefog79	R_PAD-936647525	draft	UPN:DMS:Claims:Claim:7305471	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453209835	1453209835	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
156436774	1286808364	115997801	10341110	1093851318	citusdatacitusdatacitusdatacitusdata	{/original/urn-Document-Claim-R_PAD-156436774/1.jpg}	citusdata	citusdata	citusdata	R_PAD-832730	Reviewing	UPN:DMS:Claims:Claim:2324915	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	f	f	t	f	t	t	1453753543	1453753543	{1093851318}	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	676808974	\N
532005241	\N	\N	\N	\N	\N	\N	\N	\N	\N	R_PAD-532005241	draft	UPN:DMS:Claims:Claim:5319151	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453209966	1453209966	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
175444597	1117149226	1156372664	1293144792	1399815909	test	{"/original/urn-Document-Claim-R_PAD-175444597/2015-08-02-20-19-15 New Tab.png"}	Вложение отображать - минимум 1	Вложение отображать - минимум 1	Вложение отображать - минимум 1	R_PAD-175444597	draft	UPN:DMS:Claims:Claim:8025545	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453724488	1453724488	{1399815909,1306100958}	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
684721270	364846007	115997801	966042166	\N	dfvdfv	{}	fvdvdf	dfvdfv	dfvdfvd	R_PAD-278401	Editing	UPN:DMS:Claims:Claim:645276	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	f	f	f	f	\N	\N	1456307800	1456307800	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	949710509	\N
\.


--
-- Data for Name: Document_Claim_R_PAI; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Claim_R_PAI" (id, companyproject, "CompanyLegalEntityCounterparty", warehouse, "DocumentSolutionUniversal", "employees_ManagementPostIndividual", specialrequirement, attachments, software, descriptionneed, description, link, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "solutionvariants_DocumentSolutionUniversal", basevisants, additionalvisants, "DMSDocumentUniversal", rtaken) FROM stdin;
150017398	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217230	\N	\N	\N	\N	\N	\N
31519063	1117149226	1156372664	1293144792	1438182459	{2065101785,406611786}	Связать CapaApp, AttestationApp с Managed Process Execution / Inbox	{/original/urn-Document-Claim-R_PAI-31519063/1.jpg}	Связать CapaApp, AttestationApp с Managed Process Execution / Inbox	Связать CapaApp, AttestationApp с Managed Process Execution / Inbox	Связать CapaApp, AttestationApp с Managed Process Execution / Inbox	Связать CapaApp, AttestationApp с Managed Process Execution / Inbox	\N	Reviewing	UPN:DMS:Claims:Claim:6599018	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	f	f	t	f	t	t	1453755321	1453755321	{1438182459}	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
945543748	1117149226	1156372664	1293144792	1082747180	{1982566019,1349687451,1138179996}	PAI 3	{"/original/urn-Document-Claim-R_PAI-945543748/2015-08-02-20-19-15 New Tab.png"}	PAI 3	PAI 3	PAI 3	PAI 3	R_PAI-945543748	draft	UPN:DMS:Claims:Claim:1106561	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453733112	1453733112	{1082747180,303185365}	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
427623980	1117149226	1156372664	1293144792	1516320581	{921220994,990258053}	 Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect	{"/original/urn-Document-Claim-R_PAI-427623980/2015-08-02-20-19-15 New Tab.png"}	 Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect	 Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect	 Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect	 Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect	R_PAI-427623980	draft	UPN:DMS:Claims:Claim:3944679	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453208962	1453208962	{1516320581,1621009817}	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
212222104	1286808364	1156372664	1293144792	893995240	{1109817505}	http://bcstage.icebrg.net/inbox	{/original/urn-Document-Claim-R_PAI-212222104/1-2476805.jpg}	http://bcstage.icebrg.net/inbox	http://bcstage.icebrg.net/inbox	http://bcstage.icebrg.net/inbox	http://bcstage.icebrg.net/inbox	R_PAI-175820	Reviewing	UPN:DMS:Claims:Claim:3757030	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	f	f	t	f	t	t	1453927205	1453927205	{893995240,1911361363}	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	1375591503	\N
529249433	364846007	115997801	614895960	244777135	{1816416714}	вамвамва	{}	авмвам	вамва	вамва	авм	R_PAI-676237	Reviewing	UPN:DMS:Claims:Claim:3784142	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	f	f	t	f	t	t	1456404448	1456404448	{244777135}	{urn:Management:Post:Individual:1118804000}	\N	561392135	1
247202720	364846007	115997801	966042166	\N	{2065101785}	dvf	{}	test	dfvdfv	dfvdf	dfvdf	R_PAI-369465	Considering	UPN:DMS:Claims:Claim:4520326	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	f	f	f	f	\N	\N	1456397642	1456397642	{700481238}	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	1829200988	\N
\.


--
-- Data for Name: Document_Claim_R_PAT; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Claim_R_PAT" (id, companyproject, "CompanyLegalEntityCounterparty", warehouse, "DocumentSolutionUniversal", "hardwareuser_ManagementPostIndividual", specialrequirement, attachments, hardware, minimumrequirement, descriptionneed, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "solutionvariants_DocumentSolutionUniversal", basevisants, additionalvisants, "DMSDocumentUniversal", rtaken) FROM stdin;
56521947	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217230	\N	\N	\N	\N	\N	\N
739766084	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	R_PAT-739766084	draft	UPN:DMS:Claims:Claim:2437106	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453733776	1453733776	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
332193500	1286808364	115997801	10341110	561530900	{1724728515,1897084668}	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	{/original/urn-Document-Claim-R_PAT-332193500/1.jpg}	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	R_PAT-332193500	draft	UPN:DMS:Claims:Claim:1373033	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453733909	1453733909	{561530900,1054167158}	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
\.


--
-- Data for Name: Document_Claim_R_QDA; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Claim_R_QDA" (id, companyproject, "CompanyLegalEntityCounterparty", warehouse, "DocumentSolutionUniversal", auditwarehouse, auditcounterparty, "CalendarPeriodMonth", "solutionvariants_DocumentSolutionUniversal", specialrequirement, attachments, eventtype, dateprev, datenext, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal", rtaken) FROM stdin;
941252389	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1453721959	\N	\N	\N	\N	\N
548677890	1286808364	115997801	10341110	448722696	10341110	1156372664	2059004404	{448722696,1358291385}	СПЕЦИАЛЬНОЕ ТРЕБОВАНИЕСПЕЦИАЛЬНОЕ ТРЕБОВАНИЕСПЕЦИАЛЬНОЕ ТРЕБОВАНИЕСПЕЦИАЛЬНОЕ ТРЕБОВАНИЕ	{/original/urn-Document-Claim-R_QDA-548677890/1.jpg}	2	2016-01-30	2016-01-31	R_QDA-548677890	draft	UPN:DMS:Claims:Claim:2657429	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453740365	1453740365	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
\.


--
-- Data for Name: Document_Claim_R_QDC; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Claim_R_QDC" (id, companyproject, "CompanyLegalEntityCounterparty", warehouse, "DocumentSolutionUniversal", specialrequirement, attachments, initialsituation, changesdescription, expectedresult, link, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "solutionvariants_DocumentSolutionUniversal", basevisants, additionalvisants, "DMSDocumentUniversal", rtaken) FROM stdin;
189447620	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217230	\N	\N	\N	\N	\N	\N
940504138	364846007	404906315	206674127	610915775	ОЦЕНОЧНАЯ СТОИМОСТЬОЦЕНОЧНАЯ СТОИМОСТЬОЦЕНОЧНАЯ СТОИМОСТЬОЦЕНОЧНАЯ СТОИМОСТЬОЦЕНОЧНАЯ СТОИМОСТЬОЦЕНОЧНАЯ СТОИМОСТЬОЦЕНОЧНАЯ СТОИМОСТЬ	{/original/urn-Document-Claim-R_QDC-940504138/google-beta.png}	ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ	ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ	ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ	ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ	R_QDC-940504138	draft	UPN:DMS:Claims:Claim:9896791	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453739862	1453739862	{610915775,1473257722}	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
\.


--
-- Data for Name: Document_Claim_R_QDE; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Claim_R_QDE" (id, companyproject, "CompanyLegalEntityCounterparty", warehouse, "DocumentSolutionUniversal", "CalendarPeriodMonth", "trainingprogram_DocumentRegulationsTA", "student_ManagementPostIndividual", specialrequirement, attachments, reason, dateprev, datenext, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "solutionvariants_DocumentSolutionUniversal", basevisants, additionalvisants, "DMSDocumentUniversal", rtaken) FROM stdin;
985621029	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217230	\N	\N	\N	\N	\N	\N
499283193	1286808364	115997801	10341110	\N	1464808265	\N	{1109817505}	Editing R_RDD-109360131Editing R_RDD-109360131Editing R_RDD-109360131Editing R_RDD-109360131Editing R_RDD-109360131	{/original/urn-Document-Claim-R_QDE-499283193/1.jpg}	Editing R_RDD-109360131Editing R_RDD-109360131Editing R_RDD-109360131	2016-01-29	2016-01-27	R_QDE-499283193	draft	UPN:DMS:Claims:Claim:9103504	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453743313	1453743313	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
819529807	364846007	404906315	206674127	738220964	464841811	\N	{1816416714,272312991,1422803024,1118804000}	Editing\nsave/loadEditing\nsave/loadEditing\nsave/loadEditing\nsave/load	{/original/urn-Document-Claim-R_QDE-819529807/1.jpg}	Editing\nsave/loadEditing\nsave/loadEditing\nsave/loadEditing\nsave/loadEditing\nsave/load	2015-12-14	2016-02-15	\N	Reviewing	UPN:DMS:Claims:Claim:3453881	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	f	f	t	f	t	t	1453758092	1453758092	{738220964}	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
507411767	1286808364	115997801	10341110	1786744849	464841811	\N	{1816416714,1724728515}	и САРА на этапах Considering и САРА на этапах Considering и САРА на этапах Considering и САРА на этапах Considering 	{/original/urn-Document-Claim-R_QDE-507411767/1.jpg}	и САРА на этапах Considering и САРА на этапах Considering и САРА на этапах Considering и САРА на этапах Considering 	2016-01-07	2016-03-07	\N	Reviewing	UPN:DMS:Claims:Claim:7269951	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	f	f	t	f	t	t	1453757403	1453757403	{1786744849,2133186783}	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
100926096	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	R_QDE-933192	Editing	UPN:DMS:Claims:Claim:636048	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	f	f	f	f	\N	\N	1453757853	1453757853	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	727645316	\N
\.


--
-- Data for Name: Document_Claim_R_QDM; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Claim_R_QDM" (id, companyproject, "CompanyLegalEntityCounterparty", warehouse, "DocumentSolutionUniversal", "BusinessObjectRecordPolymorph", specialrequirement, attachments, descriptionneed, eventtype, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "solutionvariants_DocumentSolutionUniversal", basevisants, additionalvisants, "DMSDocumentUniversal", rtaken) FROM stdin;
1374861633	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217230	\N	\N	\N	\N	\N	\N
187979529	1286808364	115997801	10341110	217573489	643421900	ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ	{/original/urn-Document-Claim-R_QDM-187979529/google-beta.png}	ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ	2	R_QDM-187979529	draft	UPN:DMS:Claims:Claim:3045784	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453740086	1453740086	{217573489,1835122629}	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
\.


--
-- Data for Name: Document_Claim_R_QDА; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Claim_R_QDА" (id, companyproject, "CompanyLegalEntityCounterparty", warehouse, "DocumentSolutionUniversal", auditwarehouse, auditcounterparty, "CalendarPeriodMonth", "proposedsolutions_DocumentSolutionUniversal", specialrequirement, attachments, eventtype, dateprev, datenext, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated) FROM stdin;
1108246143	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217230	\N
\.


--
-- Data for Name: Document_Claim_R_RDC; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Claim_R_RDC" (id, companyproject, "CompanyLegalEntityCounterparty", warehouse, "DocumentSolutionUniversal", "DirectoryBusinessProcessItem", scaleapplication, specialrequirement, attachments, descriptionneed, regulatingdocument, docname, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "solutionvariants_DocumentSolutionUniversal", basevisants, additionalvisants, "DMSDocumentUniversal", rtaken) FROM stdin;
654588946	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217230	\N	\N	\N	\N	\N	\N
969994384	1286808364	115997801	10341110	\N	1355639494	643421900	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	{/original/urn-Document-Claim-R_RDC-969994384/1.jpg}	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	urn:Document:Claim:R_PAD:175444597	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	R_RDC-969994384	draft	UPN:DMS:Claims:Claim:4318843	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453736518	1453736518	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
\.


--
-- Data for Name: Document_Claim_R_RDD; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Claim_R_RDD" (id, companyproject, "CompanyLegalEntityCounterparty", warehouse, "DocumentSolutionUniversal", purchaseuser, specialrequirement, attachments, descriptionneed, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "solutionvariants_DocumentSolutionUniversal", basevisants, additionalvisants, "DMSDocumentUniversal", "DocumentCopyControled", rtaken) FROM stdin;
1385955136	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217230	\N	\N	\N	\N	\N	\N	\N
109360131	1422911556	115997801	10341110	\N	1897084668	okokokokokokokokokokokokokokokokokok	{/original/urn-Document-Claim-R_RDD-109360131/1.jpg}	Editing R_RDD-109360131Editing R_RDD-109360131Editing R_RDD-109360131Editing R_RDD-109360131	R_RDD-109360131	draft	UPN:DMS:Claims:Claim:3045817	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453743191	1453743191	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Claim_R_RDE; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Claim_R_RDE" (id, companyproject, "CompanyLegalEntityCounterparty", warehouse, "DocumentSolutionUniversal", specialrequirement, attachments, regulatingdocument, reasonforchange, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "solutionvariants_DocumentSolutionUniversal", basevisants, additionalvisants, "DMSDocumentUniversal", rtaken) FROM stdin;
1752422178	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217230	\N	\N	\N	\N	\N	\N
384736647	1117149226	1156372664	1293144792	\N	okokokokokokokokok	{/original/urn-Document-Claim-R_RDE-384736647/1.jpg}	urn:Document:Claim:R_UPL:154268757	okokokokokokokokokokokok	R_RDE-384736647	draft	UPN:DMS:Claims:Claim:8864496	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453743116	1453743116	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
\.


--
-- Data for Name: Document_Claim_R_TD; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Claim_R_TD" (id, companyproject, "CompanyLegalEntityCounterparty", warehouse, "DocumentSolutionUniversal", specialrequirement, attachments, goal, purchasetype, purchasename, purchaseparam, priority, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "solutionvariants_DocumentSolutionUniversal", basevisants, additionalvisants, "DMSDocumentUniversal", rtaken) FROM stdin;
91426826	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217230	\N	\N	\N	\N	\N	\N
589138124	1286808364	115997801	10341110	1349960593	okokokokokokokokokokokokokokokokokokokokokokokokokokokokokokokokok	{/original/urn-Document-Claim-R_TD-589138124/1.jpg}	\N	2	okokokokokokokokokokokokokokokokokokokokokokokok	okokokokokokokokokokokok	okokokokokokokokokokokok	R_TD-589138124	draft	UPN:DMS:Claims:Claim:5239063	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453742853	1453742853	{1662940975,1349960593}	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
381529737	364846007	1156372664	1293144792	2091060584	СРОЧНО!	{/original/urn-Document-Claim-R_TD-381529737/eco_leaves_160110-8057260.jpg}	\N	2	Отремонтировать забор	такой же	-	R_TD-437968	Reviewing	UPN:DMS:Claims:Claim:5886109	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	f	f	t	f	t	t	1453931835	1453931835	{2091060584,1577756981}	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	464123760	\N
\.


--
-- Data for Name: Document_Claim_R_UPC; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Claim_R_UPC" (id, companyproject, "CompanyLegalEntityCounterparty", warehouse, "DocumentSolutionUniversal", mailuser, "skduser_ManagementPostIndividual", specialrequirement, attachments, descriptionneed, permissionsnew, processtype, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "solutionvariants_DocumentSolutionUniversal", permissionscurrent, basevisants, additionalvisants, "DMSDocumentUniversal", rtaken) FROM stdin;
126993802	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217230	\N	\N	\N	\N	\N	\N	\N
466849175	1117149226	1156372664	1293144792	1327072402	1109817505	\N	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	{/original/urn-Document-Claim-R_UPC-466849175/755310-R3L8T8D-1000-101.jpg}	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	2	R_UPC-466849175	draft	UPN:DMS:Claims:Claim:543151	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453735922	1453735922	{1327072402,446591594}	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
\.


--
-- Data for Name: Document_Claim_R_UPE; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Claim_R_UPE" (id, companyproject, "CompanyLegalEntityCounterparty", warehouse, "DocumentSolutionUniversal", mailusernew, mailuserold, specialrequirement, attachments, descriptionneed, login, signature, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "solutionvariants_DocumentSolutionUniversal", basevisants, additionalvisants, "DMSDocumentUniversal", rtaken) FROM stdin;
2035053870	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217230	\N	\N	\N	\N	\N	\N
812640775	1117149226	1156372664	1293144792	\N	475767870	272312991	Вы не должны наказывать людей за то, что они рискуют или ошибаются. Вы хотите, чтобы они не боялись экспериментировать, ошибаться и извлекать уроки из этого. По крайней мере, пока они не разрушают ваш бизнес. Если они хотят экспериментировать на таком уровне, то пусть делают это со своим бизнесом.	{"/original/urn-Document-Claim-R_UPE-812640775/2015-08-02-20-19-15 New Tab.png"}	Вы не должны наказывать людей за то, что они рискуют или ошибаются. Вы хотите, чтобы они не боялись экспериментировать, ошибаться и извлекать уроки из этого. По крайней мере, пока они не разрушают ваш бизнес. Если они хотят экспериментировать на таком уровне, то пусть делают это со своим бизнесом.	3asedt	Вы не должны наказывать людей за то, что они рискуют или ошибаются. Вы хотите, чтобы они не боялись экспериментировать, ошибаться и извлекать уроки из этого. По крайней мере, пока они не разрушают ваш бизнес. Если они хотят экспериментировать на таком уровне, то пусть делают это со своим бизнесом.	R_UPE-812640775	draft	UPN:DMS:Claims:Claim:2790432	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453204915	1453204915	{699090876,1744527723}	{urn:Management:Post:Individual:1982566019}	\N	\N	\N
219008952	1286808364	115997801	10341110	1847460396	1118804000	1138179996	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	{/original/urn-Document-Claim-R_UPE-219008952/1.jpg}	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	jhgfdszxcvbnm	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	R_UPE-219008952	draft	UPN:DMS:Claims:Claim:7937134	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453734087	1453734087	{1986557686,1847460396}	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
543056065	1117149226	1156372664	1293144792	771180076	1724728515	275450805	Вы не должны наказывать людей за то, что они рискуют или ошибаются. Вы хотите, чтобы они не боялись экспериментировать, ошибаться и извлекать уроки из этого. По крайней мере, пока они не разрушают ваш бизнес. Если они хотят экспериментировать на таком уровне, то пусть делают это со своим бизнесом.	{"/original/urn-Document-Claim-R_UPE-543056065/2015-08-02-20-19-15 New Tab.png"}	Вы не должны наказывать людей за то, что они рискуют или ошибаются. Вы хотите, чтобы они не боялись экспериментировать, ошибаться и извлекать уроки из этого. По крайней мере, пока они не разрушают ваш бизнес. Если они хотят экспериментировать на таком уровне, то пусть делают это со своим бизнесом.	jhgfdx	Вы не должны наказывать людей за то, что они рискуют или ошибаются. Вы хотите, чтобы они не боялись экспериментировать, ошибаться и извлекать уроки из этого. По крайней мере, пока они не разрушают ваш бизнес. Если они хотят экспериментировать на таком уровне, то пусть делают это со своим бизнесом.	R_UPE-543056065	draft	UPN:DMS:Claims:Claim:122188	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453205718	1453205718	{1798786892,771180076}	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
\.


--
-- Data for Name: Document_Claim_R_UPI; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Claim_R_UPI" (id, companyproject, "CompanyLegalEntityCounterparty", warehouse, "DocumentSolutionUniversal", internetuser, specialrequirement, attachments, descriptionneed, link, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "solutionvariants_DocumentSolutionUniversal", basevisants, additionalvisants, "DMSDocumentUniversal", rtaken) FROM stdin;
666942908	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217230	\N	\N	\N	\N	\N	\N
114746734	1117149226	1156372664	1293144792	1002074943	1118804000	okokokokokokokokokokokokokokokokokokokokok	{/original/urn-Document-Claim-R_UPI-114746734/1.jpg}	okokokokokokokokokokokokokokokokokok	okokokokokokokokokokokokokokokokokokokokok	R_UPI-114746734	draft	UPN:DMS:Claims:Claim:5288786	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453741845	1453741845	{1002074943,964151484}	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
\.


--
-- Data for Name: Document_Claim_R_UPK; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Claim_R_UPK" (id, companyproject, "CompanyLegalEntityCounterparty", warehouse, "DocumentSolutionUniversal", printuser, specialrequirement, attachments, descriptionneed, printname, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "solutionvariants_DocumentSolutionUniversal", basevisants, additionalvisants, "DMSDocumentUniversal", rtaken) FROM stdin;
402009327	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217230	\N	\N	\N	\N	\N	\N
495515307	1117149226	1156372664	1293144792	284778779	1138179996	okokokokokokokokokokokokokokokokokokokokok	{/original/urn-Document-Claim-R_UPK-495515307/1.jpg}	okokokokokokokokokokokokokokokokokokokokokokokok	okokokokokokokokokokokokokokokokokokokokokokokok	R_UPK-495515307	draft	UPN:DMS:Claims:Claim:1523491	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453742596	1453742596	{284778779,1464142677}	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
179230462	1117149226	1156372664	1293144792	871593845	697130132	uyfdxcvbn	{/original/urn-Document-Claim-R_UPK-179230462/2504865-6874878274-Black-1790237.jpg}	hgfdsxcvbn	uytredsxcvbn	R_UPK-652477	Reviewing	UPN:DMS:Claims:Claim:7693661	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	f	f	t	f	t	t	1453929545	1453929545	{871593845,2092790685}	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	573567840	\N
434677776	1286808364	1156372664	1293144792	\N	1109817505	dytfujlkm	{}	juytresdfcv	uytrdfc	R_UPK-143510	Editing	UPN:DMS:Claims:Claim:3272093	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	f	f	f	f	\N	\N	1453974953	1453974953	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	1806071024	\N
\.


--
-- Data for Name: Document_Claim_R_UPL; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Claim_R_UPL" (id, companyproject, "CompanyLegalEntityCounterparty", warehouse, "DocumentSolutionUniversal", recipient, specialrequirement, attachments, descriptionneed, materialquantity, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "solutionvariants_DocumentSolutionUniversal", permissionscurrent, basevisants, additionalvisants, "DMSDocumentUniversal", rtaken) FROM stdin;
994331634	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217230	\N	\N	\N	\N	\N	\N	\N
154268757	1117149226	1156372664	1293144792	2044463173	1118804000	okokokokokokokokokokokokokokokokokok	{/original/urn-Document-Claim-R_UPL-154268757/google-beta.png}	okokokokokokokokok	123456	R_UPL-154268757	draft	UPN:DMS:Claims:Claim:2972942	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453742077	1453742077	{2044463173,461710851}	okokokokokokokokok	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
\.


--
-- Data for Name: Document_Claim_R_UPP; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Claim_R_UPP" (id, companyproject, "CompanyLegalEntityCounterparty", warehouse, "DocumentSolutionUniversal", specialrequirement, attachments, descriptionneed, permissionnewsdescription, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "solutionvariants_DocumentSolutionUniversal", permissionscurrent, basevisants, additionalvisants, "DMSDocumentUniversal", rtaken) FROM stdin;
404496447	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217230	\N	\N	\N	\N	\N	\N	\N
681339097	1286808364	115997801	10341110	321956214	test 1	{"/original/urn-Document-Claim-R_UPP-681339097/2015-08-02-20-19-15 New Tab.png"}	test 1	test 1	R_UPP-681339097	draft	UPN:DMS:Claims:Claim:1201089	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	t	f	f	f	\N	\N	1453731397	1453731397	{321956214,648263024}	test 1	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
869176728	1117149226	1156372664	1293144792	\N	Cyber	{/original/urn-Document-Claim-R_UPP-869176728/1-7534073.jpg}	Cyber	Cyber	R_UPP-346724	Considering	UPN:DMS:Claims:Claim:3164715	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	f	f	f	f	\N	\N	1453928869	1453928869	{1441243850,1050262913}	Cyber	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	1939589340	\N
\.


--
-- Data for Name: Document_Complaint_C_IS; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Complaint_C_IS" (id, fromclient, stillactual, "CompanyLegalEntityCounterparty", warehouse, object, datestart, dateend, description, attachments, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal") FROM stdin;
1642560186	0	0	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N
21836	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-21836	draft	UPN:ClaimsManagement:Claims:Claim:759349	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450864971	1450864971	\N	\N	\N
9090	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-9090	draft	UPN:ClaimsManagement:Claims:Claim:3791135	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450869058	1450869058	\N	\N	\N
16499	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-16499	draft	UPN:ClaimsManagement:Claims:Claim:5872819	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450868082	1450868082	\N	\N	\N
66241	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-66241	draft	UPN:ClaimsManagement:Claims:Claim:245624	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450865112	1450865112	\N	\N	\N
63360	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-63360	draft	UPN:ClaimsManagement:Claims:Claim:825695	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450870427	1450870427	\N	\N	\N
62983	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-62983	draft	UPN:ClaimsManagement:Claims:Claim:2694902	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450869582	1450869582	\N	\N	\N
91020	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-91020	draft	UPN:ClaimsManagement:Claims:Claim:743529	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450865201	1450865201	\N	\N	\N
56734	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-56734	draft	UPN:ClaimsManagement:Claims:Claim:7420712	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450868234	1450868234	\N	\N	\N
53223	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-53223	draft	UPN:ClaimsManagement:Claims:Claim:7135477	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450865300	1450865300	\N	\N	\N
63062	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-63062	draft	UPN:ClaimsManagement:Claims:Claim:2315055	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450869118	1450869118	\N	\N	\N
94760	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-94760	draft	UPN:ClaimsManagement:Claims:Claim:4693529	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450868325	1450868325	\N	\N	\N
67441	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-67441	draft	UPN:ClaimsManagement:Claims:Claim:2217010	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450865336	1450865336	\N	\N	\N
34392	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-34392	draft	UPN:ClaimsManagement:Claims:Claim:875876	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450869868	1450869868	\N	\N	\N
98635	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-98635	draft	UPN:ClaimsManagement:Claims:Claim:3776262	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450867099	1450867099	\N	\N	\N
56109	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-56109	draft	UPN:ClaimsManagement:Claims:Claim:8417076	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450868624	1450868624	\N	\N	\N
19027	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-19027	draft	UPN:ClaimsManagement:Claims:Claim:225488	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450867989	1450867989	\N	\N	\N
49977	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-49977	draft	UPN:ClaimsManagement:Claims:Claim:8398071	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450869197	1450869197	\N	\N	\N
66698	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-66698	draft	UPN:ClaimsManagement:Claims:Claim:9984011	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450869606	1450869606	\N	\N	\N
88178	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-88178	draft	UPN:ClaimsManagement:Claims:Claim:517375	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450868063	1450868063	\N	\N	\N
2089	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-2089	draft	UPN:ClaimsManagement:Claims:Claim:3846600	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450868769	1450868769	\N	\N	\N
86483	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-86483	draft	UPN:ClaimsManagement:Claims:Claim:3441443	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450870317	1450870317	\N	\N	\N
71958	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-71958	draft	UPN:ClaimsManagement:Claims:Claim:6528877	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450869248	1450869248	\N	\N	\N
40357	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-40357	draft	UPN:ClaimsManagement:Claims:Claim:7638302	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450868953	1450868953	\N	\N	\N
64635	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-64635	draft	UPN:ClaimsManagement:Claims:Claim:4675565	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450870724	1450870724	\N	\N	\N
29558	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-29558	draft	UPN:ClaimsManagement:Claims:Claim:4693696	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450869631	1450869631	\N	\N	\N
1957	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-1957	draft	UPN:ClaimsManagement:Claims:Claim:1691539	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450869446	1450869446	\N	\N	\N
54529	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-54529	draft	UPN:ClaimsManagement:Claims:Claim:8471236	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450869926	1450869926	\N	\N	\N
76956	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-76956	draft	UPN:ClaimsManagement:Claims:Claim:3025769	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450869822	1450869822	\N	\N	\N
56873	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-56873	draft	UPN:ClaimsManagement:Claims:Claim:7942146	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450877681	1450877681	\N	\N	\N
84743	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-84743	draft	UPN:ClaimsManagement:Claims:Claim:9429111	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450870374	1450870374	\N	\N	\N
13052	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-13052	draft	UPN:ClaimsManagement:Claims:Claim:8701847	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450870086	1450870086	\N	\N	\N
36110	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-36110	draft	UPN:ClaimsManagement:Claims:Claim:1287626	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450870642	1450870642	\N	\N	\N
57060	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-57060	draft	UPN:ClaimsManagement:Claims:Claim:1770483	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450875778	1450875778	\N	\N	\N
44509	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-44509	draft	UPN:ClaimsManagement:Claims:Claim:2492252	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450875556	1450875556	\N	\N	\N
77031	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-77031	draft	UPN:ClaimsManagement:Claims:Claim:4325640	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450874935	1450874935	\N	\N	\N
49908	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-49908	draft	UPN:ClaimsManagement:Claims:Claim:4273351	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450876993	1450876993	\N	\N	\N
37184	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-37184	draft	UPN:ClaimsManagement:Claims:Claim:4161508	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450879670	1450879670	\N	\N	\N
92904	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-92904	draft	UPN:ClaimsManagement:Claims:Claim:6472509	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450879838	1450879838	\N	\N	\N
64970	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-64970	draft	UPN:ClaimsManagement:Claims:Claim:2746912	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450880930	1450880930	\N	\N	\N
10565	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-10565	draft	UPN:ClaimsManagement:Claims:Claim:9446614	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450881368	1450881368	\N	\N	\N
3969	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-3969	draft	UPN:ClaimsManagement:Claims:Claim:2767499	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450881440	1450881440	\N	\N	\N
58462	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-58462	draft	UPN:DMS:Complaints:Complaint:7560639	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450898547	1450898547	\N	\N	\N
36227	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-36227	draft	UPN:ClaimsManagement:Claims:Claim:4841970	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450881523	1450881523	\N	\N	\N
31051	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-31051	draft	UPN:DMS:Complaints:Complaint:7580197	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450885694	1450885694	\N	\N	\N
64929	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-64929	draft	UPN:DMS:Complaints:Complaint:4957654	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450883614	1450883614	\N	\N	\N
11638	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-11638	draft	UPN:ClaimsManagement:Claims:Claim:917029	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450881736	1450881736	\N	\N	\N
20591	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-20591	draft	UPN:DMS:Complaints:Complaint:7173313	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450886246	1450886246	\N	\N	\N
47985	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-47985	draft	UPN:ClaimsManagement:Claims:Claim:7548071	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450881993	1450881993	\N	\N	\N
21738	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-21738	draft	UPN:DMS:Complaints:Complaint:2464357	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450884454	1450884454	\N	\N	\N
89829	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-89829	draft	UPN:DMS:Complaints:Complaint:6186459	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450887551	1450887551	\N	\N	\N
81620	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-81620	draft	UPN:ClaimsManagement:Claims:Claim:6390124	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450882150	1450882150	\N	\N	\N
75137	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-75137	draft	UPN:DMS:Complaints:Complaint:6980199	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450885760	1450885760	\N	\N	\N
22787	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-22787	draft	UPN:DMS:Complaints:Complaint:4677370	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450884478	1450884478	\N	\N	\N
64861	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-64861	draft	UPN:ClaimsManagement:Claims:Claim:5196360	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450882209	1450882209	\N	\N	\N
38214	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-38214	draft	UPN:ClaimsManagement:Claims:Claim:427384	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450882265	1450882265	\N	\N	\N
30557	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-30557	draft	UPN:DMS:Complaints:Complaint:1562453	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450886326	1450886326	\N	\N	\N
95213	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-95213	draft	UPN:DMS:Complaints:Complaint:744213	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450884517	1450884517	\N	\N	\N
69746	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-69746	draft	UPN:ClaimsManagement:Claims:Claim:6613373	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450882686	1450882686	\N	\N	\N
91441	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-91441	draft	UPN:DMS:Complaints:Complaint:5462760	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450883330	1450883330	\N	\N	\N
1447	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-1447	draft	UPN:DMS:Complaints:Complaint:4033133	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450886077	1450886077	\N	\N	\N
35877	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-35877	draft	UPN:DMS:Complaints:Complaint:3231110	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450883602	1450883602	\N	\N	\N
22554	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-22554	draft	UPN:DMS:Complaints:Complaint:8496514	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450884546	1450884546	\N	\N	\N
57118	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-57118	draft	UPN:DMS:Complaints:Complaint:3914859	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450898622	1450898622	\N	\N	\N
44371	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-44371	draft	UPN:DMS:Complaints:Complaint:5101193	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450886137	1450886137	\N	\N	\N
8567	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-8567	draft	UPN:DMS:Complaints:Complaint:7483572	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450884618	1450884618	\N	\N	\N
48255	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-48255	draft	UPN:DMS:Complaints:Complaint:1633643	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450888117	1450888117	\N	\N	\N
84428	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-84428	draft	UPN:DMS:Complaints:Complaint:2785958	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450886375	1450886375	\N	\N	\N
17962	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-17962	draft	UPN:DMS:Complaints:Complaint:3645147	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450886219	1450886219	\N	\N	\N
73805	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-73805	draft	UPN:DMS:Complaints:Complaint:4409964	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450898615	1450898615	\N	\N	\N
54591	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-54591	draft	UPN:DMS:Complaints:Complaint:9261544	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450898569	1450898569	\N	\N	\N
4012	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-4012	draft	UPN:DMS:Complaints:Complaint:1323824	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450886693	1450886693	\N	\N	\N
67702	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-67702	draft	UPN:DMS:Complaints:Complaint:1019143	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450898588	1450898588	\N	\N	\N
1587	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-1587	draft	UPN:DMS:Complaints:Complaint:8684004	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450892139	1450892139	\N	\N	\N
22909	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-22909	draft	UPN:DMS:Complaints:Complaint:7644179	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450898605	1450898605	\N	\N	\N
58534	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-58534	draft	UPN:DMS:Complaints:Complaint:2331956	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450898575	1450898575	\N	\N	\N
27715	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-27715	draft	UPN:DMS:Complaints:Complaint:4640521	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450898593	1450898593	\N	\N	\N
14269	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-14269	draft	UPN:DMS:Complaints:Complaint:1468052	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450898630	1450898630	\N	\N	\N
7220	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-7220	draft	UPN:DMS:Complaints:Complaint:9781023	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450898643	1450898643	\N	\N	\N
3249	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-3249	draft	UPN:DMS:Complaints:Complaint:7396458	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450898667	1450898667	\N	\N	\N
67858	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-67858	draft	UPN:DMS:Complaints:Complaint:3827684	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450898680	1450898680	\N	\N	\N
39565	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-39565	draft	UPN:DMS:Complaints:Complaint:4795490	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450898828	1450898828	\N	\N	\N
922098016	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-922098016	draft	UPN:DMS:Complaints:Complaint:2128860	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450898999	1450898999	\N	\N	\N
249792837	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-249792837	draft	UPN:DMS:Complaints:Complaint:9118467	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450899657	1450899657	\N	\N	\N
263679087	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-263679087	draft	UPN:DMS:Complaints:Complaint:8202083	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450899799	1450899799	\N	\N	\N
675287481	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-675287481	draft	UPN:DMS:Complaints:Complaint:7998716	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450901759	1450901759	\N	\N	\N
922866535	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-922866535	draft	UPN:DMS:Complaints:Complaint:1741005	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450901815	1450901815	\N	\N	\N
271561372	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-271561372	draft	UPN:DMS:Complaints:Complaint:4452688	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450899864	1450899864	\N	\N	\N
953231224	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-953231224	draft	UPN:DMS:Complaints:Complaint:2934813	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450906466	1450906466	\N	\N	\N
834561605	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-834561605	draft	UPN:DMS:Complaints:Complaint:6149254	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450899951	1450899951	\N	\N	\N
363289261	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-363289261	draft	UPN:DMS:Complaints:Complaint:9479513	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450954942	1450954942	\N	\N	\N
129572852	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-129572852	draft	UPN:DMS:Complaints:Complaint:5626264	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450906088	1450906088	\N	\N	\N
766718472	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-766718472	draft	UPN:DMS:Complaints:Complaint:3598382	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450899973	1450899973	\N	\N	\N
539382357	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-539382357	draft	UPN:DMS:Complaints:Complaint:5180005	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450945238	1450945238	\N	\N	\N
4213851	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-4213851	draft	UPN:DMS:Complaints:Complaint:7437949	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450944310	1450944310	\N	\N	\N
219205463	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-219205463	draft	UPN:DMS:Complaints:Complaint:1575570	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450900016	1450900016	\N	\N	\N
997674428	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-997674428	draft	UPN:DMS:Complaints:Complaint:1496905	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450906136	1450906136	\N	\N	\N
808202689	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-808202689	draft	UPN:DMS:Complaints:Complaint:3789574	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450900460	1450900460	\N	\N	\N
792364969	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-792364969	draft	UPN:DMS:Complaints:Complaint:6176326	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450953948	1450953948	\N	\N	\N
283939422	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-283939422	draft	UPN:DMS:Complaints:Complaint:4388727	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450906222	1450906222	\N	\N	\N
935696749	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-935696749	draft	UPN:DMS:Complaints:Complaint:2745124	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450900977	1450900977	\N	\N	\N
315669150	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-315669150	draft	UPN:DMS:Complaints:Complaint:5904119	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450952839	1450952839	\N	\N	\N
751075655	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-751075655	draft	UPN:DMS:Complaints:Complaint:7126240	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450944590	1450944590	\N	\N	\N
595781658	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-595781658	draft	UPN:DMS:Complaints:Complaint:6591035	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450901041	1450901041	\N	\N	\N
888409186	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-888409186	draft	UPN:DMS:Complaints:Complaint:2817299	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450945730	1450945730	\N	\N	\N
80909133	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-80909133	draft	UPN:DMS:Complaints:Complaint:2208727	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450906288	1450906288	\N	\N	\N
360631124	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-360631124	draft	UPN:DMS:Complaints:Complaint:302266	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450944635	1450944635	\N	\N	\N
856686682	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-856686682	draft	UPN:DMS:Complaints:Complaint:71898	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450906335	1450906335	\N	\N	\N
309600675	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-309600675	draft	UPN:DMS:Complaints:Complaint:7480239	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450953771	1450953771	\N	\N	\N
386934948	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-386934948	draft	UPN:DMS:Complaints:Complaint:629497	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450951551	1450951551	\N	\N	\N
540258970	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-540258970	draft	UPN:DMS:Complaints:Complaint:5933902	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450945173	1450945173	\N	\N	\N
40360217	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-40360217	draft	UPN:DMS:Complaints:Complaint:5462463	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450953673	1450953673	\N	\N	\N
695424354	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-695424354	draft	UPN:DMS:Complaints:Complaint:3434520	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450954810	1450954810	\N	\N	\N
250212331	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-250212331	draft	UPN:DMS:Complaints:Complaint:3194869	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450952386	1450952386	\N	\N	\N
414946134	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-414946134	draft	UPN:DMS:Complaints:Complaint:7357395	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450953808	1450953808	\N	\N	\N
536216930	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-536216930	draft	UPN:DMS:Complaints:Complaint:579895	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450953730	1450953730	\N	\N	\N
202757614	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-202757614	draft	UPN:DMS:Complaints:Complaint:9007919	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450954201	1450954201	\N	\N	\N
347186241	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-347186241	draft	UPN:DMS:Complaints:Complaint:1624862	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450955247	1450955247	\N	\N	\N
893207518	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-893207518	draft	UPN:DMS:Complaints:Complaint:1589143	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450955009	1450955009	\N	\N	\N
355598969	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-355598969	draft	UPN:DMS:Complaints:Complaint:7799128	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450955075	1450955075	\N	\N	\N
517273015	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-517273015	draft	UPN:DMS:Complaints:Complaint:348086	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450955303	1450955303	\N	\N	\N
380994748	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-380994748	draft	UPN:DMS:Complaints:Complaint:7831524	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450955422	1450955422	\N	\N	\N
467845286	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-467845286	draft	UPN:DMS:Complaints:Complaint:5794799	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450955452	1450955452	\N	\N	\N
296244861	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-296244861	draft	UPN:DMS:Complaints:Complaint:7239917	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450955738	1450955738	\N	\N	\N
524493602	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-524493602	draft	UPN:DMS:Complaints:Complaint:8142503	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450955849	1450955849	\N	\N	\N
629503030	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-629503030	draft	UPN:DMS:Complaints:Complaint:4438120	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450990116	1450990116	\N	\N	\N
156492661	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-156492661	draft	UPN:DMS:Complaints:Complaint:3722156	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450955913	1450955913	\N	\N	\N
162937498	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-162937498	draft	UPN:DMS:Complaints:Complaint:3632304	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450963774	1450963774	\N	\N	\N
205362102	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-205362102	draft	UPN:DMS:Complaints:Complaint:6158159	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450956038	1450956038	\N	\N	\N
421634690	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-421634690	draft	UPN:DMS:Complaints:Complaint:4712634	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450983353	1450983353	\N	\N	\N
640073338	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-640073338	draft	UPN:DMS:Complaints:Complaint:3736827	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450988335	1450988335	\N	\N	\N
374064828	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-374064828	draft	UPN:DMS:Complaints:Complaint:3881414	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450957183	1450957183	\N	\N	\N
627399019	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-627399019	draft	UPN:DMS:Complaints:Complaint:4125046	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450976899	1450976899	\N	\N	\N
624426997	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-624426997	draft	UPN:DMS:Complaints:Complaint:3107594	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450957900	1450957900	\N	\N	\N
512930221	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-512930221	draft	UPN:DMS:Complaints:Complaint:3635111	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450990997	1450990997	\N	\N	\N
393449215	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-393449215	draft	UPN:DMS:Complaints:Complaint:9520454	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450983466	1450983466	\N	\N	\N
969544453	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-969544453	draft	UPN:DMS:Complaints:Complaint:4589633	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450976964	1450976964	\N	\N	\N
678371757	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-678371757	draft	UPN:DMS:Complaints:Complaint:7117408	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450957975	1450957975	\N	\N	\N
437281299	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-437281299	draft	UPN:DMS:Complaints:Complaint:9602080	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450982882	1450982882	\N	\N	\N
509532538	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-509532538	draft	UPN:DMS:Complaints:Complaint:6109695	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450958745	1450958745	\N	\N	\N
210082607	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-210082607	draft	UPN:DMS:Complaints:Complaint:292851	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450982951	1450982951	\N	\N	\N
247984775	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-247984775	draft	UPN:DMS:Complaints:Complaint:7208401	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450962457	1450962457	\N	\N	\N
768685541	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-768685541	draft	UPN:DMS:Complaints:Complaint:4466365	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450988839	1450988839	\N	\N	\N
951307678	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-951307678	draft	UPN:DMS:Complaints:Complaint:1235566	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450983552	1450983552	\N	\N	\N
462299942	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-462299942	draft	UPN:DMS:Complaints:Complaint:229593	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450962567	1450962567	\N	\N	\N
329046293	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-329046293	draft	UPN:DMS:Complaints:Complaint:9745157	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450982988	1450982988	\N	\N	\N
679488439	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-679488439	draft	UPN:DMS:Complaints:Complaint:1464955	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450990322	1450990322	\N	\N	\N
215842408	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-215842408	draft	UPN:DMS:Complaints:Complaint:6100790	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450983295	1450983295	\N	\N	\N
625474739	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-625474739	draft	UPN:DMS:Complaints:Complaint:9619145	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450983834	1450983834	\N	\N	\N
770329649	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-770329649	draft	UPN:DMS:Complaints:Complaint:4548479	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450995472	1450995472	\N	\N	\N
891776107	0	0	\N	\N	\N	2015-12-31	2015-12-24	вамавмва	\N	C_IS-891776107	draft	UPN:DMS:Complaints:Complaint:9861594	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450989137	1450989137	\N	\N	\N
475170436	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-475170436	draft	UPN:DMS:Complaints:Complaint:4119429	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450988192	1450988192	\N	\N	\N
923772853	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-923772853	draft	UPN:DMS:Complaints:Complaint:758799	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450995427	1450995427	\N	\N	\N
959861082	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-959861082	draft	UPN:DMS:Complaints:Complaint:4404219	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450991121	1450991121	\N	\N	\N
953612190	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-953612190	draft	UPN:DMS:Complaints:Complaint:546400	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450990588	1450990588	\N	\N	\N
583173612	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-583173612	draft	UPN:DMS:Complaints:Complaint:6266445	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450991376	1450991376	\N	\N	\N
946047178	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-946047178	draft	UPN:DMS:Complaints:Complaint:9419182	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450991499	1450991499	\N	\N	\N
50602574	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-50602574	draft	UPN:DMS:Complaints:Complaint:5153628	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450991363	1450991363	\N	\N	\N
676367693	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-676367693	draft	UPN:DMS:Complaints:Complaint:9319006	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450991484	1450991484	\N	\N	\N
602399780	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-602399780	draft	UPN:DMS:Complaints:Complaint:8159440	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450995671	1450995671	\N	\N	\N
322241724	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-322241724	draft	UPN:DMS:Complaints:Complaint:7100030	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450995835	1450995835	\N	\N	\N
32573968	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-32573968	draft	UPN:DMS:Complaints:Complaint:1963321	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450995854	1450995854	\N	\N	\N
519925331	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-519925331	draft	UPN:DMS:Complaints:Complaint:1485048	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450995890	1450995890	\N	\N	\N
961876273	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-961876273	draft	UPN:DMS:Complaints:Complaint:303506	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450996000	1450996000	\N	\N	\N
991950415	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-991950415	draft	UPN:DMS:Complaints:Complaint:496611	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450996031	1450996031	\N	\N	\N
532083974	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-532083974	draft	UPN:DMS:Complaints:Complaint:5957948	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450996085	1450996085	\N	\N	\N
4299341	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-4299341	draft	UPN:DMS:Complaints:Complaint:1040358	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450998970	1450998970	\N	\N	\N
831328576	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-831328576	draft	UPN:DMS:Complaints:Complaint:4751756	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450996133	1450996133	\N	\N	\N
285501594	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-285501594	draft	UPN:DMS:Complaints:Complaint:5921379	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450997461	1450997461	\N	\N	\N
912866	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-912866	draft	UPN:DMS:Complaints:Complaint:8352799	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450997113	1450997113	\N	\N	\N
631871664	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-631871664	draft	UPN:DMS:Complaints:Complaint:3597015	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450996142	1450996142	\N	\N	\N
57763971	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-57763971	draft	UPN:DMS:Complaints:Complaint:6427604	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450998313	1450998313	\N	\N	\N
261108644	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-261108644	draft	UPN:DMS:Complaints:Complaint:1304935	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450996309	1450996309	\N	\N	\N
608350050	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-608350050	draft	UPN:DMS:Complaints:Complaint:9210134	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450997176	1450997176	\N	\N	\N
620920043	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-620920043	draft	UPN:DMS:Complaints:Complaint:8497023	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450998691	1450998691	\N	\N	\N
467895992	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-467895992	draft	UPN:DMS:Complaints:Complaint:7463465	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450996317	1450996317	\N	\N	\N
160231376	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-160231376	draft	UPN:DMS:Complaints:Complaint:4987199	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450997467	1450997467	\N	\N	\N
833536487	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-833536487	draft	UPN:DMS:Complaints:Complaint:722693	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450997234	1450997234	\N	\N	\N
50132461	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-50132461	draft	UPN:DMS:Complaints:Complaint:1836823	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450996564	1450996564	\N	\N	\N
848669674	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-848669674	draft	UPN:DMS:Complaints:Complaint:4554725	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450998904	1450998904	\N	\N	\N
654643806	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-654643806	draft	UPN:DMS:Complaints:Complaint:1246541	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450996622	1450996622	\N	\N	\N
676680777	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-676680777	draft	UPN:DMS:Complaints:Complaint:1198712	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450998319	1450998319	\N	\N	\N
219280228	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-219280228	draft	UPN:DMS:Complaints:Complaint:5670578	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450997365	1450997365	\N	\N	\N
537238609	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-537238609	draft	UPN:DMS:Complaints:Complaint:763435	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450996800	1450996800	\N	\N	\N
80246423	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-80246423	draft	UPN:DMS:Complaints:Complaint:5572764	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450997472	1450997472	\N	\N	\N
458612030	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-458612030	draft	UPN:DMS:Complaints:Complaint:8974701	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450997016	1450997016	\N	\N	\N
465826683	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-465826683	draft	UPN:DMS:Complaints:Complaint:7982806	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450997397	1450997397	\N	\N	\N
200300346	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-200300346	draft	UPN:DMS:Complaints:Complaint:170795	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450998993	1450998993	\N	\N	\N
233574395	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-233574395	draft	UPN:DMS:Complaints:Complaint:7404850	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450997669	1450997669	\N	\N	\N
738554184	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-738554184	draft	UPN:DMS:Complaints:Complaint:5805622	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450997440	1450997440	\N	\N	\N
870172733	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-870172733	draft	UPN:DMS:Complaints:Complaint:8069181	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450998839	1450998839	\N	\N	\N
16547591	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-16547591	draft	UPN:DMS:Complaints:Complaint:3408028	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450998376	1450998376	\N	\N	\N
691905077	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-691905077	draft	UPN:DMS:Complaints:Complaint:5411487	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450997797	1450997797	\N	\N	\N
412524130	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-412524130	draft	UPN:DMS:Complaints:Complaint:1743579	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450998948	1450998948	\N	\N	\N
868443458	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-868443458	draft	UPN:DMS:Complaints:Complaint:3925681	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450998410	1450998410	\N	\N	\N
101572180	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-101572180	draft	UPN:DMS:Complaints:Complaint:7916722	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450998898	1450998898	\N	\N	\N
935833328	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-935833328	draft	UPN:DMS:Complaints:Complaint:5009115	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450998981	1450998981	\N	\N	\N
575058994	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-575058994	draft	UPN:DMS:Complaints:Complaint:2284099	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450999569	1450999569	\N	\N	\N
181619597	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-181619597	draft	UPN:DMS:Complaints:Complaint:7237061	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450999429	1450999429	\N	\N	\N
673784239	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-673784239	draft	UPN:DMS:Complaints:Complaint:7752239	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450999236	1450999236	\N	\N	\N
583619218	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-583619218	draft	UPN:DMS:Complaints:Complaint:6715595	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450999655	1450999655	\N	\N	\N
285027683	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-285027683	draft	UPN:DMS:Complaints:Complaint:1011996	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450999673	1450999673	\N	\N	\N
47781434	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-47781434	draft	UPN:DMS:Complaints:Complaint:7252810	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1450999764	1450999764	\N	\N	\N
642958359	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-642958359	draft	UPN:DMS:Complaints:Complaint:3938053	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1451046826	1451046826	\N	\N	\N
302112995	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-302112995	draft	UPN:DMS:Complaints:Complaint:7311243	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1451140948	1451140948	\N	\N	\N
910842530	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-910842530	draft	UPN:DMS:Complaints:Complaint:7638357	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1453040452	1453040452	\N	\N	\N
9321978	0	0	\N	\N	\N	\N	\N	fdvfdv	\N	C_IS-9321978	draft	UPN:DMS:Complaints:Complaint:9224164	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1453067722	1453067722	\N	\N	\N
190664794	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-593768	Editing	UPN:DMS:Complaints:Complaint:7722618	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1453750445	1453750445	\N	\N	1889563792
858244639	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-858244639	draft	UPN:DMS:Complaints:Complaint:4581138	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:0	\N	f	f	f	f	\N	\N	1453721599	1453721599	\N	\N	\N
214620908	0	0	\N	\N	\N	\N	\N	\N	\N	\N	draft	UPN:DMS:Complaints:Complaint:204492	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1453926421	1453926421	\N	\N	\N
913497309	0	0	\N	\N	\N	\N	\N	\N	\N	\N	draft	UPN:DMS:Complaints:Complaint:7400310	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1453926444	1453926444	\N	\N	\N
611409278	0	0	\N	\N	\N	\N	\N	\N	\N	\N	draft	UPN:DMS:Complaints:Complaint:2062561	\N	{}	\N	urn:Actor:User:System:1	\N	t	f	f	f	\N	\N	1453926603	1453926603	\N	\N	\N
548205124	0	0	\N	\N	\N	\N	\N	\N	\N	\N	draft	UPN:DMS:Complaints:Complaint:7491341	\N	{}	\N	urn:Actor:User:System:1	\N	t	f	f	f	\N	\N	1453926690	1453926690	\N	\N	\N
53621176	0	0	\N	\N	\N	\N	\N	\N	\N	\N	draft	UPN:DMS:Complaints:Complaint:1010187	\N	{}	\N	urn:Actor:User:System:1	\N	t	f	f	f	\N	\N	1453926750	1453926750	\N	\N	\N
61309671	0	0	\N	\N	\N	\N	\N	\N	\N	\N	draft	UPN:DMS:Complaints:Complaint:7341295	\N	{}	\N	urn:Actor:User:System:1	\N	t	f	f	f	\N	\N	1453926800	1453926800	\N	\N	\N
381168181	0	0	\N	\N	\N	\N	\N	\N	\N	\N	CreateDraft	UPN:DMS:Complaints:Complaint:7053919	\N	{}	\N	urn:Actor:User:System:1	\N	t	f	f	f	\N	\N	1453927093	1453927093	\N	\N	\N
372158522	0	0	1156372664	1293144792	643421900	2016-01-19	2016-01-29	login - max@attracti.com   pass - 123\nlogin - inna@attracti.com   pass - guefog79	{/original/urn-Document-Complaint-C_IS-372158522/1-6507977.jpg}	C_IS-594109	CallCP	UPN:DMS:Complaints:Complaint:415022	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1453929984	1453929984	\N	\N	1257258427
40376504	0	0	1156372664	1293144792	1293144792	2016-01-28	\N	ьопмбори	{/original/urn-Document-Complaint-C_IS-40376504/eco_leaves_160110-1783443.jpg}	C_IS-264321	CallCP	UPN:DMS:Complaints:Complaint:7570644	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1453932623	1453932623	\N	\N	1866859654
847144382	0	0	\N	\N	\N	\N	\N	\N	\N	C_IS-747850	Editing	UPN:DMS:Complaints:Complaint:5724553	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1454413977	1454413977	\N	\N	452051329
76828139	0	0	115997801	206674127	966042166	2016-02-21	2016-02-23	Жалоба1	{/original/urn-Document-Complaint-C_IS-76828139/30c5d3e8d80f6930efc11a2152d1e5a0-7086191.gif}	C_IS-979940	Editing	UPN:DMS:Complaints:Complaint:7508668	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456409978	1456409978	\N	\N	943481548
\.


--
-- Data for Name: Document_Complaint_C_IV; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Complaint_C_IV" (id, fromclient, stillactual, "CompanyLegalEntityCounterparty", warehouse, object, datestart, dateend, description, attachments, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal") FROM stdin;
635007251	0	0	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Complaint_C_IW; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Complaint_C_IW" (id, fromclient, stillactual, client, warehouse, counterparty, "DocumentContractTME", datestart, dateend, description, attachments, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal") FROM stdin;
1282564341	0	0	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Complaint_C_LB; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Complaint_C_LB" (id, fromclient, stillactual, "CompanyLegalEntityCounterparty", warehouse, datestart, dateend, description, attachments, invoice, invoicedate, invoicesum, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal") FROM stdin;
1778773020	0	0	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Complaint_C_LC; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Complaint_C_LC" (id, fromclient, stillactual, "CompanyLegalEntityCounterparty", warehouse, datestart, dateend, description, attachments, productname, seriesofproduct, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal") FROM stdin;
359675714	0	0	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N
88687448	0	0	115997801	206674127	2016-02-20	2016-02-27	v cv vc v	{/original/urn-Document-Complaint-C_LC-88687448/353100_Tempo__00_m-4751265.jpg}	ddd	222	C_LC-358384	Editing	UPN:DMS:Complaints:Complaint:1626004	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456416892	1456416892	\N	\N	1035562271
\.


--
-- Data for Name: Document_Complaint_C_LP; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Complaint_C_LP" (id, fromclient, stillactual, "CompanyLegalEntityCounterparty", warehouse, datestart, dateend, description, attachments, documentnumber, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal") FROM stdin;
2081104927	0	0	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Complaint_C_LT; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Complaint_C_LT" (id, fromclient, stillactual, "CompanyLegalEntityCounterparty", warehouse, datestart, dateend, description, attachments, transportdocument, transportdate, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal") FROM stdin;
1488647933	0	0	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N
138672545	0	0	115997801	10341110	2016-01-20	2016-01-30	nononononononononononononononononononononononononononononononononononononononononononononononono	{/original/urn-Document-Complaint-C_LT-138672545/1.jpg}	trdxcvbnm	2016-01-21	\N	Editing	UPN:DMS:Complaints:Complaint:8191934	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1453760154	1453760154	\N	\N	\N
219904659	0	0	115997801	10341110	2016-01-20	2016-01-25	nononononononononononononononononononononononononononononononononononononononononononononononononononononononononononononononono	{/original/urn-Document-Complaint-C_LT-219904659/1.jpg}	3werth	2016-01-20	\N	Editing	UPN:DMS:Complaints:Complaint:2947966	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1453759663	1453759663	\N	\N	\N
593922708	0	0	115997801	10341110	2016-01-25	2016-01-26	nononononononononononononononononononononononononononononononononononononononononononononononono	{/original/urn-Document-Complaint-C_LT-593922708/1.jpg}	werthyguhilkjhgcfxd	2016-01-26	\N	Editing	UPN:DMS:Complaints:Complaint:9880560	\N	{URN:D:A:A:123}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1453759990	1453759990	\N	\N	\N
24913852	0	0	115997801	614895960	2016-02-25	2016-02-25	vdfvdfvf	{}	vfdvdf	2016-02-25	C_LT-757339	Editing	UPN:DMS:Complaints:Complaint:2084582	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456407202	1456407202	\N	\N	1806352408
917825520	0	0	115997801	614895960	2016-02-25	2016-02-25	м с см см	{/original/urn-Document-Complaint-C_LT-917825520/5322eb2c28cc27be5bb4362aa5ce020a-3599871.jpg}	мавломватлм	2016-02-25	C_LT-535342	Editing	UPN:DMS:Complaints:Complaint:9995676	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456429678	1456429678	\N	\N	1034011664
\.


--
-- Data for Name: Document_ContractAgreement_SAE; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_ContractAgreement_SAE" (id, "contractapplication_DocumentContractApplicationUniversal", text, attachment, contractlink, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, date, basevisants, additionalvisants, "DMSDocumentUniversal") FROM stdin;
1149947724	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450607134	\N	\N	\N	\N	\N
\.


--
-- Data for Name: Document_ContractApplication_Universal; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_ContractApplication_Universal" (id, text, contractlink, "MediaAttributed_DirectoryMediaAttributed", "DMSDocumentUniversal", privatedraft, state, code, process, parent, children, related, initiator, authors, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, ordered) FROM stdin;
1983900789	<p>fdvdfvfd</p>	\N	{1051549032}	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453933912	\N	\N	\N	1
1933296009	\N	\N	{1638661325}	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1454009086	\N	\N	\N	2
1569434862	\N	\N	{73945746}	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1454009090	\N	\N	\N	3
645765354	\N	\N	{2133954157}	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1454009091	\N	\N	\N	4
1279888883	\N	\N	{165119542}	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1454009093	\N	\N	\N	5
50190250	\N	\N	{573823332}	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1454009093	\N	\N	\N	6
1761134326	\N	\N	{1251376077}	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1454009093	\N	\N	\N	7
1577361217	<table><tr><td>\n<p><strong>№</strong></p>\n</td>\n<td>\n<p><strong>Назва</strong></p>\n</td>\n<td>\n<p><strong>Од.</strong></p>\n</td>\n<td>\n<p><strong>Кількість,</strong></p>\n<p><strong>шт.</strong></p>\n</td>\n<td>\n<p><strong>Ціна без ПДВ, грн.</strong></p>\n</td>\n<td>\n<p><strong>Сума без ПДВ, грн.</strong></p>\n</td>\n</tr><tr><td>\n<p>1</p>\n</td>\n<td>\n<p>Холодильна машина з водяним охолодженням конденсатора RHOSS THHEBY <br /> 4360 HT</p>\n</td>\n<td>\n<p>шт.</p>\n</td>\n<td>\n<p>2,000</p>\n</td>\n<td>\n<p>1 596 303,40</p>\n</td>\n<td>\n<p>3 192 606,80</p>\n</td>\n</tr><tr><td>\n<p>2</p>\n</td>\n<td>\n<p>Насос циркуляційний Р1 до холодильної  машини THHEBY 4360 HT</p>\n</td>\n<td>\n<p>шт.</p>\n</td>\n<td>\n<p>2,000</p>\n</td>\n<td>\n<p>138 552,20</p>\n</td>\n<td>\n<p>277 104,40</p>\n</td>\n</tr><tr><td>\n<p>3</p>\n</td>\n<td>\n<p>Контроль конденсації BSP до холодильної машини THHEBY 4360 HT</p>\n</td>\n<td>\n<p>шт.</p>\n</td>\n<td>\n<p>2,000</p>\n</td>\n<td>\n<p>8 859,80</p>\n</td>\n<td>\n<p>17 719,60</p>\n</td>\n</tr><tr><td>\n<p>4</p>\n</td>\n<td>\n<p>Терморегулчий вентиль EVV до холодиль-ної машини THHEBY 4360 HT</p>\n</td>\n<td>\n<p>шт.</p>\n</td>\n<td>\n<p>2,000</p>\n</td>\n<td>\n<p>49 885,20</p>\n</td>\n<td>\n<p>99 770,40</p>\n</td>\n</tr><tr><td>\n<p>5</p>\n</td>\n<td>\n<p>Манометри високого та низького тиску GM до холодильної машини <br /> THHEBY 4360 HT</p>\n</td>\n<td>\n<p>шт.</p>\n</td>\n<td>\n<p>2,000</p>\n</td>\n<td>\n<p>19 848,60</p>\n</td>\n<td>\n<p>39 697,20</p>\n</td>\n</tr><tr><td>\n<p>6</p>\n</td>\n<td>\n<p>Антивібраційні опори SAG2 до холодиль-ної машини THHEBY 4360 HT</p>\n</td>\n<td>\n<p>шт.</p>\n</td>\n<td>\n<p>2,000</p>\n</td>\n<td>\n<p>18 017,40</p>\n</td>\n<td>\n<p>36 034,80</p>\n</td>\n</tr><tr><td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n<td>\n<p><strong>Разом без ПДВ: </strong></p>\n</td>\n<td>\n<p><strong> </strong><strong>3 662 933,20</strong></p>\n</td>\n</tr><tr><td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n<td>\n<p><strong>ПДВ: </strong></p>\n</td>\n<td>\n<p><strong>732 586,64</strong></p>\n</td>\n</tr><tr><td> </td>\n<td> </td>\n<td> </td>\n<td> </td>\n<td>\n<p><strong>Всього з ПДВ: </strong></p>\n</td>\n<td>\n<p><strong>4 395 519</strong><strong>,</strong><strong>84</strong></p>\n</td>\n</tr></table>	\N	{1546767246}	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1454009446	\N	\N	\N	8
703664090	<p><strong>АКТ</strong></p>\n<p><strong>ПРИЙМАННЯ-ПЕРЕДАЧІ</strong></p>\n<p><strong>НЕЖИЛИХ ПРИМІЩЕНЬ В ОРЕНДУ</strong></p>\n<p> </p>\n<p>Ми, що нижче підписалися, <strong>Товариство з обмеженою відповідальністю «ХФК «Біокон»</strong>, що надалі іменуються «ОРЕНДОДАВЕЦЬ», в особі  директора Павлової В. В., та  </p>\n<p><strong>_______________ «______________»</strong>, далі – ОРЕНДАР, в особі директора __________, склали цей акт про наступне:</p>\n<p> </p>\n<p>Відповідно до умов укладеного договору оренди нежилого приміщення від _____ 2015року:</p>\n<p> </p>\n<p>1.1. ОРЕНДОДАВЕЦЬ здає, а ОРЕНДАР приймає в оренду нежиле приміщення загальною площею ________  кв.м. за адресою: Київська обл., Бориспільський р-н,  с. Велика Олександрівка, вул. Бориспільска, 9.</p>\n<p> </p>\n<p>1.2. Сторони не мають взаємних претензій щодо стану приміщення, що орендується.</p>	\N	{873870228}	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1454015156	\N	\N	\N	9
\.


--
-- Data for Name: Document_Contract_BW; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Contract_BW" (id, timecontract, timenotifyfor, "DirectoryBusinessProjectsItem", "CompanyLegalEntityCounterparty", "BusinessObjectRecordPolymorph", "CompanyStructureCompanygroup", tenderdoc, "contractapplication_DocumentContractApplicationUniversal", "notifyusercompany_ManagementPostIndividual", "notifyusercounterparty_PeopleEmployeeCounterparty", place, date, prolongation, enddate, summ, justification, attachments, introduction, contractsubject, rightsandliabilities, timeofworks, termofcustompayments, payments, specialconditions, otherconditions, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, notifyusercounterparty, "DMSDocumentUniversal") FROM stdin;
303280635	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N	\N
591082645	246037407	464841811	364846007	115997801	\N	267613092	\N	{1983900789}	\N	\N	dfvdfvdf	2016-01-28	2	\N	1200.00	fdvdfvdf	{/original/urn-Document-Contract-BW-591082645/KABINET-6529545.png}	<p>dfvfdvdf</p>	<p>bgfbfgb</p>	<ol><li>fgbgfbfg</li>\n</ol>	<ul><li>fgbgfbfbgfgbfg</li>\n</ul>	<p>fgbgfbvc </p>	<p>fdvfdv</p>	<p>fdvd</p>	<p>dfvdfvf</p>	\N	Configuring	UPN:DMS:Contracts:Contract:7673076	\N	{}	\N	urn:Actor:User:System:1	\N	t	f	f	f	\N	\N	1453933824	1453933824	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
\.


--
-- Data for Name: Document_Contract_LC; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Contract_LC" (id, timecontract, timenotifyfor, "DirectoryBusinessProjectsItem", "CompanyLegalEntityCounterparty", "BusinessObjectRecordPolymorph", "CompanyStructureCompanygroup", tenderdoc, "contractapplication_DocumentContractApplicationUniversal", "notifyusercompany_ManagementPostIndividual", "notifyusercounterparty_PeopleEmployeeCounterparty", place, date, prolongation, enddate, summ, justification, attachments, introduction, contractsubject, payments, disputeresolutions, final, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal", rightsandliabilities) FROM stdin;
\.


--
-- Data for Name: Document_Contract_LOP; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Contract_LOP" (id, timecontract, timenotifyfor, "DirectoryBusinessProjectsItem", "CompanyLegalEntityCounterparty", "BusinessObjectRecordPolymorph", "CompanyStructureCompanygroup", tenderdoc, "contractapplication_DocumentContractApplicationUniversal", "notifyusercompany_ManagementPostIndividual", "notifyusercounterparty_PeopleEmployeeCounterparty", place, date, prolongation, enddate, summ, justification, attachments, introduction, objectforrent, timeofrent, priceandterms, responsibilitiesoflandlord, responsibilities, termsofreturn, liabilities, disputesresolving, forcemajeure, contracttermination, otherconditions, appendix, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal", contractsubject) FROM stdin;
\.


--
-- Data for Name: Document_Contract_LWP; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Contract_LWP" (id, timecontract, timenotifyfor, "DirectoryBusinessProjectsItem", "CompanyLegalEntityCounterparty", "BusinessObjectRecordPolymorph", "CompanyStructureCompanygroup", tenderdoc, "contractapplication_DocumentContractApplicationUniversal", "notifyusercompany_ManagementPostIndividual", "notifyusercounterparty_PeopleEmployeeCounterparty", place, date, prolongation, enddate, summ, justification, attachments, introduction, definitions, contractsubject, warehouseconditions, leabilities, rights, lenlordleabilities, lenlordrights, rentpayments, partyliabilities, contractterm, specialconditions, final, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal") FROM stdin;
\.


--
-- Data for Name: Document_Contract_MT; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Contract_MT" (id, timecontract, timenotifyfor, "DirectoryBusinessProjectsItem", "CompanyLegalEntityCounterparty", "BusinessObjectRecordPolymorph", "CompanyStructureCompanygroup", tenderdoc, "contractapplication_DocumentContractApplicationUniversal", "notifyusercompany_ManagementPostIndividual", "notifyusercounterparty_PeopleEmployeeCounterparty", place, date, prolongation, enddate, summ, justification, attachments, introduction, contractsubject, qualityofgoods, deliveryconditions, goodstransfer, termsofpayment, termsofcontract, liabilities, final, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal") FROM stdin;
\.


--
-- Data for Name: Document_Contract_RSS; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Contract_RSS" (id, timecontract, timenotifyfor, "DirectoryBusinessProjectsItem", "CompanyLegalEntityCounterparty", "BusinessObjectRecordPolymorph", "CompanyStructureCompanygroup", tenderdoc, "contractapplication_DocumentContractApplicationUniversal", "notifyusercompany_ManagementPostIndividual", "notifyusercounterparty_PeopleEmployeeCounterparty", place, date, prolongation, enddate, summ, justification, attachments, introduction, wordsdefinition, subjectofcontract, responsibilityofdoer, responsibility, priceandterm, insurance, accounting, trademarks, confidentiality, timeofcontract, forcemajeure, refuce, fullcontract, language, jurisdiction, otherconditions, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal") FROM stdin;
\.


--
-- Data for Name: Document_Contract_SS; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Contract_SS" (id, timecontract, timenotifyfor, "DirectoryBusinessProjectsItem", "CompanyLegalEntityCounterparty", "BusinessObjectRecordPolymorph", "CompanyStructureCompanygroup", tenderdoc, "contractapplication_DocumentContractApplicationUniversal", "notifyusercompany_ManagementPostIndividual", "notifyusercounterparty_PeopleEmployeeCounterparty", place, date, prolongation, enddate, summ, justification, attachments, introduction, contractsubject, price, payments, termofworks, maintanance, worksdoing, guarantees, executedworks, partiesliabilities, changes, timeofcontract, forcemajeure, otherconditions, appendix, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal") FROM stdin;
\.


--
-- Data for Name: Document_Contract_TMC; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Contract_TMC" (id, timecontract, timenotifyfor, "DirectoryBusinessProjectsItem", "CompanyLegalEntityCounterparty", "BusinessObjectRecordPolymorph", "CompanyStructureCompanygroup", tenderdoc, "contractapplication_DocumentContractApplicationUniversal", "notifyusercompany_ManagementPostIndividual", "notifyusercounterparty_PeopleEmployeeCounterparty", place, date, prolongation, enddate, summ, justification, attachments, introduction, contractsubject, orderofworksexecution, rights, termsofpayment, liabilities, changesofcontracts, specialconditions, termsofcontract, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal") FROM stdin;
919702768	\N	\N	\N	404906315	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	Configuring	UPN:DMS:Contracts:Contract:5415622	\N	{}	\N	urn:Actor:User:System:1	\N	t	f	f	f	\N	\N	1454006678	1454006678	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N
\.


--
-- Data for Name: Document_Contract_TME; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Contract_TME" (id, timecontract, timenotifyfor, "DirectoryBusinessProjectsItem", "CompanyLegalEntityCounterparty", "BusinessObjectRecordPolymorph", "CompanyStructureCompanygroup", tenderdoc, "contractapplication_DocumentContractApplicationUniversal", "notifyusercompany_ManagementPostIndividual", "notifyusercounterparty_PeopleEmployeeCounterparty", place, date, prolongation, enddate, summ, justification, attachments, introduction, contractsubject, orderofworksexecution, costofworks, partyliabilities, responsibilityofpartie, timeofcontracts, final, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, notifyusercounterparty, "DMSDocumentUniversal") FROM stdin;
720047737	246037407	1464808265	364846007	1156372664	1293144792	1642094893	urn:Document:Staffdoc:OF:3882240	{1933296009,1569434862,645765354,1279888883,50190250,1761134326,1577361217}	{1118804000}	\N	Киев	2016-01-28	1	\N	1000.00	пмормьрт	{/original/urn-Document-Contract-TME-720047737/eco_leaves_160110-8345104.jpg}	<p><strong>ТОВАРИСТВО З ОБМЕЖЕНОЮ ВІДПОВІДАЛЬНІСТЮ «КЛІМАТИЧНІ СИСТЕМИ ГМБХ»</strong>, далі – Постачальник, в особі <strong>директора Дідиченка Антона Станіславовича</strong>, діючого на підставі Статуту, з однієї сторони, та <strong>ТОВАРИСТВО З ОБМЕЖЕНОЮ ВІДПОВІДАЛЬНІСТЮ «БІОСАН», </strong>далі - Покупець, в особі<strong> генерального директора Пижука Володимира Михайловича</strong>, діючого на підставі Статуту, з іншої сторони, заключили даний Договір про наступне:</p>	<p>1.1. Постачальник зобов’язується поставити та передати у власність Покупцю Обладнання, найменування, перелік та ціни якого наведені в Специфікації (Додатку №1 до даного Договору), що є невід’ємною частиною даного Договору, а Покупець зобов'язується прийняти та оплатити ціну Обладнання, на умовах цього Договору.</p>	<p>Обладнання повинно бути поставлено протягом 12-ти (дванадцяти) тижнів з моменту зарахування грошових коштів на розрахунковий рахунок Постачальника. Оплата повинна бути виконана протягом 3-х банківських днів після дати підписання Договору.</p>\n<p>3.2. Якщо Покупець в обумовлений строк не виконає свої зобов’язання щодо проплати даного Договору, то Постачальник має право на сорозмірне продовження строку поставки.</p>\n<p>3.3. Якщо Покупець, протягом 30 днів з моменту підписання Договору, не проведе перерахування грошових коштів на розрахунковий рахунок Постачальника, Постачальник має право відступити від виконання Договору.</p>\n<p>3.4. Обладнання постачається за адресою: м. Київ, пров. Електриків, 17.</p>\n<p>3.5. Право власності на Обладнання переходить до Покупця в момент передачі йому Обладнання Постачальником. Передача відбувається шляхом підписання Акта приймання-передачі або за довіреністю.</p>	<p>2.1. Загальна вартість Договору погоджується Сторонами та відображається у Специфікації до даного Договору, що є його невід‘ємною частинами.</p>\n<p>2.2. Валютою Договору є національна валюта України - гривня.</p>\n<p>2.3. Загальна вартість Договору становить: 4 395 519,84 грн. (чотири мільйони триста дев’яносто п’ять тисяч п’ятсот дев’ятнадцять гривень 84 коп.), в т.ч. ПДВ – 732 586,64 грн. (сімсот тридцять дві тисячі п’ятсот вісімдесят шість гривень 64 коп.).</p>\n<p>2.4. Беручи до уваги наявність імпортної складової в структурі вартості Обладнання, керуючись статтями 524, 533 Цивільного кодексу України, Сторони погодились визначити грошовий еквівалент зобов’язання в іноземній валюті – євро (EUR), в зв’язку з чим валютна складова вартості Товару у загальній вартості Договору станом на 11 грудня 2015 р. за курсом купівлі валюти на ПАТ «Діамантбанк» + 1,0 % дорівнює 27,472 грн. (UAH) за 1 євро (EUR) складає 160 000,00 євро (сто шістдесят тисяч євро).</p>\n<p>2.5. У разі зміни курсу на ПАТ «Діамантбанк» гривні до євро (www.diamantbank.ua) більш ніж на 2% (два відсотка), що може виникнути між датами платежів (п. 2.6.1 – п. 2.6.2) Договору, вартість остаточної сплати визначається як добуток суми в гривнях до сплати та коефіцієнту. Коефіцієнт визначається за наступною формулою: К = К2/К1, де К - коефіцієнт;</p>\n<p>К1 – курс євро до гривні за курсом на ПАТ «Діамантбанк» +1,0% на дату виставлення рахунку Постачальником;</p>\n<p>К2 – курс євро до гривні за курсом на ПАТ «Діамантбанк» +1,0% на дату остаточної сплати.</p>\n<p>2.6. Оплата за належним чином поставлене Обладнання за даним Договором виконується наступним шляхом:</p>\n<p>2.6.1. Покупець виконує передоплату в розмірі 100% від загальної вартості Договору протягом 3-х банківських днів, після дати підписання Договору.</p>\n<p>2.7. У разі таких змін Сторонами підписується відповідна додаткова угода про зміну вартості Договору.</p>	<p>liyguyglu</p>	<p>zldjvnadlkjn</p>	<ol><li><strong> АРБІТРАЖ</strong></li>\n</ol><p>11.1. Всі суперечки, які можуть виникнути з цього Договору або з інших причин, сторони спробують вирішити шляхом переговорів.</p>\n<p>11.2. Якщо сторони не можуть досягнути компромісу, суперечка розглядається в Господарському суді України.</p>	<ol><li><strong> ІНШІ УМОВИ</strong></li>\n</ol><p>12.1. В частині умов і обставин, не урегульованих даним Договором, але пов’язаних з ним, сторони урегульовують суперечки згідно з чинним законодавством України.</p>\n<p>12.2. Всі зміни та доповнення до цього Договору дійсні лише у випадку, якщо вони укладені в письмовій формі та підписані обома сторонами.</p>\n<p>12.3. Після підписання даного Договору всі попередні переговори та погодження по ньому втрачають силу.</p>\n<p>12.4. Цей договір складений українською мовою в двох примірниках, маючих однакову юридичну силу, і вступають в дію з моменту їх підписання.</p>\n<p>12.5. Постачальник та Покупець є платниками податку на прибуток на загальних умовах.</p>	TME-952980	Approving	UPN:DMS:Contracts:Contract:4920423	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1454009007	1454009007	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	872845732
614585156	246037407	47836635	364846007	115997801	\N	1642094893	urn:Document:Detective:C_IS:404331552	{703664090}	{1118804000,1816416714}	\N	Киев	2016-01-30	1	\N	9876543.00	лорпавычсмить	{}	<p>1.1. ОРЕНДОДАВЕЦЬ передає, а ОРЕНДАР приймає в оренду нежиле приміщення  (далі – приміщення, що орендується) розташовані за адресою: Київська обл., Бориспільський р-н., с. Велика Олександрівка, вул. Бориспільска,  9, загальною площею _________ кв. м. (План приміщення наведено в Додатку № 2 до цього Договору).</p>\n<p>1.2. Приміщення, що орендується, належить ОРЕНДОДАВЦЮ на праві власності відповідно до Свідоцтва про право власності на нерухоме майно серія CАК 136135  від 01.07.2013 р.  (Копія Свідоцтва додається).</p>\n<p>1.3.   Приміщення передається у користування виключно з метою його використання під офіс ОРЕНДАРЯ</p>	<p>2.1. Приміщення, що орендується, повинно бути передане ОРЕНДОДАВЦЕМ та прийняте ОРЕНДАРЕМ не пізніше 3-х календарних днів з моменту підписання Договору.</p>\n<p>2.2. При передачі приміщення, що орендується, складається акт приймання – передачі нежилих приміщень, який підписується Сторонами.</p>\n<p>     2.3. Приміщення, що орендується, вважається передане в оренду з дати підписання акта приймання – передачі нежилих приміщень.</p>	<p>3.1.  Термін дії оренди нежилих приміщень  з ___ ______ 201__ року по ___ ______2018 року. </p>\n<p>3.2. Якщо жодна із Сторін в місячний термін до закінчення цього Договору не заявить про намір його припинити, цей Договір автоматично пролонговується на новий аналогічний термін.</p>\n<p>3.3. Термін дії Договору може бути скорочений лише за двохсторонньою згодою Сторін, при умові письмового оформлення додаткової угоди та підпису її уповноваженими особами.</p>	<p>4.1. Розмір орендної плати за приміщення, що орендується становить _______________ гривень за 1 кв. м. без ПДВ щомісячно, з урахування комунальних послуг.</p>\n<p>4.1.2. Загальний розмір орендної плати за приміщення, що орендується  складає _________ (_________________) гривень без ПДВ на місяць, крім того ПДВ ___________ (________________) всього з урахуванням ПДВ ________ (___________________) на місяць. </p>\n<p>4.2. Плата за оренду Приміщень здійснюється ОРЕНДАРЕМ щомісяця за попередній місяць на підставі виставлених ООРЕНДОДАВЦЕМ рахунків, не пізніше 5 (п'яти) банківських днів з моменту отримання Орендарем рахунку, що виставляється з 1-го по 5-те число поточного місяця.</p>\n<p>4.3. Розмір орендної плати може бути переглянутий  Сторонами шляхом укладання додаткової угоди до цього Договору.</p>\n<p>4.4. Зобов'язання ОРЕНДАРЯ за сплатою орендної плати забезпечуються у вигляді завдатку в розмірі не меншому, ніж орендна плата за перший (базовий) місяць оренди.</p>\n<p>4.5. ОРЕНДАР має право вносити орендну плату наперед за будь-який термін у розмірі‚ що визначається на момент оплати.</p>\n<p>4.6. До вартості орендної плати входить 5 (п’ять) парувальних місць.</p>\n<p>4.7. Вартість комунальних послуг не входить до орендної плати.</p>\n<p>4.8. Усі витрати за користування телефонами оплачуються ОРЕНДАРЕМ самостійно.</p>\n<p>4.9. У разі припинення (розірвання) Договору оренди ОРЕНДАР сплачує орендну плату до дня повернення Приміщення та Майна за актом приймання-передавання включно.</p>\n<p>4.10. Закінчення строку дії Договору оренди не звільняє ОРЕНДАРЯ від обов'язку сплатити заборгованість за орендною платою, якщо така виникла, у повному обсязі, ураховуючи санкції.</p>	<p>5.1. ОРЕНДОДАВЕЦЬ зобов’язаний:</p>\n<p>5.1.1. ОРЕНДОДАВЕЦЬ передає ОРЕНДАРЮ приміщення, що орендується, в технічно справному стані.</p>\n<p>5.1.2. ОРЕНДОДАВЕЦЬ має право 1 (один) раз на квартал здійснювати перевірку порядку використання ОРЕНДАРЕМ приміщення ‚ що орендується відповідно до умов Цього Договору.</p>\n<p>5.1.3. Забезпечувати безперешкодне використання ОРЕНДАРЕМ приміщення‚ що орендується‚ на умовах цього Договору;</p>\n<p>5.2.     Орендодавець має право:</p>\n<p>5.2.1.  Контролювати наявність, стан, напрями та ефективність використання Приміщення та Майна, переданого в оренду за цим Договором;</p>\n<p>5.2.2.  Виступати з ініціативою щодо внесення змін до цього Договору або його розірвання;</p>\n<p>5.2.3.  Здійснювати контроль за станом Приміщення та Майна шляхом візуального обстеження зі складанням акта обстеження.</p>	<p>6.1. ОРЕНДАР зобов’язується:</p>\n<p>– використовувати приміщення‚ що орендується ‚ за його цільовим призначенням відповідно до п.1.1.  Цього Договору;</p>\n<p>–  своєчасно здійснювати орендні платежі згідно з п. 4 цього Договору;</p>\n<p>-   сплачувати експлуатаційні платежі (відшкодування вартості електроенергії, водопостачання та водовідведення, газопостачання, обслуговування систем пожежної охорони та сигналізації, вивозу сміття) – на підставі виставлених Орендодавцем рахунків та актів приймання-передачі;</p>\n<p>–  утримувати приміщення ‚ що орендується‚ у повній справності;</p>\n<p>–  не здійснювати без письмової згоди ОРЕНДОДАВЦЯ перебудову‚ добудову та перепланування приміщення‚ що орендується;</p>\n<p>–  підтримувати території‚ прилеглі до приміщення‚ що орендується, в належному санітарному стані;</p>\n<p>–  безперешкодно допускати в приміщення ‚ що орендується‚ представників ОРЕНДОДАВЦЯ з метою перевірки його використання  відповідно до умов Цього Договору;</p>\n<p>-  здійснювати заходи щодо збереження приміщення, що орендується, виконувати всі вимоги протипожежної безпеки, санітарні вимоги, заходи із збереження приміщення, що орендується, проводити вентиляцію цього приміщення, нести відповідальність за протипожежну безпеку та правильність експлуатації електроосвітлюваних та опалювальних систем.</p>\n<p>6.2. ОРЕНДАР має право:</p>\n<p>– обладнати приміщення‚ що орендується‚ на власний розсуд;</p>\n<p>– здавати приміщення, що орендується у суборенду тільки з письмової згоди ОРЕНДОДАВЦЯ.</p>	<p>7.1. Повернення ОРЕНДОДАВЦЮ приміщення‚ що орендується‚ здійснюється протягом 5-х днів з моменту закінчення терміну оренди. </p>\n<p>7.2. При передачі приміщення‚ що орендується‚ складається акт здачі-приймання‚ який підписується Сторонами.</p>\n<p>7.3. Приміщення‚ що орендується‚ вважається фактично переданим ОРЕНДОДАВЦЮ з моменту підписання акту здачі-приймання.</p>\n<p>7.4. Приміщення‚ що орендується‚ повинне бути передано ОРЕНДОДАВЦЮ у тому ж стані‚ в якому воно було передане в оренду з урахуванням нормального зносу.</p>\n<p>7.5. Здійснені ОРЕНДАРЕМ відокремлювані покращення приміщення‚ що орендується‚ є власністю ОРЕНДАРЯ.</p>\n<p>7.6. У випадку, коли ОРЕНДАР здійснив за власний рахунок та за згодою ОРЕНДОДАВЦЯ перепланування, він має право за взаємною згодою сторін на відшкодування вартості цих покращень.</p>	<p>8.1. У разі порушення чи невиконання взятих на себе договірних зобов’язань Сторони несуть відповідальність згідно з чинним законодавством України.</p>\n<p>8.2. У випадку невиконання або неналежного виконання Сторонами зобов'язань за Договором, Сторона, з вини якої сталося таке невиконання або неналежне виконання, несе відповідальність за шкоду, заподіяну іншій Стороні внаслідок такого невиконання або неналежного виконання і зобов’язується відшкодувати іншій Стороні заподіяні цим збитки в повному обсязі, відповідно до закону, якщо інше прямо не передбачено Договором. </p>\n<p>Окрім збитків, за невиконання або неналежне виконання зобов’язань Сторони зобов’язуються сплатити неустойку, виходячи з підстав, розмірів та в порядку, передбаченому Договором. </p>\n<p>Сплата неустойки, збитків здійснюється шляхом переказу належних до сплати грошових коштів на вказаний в Договорі поточний банківський рахунок Сторони, на користь якої здійснюється така сплата, протягом 5 (п’яти) банківських днів з моменту одержання вимоги про сплату від іншої Сторони. Відшкодування завданих збитків та/або сплата неустойки не звільняє винну Сторону від виконання зобов’язань за Договором. </p>\n<p>8.3. У випадку прострочення сплати ОРЕНДАРЕМ орендної плати та/або інших платежів, передбачених у Договорі, ОРЕНДОДАВЕЦЬ має право стягнути з ООРЕНДАРЯ пеню у розмірі подвійної облікової ставки Національного банку України, що діяла у період, за який стягується пеня, від несплаченої суми за кожний день прострочення.</p>\n<p>ОРЕНДОДАВЕЦЬ не відповідає за жодними зобов’язаннями ОРЕНДАРЯ, що можуть виникнути у Орендаря у зв’язку з орендою Приміщення перед будь-якими третіми особами.</p>\n<p>8.4. Орендодавець не несе відповідальності за шкоду, завдану Орендарю у зв’язку з:</p>\n<p>а) будь-яким пошкодженням, зникненням чи крадіжкою майна ОРЕНДАРЯ, що знаходиться у Приміщенні які стались не звини ОРЕНДОДАВЦЯ;</p>\n<p>б) перебоями в роботі комунальних мереж і наданням послуг, які, сталися не з вини Орендодавця; </p>\n<p>в) несхоронністю ООРЕНДАРЕМ свого майна, майна третіх осіб та Приміщення.</p>	\N	Configuring	UPN:DMS:Contracts:Contract:3502794	\N	{}	\N	urn:Actor:User:System:1	\N	t	f	f	f	\N	\N	1454012650	1454012650	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
\.


--
-- Data for Name: Document_Copy_Controled; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Copy_Controled" (id, isactive, "DocumentRegulationsSOP", master, "holders_PeopleEmployeeInternal", "previous_PeopleEmployeeInternal", created, dateissue, datereturn) FROM stdin;
\.


--
-- Data for Name: Document_Copy_Realnoncontrolcopy; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Copy_Realnoncontrolcopy" (id, isactive, isreturn, realcopy, "holders_PeopleEmployeeInternal", "previous_PeopleEmployeeInternal", created, master, dateissue) FROM stdin;
\.


--
-- Data for Name: Document_Correction_Capa; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Correction_Capa" (id, confirmed, taskcompleted, "DocumentCapaDeviation", "CompanyStructureDepartment", comment, created, updated, eventplace, controlresponsible, selectedsolution, descriptioncorrection, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, basevisants, additionalvisants, "DMSDocumentUniversal", realizationtype, selecttype, selectsolution, ordered, "DirectorySolutionvariantsSimple") FROM stdin;
1700282348	0	0	\N	\N	\N	1450217230	1450611490	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0	0	\N	\N
466906871	0	0	761327733	1870096956	\N	1453041324	\N	1293144792	1118804000	\N	Описание мероприятия  1 по складу 3, maxpost	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0	0	\N	\N
251836853	0	0	761327733	984894068	\N	1453041324	\N	206674127	1962987279	\N	Описание мероприятия  2 на ВМК склад 1, мск2	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0	0	\N	\N
1246892118	0	0	412193703	984894068	\N	1453053883	\N	1293144792	1118804000	\N	Max Mer	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0	0	\N	\N
1287446810	0	0	412193703	1641958276	\N	1453053883	\N	1293144792	1962987279	\N	Psh Mer	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0	0	\N	\N
156465310	0	0	961713773	1641958276	\N	1453055224	\N	206674127	1118804000	\N	Мер 1	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0	0	\N	\N
358651640	0	0	961713773	905805394	\N	1453055224	\N	1293144792	1962987279	\N	Мер 2	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0	0	\N	\N
117702323	0	0	\N	\N	\N	1453721959	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	\N	\N	\N	\N	0	0	\N	\N
519399118	0	0	276608231	1658312748	\N	1453973909	\N	10341110	1433279225	\N	ОПИСАНИЕ мероприятия для ОТКЛОНЕНИЯ	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0	0	\N	\N
1805612676	0	0	123943761	1658312748	\N	1453975365	\N	1293144792	1118804000	225621217	ОПИСАНИЕ МЕРОПРИЯТИЯ 1	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	3	1	1	\N	\N
299613188	0	0	123943761	1658312748	\N	1453975365	\N	206674127	1816416714	1810854105	ОПИСАНИЕ МЕРОПРИЯТИЯ 2	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	3	1	1	\N	\N
1621993936	0	0	297872527	1743222417	\N	1453976863	\N	206674127	1118804000	1006213670	Комната для приготовления растворов оборудована потолочной вытяжкой, есть заключение СЭС о соответствии данного помещения его непосредственному назначению. \nТем ни менее, комната может быть оборудована дополнительно согласно требованиям арендатора.\n\n2.1.\tУстановить столик, дополнительную вытяжку и слив.	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	3	1	1	\N	\N
970881957	0	0	297872527	1743222417	\N	1453976863	\N	1293144792	1816416714	1193884491	Поломоечные машины используются для мытья производственных помещений (зоны хранения, приёма и отгрузки лекарственных средств). Соответственно, они въезжают в склад из транспортного коридора непосредственно в помещение хранения лекарственных средств. Для поломоечных машин организовано отдельное помещение.\n\n2.2.\tПровести профилактические мероприятия и установить дополнительное оборудование для приготовления растворов непосредственно в помещении для заправки поломоечных машин.	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	3	1	1	\N	\N
\.


--
-- Data for Name: Document_Detective_C_IS; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Detective_C_IS" (id, "CompanyLegalEntityCounterparty", warehouse, responsible, "commissionmember_ManagementPostIndividual", "checkbo_BusinessObjectRecordPolymorph", datestart, dateend, actual, description, attachments, troublefix, troublefixdate, troubleevent, investigationdate, factdetected, complaintstatus, materialsused, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "DocumentComplaintC_IS", "internaldocuments_DMSDocumentUniversal", conclusion, basevisants, additionalvisants, "DMSDocumentUniversal", "deviations_DirectoryDeviationPreCapa", "riskapproved_RiskManagementRiskApproved", "risknotapproved_RiskManagementRiskNotApproved") FROM stdin;
216439117	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
9612	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-9612	draft	UPN:ClaimsManagement:Claims:Detective:6108576	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450864972	1450864972	\N	\N	\N	\N	\N	\N	\N	\N	\N
41362	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-41362	draft	UPN:ClaimsManagement:Claims:Detective:9181743	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450865113	1450865113	\N	\N	\N	\N	\N	\N	\N	\N	\N
76024	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-76024	draft	UPN:ClaimsManagement:Claims:Detective:9736652	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450865202	1450865202	\N	\N	\N	\N	\N	\N	\N	\N	\N
34406	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-34406	draft	UPN:ClaimsManagement:Claims:Detective:419570	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450865302	1450865302	\N	\N	\N	\N	\N	\N	\N	\N	\N
59907	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-59907	draft	UPN:ClaimsManagement:Claims:Detective:8652406	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450865337	1450865337	\N	\N	\N	\N	\N	\N	\N	\N	\N
22140	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-22140	draft	UPN:ClaimsManagement:Claims:Detective:300323	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450867100	1450867100	\N	\N	\N	\N	\N	\N	\N	\N	\N
89962	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-89962	draft	UPN:ClaimsManagement:Claims:Detective:1109559	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450867990	1450867990	\N	\N	\N	\N	\N	\N	\N	\N	\N
38957	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-38957	draft	UPN:ClaimsManagement:Claims:Detective:6980392	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450868064	1450868064	\N	\N	\N	\N	\N	\N	\N	\N	\N
81703	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-81703	draft	UPN:ClaimsManagement:Claims:Detective:1746830	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450868082	1450868082	\N	\N	\N	\N	\N	\N	\N	\N	\N
81499	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-81499	draft	UPN:ClaimsManagement:Claims:Detective:2060790	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450868235	1450868235	\N	\N	\N	\N	\N	\N	\N	\N	\N
94503	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-94503	draft	UPN:ClaimsManagement:Claims:Detective:4369008	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450868326	1450868326	\N	\N	\N	\N	\N	\N	\N	\N	\N
53074	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-53074	draft	UPN:ClaimsManagement:Claims:Detective:8714966	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450868624	1450868624	\N	\N	\N	\N	\N	\N	\N	\N	\N
65457	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-65457	draft	UPN:ClaimsManagement:Claims:Detective:2472914	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450868769	1450868769	\N	\N	\N	\N	\N	\N	\N	\N	\N
3885	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-3885	draft	UPN:ClaimsManagement:Claims:Detective:488648	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450868954	1450868954	\N	\N	\N	\N	\N	\N	\N	\N	\N
59504	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-59504	draft	UPN:ClaimsManagement:Claims:Detective:4296459	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450869058	1450869058	\N	\N	\N	\N	\N	\N	\N	\N	\N
19831	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-19831	draft	UPN:ClaimsManagement:Claims:Detective:5711078	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450869119	1450869119	\N	\N	\N	\N	\N	\N	\N	\N	\N
86747	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-86747	draft	UPN:ClaimsManagement:Claims:Detective:7216999	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450869198	1450869198	\N	\N	\N	\N	\N	\N	\N	\N	\N
96711	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-96711	draft	UPN:ClaimsManagement:Claims:Detective:8943155	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450869249	1450869249	\N	\N	\N	\N	\N	\N	\N	\N	\N
66038	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-66038	draft	UPN:ClaimsManagement:Claims:Detective:9031426	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450869447	1450869447	\N	\N	\N	\N	\N	\N	\N	\N	\N
64231	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-64231	draft	UPN:ClaimsManagement:Claims:Detective:4892024	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450869582	1450869582	\N	\N	\N	\N	\N	\N	\N	\N	\N
63626	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-63626	draft	UPN:ClaimsManagement:Claims:Detective:2354345	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450869607	1450869607	\N	\N	\N	\N	\N	\N	\N	\N	\N
1948	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-1948	draft	UPN:ClaimsManagement:Claims:Detective:8804878	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450869631	1450869631	\N	\N	\N	\N	\N	\N	\N	\N	\N
52625	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-52625	draft	UPN:ClaimsManagement:Claims:Detective:5126023	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450869822	1450869822	\N	\N	\N	\N	\N	\N	\N	\N	\N
3560	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-3560	draft	UPN:ClaimsManagement:Claims:Detective:9864171	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450869869	1450869869	\N	\N	\N	\N	\N	\N	\N	\N	\N
26961	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-26961	draft	UPN:ClaimsManagement:Claims:Detective:4373489	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450869926	1450869926	\N	\N	\N	\N	\N	\N	\N	\N	\N
42059	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-42059	draft	UPN:ClaimsManagement:Claims:Detective:6226777	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450870087	1450870087	\N	\N	\N	\N	\N	\N	\N	\N	\N
18074	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-18074	draft	UPN:ClaimsManagement:Claims:Detective:2029804	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450870318	1450870318	\N	\N	\N	\N	\N	\N	\N	\N	\N
80842	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-80842	draft	UPN:ClaimsManagement:Claims:Detective:8358317	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450870374	1450870374	\N	\N	\N	\N	\N	\N	\N	\N	\N
5969	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-5969	draft	UPN:ClaimsManagement:Claims:Detective:2390073	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450870428	1450870428	\N	\N	\N	\N	\N	\N	\N	\N	\N
10495	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-10495	draft	UPN:ClaimsManagement:Claims:Detective:682883	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450870642	1450870642	\N	\N	\N	\N	\N	\N	\N	\N	\N
73254	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-73254	draft	UPN:ClaimsManagement:Claims:Detective:2994680	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450870724	1450870724	\N	\N	\N	\N	\N	\N	\N	\N	\N
35454	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-35454	draft	UPN:ClaimsManagement:Claims:Detective:2706556	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450874936	1450874936	\N	\N	\N	\N	\N	\N	\N	\N	\N
18114	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-18114	draft	UPN:ClaimsManagement:Claims:Detective:1388201	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450875556	1450875556	\N	\N	\N	\N	\N	\N	\N	\N	\N
63024	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-63024	draft	UPN:ClaimsManagement:Claims:Detective:6405080	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450875779	1450875779	\N	\N	\N	\N	\N	\N	\N	\N	\N
51521	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-51521	draft	UPN:ClaimsManagement:Claims:Detective:9584711	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450876993	1450876993	\N	\N	\N	\N	\N	\N	\N	\N	\N
2347	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-2347	draft	UPN:ClaimsManagement:Claims:Detective:4264301	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450877682	1450877682	\N	\N	\N	\N	\N	\N	\N	\N	\N
44674	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-44674	draft	UPN:ClaimsManagement:Claims:Detective:1352270	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450879670	1450879670	\N	\N	\N	\N	\N	\N	\N	\N	\N
44229	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-44229	draft	UPN:ClaimsManagement:Claims:Detective:8746959	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450879838	1450879838	\N	\N	\N	\N	\N	\N	\N	\N	\N
9253	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-9253	draft	UPN:ClaimsManagement:Claims:Detective:2032039	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450880930	1450880930	\N	\N	\N	\N	\N	\N	\N	\N	\N
9459	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-9459	draft	UPN:ClaimsManagement:Claims:Detective:963703	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450881369	1450881369	\N	\N	\N	\N	\N	\N	\N	\N	\N
94581	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-94581	draft	UPN:ClaimsManagement:Claims:Detective:2490332	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450881440	1450881440	\N	\N	\N	\N	\N	\N	\N	\N	\N
6555	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-6555	draft	UPN:ClaimsManagement:Claims:Detective:5536472	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450881523	1450881523	\N	\N	\N	\N	\N	\N	\N	\N	\N
90495	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-90495	draft	UPN:ClaimsManagement:Claims:Detective:4436916	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450881736	1450881736	\N	\N	\N	\N	\N	\N	\N	\N	\N
56093	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-56093	draft	UPN:ClaimsManagement:Claims:Detective:5822104	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450881993	1450881993	\N	\N	\N	\N	\N	\N	\N	\N	\N
28974	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-28974	draft	UPN:ClaimsManagement:Claims:Detective:6761103	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450882150	1450882150	\N	\N	\N	\N	\N	\N	\N	\N	\N
32224	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-32224	draft	UPN:ClaimsManagement:Claims:Detective:4778749	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450882209	1450882209	\N	\N	\N	\N	\N	\N	\N	\N	\N
20606	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-20606	draft	UPN:ClaimsManagement:Claims:Detective:4229417	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450882265	1450882265	\N	\N	\N	\N	\N	\N	\N	\N	\N
4656	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-4656	draft	UPN:ClaimsManagement:Claims:Detective:4401574	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450882687	1450882687	\N	\N	\N	\N	\N	\N	\N	\N	\N
54782	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-54782	draft	UPN:DMS:Complaints:Detective:8206503	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450883614	1450883614	\N	\N	\N	\N	\N	\N	\N	\N	\N
69615	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-69615	draft	UPN:DMS:Complaints:Detective:5594923	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450884454	1450884454	\N	\N	\N	\N	\N	\N	\N	\N	\N
36735	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-36735	draft	UPN:DMS:Complaints:Detective:6373168	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450884478	1450884478	\N	\N	\N	\N	\N	\N	\N	\N	\N
75099	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-75099	draft	UPN:DMS:Complaints:Detective:2725050	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450884517	1450884517	\N	\N	\N	\N	\N	\N	\N	\N	\N
37286	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-37286	draft	UPN:DMS:Complaints:Detective:4158936	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450884546	1450884546	\N	\N	\N	\N	\N	\N	\N	\N	\N
18454	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-18454	draft	UPN:DMS:Complaints:Detective:323488	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450884618	1450884618	\N	\N	\N	\N	\N	\N	\N	\N	\N
83561	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-83561	draft	UPN:DMS:Complaints:Detective:4391867	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450885694	1450885694	\N	\N	\N	\N	\N	\N	\N	\N	\N
40749	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-40749	draft	UPN:DMS:Complaints:Detective:7935611	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450885760	1450885760	\N	\N	\N	\N	\N	\N	\N	\N	\N
64037	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-64037	draft	UPN:DMS:Complaints:Detective:6961334	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450886078	1450886078	\N	\N	\N	\N	\N	\N	\N	\N	\N
91303	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-91303	draft	UPN:DMS:Complaints:Detective:3707341	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450886137	1450886137	\N	\N	\N	\N	\N	\N	\N	\N	\N
69362	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-69362	draft	UPN:DMS:Complaints:Detective:4827846	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450886219	1450886219	\N	\N	\N	\N	\N	\N	\N	\N	\N
24463	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-24463	draft	UPN:DMS:Complaints:Detective:2184305	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450886247	1450886247	\N	\N	\N	\N	\N	\N	\N	\N	\N
28651	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-28651	draft	UPN:DMS:Complaints:Detective:9089690	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450886326	1450886326	\N	\N	\N	\N	\N	\N	\N	\N	\N
81178	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-81178	draft	UPN:DMS:Complaints:Detective:6642650	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450886375	1450886375	\N	\N	\N	\N	\N	\N	\N	\N	\N
71546	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-71546	draft	UPN:DMS:Complaints:Detective:4307786	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450886727	1450886727	\N	\N	\N	\N	\N	\N	\N	\N	\N
91479	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-91479	draft	UPN:DMS:Complaints:Detective:2417709	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450887653	1450887653	\N	\N	\N	\N	\N	\N	\N	\N	\N
71757	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-71757	draft	UPN:DMS:Complaints:Detective:6398891	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450888124	1450888124	\N	\N	\N	\N	\N	\N	\N	\N	\N
47009	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-47009	draft	UPN:DMS:Complaints:Detective:4910796	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450892156	1450892156	\N	\N	\N	\N	\N	\N	\N	\N	\N
71533	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-71533	draft	UPN:DMS:Complaints:Detective:3942767	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450898548	1450898548	\N	\N	\N	\N	\N	\N	\N	\N	\N
20828	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-20828	draft	UPN:DMS:Complaints:Detective:3881862	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450898569	1450898569	\N	\N	\N	\N	\N	\N	\N	\N	\N
23697	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-23697	draft	UPN:DMS:Complaints:Detective:4098647	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450898575	1450898575	\N	\N	\N	\N	\N	\N	\N	\N	\N
18795	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-18795	draft	UPN:DMS:Complaints:Detective:5700971	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450898588	1450898588	\N	\N	\N	\N	\N	\N	\N	\N	\N
50650	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-50650	draft	UPN:DMS:Complaints:Detective:7890792	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450898593	1450898593	\N	\N	\N	\N	\N	\N	\N	\N	\N
46446	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-46446	draft	UPN:DMS:Complaints:Detective:5898334	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450898605	1450898605	\N	\N	\N	\N	\N	\N	\N	\N	\N
53434	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-53434	draft	UPN:DMS:Complaints:Detective:2179947	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450898615	1450898615	\N	\N	\N	\N	\N	\N	\N	\N	\N
93746	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-93746	draft	UPN:DMS:Complaints:Detective:2621375	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450898622	1450898622	\N	\N	\N	\N	\N	\N	\N	\N	\N
13470	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-13470	draft	UPN:DMS:Complaints:Detective:2299634	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450898630	1450898630	\N	\N	\N	\N	\N	\N	\N	\N	\N
94619	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-94619	draft	UPN:DMS:Complaints:Detective:4690265	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450898643	1450898643	\N	\N	\N	\N	\N	\N	\N	\N	\N
84412	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-84412	draft	UPN:DMS:Complaints:Detective:6898863	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450898667	1450898667	\N	\N	\N	\N	\N	\N	\N	\N	\N
77321	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-77321	draft	UPN:DMS:Complaints:Detective:1720122	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450898680	1450898680	\N	\N	\N	\N	\N	\N	\N	\N	\N
945606534	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-945606534	draft	UPN:DMS:Complaints:Detective:4777608	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450899864	1450899864	\N	\N	\N	\N	\N	\N	\N	\N	\N
80419319	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-80419319	draft	UPN:DMS:Complaints:Detective:9835295	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450899951	1450899951	\N	\N	\N	\N	\N	\N	\N	\N	\N
381237014	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-381237014	draft	UPN:DMS:Complaints:Detective:851172	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450899973	1450899973	\N	\N	\N	\N	\N	\N	\N	\N	\N
527341723	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-527341723	draft	UPN:DMS:Complaints:Detective:1186431	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450900016	1450900016	\N	\N	\N	\N	\N	\N	\N	\N	\N
333290399	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-333290399	draft	UPN:DMS:Complaints:Detective:5577265	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450900460	1450900460	\N	\N	\N	\N	\N	\N	\N	\N	\N
31622799	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-31622799	draft	UPN:DMS:Complaints:Detective:8420347	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450900978	1450900978	\N	\N	\N	\N	\N	\N	\N	\N	\N
666091008	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-666091008	draft	UPN:DMS:Complaints:Detective:3755945	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450901042	1450901042	\N	\N	\N	\N	\N	\N	\N	\N	\N
902287149	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-902287149	draft	UPN:DMS:Complaints:Detective:1280317	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450901759	1450901759	\N	\N	\N	\N	\N	\N	\N	\N	\N
922434210	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-922434210	draft	UPN:DMS:Complaints:Detective:6101132	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450906088	1450906088	\N	\N	\N	\N	\N	\N	\N	\N	\N
79219216	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-79219216	draft	UPN:DMS:Complaints:Detective:7881376	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450906137	1450906137	\N	\N	\N	\N	\N	\N	\N	\N	\N
261312369	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-261312369	draft	UPN:DMS:Complaints:Detective:2075458	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450906222	1450906222	\N	\N	\N	\N	\N	\N	\N	\N	\N
52092838	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-52092838	draft	UPN:DMS:Complaints:Detective:2945915	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450906289	1450906289	\N	\N	\N	\N	\N	\N	\N	\N	\N
371981970	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-371981970	draft	UPN:DMS:Complaints:Detective:7569501	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450906340	1450906340	\N	\N	\N	\N	\N	\N	\N	\N	\N
686777852	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-686777852	draft	UPN:DMS:Complaints:Detective:2270789	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450906466	1450906466	\N	\N	\N	\N	\N	\N	\N	\N	\N
492293304	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-492293304	draft	UPN:DMS:Complaints:Detective:2822696	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450944315	1450944315	\N	\N	\N	\N	\N	\N	\N	\N	\N
466804453	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-466804453	draft	UPN:DMS:Complaints:Detective:473302	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450944590	1450944590	\N	\N	\N	\N	\N	\N	\N	\N	\N
857769574	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-857769574	draft	UPN:DMS:Complaints:Detective:4738181	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450944641	1450944641	\N	\N	\N	\N	\N	\N	\N	\N	\N
246339520	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-246339520	draft	UPN:DMS:Complaints:Detective:4350274	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450945173	1450945173	\N	\N	\N	\N	\N	\N	\N	\N	\N
256191035	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-256191035	draft	UPN:DMS:Complaints:Detective:1447584	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450945243	1450945243	\N	\N	\N	\N	\N	\N	\N	\N	\N
857082275	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-857082275	draft	UPN:DMS:Complaints:Detective:2496188	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450945730	1450945730	\N	\N	\N	\N	\N	\N	\N	\N	\N
166875968	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-166875968	draft	UPN:DMS:Complaints:Detective:2885617	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450951557	1450951557	\N	\N	\N	\N	\N	\N	\N	\N	\N
248008912	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-248008912	draft	UPN:DMS:Complaints:Detective:6251748	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450952386	1450952386	\N	\N	\N	\N	\N	\N	\N	\N	\N
419136596	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-419136596	draft	UPN:DMS:Complaints:Detective:9629424	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450952840	1450952840	\N	\N	\N	\N	\N	\N	\N	\N	\N
493998087	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-493998087	draft	UPN:DMS:Complaints:Detective:3834893	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450953674	1450953674	\N	\N	\N	\N	\N	\N	\N	\N	\N
577597593	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-577597593	draft	UPN:DMS:Complaints:Detective:5964572	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450953731	1450953731	\N	\N	\N	\N	\N	\N	\N	\N	\N
457463476	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-457463476	draft	UPN:DMS:Complaints:Detective:1495895	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450953771	1450953771	\N	\N	\N	\N	\N	\N	\N	\N	\N
427638658	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-427638658	draft	UPN:DMS:Complaints:Detective:5285673	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450953809	1450953809	\N	\N	\N	\N	\N	\N	\N	\N	\N
45810118	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-45810118	draft	UPN:DMS:Complaints:Detective:1786977	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450953949	1450953949	\N	\N	\N	\N	\N	\N	\N	\N	\N
52958164	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-52958164	draft	UPN:DMS:Complaints:Detective:9664830	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450954202	1450954202	\N	\N	\N	\N	\N	\N	\N	\N	\N
86680722	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-86680722	draft	UPN:DMS:Complaints:Detective:5835202	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450954810	1450954810	\N	\N	\N	\N	\N	\N	\N	\N	\N
142489367	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-142489367	draft	UPN:DMS:Complaints:Detective:2542523	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450954943	1450954943	\N	\N	\N	\N	\N	\N	\N	\N	\N
302796112	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-302796112	draft	UPN:DMS:Complaints:Detective:1519463	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450955009	1450955009	\N	\N	\N	\N	\N	\N	\N	\N	\N
371402645	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-371402645	draft	UPN:DMS:Complaints:Detective:5171992	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450955075	1450955075	\N	\N	\N	\N	\N	\N	\N	\N	\N
460270409	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-460270409	draft	UPN:DMS:Complaints:Detective:4255152	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450955247	1450955247	\N	\N	\N	\N	\N	\N	\N	\N	\N
101634165	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-101634165	draft	UPN:DMS:Complaints:Detective:1677048	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450955304	1450955304	\N	\N	\N	\N	\N	\N	\N	\N	\N
452247101	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-452247101	draft	UPN:DMS:Complaints:Detective:8974982	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450955422	1450955422	\N	\N	\N	\N	\N	\N	\N	\N	\N
49348577	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-49348577	draft	UPN:DMS:Complaints:Detective:1178393	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450955452	1450955452	\N	\N	\N	\N	\N	\N	\N	\N	\N
501200479	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-501200479	draft	UPN:DMS:Complaints:Detective:2128833	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450955739	1450955739	\N	\N	\N	\N	\N	\N	\N	\N	\N
324755517	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-324755517	draft	UPN:DMS:Complaints:Detective:5338130	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450955849	1450955849	\N	\N	\N	\N	\N	\N	\N	\N	\N
185591520	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-185591520	draft	UPN:DMS:Complaints:Detective:7742712	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450955913	1450955913	\N	\N	\N	\N	\N	\N	\N	\N	\N
217910313	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-217910313	draft	UPN:DMS:Complaints:Detective:7019781	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450956039	1450956039	\N	\N	\N	\N	\N	\N	\N	\N	\N
234661696	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-234661696	draft	UPN:DMS:Complaints:Detective:1440179	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450957184	1450957184	\N	\N	\N	\N	\N	\N	\N	\N	\N
589718445	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-589718445	draft	UPN:DMS:Complaints:Detective:3897283	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450957900	1450957900	\N	\N	\N	\N	\N	\N	\N	\N	\N
329052225	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-329052225	draft	UPN:DMS:Complaints:Detective:8232331	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450957981	1450957981	\N	\N	\N	\N	\N	\N	\N	\N	\N
383194579	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-383194579	draft	UPN:DMS:Complaints:Detective:1982793	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450958746	1450958746	\N	\N	\N	\N	\N	\N	\N	\N	\N
342301969	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-342301969	draft	UPN:DMS:Complaints:Detective:9931160	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450962458	1450962458	\N	\N	\N	\N	\N	\N	\N	\N	\N
247960871	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-247960871	draft	UPN:DMS:Complaints:Detective:929443	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450962573	1450962573	\N	\N	\N	\N	\N	\N	\N	\N	\N
734918560	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-734918560	draft	UPN:DMS:Complaints:Detective:2748721	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450963795	1450963795	\N	\N	\N	\N	\N	\N	\N	\N	\N
789815192	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-789815192	draft	UPN:DMS:Complaints:Detective:1350485	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450976905	1450976905	\N	\N	\N	\N	\N	\N	\N	\N	\N
459155993	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-459155993	draft	UPN:DMS:Complaints:Detective:8532375	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450976964	1450976964	\N	\N	\N	\N	\N	\N	\N	\N	\N
560838728	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-560838728	draft	UPN:DMS:Complaints:Detective:1435562	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450982951	1450982951	\N	\N	\N	\N	\N	\N	\N	\N	\N
901393586	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-901393586	draft	UPN:DMS:Complaints:Detective:3351236	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450982988	1450982988	\N	\N	\N	\N	\N	\N	\N	\N	\N
274247059	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-274247059	draft	UPN:DMS:Complaints:Detective:8979238	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450983295	1450983295	\N	\N	\N	\N	\N	\N	\N	\N	\N
140913968	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-140913968	draft	UPN:DMS:Complaints:Detective:2598695	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450983353	1450983353	\N	\N	\N	\N	\N	\N	\N	\N	\N
152531260	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-152531260	draft	UPN:DMS:Complaints:Detective:8969438	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450983466	1450983466	\N	\N	\N	\N	\N	\N	\N	\N	\N
142740336	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-142740336	draft	UPN:DMS:Complaints:Detective:8921241	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450983552	1450983552	\N	\N	\N	\N	\N	\N	\N	\N	\N
627935130	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-627935130	draft	UPN:DMS:Complaints:Detective:9273081	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450983835	1450983835	\N	\N	\N	\N	\N	\N	\N	\N	\N
83269588	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-83269588	draft	UPN:DMS:Complaints:Detective:2631267	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450988197	1450988197	\N	\N	\N	\N	\N	\N	\N	\N	\N
780433224	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-780433224	draft	UPN:DMS:Complaints:Detective:3512483	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450988335	1450988335	\N	\N	\N	\N	\N	\N	\N	\N	\N
686846435	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-686846435	draft	UPN:DMS:Complaints:Detective:5765944	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450988844	1450988844	\N	\N	\N	\N	\N	\N	\N	\N	\N
557108977	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-557108977	draft	UPN:DMS:Complaints:Detective:3884279	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450989162	1450989162	\N	\N	\N	\N	\N	\N	\N	\N	\N
595405633	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-595405633	draft	UPN:DMS:Complaints:Detective:5616549	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450990122	1450990122	\N	\N	\N	\N	\N	\N	\N	\N	\N
493767787	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-493767787	draft	UPN:DMS:Complaints:Detective:2533061	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450990328	1450990328	\N	\N	\N	\N	\N	\N	\N	\N	\N
789343848	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-789343848	draft	UPN:DMS:Complaints:Detective:8425601	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450990589	1450990589	\N	\N	\N	\N	\N	\N	\N	\N	\N
922576485	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-922576485	draft	UPN:DMS:Complaints:Detective:3340085	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450990998	1450990998	\N	\N	\N	\N	\N	\N	\N	\N	\N
596032476	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-596032476	draft	UPN:DMS:Complaints:Detective:6585034	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450991162	1450991162	\N	\N	\N	\N	\N	\N	\N	\N	\N
870065425	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-870065425	draft	UPN:DMS:Complaints:Detective:5953222	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450991363	1450991363	\N	\N	\N	\N	\N	\N	\N	\N	\N
248290259	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-248290259	draft	UPN:DMS:Complaints:Detective:1179513	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450991383	1450991383	\N	\N	\N	\N	\N	\N	\N	\N	\N
176714709	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-176714709	draft	UPN:DMS:Complaints:Detective:7196927	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450991484	1450991484	\N	\N	\N	\N	\N	\N	\N	\N	\N
14386492	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-14386492	draft	UPN:DMS:Complaints:Detective:7888098	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450991508	1450991508	\N	\N	\N	\N	\N	\N	\N	\N	\N
481053919	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-481053919	draft	UPN:DMS:Complaints:Detective:6790590	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450995427	1450995427	\N	\N	\N	\N	\N	\N	\N	\N	\N
499529149	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-499529149	draft	UPN:DMS:Complaints:Detective:6241402	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450995473	1450995473	\N	\N	\N	\N	\N	\N	\N	\N	\N
476908423	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-476908423	draft	UPN:DMS:Complaints:Detective:5319655	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450995671	1450995671	\N	\N	\N	\N	\N	\N	\N	\N	\N
257023560	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-257023560	draft	UPN:DMS:Complaints:Detective:5664427	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450995835	1450995835	\N	\N	\N	\N	\N	\N	\N	\N	\N
407795028	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-407795028	draft	UPN:DMS:Complaints:Detective:5063411	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450995855	1450995855	\N	\N	\N	\N	\N	\N	\N	\N	\N
906911829	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-906911829	draft	UPN:DMS:Complaints:Detective:1206002	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450995891	1450995891	\N	\N	\N	\N	\N	\N	\N	\N	\N
145756254	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-145756254	draft	UPN:DMS:Complaints:Detective:564917	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450996000	1450996000	\N	\N	\N	\N	\N	\N	\N	\N	\N
124078407	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-124078407	draft	UPN:DMS:Complaints:Detective:8175218	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450996031	1450996031	\N	\N	\N	\N	\N	\N	\N	\N	\N
331233774	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-331233774	draft	UPN:DMS:Complaints:Detective:3334178	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450996085	1450996085	\N	\N	\N	\N	\N	\N	\N	\N	\N
627347372	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-627347372	draft	UPN:DMS:Complaints:Detective:6882567	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450996134	1450996134	\N	\N	\N	\N	\N	\N	\N	\N	\N
980033392	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-980033392	draft	UPN:DMS:Complaints:Detective:9596960	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450996143	1450996143	\N	\N	\N	\N	\N	\N	\N	\N	\N
373015188	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-373015188	draft	UPN:DMS:Complaints:Detective:7217498	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450996309	1450996309	\N	\N	\N	\N	\N	\N	\N	\N	\N
461363868	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-461363868	draft	UPN:DMS:Complaints:Detective:5459503	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450996318	1450996318	\N	\N	\N	\N	\N	\N	\N	\N	\N
476401664	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-476401664	draft	UPN:DMS:Complaints:Detective:8237744	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450996564	1450996564	\N	\N	\N	\N	\N	\N	\N	\N	\N
136050801	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-136050801	draft	UPN:DMS:Complaints:Detective:9985168	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450996622	1450996622	\N	\N	\N	\N	\N	\N	\N	\N	\N
81180771	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-81180771	draft	UPN:DMS:Complaints:Detective:4182642	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450996800	1450996800	\N	\N	\N	\N	\N	\N	\N	\N	\N
683194524	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-683194524	draft	UPN:DMS:Complaints:Detective:352661	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450997016	1450997016	\N	\N	\N	\N	\N	\N	\N	\N	\N
620759333	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-620759333	draft	UPN:DMS:Complaints:Detective:7767042	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450997114	1450997114	\N	\N	\N	\N	\N	\N	\N	\N	\N
656996986	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-656996986	draft	UPN:DMS:Complaints:Detective:1601159	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450997176	1450997176	\N	\N	\N	\N	\N	\N	\N	\N	\N
301715020	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-301715020	draft	UPN:DMS:Complaints:Detective:9917255	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450997234	1450997234	\N	\N	\N	\N	\N	\N	\N	\N	\N
449877529	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-449877529	draft	UPN:DMS:Complaints:Detective:8976679	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450997365	1450997365	\N	\N	\N	\N	\N	\N	\N	\N	\N
53835733	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-53835733	draft	UPN:DMS:Complaints:Detective:9073128	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450997397	1450997397	\N	\N	\N	\N	\N	\N	\N	\N	\N
109248746	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-109248746	draft	UPN:DMS:Complaints:Detective:5668205	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450997440	1450997440	\N	\N	\N	\N	\N	\N	\N	\N	\N
517282179	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-517282179	draft	UPN:DMS:Complaints:Detective:9307543	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450997462	1450997462	\N	\N	\N	\N	\N	\N	\N	\N	\N
527435919	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-527435919	draft	UPN:DMS:Complaints:Detective:9555854	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450997467	1450997467	\N	\N	\N	\N	\N	\N	\N	\N	\N
600001123	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-600001123	draft	UPN:DMS:Complaints:Detective:7061356	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450997472	1450997472	\N	\N	\N	\N	\N	\N	\N	\N	\N
459459848	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-459459848	draft	UPN:DMS:Complaints:Detective:4766718	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450997669	1450997669	\N	\N	\N	\N	\N	\N	\N	\N	\N
955812393	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-955812393	draft	UPN:DMS:Complaints:Detective:558688	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450997797	1450997797	\N	\N	\N	\N	\N	\N	\N	\N	\N
99880261	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-99880261	draft	UPN:DMS:Complaints:Detective:1232326	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450998313	1450998313	\N	\N	\N	\N	\N	\N	\N	\N	\N
616126115	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-616126115	draft	UPN:DMS:Complaints:Detective:4682496	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450998319	1450998319	\N	\N	\N	\N	\N	\N	\N	\N	\N
458829957	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-458829957	draft	UPN:DMS:Complaints:Detective:3612166	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450998376	1450998376	\N	\N	\N	\N	\N	\N	\N	\N	\N
107565892	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-107565892	draft	UPN:DMS:Complaints:Detective:9884801	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450998410	1450998410	\N	\N	\N	\N	\N	\N	\N	\N	\N
787950343	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-787950343	draft	UPN:DMS:Complaints:Detective:1702560	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450998691	1450998691	\N	\N	\N	\N	\N	\N	\N	\N	\N
524138574	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-524138574	draft	UPN:DMS:Complaints:Detective:7017492	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450998839	1450998839	\N	\N	\N	\N	\N	\N	\N	\N	\N
466254081	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-466254081	draft	UPN:DMS:Complaints:Detective:1392497	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450998898	1450998898	\N	\N	\N	\N	\N	\N	\N	\N	\N
574177438	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-574177438	draft	UPN:DMS:Complaints:Detective:4501336	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450998905	1450998905	\N	\N	\N	\N	\N	\N	\N	\N	\N
793640511	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-793640511	draft	UPN:DMS:Complaints:Detective:5373235	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450998948	1450998948	\N	\N	\N	\N	\N	\N	\N	\N	\N
542394278	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-542394278	draft	UPN:DMS:Complaints:Detective:794099	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450998970	1450998970	\N	\N	\N	\N	\N	\N	\N	\N	\N
169966088	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-169966088	draft	UPN:DMS:Complaints:Detective:3156582	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450998982	1450998982	\N	\N	\N	\N	\N	\N	\N	\N	\N
739530179	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-739530179	draft	UPN:DMS:Complaints:Detective:100208	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450998993	1450998993	\N	\N	\N	\N	\N	\N	\N	\N	\N
688691245	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-688691245	draft	UPN:DMS:Complaints:Detective:1198313	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450999236	1450999236	\N	\N	\N	\N	\N	\N	\N	\N	\N
547562914	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-547562914	draft	UPN:DMS:Complaints:Detective:6319194	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450999430	1450999430	\N	\N	\N	\N	\N	\N	\N	\N	\N
427941201	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-427941201	draft	UPN:DMS:Complaints:Detective:3457407	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450999569	1450999569	\N	\N	\N	\N	\N	\N	\N	\N	\N
162841469	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-162841469	draft	UPN:DMS:Complaints:Detective:6083422	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450999656	1450999656	\N	\N	\N	\N	\N	\N	\N	\N	\N
221406291	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-221406291	draft	UPN:DMS:Complaints:Detective:1702296	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450999673	1450999673	\N	\N	\N	\N	\N	\N	\N	\N	\N
777650375	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-777650375	draft	UPN:DMS:Complaints:Detective:6602503	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1450999765	1450999765	\N	\N	\N	\N	\N	\N	\N	\N	\N
274364370	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-274364370	draft	UPN:DMS:Complaints:Detective:9270902	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1451047096	1451047096	\N	\N	\N	\N	\N	\N	\N	\N	\N
102307799	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-102307799	draft	UPN:DMS:Complaints:Detective:8481649	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1453040454	1453040454	910842530	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:680501005}	\N	\N	\N	\N	\N
367366593	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-367366593	draft	UPN:DMS:Complaints:Detective:7383856	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1453067729	1453067729	9321978	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:680501005}	\N	\N	\N	\N	\N
88214355	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	null-88214355	draft	UPN:DMS:Complaints:Detective:3919838	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1453721601	1453721601	858244639	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:680501005}	\N	\N	\N	\N	\N
404331552	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	C_IS-646101	ProtocolExtendRisk	UPN:DMS:Complaints:Detective:5385431	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1453750447	1453750447	190664794	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:680501005}	\N	1141578098	\N	\N	\N
925512033	\N	\N	1109817505	{1241886272,1404952896}	\N	\N	\N	\N	\N	{/original/urn-Document-Detective-C_IS-925512033/1-8852245.jpg}	\N	2016-01-25	login - max@attracti.com   pass - 123\nlogin - inna@attracti.com   pass - guefog79	2016-01-26	login - max@attracti.com   pass - 123\nlogin - inna@attracti.com   pass - guefog79	1	login - max@attracti.com   pass - 123\nlogin - inna@attracti.com   pass - guefog79	C_IS-850805	Route	UPN:DMS:Complaints:Detective:9553693	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1453930042	1453930042	372158522	{156436774}	login - max@attracti.com   pass - 123\nlogin - inna@attracti.com   pass - guefog79	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	1560556052	{21899740,1365100333}	\N	\N
120327729	\N	\N	536231274	{275450805,1241886272}	{10341110,1293144792}	\N	\N	\N	\N	{/original/urn-Document-Detective-C_IS-120327729/eco_leaves_160110-3041171.jpg}	\N	2016-01-28	ьопмбо	2016-01-28	тасьрпсмрь	1	ьрпмром	C_IS-940425	Route	UPN:DMS:Complaints:Detective:3155309	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1453932748	1453932748	40376504	{156436774}	ьпмборм	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	1646346974	{2019794579,408480846}	\N	\N
259035838	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	C_IS-585578	ProtocolExtendRisk	UPN:DMS:Complaints:Detective:1202257	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1454413978	1454413978	847144382	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	535453587	\N	\N	\N
857294619	\N	\N	1982566019	{1724728515,990258053}	{643421900,463803818}	\N	\N	\N	\N	{/original/urn-Document-Detective-C_IS-857294619/6db754a0ab36904698e60410f59de999-6492810.jpg}	\N	2016-02-24	все	2016-02-25	Факты	1	Материалы	C_IS-786018	ProtocolExtendRisk	UPN:DMS:Complaints:Detective:7006567	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456410044	1456410044	76828139	{24913852,76828139,839948176,226146143,1806352408,943481548}	Итого	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	519158613	{1936997503,59619772}	\N	\N
\.


--
-- Data for Name: Document_Detective_C_IV; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Detective_C_IV" (id, "CompanyLegalEntityCounterparty", warehouse, responsible, "commissionmember_ManagementPostIndividual", "checkbo_BusinessObjectRecordPolymorph", datestart, dateend, actual, description, attachments, troublefix, troublefixdate, troubleevent, investigationdate, factdetected, complaintstatus, materialsused, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "DocumentComplaintC_IV", "internaldocuments_DMSDocumentUniversal", conclusion, basevisants, additionalvisants, "DMSDocumentUniversal", "deviations_DirectoryDeviationPreCapa", "riskapproved_RiskManagementRiskApproved", "risknotapproved_RiskManagementRiskNotApproved") FROM stdin;
916029756	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Detective_C_IW; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Detective_C_IW" (id, "CompanyLegalEntityCounterparty", warehouse, responsible, "commissionmember_ManagementPostIndividual", "checkbo_BusinessObjectRecordPolymorph", datestart, dateend, actual, description, attachments, troublefix, troublefixdate, troubleevent, investigationdate, factdetected, complaintstatus, materialsused, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "DocumentComplaintC_IW", "internaldocuments_DMSDocumentUniversal", conclusion, basevisants, additionalvisants, "DMSDocumentUniversal", "deviations_DirectoryDeviationPreCapa", "riskapproved_RiskManagementRiskApproved", "risknotapproved_RiskManagementRiskNotApproved") FROM stdin;
249027069	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Detective_C_LB; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Detective_C_LB" (id, "CompanyLegalEntityCounterparty", warehouse, responsible, "commissionmember_ManagementPostIndividual", "checkbo_BusinessObjectRecordPolymorph", datestart, dateend, actual, description, attachments, troublefix, troublefixdate, troubleevent, investigationdate, factdetected, complaintstatus, materialsused, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "DocumentComplaintC_LB", "internaldocuments_DMSDocumentUniversal", conclusion, basevisants, additionalvisants, "DMSDocumentUniversal", "deviations_DirectoryDeviationPreCapa", "riskapproved_RiskManagementRiskApproved", "risknotapproved_RiskManagementRiskNotApproved") FROM stdin;
1861845291	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Detective_C_LC; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Detective_C_LC" (id, "CompanyLegalEntityCounterparty", warehouse, responsible, "commissionmember_ManagementPostIndividual", "checkbo_BusinessObjectRecordPolymorph", datestart, dateend, actual, description, attachments, troublefix, troublefixdate, troubleevent, investigationdate, factdetected, complaintstatus, materialsused, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "DocumentComplaintC_LC", "internaldocuments_DMSDocumentUniversal", conclusion, basevisants, additionalvisants, "DMSDocumentUniversal", "deviations_DirectoryDeviationPreCapa", "riskapproved_RiskManagementRiskApproved", "risknotapproved_RiskManagementRiskNotApproved") FROM stdin;
490775629	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
583758799	\N	\N	1982566019	{990258053,697130132}	{206674127,1293144792}	\N	\N	\N	\N	{/original/urn-Document-Detective-C_LC-583758799/09b2deaf8c0293d395711b18c37f7e4d-2676268.jpg}	\N	2016-02-25	vdfvdfv	\N	dvdfvdfvdf	1	dfvdfvd	C_LC-985360	ProtocolExtendRisk	UPN:DMS:Complaints:Detective:5734686	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456416942	1456416942	88687448	{943481548}	fdvfdvdf	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	942913781	{1128633188,9264227}	\N	\N
\.


--
-- Data for Name: Document_Detective_C_LP; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Detective_C_LP" (id, "CompanyLegalEntityCounterparty", warehouse, responsible, "commissionmember_ManagementPostIndividual", "checkbo_BusinessObjectRecordPolymorph", datestart, dateend, actual, description, attachments, troublefix, troublefixdate, troubleevent, investigationdate, factdetected, complaintstatus, materialsused, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "DocumentComplaintC_LP", "internaldocuments_DMSDocumentUniversal", conclusion, basevisants, additionalvisants, "DMSDocumentUniversal", "deviations_DirectoryDeviationPreCapa", "riskapproved_RiskManagementRiskApproved", "risknotapproved_RiskManagementRiskNotApproved") FROM stdin;
78486256	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Detective_C_LT; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Detective_C_LT" (id, "CompanyLegalEntityCounterparty", warehouse, responsible, "commissionmember_ManagementPostIndividual", "checkbo_BusinessObjectRecordPolymorph", datestart, dateend, actual, description, attachments, troublefix, troublefixdate, troubleevent, investigationdate, factdetected, complaintstatus, materialsused, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "DocumentComplaintC_LT", "internaldocuments_DMSDocumentUniversal", conclusion, basevisants, additionalvisants, "DMSDocumentUniversal", "deviations_DirectoryDeviationPreCapa", "riskapproved_RiskManagementRiskApproved", "risknotapproved_RiskManagementRiskNotApproved") FROM stdin;
1491259954	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
25141543	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	ProtocolCreateDraft	UPN:DMS:Complaints:Detective:186257	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1453759754	1453759754	219904659	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N	\N	\N
234556002	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	ProtocolCreateDraft	UPN:DMS:Complaints:Detective:6539979	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1453760041	1453760041	593922708	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N	\N	\N
210057801	\N	\N	\N	\N	\N	\N	\N	\N	\N	{/original/urn-Document-Detective-C_LT-210057801/wallpaper-mirrors-edge-02-1920x1080-Video-Game-Wallpapers-4766382.jpg,/original/urn-Document-Detective-C_LT-210057801/cyberpunk-6011152.png}	\N	\N	dfvfdvfd	\N	dfvfdv	1	dfv	\N	ProtocolCreateDraft	UPN:DMS:Complaints:Detective:8183783	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1453760227	1453760227	138672545	\N	fdv	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N	\N	\N
4580071	\N	\N	\N	\N	\N	\N	\N	\N	\N	{}	\N	\N	\N	\N	\N	2	\N	\N	ProtocolCreateDraft	UPN:DMS:Complaints:Detective:9659306	\N	{}	\N	urn:Actor:User:System:1	\N	t	f	f	f	\N	\N	1456429771	1456429771	917825520	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	{285535151}	\N	\N
839948176	\N	\N	1816416714	{1724728515}	{10341110}	\N	\N	\N	\N	{/original/urn-Document-Detective-C_LT-839948176/30c5d3e8d80f6930efc11a2152d1e5a0-5938640.gif}	\N	2016-02-25	dfvfdv	2016-02-25	\N	1	dfvdfvdfv	C_LT-323120	ProtocolEditing	UPN:DMS:Complaints:Detective:150798	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456407234	1456407234	24913852	\N	dfvfdvdfv	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	226146143	{26480294}	\N	\N
\.


--
-- Data for Name: Document_Protocol_CT; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Protocol_CT" (id, bo, warehouse, client, "CalendarPeriodMonth", date, contractforcalibration, relateddocuments, results, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal", upload, "ResponsibleCalibration", datep) FROM stdin;
2053052152	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1453721961	\N	\N	\N	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Protocol_EA; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Protocol_EA" (id, commisionhead, date, results, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "commisionmembers_ManagementPostIndividual", attachments, basevisants, additionalvisants, "DMSDocumentUniversal", "boprocedure_BusinessObjectRecordPolymorph", datep) FROM stdin;
1386157813	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N	\N	\N	\N	\N
645752277	\N	\N	\N	EA-356463	CreateDraft	UPN:DMS:Process:Simple:2875848	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1454083819	1454083819	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	1496697982	\N	\N
\.


--
-- Data for Name: Document_Protocol_EC; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Protocol_EC" (id, commisionhead, date, results, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "commisionmembers_ManagementPostIndividual", attachments, basevisants, additionalvisants, "DMSDocumentUniversal", datep) FROM stdin;
394291712	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1451463203	\N	\N	\N	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Protocol_KI; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Protocol_KI" (id, "BusinessObjectRecordPolymorph", date, placetime, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal", "DocumentCapaDeviation") FROM stdin;
\.


--
-- Data for Name: Document_Protocol_MT; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Protocol_MT" (id, bo, warehouse, client, "CalendarPeriodMonth", date, relateddocuments, results, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, contractforverif, basevisants, additionalvisants, "DMSDocumentUniversal", upload, "ResponsibleVerification", datep) FROM stdin;
\.


--
-- Data for Name: Document_Protocol_RR; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Protocol_RR" (id, "BusinessObjectRecordPolymorph", "DirectoryBusinessProcessItem", "DMSDocumentUniversal", "riskapproved_RiskManagementRiskApproved", date, privatedraft, state, code, process, parent, children, related, initiator, authors, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "commissionmember_ManagementPostIndividual") FROM stdin;
415777268	\N	1937134718	1599893475	\N	\N	f	Route	RR-214531	UPN:DMS:Deviations:RISKReview:1812544	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	f	f	f	\N	\N	1454528655	1454528655	\N	\N	{1816416714,1118804000}
610217781	\N	\N	1252086105	\N	\N	f	Rating	RR-927987	urn:Directory:BusinessProcess:Item:1937134718	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	f	f	f	\N	\N	1454528451	1454528451	\N	\N	{1816416714}
301216038	\N	\N	775965035	\N	\N	f	Rating	RR-698336	urn:Directory:BusinessProcess:Item:1937134718	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	f	f	f	\N	\N	1454527681	1454527681	\N	\N	{1816416714}
771586990	\N	\N	1401803045	\N	\N	f	Route	RR-792501	urn:Directory:BusinessProcess:Item:1937134718	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	f	f	f	\N	\N	1454414043	1454414043	\N	\N	{1982566019,1109817505}
970088930	\N	\N	\N	\N	\N	t	Configuring	\N	urn:Directory:BusinessProcess:Item:1937134718	\N	{}	\N	urn:Management:Post:Individual:1118804000	\N	f	f	f	\N	\N	1454420224	1454420224	\N	\N	{1982566019}
\.


--
-- Data for Name: Document_Protocol_SI; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Protocol_SI" (id, "BusinessObjectRecordPolymorph", date, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal", "riskapproved_RiskManagementRiskApproved") FROM stdin;
964417872	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Protocol_TM; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Protocol_TM" (id, bo, responsiblemo, responsibleto, warehouse, client, "CalendarPeriodMonth", servicedate, relateddocuments, results, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, contractforcalibration, basevisants, additionalvisants, "DMSDocumentUniversal", upload, datep) FROM stdin;
1995982669	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Protocol_VT; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Protocol_VT" (id, bo, warehouse, client, "CalendarPeriodMonth", scaleapplication, "equipment_BusinessObjectRecordPolymorph", normativebase, consecutivenumber, latestcheck, nextcheck, chemicals, defabbr, masterpart, attachments, finalrecommend, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal", currentcheck, industryscope, "MateriallyResponsible", "ResponsibleMaintenance", "ResponsibleValidation", "ManagementPostIndividual_DirectoryResponsibletwoSimple", serialnumber, worktype, numberequipment, specification) FROM stdin;
135655498	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
315443799	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	CreateDraft	UPN:DMS:Process:SimpleWithPlan:4725380	\N	{}	\N	urn:Actor:User:System:1	\N	t	f	f	f	\N	\N	1454083904	1454083904	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
244456918	614895960	\N	\N	\N	966042166	\N	dfvdfvfdvddvfd	\N	\N	\N	dfvdf	dfvdf	dfv	{/original/urn-Document-Protocol-VT-244456918/KABINET-5390578.png}	dfvfd	\N	Planing	UPN:DMS:Process:SimpleWithPlan:2843363	\N	{}	\N	urn:Actor:User:System:1	\N	t	f	f	f	\N	\N	1454496702	1454496702	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
429764827	966042166	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	VT-453327	Configuring	UPN:DMS:Process:SimpleWithConfiguring:7665289	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1455570317	1455570317	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	1720069256	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Protocol_СТ; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Protocol_СТ" (id, bo, responsible, warehouse, client, "CalendarPeriodMonth", date, "сontractforcalibration", relateddocuments, results, upload, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated) FROM stdin;
1463917845	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N
\.


--
-- Data for Name: Document_Regulations_AO; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Regulations_AO" (id, title, preamble, textorder, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal", scaleapplication, "userprocedure_ManagementPostIndividual", target, effectivedate) FROM stdin;
1721554801	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450607134	\N	\N	\N	\N	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Regulations_ASR; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Regulations_ASR" (id, "DocumentRegulationsSOP", "DocumentRegulationsTA", "DMSDocumentUniversal", planneddate, realeventdate, privatedraft, state, code, process, parent, children, related, initiator, authors, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, plannedattendees, notpassed, successpassed, failedpassed) FROM stdin;
802472849	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:8798303	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456070415	1456070415	\N	\N	\N	\N	\N	\N
132972475	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:4064165	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456070738	1456070738	\N	\N	\N	\N	\N	\N
592149284	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:2908388	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456071110	1456071110	\N	\N	\N	\N	\N	\N
638791284	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:1977376	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456071444	1456071444	\N	\N	\N	\N	\N	\N
684577212	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:6896124	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456071985	1456071985	\N	\N	\N	\N	\N	\N
671721691	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:1630757	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456072403	1456072403	\N	\N	\N	\N	\N	\N
93175981	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:5768828	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456072531	1456072531	\N	\N	\N	\N	\N	\N
257807361	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:4473297	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456072871	1456072871	\N	\N	\N	\N	\N	\N
670617074	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:3183332	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456073082	1456073082	\N	\N	\N	\N	\N	\N
312581790	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:2270851	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456079006	1456079006	\N	\N	\N	\N	\N	\N
577030926	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:5041969	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456079302	1456079302	\N	\N	\N	\N	\N	\N
828216122	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:8967861	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456090673	1456090673	\N	\N	\N	\N	\N	\N
531006653	\N	\N	\N	\N	\N	t	Planing	\N	UPN:DMS:Regulation:Attestation:4973110	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456093632	1456093632	\N	\N	\N	\N	\N	\N
996062144	368434041	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:8419337	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456076474	1456076474	\N	\N	\N	\N	\N	\N
503367836	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:3466954	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456078240	1456078240	\N	\N	\N	\N	\N	\N
164852097	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:5079171	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456078848	1456078848	\N	\N	\N	\N	\N	\N
802387637	\N	\N	\N	\N	\N	t	Planing	\N	UPN:DMS:Regulation:Attestation:4222751	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456094095	1456094095	\N	\N	\N	\N	\N	\N
112491265	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:2392367	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456094595	1456094595	\N	\N	\N	\N	\N	\N
585935061	\N	\N	\N	\N	\N	t	Planing	\N	UPN:DMS:Regulation:Attestation:6403716	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456093975	1456093975	\N	\N	\N	\N	\N	\N
53937386	\N	\N	\N	\N	\N	t	Planing	\N	UPN:DMS:Regulation:Attestation:9395754	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456094418	1456094418	\N	\N	\N	\N	\N	\N
265901680	\N	\N	\N	\N	\N	t	Planing	\N	UPN:DMS:Regulation:Attestation:4200557	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456094353	1456094353	\N	\N	\N	\N	\N	\N
368486383	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:5708299	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456095017	1456095017	\N	\N	\N	\N	\N	\N
515014212	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:4961280	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456094932	1456094932	\N	\N	\N	\N	\N	\N
295966649	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:846346	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456095086	1456095086	\N	\N	\N	\N	\N	\N
873845501	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:78477	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456094831	1456094831	\N	\N	\N	\N	\N	\N
252994771	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:8924932	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456095275	1456095275	\N	\N	{urn:Management:Post:Individual:1433279225}	\N	\N	\N
240371641	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:4510692	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456095378	1456095378	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
797768388	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:7090707	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456256031	1456256031	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
308590261	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:9957222	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456095206	1456095206	\N	\N	{urn:Management:Post:Individual:1433279225}	\N	\N	\N
753998388	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:615282	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456256427	1456256427	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
530376885	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:7636578	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456256253	1456256253	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
8310513	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:422338	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456255871	1456255871	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
488348123	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:4037708	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456256174	1456256174	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
588348699	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:8592241	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456256355	1456256355	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
819862571	368434041	475792605	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:5661727	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456358109	1456358109	\N	\N	\N	\N	\N	\N
8262603	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:3913478	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456256621	1456256621	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
349686015	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:4646841	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456257381	1456257381	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
332158041	368434041	394179976	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:87199	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456358230	1456358230	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1118804000}	\N
193229566	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:1416124	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456261222	1456261222	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
225402643	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:6515905	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456337766	1456337766	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714}	\N
631739911	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:3380369	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456337637	1456337637	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
841950552	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:9285276	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456337440	1456337440	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
816606836	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:5214720	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456260761	1456260761	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
4329000	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:262882	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456337437	1456337437	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714}	\N
37563911	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:6837162	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456322710	1456322710	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714}	\N
783243990	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:7752194	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456317640	1456317640	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
660624397	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:8675090	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456263341	1456263341	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
974494812	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:6836499	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456260847	1456260847	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
400907695	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:69968	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456322168	1456322168	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714}	\N
257321921	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:1776958	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456337386	1456337386	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
592759514	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:7213802	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456337382	1456337382	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714}	\N
358071107	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:9106765	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456317971	1456317971	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
459005468	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:3008303	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456317556	1456317556	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
44455917	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:47819	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456337639	1456337639	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
389104319	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:4598608	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456337634	1456337634	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714}	\N
33328625	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:7752264	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456337769	1456337769	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
491117832	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:5787046	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456337834	1456337834	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
765252480	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:7678085	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456337830	1456337830	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714}	\N
803698608	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:6041352	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456338023	1456338023	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714}	\N
395907111	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:1851422	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456340810	1456340810	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
748803294	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:5837699	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456338027	1456338027	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
842716925	368434041	861199414	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:1032517	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456357531	1456357531	\N	\N	\N	\N	\N	\N
436023795	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:8123762	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456340807	1456340807	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714}	\N
175593148	\N	\N	\N	\N	\N	t	Planing	\N	UPN:DMS:Regulation:Attestation:7279001	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456352680	1456352680	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
560547904	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:4207887	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456340803	1456340803	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714}	\N
907105150	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:6496039	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456340322	1456340322	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
868064520	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:4368473	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456340318	1456340318	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714}	\N
479908575	\N	\N	\N	\N	\N	t	Planing	\N	UPN:DMS:Regulation:Attestation:962558	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456351863	1456351863	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
203064761	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:2779499	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456338319	1456338319	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
973561386	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:3637902	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456338316	1456338316	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714}	\N
282593396	\N	\N	\N	\N	\N	t	Planing	\N	UPN:DMS:Regulation:Attestation:7316015	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456351770	1456351770	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
560914928	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:8570510	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456352853	1456352853	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
929983709	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:6593917	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456351976	1456351976	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
64439753	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:1447764	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456352676	1456352676	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
60503958	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:8809456	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456351969	1456351969	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
507285703	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:6776121	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456351973	1456351973	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
267201577	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:4717641	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456352857	1456352857	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
468566989	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:1821422	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456352860	1456352860	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
111401838	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:1938839	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456352943	1456352943	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
955930808	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:2778630	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456352947	1456352947	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
483465163	368434041	332158041	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:4173661	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456358234	1456358234	\N	\N	{urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1433279225}	\N
350502602	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:4380397	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456352951	1456352951	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
975902160	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:7777639	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456353150	1456353150	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
296924380	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:3707387	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456353157	1456353157	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
393795437	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:4621920	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456353057	1456353057	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
127868015	\N	\N	\N	\N	\N	t	AttendeesSelection	\N	UPN:DMS:Regulation:Attestation:2229770	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456353060	1456353060	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
311766127	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:8237472	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456353405	1456353405	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
202664688	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:8060111	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456353408	1456353408	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
258115376	368434041	593975459	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:9936107	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456354275	1456354275	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
58447092	368434041	156450147	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:2037903	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456354672	1456354672	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
593975459	368434041	74206247	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:756383	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456354272	1456354272	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
736372881	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:5320542	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456353053	1456353053	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
361890020	\N	\N	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:5253144	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456353416	1456353416	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
613003384	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:9032740	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456353412	1456353412	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N
170319403	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:7337558	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456353153	1456353153	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
478711769	368434041	394341969	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:8520299	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456354283	1456354283	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
394341969	368434041	258115376	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:7532896	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456354279	1456354279	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N
348664436	368434041	58447092	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:8898881	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456354676	1456354676	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
451826770	368434041	343254898	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:3612840	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456354683	1456354683	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
343254898	368434041	348664436	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:8563265	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456354680	1456354680	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N
3888766	368434041	498724075	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:5591323	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456358146	1456358146	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1118804000}	\N
340988205	368434041	483465163	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:956708	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456358237	1456358237	\N	\N	\N	\N	\N	\N
272773675	368434041	748421803	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:1401229	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456354991	1456354991	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
572310260	368434041	679791809	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:1884630	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456354999	1456354999	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N
707697851	368434041	189846602	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:8894826	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456354770	1456354770	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
911687816	368434041	315090400	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:9378466	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456355296	1456355296	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
315090400	368434041	911263136	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:5450145	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456355293	1456355293	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N
189846602	368434041	959754415	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:4286294	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456354766	1456354766	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
186026462	368434041	133398121	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:7183983	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456357615	1456357615	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
911263136	368434041	729872363	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:2727978	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456355289	1456355289	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
305568924	368434041	9312633	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:2664702	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456354777	1456354777	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
679791809	368434041	272773675	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:6081677	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456354995	1456354995	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
9312633	368434041	707697851	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:4313489	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456354774	1456354774	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N
205481228	368434041	572310260	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:5796527	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456355002	1456355002	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
217462038	368434041	544429800	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:3787523	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456357995	1456357995	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
720916907	368434041	547805312	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:9953345	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456358443	1456358443	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
705796494	368434041	3888766	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:6105457	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456358150	1456358150	\N	\N	{urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1433279225}	\N
868905607	368434041	705796494	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:8033183	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456358154	1456358154	\N	\N	\N	\N	\N	\N
78364767	368434041	217462038	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:2442451	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456357999	1456357999	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
407915817	368434041	41808599	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:312861	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456358006	1456358006	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
985600808	368434041	896367215	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:7064431	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456357833	1456357833	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
524127230	368434041	720916907	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:3425055	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456358447	1456358447	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1118804000}	\N
731414761	368434041	8088822	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:1182125	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456358096	1456358096	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
394179976	368434041	805624862	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:3811104	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456358226	1456358226	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
729872363	368434041	122019848	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:484922	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456355285	1456355285	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225,urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
41808599	368434041	78364767	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:5474662	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456358002	1456358002	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N
498724075	368434041	505142207	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:1636492	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456358143	1456358143	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
875558023	368434041	985600808	\N	\N	\N	t	Testing	\N	UPN:DMS:Regulation:Attestation:25812	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456357836	1456357836	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
641871202	368434041	731414761	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:8742973	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456358102	1456358102	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1118804000}	\N
263456047	368434041	524127230	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:5104880	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456358450	1456358450	\N	\N	{urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1433279225}	\N
728830509	368434041	396454592	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:5944804	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456357080	1456357080	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
999530659	368434041	189910091	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:7859659	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456356327	1456356327	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
355177735	368434041	238139236	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:4383238	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456356945	1456356945	\N	\N	\N	\N	\N	\N
75059862	368434041	75532535	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:9600257	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456357163	1456357163	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N
62752438	\N	\N	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:8456998	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456356330	1456356330	\N	\N	\N	\N	\N	\N
233424866	368434041	598202109	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:2439977	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456356692	1456356692	\N	\N	\N	\N	\N	\N
897654974	368434041	796584242	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:8456461	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456357155	1456357155	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
918244700	368434041	540995730	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:4798672	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456356373	1456356373	\N	\N	\N	\N	\N	\N
996587897	368434041	694282483	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:2189008	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456357309	1456357309	\N	\N	\N	\N	\N	\N
75532535	368434041	897654974	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:2434568	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456357159	1456357159	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
376369169	368434041	163838185	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:3270590	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456356782	1456356782	\N	\N	\N	\N	\N	\N
861728649	368434041	728830509	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:7652857	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456357084	1456357084	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
505387091	368434041	578254565	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:2597620	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456356408	1456356408	\N	\N	\N	\N	\N	\N
300468586	368434041	266489350	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:84272	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456357091	1456357091	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
266489350	368434041	861728649	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:9473517	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456357087	1456357087	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N
258302573	368434041	75059862	\N	\N	\N	t	CreateDraft	\N	UPN:DMS:Regulation:Attestation:1577589	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456357166	1456357166	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	\N	\N
694282483	368434041	889615447	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:976129	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456357305	1456357305	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
148845252	368434041	665448315	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:4937514	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456356490	1456356490	\N	\N	\N	\N	\N	\N
538649202	368434041	556347460	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:950871	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456357469	1456357469	\N	\N	\N	\N	\N	\N
383102784	368434041	251751254	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:2022540	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456357604	1456357604	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
925999191	368434041	383102784	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:7182739	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456357608	1456357608	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
861199414	368434041	737545915	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:8576478	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456357528	1456357528	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714}	\N
133398121	368434041	925999191	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:8957725	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456357612	1456357612	\N	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1816416714,urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1433279225}	\N
461618820	368434041	875558023	\N	\N	\N	t	draft	\N	UPN:DMS:Regulation:Attestation:772465	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456357840	1456357840	\N	\N	\N	\N	\N	\N
475792605	368434041	641871202	\N	\N	\N	t	Route	\N	UPN:DMS:Regulation:Attestation:3212468	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	\N	\N	1456358105	1456358105	\N	\N	{urn:Management:Post:Individual:1433279225}	\N	{urn:Management:Post:Individual:1433279225}	\N
\.


--
-- Data for Name: Document_Regulations_I; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Regulations_I" (id, "DirectoryBusinessProcessItem", scaleapplication, "CalendarPeriodMonth", "boprocedure_BusinessObjectRecordPolymorph", "userprocedure_ManagementPostIndividual", title, fileprocessattachment, attachments, causeedit, effectivedate, enddate, target, realmuse, response, resource, procedure, extrachapter, report, docforlink, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal") FROM stdin;
1498524924	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450607240	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Regulations_JD; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Regulations_JD" (id, instructionname, "position", duty, authority, responsibility, conditions, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal", "userprocedure_ManagementPostIndividual", effectivedate) FROM stdin;
187959340	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Regulations_MP; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Regulations_MP" (id, "CalendarPeriodMonth", initialdate, lastdate, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal", policy) FROM stdin;
\.


--
-- Data for Name: Document_Regulations_P; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Regulations_P" (id, "DirectoryBusinessProcessItem", scaleapplication, "CalendarPeriodMonth", "boprocedure_BusinessObjectRecordPolymorph", "userprocedure_ManagementPostIndividual", title, fileprocessattachment, attachments, causeedit, effectivedate, enddate, target, realmuse, response, resource, procedure, extrachapter, report, docforlink, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal") FROM stdin;
1782456960	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Regulations_PV; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Regulations_PV" (id, "BusinessObjectRecordPolymorph", programm, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal", title) FROM stdin;
902244546	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450607134	\N	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Regulations_SOP; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Regulations_SOP" (id, "DirectoryBusinessProcessItem", scaleapplication, "CalendarPeriodMonth", "boprocedure_BusinessObjectRecordPolymorph", "userprocedure_ManagementPostIndividual", title, trainingdocument, fileprocessattachment, attachments, causeedit, effectivedate, enddate, target, realmuse, response, resource, procedure, extrachapter, report, docforlink, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "ManagementPostIndividual", basevisants, additionalvisants, "userproceduregroup_ManagementPostGroup", "DMSDocumentUniversal") FROM stdin;
1602858893	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N	\N	\N
269476718	1355639494	\N	47836635	\N	{1118804000}	hhkjhk	1	\N	\N	\N	\N	\N	kjnkjnkjn	\N	\N	\N	\N	\N	\N	\N	SOP-269476718	draft	UPN:DMS:Regulation:SOP:4575449	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1453130582	1453130582	1118804000	{urn:Management:Post:Individual:275450805,urn:Management:Post:Individual:1724728515}	\N	\N	\N
580061599	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	SOP-580061599	draft	UPN:DMS:Regulation:SOP:319005	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1453130661	1453130661	\N	{urn:Management:Post:Individual:1118804000}	\N	\N	\N
569726514	1937134718	206674127	246037407	{10341110}	{1118804000,1962987279}	SOP 123	1	\N	\N	\N	\N	\N	1	2	3	4	5	\N	отчет	ссылки	SOP-569726514	draft	UPN:DMS:Regulation:SOP:1396272	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1453130918	1453130918	1118804000	{urn:Management:Post:Individual:1118804000}	\N	\N	\N
432473727	1937134718	1293144792	464841811	{463803818}	{406611786,1601586017}	неавасмидшгн	1	/original/urn-Document-Regulations-SOP-432473727/1-4883761.jpg	\N	\N	2016-01-29	\N	<p>джщшгнекуывчмитбь</p>	<p>логнеквачспмить</p>	<p>шгнекувапрмоит</p>	<p>логнекуывчапсрмоилт</p>	<p>гнекукывапсрмоилт</p>	\N	<p>гнекуыквчаспрмоил</p>	<p>лорпнеакввапро</p>	SOP-474810	Route	UPN:DMS:Regulation:SOP:5693880	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1454005611	1454005611	1816416714	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	337257777
864702321	1937134718	614895960	246037407	{2064133034}	\N	Гигиена в помещениях склада	1	/original/urn-Document-Regulations-SOP-864702321/eco_leaves_160110-2382260.jpg	{/original/urn-Document-Regulations-SOP-864702321/route-1541544.docx}	\N	2016-01-28	\N	<p>Обеспечение необходимого уровня личной гигиены в помещениях склада</p>	<p>Выполняется постоянно при работе в помещениях склада.</p>	<p>Отвечает за выполнение требований личной гигиены все сотрудники ТЛС. Контролирует выполнение требований настоящей СОП заведующий ТЛС. Сотрудники ТЛС имеет право требовать от директора, обеспечения всеми необходимыми ресурсами для выполнения требований настоящей СОП.</p>	<ol><li>Спецодежда – 2 комплекта на 1 человека.</li>\n<li>Раковины для мытья рук – 3 шт.</li>\n<li>Средство для дезинфекции рук «Бонадерм».</li>\n<li>Моющие средства.</li>\n<li>Резиновые коврики – 7 шт.</li>\n<li>Оборудованные туалетные комнаты – 2 шт.</li>\n<li>Шкаф для хранения одежды – 10 шт.</li>\n<li>Сменная обувь</li>\n</ol>	<ol><li>Общие положения\n<ol><li>Настоящая СОП содержит основные требования к личной гигиене работников ТЛС.</li>\n<li>Санитарно-технические приборы, оборудование, краны, раковины, унитазы и т.д., должны быть в исправном состоянии. Систематически чиститься от ржавчины и других наслоений, не иметь трещин и других дефектов.</li>\n<li>Места возле раковин, других санитарно-технических приборов, а также возле оборудования, во время эксплуатации которого возможно увлажнение стен, облицовывают плиткой или другими влагостойкими материалами.</li>\n<li>Светильники должны быть закрытого типа и доступные для влажной обработки.</li>\n<li>Запрещается размещать в производственных помещениях оборудование, которое не имеет отношения как исполняемым работам.</li>\n<li>Информационные стенды и таблицы, которые необходимы для работы в производственных помещениях, должны быть изготовлены из материалов, которые способствуют их влажной уборке и дезинфекции.</li>\n<li>В производственных помещениях не разрешается вешать занавески, расстилать ковры, разводить цветы, вывешивать стенные газеты, плакаты.</li>\n<li>В производственных помещениях запрещается хранение и употребление личных лекарственных средств, пищевых продуктов, в том числе жевательной резинки, напитков, табачных изделий и курение.</li>\n<li>Запрещаются любые действия, которые нарушают гигиенические требования внутри производственных помещений или других зон, если они могут негативно влиять на качество продукции.</li>\n<li>Доступ в производственные помещения и зоны контроля качества должен быть разрешен уполномоченному на это персоналу и контролироваться. Посетители и работники, которые не прошли обучение, должны предварительно пройти инструктаж, в частности относительно гигиенических требований к персоналу, при необходимости в присутствии необходимого сопровождения.</li>\n<li>Возле входа в туалет на полу должен быть резиновый коврик, смоченный дезинфецирующим раствором.</li>\n<li>Для мытья рук персонала в туалетах установлены раковины. Непосредственно возле раковин постоянно находятся моющие средства, средства для дезинфекции рук, зарегистрированные в Украине и разрешенные к применению М.О.З. Украины.</li>\n<li>Высушивание рук проводится электрополотенцами или полотенцами разового пользования.</li>\n<li>Персонал склада должен хранить верхнюю одежду и обувь отдельно от спецодежды и сменной обуви в шкафу для хранения одежды.</li>\n<li>Перед началом работы и в процессе работы проводить дезинфекцию рук: необходимо 3 мл препарата «Бонадерм» втирать в сухую кожу рук : в ладони, пальцы и, между пальцами, в кожу на тыльной стороне руки, в ногтевое ложе на протяжении тридцати секунд. На протяжении всего времени обработки, руки должны быть увлажнены средством «Бонадерм».</li>\n<li>Перед посещением туалета снять спецодежду, а после посещения тщательно вымыть и продезинфицировать руки.</li>\n<li>Запрещается выходить за пределы склада в спецодежде и в сменной обуви.</li>\n<li>Спецодежда выдается работникам из расчета: два комплекта на два года работы. Смена комплекта должна проводиться не реже два раза в неделю, а при необходимости чаще.</li>\n<li>Персонал склада, устраиваясь на работу, проходит обязательное медицинское обследование, а в дальнейшем периодический медицинский осмотр (1раз в год). Результаты обследования заносятся в личную медицинскую книжку, которая дает право на допуск к работе.</li>\n<li>Лица, у которых выявлены инфекционные болезни, направляются на лечение. Допуск этих лиц к работе проводиться только при наличии справки лечебно-профилактического учреждения о выздоровлении.</li>\n</ol></li>\n</ol>	\N	<p>Заведующий ТЛС ежегодно следит за прохождение медицинского осмотра, наличие санитарной книжки.</p>\n<p>Заведующий ТЛС следит за соблюдением правил личной гигиены сотрудников склада, наличие всех необходимых ресурсов, для выполнения настоящей СОП.</p>	<p>Руководство 42-01-2002 “Лекарственные средства. Надлежащая дистрибъюторская практика”.</p>\n<p>Приказ М.О.З.Украины № 275 от 15.05.2006.года.</p>\n<p>СОП - Уборка помещений ТЛС (SOP-P-02-01)</p>\n<p>«Санитарная книжка»</p>	SOP-222388	Route	UPN:DMS:Regulation:SOP:8040935	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1454007592	1454007592	1118804000	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	224075261
682182351	1937134718	1293144792	246037407	{463803818}	{1982566019,1138179996}	Проведение самоинспекций	1	/original/urn-Document-Regulations-SOP-682182351/google-beta-3078796.png	{/original/urn-Document-Regulations-SOP-682182351/biocon-4313473.png}	\N	2016-01-05	\N	<p>Описать порядок проведения самоинспекции на предприятии для контроля соответствия принципам и выполнения правил надлежащей дистрибьюторской практики, проверки эффективности функционирования системы  обеспечения качества, а также предложения необходимых предупреждающих и корректирующих действий.</p>	<p>При проведении самоинспекции на предприятии сотрудниками предприятия.</p>	<ol><li>Ответственность за выполнение процедуры настоящей СОП несет руководитель объекта проверки обеспечения качества (ДОК).</li>\n<li>Ответственность за контроль выполнения требований настоящей СОП несет директор ТЛС.</li>\n<li>Руководитель ДОК имеет право привлекать к проведению самоинспекции требуемых специалистов из числа работников предприятия.</li>\n<li>Руководитель ДОК и члены группы самоинспекции / аудита имеют право:</li>\n</ol><ul><li>находиться на объекте проведения самоинспекции или аудита;</li>\n<li>получать всю необходимую для проведения самоинспекции или аудита документацию и информацию.</li>\n</ul>	<ol><li>Нормативно-аналитическая, регламентирующая и протоколирующая документация предприятия.\n<ol><li>Производственные помещения, оснащенные необходимым оборудованием и средствами измерительной техники.</li>\n</ol></li>\n<li>Информация о производителях, дистрибьюторах, поставщиках и организациях, выполняющих работу по контракту.</li>\n<li>Оргтехника.</li>\n</ol>	<ol><li>Самоинспекция проводится по инициативе предприятия для внутреннего контроля соответствия принципам и выполнения правил надлежащей дистрибьюторской практики, для регулярной оценки эффективности функционирования системы обеспечения качества предприятия, а также разработки необходимых предупреждающих и корректирующих действий.\n<ol><li>На предприятии предусмотрены и проводятся следующие виды самоинспекции:</li>\n<li>регулярная самоинспекция – проводится с целью поддержания системы обеспечения качества и выполняется руководителем ДОК при проверке протоколирующей документации и действий персонала в соответствии с требованиями нормативной регламентирующей документацией предприятия (например, при проведении технологического процесса либо после завершения отпуска продукции);\n<ol><li>периодическая самоинспекция – выполняется группой самоинспекции, которая состоит из сотрудников предприятия, или независимым инспектором по контракту, и бывает:</li>\n<li>плановая самоинспекция — запланировано проводится в течении года;</li>\n<li>внеплановая самоинспекция – проводится </li>\n</ol></li>\n</ol></li>\n<li>перед инспектированием Государственным уполномоченным органом;</li>\n<li></li>\n<li>при получении рекламаций или отзыве готового лекарственного средства (далее ГЛС) с рынка;</li>\n<li>при выявлении несоответствий в ходе контроля качества;</li>\n<li>перед инспектированием уполномоченным лицом клиента (далее – внешний аудит)</li>\n<li>контрольная самоинспекция – проводится для проверки устранения несоответствий, выявленных плановой и внеплановой самоинспекции.</li>\n<li>Объектами самоинспекции являются все аспекты системы обеспечения качества, включая вопросы, касающиеся помещений, оборудования, документации, технологического процесса, контроля качества, персонала, самоинспектирования, работы с рекламациями и отзывами ГЛС.</li>\n<li>Группа самоинспекции состоит из руководителя и членов группы. Руководителем группы самоинспекции является руководитель ДОК.</li>\n<li>Самоинспекция проводиться «вертикальным» методом, т.е. каждое структурное подразделение проверяется отдельно, не реже чем 1 раз в год по всем аспектам системы обеспечения качества.</li>\n<li>Метод проведения самоинспекции отображается в<em> Графике проведения самоинспекции предприятия</em> (F-S0Р-G-03-01) (Приложение №1) на текущий год и утверждается у директора предприятия.</li>\n<li>шаблон первого раздела листа проверки для каждого из типов самоинспекций составляется вместе с графиком самоинспекций предприятия и утвеждается у директора предприятия. В случае внесения изменений шаблон утверждается заново с обязательным указанием № версии и даты документа.</li>\n<li>Государственный уполномоченный орган или другая организация осуществляют внешнюю инспекцию с целью независимого подтверждения эффективности системы обеспечения качества и контроля соответствия принципам и выполнения правил надлежащей дистрибьюторской практики.</li>\n<li>Порядок проведения самоинспекции:</li>\n<li>При подготовке к самоинспекции руководитель ДОК выполняем следующее:</li>\n<li>в начале календарного года составляет <em>График проведения самоинспекции предприятия</em> (F-S0Р-G-03-01);</li>\n<li>согласовывает<em> График проведения самоинспекции предприятия</em> (F-S0Р-G-03-01) с заведующим складом и всеми руководителями инспектируемых подразделений;</li>\n<li>непосредственно перед проведением самоинспекций в соответствии с<em> Графиком проведения самоинспекции предприятия</em> (F-S0Р-G-03-01) готовит приказ о назначении самоинспекции, с указанием инспектируемого объекта проверки, сроков проведения, состава группы самоинспекции, подписывает данный приказ у директора;</li>\n<li>ознакамливает с распоряжением членов группы самоинспекции;</li>\n<li>в Протоколе самоинспекцни (F-S0Р-G-03-02) (Приложение №2) оформляет:</li>\n<li>основной перечень объектов проверки (название склада, № секции)</li>\n<li>лист проверки (F-S0Р-G-03-03) (Приложение №3):</li>\n<li>первый раздел которого включает требования «Надлежащей дистрибьюторской практики» и Лицензионных условий, требования по охране труда и пожарной безопасности.</li>\n<li>второй раздел включает всю имеющуюся Информацию о несоответствиях, выявленных в предыдущих двух самоинспециях;</li>\n<li>распределяет обязанности между членами группы самоинспекции и определяет каждому задачу на предстоящую самоинспекцию;</li>\n</ol>	\N	<p><strong>ПРОТОКОЛ САМОИНСПЕКЦИИ № _______ (автомат)</strong></p>	\N	\N	CreateDraft	UPN:DMS:Regulation:SOP:6173329	\N	{}	\N	urn:Actor:User:System:1	\N	t	f	f	f	\N	\N	1454011807	1454011807	1816416714	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N
288412693	1937134718	\N	464841811	{1293144792}	{2065101785}	dfvfdv	1	\N	\N	\N	\N	\N	<p>cv </p>	\N	\N	\N	\N	\N	\N	\N	SOP-712201	Route	UPN:DMS:Regulation:SOP:9861652	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1454056145	1454056145	1118804000	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	585921068
63204935	\N	\N	\N	\N	\N	test	1	\N	\N	\N	\N	\N	<p>111</p>	\N	\N	\N	\N	\N	\N	\N	SOP-605572	Route	UPN:DMS:Regulation:SOP:7777360	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1454055045	1454055045	1118804000	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	1459100927
368434041	1937134718	614895960	2059004404	{2064133034}	{1816416714,1118804000,1433279225}	sop1	1	\N	\N	\N	2016-01-29	\N	<p>Цель</p>	\N	\N	\N	<p>текст</p>	\N	\N	\N	SOP-974236	Route	UPN:DMS:Regulation:SOP:1326513	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1454063491	1454063491	1118804000	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	2057891801
\.


--
-- Data for Name: Document_Regulations_TA; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Regulations_TA" (id, trainer, "CalendarPeriodMonth", attachments, moreinfo, questions, number, questiondescription, questiondescrip, percentage, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "time", percent, statementoftopics, basevisants, additionalvisants, "DocumentRegulationsSOP", "DMSDocumentUniversal", "interval") FROM stdin;
2012273834	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N	\N	\N	\N	\N	\N
603827105	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-603827105	draft	UPN:DMS:Regulation:Attestation:2296616	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1453131613	1453131613	\N	\N	\N	{urn:Management:Post:Individual:275450805,urn:Management:Post:Individual:1724728515}	\N	\N	\N	\N
36857616	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	CreateDraft	UPN:DMS:Regulation:Attestation:9235610	\N	{}	\N	urn:Actor:User:System:1	\N	t	f	f	f	\N	\N	1454006384	1454006384	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N	\N
690094355	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	CreateDraft	UPN:DMS:Regulation:Attestation:8079873	\N	{}	\N	urn:Actor:User:System:1	\N	t	f	f	f	\N	\N	1454008938	1454008938	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N	\N
692120587	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	CreateDraft	UPN:DMS:Regulation:Study:2192044	\N	{}	\N	urn:Actor:User:System:1	\N	t	f	f	f	\N	\N	1454063083	1454063083	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N	\N
145005863	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-587228	CallA	UPN:DMS:Regulation:Study:4949398	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456071442	1456071442	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	777043453	\N
803273128	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	CreateDraft	UPN:DMS:Regulation:Study:4559099	\N	{}	\N	urn:Actor:User:System:1	\N	t	f	f	f	\N	\N	1454063995	1454063995	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	\N	\N
295490781	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-566569	CallA	UPN:DMS:Regulation:Study:3310955	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456078237	1456078237	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	676763484	\N
913943061	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-491923	CallA	UPN:DMS:Regulation:Study:7057428	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456070412	1456070412	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1018285811	\N
592464277	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	CreateDraft	UPN:DMS:Regulation:Study:5600387	\N	{}	\N	urn:Actor:User:System:1	\N	t	f	f	f	\N	\N	1456070100	1456070100	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	\N	\N
506573119	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-964181	CallA	UPN:DMS:Regulation:Study:6483786	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456072868	1456072868	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	386756913	\N
558317407	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-537654	CallA	UPN:DMS:Regulation:Study:5338061	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456071107	1456071107	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	535634171	\N
765372689	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-189775	CallA	UPN:DMS:Regulation:Study:2815922	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456072528	1456072528	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	748150140	\N
165627481	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-226387	CallA	UPN:DMS:Regulation:Study:9920499	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456072401	1456072401	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1720896069	\N
31657088	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-344297	CallA	UPN:DMS:Regulation:Study:1357837	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456073079	1456073079	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	434693892	\N
982522859	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-587481	CallA	UPN:DMS:Regulation:Study:8862716	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456070286	1456070286	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	2139688421	\N
698264183	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-595450	CallA	UPN:DMS:Regulation:Study:7290939	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456070735	1456070735	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1933839071	\N
518107397	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-143856	CallA	UPN:DMS:Regulation:Study:5676372	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456071982	1456071982	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1859016462	\N
512429204	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-501944	CallA	UPN:DMS:Regulation:Study:5782276	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456076471	1456076471	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1577757792	\N
976383207	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-837020	CallA	UPN:DMS:Regulation:Study:3306009	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456090670	1456090670	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1254430850	\N
430685602	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-349440	CallA	UPN:DMS:Regulation:Study:2794627	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456078846	1456078846	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	807254728	\N
425286874	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-229182	CallA	UPN:DMS:Regulation:Study:8519308	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456079003	1456079003	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	700039796	\N
147476811	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-862268	CallA	UPN:DMS:Regulation:Study:6048701	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456079299	1456079299	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	655444866	\N
124055753	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-923317	CallA	UPN:DMS:Regulation:Study:9491269	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456093972	1456093972	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	2085397922	\N
890904627	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-967914	CallA	UPN:DMS:Regulation:Study:5404892	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456093629	1456093629	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1389480408	\N
350368947	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-979682	CallA	UPN:DMS:Regulation:Study:6537591	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456094092	1456094092	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	728188852	\N
881154641	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-998770	CallA	UPN:DMS:Regulation:Study:7507292	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456094351	1456094351	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1001166292	\N
401671527	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-780351	CallA	UPN:DMS:Regulation:Study:1059948	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456261219	1456261219	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1291758763	\N
197483722	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-210479	CallA	UPN:DMS:Regulation:Study:3614531	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456094415	1456094415	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1455065229	\N
253890094	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-505949	CallA	UPN:DMS:Regulation:Study:6528507	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456260845	1456260845	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	567479439	\N
491312285	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-358706	CallA	UPN:DMS:Regulation:Study:1360062	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456095084	1456095084	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	780023808	\N
565419112	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-104341	CallA	UPN:DMS:Regulation:Study:6583554	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456095272	1456095272	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	990163629	\N
952305702	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-349438	CallA	UPN:DMS:Regulation:Study:9292485	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456094929	1456094929	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	50645530	\N
478411735	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-965074	CallA	UPN:DMS:Regulation:Study:3155745	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456256352	1456256352	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	723429436	\N
194861938	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-148538	CallA	UPN:DMS:Regulation:Study:3399678	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456094592	1456094592	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	14769968	\N
853758258	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-485207	CallA	UPN:DMS:Regulation:Study:3728183	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456255868	1456255868	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1702106740	\N
742472913	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-160507	CallA	UPN:DMS:Regulation:Study:8192951	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456256425	1456256425	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1989617776	\N
737679685	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-597501	CallA	UPN:DMS:Regulation:Study:3861712	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456256250	1456256250	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	391859908	\N
544075720	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-265550	CallA	UPN:DMS:Regulation:Study:7553549	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456095204	1456095204	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	120250055	\N
722796047	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-949748	CallA	UPN:DMS:Regulation:Study:7998217	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456095014	1456095014	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1409359721	\N
27916795	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-564712	CallA	UPN:DMS:Regulation:Study:9866096	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456094829	1456094829	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1022136551	\N
629790415	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-572022	CallA	UPN:DMS:Regulation:Study:1206790	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456095375	1456095375	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	102185727	\N
133004647	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-566212	CallA	UPN:DMS:Regulation:Study:8550638	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456256171	1456256171	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1961238875	\N
838431897	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-612493	CallA	UPN:DMS:Regulation:Study:7618291	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456256618	1456256618	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1957954598	\N
794498385	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-522626	CallA	UPN:DMS:Regulation:Study:8550110	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456257378	1456257378	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	309469429	\N
908106186	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-479535	CallA	UPN:DMS:Regulation:Study:9708204	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456256028	1456256028	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1369807540	\N
480995059	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-721915	CallA	UPN:DMS:Regulation:Study:2618800	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456317552	1456317552	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1856590321	\N
390308020	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-105999	CallA	UPN:DMS:Regulation:Study:3569666	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456260758	1456260758	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1265602085	\N
91504516	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-592786	CallA	UPN:DMS:Regulation:Study:1195403	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456263338	1456263338	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	92260371	\N
506607724	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-635875	CallA	UPN:DMS:Regulation:Study:187306	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456317968	1456317968	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1343660960	\N
878451450	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-837322	CallA	UPN:DMS:Regulation:Study:8744270	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456317637	1456317637	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	342446855	\N
386639122	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-854028	CallA	UPN:DMS:Regulation:Study:3250344	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456322164	1456322164	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	2107528956	\N
535702805	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-692759	CallA	UPN:DMS:Regulation:Study:2884949	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456322707	1456322707	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1393471727	\N
30559454	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-900043	CallA	UPN:DMS:Regulation:Study:7398917	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456337379	1456337379	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1596086654	\N
785085659	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-739074	CallA	UPN:DMS:Regulation:Study:6051297	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456351859	1456351859	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1458378858	\N
886606170	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-949690	CallA	UPN:DMS:Regulation:Study:8270101	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456338020	1456338020	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	455955238	\N
643712667	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-593702	CallA	UPN:DMS:Regulation:Study:3920356	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456340315	1456340315	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	45320295	\N
41800782	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-263498	CallA	UPN:DMS:Regulation:Study:4051957	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456337763	1456337763	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	275547125	\N
35518169	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-428365	CallA	UPN:DMS:Regulation:Study:4024804	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456337433	1456337433	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	974035975	\N
32887833	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-760273	Vising	UPN:DMS:Regulation:Study:1545913	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	\N	1456351295	1456351295	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1292225624	\N
861766654	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-635823	CallA	UPN:DMS:Regulation:Study:3455084	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456353146	1456353146	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	14371471	\N
954539874	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-618260	CallA	UPN:DMS:Regulation:Study:5406115	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456351966	1456351966	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1601923286	\N
928056847	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-193555	CallA	UPN:DMS:Regulation:Study:6882872	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456338313	1456338313	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	725756722	\N
592761478	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-496722	CallA	UPN:DMS:Regulation:Study:1922887	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456337828	1456337828	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1274349471	\N
749046165	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-629729	CallA	UPN:DMS:Regulation:Study:3968682	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456337631	1456337631	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1587555015	\N
539824452	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-882461	CallA	UPN:DMS:Regulation:Study:9325112	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456353049	1456353049	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	2016153255	\N
1285953	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-844330	CallA	UPN:DMS:Regulation:Study:7600731	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456352940	1456352940	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	414355987	\N
307667893	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-881287	CallA	UPN:DMS:Regulation:Study:9872429	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1456340800	1456340800	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	59360761	\N
674230955	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-691357	CallA	UPN:DMS:Regulation:Study:2942210	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456352673	1456352673	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1235834393	\N
426457429	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-379239	CallA	UPN:DMS:Regulation:Study:8854428	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456351767	1456351767	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	387806400	\N
631913055	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-617730	Vising	UPN:DMS:Regulation:Study:4644654	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	\N	1456351528	1456351528	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1479205194	\N
366490496	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-898755	CallA	UPN:DMS:Regulation:Study:961000	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456352850	1456352850	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	364307519	\N
748421803	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-761049	CallA	UPN:DMS:Regulation:Study:7641159	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456354988	1456354988	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	2018526526	\N
959754415	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-306647	CallA	UPN:DMS:Regulation:Study:7960369	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456354763	1456354763	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	898634597	\N
156450147	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-867694	CallA	UPN:DMS:Regulation:Study:4328543	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456354669	1456354669	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	353451429	\N
844627862	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-431127	CallA	UPN:DMS:Regulation:Study:8363492	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456353401	1456353401	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1805271576	\N
74206247	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-195444	CallA	UPN:DMS:Regulation:Study:4470338	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456354268	1456354268	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1824718284	\N
122019848	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-462985	CallA	UPN:DMS:Regulation:Study:9580706	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456355281	1456355281	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	2131620157	\N
189910091	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-913858	CallA	UPN:DMS:Regulation:Study:3037013	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456356323	1456356323	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	779349966	\N
238139236	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-929980	CallA	UPN:DMS:Regulation:Study:2474658	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456356941	1456356941	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1589986624	\N
540995730	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-906404	CallA	UPN:DMS:Regulation:Study:1175098	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456356369	1456356369	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	897871856	\N
737545915	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-303452	CallA	UPN:DMS:Regulation:Study:9428893	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456357524	1456357524	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1388462074	\N
556347460	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-330392	CallA	UPN:DMS:Regulation:Study:8799549	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456357465	1456357465	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	406234384	\N
665448315	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-887093	CallA	UPN:DMS:Regulation:Study:112593	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456356487	1456356487	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1266237959	\N
251751254	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-702316	CallA	UPN:DMS:Regulation:Study:3205637	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456357601	1456357601	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	2091737039	\N
889615447	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-783212	CallA	UPN:DMS:Regulation:Study:8961614	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456357298	1456357298	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	2106945021	\N
163838185	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-267789	CallA	UPN:DMS:Regulation:Study:2460337	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456356779	1456356779	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	486163910	\N
578254565	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-787819	CallA	UPN:DMS:Regulation:Study:5248068	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456356404	1456356404	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	116804035	\N
896367215	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-921568	CallA	UPN:DMS:Regulation:Study:1615406	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456357829	1456357829	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	114342827	\N
796584242	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-499039	CallA	UPN:DMS:Regulation:Study:111719	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456357152	1456357152	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1496128100	\N
396454592	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-897893	CallA	UPN:DMS:Regulation:Study:8600947	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456357076	1456357076	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1082742896	\N
598202109	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-599183	CallA	UPN:DMS:Regulation:Study:5436889	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456356688	1456356688	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1708943409	\N
505142207	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-932462	CallA	UPN:DMS:Regulation:Study:5641529	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456358139	1456358139	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	2106771594	\N
544429800	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-729616	CallA	UPN:DMS:Regulation:Study:3839337	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456357991	1456357991	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	127049856	\N
547805312	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-300581	CallA	UPN:DMS:Regulation:Study:4291705	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456358439	1456358439	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	646659771	\N
8088822	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-758790	CallA	UPN:DMS:Regulation:Study:2147219	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456358084	1456358084	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1542735951	\N
805624862	\N	\N	\N	\N	\N	\N	\N	\N	\N	TA-523246	CallA	UPN:DMS:Regulation:Study:1360794	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1456358222	1456358222	\N	\N	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	368434041	1113614114	\N
\.


--
-- Data for Name: Document_Risk_Approved; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Risk_Approved" (id, critical, "BusinessObjectRecordPolymorph", "DirectoryBusinessProcessItem", "DirectorySLAItem", "ManagementPostIndividual", controlperiod, riskapproved, producteffect, emergenceprobability, undetectedprobability, weighted, controlact, "DMSDocumentUniversal", privatedraft, state, code, process, parent, children, related, initiator, authors, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants) FROM stdin;
581969696	1	1293144792	\N	\N	\N	\N	Беспокойство	\N	\N	\N	\N	вамавм	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
1789503934	1	643421900	\N	\N	\N	\N	Разное	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Risk_NotApproved; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Risk_NotApproved" (id, identified, "DocumentRiskApproved", bo, process, risknotapproved, documentoforigin, bprocess, "DMSDocumentUniversal", privatedraft, state, code, parent, children, related, initiator, authors, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants) FROM stdin;
1108580966	0	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
2058450194	0	\N	1293144792	1887907087	Новый риск	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
955698736	0	\N	966042166	1355639494	Риск  новый 1	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
1302515356	0	\N	966042166	1355639494	новый риск	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
904152752	0	\N	\N	urn:Directory:BusinessProcess:Item:1355639494	Н/И риск 1	urn:Document:Detective:C_IS:925512033	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453930169	\N	\N	\N
1798910171	0	\N	463803818	\N	н/и риск 2	urn:Document:Detective:C_IS:925512033	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453930169	\N	\N	\N
1090377305	0	\N	\N	urn:Directory:BusinessProcess:Item:1937134718	н/и риск 3	urn:Document:Detective:C_IS:925512033	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453930497	\N	\N	\N
630031236	0	\N	10341110	\N	бпрмплгн	urn:Document:Detective:C_IS:120327729	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453932886	\N	\N	\N
827200296	0	\N	1293144792	\N	пмборм	urn:Document:Detective:C_IS:120327729	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453932934	\N	\N	\N
1600992950	0	\N	643421900	\N	н/и реск 4	urn:Document:Capa:Deviation:276608231	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453973909	\N	\N	\N
917043806	0	\N	463803818	\N	н/и риск 1	urn:Document:Capa:Deviation:123943761	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453975365	\N	\N	\N
1264898759	0	\N	\N	\N	н/и риск 2	urn:Document:Capa:Deviation:123943761	1937134718	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453975365	\N	\N	\N
333462794	0	\N	206674127	\N	НИ Риск 1	urn:Document:Capa:Deviation:297872527	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453976863	\N	\N	\N
\.


--
-- Data for Name: Document_Solution_Correction; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Solution_Correction" (id, "DocumentCorrectionCapa", realizationdate, cost, created, updated, executor, realizationtype, descriptionsolution, "visauser_ManagementPostIndividual", code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, basevisants, additionalvisants, approveded, "DMSDocumentUniversal", ready, approved, ordered, matches, comment) FROM stdin;
1006213670	1621993936	2016-01-31	5000.00	1453977300	\N	1118804000	2	.длаьвд.	{1816416714}	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1	\N	1	\N	\N	\N	\N
1193884491	970881957	2016-01-28	1000.00	1453977344	\N	1118804000	1	яювлдаьмдыальмФЫАМВЯАИЧИП	{1118804000,1816416714}	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1	\N	1	\N	\N	\N	\N
1551139179	1805612676	2016-01-30	3456.00	1453975561	\N	1118804000	2	ОПИСАНИЕ РЕШЕНИЯ 2	{1816416714}	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0	\N	0	\N	\N	\N	\N
225621217	1805612676	2016-01-29	7000.00	1453975561	\N	1816416714	1	ОПИСАНИЕ РЕШЕНИЯ 1	{1118804000}	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1	\N	0	\N	\N	\N	\N
1810854105	299613188	2016-12-11	20.00	1453975602	\N	1118804000	4	ОПИСАНИЕ РЕШЕНИЯ 11	{1118804000,1816416714}	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1	\N	0	\N	\N	\N	\N
979529825	1621993936	2016-01-31	500.00	1453977300	\N	1118804000	1	флорсаглдфюлориторлю	{1118804000}	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0	\N	0	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Solution_Universal; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Solution_Universal" (id, executor, realizationtype, realizationdate, cost, description, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "visedby_ManagementPostIndividual", basevisants, additionalvisants, "DMSDocumentUniversal", ordered) FROM stdin;
1198691050	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217230	\N	\N	\N	\N	\N	\N
699090876	1118804000	2	2016-01-28	98765432.00	Вы не должны наказывать людей за то, что они рискуют или ошибаются. Вы хотите, чтобы они не боялись экспериментировать, ошибаться и извлекать уроки из этого. По крайней мере, пока они не разрушают ваш бизнес. Если они хотят экспериментировать на таком уровне, то пусть делают это со своим бизнесом.	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453205069	\N	\N	\N	\N	\N	\N
1744527723	1897084668	4	2016-01-20	876543356.00	Вы не должны наказывать людей за то, что они рискуют или ошибаются. Вы хотите, чтобы они не боялись экспериментировать, ошибаться и извлекать уроки из этого. По крайней мере, пока они не разрушают ваш бизнес. Если они хотят экспериментировать на таком уровне, то пусть делают это со своим бизнесом.	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453205069	\N	\N	\N	\N	\N	\N
1798786892	1118804000	4	2016-01-29	876543.00	Вы не должны наказывать людей за то, что они рискуют или ошибаются. Вы хотите, чтобы они не боялись экспериментировать, ошибаться и извлекать уроки из этого. По крайней мере, пока они не разрушают ваш бизнес. Если они хотят экспериментировать на таком уровне, то пусть делают это со своим бизнесом.	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453205847	\N	{1118804000}	\N	\N	\N	\N
1516320581	1118804000	4	2016-01-29	8765.00	 Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453209337	\N	{1118804000}	\N	\N	\N	\N
1621009817	921220994	3	2016-01-21	87654.00	 Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect Inspect	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453209337	\N	{1816416714}	\N	\N	\N	\N
771180076	1939617403	3	2016-01-22	87654.00	test	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453205847	\N	{1816416714}	\N	\N	\N	\N
1306100958	1982566019	3	2016-01-31	6543.00	Вложение отображать - минимум 2	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453724656	\N	\N	\N	\N	\N	\N
1327072402	1118804000	4	2016-01-28	2345678.00	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453736011	\N	{1118804000,1816416714}	\N	\N	\N	\N
1847460396	1118804000	3	2016-01-28	234567.00	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453734183	\N	{1118804000,1816416714}	\N	\N	\N	\N
1399815909	1118804000	4	2016-01-29	65.00	Вложение отображать - минимум 1	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453724656	\N	{1118804000,1118804000,1816416714}	\N	\N	\N	\N
648263024	1982566019	3	2016-01-28	876545678.00	test 2	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453732613	\N	\N	\N	\N	\N	\N
321956214	1118804000	4	2016-01-28	843.00	test 1	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453732613	\N	{1118804000,1816416714}	\N	\N	\N	\N
303185365	1897084668	4	2016-01-29	876543.00	PAI 3PAI 3	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453733229	\N	{1118804000}	\N	\N	\N	\N
1082747180	1118804000	2	2016-01-29	32345678.00	PAI 1	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453733229	\N	{1816416714}	\N	\N	\N	\N
1986557686	1724728515	4	2016-01-30	87654.00	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453734183	\N	{1816416714}	\N	\N	\N	\N
1054167158	1404952896	3	2016-01-28	7654345678.00	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	Universal-558057	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453735696	\N	\N	\N	\N	634799756	\N
561530900	1118804000	4	2016-01-29	23456.00	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	Universal-184771	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453735696	\N	{1118804000,1816416714}	\N	\N	367022182	\N
446591594	921220994	2	2016-01-27	765456.00	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453736011	\N	\N	\N	\N	\N	\N
1688212046	1118804000	4	2016-01-31	8765432.00	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	Universal-979072	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453737484	\N	\N	\N	\N	499366591	\N
496568797	1138179996	3	2016-01-29	34.00	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453736421	\N	\N	\N	\N	\N	\N
786579161	1962987279	2	2016-01-30	456789.00	Considering R_LST-640439056Considering R_LST-640439056Considering R_LST-640439056Considering R_LST-640439056	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453738292	\N	\N	\N	\N	\N	\N
1387578668	1118804000	4	2016-02-24	345.00	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	Universal-623863	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453736389	\N	{1118804000,1816416714}	\N	\N	1771259874	\N
1745142289	1118804000	4	2016-01-28	654.00	http://local.bc/process/act/361497413http://local.bc/process/act/361497413http://local.bc/process/act/361497413http://local.bc/process/act/361497413http://local.bc/process/act/361497413	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453737199	\N	\N	\N	\N	\N	\N
1284686991	1962987279	3	2016-01-27	234.00	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453737199	\N	\N	\N	\N	\N	\N
1167875494	1118804000	4	2016-01-28	3456.00	okokokokokokokokokokokok	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453741265	\N	{1118804000,1816416714}	\N	\N	\N	\N
1359860492	1118804000	4	2016-01-29	214.00	Considering R_LST-640439056Considering R_LST-640439056Considering R_LST-640439056Considering R_LST-640439056	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453738292	\N	{1118804000,1816416714}	\N	\N	\N	\N
660511853	1962987279	3	2016-01-20	876543.00	ДЕТАЛЬНЫЙ СПИСОК ТОВАРА ДЛЯ ИЗМЕНЕНИЯ СТАТУСА / ПЕРЕМЕЩЕНИЯ В ДРУГУЮ СКЛАДСКУЮ ЗОНУДЕТАЛЬНЫЙ СПИСОК ТОВАРА ДЛЯ ИЗМЕНЕНИЯ СТАТУСА / ПЕРЕМЕЩЕНИЯ В ДРУГУЮ СКЛАДСКУЮ ЗОНУ	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453738907	\N	\N	\N	\N	\N	\N
1655907378	1118804000	4	2016-02-18	98765432.00	ДЕТАЛЬНЫЙ СПИСОК ТОВАРА ДЛЯ ИЗМЕНЕНИЯ СТАТУСА / ПЕРЕМЕЩЕНИЯ В ДРУГУЮ СКЛАДСКУЮ ЗОНУДЕТАЛЬНЫЙ СПИСОК ТОВАРА ДЛЯ ИЗМЕНЕНИЯ СТАТУСА / ПЕРЕМЕЩЕНИЯ В ДРУГУЮ СКЛАДСКУЮ ЗОНУДЕТАЛЬНЫЙ СПИСОК ТОВАРА ДЛЯ ИЗМЕНЕНИЯ СТАТУСА / ПЕРЕМЕЩЕНИЯ В ДРУГУЮ СКЛАДСКУЮ ЗОНУДЕТАЛЬНЫЙ СПИСОК ТОВАРА ДЛЯ ИЗМЕНЕНИЯ СТАТУСА / ПЕРЕМЕЩЕНИЯ В ДРУГУЮ СКЛАДСКУЮ ЗОНУ	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453738907	\N	{1118804000,1816416714}	\N	\N	\N	\N
1959136953	1433279225	2	2016-01-28	24.00	тест	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453741265	\N	\N	\N	\N	\N	\N
1662940975	1118804000	4	2016-01-30	876543.00	okokokokokokokokokokokokokokokokokokokokok	Universal-867659	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453742931	\N	{1816416714}	\N	\N	764621023	\N
964151484	1962987279	3	2016-01-27	123456.00	цуефваим	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453741917	\N	\N	\N	\N	\N	\N
1002074943	1118804000	4	2016-01-29	234567.00	okokokokokokokokok	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453741917	\N	{1118804000,1816416714}	\N	\N	\N	\N
461710851	990258053	2	2016-01-28	6543456.00	okokokokokokokokokokokokokokokokokokokokokokokok	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453742490	\N	\N	\N	\N	\N	\N
2044463173	1118804000	4	2016-01-31	987654.00	okokokokokokokokokokokokokokok	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453742490	\N	{1118804000,1816416714}	\N	\N	\N	\N
1464142677	1349687451	3	2016-01-29	2345.00	okokokokokokokokokokokokokokok	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453742681	\N	\N	\N	\N	\N	\N
284778779	1118804000	4	2016-01-30	987654.00	okokokokokokokokokokokokokokokokokokokokok	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453742681	\N	{1816416714,1118804000}	\N	\N	\N	\N
1388824898	275450805	3	2016-01-28	24.00	Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.Следствие закона Мерфи для мобильных приложений звучит приблизительно так: “Если пользователь может что-то понять не так - он это сделает”.	Universal-197590	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453737484	\N	\N	\N	\N	455103347	\N
448722696	1118804000	4	2016-01-29	987654.00	СПЕЦИАЛЬНОЕ ТРЕБОВАНИЕСПЕЦИАЛЬНОЕ ТРЕБОВАНИЕСПЕЦИАЛЬНОЕ ТРЕБОВАНИЕСПЕЦИАЛЬНОЕ ТРЕБОВАНИЕСПЕЦИАЛЬНОЕ ТРЕБОВАНИЕ	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453740475	\N	{1118804000,1816416714}	\N	\N	\N	\N
1349960593	1816416714	3	2016-01-27	87654.00	okokokokokokokokokokokokokokokokokokokokokokokokokokok	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453743005	\N	{1118804000}	\N	\N	\N	\N
516996784	536231274	2	2016-01-22	1234.00	ОЦЕНОЧНАЯ СТОИМОСТЬОЦЕНОЧНАЯ СТОИМОСТЬОЦЕНОЧНАЯ СТОИМОСТЬ	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453739707	\N	\N	\N	\N	\N	\N
1620678598	1118804000	4	2016-01-29	3456.00	ПАКЕТ ДОКУМЕНТОВ ДЛЯ ОТГРУЗКИПАКЕТ ДОКУМЕНТОВ ДЛЯ ОТГРУЗКИПАКЕТ ДОКУМЕНТОВ ДЛЯ ОТГРУЗКИ	Universal-653644	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453739613	\N	{1118804000,1816416714}	\N	\N	757507906	\N
1473257722	921220994	3	2016-01-28	2345678.00	ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453739974	\N	\N	\N	\N	\N	\N
1093851318	1118804000	3	2016-01-28	2345.00	для всех полей, где указана сумма нельзя вставить число с заптяойдля всех полей, где указана сумма нельзя вставить число с заптяой	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453753987	\N	{1118804000,1816416714}	\N	\N	\N	\N
610915775	1118804000	4	2016-01-31	345678.00	ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453739974	\N	{1118804000,1816416714}	\N	\N	\N	\N
1835122629	921220994	2	2016-01-28	23456.00	ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453740249	\N	\N	\N	\N	\N	\N
217573489	1118804000	4	2016-01-29	2345678.00	ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ ВЛОЖЕНИЕ	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453740249	\N	{1118804000,1816416714}	\N	\N	\N	\N
1358291385	1433279225	1	2016-01-28	987654.00	СПЕЦИАЛЬНОЕ ТРЕБОВАНИЕСПЕЦИАЛЬНОЕ ТРЕБОВАНИЕ	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453740475	\N	\N	\N	\N	\N	\N
718419272	1816416714	4	2016-01-28	6543.00	nononononononononononononononononononononononononononononononononononononononononononononononononononononononononononononononono	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453759012	\N	{1118804000,1816416714}	\N	\N	\N	\N
738220964	1118804000	1	2016-01-29	\N	Considering R_QDE-499283193 - периодчность обучения - Considering R_QDE-499283193 - периодчность обучения - Considering R_QDE-499283193 - периодчность обучения - 	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453758368	\N	{1816416714,1118804000}	\N	\N	\N	\N
1438182459	1118804000	4	2016-01-29	23.00	Связать CapaApp, AttestationApp с Managed Process Execution / Inbox	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453755435	\N	{1118804000,1816416714}	\N	\N	\N	\N
2133186783	1433279225	2	2016-01-28	98765.00	и САРА на этапах Considering и САРА на этапах Considering 	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453757713	\N	\N	\N	\N	\N	\N
1786744849	1118804000	4	2016-01-29	7654.00	и САРА на этапах Considering и САРА на этапах Considering и САРА на этапах Considering и САРА на этапах Considering и САРА на этапах Considering 	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453757713	\N	{1118804000,1816416714}	\N	\N	\N	\N
959441299	1109817505	1	2016-01-27	4.00	test	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453758961	\N	\N	\N	\N	\N	\N
1943402235	1118804000	4	2016-01-27	345.00	nononononononononononononononono	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453758961	\N	{1118804000}	\N	\N	\N	\N
1911361363	1138179996	4	2016-01-23	8765.00	qwkegdfbnmc	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453927765	\N	\N	\N	\N	\N	2
893995240	1118804000	1	2016-01-30	23456.00	qljhgfcvbnm,.	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453927765	\N	{1118804000,1816416714,1816416714,1816416714,1816416714}	\N	\N	\N	1
1050262913	536231274	3	2016-01-31	23456.00	Cyber	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453929101	\N	\N	\N	\N	\N	4
1441243850	1118804000	4	2016-01-30	23456.00	Cyber	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453929101	\N	{1118804000,1816416714}	\N	\N	\N	3
2092790685	697130132	3	2016-01-23	876543.00	jhgfdsxcvbpo09876	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453929659	\N	\N	\N	\N	\N	6
871593845	1118804000	4	2016-01-30	76543.00	hgfresxcvbnm,l	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453929659	\N	{1118804000,1816416714}	\N	\N	\N	5
2091060584	1118804000	3	2016-01-28	50000.00	фдывгр	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453932290	\N	{1118804000}	\N	\N	\N	7
1577756981	1816416714	4	2016-01-30	70000.00	ыавпф	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1453932290	\N	{1816416714}	\N	\N	\N	8
700481238	1118804000	1	2016-02-25	200.00	ывмамв	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1456398082	\N	{1118804000,1118804000}	\N	\N	\N	9
244777135	1118804000	2	2016-02-25	200.00	dfvd	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1456405352	\N	{1118804000}	\N	\N	\N	10
\.


--
-- Data for Name: Document_Staffdoc_OF; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Staffdoc_OF" (id, "CompanyStructureDepartment", manager, reason, dateofdismissal, severancepay, dateunusedvacation, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, base, "DMSDocumentUniversal", "ManagementPostIndividual") FROM stdin;
293717124	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N	\N	\N
3882240	\N	1118804000	для всех полей, где указана сумма нельзя вставить число с заптяойдля всех полей, где указана сумма нельзя вставить число с заптяойдля всех полей, где указана сумма нельзя вставить число с заптяой	2016-01-27	для всех полей, где указана сумма нельзя вставить число с заптяой	34	\N	Approving	UPN:DMS:Process:Simple:3879086	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1453754136	1453754136	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	для всех полей, где указана сумма нельзя вставить число с заптяой	\N	\N
341149642	\N	1404952896	Considering R_QDE-499283193	2016-01-28	3456	23	OF-341149642	draft	UPN:DMS:Process:Simple:8781334	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1453743562	1453743562	{urn:Management:Post:Individual:1724728515,urn:Management:Post:Individual:1109817505,urn:Management:Post:Individual:990258053,urn:Management:Post:Individual:1601586017,urn:Management:Post:Individual:406611786}	\N	Considering R_QDE-499283193	\N	\N
477888036	\N	\N	\N	\N	\N	\N	\N	CreateDraft	UPN:DMS:Process:Simple:1461578	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1453754814	1453754814	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N	\N
370934935	\N	1724728515	Пьянство на работе	2016-01-01	без	12	OF-933590	CreateDraft	UPN:DMS:Process:Simple:6280921	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	\N	\N	1454015260	1454015260	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	Статья 8365876.7375	1416488568	\N
928421818	\N	2065101785	гнекуываспм	2016-01-31	54323	6	OF-354945	Approving	UPN:DMS:Process:Simple:5921290	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1454005390	1454005390	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	лорепквы	930754165	\N
\.


--
-- Data for Name: Document_Staffdoc_OR; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Staffdoc_OR" (id, "CompanyStructureDepartment", "ManagementPostGroup", manager, employeename, date, dateend, dateterm, actual, long, salary, jobtype, moremoney, evenmoremoney, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal") FROM stdin;
901083446	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N
277344038	1641958276	1492262350	1118804000	Иванов Иван Иванович	2016-01-28	2016-10-26	3 месяца	1	40 часов	5грн	2	\N	\N	OR-613483	Approving	UPN:DMS:Process:Simple:6535218	\N	{}	\N	urn:Actor:User:System:1	\N	f	f	f	f	t	t	1454006775	1454006775	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	398041660
\.


--
-- Data for Name: Document_Staffdoc_SD; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Staffdoc_SD" (id, employee, addressed, masterpart, createdate, date, based, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal") FROM stdin;
1192452069	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Staffdoc_SV; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Staffdoc_SV" (id, employee, addressed, masterpart, createdate, date, datestart, dateend, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal") FROM stdin;
712126598	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N
\.


--
-- Data for Name: Document_TechnicalTask_ForMaterials; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_TechnicalTask_ForMaterials" (id, "CompanyStructureCompanygroup", type, docpermitsneed, supplierauditneeded, deliveryconditions, priority, "desc", requirement, attachments, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "DirectoryBranchItem", basevisants, additionalvisants, "CompanyLegalEntityCounterparty_CompanyLegalEntityCounterparty", personreceive, contactperson, "DirectoryTechnicalTaskMaterials_DirectoryTechnicalTaskMaterials", "DMSDocumentUniversal") FROM stdin;
810648452	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N	\N	\N	\N	\N	\N
352462922	267613092	Вид работ	Условия	1	Условия поставки	ПРИОРИТЕТЫ	\N	333	{/original/urn-Document-TechnicalTask-ForMaterials-352462922/KABINET.png}	null-352462922	draft	UPN:DMS:Tenders:TechTask:7081511	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1453058972	1453058972	1549580271	{urn:Management:Post:Individual:1109817505,urn:Management:Post:Individual:990258053}	\N	{404906315,1156372664}	1982566019	1109817505	{388211603,73398106}	\N
538623162	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	CreateDraft	UPN:DMS:Tenders:TechTask:3955106	\N	{}	\N	urn:Actor:User:System:1	\N	t	f	f	f	\N	\N	1454011512	1454011512	\N	{urn:Management:Post:Individual:1118804000,urn:Management:Post:Individual:1816416714}	\N	\N	\N	\N	\N	\N
\.


--
-- Data for Name: Document_TechnicalTask_ForWorks; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_TechnicalTask_ForWorks" (id, "CompanyStructureCompanygroup", workstype, docpermitsneed, supplauditneeded, contactperson, projdocchangesneeded, projectdocsmusthave, sevicedesription, volume, workinside, priorities, "desc", requirements, sectionsdesc, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, "DirectoryBranchItem", basevisants, additionalvisants, "CompanyLegalEntityCounterparty_CompanyLegalEntityCounterparty", personreceive, projectdocneed, deliveryconditions, "DMSDocumentUniversal", attachments) FROM stdin;
837654432	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1450217231	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
6008209	1642094893	W type	dfvfdvdf	1	1109817505	1	dfvdf	\N	dfvdf	\N	3	\N	dfvd	\N	null-6008209	draft	UPN:DMS:Tenders:TechTask:5175638	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1453059299	1453059299	285787959	{urn:Management:Post:Individual:1109817505,urn:Management:Post:Individual:990258053}	\N	{1156372664}	2065101785	1	2	\N	\N
63070481	1642094893	Вид работ	вамва	1	\N	1	вмавмвам	\N	вамвм	\N	апиапи	\N	вапаи	\N	null-63070481	draft	UPN:DMS:Tenders:TechTask:228015	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1453068039	1453068039	285787959	{urn:Management:Post:Individual:1118804000}	{urn:Management:Post:Individual:1962987279}	{115997801}	536231274	вамвам	апипаи	\N	\N
489972516	1642094893	dfdfvfd	dfvfdv	1	\N	\N	fgbgf	\N	dfvfdv	\N	\N	\N	\N	\N	null-489972516	draft	UPN:DMS:Tenders:TechTask:2666220	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1453153305	1453153305	285787959	{urn:Management:Post:Individual:1118804000}	\N	{404906315}	\N	\N	\N	\N	\N
521898892	1642094893	dffdvdv	\N	\N	\N	\N	\N	\N	dfvfdv	\N	\N	\N	\N	\N	null-521898892	draft	UPN:DMS:Tenders:TechTask:3516327	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1453153554	1453153554	285787959	{urn:Management:Post:Individual:1118804000}	\N	{404906315}	\N	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Tender_Extended; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Tender_Extended" (id, title, attachment, attachments, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal", docpermitsneed, currency) FROM stdin;
403163989	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
565159195	\N	\N	\N	null-565159195	draft	UPN:DMS:Tenders:Tender:8354096	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1453153354	1453153354	{urn:Management:Post:Individual:1109817505,urn:Management:Post:Individual:1724728515,urn:Management:Post:Individual:697130132,urn:Management:Post:Individual:990258053}	\N	\N	\N	\N
464412311	\N	\N	\N	null-464412311	draft	UPN:DMS:Tenders:Tender:5455070	\N	{}	\N	urn:Actor:User:System:0	\N	t	f	f	f	\N	\N	1453204860	1453204860	\N	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Tender_Table; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Tender_Table" (id, currency, titleposition, priceoffer, code, state, process, parent, children, related, initiator, authors, privatedraft, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants, "DMSDocumentUniversal", "DirectoryTenderBidderSimple", priceofferarray) FROM stdin;
520038063	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
136269606	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	t	\N	\N	\N	\N	\N	1453721961	\N	\N	\N	\N	\N	\N
\.


--
-- Data for Name: Document_Tender_TableAdditional; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Tender_TableAdditional" (id, "DMSDocumentUniversal", "DirectoryTenderBidderSimple", titleposition, priceoffer, priceofferarray, privatedraft, state, code, process, parent, children, related, initiator, authors, returned, done, archived, vised, approved, created, updated, basevisants, additionalvisants) FROM stdin;
\.


--
-- Data for Name: Document_Viewaccess_ByProcedure; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Document_Viewaccess_ByProcedure" (id, isactive, isreturn, "holder_PeopleEmployeeInternal", dateissue, datereturn, master) FROM stdin;
\.


--
-- Data for Name: Event_ProcessExecutionPlanned_Staged; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Event_ProcessExecutionPlanned_Staged" (id, isdateset, ismpestarted, planningresponsible, "ManagedProcessExecutionRecord", "participants_ManagementPostIndividual", eventyear, eventmonth, eventdate, subject, processproto, subjectproto, created) FROM stdin;
\.


--
-- Data for Name: Feed_Inbox_Document; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Feed_Inbox_Document" (id, isprocessed, "PeopleEmployeeInternal", urn, created) FROM stdin;
\.


--
-- Data for Name: Feed_MPETicket_InboxItem; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Feed_MPETicket_InboxItem" (id, isvalid, allowopen, allowsave, allowcomplete, allowcomment, allowreadcomments, allowknowcuurentstage, allowseejournal, allowearly, "ManagementPostIndividual", "ManagedProcessExecutionRecord", activateat, expiresat, created) FROM stdin;
328684432	1	0	0	0	0	0	1	0	0	1118804000	5734686	2016-02-25 18:15:43.153439+02	\N	1456416943
284091437	1	0	0	0	0	0	1	0	0	1118804000	2084582	2016-02-25 15:33:22.938654+02	\N	1456407203
1797908410	1	1	0	0	0	0	1	0	0	1816416714	831524	2016-02-25 18:21:47+02	\N	1456417307
1207930150	1	0	0	0	0	0	1	0	0	1118804000	831524	2016-02-25 18:21:47+02	\N	1456417307
352284592	1	1	1	1	1	1	1	1	1	1118804000	150798	2016-02-25 15:33:54.620874+02	\N	1456407235
240455513	1	0	0	0	0	0	1	0	0	1118804000	9995676	2016-02-25 21:47:59.781711+02	\N	1456429680
502107053	1	1	1	1	1	1	1	1	1	1118804000	9659306	2016-02-25 21:49:31.835786+02	\N	1456429772
494462506	1	0	0	0	0	0	1	0	0	1118804000	7508668	2016-02-25 16:19:38.879828+02	\N	1456409979
886152030	1	0	0	0	0	0	1	0	0	1118804000	7006567	2016-02-25 16:20:44.494076+02	\N	1456410044
2012907682	1	1	0	0	0	0	1	0	0	1118804000	6511821	2016-02-25 17:22:13+02	\N	1456413733
534243314	1	1	0	0	0	0	1	0	0	1816416714	6511821	2016-02-25 17:22:13+02	\N	1456413733
506861616	1	0	0	0	0	0	1	0	0	1118804000	1626004	2016-02-25 18:14:52.48021+02	\N	1456416892
\.


--
-- Data for Name: HTTP_Redirect_FromURIToURI; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "HTTP_Redirect_FromURIToURI" (id, uri, target) FROM stdin;
\.


--
-- Data for Name: Mail_Template_HTML; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Mail_Template_HTML" (id, translated_ru, translated_en, title, title_en, fromname, fromname_en, headerhtml, headerhtml_en, contenthtml, contenthtml_en, footerhtml, footerhtml_en, specialhtml, specialhtml_en, uri, fromemail) FROM stdin;
\.


--
-- Data for Name: Mail_Template_Plain; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Mail_Template_Plain" (id, translated_ru, translated_en, title, title_en, fromname, fromname_en, headerplain, headerplain_en, contentplain, contentplain_en, footerplain, footerplain_en, specialplain, specialplain_en, uri, layout, fromemail) FROM stdin;
\.


--
-- Data for Name: ManagedProcess_Execution_Record; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "ManagedProcess_Execution_Record" (id, initiator, prototype, returntopme, subject, metadata, done, nextstage, nextactor, currentstage, currentactor, created) FROM stdin;
7508668	urn:Management:Post:Individual:1118804000	DMS:Complaints:Complaint	\N	urn:Document:Complaint:C_IS:76828139	{"subjectPrototype":"Document:Complaint:C_IS","child":"UPN:DMS:Complaints:Detective:7006567"}	f	\N	\N	CallCP	\N	2016-02-25 16:19:38.205204
2084582	urn:Management:Post:Individual:1118804000	DMS:Complaints:Complaint	\N	urn:Document:Complaint:C_LT:24913852	{"subjectPrototype":"Document:Complaint:C_LT","child":"UPN:DMS:Complaints:Detective:150798"}	f	\N	\N	CallCP	\N	2016-02-25 15:33:22.322866
5734686	urn:Management:Post:Individual:1118804000	DMS:Complaints:Detective	UPN:DMS:Complaints:Complaint:1626004	urn:Document:Detective:C_LC:583758799	{"initiatorofparent":"urn:Management:Post:Individual:1118804000","parent":"UPN:DMS:Complaints:Complaint:1626004","parentSubject":"urn:Document:Complaint:C_LC:88687448","child":"UPN:DMS:Decisions:Visa:831524"}	f	\N	\N	Vising	\N	2016-02-25 18:15:42.142216
9995676	urn:Management:Post:Individual:1118804000	DMS:Complaints:Complaint	\N	urn:Document:Complaint:C_LT:917825520	{"subjectPrototype":"Document:Complaint:C_LT","child":"UPN:DMS:Complaints:Detective:9659306"}	f	\N	\N	CallCP	\N	2016-02-25 21:47:58.242467
831524	urn:Management:Post:Individual:1118804000	DMS:Decisions:Visa	UPN:DMS:Complaints:Detective:5734686	urn:Document:Detective:C_LC:583758799	{"initiatorofparent":"urn:Management:Post:Individual:1118804000","parent":"UPN:DMS:Complaints:Detective:5734686"}	f	\N	\N	Decision	urn:Actor:AI:System:1	2016-02-25 18:21:46.708831
150798	urn:Management:Post:Individual:1118804000	DMS:Complaints:Detective	UPN:DMS:Complaints:Complaint:2084582	urn:Document:Detective:C_LT:839948176	{"initiatorofparent":"urn:Management:Post:Individual:1118804000","parent":"UPN:DMS:Complaints:Complaint:2084582","parentSubject":"urn:Document:Complaint:C_LT:24913852"}	f	\N	\N	ProtocolExtendRisk	urn:Management:Post:Individual:1118804000	2016-02-25 15:33:53.726545
7006567	urn:Management:Post:Individual:1118804000	DMS:Complaints:Detective	UPN:DMS:Complaints:Complaint:7508668	urn:Document:Detective:C_IS:857294619	{"initiatorofparent":"urn:Management:Post:Individual:1118804000","parent":"UPN:DMS:Complaints:Complaint:7508668","parentSubject":"urn:Document:Complaint:C_IS:76828139","child":"UPN:DMS:Decisions:Visa:6511821"}	f	\N	\N	Vising	\N	2016-02-25 16:20:43.51779
1626004	urn:Management:Post:Individual:1118804000	DMS:Complaints:Complaint	\N	urn:Document:Complaint:C_LC:88687448	{"subjectPrototype":"Document:Complaint:C_LC","child":"UPN:DMS:Complaints:Detective:5734686"}	f	\N	\N	CallCP	\N	2016-02-25 18:14:51.689335
6511821	urn:Management:Post:Individual:1118804000	DMS:Decisions:Visa	UPN:DMS:Complaints:Detective:7006567	urn:Document:Detective:C_IS:857294619	{"initiatorofparent":"urn:Management:Post:Individual:1118804000","parent":"UPN:DMS:Complaints:Detective:7006567"}	f	\N	\N	Decision	urn:Actor:AI:System:1	2016-02-25 17:22:13.274394
9659306	urn:Management:Post:Individual:1118804000	DMS:Complaints:Detective	UPN:DMS:Complaints:Complaint:9995676	urn:Document:Detective:C_LT:4580071	{"initiatorofparent":"urn:Management:Post:Individual:1118804000","parent":"UPN:DMS:Complaints:Complaint:9995676","parentSubject":"urn:Document:Complaint:C_LT:917825520"}	f	\N	\N	ProtocolEditing	urn:Management:Post:Individual:1118804000	2016-02-25 21:49:30.883856
\.


--
-- Data for Name: ManagedProcess_Journal_Record; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "ManagedProcess_Journal_Record" (id, "ManagedProcessExecutionRecord", stagedirection, operationtime, stage, actor, metadata) FROM stdin;
655837452	2084582	2	2016-02-25 15:33:22.920475	CreateDraft	\N	{}
257800176	2084582	2	2016-02-25 15:33:53.698595	Editing	urn:Management:Post:Individual:1118804000	{}
797938903	150798	1	2016-02-25 15:33:54.055507	ProtocolCreateDraft	\N	{}
765925000	150798	1	2016-02-25 15:33:54.625785	ProtocolEditing	urn:Management:Post:Individual:1118804000	{}
534261852	150798	1	2016-02-25 15:35:51.314429	ProtocolExtendRisk	urn:Management:Post:Individual:1118804000	{}
663581506	7508668	2	2016-02-25 16:19:38.856928	CreateDraft	\N	{}
420715414	7508668	2	2016-02-25 16:20:43.446535	Editing	urn:Management:Post:Individual:1118804000	{}
30916396	7006567	1	2016-02-25 16:20:43.874087	ProtocolCreateDraft	\N	{}
695119816	7006567	1	2016-02-25 16:20:44.499545	ProtocolEditing	urn:Management:Post:Individual:1118804000	{}
726676560	7006567	1	2016-02-25 17:17:27.264346	ProtocolExtendRisk	urn:Management:Post:Individual:1118804000	{}
586967290	7006567	1	2016-02-25 17:22:13.26375	Vising	\N	{}
991431865	1626004	1	2016-02-25 18:14:52.165468	CreateDraft	\N	{}
666650921	1626004	1	2016-02-25 18:14:52.486267	Editing	urn:Management:Post:Individual:1118804000	{}
812197628	1626004	1	2016-02-25 18:15:42.132664	CallCP	\N	{}
446098171	5734686	2	2016-02-25 18:15:42.79498	ProtocolCreateDraft	\N	{}
972808189	5734686	2	2016-02-25 18:17:38.996674	ProtocolEditing	urn:Management:Post:Individual:1118804000	{}
974588847	5734686	2	2016-02-25 18:21:46.683721	ProtocolExtendRisk	urn:Management:Post:Individual:1118804000	{}
182178162	831524	1	2016-02-25 18:21:47.05473	Decision	urn:Actor:AI:System:1	{}
630985009	9995676	2	2016-02-25 21:47:59.76101	CreateDraft	\N	{}
434032355	9995676	2	2016-02-25 21:49:30.857775	Editing	urn:Management:Post:Individual:1118804000	{}
463788921	9659306	1	2016-02-25 21:49:31.214788	ProtocolCreateDraft	\N	{}
447171212	9659306	1	2016-02-25 21:49:31.841574	ProtocolEditing	urn:Management:Post:Individual:1118804000	{}
486901296	2084582	1	2016-02-25 15:33:22.622781	CreateDraft	\N	{}
599026395	2084582	1	2016-02-25 15:33:22.945324	Editing	urn:Management:Post:Individual:1118804000	{}
836196338	2084582	1	2016-02-25 15:33:53.71652	CallCP	\N	{}
282500491	150798	2	2016-02-25 15:33:54.332993	ProtocolCreateDraft	\N	{}
341195731	150798	2	2016-02-25 15:35:50.978571	ProtocolEditing	urn:Management:Post:Individual:1118804000	{}
348325809	7508668	1	2016-02-25 16:19:38.545641	CreateDraft	\N	{}
629208035	7508668	1	2016-02-25 16:19:38.885874	Editing	urn:Management:Post:Individual:1118804000	{}
787882645	7508668	1	2016-02-25 16:20:43.506772	CallCP	\N	{}
519868767	7006567	2	2016-02-25 16:20:44.194603	ProtocolCreateDraft	\N	{}
47971045	7006567	2	2016-02-25 17:17:26.859317	ProtocolEditing	urn:Management:Post:Individual:1118804000	{}
142164557	7006567	2	2016-02-25 17:22:13.247833	ProtocolExtendRisk	urn:Management:Post:Individual:1118804000	{}
433095659	6511821	1	2016-02-25 17:22:13.615622	Decision	urn:Actor:AI:System:1	{}
607453127	1626004	2	2016-02-25 18:14:52.459709	CreateDraft	\N	{}
834740277	1626004	2	2016-02-25 18:15:42.11788	Editing	urn:Management:Post:Individual:1118804000	{}
363002869	5734686	1	2016-02-25 18:15:42.489082	ProtocolCreateDraft	\N	{}
168434324	5734686	1	2016-02-25 18:15:43.158925	ProtocolEditing	urn:Management:Post:Individual:1118804000	{}
546591364	5734686	1	2016-02-25 18:17:39.308342	ProtocolExtendRisk	urn:Management:Post:Individual:1118804000	{}
713829588	5734686	1	2016-02-25 18:21:46.699153	Vising	\N	{}
2796820	9995676	1	2016-02-25 21:47:59.465802	CreateDraft	\N	{}
829575493	9995676	1	2016-02-25 21:47:59.788225	Editing	urn:Management:Post:Individual:1118804000	{}
809319701	9995676	1	2016-02-25 21:49:30.872971	CallCP	\N	{}
106328549	9659306	2	2016-02-25 21:49:31.550729	ProtocolCreateDraft	\N	{}
\.


--
-- Data for Name: Management_Post_Group; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Management_Post_Group" (id, title, description, created, updated, ordered) FROM stdin;
1174599017	MegatestersZ	\N	1450777645	1450777645	1
1559741276	MaxGroupPosts	\N	1450863798	1450863798	4
2002081508	Специалисты по финансовым вопросам	\N	1451157554	1451157554	9
121405726	Юристы	\N	1451157421	1451157559	8
1123296469	Директора	\N	1451157380	1451157563	7
1334235394	Специалисты по экономическим вопросам	\N	1451157590	1451157590	10
2015280989	Бухгалтера	\N	1451157603	1451157609	11
1376342406	Менеджеры ВЭД	\N	1451157628	1451157628	12
159637227	Заведующие складом	\N	1451157660	1451157660	13
589216894	Контролеры качества	\N	1451157710	1451157710	14
465378719	Кладовщики	\N	1451157721	1451157721	15
1558178125	Водители штабелера	\N	1451157735	1451157735	16
1482646589	Менеджеры по административной деятельности	\N	1451157751	1451157751	17
1118263121	Комплектовщики товаров	\N	1451157785	1451157785	18
622983534	Дежурные по режиму	\N	1451157805	1451157805	19
928517613	Консультанты	\N	1451157829	1451157829	20
1617130418	Менеджеры	\N	1451157845	1451157845	21
878449532	Менеджеры по логистике	\N	1451157857	1451157857	22
44829920	Секретари	\N	1451157872	1451157872	23
1489733026	Брокеры	\N	1451157883	1451157883	24
1492262350	Администраторы системы	\N	1451157931	1451157940	25
805254919	Инженеры компьютерных систем	\N	1451158028	1451158034	26
528957266	Менеджеры систем качества	\N	1451158114	1451158114	27
1450740517	Менеджеры по логистике	\N	1451158135	1451158135	28
1445012633	Экономисты	\N	1451156135	1451158280	6
604271847	Диспетчера	\N	1451158367	1451158367	29
1744696571	Водители	\N	1451158390	1451158390	30
630738386	Инженеры по охране труда	\N	1451158449	1451158449	32
1517678892	Начальник отдела кадров	\N	1451158509	1451158509	33
1796506808	Главный инженер	\N	1451158544	1451158544	34
984772092	Начальник службы эксплуатации	\N	1451158549	1451158549	35
1701947962	Главный энергетик	\N	1451158597	1451158597	36
1675974039	Инженеры по автоматизированным системам управления производством	\N	1451158613	1451158613	37
786189638	Электрослесари	\N	1451158636	1451158636	38
1431341521	Машинисты холодильных установок	\N	1451158648	1451158648	39
1119889562	Начальник службы эксплуатации газа	\N	1451158657	1451158657	40
94572087	Операторы котельной	\N	1451158670	1451158670	41
622889556	Слесари	\N	1451158682	1451158682	42
901207660	Специалисты по контролю за эксплуатацией зданий - сооружений, помещений	\N	1451158701	1451158701	43
579279750	Уборщики	\N	1451158722	1451158722	44
512371010	Трактористы	\N	1451158733	1451158733	45
141834074	Столяры	\N	1451158748	1451158748	46
556299886	Плотники	\N	1451158756	1451158756	47
1242685801	Электросварщики	\N	1451158769	1451158775	48
485518107	Механики автомобильного гаража	\N	1451158411	1451158782	31
1425428266	Инженеры по технадзору	\N	1451158805	1451158805	49
2137845710	Табельники	\N	1451158813	1451158813	50
1820010695	Сметчики	\N	1451158822	1451158822	51
1976197731	Бригадиры на участках основного производства	\N	1451158837	1451158837	52
1468243865	Маляры	\N	1451158850	1451158850	53
716538322	Монтажники строительные	\N	1451158866	1451158866	54
354296824	Инженеры-электроники	\N	1451158883	1451158883	55
583603583	Арматурщики	\N	1451158892	1451158892	56
1994326448	Бетонщики	\N	1451158900	1451158900	57
\.


--
-- Data for Name: Management_Post_Individual; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Management_Post_Individual" (id, isactive, "ManagementPostGroup", "CompanyStructureDepartment", title, created, updated, ordered, "takepartinevents_EventProcessExecutionPlannedStaged") FROM stdin;
536231274	1	1123296469	905805394	Директор	1451159811	1451159811	10	\N
475767870	1	121405726	905805394	Юрист	1451160644	1451160644	11	\N
990258053	1	2002081508	828479089	Главный специалист по финансовым вопросам	1451291704	1451291704	12	\N
697130132	1	1334235394	828479089	Главный специалист по экономическим вопросам	1451291905	1451291905	13	\N
1109817505	1	2015280989	828479089	Главный бухгалтер	1451293665	1451293665	14	\N
1433279225	1	528957266	1261974287	Менеджер систем качества 1	1451296522	1451296522	15	\N
1138179996	1	805254919	1658312748	Инженер компьютерных систем 1	1451300389	1451300389	16	\N
1349687451	1	805254919	1658312748	Инженер компьютерных систем 2	1451300594	1451300594	17	\N
921220994	1	984772092	1743222417	Начальник службы эксплуатации	1451301657	1451301657	18	\N
1422803024	1	1445012633	1261974287	Экономист	1451156135	1451302348	9	\N
1724728515	1	1796506808	1743222417	Главный инженер	1451304069	1451304069	19	\N
275450805	1	1123296469	1261974287	Заместитель директора по вопросам обеспечения качества	1451304588	1451304805	20	\N
2065101785	1	1123296469	1190511098	Генеральный менеджер	1451308693	1451308693	21	\N
1404952896	1	589216894	1870096956	Контролер качества (Санофи+Линк)	1451309652	1451309652	22	\N
1897084668	1	589216894	1870096956	Контролер качества (Тева+Рош+Аптека)	1451309727	1451309727	23	\N
272312991	1	1450740517	1190511098	Менеджер по логистике 1	1451310926	1451310926	24	\N
406611786	1	159637227	1870096956	Заместитель заведующего складом (Тева+Джонсон+Астелас+Линк)	1451318192	1451318192	25	\N
1601586017	1	159637227	1870096956	Заместитель заведующего складом (Санофи+Аптека+Рош)	1451318332	1451318332	26	\N
1982566019	1	1492262350	1658312748	Администратор системы	1451319331	1451319331	27	\N
1241886272	1	1123296469	905805394	Заместитель директора	1451320286	1451320286	28	\N
1939617403	1	1517678892	1412798552	Начальник отдела кадров	1451328061	1451328061	29	\N
680501005	1	2015280989	828479089	Бухгалтер транспортный	1450801190	1451373479	4	\N
1118804000	1	1559741276	828479089	MaxPost	1450863798	1453044859	7	\N
1962987279	1	528957266	1261974287	Менеджер систем качества 2	1450800316	1453045128	2	\N
1816416714	1	1174599017	1658312748	TesterZ	1450777645	1453742980	1	\N
\.


--
-- Data for Name: Membership_Online_Record; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Membership_Online_Record" (id, "ActorUserSystem", securehash, hash, renewhash, ip, created) FROM stdin;
1255247246	540038425	\N	622008495	\N	127.0.0.1	1450217017
1082588492	540038425	\N	1644336212	\N	127.0.0.1	1450217017
1927487255	93140008	\N	985635244	\N	127.0.0.1	1450777685
1786799274	13558749	\N	1719906235	\N	127.0.0.1	1450863501
1981965110	13558749	\N	212786247	\N	127.0.0.1	1450863501
1868222145	572050616	\N	581745050	\N	127.0.0.1	1450886520
1334107638	183895516	\N	424152625	\N	127.0.0.1	1450887308
1681894430	2044866097	\N	205567005	\N	127.0.0.1	1450888069
459906441	1308577359	\N	1157289990	\N	127.0.0.1	1450962775
1822710622	1051038495	\N	1048737107	\N	127.0.0.1	1451140644
990339995	645239602	\N	887391659	\N	127.0.0.1	1451308005
824211441	645239602	\N	387437653	\N	127.0.0.1	1451308005
1391527553	1513412582	\N	1965012474	\N	127.0.0.1	1451308134
2066595592	1513412582	\N	1696486585	\N	127.0.0.1	1451308134
1855963981	96636757	\N	23202599	\N	127.0.0.1	1451308198
1029786569	96636757	\N	1001653917	\N	127.0.0.1	1451308198
389618894	1454454205	\N	1816505418	\N	127.0.0.1	1451308784
210720293	1454454205	\N	1306879587	\N	127.0.0.1	1451308784
1041613856	755312870	\N	732305774	\N	127.0.0.1	1451309749
496263267	755312870	\N	1171046	\N	127.0.0.1	1451309749
524160622	1051038495	\N	1708132676	\N	127.0.0.1	1453040280
1011895139	183895516	\N	1224536408	\N	127.0.0.1	1453045090
1537461953	1051038495	\N	1199983059	\N	127.0.0.1	1453204846
287265594	540038425	\N	300209453	\N	127.0.0.1	1453209583
1874910746	540038425	\N	938238776	\N	127.0.0.1	1453735014
1469329915	1051038495	\N	2066045656	\N	178.216.8.244	1453917097
252950609	540038425	\N	1588180127	\N	178.216.8.244	1453926228
999049841	1051038495	\N	50862232	\N	178.216.8.244	1453927187
2044127715	1051038495	\N	1485571530	\N	178.216.8.244	1453927709
702268604	540038425	\N	1376391698	\N	178.216.8.244	1453927817
1965668356	540038425	\N	1910199257	\N	178.216.8.244	1453928365
567972901	1051038495	\N	1436248720	\N	212.90.62.231	1453931669
1433429926	540038425	\N	1329106533	\N	212.90.62.231	1453931787
1218368955	1051038495	\N	2113662378	\N	178.216.8.244	1453933791
633433116	1051038495	\N	1939241296	\N	93.183.198.82	1453976607
1871291700	540038425	\N	1341859416	\N	127.0.0.1	1454055228
426364416	1051038495	\N	1626591382	\N	127.0.0.1	1456408630
1562288917	1051038495	\N	844673046	\N	127.0.0.1	1456505881
\.


--
-- Data for Name: Membership_PasswordChangeIntent_ActivationToken; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Membership_PasswordChangeIntent_ActivationToken" (id, "ActorUserSystem", activationcode, created, emailssent) FROM stdin;
\.


--
-- Data for Name: Membership_RegisterIntent_ActivationToken; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Membership_RegisterIntent_ActivationToken" (id, email, activationcode, created, emailssent) FROM stdin;
\.


--
-- Data for Name: OAuth_Link_UserId; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "OAuth_Link_UserId" (id, "ActorUserSystem", userid64, oauth2service) FROM stdin;
\.


--
-- Data for Name: OAuth_Session_Tokens; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "OAuth_Session_Tokens" (id, "ActorUserSystem", oauth2service, oauthaccesstoken, oauthtokensecret, created, expire) FROM stdin;
\.


--
-- Data for Name: People_Employee_Counterparty; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "People_Employee_Counterparty" (id, isactive, "CompanyLegalEntityCounterparty", "ActorUserSystem", title, mail, number, created, updated, ordered) FROM stdin;
\.


--
-- Data for Name: People_Employee_Internal; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "People_Employee_Internal" (id, isactive, "ActorUserSystem", "ManagementPostIndividual", title, medicalinspectiondate, fluorographydate, created, updated, ordered, istrener, "issuedrealcopy_DMSCopyControled", "removedrealcopy_DMSCopyControled") FROM stdin;
956428531	1	1051038495	1118804000	Max Bezugly	\N	\N	1450863798	1450864285	7	0	\N	\N
534107133	1	621859804	536231274	Павлова В.В.	\N	\N	1451159811	1451159811	10	0	\N	\N
985766538	1	1769099644	475767870	Стопыкин В.С.	\N	\N	1451160644	1451160644	11	0	\N	\N
1344038391	1	542035496	990258053	Грибенник В.А.	\N	\N	1451291704	1451291704	12	0	\N	\N
992396615	1	688030890	697130132	Осокина М.И.	\N	\N	1451291905	1451291905	13	0	\N	\N
86946793	1	682905848	1138179996	Шлапак Д.Л.	\N	\N	1451300389	1451300389	16	0	\N	\N
126402954	1	1381457867	1349687451	Клестов М.М.	\N	\N	1451300594	1451300594	17	0	\N	\N
357363473	1	1933913319	921220994	Колесник В.Л.	\N	\N	1451301657	1451301657	18	0	\N	\N
227251779	1	758119360	275450805	Белоусова О.В.	\N	\N	1451304588	1451304588	20	0	\N	\N
702224267	1	645239602	1109817505	Прозорова О.О.	\N	\N	1451293665	1451308106	14	0	\N	\N
1897621294	1	1513412582	1433279225	Кузьмицкий К.	\N	\N	1451296522	1451308180	15	0	\N	\N
1419719096	1	96636757	1724728515	Пижук В.М.	\N	\N	1451304069	1451308221	19	0	\N	\N
1650998376	1	1410396892	1422803024	Лаврухина Г.В.	\N	\N	1451156135	1451308279	9	0	\N	\N
1574342117	1	183895516	1962987279	Пшеничная И.В.	\N	\N	1450800316	1451308354	2	0	\N	\N
1080319634	1	540038425	1816416714	Inna Okun	\N	\N	1450777645	1451308764	1	0	\N	\N
1936619102	1	1454454205	2065101785	Бычковская Т.Л.	\N	\N	1451308693	1451308832	21	0	\N	\N
495918308	1	1271104544	1897084668	Фурс С.Л.	\N	\N	1451309727	1451309727	23	0	\N	\N
136011810	1	755312870	1404952896	Петрина О.И.	\N	\N	1451309652	1451309790	22	0	\N	\N
889243770	1	1113816525	272312991	Тарасенко И.И.	\N	\N	1451310926	1451310926	24	0	\N	\N
359648071	1	1045410703	406611786	Топильский А.О.	\N	\N	1451318192	1451318192	25	0	\N	\N
469792787	1	441433506	1601586017	Копань А.В.	\N	\N	1451318332	1451318332	26	0	\N	\N
1264318487	1	851333727	1982566019	Якушко И.В.	\N	\N	1451319331	1451319331	27	0	\N	\N
1300157686	1	341959841	1241886272	Демченко Р.В.	\N	\N	1451320286	1451320286	28	0	\N	\N
23647114	1	1288440648	1939617403	Ерко А.В.	\N	\N	1451328061	1451328061	29	0	\N	\N
\.


--
-- Data for Name: PushNotification_Template_Simple; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "PushNotification_Template_Simple" (id, translated_ru, translated_en, title, description) FROM stdin;
\.


--
-- Data for Name: RBAC_DocumentPrototypeResponsible_System; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "RBAC_DocumentPrototypeResponsible_System" (id, delegationactive, managementrole, processprototype, subjectprototype, stage, ordered) FROM stdin;
1285205257	1	1118804000	340896185	2105755130	ProtocolEditing	27
1061172622	1	1118804000	340896185	2105755130	ProtocolExtendRisk	28
23903764	1	1118804000	340896185	1167203278	ProtocolExtendRisk	29
26641769	1	1118804000	340896185	986165029	ProtocolExtendRisk	30
1647492469	1	1118804000	340896185	878247650	ProtocolExtendRisk	31
901984512	1	1118804000	340896185	77748383	ProtocolExtendRisk	32
890501543	1	1118804000	340896185	1256701256	ProtocolExtendRisk	33
384119232	1	1118804000	340896185	1531101050	ProtocolExtendRisk	34
803604455	1	1118804000	340896185	986165029	ProtocolEditing	35
278140432	1	1118804000	340896185	1167203278	ProtocolEditing	36
1971518699	1	1118804000	340896185	878247650	ProtocolEditing	46
1783665063	1	1118804000	340896185	77748383	ProtocolEditing	47
1550706889	1	1118804000	340896185	1256701256	ProtocolEditing	48
1161471875	1	1118804000	340896185	1531101050	ProtocolEditing	49
920079099	1	1118804000	460955489	416562378	Considering	17
1603738207	1	1118804000	460955489	1863241730	Considering	18
158793491	1	1118804000	460955489	604705749	Considering	19
1466387001	1	1118804000	460955489	1622205108	Considering	20
2116549986	1	1118804000	460955489	1285839997	Considering	21
1867506935	1	1118804000	460955489	1268611089	Considering	22
1677955362	1	1118804000	460955489	170900260	Considering	23
1528227144	1	1118804000	460955489	733452792	Considering	24
835876534	1	1118804000	460955489	939900508	Considering	25
1046938923	1	1118804000	460955489	418793814	Considering	26
497857532	1	1118804000	460955489	1137292124	Considering	37
1486029195	1	1118804000	460955489	1083584237	Considering	38
1924684124	1	1118804000	460955489	40262480	Considering	39
1065363353	1	1118804000	460955489	2105419881	Considering	40
1509790496	1	1118804000	460955489	1048668441	Considering	41
292285491	1	1118804000	460955489	1928699298	Considering	42
1758882270	1	1118804000	460955489	336749675	Considering	43
1832678240	1	1118804000	460955489	288174676	Considering	44
48481282	1	1118804000	460955489	1257729064	Considering	45
1782114578	1	1816416714	2084105390	1543010125	Tour1_Step2	3
2101002429	1	1118804000	2084105390	1543010125	Tour1_Step1	4
824284356	1	1118804000	2084105390	1543010125	Tour1_Step3	5
1479553793	1	1118804000	2084105390	1543010125	Tour1_Step4	6
1516117898	1	1118804000	2084105390	1543010125	Tour2_Step5	7
1168952517	1	1118804000	167680249	486464159	Vising	50
1573200180	1	1118804000	545291303	676050443	Plalning	51
565795334	1	1118804000	545291303	676050443	Editing	52
\.


--
-- Data for Name: RBAC_ProcessStartPermission_System; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "RBAC_ProcessStartPermission_System" (id, accessactive, managementrole, processprototype, subjectprototype, ordered) FROM stdin;
1736518894	1	1118804000	1400449434	404140251	5
1978777221	1	1118804000	1400449434	703651450	4
1104363766	1	1118804000	460955489	1863241730	3
1615650340	1	1118804000	460955489	1268611089	2
1878576430	1	1118804000	460955489	416562378	1
1779067585	1	1118804000	460955489	1622205108	6
1939484990	1	1118804000	460955489	733452792	7
1470764201	1	1118804000	460955489	939900508	8
1647296237	1	1118804000	460955489	1285839997	9
1365320571	1	1118804000	460955489	418793814	10
575925896	1	1118804000	460955489	1257729064	11
179877857	1	1118804000	460955489	1083584237	12
1431094585	1	1118804000	460955489	1137292124	13
1030679719	1	1118804000	460955489	2105419881	14
1440566055	1	1118804000	460955489	1928699298	15
529872703	1	1118804000	460955489	288174676	16
1932572350	1	1118804000	460955489	545570964	17
1397250872	1	1118804000	460955489	359991105	18
82914819	1	1118804000	460955489	1214964131	19
1238634030	1	1118804000	460955489	174317803	20
1963366677	1	1118804000	460955489	40262480	21
799968965	1	1118804000	460955489	170900260	22
1015342901	1	1118804000	460955489	604705749	23
1279733770	1	1118804000	460955489	336749675	24
249682310	1	1118804000	460955489	1048668441	25
190703851	1	1118804000	1400449434	1960563853	26
708483742	1	1118804000	1400449434	1697834005	27
1017425224	1	1118804000	1400449434	1960794193	28
2066002559	1	1118804000	1400449434	1677181573	29
1682493198	1	1118804000	1400449434	1036859738	30
1807691162	1	1962987279	340896185	1167203278	31
2035514813	1	1962987279	340896185	1531101050	32
1691715787	1	1962987279	340896185	2105755130	33
1498482532	1	1962987279	340896185	878247650	34
1429582823	1	1962987279	340896185	1256701256	35
2103282942	1	1962987279	340896185	986165029	36
1632260815	1	1962987279	340896185	77748383	37
1709914100	1	1118804000	1282849890	1001425402	38
289014128	1	1118804000	1544741608	676050443	39
631330656	1	1118804000	167680249	873954393	40
101591031	1	1118804000	167680249	302885503	41
1367562776	1	1118804000	167680249	1058982785	42
590108978	1	1118804000	167680249	998804143	43
1439997244	1	1118804000	167680249	527566292	44
1087671588	1	1118804000	167680249	465354554	45
1012816497	1	1118804000	167680249	1398643653	46
7197441	1	1118804000	167680249	486464159	47
1777394335	1	1118804000	167680249	2057180597	48
239577956	1	1118804000	167680249	1331286282	49
2099606356	1	1118804000	167680249	845111633	50
693154578	1	1118804000	125012940	872530236	51
756231580	1	1118804000	125012940	122654907	52
634436903	1	1118804000	125012940	1867192760	53
1957442763	1	1118804000	125012940	1132211558	54
192539166	1	1118804000	125012940	138262019	55
2040822190	1	1118804000	125012940	156864361	56
1375331709	1	1118804000	125012940	1562279611	57
1209792940	1	1118804000	125012940	971604962	58
260357022	1	1118804000	125012940	475765487	59
731214745	1	1422803024	2084105390	1543010125	62
1968227427	1	536231274	167680249	873954393	63
39925753	1	536231274	167680249	465354554	64
477596228	1	536231274	167680249	1398643653	65
709363048	1	536231274	460955489	1268611089	66
1664915095	1	536231274	460955489	1863241730	67
1179721930	1	536231274	460955489	416562378	68
329232445	1	536231274	460955489	1622205108	69
1344179033	1	536231274	460955489	733452792	70
799353861	1	536231274	460955489	939900508	71
1035727774	1	536231274	460955489	1285839997	72
190694086	1	536231274	460955489	418793814	73
209372966	1	536231274	460955489	1257729064	74
1651660317	1	536231274	460955489	1083584237	75
1197982047	1	536231274	460955489	1137292124	76
589562716	1	536231274	460955489	2105419881	77
829887222	1	536231274	460955489	1048668441	78
1343726058	1	536231274	460955489	336749675	79
1133804333	1	536231274	460955489	1928699298	80
1662243843	1	536231274	460955489	288174676	81
1388278664	1	536231274	460955489	545570964	82
1535943341	1	536231274	460955489	359991105	83
1723970399	1	536231274	460955489	1214964131	84
1088510266	1	536231274	460955489	174317803	85
1367746263	1	536231274	460955489	40262480	86
207965583	1	536231274	460955489	170900260	87
1928138960	1	536231274	460955489	604705749	88
1399246338	1	536231274	1400449434	1960563853	89
1283614165	1	536231274	1400449434	703651450	90
168541249	1	536231274	1400449434	1697834005	91
216206914	1	536231274	1400449434	1960794193	92
1682822123	1	536231274	1400449434	1677181573	93
106534585	1	536231274	1400449434	1036859738	94
1532801763	1	536231274	1400449434	404140251	95
307938686	1	475767870	460955489	1622205108	96
295895660	1	475767870	460955489	170900260	97
1156378680	1	475767870	460955489	939900508	98
1968290132	1	1109817505	460955489	1257729064	99
394041178	1	1109817505	460955489	604705749	100
1678279579	1	1109817505	460955489	939900508	101
100227167	1	1433279225	1544741608	676050443	102
1475162660	1	1433279225	979439350	665478613	103
1684380755	1	1724728515	1600382509	44702326	104
1470550112	1	1724728515	1600382509	80301308	105
1631954235	1	1433279225	1544741608	676050443	106
1058392272	1	1962987279	1544741608	676050443	107
1707071906	1	1962987279	167680249	302885503	108
298359677	1	1962987279	167680249	1058982785	109
1958142256	1	1962987279	167680249	998804143	110
97078142	1	1962987279	167680249	527566292	111
226774940	1	1939617403	167680249	465354554	112
2075663272	1	1939617403	167680249	1398643653	113
2077343	1	1939617403	167680249	486464159	114
1352154196	1	1118804000	167680249	465354554	115
1991929222	1	1118804000	167680249	1398643653	116
1388115305	1	1118804000	167680249	486464159	117
503380660	1	1118804000	167680249	2057180597	118
2020627462	1	1939617403	167680249	2057180597	119
1470210086	1	1433279225	167680249	1331286282	120
23601454	1	1433279225	167680249	845111633	121
1938217273	1	1433279225	1282849890	1001425402	122
296228646	1	1962987279	1282849890	1001425402	123
1978854036	1	275450805	1282849890	1001425402	124
1111056474	1	1118804000	1600382509	44702326	60
880341228	1	1118804000	1600382509	80301308	61
1082294920	1	1816416714	1282849890	1001425402	125
1435167236	1	1118804000	979439350	665478613	126
647867629	1	1118804000	99930200	1061101692	128
1653517567	1	1118804000	1579880001	996324008	127
\.


--
-- Data for Name: RiskManagement_Risk_Approved; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "RiskManagement_Risk_Approved" (id, critical, "BusinessObjectRecordPolymorph", "DirectoryBusinessProcessItem", "DirectorySLAItem", "ManagementPostIndividual", controlperiod, title, producteffect, emergenceprobability, undetectedprobability, weighted, controlact, riskdescription, "controlactions_DirectoryControlActionUniversal") FROM stdin;
1836802929	1	\N	1937134718	2033393079	1962987279	47836635	Курс валют	1	12	21	2	Do it	Смена курса	{1056995091,301171611}
493378808	1	\N	1937134718	2033393079	1816416714	\N	fgbcbgg	2	3	2	\N	\N	\N	{570027031}
\.


--
-- Data for Name: RiskManagement_Risk_NotApproved; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "RiskManagement_Risk_NotApproved" (id, identified, "RiskManagementRiskApproved", documentoforigin, riskdescription, "DirectoryBusinessProcessItem", "BusinessObjectRecordPolymorph") FROM stdin;
834990974	0	\N	urn:Document:Detective:C_LT:839948176	fbfgbfg	1937134718	614895960
2139582129	0	\N	urn:Document:Detective:C_IS:857294619	nor	1937134718	10341110
1003463425	0	\N	urn:Document:Detective:C_IS:857294619	vdfvdfv NEW	1355639494	1293144792
298830768	0	\N	urn:Document:Detective:C_LC:583758799	rrr2	1937134718	206674127
\.


--
-- Data for Name: Study_RegulationStudy_A; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Study_RegulationStudy_A" (id, "StudyRegulationStudyQ", content, correctly, ordered, created, updated) FROM stdin;
\.


--
-- Data for Name: Study_RegulationStudy_Q; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Study_RegulationStudy_Q" (id, "DocumentRegulationsTA", content, ordered, created, updated) FROM stdin;
\.


--
-- Data for Name: Study_RegulationStudy_R; Type: TABLE DATA; Schema: public; Owner: bc
--

COPY "Study_RegulationStudy_R" (id, questionnaire, "user", question, useranswer, done, trua, falsea, alla, starttime, endtime, created, updated, "DocumentRegulationsASR") FROM stdin;
\.


--
-- Name: Actor_Role_System_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Actor_Role_System"
    ADD CONSTRAINT "Actor_Role_System_pkey" PRIMARY KEY (id);


--
-- Name: Actor_User_System_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Actor_User_System"
    ADD CONSTRAINT "Actor_User_System_pkey" PRIMARY KEY (id);


--
-- Name: BusinessObject_Record_Polymorph_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "BusinessObject_Record_Polymorph"
    ADD CONSTRAINT "BusinessObject_Record_Polymorph_pkey" PRIMARY KEY (id);


--
-- Name: Calendar_Period_Month_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Calendar_Period_Month"
    ADD CONSTRAINT "Calendar_Period_Month_pkey" PRIMARY KEY (id);


--
-- Name: Communication_Comment_Level2withEditingSuggestion_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Communication_Comment_Level2withEditingSuggestion"
    ADD CONSTRAINT "Communication_Comment_Level2withEditingSuggestion_pkey" PRIMARY KEY (id);


--
-- Name: Company_LegalEntity_Counterparty_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Company_LegalEntity_Counterparty"
    ADD CONSTRAINT "Company_LegalEntity_Counterparty_pkey" PRIMARY KEY (id);


--
-- Name: Company_Structure_Companygroup_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Company_Structure_Companygroup"
    ADD CONSTRAINT "Company_Structure_Companygroup_pkey" PRIMARY KEY (id);


--
-- Name: Company_Structure_Department_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Company_Structure_Department"
    ADD CONSTRAINT "Company_Structure_Department_pkey" PRIMARY KEY (id);


--
-- Name: Company_Structure_Division_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Company_Structure_Division"
    ADD CONSTRAINT "Company_Structure_Division_pkey" PRIMARY KEY (id);


--
-- Name: DMS_Copy_Controled_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "DMS_Copy_Controled"
    ADD CONSTRAINT "DMS_Copy_Controled_pkey" PRIMARY KEY (id);


--
-- Name: DMS_Copy_Realnoncontrolcopy_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "DMS_Copy_Realnoncontrolcopy"
    ADD CONSTRAINT "DMS_Copy_Realnoncontrolcopy_pkey" PRIMARY KEY (id);


--
-- Name: DMS_DecisionSheet_Signed_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "DMS_DecisionSheet_Signed"
    ADD CONSTRAINT "DMS_DecisionSheet_Signed_pkey" PRIMARY KEY (id);


--
-- Name: DMS_Document_Universal_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "DMS_Document_Universal"
    ADD CONSTRAINT "DMS_Document_Universal_pkey" PRIMARY KEY (id);


--
-- Name: DMS_Viewaccess_ByProcedure_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "DMS_Viewaccess_ByProcedure"
    ADD CONSTRAINT "DMS_Viewaccess_ByProcedure_pkey" PRIMARY KEY (id);


--
-- Name: Definition_Class_BusinessObject_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Definition_Class_BusinessObject"
    ADD CONSTRAINT "Definition_Class_BusinessObject_pkey" PRIMARY KEY (id);


--
-- Name: Definition_DocumentClass_ForPrototype_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Definition_DocumentClass_ForPrototype"
    ADD CONSTRAINT "Definition_DocumentClass_ForPrototype_pkey" PRIMARY KEY (id);


--
-- Name: Definition_Prototype_System_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Definition_Prototype_System"
    ADD CONSTRAINT "Definition_Prototype_System_pkey" PRIMARY KEY (id);


--
-- Name: Definition_Type_BusinessObject_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Definition_Type_BusinessObject"
    ADD CONSTRAINT "Definition_Type_BusinessObject_pkey" PRIMARY KEY (id);


--
-- Name: Directory_AdditionalSection_Simple_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Directory_AdditionalSection_Simple"
    ADD CONSTRAINT "Directory_AdditionalSection_Simple_pkey" PRIMARY KEY (id);


--
-- Name: Directory_Branch_Item_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Directory_Branch_Item"
    ADD CONSTRAINT "Directory_Branch_Item_pkey" PRIMARY KEY (id);


--
-- Name: Directory_BusinessProcess_Item_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Directory_BusinessProcess_Item"
    ADD CONSTRAINT "Directory_BusinessProcess_Item_pkey" PRIMARY KEY (id);


--
-- Name: Directory_BusinessProjects_Item_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Directory_BusinessProjects_Item"
    ADD CONSTRAINT "Directory_BusinessProjects_Item_pkey" PRIMARY KEY (id);


--
-- Name: Directory_CalendarPlan_Simple_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Directory_CalendarPlan_Simple"
    ADD CONSTRAINT "Directory_CalendarPlan_Simple_pkey" PRIMARY KEY (id);


--
-- Name: Directory_ControlAction_Universal_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Directory_ControlAction_Universal"
    ADD CONSTRAINT "Directory_ControlAction_Universal_pkey" PRIMARY KEY (id);


--
-- Name: Directory_Deviation_PreCapa_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Directory_Deviation_PreCapa"
    ADD CONSTRAINT "Directory_Deviation_PreCapa_pkey" PRIMARY KEY (id);


--
-- Name: Directory_Fixedasset_Simple_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Directory_Fixedasset_Simple"
    ADD CONSTRAINT "Directory_Fixedasset_Simple_pkey" PRIMARY KEY (id);


--
-- Name: Directory_KindOfOperations_Item_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Directory_KindOfOperations_Item"
    ADD CONSTRAINT "Directory_KindOfOperations_Item_pkey" PRIMARY KEY (id);


--
-- Name: Directory_Materialbase_Simple_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Directory_Materialbase_Simple"
    ADD CONSTRAINT "Directory_Materialbase_Simple_pkey" PRIMARY KEY (id);


--
-- Name: Directory_Media_Attributed_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Directory_Media_Attributed"
    ADD CONSTRAINT "Directory_Media_Attributed_pkey" PRIMARY KEY (id);


--
-- Name: Directory_MissingPeople_Item_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Directory_MissingPeople_Item"
    ADD CONSTRAINT "Directory_MissingPeople_Item_pkey" PRIMARY KEY (id);


--
-- Name: Directory_Options_Simple_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Directory_Options_Simple"
    ADD CONSTRAINT "Directory_Options_Simple_pkey" PRIMARY KEY (id);


--
-- Name: Directory_Replacement_Item_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Directory_Replacement_Item"
    ADD CONSTRAINT "Directory_Replacement_Item_pkey" PRIMARY KEY (id);


--
-- Name: Directory_Responsible_Simple_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Directory_Responsible_Simple"
    ADD CONSTRAINT "Directory_Responsible_Simple_pkey" PRIMARY KEY (id);


--
-- Name: Directory_Responsibletwo_Simple_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Directory_Responsibletwo_Simple"
    ADD CONSTRAINT "Directory_Responsibletwo_Simple_pkey" PRIMARY KEY (id);


--
-- Name: Directory_RiskProtocolSolution_SI_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Directory_RiskProtocolSolution_SI"
    ADD CONSTRAINT "Directory_RiskProtocolSolution_SI_pkey" PRIMARY KEY (id);


--
-- Name: Directory_SLA_Item_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Directory_SLA_Item"
    ADD CONSTRAINT "Directory_SLA_Item_pkey" PRIMARY KEY (id);


--
-- Name: Directory_Solutionvariants_Simple_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Directory_Solutionvariants_Simple"
    ADD CONSTRAINT "Directory_Solutionvariants_Simple_pkey" PRIMARY KEY (id);


--
-- Name: Directory_TechnicalTask_ForWorks_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Directory_TechnicalTask_ForWorks"
    ADD CONSTRAINT "Directory_TechnicalTask_ForWorks_pkey" PRIMARY KEY (id);


--
-- Name: Directory_TechnicalTask_Materials_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Directory_TechnicalTask_Materials"
    ADD CONSTRAINT "Directory_TechnicalTask_Materials_pkey" PRIMARY KEY (id);


--
-- Name: Directory_TechnicalTask_Works_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Directory_TechnicalTask_Works"
    ADD CONSTRAINT "Directory_TechnicalTask_Works_pkey" PRIMARY KEY (id);


--
-- Name: Directory_TenderBidder_Simple_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Directory_TenderBidder_Simple"
    ADD CONSTRAINT "Directory_TenderBidder_Simple_pkey" PRIMARY KEY (id);


--
-- Name: Directory_TenderPosition_Simple_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Directory_TenderPosition_Simple"
    ADD CONSTRAINT "Directory_TenderPosition_Simple_pkey" PRIMARY KEY (id);


--
-- Name: Document_Capa_Deviation_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Capa_Deviation"
    ADD CONSTRAINT "Document_Capa_Deviation_pkey" PRIMARY KEY (id);


--
-- Name: Document_Claim_R_LSC_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Claim_R_LSC"
    ADD CONSTRAINT "Document_Claim_R_LSC_pkey" PRIMARY KEY (id);


--
-- Name: Document_Claim_R_LSD_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Claim_R_LSD"
    ADD CONSTRAINT "Document_Claim_R_LSD_pkey" PRIMARY KEY (id);


--
-- Name: Document_Claim_R_LSM_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Claim_R_LSM"
    ADD CONSTRAINT "Document_Claim_R_LSM_pkey" PRIMARY KEY (id);


--
-- Name: Document_Claim_R_LST_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Claim_R_LST"
    ADD CONSTRAINT "Document_Claim_R_LST_pkey" PRIMARY KEY (id);


--
-- Name: Document_Claim_R_LSС_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Claim_R_LSС"
    ADD CONSTRAINT "Document_Claim_R_LSС_pkey" PRIMARY KEY (id);


--
-- Name: Document_Claim_R_OQF_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Claim_R_OQF"
    ADD CONSTRAINT "Document_Claim_R_OQF_pkey" PRIMARY KEY (id);


--
-- Name: Document_Claim_R_OQR_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Claim_R_OQR"
    ADD CONSTRAINT "Document_Claim_R_OQR_pkey" PRIMARY KEY (id);


--
-- Name: Document_Claim_R_PAD_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Claim_R_PAD"
    ADD CONSTRAINT "Document_Claim_R_PAD_pkey" PRIMARY KEY (id);


--
-- Name: Document_Claim_R_PAI_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Claim_R_PAI"
    ADD CONSTRAINT "Document_Claim_R_PAI_pkey" PRIMARY KEY (id);


--
-- Name: Document_Claim_R_PAT_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Claim_R_PAT"
    ADD CONSTRAINT "Document_Claim_R_PAT_pkey" PRIMARY KEY (id);


--
-- Name: Document_Claim_R_QDA_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Claim_R_QDA"
    ADD CONSTRAINT "Document_Claim_R_QDA_pkey" PRIMARY KEY (id);


--
-- Name: Document_Claim_R_QDC_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Claim_R_QDC"
    ADD CONSTRAINT "Document_Claim_R_QDC_pkey" PRIMARY KEY (id);


--
-- Name: Document_Claim_R_QDE_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Claim_R_QDE"
    ADD CONSTRAINT "Document_Claim_R_QDE_pkey" PRIMARY KEY (id);


--
-- Name: Document_Claim_R_QDM_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Claim_R_QDM"
    ADD CONSTRAINT "Document_Claim_R_QDM_pkey" PRIMARY KEY (id);


--
-- Name: Document_Claim_R_QDА_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Claim_R_QDА"
    ADD CONSTRAINT "Document_Claim_R_QDА_pkey" PRIMARY KEY (id);


--
-- Name: Document_Claim_R_RDC_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Claim_R_RDC"
    ADD CONSTRAINT "Document_Claim_R_RDC_pkey" PRIMARY KEY (id);


--
-- Name: Document_Claim_R_RDD_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Claim_R_RDD"
    ADD CONSTRAINT "Document_Claim_R_RDD_pkey" PRIMARY KEY (id);


--
-- Name: Document_Claim_R_RDE_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Claim_R_RDE"
    ADD CONSTRAINT "Document_Claim_R_RDE_pkey" PRIMARY KEY (id);


--
-- Name: Document_Claim_R_TD_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Claim_R_TD"
    ADD CONSTRAINT "Document_Claim_R_TD_pkey" PRIMARY KEY (id);


--
-- Name: Document_Claim_R_UPC_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Claim_R_UPC"
    ADD CONSTRAINT "Document_Claim_R_UPC_pkey" PRIMARY KEY (id);


--
-- Name: Document_Claim_R_UPE_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Claim_R_UPE"
    ADD CONSTRAINT "Document_Claim_R_UPE_pkey" PRIMARY KEY (id);


--
-- Name: Document_Claim_R_UPI_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Claim_R_UPI"
    ADD CONSTRAINT "Document_Claim_R_UPI_pkey" PRIMARY KEY (id);


--
-- Name: Document_Claim_R_UPK_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Claim_R_UPK"
    ADD CONSTRAINT "Document_Claim_R_UPK_pkey" PRIMARY KEY (id);


--
-- Name: Document_Claim_R_UPL_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Claim_R_UPL"
    ADD CONSTRAINT "Document_Claim_R_UPL_pkey" PRIMARY KEY (id);


--
-- Name: Document_Claim_R_UPP_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Claim_R_UPP"
    ADD CONSTRAINT "Document_Claim_R_UPP_pkey" PRIMARY KEY (id);


--
-- Name: Document_Complaint_C_IS_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Complaint_C_IS"
    ADD CONSTRAINT "Document_Complaint_C_IS_pkey" PRIMARY KEY (id);


--
-- Name: Document_Complaint_C_IV_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Complaint_C_IV"
    ADD CONSTRAINT "Document_Complaint_C_IV_pkey" PRIMARY KEY (id);


--
-- Name: Document_Complaint_C_IW_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Complaint_C_IW"
    ADD CONSTRAINT "Document_Complaint_C_IW_pkey" PRIMARY KEY (id);


--
-- Name: Document_Complaint_C_LB_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Complaint_C_LB"
    ADD CONSTRAINT "Document_Complaint_C_LB_pkey" PRIMARY KEY (id);


--
-- Name: Document_Complaint_C_LC_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Complaint_C_LC"
    ADD CONSTRAINT "Document_Complaint_C_LC_pkey" PRIMARY KEY (id);


--
-- Name: Document_Complaint_C_LP_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Complaint_C_LP"
    ADD CONSTRAINT "Document_Complaint_C_LP_pkey" PRIMARY KEY (id);


--
-- Name: Document_Complaint_C_LT_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Complaint_C_LT"
    ADD CONSTRAINT "Document_Complaint_C_LT_pkey" PRIMARY KEY (id);


--
-- Name: Document_ContractAgreement_SAE_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_ContractAgreement_SAE"
    ADD CONSTRAINT "Document_ContractAgreement_SAE_pkey" PRIMARY KEY (id);


--
-- Name: Document_ContractApplication_Universal_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_ContractApplication_Universal"
    ADD CONSTRAINT "Document_ContractApplication_Universal_pkey" PRIMARY KEY (id);


--
-- Name: Document_Contract_BW_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Contract_BW"
    ADD CONSTRAINT "Document_Contract_BW_pkey" PRIMARY KEY (id);


--
-- Name: Document_Contract_LC_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Contract_LC"
    ADD CONSTRAINT "Document_Contract_LC_pkey" PRIMARY KEY (id);


--
-- Name: Document_Contract_LOP_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Contract_LOP"
    ADD CONSTRAINT "Document_Contract_LOP_pkey" PRIMARY KEY (id);


--
-- Name: Document_Contract_LWP_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Contract_LWP"
    ADD CONSTRAINT "Document_Contract_LWP_pkey" PRIMARY KEY (id);


--
-- Name: Document_Contract_MT_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Contract_MT"
    ADD CONSTRAINT "Document_Contract_MT_pkey" PRIMARY KEY (id);


--
-- Name: Document_Contract_RSS_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Contract_RSS"
    ADD CONSTRAINT "Document_Contract_RSS_pkey" PRIMARY KEY (id);


--
-- Name: Document_Contract_SS_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Contract_SS"
    ADD CONSTRAINT "Document_Contract_SS_pkey" PRIMARY KEY (id);


--
-- Name: Document_Contract_TMC_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Contract_TMC"
    ADD CONSTRAINT "Document_Contract_TMC_pkey" PRIMARY KEY (id);


--
-- Name: Document_Contract_TME_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Contract_TME"
    ADD CONSTRAINT "Document_Contract_TME_pkey" PRIMARY KEY (id);


--
-- Name: Document_Copy_Controled_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Copy_Controled"
    ADD CONSTRAINT "Document_Copy_Controled_pkey" PRIMARY KEY (id);


--
-- Name: Document_Copy_Realnoncontrolcopy_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Copy_Realnoncontrolcopy"
    ADD CONSTRAINT "Document_Copy_Realnoncontrolcopy_pkey" PRIMARY KEY (id);


--
-- Name: Document_Correction_Capa_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Correction_Capa"
    ADD CONSTRAINT "Document_Correction_Capa_pkey" PRIMARY KEY (id);


--
-- Name: Document_Detective_C_IS_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Detective_C_IS"
    ADD CONSTRAINT "Document_Detective_C_IS_pkey" PRIMARY KEY (id);


--
-- Name: Document_Detective_C_IV_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Detective_C_IV"
    ADD CONSTRAINT "Document_Detective_C_IV_pkey" PRIMARY KEY (id);


--
-- Name: Document_Detective_C_IW_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Detective_C_IW"
    ADD CONSTRAINT "Document_Detective_C_IW_pkey" PRIMARY KEY (id);


--
-- Name: Document_Detective_C_LB_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Detective_C_LB"
    ADD CONSTRAINT "Document_Detective_C_LB_pkey" PRIMARY KEY (id);


--
-- Name: Document_Detective_C_LC_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Detective_C_LC"
    ADD CONSTRAINT "Document_Detective_C_LC_pkey" PRIMARY KEY (id);


--
-- Name: Document_Detective_C_LP_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Detective_C_LP"
    ADD CONSTRAINT "Document_Detective_C_LP_pkey" PRIMARY KEY (id);


--
-- Name: Document_Detective_C_LT_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Detective_C_LT"
    ADD CONSTRAINT "Document_Detective_C_LT_pkey" PRIMARY KEY (id);


--
-- Name: Document_Protocol_CT_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Protocol_CT"
    ADD CONSTRAINT "Document_Protocol_CT_pkey" PRIMARY KEY (id);


--
-- Name: Document_Protocol_EA_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Protocol_EA"
    ADD CONSTRAINT "Document_Protocol_EA_pkey" PRIMARY KEY (id);


--
-- Name: Document_Protocol_EC_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Protocol_EC"
    ADD CONSTRAINT "Document_Protocol_EC_pkey" PRIMARY KEY (id);


--
-- Name: Document_Protocol_KI_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Protocol_KI"
    ADD CONSTRAINT "Document_Protocol_KI_pkey" PRIMARY KEY (id);


--
-- Name: Document_Protocol_MT_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Protocol_MT"
    ADD CONSTRAINT "Document_Protocol_MT_pkey" PRIMARY KEY (id);


--
-- Name: Document_Protocol_RR_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Protocol_RR"
    ADD CONSTRAINT "Document_Protocol_RR_pkey" PRIMARY KEY (id);


--
-- Name: Document_Protocol_SI_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Protocol_SI"
    ADD CONSTRAINT "Document_Protocol_SI_pkey" PRIMARY KEY (id);


--
-- Name: Document_Protocol_TM_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Protocol_TM"
    ADD CONSTRAINT "Document_Protocol_TM_pkey" PRIMARY KEY (id);


--
-- Name: Document_Protocol_VT_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Protocol_VT"
    ADD CONSTRAINT "Document_Protocol_VT_pkey" PRIMARY KEY (id);


--
-- Name: Document_Protocol_СТ_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Protocol_СТ"
    ADD CONSTRAINT "Document_Protocol_СТ_pkey" PRIMARY KEY (id);


--
-- Name: Document_Regulations_AO_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Regulations_AO"
    ADD CONSTRAINT "Document_Regulations_AO_pkey" PRIMARY KEY (id);


--
-- Name: Document_Regulations_ASR_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Regulations_ASR"
    ADD CONSTRAINT "Document_Regulations_ASR_pkey" PRIMARY KEY (id);


--
-- Name: Document_Regulations_I_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Regulations_I"
    ADD CONSTRAINT "Document_Regulations_I_pkey" PRIMARY KEY (id);


--
-- Name: Document_Regulations_JD_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Regulations_JD"
    ADD CONSTRAINT "Document_Regulations_JD_pkey" PRIMARY KEY (id);


--
-- Name: Document_Regulations_MP_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Regulations_MP"
    ADD CONSTRAINT "Document_Regulations_MP_pkey" PRIMARY KEY (id);


--
-- Name: Document_Regulations_PV_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Regulations_PV"
    ADD CONSTRAINT "Document_Regulations_PV_pkey" PRIMARY KEY (id);


--
-- Name: Document_Regulations_P_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Regulations_P"
    ADD CONSTRAINT "Document_Regulations_P_pkey" PRIMARY KEY (id);


--
-- Name: Document_Regulations_SOP_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Regulations_SOP"
    ADD CONSTRAINT "Document_Regulations_SOP_pkey" PRIMARY KEY (id);


--
-- Name: Document_Regulations_TA_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Regulations_TA"
    ADD CONSTRAINT "Document_Regulations_TA_pkey" PRIMARY KEY (id);


--
-- Name: Document_Risk_Approved_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Risk_Approved"
    ADD CONSTRAINT "Document_Risk_Approved_pkey" PRIMARY KEY (id);


--
-- Name: Document_Risk_NotApproved_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Risk_NotApproved"
    ADD CONSTRAINT "Document_Risk_NotApproved_pkey" PRIMARY KEY (id);


--
-- Name: Document_Solution_Correction_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Solution_Correction"
    ADD CONSTRAINT "Document_Solution_Correction_pkey" PRIMARY KEY (id);


--
-- Name: Document_Solution_Universal_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Solution_Universal"
    ADD CONSTRAINT "Document_Solution_Universal_pkey" PRIMARY KEY (id);


--
-- Name: Document_Staffdoc_OF_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Staffdoc_OF"
    ADD CONSTRAINT "Document_Staffdoc_OF_pkey" PRIMARY KEY (id);


--
-- Name: Document_Staffdoc_OR_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Staffdoc_OR"
    ADD CONSTRAINT "Document_Staffdoc_OR_pkey" PRIMARY KEY (id);


--
-- Name: Document_Staffdoc_SD_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Staffdoc_SD"
    ADD CONSTRAINT "Document_Staffdoc_SD_pkey" PRIMARY KEY (id);


--
-- Name: Document_Staffdoc_SV_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Staffdoc_SV"
    ADD CONSTRAINT "Document_Staffdoc_SV_pkey" PRIMARY KEY (id);


--
-- Name: Document_TechnicalTask_ForMaterials_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_TechnicalTask_ForMaterials"
    ADD CONSTRAINT "Document_TechnicalTask_ForMaterials_pkey" PRIMARY KEY (id);


--
-- Name: Document_TechnicalTask_ForWorks_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_TechnicalTask_ForWorks"
    ADD CONSTRAINT "Document_TechnicalTask_ForWorks_pkey" PRIMARY KEY (id);


--
-- Name: Document_Tender_Extended_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Tender_Extended"
    ADD CONSTRAINT "Document_Tender_Extended_pkey" PRIMARY KEY (id);


--
-- Name: Document_Tender_TableAdditional_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Tender_TableAdditional"
    ADD CONSTRAINT "Document_Tender_TableAdditional_pkey" PRIMARY KEY (id);


--
-- Name: Document_Tender_Table_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Tender_Table"
    ADD CONSTRAINT "Document_Tender_Table_pkey" PRIMARY KEY (id);


--
-- Name: Document_Viewaccess_ByProcedure_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Document_Viewaccess_ByProcedure"
    ADD CONSTRAINT "Document_Viewaccess_ByProcedure_pkey" PRIMARY KEY (id);


--
-- Name: Event_ProcessExecutionPlanned_Staged_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Event_ProcessExecutionPlanned_Staged"
    ADD CONSTRAINT "Event_ProcessExecutionPlanned_Staged_pkey" PRIMARY KEY (id);


--
-- Name: Feed_Inbox_Document_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Feed_Inbox_Document"
    ADD CONSTRAINT "Feed_Inbox_Document_pkey" PRIMARY KEY (id);


--
-- Name: Feed_MPETicket_InboxItem_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Feed_MPETicket_InboxItem"
    ADD CONSTRAINT "Feed_MPETicket_InboxItem_pkey" PRIMARY KEY (id);


--
-- Name: HTTP_Redirect_FromURIToURI_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "HTTP_Redirect_FromURIToURI"
    ADD CONSTRAINT "HTTP_Redirect_FromURIToURI_pkey" PRIMARY KEY (id);


--
-- Name: Mail_Template_HTML_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Mail_Template_HTML"
    ADD CONSTRAINT "Mail_Template_HTML_pkey" PRIMARY KEY (id);


--
-- Name: Mail_Template_Plain_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Mail_Template_Plain"
    ADD CONSTRAINT "Mail_Template_Plain_pkey" PRIMARY KEY (id);


--
-- Name: ManagedProcess_Execution_Record_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "ManagedProcess_Execution_Record"
    ADD CONSTRAINT "ManagedProcess_Execution_Record_pkey" PRIMARY KEY (id);


--
-- Name: ManagedProcess_Journal_Record_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "ManagedProcess_Journal_Record"
    ADD CONSTRAINT "ManagedProcess_Journal_Record_pkey" PRIMARY KEY (id);


--
-- Name: Management_Post_Group_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Management_Post_Group"
    ADD CONSTRAINT "Management_Post_Group_pkey" PRIMARY KEY (id);


--
-- Name: Management_Post_Individual_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Management_Post_Individual"
    ADD CONSTRAINT "Management_Post_Individual_pkey" PRIMARY KEY (id);


--
-- Name: Membership_Online_Record_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Membership_Online_Record"
    ADD CONSTRAINT "Membership_Online_Record_pkey" PRIMARY KEY (id);


--
-- Name: Membership_PasswordChangeIntent_ActivationToken_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Membership_PasswordChangeIntent_ActivationToken"
    ADD CONSTRAINT "Membership_PasswordChangeIntent_ActivationToken_pkey" PRIMARY KEY (id);


--
-- Name: Membership_RegisterIntent_ActivationToken_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Membership_RegisterIntent_ActivationToken"
    ADD CONSTRAINT "Membership_RegisterIntent_ActivationToken_pkey" PRIMARY KEY (id);


--
-- Name: OAuth_Link_UserId_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "OAuth_Link_UserId"
    ADD CONSTRAINT "OAuth_Link_UserId_pkey" PRIMARY KEY (id);


--
-- Name: OAuth_Session_Tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "OAuth_Session_Tokens"
    ADD CONSTRAINT "OAuth_Session_Tokens_pkey" PRIMARY KEY (id);


--
-- Name: People_Employee_Counterparty_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "People_Employee_Counterparty"
    ADD CONSTRAINT "People_Employee_Counterparty_pkey" PRIMARY KEY (id);


--
-- Name: People_Employee_Internal_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "People_Employee_Internal"
    ADD CONSTRAINT "People_Employee_Internal_pkey" PRIMARY KEY (id);


--
-- Name: PushNotification_Template_Simple_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "PushNotification_Template_Simple"
    ADD CONSTRAINT "PushNotification_Template_Simple_pkey" PRIMARY KEY (id);


--
-- Name: RBAC_DocumentPrototypeResponsible_System_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "RBAC_DocumentPrototypeResponsible_System"
    ADD CONSTRAINT "RBAC_DocumentPrototypeResponsible_System_pkey" PRIMARY KEY (id);


--
-- Name: RBAC_ProcessStartPermission_System_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "RBAC_ProcessStartPermission_System"
    ADD CONSTRAINT "RBAC_ProcessStartPermission_System_pkey" PRIMARY KEY (id);


--
-- Name: RiskManagement_Risk_Approved_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "RiskManagement_Risk_Approved"
    ADD CONSTRAINT "RiskManagement_Risk_Approved_pkey" PRIMARY KEY (id);


--
-- Name: RiskManagement_Risk_NotApproved_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "RiskManagement_Risk_NotApproved"
    ADD CONSTRAINT "RiskManagement_Risk_NotApproved_pkey" PRIMARY KEY (id);


--
-- Name: Study_RegulationStudy_A_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Study_RegulationStudy_A"
    ADD CONSTRAINT "Study_RegulationStudy_A_pkey" PRIMARY KEY (id);


--
-- Name: Study_RegulationStudy_Q_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Study_RegulationStudy_Q"
    ADD CONSTRAINT "Study_RegulationStudy_Q_pkey" PRIMARY KEY (id);


--
-- Name: Study_RegulationStudy_R_pkey; Type: CONSTRAINT; Schema: public; Owner: bc; Tablespace: 
--

ALTER TABLE ONLY "Study_RegulationStudy_R"
    ADD CONSTRAINT "Study_RegulationStudy_R_pkey" PRIMARY KEY (id);


--
-- Name: Actor_User_System_active_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Actor_User_System_active_idx" ON "Actor_User_System" USING btree (active);


--
-- Name: Actor_User_System_system_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Actor_User_System_system_idx" ON "Actor_User_System" USING btree (system);


--
-- Name: Actor_User_System_tester_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Actor_User_System_tester_idx" ON "Actor_User_System" USING btree (tester);


--
-- Name: BusinessObject_Record_Polymorph_isarchive_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "BusinessObject_Record_Polymorph_isarchive_idx" ON "BusinessObject_Record_Polymorph" USING btree (isarchive);


--
-- Name: Calendar_Period_Month_isactive_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Calendar_Period_Month_isactive_idx" ON "Calendar_Period_Month" USING btree (isactive);


--
-- Name: Communication_Comment_Level2withEditingSuggestion_cancel_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Communication_Comment_Level2withEditingSuggestion_cancel_idx" ON "Communication_Comment_Level2withEditingSuggestion" USING btree (cancel);


--
-- Name: Communication_Comment_Level2withEditingSuggestion_iseditingsugg; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Communication_Comment_Level2withEditingSuggestion_iseditingsugg" ON "Communication_Comment_Level2withEditingSuggestion" USING btree (iseditingsuggestion);


--
-- Name: Company_LegalEntity_Counterparty_isactive_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Company_LegalEntity_Counterparty_isactive_idx" ON "Company_LegalEntity_Counterparty" USING btree (isactive);


--
-- Name: Company_LegalEntity_Counterparty_isclient_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Company_LegalEntity_Counterparty_isclient_idx" ON "Company_LegalEntity_Counterparty" USING btree (isclient);


--
-- Name: Company_LegalEntity_Counterparty_iscontractor_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Company_LegalEntity_Counterparty_iscontractor_idx" ON "Company_LegalEntity_Counterparty" USING btree (iscontractor);


--
-- Name: DMS_Copy_Controled_isactive_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "DMS_Copy_Controled_isactive_idx" ON "DMS_Copy_Controled" USING btree (isactive);


--
-- Name: DMS_Copy_Realnoncontrolcopy_isactive_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "DMS_Copy_Realnoncontrolcopy_isactive_idx" ON "DMS_Copy_Realnoncontrolcopy" USING btree (isactive);


--
-- Name: DMS_Copy_Realnoncontrolcopy_isreturn_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "DMS_Copy_Realnoncontrolcopy_isreturn_idx" ON "DMS_Copy_Realnoncontrolcopy" USING btree (isreturn);


--
-- Name: DMS_DecisionSheet_Signed_closed_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "DMS_DecisionSheet_Signed_closed_idx" ON "DMS_DecisionSheet_Signed" USING btree (closed);


--
-- Name: DMS_Viewaccess_ByProcedure_isactive_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "DMS_Viewaccess_ByProcedure_isactive_idx" ON "DMS_Viewaccess_ByProcedure" USING btree (isactive);


--
-- Name: DMS_Viewaccess_ByProcedure_isreturn_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "DMS_Viewaccess_ByProcedure_isreturn_idx" ON "DMS_Viewaccess_ByProcedure" USING btree (isreturn);


--
-- Name: Definition_Prototype_System_isprocess_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Definition_Prototype_System_isprocess_idx" ON "Definition_Prototype_System" USING btree (isprocess);


--
-- Name: Definition_Prototype_System_unmanaged_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Definition_Prototype_System_unmanaged_idx" ON "Definition_Prototype_System" USING btree (unmanaged);


--
-- Name: Definition_Prototype_System_withhardcopy_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Definition_Prototype_System_withhardcopy_idx" ON "Definition_Prototype_System" USING btree (withhardcopy);


--
-- Name: Directory_CalendarPlan_Simple_date_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Directory_CalendarPlan_Simple_date_idx" ON "Directory_CalendarPlan_Simple" USING btree (date);


--
-- Name: Directory_TechnicalTask_ForWorks_datebegin_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Directory_TechnicalTask_ForWorks_datebegin_idx" ON "Directory_TechnicalTask_ForWorks" USING btree (datebegin);


--
-- Name: Directory_TechnicalTask_ForWorks_dateend_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Directory_TechnicalTask_ForWorks_dateend_idx" ON "Directory_TechnicalTask_ForWorks" USING btree (dateend);


--
-- Name: Directory_TechnicalTask_Materials_date_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Directory_TechnicalTask_Materials_date_idx" ON "Directory_TechnicalTask_Materials" USING btree (date);


--
-- Name: Directory_TechnicalTask_Works_datebegin_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Directory_TechnicalTask_Works_datebegin_idx" ON "Directory_TechnicalTask_Works" USING btree (datebegin);


--
-- Name: Directory_TechnicalTask_Works_dateend_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Directory_TechnicalTask_Works_dateend_idx" ON "Directory_TechnicalTask_Works" USING btree (dateend);


--
-- Name: Document_Claim_R_LSC_plancomingdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Claim_R_LSC_plancomingdate_idx" ON "Document_Claim_R_LSC" USING btree (plancomingdate);


--
-- Name: Document_Claim_R_LSD_planshipmentdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Claim_R_LSD_planshipmentdate_idx" ON "Document_Claim_R_LSD" USING btree (planshipmentdate);


--
-- Name: Document_Claim_R_LSM_desireddate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Claim_R_LSM_desireddate_idx" ON "Document_Claim_R_LSM" USING btree (desireddate);


--
-- Name: Document_Claim_R_LST_customsclearancedate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Claim_R_LST_customsclearancedate_idx" ON "Document_Claim_R_LST" USING btree (customsclearancedate);


--
-- Name: Document_Claim_R_LSС_plancomingdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Claim_R_LSС_plancomingdate_idx" ON "Document_Claim_R_LSС" USING btree (plancomingdate);


--
-- Name: Document_Claim_R_QDA_datenext_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Claim_R_QDA_datenext_idx" ON "Document_Claim_R_QDA" USING btree (datenext);


--
-- Name: Document_Claim_R_QDA_dateprev_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Claim_R_QDA_dateprev_idx" ON "Document_Claim_R_QDA" USING btree (dateprev);


--
-- Name: Document_Claim_R_QDE_datenext_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Claim_R_QDE_datenext_idx" ON "Document_Claim_R_QDE" USING btree (datenext);


--
-- Name: Document_Claim_R_QDE_dateprev_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Claim_R_QDE_dateprev_idx" ON "Document_Claim_R_QDE" USING btree (dateprev);


--
-- Name: Document_Claim_R_QDА_datenext_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Claim_R_QDА_datenext_idx" ON "Document_Claim_R_QDА" USING btree (datenext);


--
-- Name: Document_Claim_R_QDА_dateprev_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Claim_R_QDА_dateprev_idx" ON "Document_Claim_R_QDА" USING btree (dateprev);


--
-- Name: Document_Complaint_C_IS_dateend_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_IS_dateend_idx" ON "Document_Complaint_C_IS" USING btree (dateend);


--
-- Name: Document_Complaint_C_IS_datestart_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_IS_datestart_idx" ON "Document_Complaint_C_IS" USING btree (datestart);


--
-- Name: Document_Complaint_C_IS_fromclient_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_IS_fromclient_idx" ON "Document_Complaint_C_IS" USING btree (fromclient);


--
-- Name: Document_Complaint_C_IS_stillactual_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_IS_stillactual_idx" ON "Document_Complaint_C_IS" USING btree (stillactual);


--
-- Name: Document_Complaint_C_IV_dateend_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_IV_dateend_idx" ON "Document_Complaint_C_IV" USING btree (dateend);


--
-- Name: Document_Complaint_C_IV_datestart_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_IV_datestart_idx" ON "Document_Complaint_C_IV" USING btree (datestart);


--
-- Name: Document_Complaint_C_IV_fromclient_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_IV_fromclient_idx" ON "Document_Complaint_C_IV" USING btree (fromclient);


--
-- Name: Document_Complaint_C_IV_stillactual_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_IV_stillactual_idx" ON "Document_Complaint_C_IV" USING btree (stillactual);


--
-- Name: Document_Complaint_C_IW_dateend_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_IW_dateend_idx" ON "Document_Complaint_C_IW" USING btree (dateend);


--
-- Name: Document_Complaint_C_IW_datestart_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_IW_datestart_idx" ON "Document_Complaint_C_IW" USING btree (datestart);


--
-- Name: Document_Complaint_C_IW_fromclient_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_IW_fromclient_idx" ON "Document_Complaint_C_IW" USING btree (fromclient);


--
-- Name: Document_Complaint_C_IW_stillactual_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_IW_stillactual_idx" ON "Document_Complaint_C_IW" USING btree (stillactual);


--
-- Name: Document_Complaint_C_LB_dateend_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_LB_dateend_idx" ON "Document_Complaint_C_LB" USING btree (dateend);


--
-- Name: Document_Complaint_C_LB_datestart_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_LB_datestart_idx" ON "Document_Complaint_C_LB" USING btree (datestart);


--
-- Name: Document_Complaint_C_LB_fromclient_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_LB_fromclient_idx" ON "Document_Complaint_C_LB" USING btree (fromclient);


--
-- Name: Document_Complaint_C_LB_invoicedate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_LB_invoicedate_idx" ON "Document_Complaint_C_LB" USING btree (invoicedate);


--
-- Name: Document_Complaint_C_LB_stillactual_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_LB_stillactual_idx" ON "Document_Complaint_C_LB" USING btree (stillactual);


--
-- Name: Document_Complaint_C_LC_dateend_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_LC_dateend_idx" ON "Document_Complaint_C_LC" USING btree (dateend);


--
-- Name: Document_Complaint_C_LC_datestart_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_LC_datestart_idx" ON "Document_Complaint_C_LC" USING btree (datestart);


--
-- Name: Document_Complaint_C_LC_fromclient_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_LC_fromclient_idx" ON "Document_Complaint_C_LC" USING btree (fromclient);


--
-- Name: Document_Complaint_C_LC_stillactual_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_LC_stillactual_idx" ON "Document_Complaint_C_LC" USING btree (stillactual);


--
-- Name: Document_Complaint_C_LP_dateend_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_LP_dateend_idx" ON "Document_Complaint_C_LP" USING btree (dateend);


--
-- Name: Document_Complaint_C_LP_datestart_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_LP_datestart_idx" ON "Document_Complaint_C_LP" USING btree (datestart);


--
-- Name: Document_Complaint_C_LP_fromclient_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_LP_fromclient_idx" ON "Document_Complaint_C_LP" USING btree (fromclient);


--
-- Name: Document_Complaint_C_LP_stillactual_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_LP_stillactual_idx" ON "Document_Complaint_C_LP" USING btree (stillactual);


--
-- Name: Document_Complaint_C_LT_dateend_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_LT_dateend_idx" ON "Document_Complaint_C_LT" USING btree (dateend);


--
-- Name: Document_Complaint_C_LT_datestart_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_LT_datestart_idx" ON "Document_Complaint_C_LT" USING btree (datestart);


--
-- Name: Document_Complaint_C_LT_fromclient_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_LT_fromclient_idx" ON "Document_Complaint_C_LT" USING btree (fromclient);


--
-- Name: Document_Complaint_C_LT_stillactual_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_LT_stillactual_idx" ON "Document_Complaint_C_LT" USING btree (stillactual);


--
-- Name: Document_Complaint_C_LT_transportdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Complaint_C_LT_transportdate_idx" ON "Document_Complaint_C_LT" USING btree (transportdate);


--
-- Name: Document_ContractAgreement_SAE_date_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_ContractAgreement_SAE_date_idx" ON "Document_ContractAgreement_SAE" USING btree (date);


--
-- Name: Document_Contract_BW_date_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Contract_BW_date_idx" ON "Document_Contract_BW" USING btree (date);


--
-- Name: Document_Contract_BW_enddate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Contract_BW_enddate_idx" ON "Document_Contract_BW" USING btree (enddate);


--
-- Name: Document_Contract_LC_date_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Contract_LC_date_idx" ON "Document_Contract_LC" USING btree (date);


--
-- Name: Document_Contract_LC_enddate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Contract_LC_enddate_idx" ON "Document_Contract_LC" USING btree (enddate);


--
-- Name: Document_Contract_LOP_date_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Contract_LOP_date_idx" ON "Document_Contract_LOP" USING btree (date);


--
-- Name: Document_Contract_LOP_enddate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Contract_LOP_enddate_idx" ON "Document_Contract_LOP" USING btree (enddate);


--
-- Name: Document_Contract_LWP_date_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Contract_LWP_date_idx" ON "Document_Contract_LWP" USING btree (date);


--
-- Name: Document_Contract_LWP_enddate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Contract_LWP_enddate_idx" ON "Document_Contract_LWP" USING btree (enddate);


--
-- Name: Document_Contract_MT_date_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Contract_MT_date_idx" ON "Document_Contract_MT" USING btree (date);


--
-- Name: Document_Contract_MT_enddate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Contract_MT_enddate_idx" ON "Document_Contract_MT" USING btree (enddate);


--
-- Name: Document_Contract_RSS_date_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Contract_RSS_date_idx" ON "Document_Contract_RSS" USING btree (date);


--
-- Name: Document_Contract_RSS_enddate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Contract_RSS_enddate_idx" ON "Document_Contract_RSS" USING btree (enddate);


--
-- Name: Document_Contract_SS_date_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Contract_SS_date_idx" ON "Document_Contract_SS" USING btree (date);


--
-- Name: Document_Contract_SS_enddate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Contract_SS_enddate_idx" ON "Document_Contract_SS" USING btree (enddate);


--
-- Name: Document_Contract_TMC_date_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Contract_TMC_date_idx" ON "Document_Contract_TMC" USING btree (date);


--
-- Name: Document_Contract_TMC_enddate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Contract_TMC_enddate_idx" ON "Document_Contract_TMC" USING btree (enddate);


--
-- Name: Document_Contract_TME_date_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Contract_TME_date_idx" ON "Document_Contract_TME" USING btree (date);


--
-- Name: Document_Contract_TME_enddate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Contract_TME_enddate_idx" ON "Document_Contract_TME" USING btree (enddate);


--
-- Name: Document_Copy_Controled_isactive_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Copy_Controled_isactive_idx" ON "Document_Copy_Controled" USING btree (isactive);


--
-- Name: Document_Copy_Realnoncontrolcopy_isactive_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Copy_Realnoncontrolcopy_isactive_idx" ON "Document_Copy_Realnoncontrolcopy" USING btree (isactive);


--
-- Name: Document_Copy_Realnoncontrolcopy_isreturn_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Copy_Realnoncontrolcopy_isreturn_idx" ON "Document_Copy_Realnoncontrolcopy" USING btree (isreturn);


--
-- Name: Document_Correction_Capa_confirmed_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Correction_Capa_confirmed_idx" ON "Document_Correction_Capa" USING btree (confirmed);


--
-- Name: Document_Correction_Capa_selectsolution_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Correction_Capa_selectsolution_idx" ON "Document_Correction_Capa" USING btree (selectsolution);


--
-- Name: Document_Correction_Capa_selecttype_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Correction_Capa_selecttype_idx" ON "Document_Correction_Capa" USING btree (selecttype);


--
-- Name: Document_Correction_Capa_taskcompleted_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Correction_Capa_taskcompleted_idx" ON "Document_Correction_Capa" USING btree (taskcompleted);


--
-- Name: Document_Detective_C_IS_dateend_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_IS_dateend_idx" ON "Document_Detective_C_IS" USING btree (dateend);


--
-- Name: Document_Detective_C_IS_datestart_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_IS_datestart_idx" ON "Document_Detective_C_IS" USING btree (datestart);


--
-- Name: Document_Detective_C_IS_investigationdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_IS_investigationdate_idx" ON "Document_Detective_C_IS" USING btree (investigationdate);


--
-- Name: Document_Detective_C_IS_troublefixdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_IS_troublefixdate_idx" ON "Document_Detective_C_IS" USING btree (troublefixdate);


--
-- Name: Document_Detective_C_IV_dateend_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_IV_dateend_idx" ON "Document_Detective_C_IV" USING btree (dateend);


--
-- Name: Document_Detective_C_IV_datestart_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_IV_datestart_idx" ON "Document_Detective_C_IV" USING btree (datestart);


--
-- Name: Document_Detective_C_IV_investigationdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_IV_investigationdate_idx" ON "Document_Detective_C_IV" USING btree (investigationdate);


--
-- Name: Document_Detective_C_IV_troublefixdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_IV_troublefixdate_idx" ON "Document_Detective_C_IV" USING btree (troublefixdate);


--
-- Name: Document_Detective_C_IW_dateend_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_IW_dateend_idx" ON "Document_Detective_C_IW" USING btree (dateend);


--
-- Name: Document_Detective_C_IW_datestart_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_IW_datestart_idx" ON "Document_Detective_C_IW" USING btree (datestart);


--
-- Name: Document_Detective_C_IW_investigationdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_IW_investigationdate_idx" ON "Document_Detective_C_IW" USING btree (investigationdate);


--
-- Name: Document_Detective_C_IW_troublefixdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_IW_troublefixdate_idx" ON "Document_Detective_C_IW" USING btree (troublefixdate);


--
-- Name: Document_Detective_C_LB_dateend_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_LB_dateend_idx" ON "Document_Detective_C_LB" USING btree (dateend);


--
-- Name: Document_Detective_C_LB_datestart_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_LB_datestart_idx" ON "Document_Detective_C_LB" USING btree (datestart);


--
-- Name: Document_Detective_C_LB_investigationdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_LB_investigationdate_idx" ON "Document_Detective_C_LB" USING btree (investigationdate);


--
-- Name: Document_Detective_C_LB_troublefixdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_LB_troublefixdate_idx" ON "Document_Detective_C_LB" USING btree (troublefixdate);


--
-- Name: Document_Detective_C_LC_dateend_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_LC_dateend_idx" ON "Document_Detective_C_LC" USING btree (dateend);


--
-- Name: Document_Detective_C_LC_datestart_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_LC_datestart_idx" ON "Document_Detective_C_LC" USING btree (datestart);


--
-- Name: Document_Detective_C_LC_investigationdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_LC_investigationdate_idx" ON "Document_Detective_C_LC" USING btree (investigationdate);


--
-- Name: Document_Detective_C_LC_troublefixdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_LC_troublefixdate_idx" ON "Document_Detective_C_LC" USING btree (troublefixdate);


--
-- Name: Document_Detective_C_LP_dateend_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_LP_dateend_idx" ON "Document_Detective_C_LP" USING btree (dateend);


--
-- Name: Document_Detective_C_LP_datestart_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_LP_datestart_idx" ON "Document_Detective_C_LP" USING btree (datestart);


--
-- Name: Document_Detective_C_LP_investigationdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_LP_investigationdate_idx" ON "Document_Detective_C_LP" USING btree (investigationdate);


--
-- Name: Document_Detective_C_LP_troublefixdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_LP_troublefixdate_idx" ON "Document_Detective_C_LP" USING btree (troublefixdate);


--
-- Name: Document_Detective_C_LT_dateend_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_LT_dateend_idx" ON "Document_Detective_C_LT" USING btree (dateend);


--
-- Name: Document_Detective_C_LT_datestart_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_LT_datestart_idx" ON "Document_Detective_C_LT" USING btree (datestart);


--
-- Name: Document_Detective_C_LT_investigationdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_LT_investigationdate_idx" ON "Document_Detective_C_LT" USING btree (investigationdate);


--
-- Name: Document_Detective_C_LT_troublefixdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Detective_C_LT_troublefixdate_idx" ON "Document_Detective_C_LT" USING btree (troublefixdate);


--
-- Name: Document_Protocol_CT_date_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Protocol_CT_date_idx" ON "Document_Protocol_CT" USING btree (date);


--
-- Name: Document_Protocol_CT_datep_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Protocol_CT_datep_idx" ON "Document_Protocol_CT" USING btree (datep);


--
-- Name: Document_Protocol_EA_date_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Protocol_EA_date_idx" ON "Document_Protocol_EA" USING btree (date);


--
-- Name: Document_Protocol_EA_datep_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Protocol_EA_datep_idx" ON "Document_Protocol_EA" USING btree (datep);


--
-- Name: Document_Protocol_EC_date_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Protocol_EC_date_idx" ON "Document_Protocol_EC" USING btree (date);


--
-- Name: Document_Protocol_EC_datep_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Protocol_EC_datep_idx" ON "Document_Protocol_EC" USING btree (datep);


--
-- Name: Document_Protocol_KI_date_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Protocol_KI_date_idx" ON "Document_Protocol_KI" USING btree (date);


--
-- Name: Document_Protocol_MT_date_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Protocol_MT_date_idx" ON "Document_Protocol_MT" USING btree (date);


--
-- Name: Document_Protocol_MT_datep_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Protocol_MT_datep_idx" ON "Document_Protocol_MT" USING btree (datep);


--
-- Name: Document_Protocol_RR_date_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Protocol_RR_date_idx" ON "Document_Protocol_RR" USING btree (date);


--
-- Name: Document_Protocol_SI_date_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Protocol_SI_date_idx" ON "Document_Protocol_SI" USING btree (date);


--
-- Name: Document_Protocol_TM_datep_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Protocol_TM_datep_idx" ON "Document_Protocol_TM" USING btree (datep);


--
-- Name: Document_Protocol_TM_servicedate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Protocol_TM_servicedate_idx" ON "Document_Protocol_TM" USING btree (servicedate);


--
-- Name: Document_Protocol_VT_currentcheck_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Protocol_VT_currentcheck_idx" ON "Document_Protocol_VT" USING btree (currentcheck);


--
-- Name: Document_Protocol_VT_latestcheck_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Protocol_VT_latestcheck_idx" ON "Document_Protocol_VT" USING btree (latestcheck);


--
-- Name: Document_Protocol_VT_nextcheck_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Protocol_VT_nextcheck_idx" ON "Document_Protocol_VT" USING btree (nextcheck);


--
-- Name: Document_Protocol_СТ_date_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Protocol_СТ_date_idx" ON "Document_Protocol_СТ" USING btree (date);


--
-- Name: Document_Regulations_AO_effectivedate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Regulations_AO_effectivedate_idx" ON "Document_Regulations_AO" USING btree (effectivedate);


--
-- Name: Document_Regulations_ASR_planneddate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Regulations_ASR_planneddate_idx" ON "Document_Regulations_ASR" USING btree (planneddate);


--
-- Name: Document_Regulations_ASR_realeventdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Regulations_ASR_realeventdate_idx" ON "Document_Regulations_ASR" USING btree (realeventdate);


--
-- Name: Document_Regulations_I_effectivedate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Regulations_I_effectivedate_idx" ON "Document_Regulations_I" USING btree (effectivedate);


--
-- Name: Document_Regulations_I_enddate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Regulations_I_enddate_idx" ON "Document_Regulations_I" USING btree (enddate);


--
-- Name: Document_Regulations_JD_effectivedate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Regulations_JD_effectivedate_idx" ON "Document_Regulations_JD" USING btree (effectivedate);


--
-- Name: Document_Regulations_MP_initialdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Regulations_MP_initialdate_idx" ON "Document_Regulations_MP" USING btree (initialdate);


--
-- Name: Document_Regulations_MP_lastdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Regulations_MP_lastdate_idx" ON "Document_Regulations_MP" USING btree (lastdate);


--
-- Name: Document_Regulations_P_effectivedate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Regulations_P_effectivedate_idx" ON "Document_Regulations_P" USING btree (effectivedate);


--
-- Name: Document_Regulations_P_enddate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Regulations_P_enddate_idx" ON "Document_Regulations_P" USING btree (enddate);


--
-- Name: Document_Regulations_SOP_effectivedate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Regulations_SOP_effectivedate_idx" ON "Document_Regulations_SOP" USING btree (effectivedate);


--
-- Name: Document_Regulations_SOP_enddate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Regulations_SOP_enddate_idx" ON "Document_Regulations_SOP" USING btree (enddate);


--
-- Name: Document_Risk_Approved_critical_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Risk_Approved_critical_idx" ON "Document_Risk_Approved" USING btree (critical);


--
-- Name: Document_Risk_NotApproved_identified_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Risk_NotApproved_identified_idx" ON "Document_Risk_NotApproved" USING btree (identified);


--
-- Name: Document_Solution_Correction_approveded_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Solution_Correction_approveded_idx" ON "Document_Solution_Correction" USING btree (approveded);


--
-- Name: Document_Solution_Correction_ready_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Solution_Correction_ready_idx" ON "Document_Solution_Correction" USING btree (ready);


--
-- Name: Document_Solution_Correction_realizationdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Solution_Correction_realizationdate_idx" ON "Document_Solution_Correction" USING btree (realizationdate);


--
-- Name: Document_Solution_Universal_realizationdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Solution_Universal_realizationdate_idx" ON "Document_Solution_Universal" USING btree (realizationdate);


--
-- Name: Document_Staffdoc_OF_dateofdismissal_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Staffdoc_OF_dateofdismissal_idx" ON "Document_Staffdoc_OF" USING btree (dateofdismissal);


--
-- Name: Document_Staffdoc_OR_date_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Staffdoc_OR_date_idx" ON "Document_Staffdoc_OR" USING btree (date);


--
-- Name: Document_Staffdoc_OR_dateend_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Staffdoc_OR_dateend_idx" ON "Document_Staffdoc_OR" USING btree (dateend);


--
-- Name: Document_Staffdoc_SD_createdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Staffdoc_SD_createdate_idx" ON "Document_Staffdoc_SD" USING btree (createdate);


--
-- Name: Document_Staffdoc_SD_date_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Staffdoc_SD_date_idx" ON "Document_Staffdoc_SD" USING btree (date);


--
-- Name: Document_Staffdoc_SV_createdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Staffdoc_SV_createdate_idx" ON "Document_Staffdoc_SV" USING btree (createdate);


--
-- Name: Document_Staffdoc_SV_date_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Staffdoc_SV_date_idx" ON "Document_Staffdoc_SV" USING btree (date);


--
-- Name: Document_Staffdoc_SV_dateend_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Staffdoc_SV_dateend_idx" ON "Document_Staffdoc_SV" USING btree (dateend);


--
-- Name: Document_Staffdoc_SV_datestart_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Staffdoc_SV_datestart_idx" ON "Document_Staffdoc_SV" USING btree (datestart);


--
-- Name: Document_Viewaccess_ByProcedure_isactive_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Viewaccess_ByProcedure_isactive_idx" ON "Document_Viewaccess_ByProcedure" USING btree (isactive);


--
-- Name: Document_Viewaccess_ByProcedure_isreturn_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Document_Viewaccess_ByProcedure_isreturn_idx" ON "Document_Viewaccess_ByProcedure" USING btree (isreturn);


--
-- Name: Event_ProcessExecutionPlanned_Staged_eventdate_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Event_ProcessExecutionPlanned_Staged_eventdate_idx" ON "Event_ProcessExecutionPlanned_Staged" USING btree (eventdate);


--
-- Name: Event_ProcessExecutionPlanned_Staged_isdateset_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Event_ProcessExecutionPlanned_Staged_isdateset_idx" ON "Event_ProcessExecutionPlanned_Staged" USING btree (isdateset);


--
-- Name: Event_ProcessExecutionPlanned_Staged_ismpestarted_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Event_ProcessExecutionPlanned_Staged_ismpestarted_idx" ON "Event_ProcessExecutionPlanned_Staged" USING btree (ismpestarted);


--
-- Name: Event_ProcessExecutionPlanned_Staged_planningresponsible_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Event_ProcessExecutionPlanned_Staged_planningresponsible_idx" ON "Event_ProcessExecutionPlanned_Staged" USING btree (planningresponsible);


--
-- Name: Feed_Inbox_Document_isprocessed_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Feed_Inbox_Document_isprocessed_idx" ON "Feed_Inbox_Document" USING btree (isprocessed);


--
-- Name: Feed_MPETicket_InboxItem_allowcomment_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Feed_MPETicket_InboxItem_allowcomment_idx" ON "Feed_MPETicket_InboxItem" USING btree (allowcomment);


--
-- Name: Feed_MPETicket_InboxItem_allowcomplete_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Feed_MPETicket_InboxItem_allowcomplete_idx" ON "Feed_MPETicket_InboxItem" USING btree (allowcomplete);


--
-- Name: Feed_MPETicket_InboxItem_allowearly_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Feed_MPETicket_InboxItem_allowearly_idx" ON "Feed_MPETicket_InboxItem" USING btree (allowearly);


--
-- Name: Feed_MPETicket_InboxItem_allowknowcuurentstage_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Feed_MPETicket_InboxItem_allowknowcuurentstage_idx" ON "Feed_MPETicket_InboxItem" USING btree (allowknowcuurentstage);


--
-- Name: Feed_MPETicket_InboxItem_allowopen_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Feed_MPETicket_InboxItem_allowopen_idx" ON "Feed_MPETicket_InboxItem" USING btree (allowopen);


--
-- Name: Feed_MPETicket_InboxItem_allowreadcomments_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Feed_MPETicket_InboxItem_allowreadcomments_idx" ON "Feed_MPETicket_InboxItem" USING btree (allowreadcomments);


--
-- Name: Feed_MPETicket_InboxItem_allowsave_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Feed_MPETicket_InboxItem_allowsave_idx" ON "Feed_MPETicket_InboxItem" USING btree (allowsave);


--
-- Name: Feed_MPETicket_InboxItem_allowseejournal_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Feed_MPETicket_InboxItem_allowseejournal_idx" ON "Feed_MPETicket_InboxItem" USING btree (allowseejournal);


--
-- Name: Feed_MPETicket_InboxItem_isvalid_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Feed_MPETicket_InboxItem_isvalid_idx" ON "Feed_MPETicket_InboxItem" USING btree (isvalid);


--
-- Name: Mail_Template_HTML_translated_en_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Mail_Template_HTML_translated_en_idx" ON "Mail_Template_HTML" USING btree (translated_en);


--
-- Name: Mail_Template_HTML_translated_ru_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Mail_Template_HTML_translated_ru_idx" ON "Mail_Template_HTML" USING btree (translated_ru);


--
-- Name: Mail_Template_Plain_translated_en_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Mail_Template_Plain_translated_en_idx" ON "Mail_Template_Plain" USING btree (translated_en);


--
-- Name: Mail_Template_Plain_translated_ru_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Mail_Template_Plain_translated_ru_idx" ON "Mail_Template_Plain" USING btree (translated_ru);


--
-- Name: ManagedProcess_Journal_Record_operationtime_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "ManagedProcess_Journal_Record_operationtime_idx" ON "ManagedProcess_Journal_Record" USING btree (operationtime);


--
-- Name: ManagedProcess_Journal_Record_stagedirection_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "ManagedProcess_Journal_Record_stagedirection_idx" ON "ManagedProcess_Journal_Record" USING btree (stagedirection);


--
-- Name: Management_Post_Individual_isactive_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "Management_Post_Individual_isactive_idx" ON "Management_Post_Individual" USING btree (isactive);


--
-- Name: OAuth_Session_Tokens_oauth2service_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "OAuth_Session_Tokens_oauth2service_idx" ON "OAuth_Session_Tokens" USING btree (oauth2service);


--
-- Name: People_Employee_Counterparty_isactive_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "People_Employee_Counterparty_isactive_idx" ON "People_Employee_Counterparty" USING btree (isactive);


--
-- Name: People_Employee_Internal_isactive_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "People_Employee_Internal_isactive_idx" ON "People_Employee_Internal" USING btree (isactive);


--
-- Name: People_Employee_Internal_istrener_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "People_Employee_Internal_istrener_idx" ON "People_Employee_Internal" USING btree (istrener);


--
-- Name: PushNotification_Template_Simple_translated_en_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "PushNotification_Template_Simple_translated_en_idx" ON "PushNotification_Template_Simple" USING btree (translated_en);


--
-- Name: PushNotification_Template_Simple_translated_ru_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "PushNotification_Template_Simple_translated_ru_idx" ON "PushNotification_Template_Simple" USING btree (translated_ru);


--
-- Name: RBAC_DocumentPrototypeResponsible_System_delegationactive_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "RBAC_DocumentPrototypeResponsible_System_delegationactive_idx" ON "RBAC_DocumentPrototypeResponsible_System" USING btree (delegationactive);


--
-- Name: RBAC_ProcessStartPermission_System_accessactive_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "RBAC_ProcessStartPermission_System_accessactive_idx" ON "RBAC_ProcessStartPermission_System" USING btree (accessactive);


--
-- Name: RiskManagement_Risk_Approved_critical_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "RiskManagement_Risk_Approved_critical_idx" ON "RiskManagement_Risk_Approved" USING btree (critical);


--
-- Name: RiskManagement_Risk_NotApproved_identified_idx; Type: INDEX; Schema: public; Owner: bc; Tablespace: 
--

CREATE INDEX "RiskManagement_Risk_NotApproved_identified_idx" ON "RiskManagement_Risk_NotApproved" USING btree (identified);


--
-- Name: public; Type: ACL; Schema: -; Owner: max
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM max;
GRANT ALL ON SCHEMA public TO max;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

