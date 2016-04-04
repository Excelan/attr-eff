<?php

class Presentation
{

	public static function entityFormat($d)
	{
		
		$E = $d->entity;

		// EXTENDED STRUCTURE
		foreach ($E->extendstructure as $ee)
		{
			$extender = $d->$ee; // категория редактируемого объекта
			
			$extender->extendMergeParents();
			$propertiesAndVariators = Entity::extenderPropertiesVariatorsHelper($extender);
			$properties = $propertiesAndVariators['properties'];
			$variators = $propertiesAndVariators['variators'];

			foreach ($properties as $property)
			{
				$pname = $property->uri;
				$Fdyn = new FieldMeta(array('name'=>$pname,'title'=>$property->title,'type'=>$property->basetype,'units'=>$property->units));				
				$fv2 = Field::formatValue($Fdyn, $d->$pname);
				$fv = $d->$pname;
				$h .= "<dt class='TO'>{$property->title}</dt><dd class='TO'>{$fv}&nbsp;{$property->units}</dd>";
			}

			foreach ($variators as $variator)
			{			
				$pname = $variator->uri;
				if ($d->$pname)
				{
					if ($variator->multiple)
					{
						$vt = array();
						foreach ($d->$pname as $variation)
						{
							$vt[] = $variation->title;
						}
						$varsjoined = join(', ', $vt);
						$h .= "<dt>{$variator->title}</dt><dd>{$varsjoined}</dd>";
					}
					else //$cp = current($d->$pname); if ($cp) 
					{
						//printlnd($cp);
						//$uv = new URN($cp);
						//$variation = $uv->resolve();
						$variation = $d->$pname;
						$h .= "<dt class='TO'>{$variator->title}</dt><dd class='TO'>{$variation->title}</dd>";
					}
				}
				//$h .= Form::category_selectbox($variator, $variator->variation, $selected_urns, $include_blank, $multiple);
				
			}	
		}
		
		if ($h) $h = '<dl>'.$h.'</dl>';
		return $h;

	}
	
}


?>