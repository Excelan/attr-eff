<?php 

class AnyContentRelatedAnyUser extends FormWidget
{
	protected function build()
	{	
		try
		{
			$ff = $this->subject->name;	
			$this->setData($this->eo->$ff);
			
			$this->html .= "<script> var rt = [] </script>";
			$countRelated = count($this->data);
			if ($countRelated) 
			{
				$urns = $this->data->asURNs();
				$mm = json_encode($this->data->toArray());
				$this->html .= "<script> rt['$ff'] = $mm </script>";
				//$this->html .= join(', ', $urns);
				//$this->html .= $mm;
			}
			
			// число
			$m = new Message();
			$m->action = "load";
			$m->last = 1000; // !!!
			$m->urn = $this->subject->urn;
			//if ($this->subject->has_field('title'))
			//	$m->order = array('title'=>'ASC');
			if ($this->subject->defaultorder)
				$m->order = $ENTITY->defaultorder;						
			$ds = $m->deliver();
			
			$countPotential = count($ds);
			//if (false)
			
			if (!defined('RELATED_WIDGET_MORPH_ON')) echo $this->html .= "<p>RELATED_WIDGET_MORPH_ON не определен в конфигурации</p>";
			
			if ($countPotential < RELATED_WIDGET_MORPH_ON)
			{
				
				$this->html .= Form::category_selectbox($this->subject, $ds, $urns, false, true);
			}
			else
			{
				// какие загружать до поиска? последние использованные, часто используемые
				$listid = 7;
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
				
				$jsuri = '/goldcut/js/formwidgets/AnyContentRelatedAnyContent.js';
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