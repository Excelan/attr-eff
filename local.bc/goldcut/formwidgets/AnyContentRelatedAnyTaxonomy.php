<?php 
class AnyContentRelatedAnyTaxonomy extends FormWidget
{
	protected function build()
	{	
		try
		{
				
			$ff = $this->subject->name;	
			//$this->setData($this->eo->$ff);
			
			$ENTITY = Entity::ref($ff);
			$m = new Message();
			$m->urn = 'urn:'.$ENTITY->name;
			$m->action = 'load';
			$m->last = 1000; // !!!
			if ($ENTITY->defaultorder)
				$m->order = $ENTITY->defaultorder;
			$data = $m->deliver();
			$this->setData($data);			

			$sel = $this->eo->$ff;
			if (count($sel))
				$selected_urns = $sel->asURNs();
			
			$this->html .= "<script> var rt = [] </script>";
			
			$countRelated = count($this->data);
			if ($countRelated) 
			{
				$urns = $this->data->asURNs(); // SAME as selected_urns
				$mm = json_encode($this->data->toArray());
				$this->html .= "<script> rt['$ff'] = $mm </script>";
			}
			
			// число
			/*
			$m = new Message();
			$m->action = "load";
			$m->urn = $this->subject->urn;
			$m->order = array('title'=>'ASC'); // TODO! ADD IF HAS FIELD
			$ds = $m->deliver();
			*/
			
			$countPotential = count($data); // $ds
			
			if (!defined('RELATED_WIDGET_MORPH_ON')) echo $this->html .= "<p>RELATED_WIDGET_MORPH_ON не определен в конфигурации</p>";
			
			if ($countPotential < RELATED_WIDGET_MORPH_ON)
			{
				$this->html .= Form::category_selectbox($this->subject, $this->data, $selected_urns, false, true);
			}
			else
			{
				// какие загружать до поиска? последние использованные, часто используемые
				/**
				TODO !!! STATIC LIST ID 7 !
				*/
				$listid = 7; // TODO!!!
				$this->html .= '
				<div class="fc" id="rt_'.$ff.'" data-listid="'.$listid.'">
					<div class="fleft">
						<div>
							<p>Поиск (Всего '.$countPotential.')</p>
							<input type="text" class="myInput" data-entity="'.$ff.'" maxlength="255" /> 
							<!-- <span id="counter_number" class="counter">10</span> -->
						</div>
						<div class="m2" data-host='.$this->eo->urn.'>
							
						</div>
					</div>
					<div class="fleft m3" data-host='.$this->eo->urn.'>
						
					</div>
				</div>
				';
				
				$jsuri = '/goldcut/js/formwidgets/'.__CLASS__.'.js';
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