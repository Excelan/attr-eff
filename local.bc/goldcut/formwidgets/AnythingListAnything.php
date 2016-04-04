<?php 
class AnythingListAnything extends FormWidget
{
	protected function build()
	{	
		try
		{
			//$ff = $this->subject->name;
			$ff = $this->meta['name'];
			//$this->setData($this->eo->$ff);
			//printlnd($ff);
			//printlnd($this->eo);
			
			$m = new Message();
			$m->action = 'members';
			$m->urn = new URN($this->eo->urn.':'.$ff);
			//println($m,1,TERM_BLUE);
			$listMembers = $m->deliver();
			//println($listMembers,1,TERM_VIOLET);
			
			if ($listMembers->count())
			{
				$m = new Message();
				$m->action = 'load';
				$m->urn = (string) $listMembers->entity;
				$m->in = $listMembers;
				$m->order = $listMembers->entity->defaultorder;
				//println($m,1,TERM_BLUE);
				$members = $m->deliver();
				//printlnd($members);
				$this->setData($members);
			}
			
			//$ff .= '-'.$this->meta['name'];
			
			$this->html .= "<script> var rt = [] </script>";
			$countRelated = count($this->data);
			if ($countRelated) 
			{
				// foreach($this->data as $d)				println($d);
				//$urns = $this->data->asURNs();
				//$this->html .= join(', ', $urns);
				$mm = json_encode($this->data->toArray());
				$this->html .= "<script> rt['$ff'] = $mm; </script>";
				//$this->html .= "<script> console.log($mm); </script>";
				//$this->html .= $mm;
			}
			
			// число
			/**
			$m = new Message();
			$m->action = "load";
			$m->urn = $this->subject->urn;
			//$m->order = array('title'=>'ASC'); // TODO! ADD IF HAS FIELD
			if ($this->subject->defaultorder)
				$m->order = $ENTITY->defaultorder;						
			$ds = $m->deliver();
			//$this->html .= $m;
			//$this->html .= $ds;
			$countPotential = count($ds);
			*/
			$countPotential = '.';
			
			//if ($countPotential < 100) // !!!!!!!!!!!!!!!!!!!!!!!!!!!
			if (false)
			{
				//$this->html .= Form::category_selectbox($this->subject, $ds, $urns, false, true);
			}
			else
			{
				// какие загружать до поиска? последние использованные, часто используемые
				$listid = $this->meta['ns'];
				$dataNS = "data-ns=$listid";
				$dataListName = "data-listname=$ff";
				$listentity = $this->meta['entity'];
				$dataentity = $listentity; // compat with simple join widget
				
				$this->html .= '
				<div class="fc" id="rt_'.$ff.'" '.$dataListName.' data-listid="'.$listid.'" data-listentity="'.$listentity.'">
					<div class="fleft">
						<div>
							<p>Search</p> <!-- (Всего '.$countPotential.') -->
							<input type="text" class="myInput" data-entity="'.$dataentity.'" '.$dataNS.' maxlength="255" /> 
							<!-- <span id="counter_number" class="counter">10</span> -->
						</div>
						<div class="m2" data-host='.$this->eo->urn.'>
							
						</div>
					</div>
					<div class="fleft m3" data-host='.$this->eo->urn.'>
						
					</div>
				</div>
				';
				
				//$jsuri = '/goldcut/js/formwidgets/'.__CLASS__.'.js';
				$jsuri = '/goldcut/js/formwidgets/AnythingListAnything.js';
				$jsfile = BASE_DIR.$jsuri;
				if ($jsfile) 
				{
					$this->html .= "<script src='{$jsuri}'></script>";
					$this->html .= "<script> rtInit('rt_{$ff}', rt['$ff']) </script>";
				}
			}	
		
		}
		catch (Exception $e)
		{
			$this->html = $e;
		}
		
		/*
		foreach ($this->data as $t)
		{
			$this->html .= "<input id='query-{$t->urn}' type=text name='{$ff}[]' value='{$t->urn}'><br>"; // inline in <photosContainer
		}
		*/
	}
}
	
?>