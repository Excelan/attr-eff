<?php 
/**
пустая, чтобы категории не подгружали к себе список все, что в них есть, по умолчанию
*/
class AnyTaxonomyRelatedAnything extends FormWidget
{
	protected function build()
	{
		try
		{
			$ff = $this->subject->name;
			$this->setData($this->eo->$ff);
		}
		catch (Exception $e)
		{
			$this->html = $e;
		}
	}
}
	
?>